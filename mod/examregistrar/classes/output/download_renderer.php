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



class download_renderer extends renderer {


    /**
     * Generate HTML table of users (num, IDnumner, name) with additional extra columns if appropiate
     *
     * @param array $users collection of users for table, must have id, idnumbre, firstname & lastname fields
     * @param int $width widthd of the table as a whole
     * @param array $widths array of widths of each column in the table, ir order
     * @param array $extraheads array of extra columns in addition to nº, ID, name & additional
     * @param array $star associative array for 'star' column field => value used has head
     * @param array $extracontent associative array for extracolums user field => style string. If empty, a checkbox is added
     * @return string, HTML trable
     */
    public function print_exam_user_table($users, $width, $widths, $extraheads, $star=null, $extracontent=array()) {
        $table = new html_table();
        $table->attributes = array('style'=>'border:1px solid black;border-collapse:collapse;');
        $table->width = "$width%";
        $numextra = count($extraheads);
        if(count($widths) != ($numextra + 4)) {
            $widths[0] = '6%';
            $widths[1] = '12%';
            $widths[2] = '42%';
            $widths[3] = '4%';
            foreach($extraheads as $i => $extra)  {
                $widths[$i+4] = round(36/$numextra, 1).'%';
            }
        }
        foreach($extraheads as $i => $head) {
            $head = new html_table_cell($head);
            $head->style = 'text-align:center;border-bottom:1px solid black;';
            $extraheads[$i] = $head;
        }

        $extracols = array();
        if(!$extracontent) {
            $checkbox = new html_table_cell('&#9744;');
            $checkbox->style = 'text-align:center;';  //width:12%;
            $extracols = array_fill(0,$numextra,$checkbox);
        }

        if(!$star) {
            $starhead = '*';
            $starfield = 'additional';
        } else {
            $starhead = reset($star);
            $starfield = key($star);
        }

        $heads = array();
        $cell = new html_table_cell('');
        $cell->style = 'text-align:right;border-bottom:1px solid black;'; //width:6%;
        $heads[] = $cell;
        $cell = new html_table_cell(get_string('idnumber'));
        $cell->style = 'text-align:left;border-bottom:1px solid black;'; //width:12%;
        $heads[] = $cell;
        $cell = new html_table_cell(get_string('student', 'examregistrar'));
        $cell->style = 'text-align:left;border-bottom:1px solid black;'; //width:42%;
        $heads[] = $cell;
        $cell = new html_table_cell($starhead);
        $cell->style = 'text-align:center;border-bottom:1px solid black;'; //width:4%;
        $heads[] = $cell;
        $heads =  array_merge($heads, $extraheads);

        foreach($heads as $i => $cell) {
            $cell->style .= ' width:'.$widths[$i];
            $heads[$i] = $cell;
        }
        $table->head = $heads; //array_merge(array(' ', get_string('idnumber'), get_string('student', 'examregistrar'), '*'), $extraheads);
        $index = 1;

        foreach($users as $user) {
            $row = new html_table_row();
            if($index % 2 == 1) {
                $row->style = 'background-color:lightgray;';
            }
            $cell1 = new html_table_cell($index);
            $cell1->style = 'text-align:right;'; //width:6%;
            $cell2 = new html_table_cell(substr_replace($user->idnumber, '****', 1, 4)); // mask DNI numbers GDPR
            $cell2->style = 'text-align:left;'; //width:12%;
            $cell3 = new html_table_cell(fullname($user, false, 'lastname firstname'));
            $cell3->style = 'text-align:left;'; //width:42%;
            $additionals = '';
            if($starfield) {
                $additionals =  ($user->$starfield) ? $user->$starfield : '';
            }
            $cell4 = new html_table_cell($additionals);
            $cell4->style = 'text-align:center;'; //width:4%;
            if($extracontent) {
                $extracols = array();
                foreach($extracontent as $field=>$style) {
                    $col = new html_table_cell($user->$field);
                    $col->style = $style;
                    $extracols[] = $col;
                }
            }
            $row->cells = array_merge(array($cell1, $cell2, $cell3, $cell4), $extracols);
            foreach($row->cells as $i => $cell) {
                $cell->style .= ' width:'.$widths[$i];
                $row->cells[$i] = $cell;
            }

            $table->data[] = $row;
            $index += 1;
        }
        //$usertable = html_writer::table($table);

        return html_writer::table($table);
    }


    /**
     * Generate HTML table of users (num, IDnumner, name) with additional extra columns if appropiate
     *
     * @param array $users collection of users for table, must have id, idnumbre, firstname & lastname fields
     * @param int $width widthd of the table as a whole
     * @param array $widths array of widths of each column in the table, ir order
     * @param array $extraheads array of extra columns in addition to nº, ID, name & additional
     * @param array $star associative array for 'star' column field => value used has head
     * @param array $extracontent associative array for extracolums user field => style string. If empty, a checkbox is added
     * @return string, HTML trable
     */
    public function print_venue_users_table($users, $width, $widths, $extraheads, $extracontent=array()) {
        global $DB;
        $extraheads = array('Pre', 'Ent', 'Cer');
        $extracontent=array();
        $table = new html_table();
        $table->attributes = array('style'=>'border:1px solid black;border-collapse:collapse;');
        $table->width = "$width%";
        $numextra = count($extraheads);
        if(count($widths) != ($numextra + 4)) {
            $widths[0] = '5%';  // num
            $widths[1] = '12%'; // DNI
            $widths[2] = '30%'; // name
            $widths[3] = '4%';  // nº exams
            $widths[4] = '30%'; // exams
            $widths[5] = '4%';  // booked
            foreach($extraheads as $i => $extra)  {
                $widths[$i+6] = round(15/$numextra, 1).'%';
            }
        }
        foreach($extraheads as $i => $head) {
            $head = new html_table_cell($head);
            $head->style = 'text-align:center;border-bottom:1px solid black;';
            $extraheads[$i] = $head;
        }

        $extracols = array();
        if(!$extracontent) {
            $checkbox = new html_table_cell('&#9744;');
            $checkbox->style = 'text-align:center;';  //width:12%;
            $extracols = array_fill(0,$numextra,$checkbox);
        }

        $heads = array();
        $cell = new html_table_cell('');
        $cell->style = 'text-align:right;border-bottom:1px solid black;'; //width:6%;
        $heads[] = $cell;
        $cell = new html_table_cell(get_string('idnumber'));
        $cell->style = 'text-align:left;border-bottom:1px solid black;'; //width:12%;
        $heads[] = $cell;
        $cell = new html_table_cell(get_string('student', 'examregistrar'));
        $cell->style = 'text-align:left;border-bottom:1px solid black;'; //width:42%;
        $heads[] = $cell;
        //$cell = new html_table_cell(get_string('numexams', 'examregistrar'));
        $cell = new html_table_cell('N');
        $cell->style = 'text-align:center;border-bottom:1px solid black;'; //width:4%;
        $heads[] = $cell;
        $cell = new html_table_cell(get_string('exam', 'examregistrar'));
        $cell->style = 'text-align:center;border-bottom:1px solid black;'; //width:4%;
        $heads[] = $cell;
        $cell = new html_table_cell('');
        $cell->style = 'text-align:center;border-bottom:1px solid black;'; //width:4%;
        $heads[] = $cell;
        $heads =  array_merge($heads, $extraheads);

        foreach($heads as $i => $cell) {
            $cell->style .= ' width:'.$widths[$i];
            $heads[$i] = $cell;
        }
        $table->head = $heads; //array_merge(array(' ', get_string('idnumber'), get_string('student', 'examregistrar'), '*'), $extraheads);
        $index = 1;
        $usercount = 0;
        $last = 0;

        foreach($users as $user) {
            $row = new html_table_row();
            if($index % 2 == 1) {
                $row->style = 'background-color:lightgray;';
            }
            $idnumber = '';
            $name = '';
            $numexams = '';
            $count = '';
            if($user->userid != $last) {
                $last = $user->userid;
                $usercount += 1;
                $count = $usercount;
                $idnumber = $user->idnumber;
                $name = fullname($user, false, 'lastname firstname');
                $numexams = $user->numexams;
                $cell1 = new html_table_cell($usercount);
            }
            $cell1 = new html_table_cell($count);
            $cell1->style = 'text-align:right;'; //width:6%;
            $cell2 = new html_table_cell($idnumber);
            $cell2->style = 'text-align:left;'; //width:12%;
            $cell3 = new html_table_cell($name);
            $cell3->style = 'text-align:left;'; //width:42%;
            $cell4 = new html_table_cell($numexams);
            $cell4->style = 'text-align:center;'; //width:4%;
/*
            $cell5 = array();
            $cell6 = array();
            $sql = "SELECT b.id, b.userid, b.examid, c.shortname, c.fullname, ss.roomid
                    FROM {examregistrar_bookings} b
                    JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
                    JOIN {course} c ON c.id = e.courseid
                    LEFT JOIN {examregistrar_session_seats} ss ON  b.userid = ss.userid AND b.examid = ss.examid AND b.bookedsite = ss.bookedsite
                    WHERE b.bookedsite = :bookedsite AND b.booked = 1 AND b.userid = :user
                    ORDER BY c.shortname
                    ";
            if($userexams = $DB->get_records_sql($sql, array('session'=>$user->examsession, 'bookedsite'=>$user->bookedsite, 'user'=>$user->id))) {
                foreach($userexams as $userexam) {
                    $cell5[] = $userexam->shortname.'-'.$userexam->fullname;
                    $cell6[] = $userexam->roomid ? '' : '*';
                }
            }
            */
            $cell5 = new html_table_cell($user->shortname.'-'.$user->fullname);
            $cell5->style = 'text-align:left;'; //width:4%;
            $cell6 = $user->roomid ? '' : '*';
            $cell6 = new html_table_cell($cell6);
            $cell6->style = 'text-align:center;'; //width:4%;

/*
            if($extracontent) {
                $extracols = array();
                foreach($extracontent as $field=>$style) {
                    $col = new html_table_cell($user->$field);
                    $col->style = $style;
                    $extracols[] = $col;
                }
            }
            */
            $row->cells = array_merge(array($cell1, $cell2, $cell3, $cell4, $cell5, $cell6), $extracols);
            foreach($row->cells as $i => $cell) {
                $cell->style .= ' width:'.$widths[$i];
                $row->cells[$i] = $cell;
            }

            $table->data[] = $row;
            $index += 1;
        }
        //$usertable = html_writer::table($table);

        return html_writer::table($table);
    }


    public function list_allocatedrooms($rooms, $session, $downloading=false) {
            foreach($rooms as $i=>$room) {
                $staffers = examregistrar_get_room_staffers($room->roomid, $session);
                $users = array();
                foreach($staffers as $staff) {
                    $name = fullname($staff);
                    $role = ' ('.$staff->role.')';
                    $users[] = $name.$role;
                }
                $rooms[$i] = $room->name. ' ('.$room->allocated.')'.
                            '<br />'.get_string('staffers', 'examregistrar').
                            html_writer::alist($users);
            }
        return html_writer::alist($rooms, array('class'=>' roomexamlist '));
    }
 
}
