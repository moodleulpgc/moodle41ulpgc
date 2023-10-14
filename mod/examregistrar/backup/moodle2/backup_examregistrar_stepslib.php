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
 * @package    mod_examregistrar
 * @subpackage backup-moodle2
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_examregistrar_activity_task
 */

/**
 * Define the complete examregistrar structure for backup, with file and id annotations
 */
class backup_examregistrar_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        global $DB;
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
        $userinfo = false; // TODO //TODO TODO
        $registrar_included = false;

        $examregid = $this->task->get_activityid();
        $examreg = $DB->get_record('examregistrar', ['id' => $examregid], 'id, primaryreg, primaryidnumber', MUST_EXIST);
        if(($examreg->primaryreg === '') && !empty($examreg->primaryidnumber)) {
            $registrar_included = true;
        }

        if($this->setting_exists('registrarincluded')) {
            $registrar_included = $this->get_setting_value('registrarincluded');
        }
        $exams_included = false;
        if($this->setting_exists('examsincluded')) {
            $exams_included = $this->get_setting_value('examsincluded');
        }
        //$exams_included = false; // TODO //TODO TODO

        // Define each element separated
        $examregistrar = new backup_nested_element('examregistrar', array('id'), array(
            'name', 'intro', 'introformat', 'primaryreg', 'primaryidnumber', 'annuality', 'programme',
            'workmode', 'reviewmod', 'lagdays', 'configdata', 'timecreated', 'timemodified'));

        $elements = new backup_nested_element('elements');

        $element = new backup_nested_element('element', array('id'), array(
            'type', 'name', 'idnumber', 'value', 'visible', 'modifierid', 'timemodified'));

        $periods = new backup_nested_element('periods');

        $period = new backup_nested_element('period', array('id'), array(
            'period', 'annuality', 'periodtype', 'term', 'calls',
            'timestart', 'timeend', 'visible', 'modifierid', 'timemodified'));

        $sessions = new backup_nested_element('examsessions');

        $session = new backup_nested_element('examsession', array('id'), array(
            'examsession', 'period', 'examdate', 'duration', 'timeslot',
            'component', 'modifierid', 'timemodified'));

        $exams = new backup_nested_element('exams');

        $exam = new backup_nested_element('exam', array('id'), array(
            'annuality', 'programme', 'courseid', 'period',
            'examscope', 'callnum', 'examsession','visible', 'assignplugincm', 'quizplugincm', 'component', 'modifierid', 'timemodified'));

        $examfiles = new backup_nested_element('examfiles');

        $examfile = new backup_nested_element('examfile', array('id'), array(
            'examid', 'status', 'attempt', 'name', 'idnumber', 'taken', 'userid', 'reviewerid', 'reviewid',
            'printmode', 'allowedtools', 'timecreated', 'timeapproved', 'timerejected', 'timemodified'));

        $examdeliveries = new backup_nested_element('examdeliveries');

        $examdelivery = new backup_nested_element('examdelivery', array('id'), array(
            'examid', 'helpermod', 'helpercmid', 'timeopen', 'timeclose', 'timelimit', 'status',
            'parameters', 'bookedsite', 'component', 'modifierid', 'timemodified'));

        $locations = new backup_nested_element('locations');

        $location = new backup_nested_element('location', array('id'), array(
            'location', 'locationtype', 'address', 'addressformat', 'seats',
            'visible', 'parent', 'depth', 'path', 'sortorder', 'component', 'modifierid', 'timemodified'));

        $staffers = new backup_nested_element('staffers');

        $staffer = new backup_nested_element('staffer', array('id'), array(
            'examsession', 'locationid', 'userid', 'role', 'info', 'visible', 'component', 'modifierid', 'timemodified'));

        $bookings = new backup_nested_element('bookings');

        $booking = new backup_nested_element('booking', array('id'), array(
            'examid', 'userid', 'booked', 'bookedsite', 'modifierid', 'timemodified'));
            
        $vouchers = new backup_nested_element('vouchers');

        $voucher = new backup_nested_element('voucher', array('id'), array(
            'bookingid', 'uniqueid', 'timemodified'));

        $rooms = new backup_nested_element('session_rooms');

        $room = new backup_nested_element('session_room', array('id'), array(
            'examsession', 'bookedsite', 'roomid', 'available', 'modifierid', 'timemodified'));

        $rules = new backup_nested_element('seating_rules');

        $rule = new backup_nested_element('seating_rule', array('id'), array(
            'examsession', 'bookedsite', 'examid', 'roomid', 'sortorder', 'modifierid', 'timemodified'));

        $responses = new backup_nested_element('responses');

        $response = new backup_nested_element('response', array('id'), array(
            'examsession', 'examid', 'deliveryid', 'roomid', 'additional',
            'examfile', 'numfiles', 'showing', 'taken', 'status',
            'modifierid', 'reviewerid', 'timefiles', 'timeuserdata', 'timemodified', 'timereviewed'));

        $seatings = new backup_nested_element('session_seats');

        $seat = new backup_nested_element('session_seat', array('id'), array(
            'examsession', 'bookedsite', 'examid', 'userid', 'roomid', 'additional', 'seat',
            'showing', 'taken', 'certified', 'status', 'reviewerid',
            'timecreated', 'component',  'modifierid', 'timemodified', 'timereviewed'));

        $printings = new backup_nested_element('printings');

        $printing = new backup_nested_element('printing', array('id'), array(
            'page', 'element', 'content', 'contentformat', 'visible', 'modifierid', 'timemodified'));

        $pluginconfigs = new backup_nested_element('pluginconfigs');

        $pluginconfig = new backup_nested_element('pluginconfig', array('id'), array(
            'plugin', 'subtype', 'name', 'value'));


        // Build the tree
        $examregistrar->add_child($elements);
        $elements->add_child($element);

        $examregistrar->add_child($periods);
        $periods->add_child($period);

        $examregistrar->add_child($sessions);
        $sessions->add_child($session);

        $examregistrar->add_child($exams);
        $exams->add_child($exam);

        $exam->add_child($examfiles); // examfiles are dependent on examid and exams table
        $examfiles->add_child($examfile);

        $exam->add_child($examdeliveries); // examfiles are dependent on examid and exams table
        $examdeliveries->add_child($examdelivery);

        $examregistrar->add_child($locations);
        $locations->add_child($location);

        $session->add_child($staffers); // $staffers are dependent on examsession table
        $staffers->add_child($staffer);

        $exam->add_child($bookings); // $bookings are dependent on examid and exams table
        $bookings->add_child($booking);

        $booking->add_child($vouchers);  // $vouchers are dependent on bookingid and bookings table
        $vouchers->add_child($voucher);
        
        $session->add_child($rooms); // $rooms are dependent on examsession table
        $rooms->add_child($room);

        $session->add_child($rules); // $rules are dependent on examsession table
        $rules->add_child($rule);

        $session->add_child($responses); // $responses are dependent on examsession table
        $responses->add_child($response);

        $session->add_child($seatings); // $seatings are dependent on examsession table
        $seatings->add_child($seat);

        $examregistrar->add_child($printings);
        $printings->add_child($printing);

        $examregistrar->add_child($pluginconfigs);
        $pluginconfigs->add_child($pluginconfig);

        // Define sources
        $examregistrar->set_source_table('examregistrar', array('id' => backup::VAR_ACTIVITYID));

        if($registrar_included) {
            $element->set_source_table('examregistrar_elements', array('examregid' => backup::VAR_PARENTID), 'id ASC');
            $period->set_source_table('examregistrar_periods', array('examregid' => backup::VAR_PARENTID), 'id ASC');
            $session->set_source_table('examregistrar_examsessions', array('examregid' => backup::VAR_PARENTID), 'id ASC');
            // order is important locations without children first
            $location->set_source_table('examregistrar_locations', array('examregid' => backup::VAR_PARENTID), 'parent ASC, id ASC'); // parent important to store afterwards those with parents
            $printing->set_source_table('examregistrar_printing', array('examregid' => backup::VAR_PARENTID), 'id ASC');
            $pluginconfig->set_source_table('examregistrar_plugin_config', array('examregid' => backup::VAR_PARENTID), 'id ASC');


            if($exams_included) {
                $exam->set_source_table('examregistrar_exams', array('examregid' => backup::VAR_PARENTID));
                $examsfile->set_source_table('examregistrar_examfiles', array('examid' => backup::VAR_PARENTID));
                $examdelivery->set_source_table('examregistrar_examdelivery', array('examid' => backup::VAR_PARENTID));

                if($userinfo) {
                    $booking->set_source_table('examregistrar_booking', array('examid' => backup::VAR_PARENTID));
                    $voucher->set_source_table('examregistrar_vouchers', array('examregid' => backup::VAR_ACTIVITYID,
                                                                               'bookingid' => backup::VAR_PARENTID));
                    $staffer->set_source_table('examregistrar_staffers', array('examsession' => backup::VAR_PARENTID));
                    $room->set_source_table('examregistrar_session_rooms', array('examsession' => backup::VAR_PARENTID));
                    $rule->set_source_table('examregistrar_seating_rules', array('examsession' => backup::VAR_PARENTID));
                    $response->set_source_table('examregistrar_responses', array('examsession' => backup::VAR_PARENTID));
                    $seat->set_source_table('examregistrar_session_seats', array('examsession' => backup::VAR_PARENTID));
                }
            }
        }

        // Define id annotations
        $element->annotate_ids('user', 'modifierid');

        $period->annotate_ids('user', 'modifierid');

        $session->annotate_ids('user', 'modifierid');

        $exam->annotate_ids('user', 'modifierid');

        $examfile->annotate_ids('user', 'userid');
        $examfile->annotate_ids('user', 'reviewerid');

        $examdelivery->annotate_ids('user', 'modifierid');

        $location->annotate_ids('user', 'modifierid');

        $staffer->annotate_ids('user', 'userid');
        $staffer->annotate_ids('user', 'modifierid');

        $booking->annotate_ids('user', 'userid');
        $booking->annotate_ids('user', 'modifierid');

        $room->annotate_ids('user', 'modifierid');

        $rule->annotate_ids('user', 'modifierid');

        $response->annotate_ids('user', 'modifierid');
        $response->annotate_ids('user', 'reviewerid');

        $seat->annotate_ids('user', 'userid');
        $seat->annotate_ids('user', 'modifierid');
        $seat->annotate_ids('user', 'reviewerid');

        // Define file annotations
        $examregistrar->annotate_files('mod_examregistrar', 'intro', null); // This file area hasn't itemid
        /// examfiles are intentionally excluded: no export of exams PDFs

        // Return the root element (examregistrar), wrapped into standard activity structure
        return $this->prepare_activity_structure($examregistrar);
    }
}
