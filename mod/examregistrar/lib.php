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
 * Library of interface functions and constants for module examregistrar
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle are placed here.
 * It delegates examregistrar specific functions, needed to implement all the module
 * logic, to locallib.php.
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/// CONSTANTS ///////////////////////////////////////////////////////////

define('EXAMREGISTRAR_MODE_VIEW', 1);
define('EXAMREGISTRAR_MODE_BOOK', 3);
define('EXAMREGISTRAR_MODE_PRINT', 5);
define('EXAMREGISTRAR_MODE_REVIEW', 7);
define('EXAMREGISTRAR_MODE_REGISTRAR', 9);

// examfile statusses
define('EXAM_STATUS_CREATED', 0);
define('EXAM_STATUS_SENT', 3);
define('EXAM_STATUS_WAITING', 5);
define('EXAM_STATUS_REJECTED', 7);
define('EXAM_STATUS_APPROVED', 9);
define('EXAM_STATUS_VALIDATED', 10);

// responses statusses
define('EXAM_RESPONSES_UNSENT', 0);
define('EXAM_RESPONSES_SENT', 3);
define('EXAM_RESPONSES_ADDING', 4);
define('EXAM_RESPONSES_WAITING', 5);
define('EXAM_RESPONSES_REJECTED', 7);
define('EXAM_RESPONSES_COMPLETED', 9);
define('EXAM_RESPONSES_VALIDATED', 10);

define('EXAMREGISTRAR_PRINTMODE_DOUBLE', 0);
define('EXAMREGISTRAR_PRINTMODE_SINGLE', 1);

global $EXAMREGISTRAR_ELEMENTTYPES;
$EXAMREGISTRAR_ELEMENTTYPES = array('annualityitem', 'perioditem', 'periodtypeitem', 'examsessionitem', 'scopeitem', 'termitem', 'locationitem', 'locationtypeitem', 'roleitem');

/**
 * Returns and array menu (id,name) for allowed exam status values
 *
 * @param array $replaces  an associative array of key (replace codes) / values (actual data)
 * @param string/array $subject where substitutions are performed, may be a string or an array of strings
 * @return string/array depends on subject type
 */
function examregistrar_examstatus_getmenu() {

    $states = array(EXAM_STATUS_CREATED => get_string('status_created','examregistrar'),
                    EXAM_STATUS_SENT => get_string('status_sent','examregistrar'),
                    EXAM_STATUS_WAITING => get_string('status_waiting','examregistrar'),
                    EXAM_STATUS_REJECTED => get_string('status_rejected','examregistrar'),
                    EXAM_STATUS_APPROVED => get_string('status_approved','examregistrar'),
                    EXAM_STATUS_VALIDATED => get_string('status_validated','examregistrar'),
                    );
    return $states;
}



////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function examregistrar_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
        case FEATURE_BACKUP_MOODLE2:    return true;
        case FEATURE_GRADE_HAS_GRADE:   return false;
        case FEATURE_GRADE_OUTCOMES:    return false;
        default:                        return null;
    }
}

/**
 * Saves a new instance of the examregistrar into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $examregistrar An object from the form in mod_form.php
 * @param mod_examregistrar_mod_form $mform
 * @return int The id of the newly inserted examregistrar record
 */
function examregistrar_add_instance(stdClass $examregistrar, mod_examregistrar_mod_form $mform = null) {
    global $DB;

    $examregistrar->timecreated = time();
    $examregistrar->timemodified = $examregistrar->timecreated;

    $examregid = $DB->insert_record('examregistrar', $examregistrar);
    return $examregid;
}

/**
 * Updates an instance of the examregistrar in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $examregistrar An object from the form in mod_form.php
 * @param mod_examregistrar_mod_form $mform
 * @return boolean Success/Fail
 */
function examregistrar_update_instance(stdClass $examregistrar, mod_examregistrar_mod_form $mform = null) {
    global $DB;

    $examregistrar->timemodified = time();
    $examregistrar->id = $examregistrar->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('examregistrar', $examregistrar);
}

/**
 * Removes an instance of the examregistrar from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function examregistrar_delete_instance($id) {
    global $DB;

    if (! $examregistrar = $DB->get_record('examregistrar', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    // exmasfiles
    //exams
    // bookings
    // seatings

    //sessionrooms
    // sessionseats
    // staffers

    $DB->delete_records('examregistrar_locations', array('examregid' => $examregistrar->id));

    $DB->delete_records('examregistrar_examsessions', array('examregid' => $examregistrar->id));

    $DB->delete_records('examregistrar_periods', array('examregid' => $examregistrar->id));

    $DB->delete_records('examregistrar_exams', array('examregid' => $examregistrar->id));

    $DB->delete_records('examregistrar_elements', array('examregid' => $examregistrar->id));

    # Delete the module instance #

    $DB->delete_records('examregistrar', array('id' => $examregistrar->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function examregistrar_user_outline($course, $user, $mod, $examregistrar) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $examregistrar the module instance record
 * @return void, is supposed to echp directly
 */
function examregistrar_user_complete($course, $user, $mod, $examregistrar) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in examregistrar activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function examregistrar_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link examregistrar_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function examregistrar_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see examregistrar_get_recent_mod_activity()}

 * @return void
 */
function examregistrar_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 * @return array
 */
function examregistrar_get_view_actions() {
    return array('view exams', 'view exams selection', 'download exam');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 * @return array
 */
function examregistrar_get_post_actions() {
    return array('send exam', 'upload', 'review', 'resolve', 'select', 'add element', 'delete element', 'update element');
}


/**
 * Returns instanceid of primary registrar
 *
 * @param int $examregid ID if an instance
 * @return int
 */
function examregistrar_check_primaryid($examregid) {
    global $DB, $PAGE;

    //check if this is a primary instance
    $sql = "SELECT e1.id, e1.primaryidnumber, e1.primaryreg, e2.id AS pid
              FROM {examregistrar} e1  
         LEFT JOIN {examregistrar} e2 ON e2.primaryidnumber = e1.primaryreg AND e2.primaryidnumber != '' 
             WHERE e1.id = ? 
              ";
    $primaryrec = $DB->get_record_sql($sql, [$examregid], MUST_EXIST);
    
    if(empty($primaryrec)) {
        $link = new moodle_url('/course/view.php', array('id'=>$PAGE->course->id));
        print_error('errornoprimary', 'examregistrar', $link);
    }
    
    $primaryid = ($primaryrec->primaryidnumber) ? $primaryrec->id :  $primaryrec->pid;
    return $primaryid;
}

/**
 * Returns instanceid of primary registrar
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_primaryid($examregistrar) {
    global $DB;

    $exregid = false;
    if(!$examregistrar) {
        $exregid = false;
    } elseif($examregistrar->primaryreg) {
        if($exreg = $DB->get_record('examregistrar', array('primaryidnumber'=>$examregistrar->primaryreg))) {
            $exregid = $exreg->id;
        } else {
            $link = new moodle_url('/course/view.php', array('id'=>$examregistrar->course));
            print_error('errornoprimary', 'examregistrar', $link);
        }
    } elseif($examregistrar->primaryidnumber) {
        $exregid = $examregistrar->id;
        $exreg = $examregistrar;
    }
    
    return $exregid;
}


/**
 * Returns instanceid of primary registrar
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_primaryidnumber($examregistrar) {
    global $DB;

    $exregid = false;
    if($examregistrar->primaryreg) {
        if($exreg = $DB->get_record('examregistrar', array('primaryidnumber'=>$examregistrar->primaryreg))) {
            $exregid = $examregistrar->primaryreg;
        } else {
            $link = new moodle_url('/course/view.php', array('id'=>$examregistrar->course));
            print_error('errornoprimary', 'examregistrar', $link);
        }
    } elseif($examregistrar->primaryidnumber) {
        $exregid = $examregistrar->primaryidnumber;
    }
    return $exregid;
}


/**
 * Returns annuality element to use, if defined
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_annuality($examregistrar) {
    global $DB;

    $exregid = examregistrar_get_primaryid($examregistrar);
    $annuality = '';
    if($examregistrar && $examregistrar->annuality) {
        $annuality = $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$exregid, 'idnumber'=>$examregistrar->annuality));
    }
    return $annuality;
}


/**
 * Returns first currently active exam period, based on today and period start/end dates
 *
 * @param object $examregistrar object
 * @param int $now timestamp
 * @return int
 */
function examregistrar_get_period($examregistrar, $now = 0) {
    global $DB;

    if(!$now) {
        $now = time();
    }

    $period = '';
    $exregid = examregistrar_get_primaryid($examregistrar);
    $select  = " examregid = :examregid AND timestart <= :now1 AND timeend >= :now2 ";
    if($periods = $DB->get_records_select('examregistrar_periods', $select, array('examregid'=>$exregid, 'now1'=>$now, 'now2'=>$now))) {
        $period = reset($periods);
        $period = $period->id;
    }
    return $period;
}


/**
 * Returns currently active exam periods, based on today and period start/end dates
 *
 * @param object $examregistrar object
 * @param int $now timestamp
 * @return int
 */
function examregistrar_current_periods($examregistrar, $now = 0) {
    global $DB;

    if(!$now) {
        $now = time();
    }

    $annuality = examregistrar_get_annuality($examregistrar);

    $exregid = examregistrar_get_primaryid($examregistrar);
    $select = " examregid = :examregid AND timestart <= :now1 AND timeend >= :now2 AND visible = 1 ";
    $params = array('examregid'=>$exregid, 'now1'=>$now, 'now2'=>$now);
    if($annuality) {
        $select .= " AND annuality = :annuality ";
        $params['annuality'] = $annuality;
    }

    $periods = $DB->get_records_select('examregistrar_periods', $select, $params);

    if(!$periods) {
        $sql = "SELECT p.*, ABS(timestart - $now) AS timediff
                FROM {examregistrar_periods} p
                WHERE examregid = :examregid  AND visible = 1 ";
        $params = array('examregid'=>$exregid);
        if($annuality) {
            $sql .= " AND annuality = :annuality ";
            $params['annuality'] = $annuality;
        }
        $sql .= " ORDER BY timediff ASC ";
        $periods = $DB->get_records_sql($sql, $params);
    }

    return $periods;
}


/**
 * Returns next session scheduled
 *
 * @param object $examregistrar object
 * @param int $now timestamp
 * @param bool $object if returning object or id;
 * @return int/object
 */
function examregistrar_next_sessionid($examregistrar, $now = 0, $object = false, $period = false) {
    global $DB;

    $return = 0;
    if(!$now) {
        $now = time();
    }
    $now = usergetmidnight($now) - 60 ; // keep all working day in session day
    $sessionid = 0;
    $exregid = examregistrar_get_primaryid($examregistrar);

    $select = " examregid = :examregid AND examdate >= :now AND visible = 1 ";
    $params = array('examregid'=>$exregid, 'now'=>$now);
    if($period) {
        $select .= " AND period = :period ";
        $params['period'] = $period;
    }
    if(!$sessions = $DB->get_records_select('examregistrar_examsessions', $select, $params, 'examdate ASC')) {
        $params = array('examregid'=>$exregid);
        if($period) {
            $params['period'] = $period;
        }
        $sessions = $DB->get_records('examregistrar_examsessions', $params, 'examdate DESC');
    }
    if($sessions) {
        $session = reset($sessions);
        if($object) {
            $return = $session;
        } else {
            $return = $session->id;
        }
    }

    return $return;
}

/**
 * Checks if period is an EXTRA exam period
 *
 * @param object $examregistrar object
 * @param object $period object
 * @return bool
 */
function examregistrar_is_extra_period($examregistrar, $period) {
    global $DB;

    $type = $DB->get_record('examregistrar_elements', array('id'=>$period->periodtype), '*', MUST_EXIST);

    $inname = strpos('extra', core_text::strtolower($type->name));
    $inidnumber = strpos('ext', core_text::strtolower($type->idnumber));
    $hasord = strpos('ord', core_text::strtolower($type->idnumber));

    $extra = false;
    if(($inname !== false || $inidnumber !== false) && $hasord === false) {
        $extra = true;
    }

    return $extra;
}



//////////////////////////////////////////////////////////////////////////////////
//   Exams submitting &a reviewing functions                                   //
////////////////////////////////////////////////////////////////////////////////


/**
 * Generates the exam idnumber identifier from course idnumber and exam period
 *
 * @param object $exam and exam record from examregistrar_exams
 * @param string $source initial name, tipically a course idnumber
 * @return string examfile idnumber string
 */
function examregistrar_examfile_idnumber($exam, $source) {

    $pieces = explode('_', $source);
    $examidnumber = $pieces[0].'-'.$pieces[5];
    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
    $examidnumber .= '-'. $idnumber;
    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
    $callnum = $exam->callnum > 0 ? $exam->callnum : 'R'.abs($exam->callnum);
    $examidnumber .= '-'. $idnumber.'-'.$callnum;

    return $examidnumber;
}


/**
 * Returns the display name and idnumber of an item as stored in elements table
 *
 * @param object $item the record item from an examregisrar table
 * @param string $field the name of the field that stored the element ID
 * @return array (name, idnumber)
 */
function examregistrar_item_getelement($item, $field='element') {
    global $DB;

    if(!$item) {
        return array('', '');
    }

    if($field == 'stafferitem') {
        $user = $DB->get_record('user', array('id'=>$item->userid), 'id, firstname, lastname, idnumber');
            $element = new stdClass();
            $element->name = fullname($user);
            $element->idnumber = $user->idnumber;
    } else {
        if(!$field || $field == 'element' ) {
            $eid = $item->id;
        } else {
            $eid = $item->$field;
        }
        if(!$element = $DB->get_record('examregistrar_elements', array('id'=>$eid))) {
            $element = new stdClass();
            $element->name = '';
            $element->idnumber = '';
        }
    }

    return array($element->name, $element->idnumber);
}


/**
 * Returns a menu of elements by type
 *
 * @param int $itemid the ID if the item in the table
 * @param string $table table where this ID is located
 * @param string $field of element type
 * @return array element name, idnumber
 */
function examregistrar_get_namecodefromid($itemid, $table = '', $field = '') {
    global $DB;

    if($table === '') {
        if(!$element = $DB->get_record('examregistrar_elements', array('id'=>$itemid), 'name,idnumber')) {
            $element = new stdClass();
            $element->name = '';
            $element->idnumber = '';
        }

        return array($element->name, $element->idnumber);
    }

    if(!$field) {
        $field = substr($table, 0, -1);
    }
    $item = $DB->get_record('examregistrar_'.$table, array('id' => $itemid));

    if($table == 'exams') {
        $period = new stdClass;
        list($period->name,  $period->idnumber) = examregistrar_get_namecodefromid($item->period, 'periods', 'period');
        $scope = $DB->get_record('examregistrar_elements', array('id'=>$item->examscope), 'name,idnumber');
        $name = $item->programme.'_'.$DB->get_field('course', 'shortname', array('id'=>$item->courseid)).
                '_'.$period->idnumber.'_'.$scope->idnumber.'_'.$item->callnum;
        $idnumber = '';
        return array($name, $idnumber);
    }

    return examregistrar_item_getelement($item, $field);
}


function examregistrar_file_set_nameextension($examregistrar, $filename, $type, $ext='.pdf') {

    $filename = trim($filename);
    $ext = trim($ext);
    if(strpos($ext, '.') === false) {
        $ext = '.'.$ext;
    }

    $config = examregistrar_get_instance_config($examregistrar->id, 'extanswers, extkey, extresponses'); //config***

    $qualifier = '';
    if($type == 'answers') {
        $qualifier = $config->extanswers;
    } elseif($type == 'key') {
        $qualifier = $config->extkey;
    } elseif($type == 'responses') {
        $qualifier = $config->extresponses;
    }
    if($qualifier) {
        $qualifier = trim($qualifier);
    }

    return clean_filename($filename.$qualifier.$ext);
}


/**
 * Locates the Tracker issue associated to an examregistrar instance
 *
 * @param object $examregistrar the examregistrar object
 * @param object $course the course object containing teh examregistrar instance
 * @return object tracker record
 **/
function examregistrar_get_review_tracker($examregistrar, $course) {
    global $DB;

    if(!$moduleid = $DB->get_field('modules', 'id', array('name'=>'tracker'))) {
        return false;
    }

    $params = array('course'=>$course->id, 'module'=>$moduleid, 'idnumber'=>$examregistrar->reviewmod);
    if(!$cms = $DB->get_records('course_modules', $params)) {
        $sql = "SELECT cm.*, c.category
                FROM {course_modules} cm
                JOIN {course} c  ON c.id = cm.course
                WHERE cm.module = :module AND cm.idnumber = :idnumber AND c.category = :category ";
        $params['category'] = $course->category;
        $cms = $DB->get_records_sql($sql, $params);
    }
    $cm = 0;
    if($cms) {
        $cm = reset($cms);
    }

    if(!$cm) {
        mtrace("... missing review Tracker instance for examregistrar {$examregistrar->name} ({$examregistrar->id})");
        return false;
    }
    /// OK, now we have the cm of a tracker instance
    return $DB->get_record('tracker', array('id' => $cm->instance));
}

/**
 * Locates the Tracker issue associated to an examregistrar instance
 *  Returns de issueid of the issue creted for an exam file
 *
 * @param object $examregistrar the examregistrar object
 * @param object $examregistrar the examregistrar object
 * @param object $examregistrar the examregistrar object
 * @return int tracker issue ID
 */
function examregistrar_review_addissue($examregistrar, $course, $examfile, $tracker = false) {
    global $CFG, $DB, $OUTPUT;

    $issueid = 0;

    if(!$examregistrar->reviewmod) {
        return 0;
    }

    if(!$tracker) {
        $tracker = examregistrar_get_review_tracker($examregistrar, $course);
    }

    if(!$tracker) {
        return -1;
    }

    $exam = $DB->get_record('examregistrar_exams', array('id'=>$examfile->examid), '*', MUST_EXIST);
    $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);

    $examcoursename = $examcourse->shortname.' - '.format_string($examcourse->fullname);
    $summary = $examcoursename." \n".$examfile->idnumber.'  ('.$examfile->attempt.')' ;

    $items = array();
    $items[] = get_string('attemptn', 'examregistrar', $examfile->attempt);

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->annuality);
    $items[] = get_string('annualityitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    $items[] = get_string('programme', 'examregistrar').': '.$exam->programme;

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
    $items[] = get_string('perioditem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
    $items[] = get_string('scopeitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
    $items[] = get_string('examsessionitem', 'examregistrar').': '.$name.' ('.$idnumber.')';


    $examcontext = context_course::instance($examcourse->id);
    $filename = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'exam'); //$examfile->idnumber.'.pdf';
    $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$examcontext->id.'/mod_examregistrar/exam/rev/'.$tracker->course.'/'.$examfile->id.'/'.$filename);
    $mime = mimeinfo("icon", $filename);
    $icon = new pix_icon(file_extension_icon($filename), $mime, 'moodle', array('class'=>'icon'));
    $filelink = $OUTPUT->action_link($url, $filename, null, null, $icon); //   html_writer::link($ffurl, " $icon &nbsp; $filename ");
    $filelink .= '<br />';

    $filename = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'answers');//$examfile->idnumber.'_resp.pdf';
    $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$examcontext->id.'/mod_examregistrar/exam/rev/'.$tracker->course.'/'.$examfile->id.'/answers/'.$filename);
    $mime = mimeinfo("icon", $filename);
    $icon = new pix_icon(file_extension_icon($filename), $mime, 'moodle', array('class'=>'icon'));
    $filelink .= $OUTPUT->action_link($url, $filename, null, null, $icon); //   html_writer::link($ffurl, " $icon &nbsp; $filename ");

    if(isset($exam->quizplugincm) && $exam->quizplugincm) {
        $strexamfile = get_string('examfile', 'examregistrar');
        $examobj = new examregistrar_exam($exam);
        if($mkattempt = $examobj->get_makeexam_attempt($examfile->id, true)) {
            $filelink .= '<br />';
            $attemptname = $mkattempt->name .' ('.userdate($mkattempt->timecreated, get_string('strftimerecent')).') ';
            $url = new moodle_url('/mod/quiz/report.php', array('id' => $mkattempt->cm, 'mode' => 'makeexam', 'review' => $mkattempt->review, 'confirm' => 1));
            $icon = new pix_icon('icon', $strexamfile, 'quiz', array('class'=>'icon', 'title'=>$strexamfile));
            $filelink .= $OUTPUT->action_link($url,$attemptname, null, null, $icon);
        }
    }

    $description = html_writer::tag('h3', $examcoursename).html_writer::div($filelink, ' examreviewissuefilelink ').html_writer::div(implode('<br />', $items), ' examreviewissuebody ' );

    /// TODO use function tracker_submitanissue(&$tracker, &$data) TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO use function tracker_submitanissue(&$tracker, &$data) TODO

    $issue = new StdClass;
    $issue->datereported = time();
    $issue->summary = $summary;
    $issue->description = $description;
    $issue->descriptionformat = FORMAT_HTML;
    $issue->format = 1;
    $issue->assignedto = $tracker->defaultassignee;
    $issue->bywhomid = 0;
    $issue->trackerid = $tracker->id;
    $issue->status = 0;
    $issue->reportedby = $examfile->userid;
    $issue->usermodified = $issue->datereported;
    $issue->resolvermodified = $issue->datereported;
    $issue->userlastseen = 0;

    $issueid = $DB->insert_record('tracker_issue', $issue);
    if($issueid > 0) {
        if($DB->set_field('examregistrar_examfiles', 'reviewid', $issueid, array('id'=>$examfile->id))) {
            $eventdata = array();
            $eventdata['objectid'] = $examfile->id;
            list($course, $cm) = get_course_and_cm_from_instance($examregistrar, 'examregistrar', $examregistrar->course);
            $eventdata['context'] = context_module::instance($cm->id);
            $eventdata['other'] = array();
            $eventdata['other']['attempt'] = $examfile->attempt;
            $eventdata['other']['examid'] = $examfile->examid;
            $eventdata['other']['issueid'] = $issueid;
            $eventdata['other']['idnumber'] = $examfile->idnumber;
            $eventdata['other']['examregid'] = $examregistrar->id;
            $event = \mod_examregistrar\event\examfile_synced::create($eventdata);
            $event->trigger();
        }
    }
    return (int)$issueid;
}


/**
 * Retrieves the instance config settings stored in plugin_config table
 *
 * @param int $examregid the ID if the examregistrar instance
 * @param mixed $fields either a string, name of a setting,
 *               comma-separated list or an array of setting names to retrieve
 * @param string $prefix a prefix identifying config storable keys in object
 * @return mixed, object config data object or value if single field
 */
function examregistrar_get_instance_config($examregid, $fields = false, $prefix = '') {
    global $DB;

    //check if this is a primary instance
    $examregprimaryid = examregistrar_check_primaryid($examregid);

    $select = 'examregid = :examregid AND plugin = :plugin AND subtype = :subtype ';
    $params = ['examregid' => $examregprimaryid,
                'plugin' => '',
                'subtype' => 'examregistrar'];
    if(is_string($fields)) {
        $fields = array_map('trim', explode(',', $fields));

    }
    if(is_array($fields) && !empty($fields)) {
        list($insql, $inparams) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED, 'name');
        $select .= " AND name $insql ";
        $params = $params + $inparams;
    }

    $config = false;
    if($config = $DB->get_records_select_menu('examregistrar_plugin_config', $select, $params, '', 'name, value')) {
        if(isset($config['staffcats'])) {
            $config['staffcats'] = explode(',', $config['staffcats']);
        }
        if(count($config) == 1) {
            $config = reset($config);
        } else {
            if($prefix) {
                //used prefix to qualify keys for an user input form
                foreach($config as $key => $value) {
                    $config[$prefix.$key] = $value;
                    unset($config[$key]);
                }
            }
            $config = (object)$config;
        }
    }

    if(empty($config)) {
        $config = '';
    }

    return $config;
}

/**
 * Stores the instance config settings n plugin_config table
 *
 * @param int $examregid the ID if the examregistrar instance
 * @param object $config containing the key, value pairs, optionally with prefix in key
 * @param mixed $fields either a string, name of a setting,
 *               comma-separated list or an array of setting names to save
                false means all fields
 * @param string $prefix a prefix identifying storable keys in input form
 * @return mixed, object config data object or value if single field
 */
function examregistrar_save_instance_config($examregid, $config, $fields = false, $prefix = '') {
    global $DB;

    //check if this is a primary instance
    $examregprimaryid = examregistrar_check_primaryid($examregid);

    $select = 'examregid = :examregid AND plugin = :plugin AND subtype = :subtype ';
    $params = ['examregid' => $examregprimaryid,
                'plugin' => '',
                'subtype' => 'examregistrar'];
    if(is_string($fields)) {
        $fields = array_map('trim', explode(',', $fields));

    }
    if(is_array($fields) && !empty(fields)) {
        list($insql, $inparams) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED, 'name');
        $select .= " AND name $insql ";
        $params = $params + $inparams;
    }

    $stored = $DB->get_records_select_menu('examregistrar_plugin_config', $select, $params, '', 'name, id');

    $all = empty($fields);
    $record = new stdClass();
    $record->examregid = $examregprimaryid;
    $record->plugin = '';
    $record->subtype = 'examregistrar';
    foreach($config as $name => $value) {
        if($prefix) {
            // use prefix when data comes from a form, input config form
            if(strpos($name, $prefix) === 0) {
                $name = str_replace($prefix, '', $name);
            } else {
                continue;
            }
        }
        if(is_array($value)) {
            $value = implode(',', $value);
        }
        if($all || in_array($name, $fields)) {
            if(isset($stored[$name])) {
                // existing value, update
                $DB->set_field('examregistrar_plugin_config', 'value', $value, ['id' => $stored[$name]]);
            } else {
                //not existing,  insert new value
                $record->name = $name;
                $record->value = $value;
                $DB->insert_record('examregistrar_plugin_config', $record);
            }
        }
    }
}

































/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function examregistrar_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of examregistrar?
 *
 * This function returns if a scale is being used by one examregistrar
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $examregistrarid ID of an instance of this module
 * @return bool true if the scale is used by the given examregistrar instance
 */
function examregistrar_scale_used($examregistrarid, $scaleid) {
    global $DB;

     return false;
}

/**
 * Checks if scale is being used by any instance of examregistrar.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any examregistrar instance
 */
function examregistrar_scale_used_anywhere($scaleid) {
    global $DB;

    return false;

}

/**
 * Creates or updates grade item for the give examregistrar instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $examregistrar instance object with extra cmidnumber and modname property
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void
 */
function examregistrar_grade_item_update(stdClass $examregistrar, $grades=NULL) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($examregistrar->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = 0;
    $item['grademin']  = 0;

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    grade_update('mod/examregistrar', $examregistrar->course, 'mod', 'examregistrar', $examregistrar->id, 0, $grades, $item);
}

/**
 * Update examregistrar grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $examregistrar instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function examregistrar_update_grades(stdClass $examregistrar, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/examregistrar', $examregistrar->course, 'mod', 'examregistrar', $examregistrar->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////







/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function examregistrar_get_file_areas($course, $cm, $context) {
    return array('exam'=>get_string('areaexamfile', 'examregistrar'),
                 'responses'=>get_string('areaexamresponses', 'examregistrar'),
                 'examresponses'=>get_string('areaexamresponsestemp', 'examregistrar'),
                 'sessionresponses'=>get_string('areasesionresponses', 'examregistrar'),
                 'settings'=>get_string('areasettings', 'examregistrar'));
}

/**
 * File browsing support for examregistrar file areas
 *
 * @package mod_examregistrar
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function examregistrar_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_COURSE) {
        return null;
    }

    // filearea must contain a real area
    if (!isset($areas[$filearea])) {
        return null;
    }

    // Check access
    if (!has_capability('mod/examregistrar:download', $context)) {
        return null;
    }

    if (is_null($itemid)) {
        return null;
    }

    if (!$content = $DB->get_record('examregistrar_examfiles', array('id'=>$itemid))) {
        return null;
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!($storedfile = $fs->get_file($context->id, 'mod_examregistrar', $filearea, $itemid, $filepath, $filename))) {
        return null;
    }

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $itemid, true, true, false, false);
}


/**
 * Serves the files from the examregistrar file areas
 *
 * @package mod_examregistrar
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the examregistrar's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */

function mod_examregistrar_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload) {
    global $DB, $CFG;

    $fileareas = array('exam', 'responses', 'answers', 'sheet', 'sessionrooms', 'sessionresponses', 'session', 'examresponses', 'roomresponses');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    if ($filearea == 'exam' && $context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    $fs = get_file_storage();
    
    if($filearea == 'sheet') {
        $cmid = array_shift($args);
        $context = context_module::instance($cmid);
        $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
        if (count($files) < 1) {
            resource_print_filenotfound($resource, $cm, $course);
            die;
        } else {
            $file = reset($files);
            $fullpath = "/{$context->id}/mod_resource/content/0".$file->get_filepath().$file->get_filename();
            unset($files);
            unset($file);
        }
    } else {
        $rev = reset($args);
        if($rev == 'rev') {
            // we need to chek in rev module
            $rev = array_shift($args);
            $revid = (int)array_shift($args);
            require_login($revid, false, $cm, false, true);
            $checkcontext = context_course::instance($revid);
        } else {
            require_login($course, false, $cm, false, true);
            $checkcontext = $context;
        }

        $candownload = has_capability('mod/examregistrar:download', $checkcontext);

        if(!$candownload) {
            // check if board member (old style) REMOVE when all in new style
            $idnumber = 'jeval-'.strstr($course->idnumber, '_', true).'_111';
            if($boardid = $DB->get_field('course', 'id', array('category'=>$course->category, 'idnumber'=>$idnumber))) {
                require_login($boardid, false, $cm, false, true);
                $boardcontext = context_course::instance($boardid);
                $candownload = has_capability('mod/examregistrar:download',$boardcontext);
                if($boardcontext && !$candownload) {
                    return false;
                }
            } else {
                return false;
            }
        }

        if(!$candownload) {
            return false;
        }

        $itemid = (int)array_shift($args);
        if (($filearea) == 'exam' && !$examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$itemid))) {
            return false;
        }


        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_examregistrar/$filearea/$itemid/$relativepath";
    }

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // now we can set the event
    $eventdata = array();
    $eventdata['context'] = $context;
    $eventdata['other'] = array();
    $eventdata['other']['name'] = $fullpath;    
    $event = \mod_examregistrar\event\files_downloaded::create($eventdata);
    $event->trigger();
    
    // Download MUST be forced - security!
    send_stored_file($file, 0, 0, true);
}


    function examregistrar_file_decode_type($type) {
    
        // a hack to avoid changing other references
        $path = '';
        if(is_array($type)) {
            $type = $type[0];
            $path = $type[1];
        }
    
        switch($type) {
            case 'exam'     :   $area = 'exam';
                                $path = '/';
                                break;
            case 'answers'  :   $area = 'exam';
                                $path = '/answers/';
                                break;
            case 'key'      :   $area = 'exam';
                                $path = '/key/';
                                break;
            case 'responses':   $area = 'responses';
                                $path = '/';
                                break;
            case 'session'  :   $area = 'session';
                                $path = '/';
                                break;
            case 'sessionrooms':$area = 'sessionrooms';
                                $path = '/';
                                break;
            case 'sessionresponses':
                                $area = 'sessionresponses';
                                $path = '/';
                                break;
            case 'examresponses':
                                $area = 'examresponses';
                                $path = '/';
                                break;
            case 'roomresponses':
                                $area = 'roomresponses';
                                $path = '/';
                                break;

        }
        return array($area, $path);
    }

    function examregistrar_file_get_filename($contextid, $itemid, $type, $multiple=false) {

        list($area, $path) = examregistrar_file_decode_type($type);

        $filename = '';
        $fs = get_file_storage();
        if($files = $fs->get_directory_files($contextid, 'mod_examregistrar', $area, $itemid, $path, false, false, "filepath, filename")) {
            if(!$multiple) {
                $file = reset($files);
                $filename = $file->get_filename();
            } else {
                $filename = array();
                foreach($files as $file) {
                    $filename[] = $file->get_filename();
                }
            }
        }

        return $filename;
    }

    function examregistrar_file_get_file($contextid, $itemid, $type, $search=false) {

        list($area, $path) = examregistrar_file_decode_type($type);

        $file = '';
        $fs = get_file_storage();

        // if search string, use as filename
        if(is_string($search)) {
            return $fs->get_file($contextid, 'mod_examregistrar', $area, $itemid, $path, $search);
        }

        if($files = $fs->get_directory_files($contextid, 'mod_examregistrar', $area, $itemid, $path, false, false, "filepath, filename")) {
            if(!$search) {
                return reset($files);
            } else {
                return $files;
            }
        }

        return $file;
    }


    function examregistrar_file_encode_url($contextid, $itemid, $type, $filename='', $revision = false, $forcedownload=false) {
        global $CFG;

        $url = '';

        list($area, $path) = examregistrar_file_decode_type($type);

        if(!$filename) {
            $fs = get_file_storage();
            if($files = $fs->get_directory_files($contextid, 'mod_examregistrar', $area, $itemid, $path, false, false, "filepath, filename")) {
                $file = reset($files);
                $filename = $file->get_filename();
            }
        }

        $revpath = '';
        if($revision = (int)$revision) {
            $revpath = 'rev/'.$revision.'/';
        }
        $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$contextid.'/mod_examregistrar/'.$area.'/'.$revpath.$itemid.$path.$filename, $forcedownload);
        return $url;
    }


    function get_roomzip_filename($session, $bookedsite, $room) {
        $sessionstr = get_string('examsessionitem', 'examregistrar');
        $venuestr = get_string('venue', 'examregistrar');
        $roomstr = get_string('room', 'examregistrar');
        return clean_filename($sessionstr.'_'.$session.'_'.$venuestr.'_'.$bookedsite.'_'.$roomstr.'-'.$room->idnumber.'.zip');
    }

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding examregistrar nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the examregistrar module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function examregistrar_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the examregistrar settings
 *
 * This function is called when the context for the page is a examregistrar module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $examregistrarnode {@link navigation_node}
 */
function examregistrar_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $examregistrarnode=null) {
    global $PAGE, $DB;

    
/*
    if (empty($PAGE->cm->context)) {
        $PAGE->cm->context = context_module::instance($PAGE->cm->instance);
    }
    $cm = $PAGE->cm;
    if (!$cm) {
        return;
    }
    $context = $cm->context;
    $course = $PAGE->course;
    if (!$course) {
        return;
    }

    $node1 = $settingsnav->prepend('texto', null, navigation_node::TYPE_CONTAINER);


    if (has_capability('mod/examregistrar:editelements', $cm->context)) {
        $link = new moodle_url('/mod/examregistrar/editelements.php', array('id' => $cm->id));
        $linkname = get_string('editelements', 'examregistrar');
        $node = $examregistrarnode->add($linkname, $link, navigation_node::TYPE_SETTING);

        $node = $node1->add($linkname, $link, navigation_node::TYPE_SETTING);
    }

    if (has_capability('mod/examregistrar:manageperiods', $cm->context)) {
        $link = new moodle_url('/mod/examregistrar/locations/index.php', array('id' => $course->id));
        $periods = $examregistrarnode->add(get_string('manage', 'examregistrar'), $link, navigation_node::TYPE_CONTAINER);
        $link = new moodle_url('/mod/examregistrar/locations/index.php', array('id' => $course->id));
        $linkname = get_string('editelements', 'examregistrar');
        $node = $periods->add($linkname, $link, navigation_node::TYPE_SETTING);
    }
*/

}

////////////////////////////////////////////////////////////////////////////////
// Module API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns array of examregistrar formats chooseable on the examregistrar editing form
 *
 * @return array
 */
function examregistrar_get_workmodes() {
    return array (EXAMREGISTRAR_MODE_VIEW     => get_string('modeview','examregistrar'),
                  EXAMREGISTRAR_MODE_BOOK     => get_string('modebook','examregistrar'),
                  EXAMREGISTRAR_MODE_PRINT    => get_string('modeprint','examregistrar'),
                  EXAMREGISTRAR_MODE_REVIEW   => get_string('modereview','examregistrar'),
                  EXAMREGISTRAR_MODE_REGISTRAR   => get_string('moderegistrar','examregistrar'),
                 );
}

/**
 * Returns array of examregistrar formats chooseable on the examregistrar editing form
 *
 * @return array
 */
function examregistrar_get_primary_registrars() {
    global $DB;

    $regmenu = array();

    $sql = "SELECT er.primaryidnumber, CONCAT(c.shortname, '-', er.name)
            FROM {examregistrar} er
            JOIN {course} c ON c.id = er.course
            WHERE ".$DB->sql_isempty('examregistrar', 'primaryreg', true, false).
            " AND ".$DB->sql_isnotempty('examregistrar', 'primaryidnumber', true, false);
    $sort = ' ORDER BY c.shortname ASC, er.name ASC';
    $regmenu = $DB->get_records_sql_menu($sql.$sort, array());

    return $regmenu;
}

