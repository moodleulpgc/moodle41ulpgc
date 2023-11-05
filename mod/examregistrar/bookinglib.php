<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Internal library of functions for booking actions in module examregistrar
 *
 * All the examregistrar specific functions, needed to implement the module
 * logic, are placed here.
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/lib.php');



function examregistrar_set_lagdays($examregistrar, $config, $period, $capabilities) {
    if(!$period OR 
        (isset($capabilities['bookothers']) && $capabilities['bookothers']) OR
        (isset($capabilities['manageexams']) && $capabilities['manageexams'])) {

        return 0;
    }

    $lagdays = max($examregistrar->lagdays, $config->cutoffdays);
    $lagdays = ($period->calls > 1) ? $lagdays + $config->extradays : $lagdays;
    return $lagdays;
}


function examregistrar_check_exam_in_past1($now, $lagdays, $examdate) {
    $timelimit = strtotime("-$lagdays days ",  $examdate);
    return ($now > $timelimit);
}


function examregistrar_check_exam_in_past($now, $lagdays, $exam, $lastminute = false) {
    global $DB;
    
    if(!isset($exam->examdate)) {
        $exam->examdate = $DB->get_field('examregistrar_examsessions', 'examdate', 
                                        array('id'=>$exam->examsession, 
                                                'examregid'=>$exam->examregid, 
                                                'period'=>$exam->period));    
    }

    if(!$lastminute) {
       $examdate = usergetmidnight($exam->examdate);
    } else {
        // we can book to last minute, we need real exam starting hour
        if($deliveryhelpers = $DB->get_records_menu('examregistrar_examdelivery', 
                                                    ['examid' => $exam->id],
                                                    'timeopen ASC', 'id, timeopen', 0, 1)) { 
            $examdate = reset($deliveryhelpers);
        } else {
            // use session starting hour
            $timeslot = $DB->get_field('examregistrar_examsessions', 'timeslot', 
                                        array('id'=>$exam->examsession, 
                                                'examregid'=>$exam->examregid, 
                                                'period'=>$exam->period));    
            $examdate = $exam->examdate + 3600 * $timeslot;
        }
    }
    
    $timelimit = strtotime("-$lagdays days ",  $examdate);
    return ($now > $timelimit);
}


function examregistrar_check_exam_within_period($now, $period, $selectdays, $exam) {
    global $DB;
    
    if(!isset($exam->examdate)) {
        $exam->examdate = $DB->get_field('examregistrar_examsessions', 'examdate', 
                                        array('id'=>$exam->examsession, 
                                                'examregid'=>$exam->examregid, 
                                                'period'=>$exam->period));    
    }
    $examdate = usergetmidnight($exam->examdate);

    $examstart = strtotime("-{$selectdays} days ",  $examdate);
    return !(($now < max($period->timestart, $examstart)) OR ($now > $period->timeend));
}


function examregistrar_booking_need_freeze($booking, $now, $lagdays, $nofreeze = false) {
    global $DB;
    
    if($nofreeze) {
        return false;
    }

    $freeze = false;
    $bookedsession = 0;
    // determine booked session and exam date from examsession
    if($booking->numcalls > 1) {
        foreach($booking->exams as $exam) {
            $examdate = usergetmidnight($exam->examdate);
            if($booking->examid && $booking->booked && $booking->examid == $exam->id) {
                $bookedsession = $exam->examsession;
            }
        }
        // test if needing freezing
        if($booking->examid && $booking->booked  && $bookedsession) {
            $examdate = usergetmidnight($DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$bookedsession)));
            $timelimit = strtotime("-$lagdays days ",  $examdate);
            if($now > $timelimit) {
                $freeze = true;
            }
        }
    }    
    return $freeze;
}     


function examregistrar_check_course_passedgrade($courseid, $userid) {
    global $DB;

    $sql = "SELECT gi.courseid
                FROM {grade_items} gi
                LEFT JOIN {grade_grades} gg ON gi.id = gg.itemid AND gg.userid = :userid
                WHERE gi.courseid = :courseid AND gi.itemtype = 'course' AND (gg.finalgrade >= gi.gradepass) ";
    return $DB->get_records_sql($sql, array('courseid'=>$courseid, 'userid'=>$userid));     
    
}


function examregistrar_booking_get_bookable_courses($examregistrar, $course, $bookingparams, 
                                                    $canviewall, $canbookothers, $canmanageexams) {
    global $DB;

    $courses = examregistrar_get_user_courses($examregistrar, $course, $bookingparams, 
                                                array('mod/examregistrar:book', 'mod/examregistrar:bookothers'), 
                                                $canviewall, true);

    $examcourses = array();
    $noexamcourses = array();
    $excludespecials = '';
    if(!$canbookothers) {
        //$excludespecials = ' AND e.callnum > 0 ';
    }
                               
    $examregprimaryid = examregistrar_check_primaryid($examregistrar->id);
    $params = array('examregid'=>$examregprimaryid);
    $onlyvisible = '';
    if(!$canmanageexams) {
        $onlyvisible = ' AND e.visible = 1 ';
        $params['visible'] = 1;
    }

    $period = $bookingparams['period'];
    $annuality =  examregistrar_get_annuality($examregistrar);
    
    foreach($courses as $cid => $usercourse) {
        $usercourse->exams = '';
        $usercourse->noexam = '';
        $params['courseid'] = $cid;
        $sql = "SELECT e.*, s.examsession AS sessionelement, s.examdate, s.duration, s.timeslot,
                            se.name AS scopename, se.idnumber AS scopeidnumber,
                            es.name AS sessionname, es.idnumber AS sessionidnumber
                FROM {examregistrar_exams} e
                JOIN {examregistrar_examsessions} s ON e.examregid = s.examregid AND e.examsession = s.id
                JOIN {examregistrar_elements} se ON e.examregid = se.examregid AND se.type = 'scopeitem' AND e.examscope = se.id
                JOIN {examregistrar_elements} es ON s.examregid = es.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
                WHERE e.examregid = :examregid AND e.courseid = :courseid AND e.period = :period $excludespecials AND e.visible = 1
                ORDER BY s.examdate ASC, se.name ASC";

        if($exams = $DB->get_records_sql($sql, array('examregid'=>$examregprimaryid, 'courseid'=>$cid, 'period'=>$period ))) {
            $usercourse->exams = $exams;
        } elseif(!$DB->record_exists('examregistrar_exams', $params)) {
            $usercourse->noexam = 1;
        } elseif(!$DB->record_exists('examregistrar_exams', $params + array('annuality'=>$annuality))) {
            $usercourse->noexam = 2;
        } elseif(!$DB->record_exists('examregistrar_exams', $params + array('period'=>$period))) {
            $usercourse->noexam = 3;
        }
        
        if($usercourse->exams) {
            $examcourses[$cid] = clone $usercourse;
        }
        if($usercourse->noexam) {
            $noexamcourses[$cid] = clone $usercourse;
        }
    }
    unset($courses);

    return [$examcourses, $noexamcourses];
}

function examregistrar_bookings_in_courses($examcourses, $examregprimaryid, $period, $userid, $canbookothers = false) {
    global $DB;

    $bookings = array();
    foreach($examcourses as $cid => $usercourse) {
        $booking = new \stdClass();
        $examsbyscopes = array();
        // period exams re-ordered by examscope, only one booking by examscope and period even if there are several calls in a period
        foreach($usercourse->exams as $exam) {
            $examsbyscopes[$exam->examscope][$exam->id] = $exam;
        }
        unset($usercourse->exams);
        $booking->course = $usercourse;

        foreach($examsbyscopes as $examscope => $exams) {
            $booking->examperiod = $period;
            $booking->examscope = $examscope;
            $booking->exams = $exams;
            $booking->examid = 0;
            $booking->booked = -1;
            $booking->bookedsite = 0;
            $booking->voucher = '';
            $visible = false;
            // loop al exams in the period/examscope calls. If one exam, set as is,
            // if there are several calls, visibility is set if any one is visible.
            // Must be just one booking (booked=1), if any: last found is kept.
            $booking->numcalls = 0;
            foreach($exams as $exam) {
                if($exam->visible) {
                    $visible = true;
                    $booking->numcalls += 1;
                }
                $userbooking = [];
                $booking->voucher = '';
                if($userbooking = $DB->get_records('examregistrar_bookings', array('userid'=>$userid, 'examid'=>$exam->id), 'booked DESC, timemodified DESC', '*', 0, 1)) {
                    $userbooking = reset($userbooking);
                    $booking->examid = $exam->id;
                    $booking->booked = $userbooking->booked;
                    $booking->bookedsite = $userbooking->bookedsite;
                    $booking->voucher = $DB->get_record('examregistrar_vouchers', array('examregid'=>$examregprimaryid, 'bookingid'=> $userbooking->id));
                }
                if($exam->callnum < 0) {
                    if(!$canbookothers && (empty($userbooking) || empty($booking->booked)) ) {
                        unset($booking->exams[$exam->id]);
                        $visible = false;
                    }
                    if($booking->booked) {
                        // important to preserve correct $booking->voucher, not overwriting  with other
                        break;
                    }
                }
            }
            $booking->visible = $visible;
            // needed for correct display in the form
            $booking->separator = true;
            $booking->displayname = true;
            if($visible || $canmanageexams) {
                $bookings[$cid.'-'.$examscope] = clone $booking;
            }
        }
    }

    return $bookings;
}

function examregistrar_booking_booked_sessions($bookings, $userid) {
    global $DB; 
    
    $sessions = array();
    foreach($bookings as $index => $booking) {
        if($booking->exams) {
            $params = array();
            list($insql, $params) = $DB->get_in_or_equal(array_keys($booking->exams), SQL_PARAMS_NAMED, 'exam');
            $select = " booked = 1 AND userid = :user AND examid $insql ";
            $params['user'] = $userid;
            $booked = false;
            if($booked = $DB->get_record_select('examregistrar_bookings', $select, $params)) {
                $booking->booked = 1;
                $booking->examid = $booked->examid;
                $booking->bookedsite = $booked->bookedsite;
                $session = $DB->get_field('examregistrar_exams', 'examsession', array('id'=>$booked->examid));
                $sessions[$session] = $booked->bookedsite;
            } else {
                $booking->booked = 0;
                $booking->bookedsite = 0;
            }
        }
    }

    return $sessions;
}


function examregistrar_clean_userbookings($bookings, $sessions) {
    global $DB;
    
    $errors = array();
    $sites = array();
    foreach($bookings as $key => $booking) {
        if(!isset($booking['booked']) || !isset($booking['bookedsite']) || !isset($booking['examid'])) {
            unset($bookings[$key]);
        } elseif(!$booking['examid']) {
            unset($bookings[$key]);
            if(isset($booking['booked'])) {
                $booking['error'] = 'noexam';
                $errors[$key] = $booking;
            }
        } elseif(!$booking['bookedsite'] && $booking['booked']) {
                $booking['error'] = 'nosite';
                $errors[$key] = $booking;
                unset($bookings[$key]);
        } else {
            if(!isset($booking['session'])) {
                $booking['session'] = $DB->get_field('examregistrar_exams', 'examsession', array('id'=>$booking['examid']));
                $bookings[$key] = $booking;
            }
            
            if(!isset($sites[$booking['session']])) {
                $sites[$booking['session']] = array();
            }
            $sites[$booking['session']][] = $booking['bookedsite'];
        }
    }            
    
    foreach($sites as $key => $site) {
        $histo = array_count_values($site);
        asort($histo);
        $histo = array_keys($histo);
        $site = array_pop($histo);
        $sites[$key] = $site;
    }

    foreach($bookings as $key => $booking) {            
            if(!isset($sessions[$booking['session']]) && $booking['booked']) {
                $sessions[$booking['session']] = $sites[$booking['session']];   // $booking['bookedsite'];
            } elseif($booking['booked'] && isset($sessions[$booking['session']]) && ($booking['bookedsite'] != $sites[$booking['session']])) {
//                    $prev = $DB->get_field('examregistrar_bookings', 'examid', array('userid'=>$userid, 'booked'=>1, 'bookedsite'=>$sessions[$booking['session']])); 
  //                  if($prev && ($prev != $booking['examid'])) {    
                        $booking['error'] = 'twosites';
                        $errors[$key] = $booking;
                        unset($bookings[$key]);
    //                }
            }
    }
    // now only remain true bookings without errors

    return [$bookings, $errors];
}


function examregistrar_booking_store_new($booking, $userid, $now) {
    global $DB, $USER;            
    
    $newid = 0;
    $record = new \stdClass();
    $record->examid = $booking['examid'];
    $record->userid = $userid;
    $record->booked = $booking['booked'];
    $record->bookedsite = $booking['bookedsite'];
    $record->modifierid = $USER->id;
    $record->timecreated = $now;
    $record->timemodified = $now;
    
    $newid = false;
    if(!(($booking['booked'] == 1) && $DB->record_exists('examregistrar_bookings', 
                    ['examid' => $record->examid, 'userid' => $record->userid, 'booked' => 1]))) {
        // prevents accidental duplication of booked rows, 
        // each user can be booked (1) only once in an examid (and any number, on any bookedsite as 0)
    $newid = $DB->insert_record('examregistrar_bookings', $record);
    }
    if($newid) {
        $record->id = $newid;
    } else {
        $record = false;
    }
    
    return $record;
}


function examregistrar_booking_notify_newbooking($examregid, $context, $downloadurl, $record, $voucher) {
    global $DB, $OUTPUT, $USER; 
    
    // log the action
    $eventdata = array();
    $eventdata['objectid'] = $record->id;
    $eventdata['context'] = $context;
    $eventdata['userid'] = $USER->id;
    $eventdata['relateduserid'] = $record->userid;
    $eventdata['other'] = array();
    $eventdata['other']['examregid'] = $examregid;
    $eventdata['other']['examid'] = $record->examid;
    $eventdata['other']['booked'] = $record->booked;
    $eventdata['other']['bookedsite'] = $record->bookedsite;

    // Booking is already stored in database, this is a clearing
    $select = " userid = :userid AND examid = :examid AND id <> :id AND booked <> 0 ";
    $params = array('id'=>$record->id, 'examid'=>$record->examid, 'userid'=>$record->userid);
    // only clear if needed, avoid extra logging messsge
    if($DB->record_exists_select('examregistrar_bookings', $select, $params)) {
        $DB->set_field_select('examregistrar_bookings', 'timemodified', $record->timemodified, $select, $params);
        if($DB->set_field_select('examregistrar_bookings', 'booked', 0, $select, $params)) {
            $event = \mod_examregistrar\event\booking_unbooked::create($eventdata);
            $event->trigger();
        }
    }
    
    // set the log for active booking after clearing others (store was done before)
    $event = \mod_examregistrar\event\booking_submitted::create($eventdata);
    $event->add_record_snapshot('examregistrar_bookings', $record);
    $event->trigger();
    
    // return the message 
    list($examname, $notused) = examregistrar_get_namecodefromid($record->examid, 'exams');
    $attend = new stdClass();
    $attend->take = core_text::strtoupper($record->booked ?  get_string('yes') :  get_string('no'));
    list($attend->site, $notused) = examregistrar_get_namecodefromid($record->bookedsite, 'locations', 'location');
    $vouchername = '';
    if(isset($voucher->id) && $voucher->id) {
        $icon = new pix_icon('t/download', get_string('voucherdownld', 'examregistrar'), 'core', null); 
        $vouchernum = str_pad($voucher->examregid, 4, '0', STR_PAD_LEFT).'-'.$voucher->uniqueid;
        $downloadurl->param('v', $vouchernum);
        $vouchernum = $OUTPUT->action_link($downloadurl, $vouchernum, null, array('class'=>'voucherdownload'), $icon);
        $vouchername = get_string('vouchernum', 'examregistrar',  $vouchernum);
    
    }
    
    $message = get_string('exam', 'examregistrar').' '.$examname.' '. 
                get_string('takeonsite', 'examregistrar', $attend).' '.$vouchername; 

    return $message;
}

function examregistrar_booking_clean_newbookings($exam, $booking, $newid, $userid, $context) {
    global $DB, $USER;
    
    if($booking['numcalls'] > 1 && $booking['booked']) {
        if($exam) {
            $select = " examregid = :examregid AND annuality = :annuality AND courseid = :courseid
                        AND  period = :period AND examscope = :examscope AND id <> :id ";
            $params = array('examregid'=>$exam->examregid, 'annuality'=>$exam->annuality, 'courseid'=>$exam->courseid,
                            'period'=>$exam->period, 'examscope'=>$exam->examscope, 'id'=>$exam->id);
            if($others = $DB->get_fieldset_select('examregistrar_exams', 'id',  $select, $params)) {
                list($insql, $params) = $DB->get_in_or_equal($others);
                $select = " examid $insql AND userid = ? AND booked <> 0 ";
                $params[] = $userid;
                // only clear if needed, avoid extra logging messsge
                if($DB->record_exists_select('examregistrar_bookings', $select, $params)) {
                    $DB->set_field_select('examregistrar_bookings', 'timemodified', time(), $select, $params);
                    if($DB->set_field_select('examregistrar_bookings', 'booked', 0, $select, $params)) {
                        // log the action
                        $eventdata = array();
                        $eventdata['objectid'] = $newid;
                        $eventdata['context'] = $context;
                        $eventdata['userid'] = $USER->id;
                        $eventdata['relateduserid'] = $userid;
                        $eventdata['other'] = array();
                        $eventdata['other']['examregid'] = $exam->examregid;
                        $eventdata['other']['examid'] = $booking['examid'];
                        $event = \mod_examregistrar\event\booking_unbooked::create($eventdata);
                        $event->trigger();
                    }
                }
            }
        }
    }
}


////////////////////////////////////////////////////////////////////////////////
// Classes
////////////////////////////////////////////////////////////////////////////////

/**
 * class used by user selection controls
 * @package mod_examregistrar
 * @copyright 2012 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 class examregistrar_user_selector extends \user_selector_base {

    /**
     * The id of the examregistrar this selector is being used for
     * @var int
     */
    protected $examregid = null;
    /**
     * The context of the forum this selector is being used for
     * @var object
     */
    protected $context = null;
    /**
     * The id of the current group
     * @var int
     */
    protected $currentgroup = null;

    /**
     * Constructor method
     * @param string $name
     * @param array $options
     */
    public function __construct($name, $options) {
        $options['accesscontext'] = $options['context'];
        parent::__construct($name, $options);
        if (isset($options['context'])) {
            $this->context = $options['context'];
        }
        if (isset($options['currentgroup'])) {
            $this->currentgroup = $options['currentgroup'];
        }
        if (isset($options['examgregid'])) {
            $this->examregid = $options['examgregid'];
        }
    }

    /**
     * Returns an array of options to seralise and store for searches
     *
     * @return array
     */
    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] =  substr(__FILE__, strlen($CFG->dirroot.'/'));
        $options['context'] = $this->context;
        $options['currentgroup'] = $this->currentgroup;
        $options['examgregid'] = $this->examregid;
        return $options;
    }

    /**
     * Finds all potential users
     *
     * Potential users are determined by checking for users with a capability
     * determined in {@see forum_get_potential_subscribers()}
     *
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;

        // only active enrolled users or everybody on the frontpage
        list($esql, $params) = get_enrolled_sql($this->context, 'mod/examregistrar:book', $this->currentgroup, true);
        $userfieldsapi = \core_user\fields::for_name();
        $fields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $sql = "SELECT u.id, u.username, u.idnumber, u.email, $fields
                FROM {user} u
                JOIN ($esql) je ON je.id = u.id
                ORDER BY u.lastname ASC, u.firstname ASC ";

        $availableusers = $DB->get_records_sql($sql, $params);


        //$availableusers = forum_get_potential_subscribers($this->context, $this->currentgroup, $this->required_fields_sql('u'), 'u.firstname ASC, u.lastname ASC');

        if (empty($availableusers)) {
            $availableusers = array();
        } else if ($search) {
            $search = strtolower($search);
            foreach ($availableusers as $key=>$user) {
                if (stripos($user->firstname, $search) === false && stripos($user->lastname, $search) === false && stripos($user->idnumber, $search) === false && stripos($user->username, $search) === false ) {
                    unset($availableusers[$key]);
                }
            }
        }

        if ($search) {
            $groupname = get_string('potentialusersmatching', 'examregistrar', $search);
        } else {
            $groupname = get_string('potentialusers', 'examregistrar');
        }
        return array($groupname => $availableusers);
    }

}
