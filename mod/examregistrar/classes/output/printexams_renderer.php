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
use html_table_cell;
use single_select;
use moodle_url;
use pix_icon;

require_once($CFG->dirroot . '/mod/examregistrar/locallib.php');

/// TODO Eliminar e incluir en otro sitio TODO ///
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_table.php");



class printexams_renderer extends renderer {

    public function listdisplay_allocatedexam(\examregistrar_allocatedexam $exam, $basecourse, $baseurl, $venue='') {
        $output = '';

        $output .= $this->output->container_start(' allocatedexam ');

        $output .= $this->output->container_start(' allocatedexamheader ');

        $exam->set_users($venue);

        $config = examregistrar_get_instance_config($this->page->activityrecord->id, 'approvalcutoff, printdays');
        $now = time();
        $examdate = $exam->get_examdate();

        $examname = $exam->get_exam_name(false, true, true); //$exam->shortname.' - '.$exam->fullname ;
        $output .= $this->output->container($this->output->heading($examname, 3, ' roomheader '), ' allocatedroomheaderleft ');

        $message = $exam->set_valid_file();

        $canmanage = false;
        $withinstructionsclass = '';
        if(!$message && $exam->examfile) {
            $url = '';
            $context = context_course::instance($exam->courseid);
            $canmanage = has_capability('mod/examregistrar:editelements',$context);
            if($candownload = has_capability('mod/examregistrar:download',$context)) {
            //if($candownload = 0) {
                if($venue) {
                    $url = new moodle_url('/mod/examregistrar/download.php', $baseurl->params(array()) + array('down'=>'printexampdf', 'session'=>$exam->session, 'venue'=>$exam->venue, 'exam'=>$exam->get_id()));
                    $item = $this->output->single_button($url, get_string('downloadexampdf', 'examregistrar'), 'post', array('class'=>' singlelinebutton '));
                } else {
                    $url = examregistrar_file_encode_url($context->id, $exam->examfile, 'exam');
                    $icon = new pix_icon('printgreen', get_string('printexam', 'examregistrar'), 'examregistrar', array('class' => 'iconlarge'));
                    $item = '&nbsp; '.$this->output->action_icon($url, $icon);
                }
            } else {
                $printable = ($now >= strtotime(" -{$config->printdays} days ", $examdate)) && ( $now <= strtotime(" + 1 day ", $examdate));
                if($printable) {
                    $url = examregistrar_file_encode_url($context->id, $exam->examfile, 'exam', '', $basecourse->id);
                    $icon = new pix_icon('printgreen', get_string('printexam', 'examregistrar'), 'examregistrar', array('class' => 'iconlarge'));
                } else {
                    $icon = new pix_icon('t/print', get_string('printexam', 'examregistrar'), '', array('class' => 'iconlarge'));
                }
                if($url) {
                    $item = '&nbsp; '.$this->output->action_icon($url, $icon).'&nbsp; ('.count($exam->users).')';
                } else {
                    $item = '&nbsp; '.$this->output->render($icon);
                }
            }
            $item = $this->output->container($item.' &nbsp;   ', 'printiconleft');
            
            
            $printmode = $exam->get_print_mode();
            $examinstructions = $exam->get_exam_instructions();
            list($withinstructionsclass, $content) = $this->print_exam_infoinstructions($printmode, $examinstructions); 
            if(!empty($content)) {
                $item .= $this->output->container($content, ' alert-danger fa-pull-right    printiconleft');
            }
            
        } elseif($exam->examfile) {
            $icon = new pix_icon('i/risk_spam', $message, '', array('class' => 'iconlarge'));
            $item = '&nbsp; '.$this->output->render($icon);
        } else {
            $icon = new pix_icon('i/risk_xss', $message, '', array('class' => 'iconlarge'));
            $item = '&nbsp; '.$this->output->render($icon);
        }

        if($item && $exam->callnum < 0) {
            $item .= ' &nbsp; '.html_writer::span(get_string('specialexam', 'examregistrar'), ' error bold large ');
        }
        
        $output .= $this->output->container($item, " allocatedroomheaderright $withinstructionsclass ");

        $output .= $this->output->container_end('allocatedexamheader');

        $output .= $this->output->container('', ' clearfix ');

        $output .= $this->output->container_start(' allocatedexambody ');

        if($exam->users) {
            $singleroom = examregistrar_is_venue_single_room($venue);
            //$singleroom = 0;
            //$examdate = 0;
        
            $output .= $this->output->container_start(' clearfix  ');
            $output .= $this->output->container_start(' allocatedexamregistered ');
            $output .= html_writer::tag('p', get_string('exambookedstudents', 'examregistrar', count($exam->users)));

            $canresponse = $canreview = false;
            if(!$message && $exam->examfile) {
                $canresponse = has_capability('mod/examregistrar:uploadresponses',$context);
                $canreview = has_capability('mod/examregistrar:confirmresponses',$context);
            }
            
            $url = new moodle_url('/mod/examregistrar/view.php?', $baseurl->params(array()) + 
                        array('period'=>$exam->period, 'session'=>$exam->session, 'venue'=>$exam->venue,  
                        'examfile'=>$exam->examfile, 'action'=>'exam_responses_upload'));
            
            if(!$singleroom) {
                $roomvenue = '';
                if($venue) {
                    $roomvenue = $venue;
                }
                if($rooms = $exam->get_room_allocations($roomvenue)) {
                    foreach($rooms as $rid => $room) {
                        $flag = '';
                        /*
                        $status = $exam->get_responses_status($rid);
                        
                        if(!$message && $exam->examfile && $canresponse && ($now > $examdate) 
                                    && (!$exam->taken && ($status < EXAM_RESPONSES_COMPLETED) || $canreview)) {
                            $url->param('room', $rid);
                            $flag = $this->get_responses_icon($status, $url);
                        } elseif($exam->examfile) {
                            $flag = $this->get_responses_icon($status);
                        }
                        */
                        $rooms[$rid] = $room->name.' ('.$room->allocated.')'.$flag;
                    }
                }
                //$output .= html_writer::tag('p', get_string('exambookedstudents', 'examregistrar', count($exam->users)));
                $output .= html_writer::alist($rooms);
            }
            
            $output .= $this->output->container_end('allocatedexamregistered');
            
            /*
            if(!$message && $exam->examfile && ($now > $examdate)) {
                $status = $exam->get_responses_status($venue, true);
                $flag = $confirm = '';
                if($canresponse && (!$exam->taken && ($status < EXAM_RESPONSES_COMPLETED) || $canreview)) {
                    $url->param('room', $exam->venue);
                    $flag = $this->get_responses_icon($status, $url);
                } else {
                    $flag = $this->get_responses_icon($status);
                }
                
                if($canreview && ($status > EXAM_RESPONSES_UNSENT) && (($status < EXAM_RESPONSES_VALIDATED) || $canmanage)) {
                    $url->param('action', 'exam_responses_review');
                    $confirm = $this->output->single_button($url, get_string('reviewresponses', 'examregistrar'), 'post', array('class'=>' singlelinebutton '));
                }
                $output .= $this->output->container($flag.$confirm, ' fa-2x  allocatedexamresponses allocatedroomheaderright');
            }
            */
            $output .= $this->output->container_end('clearfix');
            
            //$output .= $this->output->container_end();

            $output .= $this->output->container_start(' allocatedexamstudentstable ');
            $table = new html_table();
            $table->attributes = array('style'=>'border:1px solid black;border-collapse:collapse;', 'class'=>'flexible examregprintexamtable' );
            $tableheaders = array(get_string('student', 'examregistrar'),
                                    get_string('venue', 'examregistrar'),
                                    get_string('room', 'examregistrar'),
                                    );
            $table->head = $tableheaders;
            $users = $exam->get_formatted_user_allocations($venue);
            foreach($users as $user) {
                $row = new html_table_row();
                if(is_null($user->roomid)) {
                    $row->style = 'background-color:yellow;';
                    $row->attributes = array('class'=>' error  ');
                    $user->roomname = get_string('unallocated', 'examregistrar');
                }
                $cell1 = new html_table_cell("{$user->idnumber} - ".fullname($user, false, 'lastname'));
                //$cell1->style = 'text-align:right;width:6%;';
                $cell2 = new html_table_cell($user->venuename);
                //$cell2->style = 'text-align:left;width:12%;';
                $cell3 = new html_table_cell($user->roomname);
                //$cell3->style = 'text-align:left;width:42%;';
                $row->cells = array($cell1, $cell2, $cell3);
                $table->data[] = $row;
            }
            
            $output .= print_collapsible_region(html_writer::table($table), 'userlist', 'showhideteacherlistexam_'.$exam->get_id(), get_string('userlist', 'examregistrar'),'teacherlistexam_'.$exam->get_id(), true, true);
            $output .= $this->output->container_end(' allocatedexamstudentstable ');
        }

        $output .= $this->output->container_end(' allocatedexambody ');
        $output .= $this->output->container_end(' allocatedexam ');
        
        return $output;
    }

    
    public function print_exam_infoinstructions($printmode, $examinstructions) {
        $output = '';
        $withinstructionsclass = '';
    
        if(isset($printmode)) {
            $icon = $printmode ? 'i/manual_item' : 't/copy';
            $strprint = $printmode ? get_string('printsingle', 'examregistrar') : get_string('printdouble', 'examregistrar');
            $strprint = get_string('printmode', 'examregistrar').': '.$strprint;
            $icon = new pix_icon($icon, $strprint, 'moodle', array('class'=>'iconsmall', 'title'=>$strprint));
            $printmode = ' '.$this->output->render($icon).' '.$strprint;
        } else {
            $printmode = '';
        }
        
        if(!empty($examinstructions)) {
            $instructions = '<br /> &nbsp; <i class="fa fa-calculator"> </i> '.get_string('examinstructions', 'examregistrar');
            
            $attempt = new \stdClass();
            $attempt->printmode = null;
            $attempt->allowedtools = $examinstructions;
            $inner = $this->print_examiner_instructions($attempt, false, 'bottom');
            
            if($inner) {
                $withinstructionsclass = 'withinstructions';
            }
            
            $output = $printmode . $instructions . $inner;
        } else {
            $output = $printmode;
        }
        
        return [$withinstructionsclass, $output];
    }
    
}

