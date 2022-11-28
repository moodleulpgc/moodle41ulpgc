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



class printrooms_renderer extends renderer {

    public function listdisplay_allocatedroom(\examregistrar_allocatedroom $room, $baseurl) {
        $output = '';

        $output .= $this->output->container_start(' allocatedroom ');

        $output .= $this->output->container_start(' allocatedroomheader ');
        $roomname = $this->formatted_itemname($room);
        $output .= $this->output->container($this->output->heading($roomname, 2, ' roomheader '), ' allocatedroomheaderleft ');
//        $params = $baseurl->params(array());
        $url = new moodle_url('/mod/examregistrar/download.php', $baseurl->params(array()) + array('down'=>'printroompdf', 'session'=>$room->session, 'venue'=>$room->venue, 'room'=>$room->get_id()));
        //$output .= $this->output->single_button($url, get_string('downloadroompdf1', 'examregistrar'), '', array('class'=>' mybutton'));
        //$output .= $this->output->single_button($url, get_string('downloadroompdf2', 'examregistrar'), 'post', array('class'=>' mybutton singlebutton ') );

        $output .= $this->output->container($this->output->single_button($url, get_string('downloadroompdf', 'examregistrar'), 'post', array('class'=>' singlelinebutton ')), ' allocatedroomheaderright ');

        $output .= $this->output->container_end();

        $output .= $this->output->container('', ' clearfix ');

        $output .= $this->output->container_start(' allocatedroombody ');

        $staffers = examregistrar_get_room_staffers_list($room->get_id(), $room->session);
        $output.= print_collapsible_region($staffers, 'userlist', 'showhideexamstafflist_'.$room->get_id(), get_string('roomstaff', 'examregistrar'),'examstafflist_'.$room->get_id(), true, true);

//         $staffers = examregistrar_get_room_staffers($room->get_id(), $room->session);
//         $users = array();
//         foreach($staffers as $staff) {
//             $name = fullname($staff);
//             $role = ' ('.$staff->role.')';
//             $users[] = $name.$role;
//         }
//         $output .= get_string('staffers', 'examregistrar').html_writer::alist($users);

        if($room->exams) {
            $room->set_additionals();
            $count = count($room->exams);
            if($room->additionals) {
                $count .= ' + '.count($room->additionals).' '.get_string('additionalexams', 'examregistrar');
            }
            $output .= get_string('allocatedexams', 'examregistrar',  $count);
            $items = array();
            foreach($room->exams as $exam) {
                $head = $this->list_allocatedroomexam($exam);
                $exam->set_users();
                $userlist = $this->exam_users_list($exam->users);
                $bna = '';
                if($exam->bookednotallocated) {
                    $bna = $this->exam_users_list($exam->bookednotallocated, 'error');
                }

                $collapsed = $bna ? false : true;
                $examcontent = print_collapsible_region($userlist.get_string('unallocated', 'examregistrar').$bna,
                                                         'userlist', 'showhideexamuserslist_'.$exam->get_id(), get_string('userlist', 'examregistrar'),'examuserslist_'.$exam->get_id(), $collapsed, true);
                $items[] = $head.$examcontent;
            }
            $output .= html_writer::alist($items, array('class'=>' roomexamlist '));
        }

        if($room->set_additionals()) {
            $i= 0;
            $items = array();
            foreach($room->additionals as $exam) {
                $head = $this->list_allocatedroomexam($exam);
                $exam->users = array();
                $userlist = '';
                $i += count($exam->set_users(true));
                $userlist = $this->exam_users_list($exam->users);
                $list = print_collapsible_region($userlist, 'userlist', 'showhideexamadduserslist_'.$exam->get_id(), get_string('userlist', 'examregistrar'),'examadduserslist_'.$exam->get_id(), true, true);
                $items[] = $head.$list;
            }
            $additionalslist = html_writer::alist($items, array('class'=>' roomexamlist '));
            $info = new \stdClass;
            $info->users = $i;//count($room->additionals);
            $info->exams = count($room->additionals);
            $out = get_string('additionalusersexams', 'examregistrar', $info);
            $out .= $additionalslist;

            //$out .= html_writer::alist($items, array('class'=>' roomextraexamslist '));
            $output .= html_writer::alist(array($out), array('class'=>' roomexamsnolist '));
        }
        $output .= $this->output->container_end();

        $output .= $this->output->container_end();
        return $output;
    }

    public function exam_users_list($users, $classes='') {

        $list = array();
        foreach($users as $user) {
            $name = fullname($user, false, 'lastname firstname');
            $idnumber = $user->idnumber;
            if(!$user->idnumber) {
                $idnumber = implode('', array_fill(1, 8, '0'));
            } else {
                $idnumber = str_pad($idnumber, 8, '0', STR_PAD_LEFT);
            }
            $additional = '';
            if(isset($user->additional) && $user->additional) {
                $additional = ' &nbsp( + '.$user->additional.')';
            }
            $username = $idnumber.' - '.$name.$additional;
            if($classes) {
                $username = html_writer::span($username, $classes);
            }

            $list[] = $username;
        }
        return html_writer::alist($list);
    }    
 
 
 
}

