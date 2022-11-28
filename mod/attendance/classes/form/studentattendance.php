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
 * Student form class.
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_attendance\form;

/**
 * Class studentattendance
 *
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class studentattendance extends \moodleform {
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $USER;

        $mform  =& $this->_form;

        $attforsession = $this->_customdata['session'];
        $attblock = $this->_customdata['attendance'];
        $password = $this->_customdata['password'];

        $statuses = $attblock->get_statuses();
        // Check if user has access to all statuses.
        $disabledduetotime = false;
        foreach ($statuses as $status) {
            if ($status->studentavailability === '0') {
                unset($statuses[$status->id]);
            }
            if (!empty($status->studentavailability) &&
                time() > $attforsession->sessdate + ($status->studentavailability * 60)) {
                unset($statuses[$status->id]);
                $disabledduetotime = true;
            }
        }

        $mform->addElement('hidden', 'sessid', null);
        $mform->setType('sessid', PARAM_INT);
        $mform->setConstant('sessid', $attforsession->id);

        $mform->addElement('hidden', 'sesskey', null);
        $mform->setType('sesskey', PARAM_INT);
        $mform->setConstant('sesskey', sesskey());

        // Set a title as the date and time of the session.
        $sesstiontitle = userdate($attforsession->sessdate, get_string('strftimedate')).' '
                .attendance_strftimehm($attforsession->sessdate);

        $mform->addElement('header', 'session', $sesstiontitle);

        // If a session description is set display it.
        if (!empty($attforsession->description)) {
            $mform->addElement('html', $attforsession->description);
        }
        if (!empty($attforsession->studentpassword)) {
            $mform->addElement('text', 'studentpassword', get_string('password', 'attendance'));
            $mform->setType('studentpassword', PARAM_TEXT);
            $mform->addRule('studentpassword', get_string('passwordrequired', 'attendance'), 'required');
            $mform->setDefault('studentpassword', $password);
        }
        if (!$attforsession->autoassignstatus) {

            // Create radio buttons for setting the attendance status.
            $radioarray = array();
            foreach ($statuses as $status) {
                $name = \html_writer::span($status->description, 'statusdesc');
                $radioarray[] =& $mform->createElement('radio', 'status', '', $name, $status->id, array());
            }
            if ($disabledduetotime) {
                $warning = \html_writer::span(get_string('somedisabledstatus', 'attendance'), 'somedisabledstatus');
                $radioarray[] =& $mform->createElement('static', '', '', $warning);
            }
            // Add the radio buttons as a control with the user's name in front.
            $radiogroup = $mform->addGroup($radioarray, 'statusarray', fullname($USER).':', array(''), false);
            $radiogroup->setAttributes(array('class' => 'statusgroup'));
            $mform->addRule('statusarray', get_string('attendancenotset', 'attendance'), 'required', '', 'client', false, false);
        }
        // ecastro ULPGC
        $rows = $attforsession->seatrows; 
        $columns = $attforsession->seatcols;
        $blockings = explode(',', $attforsession->seatblocking);
        $repeat = $columns +1;        
        
        $used = array();
        $selected = 0;
        if($logs = $attblock->get_session_log($attforsession->id)) {
            foreach($logs as $key => $log) {
                if($log->seat) {
                    $used[$key] = $log->seat;
                }
                if($log->studentid == $USER->id) {
                    $selected = $log->seat;
                }
            }
        }
        if($blockings) {
            foreach($blockings as $block) {
                $used[] = trim($block);
            }
        }
        
        $takenstr = get_string('seated', 'attendance');
        
        if(!empty(get_config('attendance', 'seatingactivitylevel')) && $attblock->seating &&  $rows > 1 && $columns > 1) {
        
            $mform->addElement('static', 'seatblackboard', '', get_string('seatblackboard', 'attendance'));
        
            $radioarray=array();
            $radioattrs = array('type' => 'radio', 'name' => 'seat', 'class' => 'form-seat');
            
            $radioarray[] =  \html_writer::span(get_string('seatcol', 'attendance'), 'colheader'); 
            foreach(range(1, $attforsession->seatcols) as $col ) {
                $radioarray[] = \html_writer::span($col, 'seat'); 
            }
            
            $seat = new \stdClass();
            foreach(range(1,$attforsession->seatrows) as $row) {
                $radioarray[] =  \html_writer::span(get_string('seatrow', 'attendance', $row), 'rowheader'); 
                foreach(range(1, $attforsession->seatcols) as $col ) {
                        unset($radioattrs['checked']);
                        $radioattrs['value'] = $row.'_'.$col;
                        $id = 'id_seat_'.$radioattrs['value'];
                        $radioattrs['id'] = $id;
                        $seat->row = $row;
                        $seat->col = $col;
                        if((array_search($radioattrs['value'], $used) !== false) && ($selected != $radioattrs['value'])) {
                            $value = $radioattrs['value']. ' ' .'taken';
                            $value = \html_writer::label(get_string('seatlabel', 'attendance', $seat), $id).' '.$takenstr;
                        } else {
                            if($selected == $radioattrs['value']) {
                                $radioattrs['checked'] = 'checked';
                            }
                            $value = \html_writer::label(get_string('seatlabel', 'attendance', $seat), $id).\html_writer::empty_tag('input', $radioattrs);
                        }
                        $radioarray[] = \html_writer::span($value, 'seat'); 
                }
            }

            
            
            $mform->addElement('html', '<div class="form-group row  fitem">');
            $gridkey =  "grid-template-columns: repeat($repeat, 1fr)";
            $mform->addElement('html', '<div class="col-md-3"></div>');
            $mform->addElement('html', '<div class="seatsgrid col-md-9" style="display:grid; '.$gridkey.';">');
            $mform->addElement('html', implode('', $radioarray));
            
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</div>');
        }
        // ecastro ULPGC
        $this->add_action_buttons();
    }

    /**
     * Validate Form.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB, $USER;
        $errors = array();
        if (!($this->_customdata['session']->autoassignstatus)) {
            // Check if this status is allowed to be set.
            if (empty($data['status'])) {
                $errors['statusarray'] = get_string('invalidstatus', 'attendance');
            }
        }
        $session = $this->_customdata['session'];
        $att = $this->_customdata['attendance'];        

        // ecastro ULPGC        
        if($att->seating &&  $session->seatrows && isset($data['seat']) && $data['seat']) {
            $select = 'sessionid = :sid AND seat = :seat AND studentid <> :userid';
            $params = array('sid' => $session->id, 'seat' => $data['seat'], 'userid' => $USER->id);
            if($DB->record_exists_select('attendance_log', $select, $params)) {
                $errors['seat'] = get_string('seattaken', 'attendance');
            }
        }

        return $errors;
    }
}
