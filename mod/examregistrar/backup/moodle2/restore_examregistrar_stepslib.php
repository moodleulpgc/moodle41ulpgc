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
 * Define all the restore steps that will be used by the restore_examregistrar_activity_task
 */

/**
 * Structure step to restore one examregistrar activity
 */
class restore_examregistrar_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('examregistrar', '/activity/examregistrar');
        $userinfo = $this->get_setting_value('userinfo');
        $registrar_included = $this->get_setting_value('examregistrar_registrarincluded');
        $exams_included = $this->get_setting_value('examregistrar_examsincluded');

        if($registrar_included) {
            $paths[] = new restore_path_element('examregistrar_element', '/activity/examregistrar/elements/element');
            $paths[] = new restore_path_element('examregistrar_period', '/activity/examregistrar/periods/period');
            $paths[] = new restore_path_element('examregistrar_examsession', '/activity/examregistrar/examsessions/examsession');
            $paths[] = new restore_path_element('examregistrar_location', '/activity/examregistrar/locations/location');
            $paths[] = new restore_path_element('examregistrar_printing', '/activity/examregistrar/printings/printing');
            $paths[] = new restore_path_element('examregistrar_pluginconfig', '/activity/examregistrar/pluginconfigs/pluginconfig');

        // check  TODO TODO  TODO
        //$this->task->is_samesite()
            if($exams_included) {
                $paths[] = new restore_path_element('examregistrar_exam', '/activity/examregistrar/exams/exam');
                $paths[] = new restore_path_element('examregistrar_examsfile', '/activity/examregistrar/exams/exam/examsfiles/examsfile');
                $paths[] = new restore_path_element('examregistrar_examdelivery', '/activity/examregistrar/exams/exam/examdeliveries/examdelivery');

                if ($userinfo) {
                    $paths[] = new restore_path_element('examregistrar_booking', '/activity/examregistrar/exams/exam/bookings/booking');
                    $paths[] = new restore_path_element('examregistrar_voucher', '/activity/examregistrar/exams/exam/bookings/booking/vouchers/voucher');
                    $paths[] = new restore_path_element('examregistrar_staffer', '/activity/examregistrar/examsessions/examsession/staffers/staffer');
                    $paths[] = new restore_path_element('examregistrar_session_room', '/activity/examregistrar/examsessions/examsession/session_rooms/session_room');
                    $paths[] = new restore_path_element('examregistrar_seating_rule', '/activity/examregistrar/examsessions/examsession/seating_rules/seating_rule');
                    $paths[] = new restore_path_element('examregistrar_response', '/activity/examregistrar/examsessions/examsession/responses/response');
                    $paths[] = new restore_path_element('examregistrar_session_seat', '/activity/examregistrar/examsessions/examsession/session_seats/session_seat');
                }
            }
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_examregistrar($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = time();

        // avoid duplicate primery registrars idnumbers on restore
        if($data->primaryidnumber) {
            if($count = $DB->count_records('examregistrar', array('primaryidnumber' => $data->primaryidnumber))) {
                $data->primaryidnumber .= '_restored_'.$count;
            }
        }

        // insert the examregistrar record
        $newitemid = $DB->insert_record('examregistrar', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_examregistrar_element($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examregid = $this->get_new_parentid('examregistrar');
        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_elements', 'id',
                               array('examregid'=>$data->examregid, 'type'=>$data->type,
                                     'idnumber'=>$data->idnumber));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_elements', $data);

        }
        $this->set_mapping('examregistrar_element', $oldid, $newitemid);
    }

    protected function process_examregistrar_period($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examregid = $this->get_new_parentid('examregistrar');

        $data->period = $this->get_mappingid('examregistrar_element', $data->period);
        $data->annuality = $this->get_mappingid('examregistrar_element', $data->annuality);
        $data->periodtype = $this->get_mappingid('examregistrar_element', $data->periodtype);
        $data->term = $this->get_mappingid('examregistrar_element', $data->term);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->period, $data->annuality, $data->periodtype, $data->term])) {
            return;
        }

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_periods', 'id',
                               array('examregid'=>$data->examregid, 'period'=>$data->period,
                                     'annuality'=>$data->annuality));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_periods', $data);
        }
        $this->set_mapping('examregistrar_period', $oldid, $newitemid);
    }

    protected function process_examregistrar_examsession($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examregid = $this->get_new_parentid('examregistrar');

        $data->examsession = $this->get_mappingid('examregistrar_element', $data->examsession);
        $data->period = $this->get_mappingid('examregistrar_period', $data->period);

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->period, $data->examsession])) {
            return;
        }

        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_examsessions', 'id',
                               array('examregid'=>$data->examregid, 'period'=>$data->period,
                                     'examsession'=>$data->examsession));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_examsessions', $data);
        }
        $this->set_mapping('examregistrar_examsession', $oldid, $newitemid);
    }

    protected function process_examregistrar_location($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examregid = $this->get_new_parentid('examregistrar');
        $data->location = $this->get_mappingid('examregistrar_element', $data->location);
        $data->locationtype = $this->get_mappingid('examregistrar_element', $data->locationtype);

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->location, $data->locationtype])) {
            return;
        }

        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_locations', 'id',
                               array('examregid'=>$data->examregid, 'location'=>$data->location,
                                     'locationtype'=>$data->locationtype));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_examsessions', $data);
        }
        $this->set_mapping('examregistrar_location', $oldid, $newitemid);

        // try to reconstruct parent, if not exists, set to 0 (no hierachy)
        // order is important locations without children first
        // path & depth are re-contructed afterwards in after_restore tasks function
        $newparent = $this->get_mappingid('examregistrar_location', $data->parent, 0);
        $DB->set_field('examregistrar_locations', 'parent', $newparent, ['id' => $newitemid]);
    }

    protected function process_examregistrar_printing($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examregid = $this->get_new_parentid('examregistrar');

        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_printing', 'id',
                               array('examregid'=>$data->examregid, 'page'=>$data->page,
                                     'element'=>$data->element));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_printing', $data);
        }
    }

    protected function process_examregistrar_pluginconfig($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examregid = $this->get_new_parentid('examregistrar');

        $newitemid = $DB->insert_record('examregistrar_examsessions', $data);
    }

    protected function process_examregistrar_exam($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examregid = $this->get_new_parentid('examregistrar');

        $data->annuality = $this->get_mappingid('examregistrar_element', $data->annuality);
        $data->period = $this->get_mappingid('examregistrar_period', $data->period);
        $data->examscope = $this->get_mappingid('examregistrar_element', $data->examscope);
        $data->examsession = $this->get_mappingid('examregistrar_examsession', $data->examsession);

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->annuality, $data->period,
                                                 $data->examscope, $data->examsession   ])) {
            return;
        }

        // check  TODO TODO  TODO
        //$this->task->is_samesite()
        if(!$DB->record_exists('course', ['id' => $data->courseid])) {
            $data->courseid = 0;
        }
        if(!$DB->record_exists('course_modules', ['id' => $data->assignplugincm])) {
            $data->assignplugincm = 0;
        }
        if(!$DB->record_exists('course_modules', ['id' => $data->quizplugincm])) {
            $data->quizplugincm = 0;
        }

        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_exams', 'id',
                               array('examregid'=>$data->examregid, 'annuality'=>$data->annuality,
                                     'courseid'=>$data->courseid, 'period'=>$data->period,
                                     'examscope'=>$data->examscope, 'period'=>$data->callnum));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_exams', $data);
        }
        $this->set_mapping('examregistrar_exam', $oldid, $newitemid);
    }


    protected function process_examregistrar_examsfile($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        // if dependencies not met, do to restore wrong data
        $data->examid = $this->get_new_parentid('examregistrar_exam');
        if(!$data->examid) {
            return;
        }

        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->reviewerid = $this->get_mappingid('user', $data->reviewerid, 0);
        $data->timemodified = time();

        if($data->reviewid && !$DB->record_exists('tracker_issue', ['id' => $data->reviewid])) {
            $data->reviewid = 0;
        }

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_examfiles', 'id',
                               array('examid'=>$data->examid, 'attempt'=>$data->attempt));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_examfiles', $data);
        }
    }


    protected function process_examregistrar_examdelivery($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        // if dependencies not met, do to restore wrong data
        $data->examid = $this->get_new_parentid('examregistrar_exam');
        if(!$data->examid) {
            return;
        }

        if(!$DB->record_exists('course_modules', ['id' => $data->helpercmid])) {
            $data->helpercmid = 0;
        }

        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        $newitemid = $DB->insert_record('examregistrar_examdelivery', $data);

    }


    protected function process_examregistrar_booking($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // if dependencies not met, do to restore wrong data
        $data->examid = $this->get_new_parentid('examregistrar_exam');
        if(!$data->examid) {
            return;
        }

        $data->bookedsite = $this->get_mappingid('examregistrar_location', $data->bookedsite);
        if(!$data->bookedsite) {
            return;
        }

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        $newitemid = $DB->insert_record('examregistrar_exams', $data);
    }


    protected function process_examregistrar_voucher($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->bookingid = $this->get_new_parentid('examregistrar_booking');
        $data->examregid = $this->task->get_activityid();

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->bookingid, $data->examregid])) {
            return;
        }

        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_vouchers', 'id',
                               array('examregid'=>$data->examregid, 'uniqueid'=>$data->attempt));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_vouchers', $data);
        }
    }


    protected function process_examregistrar_staffer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examsession = $this->get_new_parentid('examregistrar_examsession');
        $data->locationid = $this->get_mappingid('examregistrar_element', $data->locationid);
        $data->role = $this->get_mappingid('examregistrar_element', $data->role);

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->examsession, $data->locationid, $data->role])) {
            return;
        }

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_staffers', 'id',
                               array('examsession'=>$data->examsession, 'locationid'=>$data->locationid,
                                     'userid'=>$data->userid, 'role'=>$data->role));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_staffers', $data);
        }
    }

    protected function process_examregistrar_session_room($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examsession = $this->get_new_parentid('examregistrar_examsession');
        $data->bookedsite = $this->get_mappingid('examregistrar_location', $data->bookedsite);
        $data->roomid = $this->get_mappingid('examregistrar_location', $data->roomid);

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->examsession, $data->bookedsited, $data->roomid])) {
            return;
        }

        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_session_rooms', 'id',
                               array('examsession'=>$data->examsession, 'roomid'=>$data->roomid));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_session_rooms', $data);
        }
    }


    protected function process_examregistrar_seating_rule($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examsession = $this->get_new_parentid('examregistrar_examsession');
        $data->bookedsite = $this->get_mappingid('examregistrar_location', $data->bookedsite);
        $data->examid = $this->get_mappingid('examregistrar_exam', $data->examid);
        $data->roomid = $this->get_mappingid('examregistrar_location', $data->roomid);

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->examsession, $data->bookedsited, $data->roomid, $data->examid])) {
            return;
        }

        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        $newitemid = $DB->insert_record('examregistrar_seating_rules', $data);
    }


    protected function process_examregistrar_response($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examsession = $this->get_new_parentid('examregistrar_examsession');
        $data->bookedsite = $this->get_mappingid('examregistrar_location', $data->bookedsite);
        $data->examid = $this->get_mappingid('examregistrar_exam', $data->examid);
        $data->roomid = $this->get_mappingid('examregistrar_location', $data->roomid);
        $data->examfile = $this->get_mappingid('examregistrar_examfile', $data->examfile);

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->examsession, $data->bookedsited,
                                                    $data->roomid, $data->examid, $data->examfile])) {
            return;
        }

        $data->deliveryid = $this->get_mappingid('examregistrar_examdelivery', $data->deliveryid);

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->reviewerid = $this->get_mappingid('user', $data->reviewerid);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_responses', 'id',
                               array('examid'=>$data->examid, 'userid'=>$data->userid));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_responses', $data);
        }
    }


    protected function process_examregistrar_session_seat($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examsession = $this->get_new_parentid('examregistrar_examsession');
        $data->bookedsite = $this->get_mappingid('examregistrar_location', $data->bookedsite);
        $data->examid = $this->get_mappingid('examregistrar_exam', $data->examid);
        $data->roomid = $this->get_mappingid('examregistrar_location', $data->roomid);

        // if dependencies not met, do to restore wrong data
        if(!$this->check_dependencies_not_empty([$data->examsession, $data->bookedsited,
                                                    $data->roomid, $data->examid])) {
            return;
        }

        $data->deliveryid = $this->get_mappingid('examregistrar_examdelivery', $data->deliveryid);

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->reviewerid = $this->get_mappingid('user', $data->reviewerid);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid, $USER->id);
        $data->timemodified = time();

        // insert only if not existing yet (defined by unique index)
        $newitemid = $DB->get_field('examregistrar_session_seats', 'id',
                               array('examsession'=>$data->examsession, 'userid'=>$data->userid,
                                     'additional'=>$data->additional));
        if(!$newitemid) {
            $newitemid = $DB->insert_record('examregistrar_session_seats', $data);
        }
    }


    protected function after_execute() {
        // Add examregistrar related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_examregistrar', 'intro', null);
    }

    /**
     * Test if empty values in important data
     *
     * @param array $params the values to be tested
     * @return bool
     */
    protected function check_dependencies_not_empty(array $params) {
        foreach($params as $value) {
            if(empty($value)) {
                return false;
            }
        }
        return true;
    }

}
