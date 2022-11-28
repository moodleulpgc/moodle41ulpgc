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
 * Examregistrar module renderer
 *
 * @package    mod
 * @subpackage examregistrar
 * @copyright  2014 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examregistrar\output;
 
defined('MOODLE_INTERNAL') || die();

use html_writer;
use context_course;
use context_module;
use html_table;
use html_table_row;
use single_select;
use moodle_url;
use pix_icon;

require_once($CFG->dirroot . '/mod/examregistrar/locallib.php');

/// TODO Eliminar e incluir en otro sitio TODO ///
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_table.php");



class review_renderer extends renderer {


    public function render_examregistrar_exams_coursereview(\examregistrar_exams_coursereview $coursereview) {
        global $CFG, $DB;

        $baseurl = $coursereview->url;
        $course = $coursereview->course;
        $examregistrar = $coursereview->examregistrar;
        $config = examregistrar_get_instance_config($examregistrar->id, 'approvalcutoff, printdays');

        $quizmodid = $DB->get_field('modules', 'id', array('name'=>'quiz'));

        $strupload = get_string('uploadexamfile', 'examregistrar');
        $strgenerate = get_string('addattempt', 'examregistrar');
        $strdelete = get_string('delete');
        $strreviewitem = get_string('addreviewitem', 'examregistrar');
        $strapprove = get_string('approve', 'examregistrar');
        $strreject = get_string('reject', 'examregistrar');
        $strsend = get_string('send', 'examregistrar');
        $strapproved = get_string('approved', 'examregistrar');
        $strrejected = get_string('rejected', 'examregistrar');
        $strsent = get_string('sent', 'examregistrar');
        $strexamfile = get_string('examfile', 'examregistrar');
        $strexamfileanswers = get_string('examfileanswers', 'examregistrar');
        $strquestionwarning = get_string('questionwarning', 'examregistrar');

        $cansubmit = has_capability('mod/examregistrar:submit', $course->context);
        $candownload = has_capability('mod/examregistrar:download', $course->context);
        $canupload = has_capability('mod/examregistrar:upload', $course->context);
        $canmanageexams = has_capability('mod/examregistrar:manageexams', $course->context);
        $canresolve = has_capability('mod/examregistrar:resolve', $course->context);

        $now = time();

        $output = '';
        $output .=  $this->output->container_start(' examcoursereview'  );

        if(!$coursereview->single) {
            $url = new moodle_url('/course/view.php', array('id'=>$course->id));
            $examname = html_writer::link($url, $course->shortname.' - '.$course->fullname);
            $output .=  $this->output->heading($examname, 3, ' examheader ');
        }

        $coursereview->set_exams();
        if($coursereview->exams) {
            $table = new html_table();
            $table->attributes = array('class'=>'flexible examattemptreviewtable' );
            $tableheaders = array(get_string('perioditem', 'examregistrar'),
                                  get_string('exam', 'examregistrar'),
                                    get_string('callnum', 'examregistrar'),
                                    get_string('status', 'examregistrar'),
                                    get_string('attempts', 'examregistrar'),
                                    get_string('statereview', 'examregistrar'),
                                    get_string('action'),

                                    );
            $table->head = $tableheaders;
            $table->colclasses = array('colperiod', 'colexam', 'colcall', 'colstatus', 'colattempts', 'colreview', 'colaction'  );
            foreach($coursereview->exams as $exam) {
                $cellperiod = $this->formatted_name_fromid($exam->exam->period, 'periods');
                $cellcall = ($exam->exam->callnum > 0) ? $exam->exam->callnum : 'R'.abs($exam->exam->callnum);
                $examscope =  $this->formatted_name_fromid($exam->exam->examscope);
                $examsession = $this->formatted_name_fromid($exam->exam->examsession, 'examsessions');
                $cellexam = $examscope.',  '.$examsession;

                // do not shortcut, can_submit() is needed to execute set_attempts()
                $cansend = ($exam->can_send() && $cansubmit );

                $exam->set_attempts();

                $alreadyapproved  = false;
                foreach($exam->attempts as $attempt) {
                    if($attempt->status >= EXAM_STATUS_APPROVED) {
                        $alreadyapproved = true;
                    }
                }
                foreach($exam->attempts as $attempt) {
                    // status icons
                    $icon = '';
                    switch($attempt->status) {
                        case EXAM_STATUS_SENT       : $icon = $this->pix_icon('sent', $strsent, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strsent));
                                                        break;
                        case EXAM_STATUS_WAITING    : $icon = $this->pix_icon('waiting', $strsent, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strsent));
                                                        break;
                        case EXAM_STATUS_REJECTED   : $icon = $this->pix_icon('rejected', $strrejected, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strrejected));
                                                        break;
                        case EXAM_STATUS_APPROVED   :
                        case EXAM_STATUS_VALIDATED  : $icon = $this->pix_icon('approved', $strapproved, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strapproved));
                                                        break;
                    }
                    $cellstatus = '';
                    $cellattempt = '';
                    $cellstatereview = '';
                    $cellaction = '';
                    if($attempt->attempt) {
                        $cellstatus = $icon.'&nbsp;'.$attempt->attempt;
                        if($exam->warning_questions_used($attempt)) {
                            $icon = $this->pix_icon('i/risk_xss', $strquestionwarning, 'moodle', array('class'=>'icon', 'title'=>$strquestionwarning));
                            $cellstatus .= '<br />'.$icon;
                        }

                        $cellattempt = '';
                        if($candownload) {
                            $attemptname = $attempt->name .' ('.userdate($attempt->timecreated, get_string('strftimerecent')).') ';
                            if(isset($exam->exam->quizplugincm) && $exam->exam->quizplugincm) {
                                //https://localhost/moodle39ulpgc/mod/quiz/report.php?id=7989&mode=makeexam&review=6&confirm=1&sesskey=4K3hrYhCMu
                                if($mkattempt = $exam->get_makeexam_attempt($attempt)) {
                                    $url = new moodle_url('/mod/quiz/report.php', array('id' => $mkattempt->cm, 'mode' => 'makeexam', 'review' => $mkattempt->review, 'confirm' => 1));
                                    $icon = new pix_icon('icon', $strexamfile, 'quiz', array('class'=>'icon', 'title'=>$strexamfile));
                                    $cellattempt .= $this->output->action_link($url,$attemptname, null, null, $icon);                            
                                    $attemptname = '';
                                }
                            }
                            $url = examregistrar_file_encode_url($course->context->id, $attempt->id, 'exam');
                            $icon = new pix_icon('f/pdf-32', $strexamfile, 'moodle', array('class'=>'icon', 'title'=>$strexamfile));
                            $cellattempt .= $this->output->action_link($url,$attemptname, null, null, $icon);
                            
                            $url = examregistrar_file_encode_url($course->context->id, $attempt->id, 'answers');
                            $icon = new pix_icon('i/key', $strexamfileanswers, 'moodle', array('class'=>'iconlarge', 'title'=>$strexamfileanswers));
                            $cellattempt .= ' &nbsp;  &nbsp; '.$this->action_icon($url, $icon);
                            
                            $instructions = '';
                            if(!$attempt->printmode) {
                                $instructions .= ' &nbsp; <i class="fa fa-print"></i> ';
                            }
                            if(isset($attempt->allowedtools) && !empty($attempt->allowedtools)) {
                                $instructions .= ' &nbsp; <i class="fa fa-calculator"></i> &nbsp; ';
                            }
                            if($instructions) {
                                $cellattempt .= html_writer::span($instructions, 'alert-danger fa-pull-right');
                            }
                        }
                        if($attempt->timerejected) {
                            $cellattempt .= '<br />'.get_string('rejected', 'examregistrar').': '.userdate($attempt->timerejected, get_string('strftimedaydatetime'));
                        }
                        if($attempt->timeapproved) {
                            $cellattempt .= '<br />'.get_string('approved', 'examregistrar').': '.userdate($attempt->timeapproved, get_string('strftimedaydatetime'));
                        }
                        
                        if(!$attempt->printmode || (isset($attempt->allowedtools) && !empty($attempt->allowedtools)))  {
                            $cellattempt = $this->add_exam_instructions($attempt, $cellattempt);
                        }

                        // add status review actions
                        // can submit if not summited before
                        if($cansend && $attempt->status == EXAM_STATUS_CREATED ) {
                            $icon = new pix_icon('i/completion-manual-enabled', $strsend, 'moodle', array('class'=>'icon', 'title'=>$strsend));
                            $url = new moodle_url($baseurl, array('status'=>'send', 'attempt'=>$attempt->id));
                            $cellstatereview = $this->action_icon($url, $icon);
                        }

                        $resolvericons = '';
                        if($canresolve) {
                            $icons = array();
                            // if status is sent, coordinator can operate
                            if(($attempt->status >= EXAM_STATUS_SENT) || $canmanageexams) {
                                if(($attempt->status != EXAM_STATUS_REJECTED) && (($attempt->status <= EXAM_STATUS_APPROVED) || $canmanageexams)) {
                                    $icon = new pix_icon('i/completion-auto-fail', $strreject, 'moodle', array('class'=>'icon', 'title'=>$strreject));
                                    $url = new moodle_url($baseurl, array('status'=>'reject', 'attempt'=>$attempt->id));
                                    $icons[] = $this->action_icon($url, $icon);
                                }
                                if(!$alreadyapproved && ($attempt->status != EXAM_STATUS_APPROVED) &&  (($attempt->status == EXAM_STATUS_SENT) || $canmanageexams)) {
                                    $icon = new pix_icon('i/completion-auto-pass', $strapprove, 'moodle', array('class'=>'iconlarge', 'title'=>$strapprove));
                                    $url = new moodle_url($baseurl, array('status'=>'approve', 'attempt'=>$attempt->id));
                                    $icons[] = $this->action_icon($url, $icon);
                                }

                            }
                            $resolvericons = '<br />'.$this->container(implode(' ', $icons), ' examreviewstatusicons ');
                        }
                        $cellstatereview .= $exam->get_review($attempt) . $resolvericons;
                    }

                    // action icons
                    // can delete if rejected or not submitted
                    $icons = array();
                    $examdate = $exam->get_examdate($exam->exam);
                    if( ($now < strtotime("- {$config->approvalcutoff} days", $examdate))  || $canmanageexams) {
                        if($attempt->attempt &&  ($attempt->status == EXAM_STATUS_CREATED || $attempt->status == EXAM_STATUS_REJECTED || $canmanageexams)) {
                            $icon = new pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete));
                            $url = new moodle_url($baseurl, array('delete'=>$attempt->id));
                            $icons[] = $this->action_icon($url, $icon);
                        }

                        $cmid = 0;
                        $select = " module= :module AND course = :course AND score > 0 ";
                        if($cms = $DB->get_records_select_menu('course_modules', $select, array('module'=>$quizmodid, 'course'=>$course->id), '', 'instance, id')) {
                            $cmid = reset($cms);
                        }

                        if($cmid && $cansubmit) {
                            $icon = new pix_icon('contextmenu', $strgenerate, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strgenerate));
                            $url = new moodle_url('/mod/quiz/report.php', array('id'=>$cmid, 'mode'=>'makeexam'));
                            $icons[] = $this->action_icon($url, $icon);
                        }
                        
                        if($canupload) {
                            $icon = new pix_icon('i/import', $strupload, 'moodle', array('class'=>'icon', 'title'=>$strupload));
                            $url = new moodle_url($baseurl, array('attempt'=>$attempt->id, 'upload'=>$exam->exam->id));
                            $icons[] = $this->action_icon($url, $icon);
                        }

                        if($canresolve && $examregistrar->reviewmod && $attempt->attempt && $attempt->status && !$attempt->reviewid) {
                            $icon = new pix_icon('icon', $strreviewitem, 'mod_tracker', array('class'=>'iconsmall', 'title'=>$strreviewitem));
                            $url = new moodle_url($baseurl, array('attempt'=>$attempt->id, 'setreview'=>$exam->exam->id));
                            $icons[] = $this->action_icon($url, $icon);
                        }
                        /*
                        if($canresolve && isset($attempt->printmode)) {
                            $icon = $attempt->printmode ? 'i/manual_item' : 't/copy';
                            $strprint = $attempt->printmode ? get_string('printsingle', 'examregistrar') : get_string('printdouble', 'examregistrar');
                            $strprint = get_string('printmode', 'examregistrar').': '.$strprint;
                            $icon = new pix_icon($icon, $strprint, 'moodle', array('class'=>'iconsmall', 'title'=>$strprint));
                            $url = new moodle_url($baseurl, array('attempt'=>$attempt->id, 'toggleprint'=>$exam->exam->id));
                            $icons[] = $this->action_icon($url, $icon);
                        }
                        */
                        if($canresolve && $attempt->attempt) {
                            $strprint = get_string('examinstructions', 'examregistrar');
                            $icon = new pix_icon('i/calc', $strprint, 'moodle', array('class'=>'iconsmall', 'title'=>$strprint));
                            $url = new moodle_url($baseurl, array('attempt'=>$attempt->id, 'instructions'=>$exam->exam->id));
                            $icons[] = $this->action_icon($url, $icon);
                        }
                        
                        
                    }

                    $cellaction = implode('&nbsp; &nbsp;', $icons);
                    //$row->cells = array($cellscope, $cellcall, $cellsession, $cellattempt, $cellaction);
                    $row = new html_table_row(array($cellperiod, $cellexam, $cellcall, $cellstatus, $cellattempt, $cellstatereview, $cellaction));
                    $table->data[] = $row;
                }
            }
            $output .= html_writer::table($table);
        }

        $output .=  $this->output->container_end();

        return $output;
    }

    
    public function add_exam_instructions($attempt, $cell) {
        $output = '';
        
        if(!$cell || ($attempt->printmode && (!isset($attempt->allowedtools) || empty($attempt->allowedtools)))) {
            return $cell;
        }
        
        $output .= $this->container_start('withinstructions');
            $output .= $cell;
            
            $output .= $this->print_examiner_instructions($attempt, true);
            /*
            $output .= $this->container_start('examinstructions');
            $output .= $this->container_start('instructionscontent');
                $output .= $this->output->heading(get_string('examinstructions', 'examregistrar'), 5);
                $content = [];
                if(!$attempt->printmode) {
                    $content[] = '<i class="fa fa-copy"> </i> '. get_string('printdouble', 'examregistrar');
                } 
                
                if(isset($attempt->allowedtools) && !empty($attempt->allowedtools) && is_array($attempt->allowedtools)) {
                    $last = '';
                    foreach($attempt->allowedtools as $allowed => $value) {
                        if($allowed == 'textinstructions') {
                            $last = nl2br("$value");
                        } else {
                            $content[] = '<strong><i class="fa fa-check-square-o "></i> '. get_string('examallow_'.$allowed, 'examregistrar') . 
                                            '</strong><br />'.get_string('examallow_'.$allowed.'_help', 'examregistrar');
                        }
                    } 
                    if($last) {
                        $content[] = '<strong>' . get_string('examinstructionstext', 'examregistrar') . '</strong>' .
                                        '<br />'.$last;
                    }
                }
                if($content) {
                    $output .= html_writer::alist($content);
                }
            $output .= $this->container_end();
            $output .= $this->container_end();
            */
        $output .= $this->container_end();
        
        return $output;
    }

    
}

