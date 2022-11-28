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
 * Moodle renderer used to display special elements of the lesson module
 *
 * @package   mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace mod_examboard\output;

use plugin_renderer_base;  
use html_writer;
use pix_icon;
use moodle_url;
use single_select;

use stdClass;

 
class renderer extends plugin_renderer_base {

    /**
     * Generates the view exams table page
     *
     * @param object $examboard record from DB with module instance information
     * @param object $cm Course Module cm_info
     * @param exams_table $viewer object with the examinations list and vieweing options
     * @return string
     */
    public function view_exams($viewer) {
        $output = '';
        
        $examboard = $this->page->activityrecord;
        $cm = $this->page->cm;        
        
        $output .= $this->heading(format_string($examboard->name), 2, null);

        if ($examboard->intro) {
            $output .= $this->box(format_module_intro('examboard', $examboard, $cm->id), 'generalbox', 'intro');
        }
        
        $output .= groups_print_activity_menu($cm, $viewer->baseurl, true);
        
        //TODO //TODO //TODO //TODO //TODO //TODO //TODO //TODO 
        
        //add list of non assigned boards
        
        $output .= $this->render($viewer);
        
        $output .= $this->box($this->view_page_buttons($viewer->editurl, $viewer->canmanage, $viewer->hassubmits, $viewer->hasconfirms), 'generalbox pagebuttons');
        
        return $output;
    }

    
    
    /**
     * Generates the view submission page
     *
     * @param examination $exam the exam object 
     * @param stdClass $user the user object for the student
     * @return string
     */
    public function user_submission_data(&$exam, $user, $canmanage = false, $canviewall = false) {    
    
        $output = '';
        $examboard = $this->page->activityrecord;
        $output .= $this->heading(get_string('submissionstatus', 'examboard'), 3);    
        
        $mods = get_fast_modinfo($examboard->course)->get_cms();
        
        $exam->load_examinees();
        $examineeitemid = $exam->examinees[$user->id]->eid;
        
        $output .= $this->output->container_start('submissionsummary');
        $links = [];
        foreach(array('gradeable', 'proposal', 'defense') as $field) {
            if($examboard->$field) {
                foreach($mods as $cmid => $cm) {
                    if($cm->idnumber == $examboard->$field) {
                        break;
                    }
                }
                if($link = $this->gradeable_link($cm, $user->id, get_string($field, 'examboard'))) {
                    $links[] = $link;
                }
            }
        }
        $output .= $this->output->container_start('submission');
        $title = $this->heading(get_string('gradeableexternal', 'examboard'), 5);    
        
        $table = new \html_table();
        $table->attributes['class'] = 'generaltable submissionsummary';
        $table->align = array('right', 'left');
        //$table->size = array('', '');
        $table->data = array();
        $table->data[] = array($title, implode(' &nbsp; ', $links));
        
        //$output .= $this->output->container($title.implode(' &nbsp; ', $links), ' gradeablelinks ');

        $finaltext = '';
        if($examineeitemid && !empty($exam->examinees[$user->id]->onlinetext)) {
            $finaltext = file_rewrite_pluginfile_urls($exam->examinees[$user->id]->onlinetext,
                                                    'pluginfile.php',
                                                    $this->page->context->id,
                                                    'mod_examboard',
                                                    'user',
                                                    $examineeitemid);
            $params = array('overflowdiv' => true, 'context' => $this->page->context);
            $finaltext = format_text($finaltext, $exam->examinees[$user->id]->onlineformat, $params);
        }
        $title = $this->heading(get_string('gradeableinternal', 'examboard'), 5);            
        $table->data[] = array($title, $finaltext);
        //$output .= $this->output->container($title.$finaltext, ' gradeablelinks ');
        $output .= $this->output->container(\html_writer::table($table), ' gradeablelinks ');
        
        $output .= $this->output->container_end();
        
        $files = $this->user_submission_files($exam, $user, $canmanage, $canviewall);
        
        $output .= $this->output->container($files, ' fileviewupload ');
        $output .= $this->output->container_end();        
        
        
        
        
        return $output;
    }    
    

    /**
     * Generates the view submission page
     *
     * @param examination $exam the exam object 
     * @param stdClass $user the user object for the student
     * @param bool $canmanage if the user can edit or manage files
     * @return string
     */
    public function user_submission_files(&$exam, $user, $canmanage, $canviewall = false) {    
        global $USER;
        
        $uploads = '';
        
        $exam->load_board_members();
        $exam->load_tutors();        
        
        $isactive = $exam->is_active_member();
        $isgrader = $exam->is_grader();
        
        // the examinees table item id
        $examineeitemid = $exam->examinees[$user->id]->eid;
        $examineetutorid = reset($exam->tutors[$user->id])->tid;
        
        if($examineeitemid) {
            $uploads .= $this->show_files_area($user, $examineeitemid, 'user', false);
        }
        
        if(($canmanage || $canviewall || $isgrader) && $examineeitemid) {
            $uploads .= $this->show_files_area($user, $examineeitemid, 'examination', $isactive);
            $uploads .= $this->show_files_area($user, $examineeitemid, 'board', ($isactive || $canmanage));
            //$uploads .= $this->show_files_area($user, $examineeitemid, 'userprivate', $canmanage);
        }
        
        if(($canmanage || $canviewall) && !empty($exam->members)) {
            foreach($exam->members as $grader) {
                $grade = examboard_get_grader_grade($exam->id, $user->id, true, $grader->id);
                $uploads .= $this->show_files_area($user, $grade->id, 'member', 
                                                    ($grade->grader == $USER->id), fullname($grader));
            }     
        } elseif($isgrader || $canviewall) {
            $grade = examboard_get_grader_grade($exam->id, $user->id, true);
            $uploads .= $this->show_files_area($user, $grade->id, 'member', $isgrader);
        }
        
        if($examineetutorid && ($canmanage || $canviewall || $isgrader)) {
            $uploads .= $this->show_files_area($user, $examineetutorid, 'tutor', $isactive);
        }
    
        return $uploads;
    }
    
    /**
     * Generates the view submission page
     *
     * @param examination $exam the exam object 
     * @param stdClass $user the user object for the student
     * @param bool $cansubmit if the page will show form for submitting user data
     * @return string
     */
    public function view_user_submission_page($exam, $user, $cansubmit = false, $canmanage = false, $canviewall = false) {
        global $CFG, $USER;

        $output = '';    
        $examboard = $this->page->activityrecord;
        
        if(!$exam->is_participant($USER->id) && (($USER->id != $user->id) && (!$canmanage && !$canviewall))) {
            $output .= $this->output->heading(get_string('nopermissiontoviewpage', 'error'), 4, ' alert-info');
            $output .= $this->output->continue_button($this->page->url);
            return $output;
        }
                
        $title = get_string('usersubmission', 'examboard', $this->format_exam_name($exam));
        $output .= $this->heading($title, 2); 
        
        // print user submission data
        $output .= $this->user_submission_data($exam, $user, $canmanage, $canviewall);
        
        if($cansubmit && $USER->id == $user->id) {
            require_once($CFG->dirroot . '/mod/examboard/submission_form.php');
            $params = array('userid' => $user->id,
                            'cmid' => $this->page->cm->id,
                            'action' => 'upload_submission', 
                            'item' => $exam->examinees[$user->id]->eid,
            );
            
            $mform = new \examboard_submission_form(null, $params, 'post', '', array('class'=>'submissionform'));
            
            $upload = new stdClass;
            $upload->id = $this->page->cm->id;
            $draftitemid = file_get_submitted_draft_itemid('attachments');
            $area = 'user';
            $maxfiles = get_config('examboard', 'uploadmaxfiles');
            file_prepare_draft_area($draftitemid, $this->page->context->id, 'mod_examboard', $area, $exam->examinees[$user->id]->eid,
                                        array('subdirs' => 0, 'maxfiles' => $maxfiles));
            $upload->attachments = $draftitemid;
            $upload->itemid = $exam->examinees[$user->id]->eid;
            $upload->online['text'] = $exam->examinees[$user->id]->onlinetext;
            $upload->online['format']  = $exam->examinees[$user->id]->onlineformat ? $exam->examinees[$user->id]->onlineformat : 1 ;
            $mform->set_data($upload);                                                                    
            
            $output .= $this->output->box_start('boxaligncenter submissionform');
            $output .= $this->moodleform($mform);
            $output .= $this->output->box_end();
        } else {
            $url = $this->page->url;
            $url->params(array('view' => 'exam', 'item' => $exam->id, 'user' => $user->id));
            $output .= $this->output->single_button($url, get_string('viewexam', 'examboard'), 
                                                    'post', array('class' => 'continuebutton'));
        }
        return $output;
    }
    
    
    /**
     * Generates the user grading page
     *
     * @param examination $exam the exam object 
     * @param stdClass $user the user object for the student
     * @param bool $canmanage if the page will show form for updating user grade & other data
     * @return string
     */
    public function view_user_grade_page($exam, $user, $canmanage = false) {
        global $CFG, $USER;
        
        require_capability('mod/examboard:grade', $this->page->context);
        
        $output = '';    
        $examboard = $this->page->activityrecord;
    
        $title = get_string('gradinguser', 'examboard', $this->format_exam_name($exam));
        $output .= $this->heading($title, 2);    
        
        // print user data photo & name
        $output .= $this->output->container_start('usersummary');
        $output .= $this->output->box_start('boxaligncenter usersummarysection');
        $output .= $this->output->user_picture($user);
        $output .= $this->output->spacer(array('width'=>30));
        
        $urlparams = array('id' => $user->id, 'course'=>$examboard->course);
        $url = new moodle_url('/user/view.php', $urlparams);
        $fullname = fullname($user);
        foreach(get_extra_user_fields($this->page->context) as $extrafield) {
            $extrainfo[] = $user->$extrafield;
        }
        if (count($extrainfo)) {
            $fullname .= ' (' . implode(', ', $extrainfo) . ')';
        }
        $output .= $this->output->action_link($url, $fullname);
        
        $output .= $this->output->box_end();
        $output .= $this->output->container_end();
        
        // print user submission data
        $output .= $this->user_submission_data($exam, $user, $canmanage);
        
        /*
        $output .= $this->heading(get_string('submissionstatus', 'examboard'), 3);    
        
        $mods = get_fast_modinfo($examboard->course)->get_cms();
        
        $output .= $this->output->container_start('submissionsummary');
        $links = '';
        foreach(array('gradeable', 'proposal', 'defense') as $field) {
            if($examboard->$field) {
                foreach($mods as $cmid => $cm) {
                    if($cm->idnumber == $examboard->$field) {
                        break;
                    }
                }
                if($link = $this->gradeable_link($cm, $user->id, get_string($field, 'examboard'))) {
                    $links .= $link.'  ';
                }
            }
        }
        $output .= $this->output->container($links, ' gradeablelinks ');
        
        $exam->load_board_members();
        $exam->load_examinees();
        $exam->load_tutors();
        
        //print_object($exam);
        
        // now file / upload links  
        // view file links ul / upload file button
        $uploads = '';
        
        $isactive = $exam->is_active_member();
        $isgrader = $exam->is_grader();
        // the examinees table item id
        $examineeitemid = $exam->examinees[$user->id]->eid;
        $examineetutorid = reset($exam->tutors[$user->id])->tid;
        
        if(($canmanage || $isgrader) && $examineeitemid) {
            $uploads .= $this->show_files_area($user, $examineeitemid, 'examination', $isactive);
            $uploads .= $this->show_files_area($user, $examineeitemid, 'board', $isactive);
        }
        
        if($canmanage && !empty($exam->members)) {
            foreach($exam->members as $grader) {
                $grade = examboard_get_grader_grade($exam->id, $user->id, true, $grader->id);
                $uploads .= $this->show_files_area($user, $grade->id, 'member', 
                                                    ($grade->grader === $USER->id), fullname($grader));
            }     
        } elseif($isgrader) {
            $grade = examboard_get_grader_grade($exam->id, $user->id, true);
            $uploads .= $this->show_files_area($user, $grade->id, 'member', $isgrader);
        }
        
        if($examineetutorid) {
            $uploads .= $this->show_files_area($user, $examineetutorid, 'tutor', $isactive);
        }
        
        $output .= $this->output->container($uploads, ' fileviewupload ');
        $output .= $this->output->container_end();
        
        */
        
        // only show interface if real grader, not management
        $isgrader = $exam->is_grader();
        if($isgrader) {
            require_once($CFG->dirroot . '/mod/examboard/grading_form.php');
        
            $grade = examboard_get_grader_grade($exam->id, $user->id, true);
            
            $gradingdisabled = examboard_grading_disabled($examboard, $user->id);
            
            $params = array('userid' => $user->id,
                            'gradingdisabled' => $gradingdisabled,
                            'gradinginstance' => examboard_get_grading_instance($examboard, $user->id, $grade, $gradingdisabled),
                            'examboard' => $examboard,
                            'currentgrade' => $grade,

            );
            
            $mform = new \mod_examboard_grade_form(null, $params, 'post', '', array('class'=>'gradeform'));
            
            $output .= $this->output->box_start('boxaligncenter gradeform');
            $output .= $this->moodleform($mform);
            $output .= $this->output->box_end();
        }
    
        return $output;
    }

    /**
     * Generates the grading explanation page for advanced grading
     *
     * @param object $exam examination object
     * @param object $grade record from the DB
     * @return string
     */
    public function show_files_area($user, $itemid, $filearea, $canupload = false, $name = '') {
        global $CFG, $USER;
    
//        $canupload = true;
        $contextid = $this->page->context->id;        
        
        $fs = get_file_storage(); 
        $files = '';
        $upload = $this->output->spacer();
        
        if($files = $fs->get_directory_files($contextid, 'mod_examboard', 
                                                $filearea, $itemid, '/', true, true)) {
            foreach($files as $fid => $file) {
                $filename = $file->get_filename();
                $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', 
                                        '/'.$contextid.'/mod_examboard/'.$filearea.'/'.$itemid.'/'.$filename, 0);
                $strexamfile = get_string('downloadfile', 'examboard', $filename);      
                $icon = $this->pix_icon(file_extension_icon($filename), $strexamfile, 'moodle', 
                                            array('class'=>'icon', 'title'=>$filename));        
                $files[$fid] = html_writer::link($url, $icon.$filename);
            }
        }
        
        // if nothing to show, don't even show the title
        if(!$files && !$canupload) {
            return '';
        }
        
        $files = $this->output->container(html_writer::alist($files), 'fileslist') ;

        $title = get_string('upload_'.$filearea, 'examboard');
        
        if($canupload) {
            $url = new moodle_url('/mod/examboard/edit.php', array('id' => $this->page->cm->id));
            $url->param('action', 'upload_'.$filearea);
            $url->param('item', $itemid);
            $url->param('user', $user->id);
            $icon = new pix_icon('i/upload', get_string('uploadmanagefiles', 'examboard', $title));
            //$upload = $this->output->container($this->output->action_icon($url, $icon));
            $upload .= $this->output->action_icon($url, $icon);        
        }
        
        return $this->output->container($title.$upload.$name.$files, $filearea.'files');
    }
    
    /**
     * Generates the grading explanation page for advanced grading
     *
     * @param object $exam examination object
     * @param object $grade record from the DB
     * @return string
     */
    public function view_grading_explanation($exam, $grade, $user, $grader) {
        global $CFG, $USER;

        $examboard = $this->page->activityrecord;
        $context = $this->page->context;
        if($USER->id != $grade->userid) { 
            require_capability('mod/examboard:manage', $context);
        }
        
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $gradingmanager = get_grading_manager($context, 'mod_examboard', 'examinations');
        $hasgrade = ($examboard->grade != GRADE_TYPE_NONE );
        if ($hasgrade) {
            if ($controller = $gradingmanager->get_active_controller()) {
                $menu = make_grades_menu($examboard->grade);
                $controller->set_grade_range($menu, $examboard->grade > 0);
                $gradefordisplay = $controller->render_grade($this->page,
                                                                $grade->id,
                                                                examboard_get_grade_item($examboard->id, $examboard->course),
                                                                '',
                                                                false);

            }
        }

        //print_object($gradefordisplay);
        
        $output = '';
        
        $title = get_string('gradinguser', 'examboard', $this->format_exam_name($exam));
        $output .= $this->heading($title, 2);    
        
        $output .= $this->output->container_start('usersummary');
        $output .= $this->output->box_start('boxaligncenter usersummarysection');
        $output .= $this->output->user_picture($user);
        $output .= $this->output->spacer(array('width'=>30));
        
        $urlparams = array('id' => $user->id, 'course'=>$examboard->course);
        $url = new moodle_url('/user/view.php', $urlparams);
        $fullname = fullname($user);
        $extrainfo = array();
        foreach(get_extra_user_fields($this->page->context) as $extrafield) {
            $extrainfo[] = $user->$extrafield;
        }
        if (count($extrainfo)) {
            $fullname .= ' (' . implode(', ', $extrainfo) . ')';
        }
        $output .= $this->output->action_link($url, $fullname);
        
        $output .= $this->output->box_end();
        $output .= $this->output->container_end();

        $output .= $this->output->container_start('feedback');
        $output .= $this->output->box_start('boxaligncenter feedbacktable');
        $t = new \html_table();

        if ($grader) {
            // Grader.
            $row = new \html_table_row();
            $cell1 = new \html_table_cell(get_string('gradedby', 'assign'));
            $userdescription = $this->output->user_picture($grader) .
                               $this->output->spacer(array('width'=>30)) .
                               fullname($grader);
            $cell2 = new \html_table_cell($userdescription);
            $row->cells = array($cell1, $cell2);
            $t->data[] = $row;
        }

        // Grade.
        if (isset($gradefordisplay)) {
            $row = new \html_table_row();
            $cell1 = new \html_table_cell(get_string('grade'));
            $cell2 = new \html_table_cell($gradefordisplay);
            $row->cells = array($cell1, $cell2);
            $t->data[] = $row;

            // Grade date.
            $row = new \html_table_row();
            $cell1 = new \html_table_cell(get_string('gradedon', 'assign'));
            $cell2 = new \html_table_cell(userdate($grade->timemodified));
            $row->cells = array($cell1, $cell2);
            $t->data[] = $row;
        }
        

        $output .= html_writer::table($t);
        $output .= $this->output->box_end();

        $output .= $this->output->container_end();
        
        
        $url = new moodle_url('/mod/examboard/view.php', array('id' => $examboard->cmid,
                                                                'view' => 'exam',
                                                                'item' => $exam->id,
                                                                ));
        $output .= $this->single_button($url, get_string('returntoexam', 'examboard'), 'post',
                                                array('class' => 'continuebutton'));
        
        
        return $output;
    }
    
    
    /**
     * Adds formatted examperiod to exam records in an array
     *
     * @param array $exams collection of exam records
     * @return array
     */
    public function helper_format_examperiod($exams) {
    
        // would be possible with array_map, but options needed to be load before
        $options = get_config('examboard', 'examperiods');
        $examperiods = array();
        foreach(explode("\n", $options) as $conv) {
            $key = strstr(trim($conv), ':', true);
            $examperiods[$key] = ltrim(strstr($conv, ':'), ':');
        }
    
        $none = get_string('none');
    
        foreach($exams as $key => $exam) {
            if(array_key_exists($exam->examperiod, $examperiods)) {
                $exam->examperiod = $examperiods[$exam->examperiod];
            } else {
                $exam->examperiod = $none;
            }
            $exams[$key] = $exam;
        }
        return $exams;
    }
    
    
    
    /**
     * Generates the view single board page
     *
     * @param object $board record from DB with board info
     * @param object $url moodle url for actions
     * @param committee $committee object with members data 
     
     * @param array $otherexams collection of exam records this board can be assigned
     *              excluded those examd with grades or where members are tutors 
     *              Arrays indexed by examid  and conaininh title, idnumber and sessionname  
     * @return string
     */
    public function view_board($board, $url, $committee, $otherexams) {
        $output = '';
        
        $name = $board->title.' '.$board->idnumber;
        $output .= $this->heading(format_string($name), 2);
        if($board->name) {
            $output .= $this->heading(format_text($board->name), 4);
        }

        $editurl = new moodle_url('/mod/examboard/edit.php', $url->params());
        $editurl->param('board', $board->id);

        $editurl->param('action', 'boardtoggle');
        $active = $board->active ? get_string('yes') : get_string('no'); 
        if($committee->canmanage) {
            $action = $board->active ? get_string('inactive', 'examboard') : get_string('active', 'examboard'); 
            $icon = $board->active ? 'i/hide' : 'i/show';
            $confirm = $board->active ? get_string('boardhide', 'examboard') : get_string('boardshow', 'examboard'); 
            $confirmaction = new \confirm_action($confirm);
            // Confirmation JS.
            $this->page->requires->strings_for_js(array('boardhide', 'boardshow'), 'examboard');
            $icon = new pix_icon($icon, $action, 'core', array());
            $active .=  '&nbsp; '.$this->output->action_icon($editurl, $icon, $confirmaction);
        }
        
        $output .= $this->box(html_writer::span(get_string('boardactive', 'examboard'), 'label').
                                            ' &nbsp '.$active, 'iteminfo'); 
        //$committee->assignedexams = $this->helper_format_examperiod($committee->assignedexams);
        $output .= $this->view_board_exams(clone($url), $committee->assignedexams, get_string('assignedexams', 'examboard'));
        
        if($hasmembers = !empty($committee->members)) {
            $output .= $this->view_board_table($board, $editurl, $committee);
        } else {
            $output .= $this->heading(get_string('nothingtodisplay'));
        }
        
        //$otherexams = $this->helper_format_examperiod($otherexams);
        
        $output .= $this->box($this->view_board_buttons($url, $editurl, $committee, $otherexams), 'generalbox pagebuttons');
        
        return $output;
    }
    
    /**
     * Prints a list of exam names with header
     *
     * @param array $exams collection of exams
     * @param string $label the string tu use as label
     * @return string
     */
    public function view_board_exams($url, $exams, $label) {
        $output = '';
        
        $url->param('view', 'exam');
        //$names = array_map(array($this, 'format_exam_name'), $exams);
        foreach($exams as $eid => $exam) {
            $url->param('item', $eid);
            $name = $this->format_exam_name($exam);
            $exams[$eid] = html_writer::link($url, $name);
        }
        
        $output .= $this->box_start('iteminfo');
            $output .= $this->box($label, 'label'); 
            $output .= $this->box(html_writer::alist($exams), 'nameslist');
        $output .= $this->box_end();
        return $output;
    }
    
    /**
     * Generates the menu to select naming format: by first or last name
     *
     * @param moodle_url $baseurl the viewer url with params
     * @return string
     */
    public function view_name_sort_menu($baseurl) {
        global $SESSION;
    
        $output = '';

        if(isset($SESSION->nameformat)) {
            $userorder = $baseurl->get_param('uorder'); 
            $options = array(   0 => get_string('firstname'),
                                1 => get_string('lastname'));

            $nf = $this->single_select($baseurl, 'uorder', $options, $userorder, null, null, array('label'=>get_string('userorder', 'local_ulpgcgroups')));                            
            // groupselector class because should pair with groups menu, if used
            $output .= $this->container($nf, 'groupselector namingmenu');
        }                    
      
        return $output;
    }
    
    /**
     * Filters to select exams by a number or criteria
     *
     * @param moodle_url $baseurl the viewer url with params
     * @return string
     */
    public function view_exams_filters($baseurl, $filters) {
        global $DB;
        
        $output = '';
        $examboard = $this->page->activityrecord;
        $context = $this->page->context;   
        $none = array('' => get_string('any'));
        
        foreach($filters as $key => $filter) {
            $selected = optional_param($filter->param, $filter->default, $filter->paramtype);
            $select = new single_select($baseurl, $filter->param, $none + $filter->options, $selected, null);
            $select->set_label($filter->label);
            $select->method = 'post';
            $output .= $this->render($select); 
        
        } 
 
        $caption = get_string('filtersheader', 'examboard').'  &nbsp; ';
        return print_collapsible_region($output, 'tablefilters', 'tablefilters_'.$examboard->id, $caption, 'tablefilters_'.$examboard->id, false, true); 
     }
    
    
    static public function format_examperiod_names() {
        $periods = get_config('examboard', 'examperiods');
        $names = array();
        foreach(explode("\n", $periods) as $period) {
            $parts = explode(':', $period);
            $names[trim($parts[0])] = trim($parts[1]); 
        }
        return $names;
    }
    
    public function format_exam_name($exam, $title = false) {
        $name = '';
        if($title) {
            $name = $exam->title.' ';
        }
        
        $name .= $exam->idnumber;
        $specialty = '';
        if(isset($exam->examperiod) && $exam->examperiod) {
            $periods = self::format_examperiod_names();
            $specialty .= $periods[$exam->examperiod];
        }
        if(isset($exam->sessionname) && $exam->sessionname) {
            $separator = ($specialty) ? '-' : '';
            $specialty .= $separator.$exam->sessionname;
        }
        
        if($specialty) {
            $name .= ' ('.$specialty.')';
        }
        
        return $name;
    }
    
    
    public function show_select_button_form($url, $options, $select, $action) {
        $output = '';
    
        $output .= $this->container_start('actionselect '. $select);
        
        $attributes = array('id'=> 'examboardactionform_'.$select,
                            'action' => $url,
                            'method' => 'post');
        $output .= html_writer::start_tag('form', $attributes); 
        $output .= html_writer::input_hidden_params($url, array($select)); 

        reset($options);
        $selected = key($options);
        $output .= html_writer::label(get_string('choose'.$select, 'examboard'), $select);
        $output .= html_writer::select($options, $select, $selected, false);
        $output .= ' &nbsp; ';
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string($action, 'examboard'), 'name' => 'submitted_'.$select));      
       
        $output .= $this->container_end();
        
        return $output;
    }
    
    
    public function show_select_action_form($url, $exams) {
        $output = '';
    
        $output .= $this->container_start('actionselect');
        $attributes = array('id'=> 'examboardactionform',
                            'action' => $url,
                            'method' => 'post');
        $output .= html_writer::start_tag('form', $attributes); 
        $output .= html_writer::input_hidden_params($url, array('exam', 'action')); 

        reset($exams);
        $selected = key($exams);
        $output .= html_writer::label(get_string('chooseexam', 'examboard'), 'exam');
        $output .= html_writer::select($exams, 'exam', $selected, false);
        $output .= ' &nbsp; ';
        
        $actions = array('editexam' =>   get_string('editexam', 'examboard'),
                            'addexamsession' =>   get_string('addexam', 'examboard'),
                            'delexamsession' =>   get_string('delexam', 'examboard'),
                            'boardconfirm' =>   get_string('boardconfirm', 'examboard'),
        );
        $output .= html_writer::label(get_string('chooseaction', 'examboard'), 'action');
        $output .= html_writer::select($actions, 'action', false, false);
        
        
        $output .= ' &nbsp; ';
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('go', 'examboard'), 'name' => 'submitted_'.$select));      
        

        $output .= $this->container_end();
        
        return $output;
    }

    
    
    /**
     * Generates the main action buttons of the page, according to capabilities
     *
     * @param object $course The course record
     * @param array $examboard Array conting examboard data
     * @param object $cm Course Module cm_info
     * @param object $context The page context
     * @param array of exam records from db
     * @return string
     */
    public function view_page_buttons($url, $canmanage, $hassubmits, $hasconfirms) {
        $output = ''; 
        
        if($canmanage) {
            $url->param('action', 'addexam');
            $output .= $this->single_button($url, get_string('addexam', 'examboard'), 'post',
                                                    array('class' => 'continuebutton'));
        }
        
        if($hassubmits) {
            //students can submit from here
            $url->param('action', 'submit');
            
            // DO NOT use submitt for the moment. Just gradeables.
            //$output .= $this->show_select_button_form($url, $hassubmits, 'exam', 'submit');
        }
        
        
        
        
        if($hasconfirms) {
            //graders can confirm here
            $url->param('action', 'boardconfirm');
            //$output .= $this->show_select_button_form($url, $hasconfirms, 'exam', 'boardconfirm');
            //$output .= $this->show_select_action_form($url, $hasconfirms);
            
        }
        
        return $output;
    }
    
    
    /**
     * Generates the main action buttons of the board page
     *
     * @param object $url of the module view The course record
     * @param object $editurl url for managing editing
     * @param array of exam records from db
     * @return string
     */
    public function view_board_buttons($url, $editurl, $committee, $otherexams) {
        $output = ''; 
        
        $output .= $this->single_button($url, get_string('returntoexams', 'examboard'), 'post',
                                                array('class' => 'continuebutton'));
        
        if($committee->canmanage) {
            $name = $committee->members ? 
                            get_string('editmembers', 'examboard') : get_string('addmembers', 'examboard');
            $editurl->param('action', 'editmembers');
            $output .= $this->single_button($editurl, $name, 'post',
                                                    array('class' => 'continuebutton'));
                            
            if($committee->members && $otherexams) {
                //managers 
                foreach($otherexams as $eid => $exam) {
                    $otherexams[$eid] = $this->format_exam_name($exam);
                }
                
                $editurl->param('action', 'assignexam');
                $output .= $this->show_select_button_form($editurl, $otherexams, 'exam', 'assignexam');
            }
            
            if($committee->members) {
                $options = array(
                    EXAMBOARD_USERTYPE_MEMBER   => get_string('usermembers', 'examboard'),
                    EXAMBOARD_USERTYPE_TUTOR    => get_string('usertutors', 'examboard'),
                    EXAMBOARD_USERTYPE_STAFF    => get_string('userstaff', 'examboard'),
                    EXAMBOARD_USERTYPE_ALL      => get_string('userall', 'examboard'),
                    );
                foreach($committee->members as $member) {
                    $options[$member->uid] = fullname($member);
                }
                
                $editurl->param('action', 'notify');
                $output .= $this->show_select_button_form($editurl, $options, 'usertype', 'notify');
            }
        }
                                                
        return $output;    
    }
    

    
    public function format_board_name($exam, $addlink) {
        $output = '';
        $output .= $exam->title . '<br />'; 
        $output .= html_writer::span($exam->idnumber, 'boardidnumber');
        
        if($addlink) {
            $url = clone $addlink;
            $url->param('view', 'board');
            $url->param('item', $exam->boardid);
            $output = html_writer::link($url, $output); 
        }
        
        if($exam->name) {
            $output .= '<br />' . $exam->name;
        }
        
        return $this->box($output, 'boardname');
    }

    
    public function render_board_name(board_name $tribunal, $addlink) {
        $output = '';
        $output .= $tribunal->title . '<br />'; 
        $output .= html_writer::span($tribunal->idnumber, 'boardidnumber');
        
        if($addlink) {
            $url = clone $addlink;
            $url->param('view', 'board');
            $url->param('item', $exam->boardid);
            $output = html_writer::link($url, $output); 
        }
        
        if($tribunal->name) {
            $output .= '<br />' . $tribunal->name;
        }
        
        return $this->box($output, 'boardname');
    }
    

    public function show_exam_placedate($exam, $edit = false) {
        $output = '';
        if($exam->venue) {
            $tmpl = new \core\output\inplace_editable('mod_examboard', 'venue', $exam->id, 
                            $edit, format_text($exam->venue), $exam->venue, 
                            get_string('editchangetext', 'examboard'),  get_string('editchangenewvalue', 'examboard', format_text($exam->venue)));
            $output .= $this->box($this->render($tmpl), 'sessionvenue');
        }
        
        if($exam->accessurl) {
            $output .= $this->box(\html_writer::link($exam->accessurl, get_string('accessurltext', 'examboard')), 'sessionurl');
        }
        
        if($exam->examdate) {
            $output .= $this->box(userdate($exam->examdate), 'sessiondate');
        }

        
        if($exam->duration) {
            $tmpl = new \core\output\inplace_editable('mod_examboard', 'duration', $exam->id, 
                            $edit, '('.format_time($exam->duration).')', $exam->duration/3600, 
                            get_string('editchangetext', 'examboard'),  get_string('editchangenewvalue', 'examboard', format_string($exam->duration)));
                            $time = $this->render($tmpl);
            $output .= $this->box($time, 'sessiondate');
        }
        return $output;
    }    
    
    public function show_exam_session($exam, $edit = false) {
        $output = '';
        if($exam->examperiod) {
            $periods = self::format_examperiod_names();
            $output .= $this->box($periods[$exam->examperiod], 'sessionperiod');
        }
        
        //($table->canmanage || $table->canedit)
        if($exam->sessionname) {
            $tmpl = new \core\output\inplace_editable('mod_examboard', 'sessionname', $exam->id, 
                            $edit, format_string($exam->sessionname), $exam->sessionname, 
                            get_string('editchangetext', 'examboard'),  get_string('editchangenewvalue', 'examboard', format_string($exam->sessionname)));
            $output .= $this->box($this->render($tmpl), 'sessionname');
        }
        return $output;
    }    

    
    
    public function render_exam_session(exam_session $session) {
        $output = '';
        if($session->venue) {
            $output .= $this->box($session->venue, 'sessionvenue');
        }
        if($session->acessurl) {
            $output .= $this->box(\html_writer::link($exam->accessurl, get_string('accessurltext', 'examboard')), 'sessionurl');
        }
        
        if($session->examdate) {
            $output .= $this->box(userdate($session->examdate), 'sessiondate');
        }
        if($session->duration) {
            $output .=$this->box('('.format_time($session->duration).')', 'sessiondate');
        }
        return $output;
    }
    
    
    public function render_committee(committee $board) {
        global $USER;
        $output = '';
        
        if(!$board->active && !$board->canmanage && (empty($board->members) || !in_array($USER->id, array_keys($board->members)))) {
            return $output;
        }
        
        $class = $board->active ? '' : ' dimmed ';
        $output .= $this->container_start( $class);
        
        foreach($board->members as $user) {
            if($user->sortorder == 0) {
                $label = $board->chair;
            } elseif($user->sortorder == 1) {
                $label = $board->secretary;
            } else {
                $label = $board->vocal.'&nbsp'.($user->sortorder - 1);
            }
            
            $confirm = $user->confirmed ? html_writer::tag('span', '<i class="fa check-square-o"></i>') : '';
            $output .= $this->container_start();
            $output .= $label.'<br />'.$this->format_name($user, $board->is_downloading). '  '. $confirm ;
            $output .= $this->container_end();

        }
        
        $output .= $this->container_end();
        
        return $output;
    }
    
    
    public function format_name($user, $downloading, $field = 'userid') {
        $name = fullname($user); 
        if(!$downloading) {
            $url = new moodle_url('/user/view.php', array('id' => $user->$field, 'course' => $this->page->course->id));
            $name = $this->user_picture($user, array('size'=>24)).' '.html_writer::link($url, $name);
        }
        return $name;
    }
    
    
    public function render_examinee_list(examinee_list $list) {
        global $USER;
        
        $output = '';    

        $examinees = array();
            
        $url = new moodle_url('/mod/examboard/edit.php', array('id' => $this->page->cm->id));
        
        foreach($list->users as $uid => $user) {
            $name = $this->format_name($user, $list->is_downloading); 
            $examinee = html_writer::div($name, 'examineename');
            $tutor = '';
            $other = '';
            if(isset($list->tutors[$uid][0]) && $list->tutors[$uid][0]->main) {
                //we have a main tutor
                $name = $this->format_name($list->tutors[$uid][0], $list->is_downloading, 'tutorid'); 
                $label = html_writer::div($list->tutor, 'tutortitle');   
                $tutor = html_writer::div($name, 'tutorname');   
                $tutor = html_writer::div($label.$tutor, 'tutor');   
                array_shift($list->tutors[$uid]);
            }
            //$url->param('action', 'upload_tutor');
            if(isset($list->tutors[$uid])) {
                $label = html_writer::div(' ', 'tutortitle');
                if(!empty($list->tutors[$uid])) {
                    foreach($list->tutors[$uid] as $k => $other) {
                        $upload = '';
                        /*
                        if(($other->main == 1) && ($other->tutorid == $USER->id) && $list->canupload) {
                            $url->param('item', $other->tid);
                            $url->param('user', $other->userid);     
                            $title = get_string('upload_tutor', 'examboard');
                            $icon = new pix_icon('i/upload', get_string('uploadmanagefiles', 'examboard', $title));
                            $upload = $this->output->spacer().$this->output->action_icon($url, $icon);                          
                        }
                        */
                        $list->tutors[$uid][$k] = html_writer::div($this->format_name($other, $list->is_downloading, 'tutorid').$upload, 'tutorname'); 
                    }
                    $other = html_writer::div(implode("\n", $list->tutors[$uid]), 'tutorname' );
                    $other = html_writer::div($label.$other, 'tutor');   
                }
            }
            
            $examinees[$uid] = $user->userlabel ? html_writer::span($user->userlabel, 'userlabel') : '';
            //$url->param('action', 'upload_user');
            /*
            if(($user->userid == $USER->id) && $list->canupload) {
                $url->param('user', $user->userid);     
                $url->param('item', $user->eid);
                $title = get_string('upload_user', 'examboard');
                $icon = new pix_icon('i/upload', get_string('uploadmanagefiles', 'examboard', $title));
                $upload = $this->output->spacer().$this->output->action_icon($url, $icon);                          
                $examinees[$uid] .=  $upload;
            }
            */
            
            if($content = $tutor.$other) {
                $examinees[$uid] .= print_collapsible_region($content, 'tutors', 
                                                                'tutorlist_'.$list->examid.'_'.$uid, 
                                                                $examinee.'&nbsp;', 
                                                                'tutorlist_'.$list->examid, true, true); 
            } else {
                $examinees[$uid] .= html_writer::div($examinee, 'collapsibleregion');
            }
            
            
            //$examinee. html_writer::div($tutor.$other, 'tutors');   ;
        }
        
        foreach($examinees as $uid => $user) {
            $class = ' examinee ';
            $class .= $list->users[$uid]->excluded ? ' dimmed ' : '';
            $examinees[$uid] = html_writer::div($user, $class);
        }
        
        
        return implode("\n", $examinees);;
    }
    
    
    public function format_exam_grades($grades, $cangrade) {
        $output = '';    

        //print_object($grades);
        
        
        //$output .= '  aquí van las calificaciones ';
        return $output;
    }
    
    public function display_confirmation($userid, $confirms, $editurl, $requireconfirm = false, $defaultcornfirm = true, $canmanage = false) {
        global $USER;
        
        $output = '';    
        if($confirms == 0 && !$requireconfirm) {
            return '';
        }
        
        $button = '';
        if($USER->id == $userid || $canmanage) {
            $confirm = ($confirms == 0) ?  $defaultcornfirm : (end($confirms))->confirmed;
            $name = $confirm ? get_string('unconfirm', 'examboard') : 
                                get_string('confirm', 'examboard');
            $editurl->param('action', 'boardconfirm');
            $button = $this->single_button($editurl, $name, 'post',
                                                    array('class' => 'continuebutton'));
            $editurl->remove_params('action');
        }        
        
        if($confirms == 0) {
            // this means requireconfirm is true
            $confirm = $defaultcornfirm ? 'check-square-o'  : 'square-o';
            return html_writer::span('<i class="fa fa-'.$confirm.'"> </i>').$button;
        }
        
        // if we are here $confirm is a full record
        $output = array();
        foreach($confirms as $i => $confirm) {
            $confirmed = $confirm->confirmed ?  'check-square alert-success' : 'times-circle alert-error';
            $output[$i]  = html_writer::span('<i class="fa fa-'.$confirmed.'"> </i>');
            
            if($canmanage) {
                $timechanged = $confirm->confirmed ? $confirm->timeconfirmed : $confirm->timeunconfirmed;
                if($timechanged) {
                    $output[$i] .= ' '.userdate($timechanged);
                }
                if($confirm->discharge) {
                    $content = format_text($confirm->dischargetext, $confirm->dischargeformat);
                    $available = $confirm->available ?  'check-square alert-success' : 'times-circle alert-error';
                    $content .= html_writer::span(get_string('confirmavailable', 'examboard').
                                                    ' <i class="fa fa-'.$available.'"> </i>');
                    $output[$i] .= ' '.print_collapsible_region($content, ' ', 'examboard_confirm_'.$confirm->id,
                                                                get_string('discharge_'.$confirm->discharge, 'examboard'),
                                                                'examboard_confirm_list', false, true);  
                }
            }
        }
        
        return implode('<br />', $output).$button;
    }
    
    public function display_notifications($notifications) {
        global $USER;
    
        $output = '';    
        if($notifications) {
            $output .= $this->box_start('examnotices');
            $notices = array_map(array($this, 'format_notification'), $notifications);
            $output .= $this->box(implode('<br />', $notices), 'notices');
            $output .= $this->box_end();
        }

        return $output;
    }

    
    public function format_notification($notification) {
        global $CFG;
        $contextid = $this->page->context->id;        
        
        $fs = get_file_storage(); 
        $date = userdate($notification->timeissued);
        $files = '';
        //print_object("contextid, component, filearea, itemid, filepath, ");
        //print_object("contextid: $contextid, component: mod_examboard, filearea: notification, itemid: {$notification->id}, filepath /      ");
        if($files = $fs->get_directory_files($contextid, 'mod_examboard', 
                                                'notification', $notification->id, '/', true, true)) {
            //print_object($files);                                    
            foreach($files as $fid => $file) {
                $filename = $file->get_filename();
                $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', 
                                        '/'.$contextid.'/mod_examboard/notification/'.$notification->id.'/'.$filename, 0);
                $strexamfile = get_string('downloadfile', 'examboard', $filename);                                        
                $icon = $this->pix_icon('f/pdf-32', $strexamfile, 'moodle', array('class'=>'icon', 'title'=>$filename));        
                $files[$fid] = html_writer::link($url, $icon);
            }
            
        }
        $files = '  '.implode(', ', $files);
        //print_object($files);
    
        
        return $date.' &nbsp; '.$files;
    }
    
    
/////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////






/////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Render the grading table.
     *
     * @param object $board record
     * @param object $editurl moodle url for actions
     * @param committee committee object with names and data
     * @return string
     */
    public function view_board_table($board, $editurl, $committee) {
        global $USER;
        
        $output = '';
        
        $table = new \html_table();
        $table->attributes['class'] = ' generaltable singleboardtable ';
        $table->summary = '' ;
        
        $table->data = array();

        $accessiblecell = new \html_table_cell();
        $accessiblecell->header = true;
        $accessiblecell->scope = 'col';
        
        $header = array('role'   => get_string('memberrole', 'examboard'),
                        'name'   => get_string('membername', 'examboard'),
                        'action' => get_string('action'),
                        'exam'   => get_string('session', 'examboard'),
                        'status' => get_string('boardstatus', 'examboard'),

                        );
        $countexams = count($committee->assignedexams);
        $countmembers = count($committee->members);
        if(!$committee->canmanage) {   
            unset($header['action']);
        }
        if($countexams < 2) {
            unset($header['exam']);
        }
                        
        $lambda = function ($value) use ($accessiblecell) {
                        $accessiblecell->text = $value;     
                        return clone $accessiblecell;
                  };
        $table->head = array_map($lambda, $header);
        $table->align = array();
        $table->size = array();
        
        $deputy = clone $table;
                      
        $rolestr = array();
        $rolestr[0] = $committee->chair;
        $rolestr[1] = $committee->secretary;
        foreach(range(2, count($committee->members)) as $idx) {
            $rolestr[$idx] = $committee->vocal.' '.($idx-1);
        }

        $userurl = new moodle_url('/user/view.php', array('course' =>  $this->page->course->id));
        $lastmember = 0;
        $maxusers = count($committee->members);
        //$editurl->param('boardid', $committee->id);
        foreach($committee->members as $uid => $member) {
            $editurl->param('user', $uid);
            $columns = array();
            $columns['role'] = $rolestr[$member->sortorder];
            $userurl->param('id', $uid);
            $columns['name'] = $this->user_picture($member).' '.html_writer::link($userurl, fullname($member));
            if($committee->canmanage) {   
                $action = '';       
                $attributes = array();     
                
                
                
            // delete
                $deleteaction = new \confirm_action(get_string('userdeleteconfirm', 'examboard'));
                // Confirmation JS.
                $this->page->requires->strings_for_js(array('deleteallconfirm', 'userdeleteconfirm'), 'examboard');
                $editurl->param('action', 'delmember');
                $icon = new pix_icon('i/delete', get_string('deleteuser', 'examboard'), 'core', $attributes);
                $action .=  $this->output->action_icon($editurl, $icon, $deleteaction);
            // move buttons
                if($member->sortorder) {
                    $editurl->param('action', 'memberup');
                    $icon = new pix_icon('t/up', get_string('up'), 'core', $attributes);
                    $action .=  ' '.$this->output->action_icon($editurl, $icon);
                }

                if($member->sortorder < ($maxusers - 1)) {
                    $editurl->param('action', 'memberdown');
                    $icon = new pix_icon('t/down', get_string('down'), 'core', $attributes);
                    $action .=  ' '.$this->output->action_icon($editurl, $icon);
                }          
                
                $columns['action'] = $action;
            }
                
            foreach($committee->assignedexams as $examid => $exam) {
                if($countexams > 1) {
                    $columns['exam'] = $this->format_exam_name($exam);
                }
                $confirm = isset($committee->confirmations[$uid][$examid]) ? $committee->confirmations[$uid][$examid] : 0;
                $editurl->param('exam', $examid);
                $columns['status'] = $this->display_confirmation($uid, $confirm, $editurl, $committee->requireconfirm, 
                                                                    $committee->defaultconfirm, $committee->canmanage);
                $editurl->remove_params('exam');
                $columns['notify'] = '';
                if(($committee->canmanage || ($member->userid == $USER->id)) && isset($committee->notifications[$uid][$examid])) {
                    $columns['notify'] = $this->display_notifications($committee->notifications[$uid][$examid]);
                }
            
                $row = new \html_table_row($columns);
                $row->cells[0]->scope = 'row';
                if($member->deputy) {
                    $deputy->data[] = $row;
                } else {
                    $table->data[] = $row;
                }
                $columns['role'] = '';
                $columns['name'] = '';
                $columns['action'] = '';
            }

        }
        
        $output .= html_writer::table($table);

        $caption = html_writer::span(get_string('deputymembers', 'examboard'), 'label');
        $output .= print_collapsible_region(html_writer::table($deputy), 
                                            ' deputytable ', 'board_deputy_table_'.$committee->id, 
                                            $caption, 'board_deputy_table', true, true); 
        return $output;        
    }

    
    
    /**
     * Render the main viewing table.
     *
     * @param assign_grading_table $table
     * @return string
     */
    public function render_examinees_table(\mod_examboard\output\examinees_table $table) {
        global $USER; 
        $output = '';

        $examname = $table->examination->title.' '.$table->examination->idnumber;
        $output .= $this->heading(format_string($examname), 2);
        if($table->examination->name) {
            $output .= $this->heading(format_string($examname), 5);
        }
        
        $output .= $this->heading(get_string('periodlabel', 'examboard', $table->examination->examperiod), 3);
        $output .= $this->heading(get_string('sessionlabel', 'examboard', $table->examination->sessionname), 3);
        if($table->examination->accessurl) {
            $output .= $this->heading(get_string('accessurllabel', 'examboard', \html_writer::link($table->examination->accessurl, get_string('accessurltext', 'examboard'))), 4);
        }

        $usersearch =  $table->access_search_term();
        $numusers = $table->examination->count_examinees($usersearch);        
        
        
        // Prepare table header.
        $columns = array('examinee' => $table->examinee,
                          'tutor'    => $table->tutor,
                          'sortorder'=> get_string('order', 'examboard'),
                          'userlabel'=> get_string('userlabel', 'examboard'),
                          'gradeable'=> get_string('assessment', 'examboard'),
                          'action'   => get_string('action'),);
        if(!$table->usetutors) {
            unset($columns['tutor']);
        }
                          
        $table->define_columns(array_keys($columns));
        $table->define_headers(array_values($columns));
        $table->set_attribute('id', 'mod_examboard_view_board_table');
        $table->set_attribute('class', 'flexible admintable generaltable');
        $table->sortable(true, 'examinee, sortorder', SORT_ASC);
        $table->no_sorting('grade');
        $table->no_sorting('action');
        $table->collapsible(true);
        $table->is_downloadable(false);
       
        $table->pagesize(10, $numusers);  
        $table->setup();
        
        $table->examination->load_examinees_with_tutors($usersearch, $table->get_sql_sort(), $table->get_page_start(), $table->get_page_size()); 
        $table->initialbars(false);

        if(isset($columns['tutor'])) {
            $table->examination->load_tutors($usersearch);
        }
        if(isset($columns['gradeable'])) {
            $table->examination->load_grades();
        }
        
        $table->editurl->param('exam', $table->examination->id);
        $table->viewgradeurl = clone($table->baseurl);
        $table->viewgradeurl->params(array('item' => $table->examination->id,
                                            'view'=>'graded'));
        $gradingurl = clone($table->viewgradeurl);
        $userurl = new moodle_url('/user/view.php', array('course'=>$this->page->course->id));
        foreach($table->examination->examinees as $uid => $user) {
            if($user->excluded && !$table->canmanage) {
                //do not show excluded students to graders non managers
                //continue;
            }
        
            $gradingurl->param('user', $user->id);
            $table->editurl->param('user', $uid);
            $row = array();
            $userurl->param('id', $uid);
            $row['examinee'] = $this->box($this->user_picture($user).' '.html_writer::link($userurl, fullname($user)), 'examinee'); 
            $files = '';
            if(isset($columns['tutor'])) {
                $row['tutor'] = '';
                if(isset($table->examination->tutors[$uid])) {
                    $main = reset($table->examination->tutors[$uid]);
                    if($main->tutorid == $USER->id) {
                        $files = $this->show_files_area($user, $main->tid, 'tutor');
                    }
                    $row['tutor'] = $this->display_user_tutor($table->examination->tutors[$uid], $userurl).$files;
                }
            }
            $row['sortorder'] = $user->sortorder + 1;
            
            $tmpl = new \core\output\inplace_editable('mod_examboard', 'userlabel', $user->eid, 
                            ($table->canmanage || $table->canedit), format_string($user->userlabel), $user->userlabel, 
                            get_string('editchangetext', 'examboard'),  get_string('editchangenewvalue', 'examboard', format_string($user->userlabel)));
            $row['userlabel'] =  $this->render($tmpl);
            
            $finalgrade = -1;
            if(isset($table->examination->grades[$uid])) { 
                $finalgrade = examboard_calculate_grades($table->grademode, $table->mingraders, $table->examination->grades[$uid]);
            }
            
            if(isset($columns['gradeable'])) {
                $gradeables = '';
                $row['gradeable'] = '';
                if($table->hasexternalactivity) {
                    if(!$user->excluded || $table->canmanage) {
                        $gradeables = $this->display_user_gradeables($table, $uid); 
                    }
                }

                if($gradeables || $table->examination->hassubmitted($this->page->context, $uid)) {
                    $gradingurl->param('view', 'submission'); 
                    $row['gradeable'] .= html_writer::link($gradingurl, get_string('viewsubmission', 'examboard'));
                    $gradingurl->remove_params('view');                
                }
                
                if($table->grademax) {
                    $row['gradeable'] .= $this->display_user_grades($table, $uid);
                }
            }

            $action = $this->examinee_table_user_actions($table->editurl, $user, $numusers, $table->canmanage, $table->canedit);
            
            if(($user->userid == $USER->id) && $table->cansubmit) {
                $gradingurl->param('view', 'submit');
                $action .= html_writer::link($gradingurl, get_string('examsubmit', 'examboard'), array('class' =>'btn btn-primary'));
                $gradingurl->remove_params('view');                
            }
            
            if($table->istutor && ($finalgrade < 0) && 
                (time() > $this->page->activityrecord->allowsubmissionsfromdate)) {
                $table->editurl->param('action', 'upload_tutor');
                $table->editurl->param('item', reset($table->examination->tutors[$uid])->tid);
                $action .= html_writer::link($table->editurl, get_string('uploadtutorfiles', 'examboard'), array('class' =>'btn btn-primary'));
                $table->editurl->remove_params('action', 'item');
            }

            if($table->cangrade && ($table->grademax && !$user->excluded)) {
                $gradingurl->param('view', 'grading'); 
                $action .= html_writer::link($gradingurl, get_string('grade', 'examboard'), array('class' =>'btn btn-primary'));
                $gradingurl->remove_params('view');
            }
            
            $row['action'] = $action;
            
            // edit    = https://localhost/moodle39ulpgc/mod/examboard/edit.php?id=3992&uorder=1&exam=60&user=20&action=updateuser
            // grading = https://localhost/moodle39ulpgc/mod/examboard/view.php?id=3992&uorder=1&view=grading&item=60&user=20
            
            $class = $user->excluded  ? ' dimmed excluded ' : ''; 
        
            $table->add_data_keyed($row, $class);
        }

        $output .= $this->flexible_table($table);
        
        if($table->canedit || $table->canmanage) {
            $table->editurl->remove_params('user');
            $output .= $this->box($this->view_examinee_table_buttons($table->editurl, $numusers, $table->canmanage), 'generalbox pagebuttons');
        }
    

        return $output;           
    }
    
    /**
     * Render the tutors for a user.
     *
     * @param array $tutors including names, first is main tutor
     * @return string
     */
    public function display_user_tutor($tutors, $url) {
        global $USER;
        
        $main = '';
        $cotutors = '';
        if($tutors && is_array($tutors)) {
            $main = array_shift($tutors);
            $uid = $main->userid;
            $url->param('id', $main->tutorid);
            $main = $this->box($this->user_picture($main).' '.html_writer::link($url,fullname($main)), 'tutor'); 
            foreach($tutors as $user) {
                $url->param('id', $user->tutorid);
                $cotutors .= $this->box($this->user_picture($user).' '.html_writer::link($url,fullname($user)), 'cotutor'); 
            }
            if($tutors) {
                $cotutors = print_collapsible_region($cotutors, ' ', 'examboard_cotutors_list_'.$uid, 
                                                            get_string('othertutors', 'examboard'), 
                                                            'examboard_cotutors_list', false, true);  
            }
        }
        

        
        return $main.$cotutors;
    }

    
    /**
     * Render the link to a gradeable item
     *
     * @param object $cminfo, course module infor of complementary data
     * @param int $userid, student id
     * @param string $word, word to use in link
     * @return string
     */
    public function gradeable_link($cminfo, $userid, $word) {
        global $DB;
        $link = '';
        $submitflag = true;
    
        $url = new moodle_url("/mod/{$cminfo->modname}/view.php", array('id'=>$cminfo->id));
    
        switch($cminfo->modname) {
            case 'assign'   :   $url->params(array('action' => 'grade', 'userid' => $userid));
                                $select = "assignment = :assignment AND userid = :userid AND latest = 1 AND status <> 'new' ";
                                $submitflag = $DB->record_exists_select('assign_submission', $select, array('assignment' => $cminfo->instance, 'userid'=>$userid));
                        break;

            case 'tracker'  :   $url->param('view','view'); 
                                $url->param('screen','mywork'); 
                                $select = "trackerid = :trackerid AND reportedby = :userid AND status > 0 AND status <> 5 ";
                                $params = array('trackerid' => $cminfo->instance, 'userid'=>$userid);
                                if($submitflag = $DB->record_exists_select('tracker_issue', $select, $params)) {
                                    $issue = $DB->get_records_select('tracker_issue', $select, $params, 'datereported DESC', 'id, trackerid, reportedby, assignedto', 0, 1);
                                    $issue = reset($issue);
                                    $url->param('screen','viewanissue'); 
                                    $url->param('issueid', $issue->id);
                                }
                        break;        

            case 'data'     :
                        break;  
                        
            case 'datalynx' :
            
                        break;  
        }
    
        if($submitflag) {
            $link = $this->action_link($url, $word);
        }
        
        return $link;
    }
    
    
    /**
     * Render the grades for a user in the examinee table
     *
     * @param array $tutors including names, first is main tutor
     * @return string
     */
    public function display_user_gradeables($table, $userid) {
        
        $output = '';

        if($table->gradeable && 
            $link = $this->gradeable_link($table->gradeable, $userid, get_string('gradeable', 'examboard'))) {
            $output .= $link.'<br>';
        }
        
        if($table->proposal && 
            $link = $this->gradeable_link($table->proposal, $userid, get_string('proposal', 'examboard'))) {
            $output .= $link.'<br>';
        }
        
        if($table->defense &&
            $link = $this->gradeable_link($table->defense, $userid, get_string('defense', 'examboard'))) {
            $output .= $link.'<br>';
        }
        
        return $output;
    }
    
    /**
     * Render the grades for a user in the examinee table
     *
     * @param array $tutors including names, first is main tutor
     * @return string
     */
    public function display_user_grades($table, $userid, $short = false) {
        global $USER;
        
        $output = '';
        $files = '';
        
        if(!$table->grademax || !isset($table->examination->grades[$userid]) ) {
            //there are no grades, activity not graded
            return $output;
        }
    
        $grades = $table->examination->grades[$userid];
        
        $finalgrade = '';
        if($grades) {
            $finalgrade = examboard_calculate_grades($table->grademode, $table->mingraders, $grades);
            $finalgrade = $this->display_grade($finalgrade, $table->grademax, $table->gradeitem, false);
            
            if($short) {
                return $this->box($finalgrade, 'finalgrade');
            }

            $bgrades = '';
            $table->viewgradeurl->param('user', $userid);
            $attributes = array('title' => get_string('viewgradingdetails', 'examboard'));
            foreach($grades as $gid => $grade) {
                if($grade->sortorder == 0) {
                    $role = $table->chair;
                } elseif($grade->sortorder == 1) {
                    $role = $table->secretary;
                } else {
                    $role = $table->vocal.' '.($grade->sortorder -1);
                }
                if($grade->grade > 0) {
                    $grade = format_float($grade->grade, $table->gradeitem->get_decimals());
                    $text = $role.': '. $grade;
                    if($table->advancedgrading && ($userid == $USER->id || $table->canmanage)) {
                        $table->viewgradeurl->param('gid', $gid);
                        $text = html_writer::link($table->viewgradeurl, $text, $attributes);
                    }
                    
                    $bgrades .= $this->box($text, 'membergrade');
                }
            }
            
            if($user = $table->examination->examinees[$userid]) {
                $files = $this->show_files_area($user, $user->eid, 'examination');
            } 
            
            //$output .= $this->box($finalgrade, 'finalgrade');
            if($bgrades) {
                $output .= print_collapsible_region($bgrades.$files, '', 'examboard_grades_'.$userid, 
                                                    $finalgrade, 'examboard_grades', false, true); 
            }
        }
        
        return $output;
    }
    
    
    /**
     * Return a grade in user-friendly form, whether it's a scale or not.
     *
     * @param float $grade int|null
     * @param int $grademax the grade settih in the module instance. 
     *             Indicates if graded (!=0) and scales used (negative) 
     * @param stdclass $gradeitem record from grade_item table PLUS scale record as scale
     * @param bool $downloading if the data are been downloaded to a file (vs displayed on screen)
     * @return string User-friendly representation of grade
     */
    public function display_grade($grade, $grademax, $gradeitem, $downloading = false) {  
        $o = '';
        
        if ($grade == -2) {
            return get_string('partialgrading', 'examboard');
        }
        
        
        if ($grademax >= 0) {
            if ($grade == -1 || $grade === null || $grademax == 0) {
                if ($downloading) {
                    return '';
                }
                $o .= '-';
            } else {
                if ($downloading) {
                    return format_float($grade, $gradeitem->get_decimals());
                }
                $o .= grade_format_gradevalue($grade, $gradeitem);
                if ($gradeitem->get_displaytype() == GRADE_DISPLAY_TYPE_REAL) {
                    // If displaying the raw grade, also display the total value.
                    $o .= '&nbsp;/&nbsp;' . format_float($grademax, $gradeitem->get_decimals());
                }
            }
            return $o;
        } else {
            if(!$gradeitem->scale) {
                $o .= '-';
            } else {
                $scaleid = (int)$grade;
                if (isset($scale[$scaleid])) {
                    $o .= $scale[$scaleid];
                    return $o;
                }
                $o .= '-';
            }
        }
        if ($downloading && ($o == '-')) {
            return '';
        }
        return $o;
    }
    

    /**
     * Render the action icons for examinee table
     *
     * @param array $tutors including names, first is main tutor
     * @return string
     */
    public function examinee_table_user_actions($url, $user, $maxusers, $canmanage, $canedit) {
        global $PAGE;
        $action = '';  
        $actions = array();
        $attributes = array(); //array('class' => 'icon');
        if($canmanage) {
        
        // hide/show
            $str = ($user->excluded) ? get_string('include', 'examboard') : get_string('exclude', 'examboard');
            $icon =  ($user->excluded) ? 'i/show' :  'i/hide';
            $toggleaction = ($user->excluded) ? get_string('usershow', 'examboard') : get_string('userhide', 'examboard');
            $toggleaction = new \confirm_action($toggleaction);

            $url->param('action', 'usertoggle');
            $icon = new pix_icon($icon, $str, 'core', $attributes);
            $actions[] = $this->output->action_icon($url, $icon, $toggleaction);
        
        // delete
            $deleteaction = new \confirm_action(get_string('userdeleteconfirm', 'examboard'));
            $url->param('action', 'deleteuser');
            $icon = new pix_icon('i/delete', get_string('deleteuser', 'examboard'), 'core', $attributes);
            $actions[] = $this->output->action_icon($url, $icon, $deleteaction);
            
            }
        if($canedit || $canmanage) {
        // edit 
            $url->param('action', 'updateuser');
            $icon = new pix_icon('i/settings', get_string('updateuser', 'examboard'), 'core', $attributes);
            $actions[] = $this->output->action_icon($url, $icon);

        // move buttons
            if($user->sortorder) {
                $url->param('action', 'userup');
                $icon = new pix_icon('t/up', get_string('up'), 'core', $attributes);
                $actions[] = $this->output->action_icon($url, $icon);
            }

            if($user->sortorder < ($maxusers - 1)) {
                $url->param('action', 'userdown');
                $icon = new pix_icon('t/down', get_string('down'), 'core', $attributes);
                $actions[] = $this->output->action_icon($url, $icon);
            }

            // Confirmation JS.
            $this->page->requires->strings_for_js(array('usershow', 'userhide', 'userdeleteconfirm'), 'examboard');
        }
        
        if($actions) {
            $action = \html_writer::div(implode(' &nbsp; ', $actions));
        }            
        
        return $action;
    }

    
    /**
     * Render the action buttons for a manager in the examinee table
     *
     * @param object $url the url forn editing actions
     * @param int $numusers count of users in the form, students to be assessed
     * @param bool $canmanage if this user can manage exams & allocate users
     * @return string
     */
    public function view_examinee_table_buttons($url, $numusers, $canmanage) {
        $output = '';
    
        $returnurl = new moodle_url('/mod/examboard/view.php', array('id'=>$url->get_param('id')));
        $output .= $this->single_button($returnurl, get_string('returntoexams', 'examboard'), 'post',
                                                array('class' => 'continuebutton'));
                                                
                                               
        if($canmanage) {
            $url->param('action', 'updateuser');
            $output .= $this->single_button($url, get_string('adduser', 'examboard'), 'post',
                                                        array('class' => 'continuebutton'));
        }

        if($numusers) {
            $url->param('action', 'moveusers');
            $output .= $this->single_button($url, get_string('moveto', 'examboard'), 'post',
                                                        array('class' => 'continuebutton'));
            if($numusers > 1) {        
                if($canmanage) {
                    $url->param('action', 'deleteall');
                    $deleteaction = new \confirm_action(get_string('deleteallconfirm', 'examboard'));
                    $output .= $this->single_button($url, get_string('deleteall', 'examboard'), 'post',
                                                            array('class' => 'continuebutton', 
                                                                    'actions' =>array($deleteaction)));            
                }
                //managers can reorder here
                $options = array(EXAMBOARD_ORDER_KEEP   => get_string('orderkeepchosen', 'examboard'),
                                EXAMBOARD_ORDER_RANDOM  => get_string('orderrandomize', 'examboard'),
                                EXAMBOARD_ORDER_ALPHA   => get_string('orderalphabetic', 'examboard'),
                                EXAMBOARD_ORDER_TUTOR   => get_string('orderalphatutor', 'examboard'),
                                EXAMBOARD_ORDER_LABEL   => get_string('orderalphalabel', 'examboard'),
                                );
                $url->param('action', 'reorder');
                $output .= $this->show_select_button_form($url, $options, 'reorder', 'reorder');
            }
        }
        return $output;
    }
    

    /**
     * Render the main viewing table.
     *
     * @param assign_grading_table $table
     * @return string
     */
    public function render_exams_table(\mod_examboard\output\exams_table $viewer) {
        global $USER; 
        
        $output = '';
/*  //TODO   //TODO    //TODO    //TODO    //TODO    //TODO    //TODO       
        combinar estudainets & evaluación en single cell with rows
       $hed = html_writer::div(get_string('examinees', 'examboard'));
       $hed .= html_writer::div(' ee ');
       $hed = $this->output->container($hed);
  */     
        // Prepare table header.
        $table = array('idnumber'       => get_string('codename', 'examboard'),
                        'board'         => get_string('board', 'examboard'),
                        'sessionname'   => get_string('session', 'examboard'),
                        'examdate'      => get_string('examplacedate', 'examboard'),
                        'examinee'      => get_string('examinees', 'examboard'),
                        'grade'         => get_string('assessment', 'examboard'), //get_string('grade'),
                        'action'        => get_string('actions'),
                        );
        if(!$viewer->canmanage) {
            unset($table['action']);
        }
                        
        $viewer->define_columns(array_keys($table));
        $viewer->define_headers(array_values($table));
        $viewer->set_attribute('id', 'mod_examboard_view_exams_table');
        $viewer->set_attribute('class', 'flexible admintable generaltable');
        
        $viewer->sortable(true, 'idnumber, examdate', SORT_ASC);
        $viewer->no_sorting('board');
        $viewer->no_sorting('examinee');
        $viewer->no_sorting('grade');
        $viewer->no_sorting('action');
        $viewer->collapsible(true);

        /*
        $examboard = new stdclass();
        $examboard->id = $viewer->examboardid;
        $examboard->usetutors = true;
        */
        $examboard = $this->page->activityrecord;
        $userid = $viewer->baseurl->get_param('fuser');   
        $viewall = ($viewer->canmanage || $viewer->canviewall);
        
        $viewer->table_filters_setup($viewer->baseurl);
        $filters = $viewer->get_exams_table_filter_values();
        $count = examboard_count_user_exams($examboard, $viewall, $userid, $viewer->groupid, $filters);
        $viewer->pagesize(30, $count);  

        $viewer->setup();
        
        $viewer->examinations = examboard_get_user_exams($examboard, $viewall, $userid, $viewer->groupid, 
                                                            $viewer->get_sql_sort(), $filters, $viewer->get_page_start(), $viewer->get_page_size(), true); 
        $viewer->initialbars(false);
        
        //TODO //TODO //TODO //TODO //TODO //TODO //TODO 
        // add list unassigned boards(if can manage)
        
        $output .= $this->view_name_sort_menu($viewer->baseurl);
        
        if(examboard_count_user_exams($examboard, $viewall, 0, $viewer->groupid) > 1) {
            $output .= $this->view_exams_filters($viewer->baseurl, $viewer->filters);
        }
                                                            
        foreach($viewer->examinations as $examid => $exam) {
            $row = array();
            
            $committee = '';
            $ismember = false;
            $isgrader = $viewer->cangrade;
            if($viewer->cangrade || ($viewer->publishboard) || $viewer->canmanage) {
                //we get all mebers, including deputy for capbility checking 
                $members = $exam->load_board_members();
                if($viewer->cangrade && isset($members[$USER->id]) && $exam->active) {
                    $ismember = true;
                }
                // remove deputy members
                foreach($members as $key => $member) {
                    if($member->deputy) {
                        unset($members[$key]);
                    }
                }
                //Grader only if full member, not deputy
                $isgrader = ($viewer->cangrade && isset($members[$USER->id]) && $exam->active );
                
                $board = new committee($exam->boardid, $exam->boardactive, $members, $viewer->requireconfirm, $viewer->defaultconfirm,  
                                                        $viewer->chair, $viewer->secretary, $viewer->vocal); 
                $board->is_downloading = $viewer->is_downloading();
                $board->canmanage = $viewer->canmanage;
                if($isgrader) {
                    $viewer->hasconfirms[$exam->id] = $this->format_exam_name($exam);
                }
                $committee = $this->render($board);
            } else {
                if($viewer->publishboard && $viewer->publishboarddate) { 
                    $committee = get_string('tobepublishedafter', 'examboard', userdate($viewer->publishboarddate));
                }
            }
            
            $link = ($ismember || $viewer->canmanage) ? $viewer->baseurl : false;
            $tribunal = $this->format_board_name($exam, $link);
            /*
            if($ismember || $viewer->canmanage) {
                $url = clone $viewer->baseurl;
                $url->param('view', 'board');
                $url->param('item', $exam->boardid);
                $tribunal = html_writer::link($url, $tribunal); 
            }*/
            $row[] =  $this->box($tribunal, 'boardname'); 
            $row[] =  $committee;
            
            $viewer->canedit = $ismember && $exam->is_active_member($USER->id);
            $row[] = $this->show_exam_session($exam, $viewer->canmanage || $viewer->canedit);
            $row[] = $this->show_exam_placedate($exam, $viewer->canmanage || $viewer->canedit);

            $examinee_list = new examinee_list($viewer->examinee, $viewer->tutor, $viewer->usetutors, $examid);
            //$ownuser = ($viewall || $isgrader) ? '' : " userid = '{$USER->id}' ";            
            
            $examinee_list->is_downloading = $viewer->is_downloading();
            $examinee_list->users = $exam->load_examinees();
            if($viewer->usetutors) {
                $examinee_list->tutors = $exam->load_tutors();
            }
            // eliminate users that shouldn't be visible, 
            if(!$viewall && !$ismember) {
                foreach($examinee_list->users as $uid => $user) {
                    // either this user is a student or a tutor
                    if( $uid != $USER->id && 
                        !array_key_exists($USER->id, $examinee_list->tutors[$uid])) {
                        //if  not student or tutor, delete
                        unset($examinee_list->users[$uid]);
                        unset($examinee_list->tutors[$uid]);
                    }
                }
            }
            $examinee_list->canupload = $viewer->cansubmit && (time() < $exam->examdate);
            
            $row[] = $this->render($examinee_list);
            
            // 'Grades' column should contain gradeable link && user grades for user, link to assess for others
            $grades = '';
            $useractions = '';
            $accesslabel = '';
            $accessbutton = '';
            $hasgrades = true;
                    $url = clone $viewer->baseurl;
                    $url->param('view', 'exam');
                    $url->param('item', $exam->id);
            
            $examinee = 0;
            if(!$isgrader && $viewer->cansubmit && isset($examinee_list->users[$USER->id])) {
                // this is a student, should see only his grades
                if($exam->active) {
                    $viewer->hassubmits[$exam->id] = $exam->idnumber;
                }
                $examinee = $USER->id;
                $accesslabel = 'examaccess';
            }
            
            if(!$isgrader && ($exam->is_tutor() || $viewall)) {
                $accesslabel = 'examaccess';
            }
            
            
            if(($viewer->publishgrade) || $viewer->canmanage || $isgrader) {
                $exam->load_grades($examinee);
                $userstable = examinees_table::get_from_url($viewer->baseurl);
                $userstable->examination = $exam;
                $userstable->canmanage = $viewer->canmanage;
                $userstable->viewgradeurl = clone($viewer->baseurl);
                $userstable->viewgradeurl->params(array('item' => $userstable->examination->id,
                                                        'view'=>'graded'));
                $examinees = $examinee ? array($examinee => $examinee_list->users[$examinee]) : $examinee_list->users;
                
                $gradingurl = clone($viewer->baseurl);
                $gradingurl->params(array('view' => 'submission', 'item' => $exam->id));
                
                foreach($examinees as $uid => $user) {
                    $gradeables = '';
                    if($examinee && $viewer->hassubmits[$exam->id]) {
                        $gradeables = $this->display_user_gradeables($userstable, $uid); 
                        if($gradeables || $exam->hassubmitted($this->page->context, $uid)) {
                            $gradingurl->param('user', $uid); 
                            $grades .= html_writer::link($gradingurl, get_string('viewsubmission', 'examboard'));
                        }            
                    } 
                    if($usergrades = $this->display_user_grades($userstable, $uid, true)) { 
                        $grades .= html_writer::div($usergrades, 'usergrades');
                    } else {
                        $hasgrades = false;
                    }
                }
                $gradingurl->remove_params('view');                

                // add grade button if user can grade those students
                if(($viewer->hasexternalactivity && $isgrader && $examinee_list->users) && !$viewer->is_downloading()) {
//                    $icon = new pix_icon('i/grades', get_string('gradeusers', 'examboard'));
                    $accesslabel = 'assess';
                }
            }
            
            if($accesslabel) {
                $accessbutton = html_writer::link($url, get_string($accesslabel, 'examboard'), array('class' =>'btn btn-secondary'));
            }
            
            
            if($isgrader && $viewer->canedit) {
                $useractions = $this->exam_edit_actions(clone $viewer->editurl, $exam, $viewer->canmanage, 
                                                            isset($viewer->hasconfirms[$exam->id]), $hasgrades, count($examinee_list->users));
            }
            $row[] =  $grades.$accessbutton.$useractions;


            // now the last 'action' column
            if($viewer->canmanage ) {
                $action = $this->exam_row_actions(clone $viewer->editurl, clone $viewer->baseurl, $exam, $viewer->canedit);
                $row[] = $action;
            }
            
            $class = $exam->active ? '' : ' dimmed '; 
            
            $viewer->add_data($row, $class);
        }

        $output .= $this->flexible_table($viewer);

        $output .= '<div class="clearer clearfix"></div>';

        //// TODO put here buttons
        
        return $output;
    }


    /**
     * Helper method dealing with the fact we can not just fetch the output of flexible_table
     *
     * @param flexible_table $table The table to render
     * @return string HTML
     */
    protected function exam_row_actions($url, $viewurl, $exam, $canedit) {
        global $PAGE;
        
        $action = '';       
        $actions = array();
        $url->param('exam', $exam->id);
        $attributes = array(); //array('class' => 'icon');
        // edit 
            $url->param('action', 'updateexam');
            $icon = new pix_icon('i/settings', get_string('updateexam', 'examboard'), 'core', $attributes);
            $actions[] = $this->output->action_icon($url, $icon);
            
        // visible 
            $name = $exam->active ? 'hide' : 'show';
            $url->param('action', 'exam'.$name);
            $icon = new pix_icon('t/'.$name, get_string($name), 'core', $attributes);
            $toggleaction = ($exam->active) ? get_string('examhide', 'examboard') : get_string('examshow', 'examboard');
            $toggleaction = new \confirm_action($toggleaction);
            
            $actions[] = $this->output->action_icon($url, $icon, $toggleaction);
            
        // delete
            $deleteaction = new \confirm_action(get_string('examdeleteconfirm', 'examboard'));
            // Confirmation JS.
            $PAGE->requires->strings_for_js(array('examdeleteconfirm'), 'examboard');

            $url->param('action', 'deleteexam');
            $icon = new pix_icon('i/delete', get_string('deleteexam', 'examboard'), 'core', $attributes);
            $actions[] = $this->output->action_icon($url, $icon, $deleteaction);
            
            $action .= '<br />';
        // add board
            $viewurl->param('view', 'board');
            $viewurl->param('item', $exam->boardid);
            $icon = new pix_icon('i/enrolusers', get_string('viewboard', 'examboard'), 'core', $attributes);
            $actions[] = $this->output->action_icon($viewurl, $icon);
        // add examinees
            $viewurl->param('view', 'exam');
            $viewurl->param('item', $exam->id);
            $icon = new pix_icon('i/cohort', get_string('viewusers', 'examboard'), 'core', $attributes);
            $actions[] = $this->output->action_icon($viewurl, $icon);
            
        if($actions) {
            $action = \html_writer::div(implode(' &nbsp; ', $actions));
        }            
            
        return $action;
    }

    
    /**
     * Helper method dealing with the fact we can not just fetch the output of flexible_table
     *
     * @param flexible_table $table The table to render
     * @return string HTML
     */
    protected function exam_edit_actions($url, $exam, $canmanage, $hasconfirms, $hasgrades, $numusers) {
        global $PAGE;
        
        $action = '';       
        $actions = array();
        $url->param('exam', $exam->id);
        //$url->remove_params('group');
        $attributes = array(); //array('class' => 'icon');
        // confirm 
            if($hasconfirms && !$hasgrades) {
                $url->param('action', 'boardconfirm');        
                $icon = new pix_icon('i/completion-manual-enabled', get_string('boardconfirm', 'examboard'), 'core', $attributes);
                $actions[] = $this->output->action_icon($url, $icon);
            }
            
        // edit 
            if(!$canmanage && !$hasgrades) {
                $url->param('action', 'updateexam');
                $url->param('manage', 0);
                $icon = new pix_icon('i/settings', get_string('updateexam', 'examboard'), 'core', $attributes);
                $actions[] = $this->output->action_icon($url, $icon);
            }

        // split
            if($numusers > 1) {
                $url->param('action', 'addexam');
                $url->param('manage', 0);
                $icon = new pix_icon('i/addblock', get_string('newexamsession', 'examboard'), 'core', $attributes);
                $actions[] = $this->output->action_icon($url, $icon);
            }

        // moveusers
            if($numusers) {
                $url->param('action', 'moveusers');
                $icon = new pix_icon('e/resize', get_string('moveusers', 'examboard'), 'core', $attributes);
                $actions[] = $this->output->action_icon($url, $icon);
            }

        // notify
            if($numusers) {
                $url->param('action', 'notify');
                $icon = new pix_icon('t/email', get_string('notify', 'examboard'), 'core', $attributes);
                $actions[] = $this->output->action_icon($url, $icon);
            }

            
            
        /*    
        // delete
            $deleteaction = new \confirm_action(get_string('examdeleteconfirm', 'examboard'));
            // Confirmation JS.
            $PAGE->requires->strings_for_js(array('examdeleteconfirm'), 'examboard');

            $url->param('action', 'deleteexam');
            $icon = new pix_icon('i/delete', get_string('deleteexam', 'examboard'), 'core', $attributes);
            $actions[] = $this->output->action_icon($url, $icon, $deleteaction);
            
            $action .= '<br />';
        */
        
        if($actions) {
            $action = ' <br /><br /> '.\html_writer::div(implode(' &nbsp; ', $actions));
            
        }
        
        return $action;
    }
    
    
    
    
    /**
     * Helper method dealing with the fact we can not just fetch the output of flexible_table
     *
     * @param flexible_table $table The table to render
     * @return string HTML
     */
    protected function flexible_table(\flexible_table $table) {

        $o = '';
        ob_start();
        $table->finish_output();
        $o = ob_get_contents();
                
        ob_end_clean();
        ob_end_flush();



        return $o;
    }

        /**
     * Helper method dealing with the fact we can not just fetch the output of moodleforms
     *
     * @param moodleform $mform
     * @return string HTML
     */
    protected function moodleform($mform) {

        $o = '';
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }
    

    
}
