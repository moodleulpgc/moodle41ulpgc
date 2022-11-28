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



class session_renderer extends renderer {

    public function session_control_panel_links($baseurl, $editurl, $actionurl) {
        $output = '';

        $cmid = $this->page->cm->id;

        $output .= $this->container_start(' examregistrarmanagelinks ');
        $output .= html_writer::empty_tag('hr');
        $uploadurl = new moodle_url($editurl);

        $output .= html_writer::nonempty_tag('span', get_string('roomassignments', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
        $text = array();
        $editurl->param('edit', 'session_rooms');
        $text[] = html_writer::link($editurl, get_string('editsessionrooms', 'examregistrar'));
        $actionurl->param('action', 'sessionrooms');
        $text[] = html_writer::link($actionurl, get_string('assignsessionrooms', 'examregistrar'));
        $uploadurl->param('csv', 'session_rooms');
        $uploadurl->param('edit', 'session_rooms');
        $text[] = html_writer::link($uploadurl, get_string('uploadcsvsession_rooms', 'examregistrar'));
        $actionurl->param('action', 'stafffromexam');
        $text[] = html_writer::link($actionurl, get_string('stafffromexam', 'examregistrar'));

        $output .= implode(',&nbsp;&nbsp;',$text).'<br />';

        $output .= html_writer::nonempty_tag('span', get_string('seatassignments', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
        $text = array();
        $url = new moodle_url('/mod/examregistrar/manage/assignseats.php', array('id'=>$cmid, 'edit'=>'session_rooms'));
        $text[] = html_writer::link($url, get_string('assignseats', 'examregistrar'));

        $url = new moodle_url($baseurl, array('action'=>'assignseats_venues'));
        $text[] = html_writer::link($url, get_string('assignseats_venues', 'examregistrar'));

        $uploadurl->param('csv', 'assignseats');
        $uploadurl->param('edit', 'session_rooms');
        $text[] = html_writer::link($uploadurl, get_string('uploadcsvassignseats', 'examregistrar'));

        $url = new moodle_url($baseurl, array('action'=>'checkvoucher'));
        $url->params($editurl->params());
        $text[] = html_writer::link($url, get_string('checkvoucher', 'examregistrar'));

        $output .= implode(',&nbsp;&nbsp;',$text).'<br />';

        $output .= html_writer::nonempty_tag('span', get_string('quizmanagement', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
        $text = array();

        //examssetquestions  examsdelquestions examssetoptions

        $url = new moodle_url($baseurl, array('action'=>'examssetquestions'));
        $text[] = html_writer::link(clone $url, get_string('assignquestions', 'examregistrar'));
        /*
        $url->param('action', 'examsdelquestions');
        $text[] = html_writer::link(clone $url, get_string('examsdelquestions', 'examregistrar'));
        */
        $url->param('action', 'examssetoptions');
        $text[] = html_writer::link(clone $url, get_string('examssetoptions', 'examregistrar'));
        $url->param('action', 'updateqzdates');
        $text[] = html_writer::link($url, get_string('updatequizzes', 'examregistrar'));
        $url->param('action', 'removequizpass');
        $text[] = html_writer::link(clone $url, get_string('removequizpass', 'examregistrar'));

        $output .= implode(',&nbsp;&nbsp;',$text).'<br />';

        $output .= html_writer::nonempty_tag('span', get_string('printingoptions', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
        $text = array();
        $actionurl->param('action', 'roomprintoptions');
        $text[] = html_writer::link($actionurl, get_string('roomprintoptions', 'examregistrar'));
        $actionurl->param('action', 'examprintoptions');
        $text[] = html_writer::link($actionurl, get_string('examprintoptions', 'examregistrar'));
        $actionurl->param('action', 'binderprintoptions');
        $text[] = html_writer::link($actionurl, get_string('binderprintoptions', 'examregistrar'));
        $actionurl->param('action', 'userlistprintoptions');
        $text[] = html_writer::link($actionurl, get_string('userlistprintoptions', 'examregistrar'));
        $actionurl->param('action', 'bookingprintoptions');
        $text[] = html_writer::link($actionurl, get_string('bookingprintoptions', 'examregistrar'));
        $actionurl->param('action', 'venueprintoptions');
        $text[] = html_writer::link($actionurl, get_string('venueprintoptions', 'examregistrar'));
        $actionurl->param('action', 'venuefaxprintoptions');
        $text[] = html_writer::link($actionurl, get_string('venuefaxprintoptions', 'examregistrar'));



        $output .= implode(',&nbsp;&nbsp;',$text).'<br />';

        $output .= html_writer::empty_tag('hr');
        $output .= $this->container_end();

        return $output;
    }


    public function print_session_control_box($heading, array $headerlinks, array $footerlinks,
                                                $title, $collapseclass, $contents) {
        $output = '';

        if($heading || !empty($headerlinks)) {
            $output .= $this->container_start('managesessionheader clearfix ');
            $output .= $this->container($heading,  'managesessioniteminfo');
            foreach($headerlinks as $name => $url) {
                $output .= $this->heading(html_writer::link($url, get_string($name, 'examregistrar')),
                                            4, 'managesesionactionlink');
            }
            $output .= $this->container_end();
            $output .= $this->container('', 'clearfix');
        }

        $footer = '';
        if(!empty($footerlinks)) {
            $footer .= $this->container_start('managesessionfooter clearfix ');
            foreach($headerlinks as $name => $url) {
                $footer .= $this->heading(html_writer::link($url, get_string($name, 'examregistrar')),
                                            4, 'managesesionactionlink');
            }
            $footer .= $this->container_end();
        }

        $output .= print_collapsible_region($contents.$footer, 'managesession', 'showhide'.$collapseclass,
                                            $title, $collapseclass, true, true);
//        $output .= $this->container_end();
        return $output;
    }


    public function session_quality_control($session, $bookedsite, $esort, $roomsnonstaffed, $numusers) {
        $output = '';

        list($totalbooked, $totalseated) = examregistrar_qc_counts($session, $bookedsite);
        $examregistrar = $this->page->activityrecord;
        $examregprimaryid = examregistrar_get_primaryid($examregistrar);

        $class = ($totalbooked != $totalseated) ?  ' busyalloc ' : ' freealloc ';
        $count = get_string('totalseated', 'examregistrar', $totalseated).
                           ' / '.get_string('totalbooked', 'examregistrar', $totalbooked);
        $output .= html_writer::div($count, $class);
        $failures = examregistrar_booking_seating_qc($session, $bookedsite, $esort);

        if($failures) {

            if(!$bookedsite) {
                $venueelement = examregistrar_get_venue_element($examregistrar);
                $venuemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, '', '', array('locationtype'=>$venueelement));
                $venuefails = $venuemenu;
                foreach($venuefails as $key => $v) {
                    $venuefails[$key] = 0;
                }
                foreach($failures as $fail) {
                     $venuefails[$fail->bookedsite] += 1;
                }
                $numfail = 0;
                foreach($venuemenu as $key => $venue) {
                    $class = $venuefails[$key] ? ' busyalloc ' : ' freealloc ';
                    if($venuefails[$key]) {
                        $numfail += 1;
                    }
                    $venuemenu[$key] = html_writer::span($venue.': '.get_string('unallocatedbooking', 'examregistrar', $venuefails[$key]), $class);
                }
                $venues = html_writer::alist($venuemenu);

                $output .= print_collapsible_region($venues, 'qcuserlist', 'showhidevenuelistfail'.$session, get_string('qcvenuesnonallocated', 'examregistrar')." ($numfail) ",'userlist', true, true);
            }
            $failusers = array();
            foreach($failures as $fail) {
                $failusers[] = fullname($fail, false, 'lastname firstname').' : '.$fail->programme.'-'.$fail->shortname ;
            }
            $numfail = count($failusers);
            $failusers = html_writer::alist($failusers);
            $output .= print_collapsible_region($failusers, 'qcuserlist', 'showhideuserlistfail'.$session, get_string('qcbookingsnonallocated', 'examregistrar')." ($numfail) ",'userlist', true, true);
            //print_collapsible_region($contents, $classes, $id, $caption, $userpref = '', $default = false, $return = false)
        } else {
            $class = ' freealloc ';
            $numfail = 0;
            if(!$bookedsite) {
                $output .= html_writer::span(get_string('qcvenuesnonallocated', 'examregistrar').": $numfail ", $class);
            }
            $output .= html_writer::span(get_string('qcbookingsnonallocated', 'examregistrar').": $numfail ", $class);
        }


        // Rooms without staff, number, drop down list
        $class = ($roomsnonstaffed > 0) ?  ' busyalloc ' : ' freealloc ';
        $output .= html_writer::div(get_string('countroomsnonstaffed', 'examregistrar', $roomsnonstaffed), $class);

        // Staff without room, number, drop down list
        $class = ($numusers > 0) ?  ' busyalloc ' : ' freealloc ';
        $output .= html_writer::div(get_string('qcstaffnonallocated', 'examregistrar').': '.$numusers, $class);


        return $output;
    }

    public function session_printing_buttons($downloadurl, $rsort) {
        $output = '';

        $output .= $this->container_start('examregprintbuttons clearfix ');
        $downloadurl->param('down', 'printroompdf');
        $downloadurl->param('rsort', $rsort);
        $output .= $this->single_button($downloadurl, get_string('printroompdf', 'examregistrar'), 'post', array('class'=>' clearfix '));
        
        $downloadurl->param('down', 'printroomsumarypdf');
        $downloadurl->param('rsort', $rsort);
        $output .= $this->single_button($downloadurl, get_string('printroomsummarypdf', 'examregistrar'), 'post', array('class'=>' clearfix '));

        $downloadurl->param('down', 'printexampdf');
        $output .= $this->single_button($downloadurl, get_string('printexampdf', 'examregistrar'), 'post', array('class'=>' clearfix '));
        
        $downloadurl->param('down', 'zipexampdfs');
        $output .= $this->single_button($downloadurl, get_string('downloadexampdfszip', 'examregistrar'), 'post', array('class'=>' clearfix '));
        
        
        $downloadurl->param('down', 'printbinderpdf');
        $output .= $this->single_button($downloadurl, get_string('printbinderpdf', 'examregistrar'), 'post', array('class'=>' clearfix '));

        $downloadurl->param('down', 'printuserspdf');
        $output .= $this->single_button($downloadurl, get_string('printuserspdf', 'examregistrar'), 'post', array('class'=>' clearfix '));

        $output .= $this->container_end();
        return $output;
    }

    public function build_session_rooms_table(array $sessionrooms, $baseurl,
                                                $esort, $rsort,
                                                $session, $bookedsite, $candownload)  {
        global $DB;

        $output = '';

        if(empty($sessionrooms)) {
            return $output;
        }

        // form for ordering
        $baseurl->param('esort', $esort);
        $sorting = array(''=>get_string('sortroomname', 'examregistrar'),
                        'seats'=>get_string('sortseats', 'examregistrar'),
                        'freeseats'=>get_string('sortfreeseats', 'examregistrar'),
                        'booked'=>get_string('sortbooked', 'examregistrar'));
        $select = new single_select($baseurl, 'rsort', $sorting, $rsort, '');
        $select->set_label(get_string('sortby', 'examregistrar'));
        $output = $this->render($select);

        $table = new html_table();
        $table->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
        $tableheaders = array(get_string('room', 'examregistrar'),
                                get_string('seats', 'examregistrar'),
                                get_string('exams', 'examregistrar'),
                                get_string('staffers', 'examregistrar'),
                                get_string('status'),
                                );
        $table  ->head = $tableheaders;
        $table->colclasses = array();

        $strstaffers = get_string('roomstaffers', 'examregistrar');
        $cmid = $this->page->cm->id;
        $staffurl = new moodle_url('/mod/examregistrar/manage/assignroomstaffers.php', array('id'=>$cmid, 'action'=>'roomstaffers', 'edit'=>''));
        $iconaddstaff = new pix_icon('t/enrolusers', $strstaffers, 'moodle', array('class'=>'icon', 'title'=>$strstaffers));
            //$cellattempt = $name.'&nbsp;   &nbsp;'.$this->action_icon($url, $icon);

            //$buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/assignroles'), 'alt'=>$strstaffers, 'class'=>'iconsmall')), array('title'=>$strstaffers));

        foreach($sessionrooms as $room) {
            //print_object($room);
            $cellroom = $this->formatted_name($room->name, $room->idnumber);
            if(!$bookedsite) {
                $cellroom = $this->formatted_name($room->venuename, $room->venueidnumber);
            }
            $cellseats = "&nbsp;&nbsp;  {$room->booked} / {$room->seats} ";
            $seatclass = ($room->booked > $room->seats) ?  ' busyalloc ' : ' freealloc ';
            $cellseats = html_writer::span($cellseats, $seatclass);
            $cellexams = '';

            $list = array();
            if($exams = examregistrar_get_sessionroom_exams($room->id, $session, $bookedsite)) {
                foreach($exams as $exam) {
                    $examclass = new \examregistrar_exam($exam);
                    $list[] = $examclass->get_exam_name(true, true);  //$exam->programme.'-'.$exam->shortname;
                }
                $cellexams = implode('<br />', $list);
            }

            $cellstaff = '';
            $staffers = examregistrar_get_room_staffers_list($room->id, $room->examsession);
            $staffurl->params(array('session'=>$room->examsession, 'room'=>$room->id));
            if($staffers) {
                $cellstaff = print_collapsible_region($staffers, 'userlist', 'showhideuserlist'.$room->id, get_string('roomstaff', 'examregistrar'),'userlist', true, true);
            } elseif($cellexams){
                $cellstaff = html_writer::span(get_string('notyet', 'examregistrar'), 'notifyproblem');
            }

            $cellaction = $this->action_icon($staffurl, $iconaddstaff);
            if($bookedsite && $room->booked && $candownload) {
                    $filename = get_roomzip_filename($session, $bookedsite, $room);
                    $context = $this->page->context;
                    if($file = examregistrar_file_get_file($context->id, $room->examsession, 'sessionrooms', $filename)) {
                        $lastallocated = $DB->get_records_menu('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'roomid'=>$room->id),
                                                        'timemodified DESC', 'id, timemodified', 0, 1);
                        $lastallocated = ($lastallocated) ? reset($lastallocated) : 0;
                        if($file->get_timemodified() > $lastallocated) {
                            $filename = $file->get_filename();
                            $message = get_string('printroomwithexams', 'examregistrar');
                            $url = examregistrar_file_encode_url($context->id, $session, 'sessionrooms', $filename, false, true);
                            $icon = new pix_icon('printgreen', $message, 'examregistrar', array('class' => 'icon'));
                            $item = $this->action_icon($url, $icon);
                            $cellaction .= $item;
                        }
                    }
            }
            $cellaction = html_writer::span($cellaction, 'examreviewstatusicons');


            $row = new html_table_row(array($cellroom, $cellseats, $cellexams, $cellstaff, $cellaction));
            $table->data[] = $row;
        }

        $output .= html_writer::table($table);

        return $output;
    }

    public function build_online_exams_table(array $sessionexams, $baseurl,
                                                $esort, $rsort)  {
        $output = '';

        if(empty($sessionexams)) {
            return $output;
        }

        $examregistrar = $this->page->activityrecord;
        $examregprimaryid = examregistrar_get_primaryid($examregistrar);
        $now = time();

        // form for ordering
        $baseurl->param('rsort', $rsort);
        $sorting = array(''=>get_string('sortprogramme', 'examregistrar'),
                        'fullname'=>get_string('sortfullname', 'examregistrar'),
                        'booked'=>get_string('sortbooked', 'examregistrar'),
                        'allocated'=>get_string('sortbooked', 'examregistrar'));
        $select = new single_select($baseurl, 'edsort', $sorting, $esort, '');
        $select->set_label(get_string('sortby', 'examregistrar'));
        $output .=  $this->render($select);

        $table = new html_table();
        $table->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
        $tableheaders = array(get_string('exam', 'examregistrar'),
                                get_string('takingmode', 'examregistrar'),
                                get_string('allocated', 'examregistrar'),
                                get_string('taken', 'examregistrar'),
                                get_string('status'),
                                );
        $table  ->head = $tableheaders;
        $table->colclasses = array();
        $helperurl = new moodle_url('/mod/quiz/view.php');


        foreach($sessionexams as $exam) {
            //print_object($exam);
            if(!isset($exam->booked)) {
                $exam->booked = 0;
            }

            $exam->examregid = $examregprimaryid;
            $exam->annuality = $examregistrar->annuality;
            $examclass = new \examregistrar_exam($exam);
            $examclass->examregid = $examregprimaryid;
            $cellexam = $examclass->get_exam_name(true);

            $celldelivery = '';
            $cms = get_fast_modinfo($exam->courseid)->cms;
            if(isset($cms[$exam->helpercmid]) && $cminfo = $cms[$exam->helpercmid]) {
                unset($cms);
                $visible = $cminfo->visible;
                $deliveryclass = ($visible) ?  '' : ' dimmed ';
                $celldelivery = html_writer::link($cminfo->url, $this->pix_icon('icon', '', $exam->helpermod) .' '. $cminfo->name,
                                                ['class' => $deliveryclass]);
            }
            //$celldelivery = $examclass->get_exam_deliver_helper(true, true);

            $cellseats = "&nbsp;  {$exam->allocated} / {$exam->booked} ";
            $seatclass = ($exam->allocated != $exam->booked) ?  ' busyalloc ' : ' freealloc ';
            $cellseats = html_writer::span($cellseats, $seatclass);

            $celltaken = '';
            $cellaction = '';
            if(isset($exam->deliveryid) && $exam->deliveryid) {
                list($numfinished, $numattempts) = $examclass->get_helper_taken_data();
                if($numfinished || $numattempts) {
                    $celltaken = "$numfinished / $numattempts";
                }
                $examclass->set_valid_file();
                $flags = $examclass->get_helper_flags();
                $cellaction = $this->print_exam_flags($exam->examid, $baseurl, $flags, $examclass->taken);
            }

            if($cellaction) {
                $cellaction = html_writer::span($cellaction, 'examreviewstatusicons');
            }

            $row = new html_table_row(array($cellexam, $celldelivery, $cellseats, $celltaken, $cellaction));
            $table->data[] = $row;
        }
        $output .=  html_writer::table($table);

        return $output;
    }

    public function build_session_exams_table(array $sessionexams, $baseurl, $actionurl,
                                                $esort, $rsort, $session, $bookedsite)  {
        global $DB;

        $output = '';

        if(empty($sessionexams)) {
            return $output;
        }

        $examregistrar = $this->page->activityrecord;
        $examregprimaryid = examregistrar_get_primaryid($examregistrar);
        $now = time();

        // form for ordering
        $baseurl->param('rsort', $rsort);
        $sorting = array(''=>get_string('sortprogramme', 'examregistrar'),
                        'fullname'=>get_string('sortfullname', 'examregistrar'),
                        'booked'=>get_string('sortbooked', 'examregistrar'),
                        'allocated'=>get_string('sortbooked', 'examregistrar'));
        $select = new single_select($baseurl, 'esort', $sorting, $esort, '');
        $select->set_label(get_string('sortby', 'examregistrar'));
        $output .=  $this->render($select);

        $table = new html_table();
        $table->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
        $tableheaders = array(get_string('exam', 'examregistrar'),
                                get_string('allocated', 'examregistrar'),
                                get_string('rooms', 'examregistrar'),
                                get_string('status'),
                                );
        $table  ->head = $tableheaders;
        $table->colclasses = array();

        $strstaffers = get_string('roomstaffers', 'examregistrar');

        $straddcall = get_string('addextracall', 'examregistrar');

        $addcallurl = new moodle_url('/mod/examregistrar/manage/action.php', $baseurl->params() + array('action'=>'addextracall'));
        $iconaddcall = new pix_icon('i/manual_item', $straddcall, 'moodle', array('class'=>'icon', 'title'=>$straddcall));

        $buttons[] = html_writer::link($actionurl, $this->pix_icon('i/manual_item', $straddcall, 'manual', array('class'=>'iconsmall', 'title'=>$straddcall)));

        foreach($sessionexams as $exam) {
            //print_object($exam);
            if(!isset($exam->booked)) {
                $exam->booked = 0;
            }
            $exam->examregid = $examregprimaryid;
            $exam->annuality = $examregistrar->annuality;

            $examclass = new \examregistrar_exam($exam);
            $examclass->examregid = $examregprimaryid;
            $cellexam = $examclass->get_exam_name(true, true, true, true);

            $cellseats = "&nbsp;&nbsp;  {$exam->allocated} / {$exam->booked} ";
            $seatclass = ($exam->allocated != $exam->booked) ?  ' busyalloc ' : ' freealloc ';
            $cellseats = html_writer::span($cellseats, $seatclass);

            // cell rooms used / exams used
            $cellrooms = '';
            $list = array();
            if($rooms = examregistrar_get_sessionexam_rooms($exam->id, $session, $bookedsite)) {
                foreach($rooms as $room) {
                    $name = $this->formatted_name($room->name, $room->idnumber);
                    if(!$bookedsite) {
                        $name = $this->formatted_name($room->venuename, $room->venueidnumber);
                    }
                    $list[] = $name;
                }
                $cellrooms = implode('<br />', $list);
            }


            $cellaction = '';
            if($exam->callnum < 0) {
                $cellaction = html_writer::span('R'.abs($exam->callnum), 'error').' ';
            }
            //$examclass = new examregistrar_exam($exam);
            $exam->examdate = $examclass->get_examdate();
            $message = $examclass->set_valid_file();

            $ccontext = context_course::instance($exam->courseid);
            $candownload_incourse = has_capability('mod/examregistrar:download',$ccontext);
            $item = '';
            $component = '';
            if(!$message && $examclass->examfile) {
                $message = get_string('printexam', 'examregistrar');
                if($candownload_incourse) {
                    $url = examregistrar_file_encode_url($ccontext->id, $examclass->examfile, 'exam');
                    //http://localhost/moodle26ulpgc/pluginfile.php/5438/mod_examregistrar/exam/109/4036-46052-ORDC1-F-R6.pdf
                    $icon = new pix_icon('printgreen', $message, 'examregistrar', array('class' => 'icon'));
                    $item = $this->action_icon($url, $icon);
                } else {
                    $icon = 'printgreen';
                    $component = 'examregistrar';
                }
            } elseif($examclass->examfile) {
                $icon = 'i/risk_spam';
            } else {
                $icon = 'i/risk_xss';
            }
            if(!$item) {
                $icon = new pix_icon($icon, $message, $component, array('class' => 'icon'));
                $item = $this->render($icon);
            }
            $cellaction .= $item;

            /// TODO TODO this code is duplicated from render_examregistrar_exams_course(), renderable.php
            /// FACTORIZE

            if($exam->examdate < $now && $examclass->examfile)  {
                if($examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$examclass->examfile))) {
                    if($examfile->taken > 0) {
                        if($filenames = examregistrar_file_get_filename($ccontext->id, $examfile->id, 'responses', true)) {
                            $celltaken = '';
                            $icon = 'i/completion-manual-enabled';
                            $strexamresponses = get_string('examresponses', 'examregistrar');
                        } else {
                            $icon = 'i/completion-auto-fail';
                            $strexamresponses = get_string('filemissing', 'moodle', get_string('file'));
                        }
                        $url = new moodle_url('view.php', $baseurl->params()+array('action'=>'response_files', 'examf'=>$examfile->id));
                        $icon = new pix_icon($icon, $strexamresponses, '', array('class' => 'iconsmall'));
                        $item = $this->action_icon($url, $icon); //$this->render($icon);
                        $cellaction .= $item;
                    }
                }
            }
            $cellaction = html_writer::span($cellaction, 'examreviewstatusicons');

            $row = new html_table_row(array($cellexam, $cellseats, $cellrooms, $cellaction));
            $table->data[] = $row;
        }
        $output .=  html_writer::table($table);

        return $output;
    }

    public function print_exam_flags($examid, $url, $flags, $taken = false) {
        $output = '';

        if(isset($flags['extranobook']) && $flags['extranobook'] ) {
            $output .= $this->pix_icon('i/groupn', get_string('extranobook', 'examregistrar'), 'moodle', ['class' => 'icon']);
        }

        $datesicons = '';
        if(isset($flags['datetime']) && $flags['datetime']) {
            $title = get_string('unsynchdate', 'examregistrar');
            $datesicons .= html_writer::tag('i', ' ', array('class' => "fa fa-calendar-times-o responseicon text-{$flags['datetime']}",
                                                    'title' => $title,
                                                    'aria-label' => $title,
                                                    ));
        }

        if(isset($flags['timeopen']) && $flags['timeopen']) {
            $title = get_string('unsynchtimeopen', 'examregistrar');
            $datesicons .= html_writer::tag('i', ' ', array('class' => "fa fa-clock-o responseicon text-{$flags['timeopen']}",
                                                    'title' => $title,
                                                    'aria-label' => $title,
                                                    ));
        }
        if(isset($flags['timeclose']) && $flags['timeclose']) {
            $title = get_string('unsynchtimeclose', 'examregistrar');
            $datesicons .= html_writer::tag('i', ' ', array('class' => "fa fa-bell responseicon text-{$flags['timeclose']}",
                                                    'title' => $title,
                                                    'aria-label' => $title,
                                                    ));
        }
        if(isset($flags['timelimit']) && $flags['timelimit']) {
            $title = get_string('unsynchtimelimit', 'examregistrar');
            $datesicons .= html_writer::tag('i', ' ', array('class' => "fa fa-hourglass responseicon text-{$flags['timelimit']}",
                                                    'title' => $title,
                                                    'aria-label' => $title,
                                                    ));
        }

        $url->param('exam', $examid);
        if($datesicons) {
            $url->param('action', 'updatequizzes');
            $output .= \html_writer::link($url, $datesicons);
        }

        if(isset($flags['password']) && $flags['password']) {
            $url->param('action', 'removequizpass');
            $title = get_string('passwordlocked', 'examregistrar');
            $flag = html_writer::tag('i', '&nbsp; ', array('class' => "fa fa-key responseicon text-{$flags['password']}",
                                                    'title' => $title,
                                                    'aria-label' => $title,
                                                    ));
            $output .= html_writer::link($url, $flag);
        }

        $accessicons = '';
        if(isset($flags['accessfree']) && $flags['accessfree']) {
            $title = get_string('mkaccessfree', 'examregistrar');
            $accessicons .= html_writer::tag('i', '&nbsp ', array('class' => "fa fa-universal-access responseicon text-danger",
                                                    'title' => $title,
                                                    'aria-label' => $title,
                                                    ));
        }
        if(isset($flags['accesslocked']) && $flags['accesslocked']) {
            $title = get_string('mkaccesslocked', 'examregistrar');
            $accessicons .= html_writer::tag('i', '&nbsp ', array('class' => "fa fa-lock responseicon text-danger",
                                                    'title' => $title,
                                                    'aria-label' => $title,
                                                    ));
        }

        if($accessicons) {
            $url->param('action', 'mklockquizzes');
            $output .= \html_writer::link($url, $accessicons);
        }

        $url->param('action', 'examssetquestions');
        if($flags['questions'] == 'success') {
            $output .= $this->pix_icon('quizgreen', get_string('okstatus', 'examregistrar'), 'examregistrar', ['class' => 'icon']);
        } elseif($taken) {
                $output .= $this->pix_icon('quizred', get_string('invalidquestions', 'examregistrar'), 'examregistrar', ['class' => 'icon']);
        } else {
            $icon = new pix_icon('quizred', get_string('invalidquestions', 'examregistrar'), 'examregistrar', array('class' => 'icon'));
            $output .= $this->action_icon($url, $icon);
        }

        return $output;
    }

    public function special_exams_form($baseurl) {
        global $CFG;

        $output = '';

        $output .= '<form id="examregistrarfilterform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage/action.php" method="post">'."\n";;
        $output .= html_writer::input_hidden_params($baseurl);
        $output .= html_writer::empty_tag('input', array('name'=>'action', 'type'=>'hidden', 'value'=>'addextrasessioncall'));
        $output .= html_writer::label(get_string('specialfor', 'examregistrar').'&nbsp;', 'examshort', false, array('class' => 'accesshidexx'));
        $output .= html_writer::empty_tag('input', array('name'=>'examshort', 'type'=>'text', 'value'=>'', 'size'=>8));
        $output .= '&nbsp;  ';
        $output .= '<input type="submit" value="'.get_string('addspecial', 'examregistrar').'" />'."\n";
        $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'addspecial', 'value'=>'addspecial'));
        $output .= '</form>'."\n";

        return $output;
    }

    public function session_response_files_table($pending, $ndistributed) {

        $output = '';
            $output .= $this->container(get_string('distributedresponsefiles', 'examregistrar', $ndistributed),  'managesessioniteminfo');
        if($pending ) {
            $output .= $this->container_start('managesessioniteminfo');
            $output .= get_string('unknownresponsefiles', 'examregistrar', count($pending));
            $list = array();
            foreach($pending as $file) {
                $list[] = $file->get_filename();
            }
            $output .= html_writer::alist($list);
            $output .= $this->container_end();
        }

        return $output;
    }

}

