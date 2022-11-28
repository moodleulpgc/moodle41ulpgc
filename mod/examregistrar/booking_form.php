<?php

/**
 * This file contains form classes & form definitions for Examregistrar booking interface
 *
 * @package   mod_examregistrar
 * @copyright 2014 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');

class examregistrar_booking_form extends moodleform {

    function definition() {
        global $DB, $OUTPUT;

        $mform =& $this->_form;
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);
        $period = $this->_customdata['period'];
        $coursebookings = $this->_customdata['examcourses'];
        $noexamcourses = $this->_customdata['noexamcourses'];
        $params = $this->_customdata['params'];
        $capabilities = $this->_customdata['capabilities'];
        $canbookothers = $capabilities['bookothers'];
        $canmanageexams = $capabilities['manageexams'];
        $config = examregistrar_get_instance_config($exreg);

        $mform->addElement('header', 'examcourses', get_string('examcourses', 'examregistrar'));
        $mform->setExpanded('examcourses', true);

        $venueelement = examregistrar_get_venue_element($examreg);
        $venuemenu = examregistrar_get_referenced_namesmenu($examreg, 'locations', 'locationitem', $exreg, 'choose', '', array('locationtype'=>$venueelement));
        $now = time();
        //$now = strtotime('4 may 2014') + 3605;

        // to check for groups and set separators
        $lastkey = '';
        foreach($coursebookings as $key => $booking) {
            $pieces = explode('-', $key);
            $last = explode('-', $lastkey);
            if($pieces[0] == $last[0]) {
                $book =  $coursebookings[$lastkey];
                $book->separator = false;
                $coursebookings[$lastkey] = clone $book;
                $booking->displayname = false;
                $coursebookings[$key] = clone $booking;
            }
            $lastkey = $key;
        }
/*
        $lagdays = max($examreg->lagdays, $config->cutoffdays);
        $lagdays = ($period->calls > 1) ? $lagdays + $config->extradays : $lagdays;
*/
        $lagdays = examregistrar_set_lagdays($examreg, $config, $period, $capabilities);

        foreach($coursebookings as $index => $booking) {
            $coursename = $booking->course->shortname.' - '. $booking->course->fullname;
            $scope =  examregistrar_get_namecodefromid($booking->examscope);
            $examgroup = array();
            $examsmenu = array();
            $examselect = $mform->createElement('select');
            $examselect->setName('examid');
            $visible = false;
            $freeze = false;
            $examdate = 0;
            $disabled = '';
            //$lagdays = ($booking->numcalls > 1) ? $examreg->lagdays + 2 : $examreg->lagdays;

            // first check if need freeze in multiple calls: booked an exam in the past, any of the set
            // softfreeze means it can be relieved by manageexams capability: to book in available exams in the set scheduled in the future (if any)
            // only applicable to sets of multiple calls. If sibgle call, freezing is controled by the proper examdate
            $softfreeze = examregistrar_booking_need_freeze($booking, $now, $lagdays, 
                                                            ($canbookothers || $canmanageexams));
/*                                                            
            $softfreeze = false;
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
                        $softfreeze = true;
                    }
                }
            }
*/
            $examids = array();
            // $booking->exams is sorted by examdate ASC. Last $exam is later on time
            foreach($booking->exams as $exam) {
                if($exam->visible) {
                    $visible = true;
                }
                //$examdate = usergetmidnight($exam->examdate);
                $disabled = '';
                if(($booking->numcalls > 1) && ( $softfreeze ||
                        examregistrar_check_exam_in_past($now, $lagdays, $exam, $canmanageexams))) {
                    $disabled = array('disabled'=>'disabled');
                }
                $star = '';
                if($exam->callnum < 1) {
                    if(!$canmanageexams && !$DB->record_exists('examregistrar_bookings', array('examid'=>$exam->id, 'userid'=>$params['user']))  ) {
                        // if not own booking as extra, skip
                        continue;
                    }
                    $star = '** ';
                    $freeze = $canmanageexams ? false: true;
                    //$booking->numcalls -= 1;
                }

                if($exam->examdate) {
                    $name = $star.$exam->sessionidnumber.'. '.userdate($exam->examdate, get_string('strftimedaydate')).$star;
                } else {
                    $name = $scope[0];
                }
                $examselect->addOption($name, $exam->id, $disabled);
                $examids[] = $exam->id;
            }

            // $exam is now the last exam in time, guaranteed by sorting of $booking->exams 

            if(examregistrar_check_exam_in_past($now, $lagdays, $exam, $canmanageexams)) {
                // freeze because if last exam date is in the past, cannot be overcome by capabilities: no sense to book in the past
                $freeze = true;
            }
            if(!examregistrar_check_exam_within_period($now, $period, $config->selectdays, $exam)
                && !$canmanageexams) {
                $freeze = true;
            }
            $passmsg = '';
            if($passed = examregistrar_check_course_passedgrade($booking->course->id, $params['user'])) {
                //$freeze = true;
                //$passmsg = get_string('noexam_4', 'examregistrar');
            }

            $voucherlink = '';
            if($booking->voucher) {
                $icon = new pix_icon('t/download', get_string('voucherdownld', 'examregistrar'), 'core', null); 
                $vouchernum = str_pad($booking->voucher->examregid, 4, '0', STR_PAD_LEFT).'-'.$booking->voucher->uniqueid;
                $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cmid, 'down'=>'voucher', 'v'=>$vouchernum));
                //$vouchernum = $OUTPUT->action_link($downloadurl, $vouchernum, null, null, $icon);
                $voucherlink = get_string('vouchernum', 'examregistrar',  $OUTPUT->action_link($downloadurl, $vouchernum, null, array('class'=>'voucherdownload'), $icon));
            }
            
            $examgroup[] = $mform->createElement('static', '', '',$scope[0]);
            $examgroup[] = $examselect;
            $examgroup[] = $mform->createElement('static', '', '', get_string('take', 'examregistrar'));
            $examgroup[] = $mform->createElement('selectyesno', 'booked', $booking->booked, 1);
            $examgroup[] = $mform->createElement('static', '', '',' &nbsp '.get_string('takeat', 'examregistrar').' &nbsp ');
            $examgroup[] = $mform->createElement('select', 'bookedsite', $booking->bookedsite, $venuemenu);
            $examgroup[] = $mform->createElement('static', '', '', $voucherlink);
            $examgroup[] = $mform->createElement('hidden', 'numcalls', $booking->numcalls);
            $examgroup[] = $mform->createElement('hidden', 'shortname', $booking->course->shortname);

            $label = ($booking->displayname) ? $coursename : '&nbsp;  &nbsp;' ;
            $mform->addGroup($examgroup, "booking[$index]", $label, array(' ', '<br />', ' ', ' '), true);

            $mform->setDefault("booking[$index][bookedsite]", $booking->bookedsite);
            if($booking->booked != -1) {
                $mform->setDefault("booking[$index][booked]", $booking->booked);
            } else {
                $mform->setDefault("booking[$index][booked]", 1);
            }

            $defaultexamid = $examids[0];
            if(in_array($booking->examid, $examids)) {
                $defaultexamid = $booking->examid;
            }
            $mform->setDefault("booking[$index][examid]", $defaultexamid);

            $mform->setType("booking[$index][numcalls]", PARAM_INT);
            $mform->setType("booking[$index][shortname]", PARAM_INT);

            $mform->disabledIf("booking[$index][booked]", "booking[$index][bookedsite]", 'eq', '');
            if($freeze || $softfreeze) {
                $mform->disabledIf("booking[$index][booked]", "booking[$index][numcalls]", 'neq', 0);
                $mform->disabledIf("booking[$index][bookedsite]", "booking[$index][numcalls]", 'neq', 0);
            }

            $separator = $passmsg;
            $separator .= $booking->separator ? '<br /><hr />' : '<br />';
            $mform->addElement('html', $separator);
        }

        $mform->addElement('header', 'noexamcourses', get_string('noexamcourses', 'examregistrar'));
        $expanded = $coursebookings ? false : true;
        $mform->setExpanded('noexamcourses', $expanded);

        foreach($noexamcourses as $cid => $usercourse) {
            $name = $usercourse->shortname.' - '. $usercourse->fullname;
            $mform->addElement('static', 'noexam_'.$cid, $name, get_string('noexam_'.$usercourse->noexam, 'examregistrar'));
        }

        foreach($params as $key => $value ) {
            $mform->addElement('hidden', $key, $value);
            $mform->setType($key, PARAM_RAW);
        }

        $mform->addElement('hidden', 'setbooking', 'process');
        $mform->setType('setbooking', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'tab', 'booking');
        $mform->setType('tab', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges'));
    }

}

