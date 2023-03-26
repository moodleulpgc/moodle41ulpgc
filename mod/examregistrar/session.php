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
 * Prints the Session management interface of an instance of examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
*/
require_once($CFG->dirroot.'/mod/examregistrar/managelib.php');
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_forms.php");
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_table.php");

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

require_capability('mod/examregistrar:manageseats', $context);

$edit   = optional_param('edit', '', PARAM_ALPHANUMEXT);  // list/edit items
$action = optional_param('action', '', PARAM_ALPHANUMEXT);  // complex action not managed by edit
$upload = optional_param('csv', '', PARAM_ALPHANUMEXT);  // upload CSV file
$rsort = optional_param('rsort', '', PARAM_ALPHANUMEXT);
$esort = optional_param('esort', '', PARAM_ALPHANUMEXT);
$perpage  = optional_param('perpage', 100, PARAM_INT);

$SESSION->nameformat = 'lastname';

$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id' => $cm->id, 'tab' =>'session'));
$tab = 'session';

if(!$session = optional_param('session', 0, PARAM_INT)) {

$now = time();
    $session = examregistrar_next_sessionid($examregistrar, $now);
}
$baseurl->param('session', $session);
if($bookedsite = optional_param('venue', '', PARAM_INT)) {
    $baseurl->param('venue', $bookedsite);
} 

//$baseurl->params(array('session'=>$session, 'venue'=>$bookedsite));

// set control question, if in use and ID exists
examregistrar_get_primaryid($examregistrar);
$configdata = examregistrar_get_instance_config($examregistrar->id);
$controlquestion = false;
if(isset($configdata->insertcontrolq) && $configdata->insertcontrolq && $DB->record_exists('question', array('id' => $configdata->controlquestion))) {
    $controlquestion = $configdata->controlquestion;
}
$output = $PAGE->get_renderer('mod_examregistrar', 'session');
///////////////////////////////////////////////////////////////////////////////


/// process forms actions

if($action == 'assignseats_venues') {
    // get venues and heck for single room
    $venueelement = examregistrar_get_venue_element($examregistrar);
    
    if($venues = $DB->get_records('examregistrar_locations', array('examregid'=>$examregprimaryid, 'locationtype'=>$venueelement, 'visible'=>1))) {
        foreach($venues as $venue) {
            if($roomid = examregistrar_is_venue_single_room($venue)) {
                // assign venue exams to room
                if(!$max = $DB->get_records('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$venue->id),
                                                ' timemodified DESC ', '*', 0, 1)) {
                    examregistrar_session_seats_makeallocation($session, $venue->id);
                } else {
                    $lasttime = reset($max)->timecreated;
                    examregistrar_session_seats_newbookings($session, $venue->id, $lasttime+1);
                }
            }
        }
    }
} elseif(($action == 'session_responses') && $session) {
    $config = get_config('examregistrar');
    $fs = get_file_storage();
    if($pending = $fs->get_directory_files($context->id, 'mod_examregistrar', 'sessionresponses', $session, '/', false, false)) {
        $sessiondir =  clean_filename($config->distributedfolder);
        $sessiondir = '/'.$sessiondir.'/';
        $fs->create_directory($context->id, 'mod_examregistrar', 'sessionresponses', $session, $sessiondir);
        $exams = array();
        //make_upload_directory($sessiondir);
        //check_dir_exists($sessiondir);
        if($sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  false, false)) {
            foreach($sessionexams as $exam) {
                $examclass = new examregistrar_exam($exam);
                $exams[$exam->id] = $examclass->get_exam_name(false, true, false, false);
                //$exams[$exam->id] = $exam->shortname;
            }
        }
        $filerecord = array('component'=>'mod_examregistrar', 'filearea'=>'responses', 'filepath'=>'/');
        $info = new stdClass();
        $info->delivered = 0;
        $info->fail = 0;
        $cinfo = new stdClass();
        list($sname, $sidnumber) = examregistrar_get_namecodefromid($session, 'examsessions', 'examsession');
        $cinfo->session = $sidnumber;
        $userfieldsapi = \core_user\fields::for_name();
        $names = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $from = get_string('mailfrom',  'examregistrar');
        $delivered = array();
        foreach($pending as $file) {
            $fname = $file->get_filename();
            $name =  (false === strpos($fname, '.')) ? $fname : strstr($fname, '.', true);
            $name =  (false === strpos($name, '_')) ? $name : strstr($name, '_', true);
            if($examid = array_search($name, $exams)) {
                // we have an exam: addfile to areafiles and move to backup
                if($examfile = $DB->get_record('examregistrar_examfiles', array('examid'=>$examid, 'status'=>EXAM_STATUS_APPROVED))) {
                    $fcontext = context_course::instance($sessionexams[$examid]->courseid);
                    $filerecord['contextid'] = $fcontext->id;
                    $filerecord['itemid'] = $examfile->id;
                    $filerecord['filename'] = $examfile->idnumber.$configdata->extresponses;
                    $files = $fs->get_area_files($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'], $filerecord['itemid']);
                    $num = 1;
                    if($files) {
                        $num = count($files) + 1;
                        $filerecord['filename'] .= "($num)";
                    }
                    $filerecord['filename'] .= '.pdf';
                    if($fs->create_file_from_storedfile($filerecord, $file)) {
                        // file is delivered to exam now move to backup
                        if($fs->file_exists($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $sessiondir, $fname)) {
                            $pathinfo = pathinfo($fname);
                            $fname = $pathinfo['filename']."($num)".'.'.$pathinfo['extension'];
                        }
                        $file->rename($sessiondir, $fname);
                        $DB->set_field('examregistrar_examfiles', 'taken', 1, array('id'=>$examfile->id, 'examid'=>$examid));
                        $info->delivered += 1;
                        $delivered[] = $name;
                        // now send email
                        $teachers = get_enrolled_users($fcontext, 'mod/examregistrar:download', 0, 'u.id, u.idnumber, u.email, u.mailformat, u.username, '.$names);
                        $subject = get_string('mailresponsessubject',  'examregistrar', $name);
                        $cinfo->fname = $filerecord['filename'];
                        $cinfo->course = $name;
                        $text = get_string('mailresponsestext',  'examregistrar', $cinfo);


                        foreach($teachers as $user) {
                            email_to_user($user, $from, $subject, $text, $text);
                        }
                    }
                }
            } else {
                $info->fail += 1;
            }
        }
        $controluser = core_user::get_support_user();
        $controluser->email = 'ccv@ulpgc.es';
        $controluser->mailformat = 1;
        $controluser->id = 1;
        $subject = get_string('mailsessionsubject',  'examregistrar', $sidnumber);
        $text = get_string('mailsessioncontrol',  'examregistrar', implode("\n", $delivered) );
        email_to_user($controluser, $from, $subject, $text, $text);
        $controluser->email = 'ditele@ulpgc.es';
        email_to_user($controluser, $from, $subject, $text, $text);
        redirect($baseurl, get_string('loadresponsesconfirm', 'examregistrar', $info), 5);
    }
} elseif(($action == 'session_files') && $session) {
    if($del = optional_param('deleteresponsefiles', '', PARAM_ALPHANUMEXT)) {
        $success = false;
        $fs = get_file_storage();
        if($files = $fs->get_directory_files($context->id, 'mod_examregistrar', 'sessionresponses', $session, '/', false, false)) {
            foreach($files as $file) {
                $success = $file->delete();
            }
        }
        if($success)  {
            add_to_log($course->id, 'examregistrar', 'delete session files', 'view.php?id='.$cm->id, $examregistrar->name, $cm->id);
        }
        $baseurl->param('action', 'session_files');
        redirect($baseurl);
    }

    $data = new stdClass();
    $data->id = $cm->id;
    $data->tab = 'session';
    $data->session = $session;
    $data->bookedsite = $bookedsite;
    $data->action = $action;
    $data->area = optional_param('area', 'sessionresponses', PARAM_ALPHANUMEXT);
    $options = array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'accepted_types'=>'*');
    file_prepare_standard_filemanager($data, 'files', $options, $context, 'mod_examregistrar', $data->area, $session);
    $mform = new examregistrar_files_form(null, array('data'=>$data, 'options'=>$options));

    if (!$mform->is_cancelled()) {
        if ($formdata = $mform->get_data()) {
            if(!isset($formdata->deleteresponsefiles)) {
                $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $context, 'mod_examregistrar', $data->area, $session);
                add_to_log($course->id, 'examregistrar', 'edit session files', 'view.php?id='.$cm->id, $examregistrar->name, $cm->id);
            }
        } elseif(!$formdata) {
            $sessionname = '';
            if($session) {
                $sql = "SELECT s.id, s.examsession, es.name, es.idnumber, s.examdate, ep.name AS periodname, ep.idnumber AS periodidnumber
                        FROM {examregistrar_examsessions} s
                        JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
                        JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
                        JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                        WHERE s.id = :id ";
                $examsession = $DB->get_record_sql($sql, array('id'=>$session), MUST_EXIST);
                $sessionname = $output->formatted_name($examsession->periodname, $examsession->periodidnumber).'; ';
                $sessionname .= $output->formatted_name($examsession->name, $examsession->idnumber).',  '. userdate($examsession->examdate, get_string('strftimedaydate'));
            }

            echo $output->heading($sessionname, 3, 'main');
            echo $output->container('', 'clearfix');
            $headstr = ($data->area == 'control') ? 'loadsessioncontrol' : 'loadsessionresponses';
            echo $OUTPUT->heading(get_string($headstr,  'examregistrar'), 4, 'main');
            echo $OUTPUT->box_start('generalbox foldertree');
            $mform->display();
            echo $OUTPUT->box_end();
            echo $OUTPUT->footer();
            die();
        }
    }
} elseif(($action == 'response_files') && $session) {
    $data = new stdClass();
    $data->id = $cm->id;
    $data->tab = 'session';
    $data->session = $session;
    $data->bookedsite = $bookedsite;
    $data->action = $action;
    $data->area = 'responses';
    $data->examfile = optional_param('examf', 0, PARAM_INT);
    $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$data->examfile), '*', MUST_EXIST);
    $exam = $DB->get_record('examregistrar_exams', array('id'=>$examfile->examid), '*', MUST_EXIST);
    $ccontext = context_course::instance($exam->courseid);
    $options = array('subdirs'=>0, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'accepted_types'=>'*');
    file_prepare_standard_filemanager($data, 'files', $options, $ccontext, 'mod_examregistrar', 'responses', $data->examfile);
    $mform = new examregistrar_files_form(null, array('data'=>$data, 'options'=>$options));
    if (!$mform->is_cancelled()) {
        if ($formdata = $mform->get_data()) {
            $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $ccontext, 'mod_examregistrar', 'responses', $data->examfile);
            add_to_log($course->id, 'examregistrar', 'edit response files', 'view.php?id='.$cm->id, $examregistrar->name, $cm->id);
        } elseif(!$formdata) {
            $sessionname = '';
            if($session) {
                $sql = "SELECT s.id, s.examsession, es.name, es.idnumber, s.examdate, ep.name AS periodname, ep.idnumber AS periodidnumber
                        FROM {examregistrar_examsessions} s
                        JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
                        JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
                        JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                        WHERE s.id = :id ";
                $examsession = $DB->get_record_sql($sql, array('id'=>$session), MUST_EXIST);
                $sessionname = $output->formatted_name($examsession->periodname, $examsession->periodidnumber).'; ';
                $sessionname .= $output->formatted_name($examsession->name, $examsession->idnumber).',  '. userdate($examsession->examdate, get_string('strftimedaydate'));
            }

            echo $output->heading($sessionname, 3, 'main');
            echo $output->container('', 'clearfix');
            echo $OUTPUT->heading(get_string('examresponsesfiles',  'examregistrar'), 4, 'main');
            echo $OUTPUT->box_start('generalbox foldertree');
            $mform->display();
            echo $OUTPUT->box_end();
            echo $OUTPUT->footer();
            die();
        }
    }
} elseif(($action == 'checkvoucher')) {
    $vouchernum = optional_param('vouchernum', '', PARAM_ALPHANUMEXT);
    $crccode = optional_param('code', '', PARAM_ALPHANUMEXT);
    // we put this here to be aable to perfom checking from the QRcode url, without web form
    if($vouchernum && $crccode) {
        $verify = true;
        echo $output->heading(get_string('checkvoucher',  'examregistrar'), 3, 'main');
        
        echo examregistrar_verify_voucher($cm->id, $vouchernum, $crccode, ($canmanageseats || $canprintexams || $canmanage));
        
        if(!$canmanageseats) {
            if($canprintexams) {
                $baseurl->param('tab','printexams');
            } elseif($canbook) {
                $baseurl->param('tab','booking');
            } 
        }
        echo $OUTPUT->continue_button($baseurl);

        echo $output->footer();
        die();
    
    } else {
        $data = new stdClass();
        $data->id = $cm->id;
        $data->tab = 'session';
        $data->session = $session;
        $data->bookedsite = $bookedsite;

        $mform = new examregistrar_voucher_checking_form(null, array('data'=>$data));
        if (!$mform->is_cancelled()) {
            echo $output->heading(get_string('checkvoucher',  'examregistrar'), 3, 'main');
            $mform->display();
            echo $output->footer();
            die();
        }
    }
    
} elseif(($action == 'examssetquestions')) {    
    if($examid = optional_param('exam', 0, PARAM_INT)) {
        $sessionexams = $DB->get_records('examregistrar_exams', array('id' => $examid));
    } else {
        $sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  false, false, 'quiz');
    }

    $num = 0;
    foreach($sessionexams as $examrec) {
        if($examrec->quizplugincm) {
            $exam = new examregistrar_exam($examrec); 
            if(!$message = $exam->set_valid_file()) {
                if(!$message = $exam->set_valid_questions($controlquestion,  $examid)) {
                    $num++;
                }
            }
        }
    }
    
    if(!$examid) {
        \core\notification::add(get_string('examsquestionsloaded', 'examregistrar', $num), \core\output\notification::NOTIFY_SUCCESS);
    }
} elseif(($action == 'examsdelquestions')) {     
    if($examid = optional_param('exam', 0, PARAM_INT)) {
        $sessionexams = $DB->get_records('examregistrar_exams', array('id' => $examid));
    } else {
        $sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  false, false, 'quiz');
    }

    $num = 0;
    foreach($sessionexams as $examrec) {
        if($examrec->quizplugincm) {
            $exam = new examregistrar_exam($examrec); 
            $exam->clear_quiz_questions();
            $num++;
        }
    }
    
    if(!$examid) {
        \core\notification::add(get_string('examsquestionscleared', 'examregistrar', $num), \core\output\notification::NOTIFY_SUCCESS);
    }

} elseif(($action == 'updatequizzes') || 
            ($action == 'removequizpass') || 
            ($action == 'mklockquizzes') ) {
    $options = ['session' => $session];
    if($examid = optional_param('exam', 0, PARAM_INT)) {
        $options['examid'] = $examid;;
    }
    if($action == 'updatequizzes') {
        examregistrar_update_exam_quizzes($examregistrar, $options);
    } elseif($action == 'removequizpass') {
        examregistrar_exam_quizzes_remove_password($examregistrar, $options);
    } elseif($action == 'mklockquizzes') {
        examregistrar_exam_quizzes_mklock($examregistrar, $options);
    }
    
} elseif(($action == 'examssetoptions')) {    
    $sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  false, false, 'quiz');
        $num = 0;
    foreach($sessionexams as $examrec) {
        if($examrec->quizplugincm && $quiz = examregistrar_exam::get_quiz_from_cmid($examrec->quizplugincm)) {
            if(isset($configdata->optionsinstance) && $options = examregistrar_exam::get_quiz_from_cmid($configdata->optionsinstance)) {
                $fields = array_walk(explode(',', $configdata->quizoptions), 'trim');
                if($key = array_search('review', $fields)) {
                    unset($fields[$key]);
                    $fields += ['reviewattempt',
                                'reviewcorrectness',
                                'reviewmarks',
                                'reviewspecificfeedback',
                                'reviewgeneralfeedback',
                                'reviewrightanswer',
                                'reviewoverallfeedback',
                                'reviewattempt',
                                'reviewoverallfeedback'];
                }
                
                foreach($fields  as $field) {
                    $quiz->{$field} = $options->{$field};
                }
                if($DB->update_record('quiz', $quiz)) {
                    $num++;
                }
            } 
        }
    }
    \core\notification::add(get_string('examsoptionsset', 'examregistrar', $num), \core\output\notification::NOTIFY_SUCCESS);
}

////////////////////////////////////////////////////////////////////////////////

/// Print the page header, Output starts here
    $candownload = has_capability('mod/examregistrar:download',$context);

    // Add tabs, if needed
    include_once('tabs.php');
    
    $params = array('id' => $cm->id, 'session' => $session, 'venue' => $bookedsite);
    $editurl = new moodle_url('/mod/examregistrar/manage.php', $params);
    $actionurl = new moodle_url('/mod/examregistrar/manage/action.php', $params);

/// Control panel & links header
    echo $output->session_control_panel_links($baseurl, $editurl, $actionurl);
   
/// Session & venue selector

    echo $output->container_start('examregistrarfilterform clearfix ');
        $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, '', '', array(), 't.examdate ASC');
        $sessionurl = clone $baseurl;
        $sessionurl->remove_params('session');
        $select = new single_select($sessionurl, 'session', $sessionmenu, $session, '');
        $select->label = get_string('examsessionitem', 'examregistrar');
        $select->set_label(get_string('examsessionitem', 'examregistrar'), array('class'=>' singleselect filter'));
        $select->class .= ' filter ';
    echo $output->render($select);
        $venueelement = examregistrar_get_venue_element($examregistrar);
        $venuemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose', '', array('locationtype'=>$venueelement));
        //natcasesort($venuemenu);
        $venueurl = clone $baseurl;
        $venueurl->remove_params('venue');
        $select = new single_select($venueurl, 'venue', $venuemenu, $bookedsite);
        $select->set_label(get_string('venue', 'examregistrar'), array('class'=>'singleselect  filter'));
        $select->class .= ' filter ';
    echo $output->render($select);
    echo $output->container_end();

/// main part of interface

$sessionname = '';
if($session) {
    $sql = "SELECT s.id, s.examsession, es.name, es.idnumber, s.examdate, ep.name AS periodname, ep.idnumber AS periodidnumber
            FROM {examregistrar_examsessions} s
            JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
            JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
            JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
            WHERE s.id = :id ";
    $examsession = $DB->get_record_sql($sql, array('id'=>$session), MUST_EXIST);
    $sessionname = $output->formatted_name($examsession->periodname, $examsession->periodidnumber).'; ';
    $sessionname .= $output->formatted_name($examsession->name, $examsession->idnumber).',  '. userdate($examsession->examdate, get_string('strftimedaydate'));
}

    echo $output->heading($sessionname, 3, 'main');
    echo $output->container('', 'clearfix');

////  qualitycontrol //////////////////////////////////////////////////////////    
    echo $output->container_start('examregqualitycontrol clearfix ');
    
        // Rooms without staff número, lista desplegable
        $sql = "SELECT COUNT(DISTINCT sr.id)
                    FROM {examregistrar_session_rooms} sr
                    JOIN {examregistrar_locations} l ON sr.roomid = l.id
                    WHERE sr.examsession = :session
                    AND EXISTS (SELECT 1
                                    FROM {examregistrar_session_seats} ss
                                    WHERE ss.examsession = sr.examsession AND ss.roomid = sr.roomid AND sr.available = 1)

                    AND NOT EXISTS (SELECT 1
                                        FROM {examregistrar_staffers} s
                                        WHERE s.examsession = sr.examsession AND s.locationid = sr.roomid AND s.visible = 1 AND sr.available = 1)
                    ";
        $params = array('session'=>$session);
        $roomsnonstaffed = $DB->count_records_sql($sql, $params);    
    
        // Staff without room número, lista desplegable
        $courseids = $DB->get_fieldset_select('examregistrar_exams', 'courseid', ' courseid <> 0 AND  examsession = ? ', array($session));
        $users = array();
        foreach($courseids as $courseid) {
            $coursecontext = context_course::instance($courseid);
            $managers = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.firstname, u.lastname, u.idnumber, u.picture', ' u.lastname ASC ');
            foreach($managers as $uid => $user) {
                if(!isset($users[$uid]) && !$DB->record_exists('examregistrar_staffers', array('examsession'=>$session, 'userid'=>$uid, 'visible'=>1))) {
                    $users[$uid] = $user;
                }
            }
        }    
    
        echo $output->print_session_control_box('', [], [], 
                                                get_string('qualitycontrol', 'examregistrar'), 
                                                'examregqualitycontrol', 
                                                $output->session_quality_control($session, $bookedsite, $esort, $roomsnonstaffed, count($users))
                                                );
        unset($users);
    echo $output->container_end();

////  printoperators //////////////////////////////////////////////////////////    
    echo $output->container_start('examregprintoperators clearfix ');
        $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cm->id, 'edit'=>'assignseats',
                                                                               'session'=>$session, 'venue'=>$bookedsite));
        echo $output->print_session_control_box('', [], [], 
                                                get_string('printingbuttons', 'examregistrar'), 
                                                'examregprintoperators', 
                                                $output->session_printing_buttons($downloadurl, $rsort)
                                                );     
    echo $output->container_end();    
    
    echo $output->container('', 'clearfix');    

////  sessionrooms ////////////////////////////////////////////////////////////  
    echo $output->container_start('examregsessionrooms clearfix ');

        $sessionrooms = examregistrar_get_session_rooms($session, $bookedsite, $rsort,  true, 1);
        
        $headerlinks = ['sessionrooms' => new moodle_url('/mod/examregistrar/manage/assignsessionrooms.php',
                                                    $baseurl->params() + array('action'=>'sessionrooms', 'edit'=>''))];
        
        $footerlinks = ['stafffromexam' => new moodle_url('/mod/examregistrar/manage/action.php',
                                                        $baseurl->params() + array('action'=>'stafffromexam', 'edit'=>''))];
        if($bookedsite) {
            $footerlinks['generateroomspdfs'] = new moodle_url('/mod/examregistrar/download.php', 
                                                                array('id' => $cm->id, 'edit'=>'assignseats',
                                                                        'session'=>$session, 'venue'=>$bookedsite,
                                                                        'down'=>'genvenuezips'));
        }
        
        echo $output->print_session_control_box(get_string('roomsinsession', 'examregistrar', count($sessionrooms)), 
                                                $headerlinks, $footerlinks, 
                                                get_string('managesessionrooms', 'examregistrar'), 
                                                'examregsessionrooms', 
                                                $output->build_session_rooms_table($sessionrooms, $baseurl, 
                                                                $esort, $rsort, $session, $bookedsite, $candownload)
                                                ); 
    //echo $output->container_end();          

////  sessionalttaking ////////////////////////////////////////////////////////
    echo $output->container_start('examregsessionalttaking clearfix');
            $sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  true, true, true);
            if(examregistrar_exams_have_quizzes(array_keys($sessionexams))) { 
                $headerlinks = ['assignquestions' => new moodle_url($baseurl, $baseurl->params() 
                                                                    + array('action'=>'examssetquestions'))];            
            }            
            
        echo $output->print_session_control_box(get_string('alttakingmodeinsession', 'examregistrar', count($sessionexams)), 
                                                $headerlinks, [], 
                                                get_string('managesessionalttaking', 'examregistrar'), 
                                                'sessionalttaking', 
                                                $output->build_online_exams_table($sessionexams, $baseurl, 
                                                                                    $esort, $rsort)
                                                );             

    echo $output->container_end();        

    echo $output->container_end(); 
    //echo $output->container('', 'clearfix');        


////  sessionexams ///////////////////////////////////////////////////////////    
    echo $output->container_start('examregsessionexams clearfix ');

        $sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  true, true);
        
        $headerlinks = ['assignseats' => new moodle_url('/mod/examregistrar/manage/assignseats.php',
                                                        $baseurl->params() + array('edit'=>'session_rooms'))];
        
        echo $output->print_session_control_box(get_string('managesessionexams', 'examregistrar'), 
                                                $headerlinks, [], 
                                                get_string('managesessionexams', 'examregistrar'), 
                                                'sessionexams', 
                                                $output->build_session_exams_table($sessionexams, $baseurl, $actionurl, 
                                                                                    $esort, $rsort, $session, $bookedsite)
                                                );          
  
    echo $output->container_end();

    echo $output->container('', 'clearfix');

////  sessionresponses ////////////////////////////////////////////////////////
    echo $output->container_start('examregsessionresponses clearfix ');

        $config = get_config('examregistrar');
        $sessiondir =  clean_filename($config->distributedfolder);
        $sessiondir = '/'.$sessiondir.'/';
        $pending = array();
        $distributed = array();
        $fs = get_file_storage();
        $pending = $fs->get_directory_files($context->id, 'mod_examregistrar', 'sessionresponses', $session, '/', false, false);
        $distributed = $fs->get_directory_files($context->id, 'mod_examregistrar', 'sessionresponses', $session, $sessiondir, false, false);

        $sessionurl = new moodle_url('view.php', $baseurl->params());
        $sessionurl->param('action', 'session_files');
        $sessionurl->param('area', 'sessionresponses');
        
        $headerlinks = ['loadsessionresponses' => clone $sessionurl];
        if($pending) {
            $sessionurl->param('action', 'session_responses');
            $headerlinks['assignsessionresponses'] = clone $sessionurl;
        }
        if($distributed) {
            $sessionurl->param('action', 'session_files');
            $sessionurl->param('area', 'sessioncontrol');
            $headerlinks['loadsessioncontrol'] = clone $sessionurl;
            $sessionresponseslink = html_writer::link($sessionurl, get_string('loadsessioncontrol', 'examregistrar'));
        }
            
        echo $output->print_session_control_box(get_string('pendingresponsefiles', 'examregistrar', count($pending)), 
                                        $headerlinks, [], 
                                        get_string('managesessionresponses', 'examregistrar'), 
                                        'examregsessionresponses', 
                                        $output->session_response_files_table($pending, count($distributed))
                                        );  

    echo $output->container_end();

////  examregspecialexams ////////////////////////////////////////////////////////    
    echo $output->container_start('examregspecialexams clearfix ');

        $sessionextraexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  true, false, false, true);
        echo $output->print_session_control_box(get_string('specialexamsinsession', 'examregistrar', count($sessionextraexams)), 
                                                [], [], 
                                                get_string('managespecialexams', 'examregistrar'), 
                                                'examregspecialexams', 
                                                $output->special_exams_form($baseurl)
                                                );                  
    echo $output->container_end();

    echo $output->container('', 'clearfix');
