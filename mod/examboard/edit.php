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
 * This page handles adding, editing and manipulation of data on examboard instances
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$e  = optional_param('e', 0, PARAM_INT); // ... module instance id.
$action = optional_param('action', '', PARAM_ALPHAEXT);

$view   = optional_param('view', '', PARAM_ALPHA);  // ... viewing list, board, exam
$itemid = optional_param('item', 0, PARAM_INT);     // ... item to view

$groupid = optional_param('group', 0, PARAM_INT);
$sort = optional_param('tsort', '', PARAM_ALPHANUMEXT);
$order = optional_param('order', '', PARAM_ALPHANUMEXT);
$userorder = optional_param('uorder', 1, PARAM_INT); 
$examid = optional_param('exam', 0, PARAM_INT);
$boardid = optional_param('board', 0, PARAM_INT);


if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'examboard');
    $examboard = $DB->get_record('examboard', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($e) {
    $examboard = $DB->get_record('examboard', array('id' => $e), '*', MUST_EXIST);
    list ($course, $cm) = get_course_and_cm_from_instance($e, 'examboard'); 
} else {
    print_error(get_string('missingidandcmid', 'examboard'));
}

require_login($course, true, $cm);

$examboard->cmidnumber = $cm->idnumber;
$examboard->cmid = $cm->id; 
$context = context_module::instance($cm->id);

// set url params 
$urlparams = array('id' => $cm->id);
if($action) {
    $urlparams['action'] = $action;
}
if($view) {
    $urlparams['view'] = $view;
}
if($itemid) {
    $urlparams['item'] = $itemid;
}
if($sort) {
    $urlparams['sort'] = $sort;
}
if(!$userorder) {
    $urlparams['uorder'] = $userorder;
}
if($examid) {
    $urlparams['view'] = 'exam';
    $urlparams['item'] = $examid;
}


// http://localhost/moodle31ulpgc/mod/examboard/view.php?id=8975&view=exam&item=1
// http://localhost/moodle31ulpgc/mod/examboard/edit.php?id=8975&exam=1&user=58&action=userdown

/// Check to see if groups are being used in this examboard
$groupmode = groups_get_activity_groupmode($cm);
if ($groupmode) {
    $groupid = groups_get_activity_group($cm, true);
}
if($groupid) {
    $urlparams['group'] = $groupid;
}

$url = new moodle_url('/mod/examboard/edit.php', $urlparams);
$returnurl = new moodle_url('/mod/examboard/view.php', $urlparams);
$returnurl->remove_params('action');

$PAGE->set_url($url);
$PAGE->set_title(format_string($examboard->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if($action) {
   // $PAGE->navbar->add(get_string($action, 'examboard'), $url);
}

if($ulpgc = get_config('local_ulpgccore')) {
    $nameformat = '';
    if(!$userorder) {
        $nameformat = 'firstname';
    } else {
        $nameformat = 'lastname';
    }
    $SESSION->nameformat = $nameformat;
}

/*
print_object($_POST);
print_object($_GET);

die();
*/

// actions not requiring interface form
$actions = array('examhide', 'examshow', 'userup', 'userdown', 'usertoggle', 'reorder', 'memberup', 'memberdown', 'delmember', 'boardtoggle');

$eventparams = array(
    'context' => $context,
    'other' => array('examboardid'=>$examboard->id),
);

if($examid && in_array($action, $actions)) {
    if($action == 'examhide' || $action == 'examshow') {
        $toggle = ($action == 'examshow') ? 1 : 0;
        $DB->set_field('examboard_exam', 'active', $toggle, array('id' => $examid));
        $returnurl->remove_params('view', 'item');
    } elseif($action == 'userup') {
        if($userid = optional_param('user', 0, PARAM_INT)) {
            $params = array('examid'=>$examid, 'userid'=>$userid);
            if($sortorder = $DB->get_field('examboard_examinee', 'sortorder', $params)) {
                $upper = $DB->get_field('examboard_examinee', 'id', array('examid'=>$examid, 'sortorder' => ($sortorder - 1)));
                $DB->set_field('examboard_examinee', 'sortorder', ($sortorder -1), $params);
                $DB->set_field('examboard_examinee', 'sortorder', $sortorder, array('id'=>$upper));
            }
        }
    } elseif($action == 'userdown') {
        if($userid = optional_param('user', 0, PARAM_INT)) {
            $params = array('examid'=>$examid, 'userid'=>$userid);
            if($max = $DB->get_records_menu('examboard_examinee', $params, 'sortorder DESC', 'id, sortorder', 0, 1)) {
                $max = reset($max) + 1;
            } else {
                $max = 0;
            }
            $sortorder = $DB->get_field('examboard_examinee', 'sortorder', $params);
            if($sortorder < $max) {
                $lower = $DB->get_field('examboard_examinee', 'id', array('examid'=>$examid, 'sortorder' => ($sortorder + 1)));
                $DB->set_field('examboard_examinee', 'sortorder', ($sortorder + 1), $params);
                $DB->set_field('examboard_examinee', 'sortorder', $sortorder, array('id'=>$lower));
            }
        }
    } elseif($action == 'reorder') {    
        examboard_reorder_examinees($examid, optional_param('reorder', 0, PARAM_INT));
    } elseif($action == 'usertoggle') {   
        if($userid = optional_param('user', 0, PARAM_INT)) {
            $params = array('examid'=>$examid, 'userid'=>$userid);
            $status = $DB->get_field('examboard_examinee', 'excluded', $params); 
            $status = $status ? 0 : 1;
            $DB->set_field('examboard_examinee', 'excluded', $status, $params); 
        }
    }
    redirect($returnurl);
}    
if($boardid && in_array($action, $actions)) {
    $returnurl->param('view', 'board');
    $returnurl->param('item', $boardid);
    if($action == 'memberup') {
        if($userid = optional_param('user', 0, PARAM_INT)) {
            $params = array('boardid'=>$boardid, 'userid'=>$userid);
            if($sortorder = $DB->get_field('examboard_member', 'sortorder', $params)) {
                $upper = $DB->get_field('examboard_member', 'id', array('boardid'=>$boardid, 'sortorder' => ($sortorder - 1)));
                $DB->set_field('examboard_member', 'sortorder', ($sortorder -1), $params);
                $DB->set_field('examboard_member', 'sortorder', $sortorder, array('id'=>$upper));
            }
        }
    } elseif($action == 'memberdown') {
        if($userid = optional_param('user', 0, PARAM_INT)) {
            $params = array('boardid'=>$boardid, 'userid'=>$userid);
            if($max = $DB->get_records_menu('examboard_member', $params, 'sortorder DESC', 'id, sortorder', 0, 1)) {
                $max = reset($max) + 1;
            } else {
                $max = 0;
            }
            $sortorder = $DB->get_field('examboard_member', 'sortorder', $params);
            if($sortorder < $max) {
                $lower = $DB->get_field('examboard_member', 'id', array('boardid'=>$boardid, 'sortorder' => ($sortorder + 1)));
                $DB->set_field('examboard_member', 'sortorder', ($sortorder + 1), $params);
                $DB->set_field('examboard_member', 'sortorder', $sortorder, array('id'=>$lower));
            }
        }
    } elseif($action == 'delmember') {
        if($userid = optional_param('user', 0, PARAM_INT)) {
            $params = array('boardid'=>$boardid, 'userid'=>$userid);
            if($DB->delete_records('examboard_member', $params)) {
                $eventparams['objectid'] = $boardid;
                $eventparams['relateduserid'] = $userid;
                $event = \mod_examboard\event\member_updated::create($eventparams);
                $event->trigger();
                $event = \mod_examboard\event\board_updated_members::create($eventparams);
                $event->trigger();
            }
        }
    } elseif($action == 'boardtoggle') {       
        $status = $DB->get_field('examboard_board', 'active', array('id'=>$boardid)); 
        $status = $status ? 0 : 1;
        $DB->set_field('examboard_board', 'active', $status, array('id'=>$boardid)); 
    }
    redirect($returnurl);
}

if($action == 'synchusers') {
    examboard_synchronize_groups($examboard);
    examboard_synchronize_gradeables($examboard, false, false);
    redirect($returnurl);
}

$straction = get_string($action, 'examboard');

$mform = new stdClass();       
examboard_set_action_form($cm, $context, $examboard, $action, $mform);
        
if(is_subclass_of($mform, 'moodleform')) {
        
    // If data has been uploaded, then process it
    if ($mform->is_cancelled()) {
        redirect($returnurl);

    } else if ($fromform = $mform->get_data()) {
        $message = '';

        // capabilities has been checkef in examboard_set_action_form
        
        if($action == 'addexam' || $action == 'updateexam') {
            $message = examboard_process_add_update_exam($examboard, $fromform);
            $returnurl->remove_params('view', 'item');

        } elseif($action == 'editmembers') {
            $message = examboard_process_editmembers($examboard, $fromform);
            
        } elseif($action == 'updateuser') {
            $message = examboard_process_updateuser($examboard, $fromform);
        } elseif($action == 'moveusers') {
            $message = examboard_process_change_user_session($examboard, $fromform);
            if($message && $fromform->targetexam  && $fromform->movetoreturn) {
                $returnurl->param('item', $fromform->targetexam);
            }
        } elseif($action == 'notify') {
            // store input files on temdir
            $tempdir = make_request_directory();
            //$tempdir = 'examboard_notifications_'.$examboard->id;
            //make_temp_directory($tempdir);
            $fromform->tempdir = $tempdir;
            $files = ($fromform->logofile) ? array('logo', 'signature') : array('signature');
            foreach($files as $formfile) {
                $filename = $mform->get_new_filename($formfile.'file');
                $file = $tempdir . '/' . $filename;
                if($mform->save_file($formfile.'file', $file)) {
                    $fromform->{$formfile.'file'} = $file;
                } else {
                    $fromform->{$formfile.'file'} = '';
                }
            }
            
            $message = examboard_process_notifications($examboard, $course, $cm, $context, $fromform);
            //remove_dir($CFG->tempdir . '/' . $tempdir ); 
            
        } elseif(($action == 'deleteexam') && ($fromform->confirmed == 'deleteexam')) {
            // OK, delete it
            $message = examboard_remove_exam($fromform->exam, $fromform->withboard);
            $returnurl->remove_params(array('view', 'item'));
            
        } elseif(($action == 'deleteuser') && ($fromform->confirmed == 'deleteuser')) {
            // OK, delete it
            $success = examboard_remove_user_from_exam($examboard, $fromform->exam, $fromform->user);
            if($success) {
                $message = get_string('deletedexaminees', 'examboard', 1);
            } else {
                $message = get_string('cannotsavedata', 'error');   
            }

            $eventparams['objectid'] = $fromform->exam;
            $eventparams['relateduserid'] = $fromform->user;
            $event = \mod_examboard\event\exam_updated_users::create($eventparams);
            $event->trigger();
            
        } elseif(($action == 'deleteall') && ($fromform->confirmed == 'deleteall')) {
            $users = examboard_get_exam_examinees($fromform->exam);
            $deleted = 0;
            foreach($users as $examinee) {
                if(examboard_remove_user_from_exam($examboard, $fromform->exam, $examinee->userid)) {
                    $deleted++;
                }
            }
            $eventparams['objectid'] = $fromform->exam;
            $event = \mod_examboard\event\exam_updated_users::create($eventparams);
            $event->trigger();
            $message = get_string('deletedexaminees', 'examboard', $deleted);
            
        } elseif($action == 'userassign') {
            $message = examboard_process_userassign($examboard, $fromform);
            
        } elseif($action == 'import') {
            require_once($CFG->libdir.'/csvlib.class.php');     
            // Large files are likely to take their time and memory. Let PHP know
            // that we'll take longer, and that the process should be recycled soon
            // to free up memory.
            core_php_time_limit::raise();
            raise_memory_limit(MEMORY_EXTRA);

            $iid = csv_import_reader::get_new_iid('mod_examboard_import_examinations');
            $cir = new csv_import_reader($iid, 'mod_examboard_import_examinations');

            $filecontent = $mform->get_file_content('recordsfile');
            $readcount = $cir->load_csv_content($filecontent, $fromform->encoding, $fromform->separator);
            
            if (empty($readcount)) {
                //show meaningful error notice
                $line = strstr($filecontent, "\n", true);
                $line2 = '';
                if($p = strpos($filecontent, "\n", (strlen($line) + 2))) {
                    $line2 = substr($filecontent, strlen($line) + 1,  $p - strlen($line));
                }
                $line = $OUTPUT->box($line.'<br /><br />'.$line2.'<br />', 'csverror alert-error');
                unset($filecontent);
                
                notice($line.$cir->get_error(), $returnurl);
                
            } else {
                unset($filecontent);
                $message = examboard_import_examinations($examboard, $returnurl, $cir, $fromform);
            }
            
        } elseif($action == 'export') {
            $message = examboard_export_examinations($examboard, $fromform);
            die;
            
        } elseif($action == 'boardconfirm') {
            if($message = examboard_process_toggleconfirm($examboard, $fromform)) {
                $returnurl->param('view', 'board');
                $returnurl->param('item', $fromform->board);
            } else {
                $returnurl->remove_params('view', 'item');
            }
            
        } elseif($action == 'allocateboard') {
            $message = examboard_process_allocateboard($examboard, $fromform);
            
        } elseif($action == 'allocateusers') {
            $message = examboard_process_allocateusers($examboard, $fromform);
            
        } elseif(substr($action, 0, 7) == 'upload_') {
            $area = substr($action, 7);
            file_save_draft_area_files($fromform->attachments, $context->id, 
                                        'mod_examboard', $area, $itemid);
            $returnurl->param('view', 'exam');
            $returnurl->param('item', $fromform->exam);
            if(($area == 'member') || ($area == 'board') || ($area == 'examination')) {
                $returnurl->param('view', 'grading'); 
                $returnurl->param('user', $fromform->user);
            }

            $eventparams['objectid'] = $itemid;
            $eventparams['relateduserid'] = $fromform->user;
            $eventparams['other']['examid'] = $fromform->exam;
            if($area == 'member') {
                $event = \mod_examboard\event\file_member_uploaded::create($eventparams);
            } elseif($area == 'tutor') {
                $event = \mod_examboard\event\file_tutor_uploaded::create($eventparams);
            } else {
                $eventparams['other']['area'] = $area;            
                $event = \mod_examboard\event\file_uploaded::create($eventparams);
            }
            $event->trigger();
             
             
             
        } else {
            print_object($_POST);
            
            print_object($fromform);
        
            die();
        }
        
        redirect($returnurl, $message);
    }

    /// Print the form
    echo $OUTPUT->header();

    echo $OUTPUT->heading_with_help($straction, $action, 'examboard');
    $mform ->display();
} else {
    /// Print error message
    echo $OUTPUT->header();
//    print_object($_POST);
    
    echo notice(get_string('invalidaction', 'error', $action), $returnurl, $course); 

}

$SESSION->nameformat = ''; // ecastro ULPGC remove naming format
echo $OUTPUT->footer();



