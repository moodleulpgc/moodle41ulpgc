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
 * Scheduled background task for sending automated appointment reminders
 *
 * @package    mod_scheduler
 * @copyright  2016 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_scheduler\task;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../mailtemplatelib.php');

/**
 * Scheduled background task for sending automated appointment reminders
 *
 * @package    mod_scheduler
 * @copyright  2016 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_reminders extends \core\task\scheduled_task {

    /**
     * get_name
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendreminders', 'mod_scheduler');
    }

    /**
     * execute
     */
    public function execute() {

        global $CFG, $DB; // CFG ecastro ULPGC

        $date = make_timestamp(date('Y'), date('m'), date('d'), date('H'), date('i'));

        // Find relevant slots in all schedulers.
        $select = 'emaildate > 0 AND emaildate <= ? AND starttime > ?';
        $slots = $DB->get_records_select('scheduler_slots', $select, array($date, $date), 'starttime');

        //use no replyuser for reminders 
        $noreplyuser = \core_user::get_noreply_user(); // ecastro ULPGC
        $noreplyuser->firstname = get_string('noreplyname', 'scheduler'); 
        
        if(1 or $slots) { 
            require_once($CFG->dirroot.'/mod/scheduler/mailtemplatelib.php');

            foreach ($slots as $slot) {
                // Get teacher record.
                $teacher = $DB->get_record('user', array('id' => $slot->teacherid));

                // Get scheduler, slot and course.
                $scheduler = \mod_scheduler\model\scheduler::load_by_id($slot->schedulerid);
                $slotm = $scheduler->get_slot($slot->id);
                $course = $scheduler->get_courserec();

                // Mark as sent. (Do this first for safe fallback in case of an exception.)
                /* // ecastro ULPGC delay after sending
                $slot->emaildate = -1;   
                $DB->update_record('scheduler_slots', $slot);
                */

                $appointments = 0;
                $msg = 0;
                // Send reminder to all students in the slot.
                foreach ($slotm->get_appointments() as $appointment) {
                    $student = $DB->get_record('user', array('id' => $appointment->studentid));
                    //cron_setup_user($student, $course);
                    $msg += \scheduler_messenger::send_slot_notification($slotm,
                            'reminder', 'reminder', $noreplyuser, $student, $teacher, $student, $course); // ecastro ULPGC no replyuser for teacher sender
                    $appointments += 1;
                    $groupid = $appointment->groupid;
                }

                // ecastro ULPGC 
                // send reminder to teacher
                $student = '';
                $vars = \scheduler_messenger::get_scheduler_variables($scheduler, $slot, $teacher, $student, $course, $teacher);
                if(isset($appointment->groupid) && $appointment->groupid > 0) {
                    $vars['ATTENDANT']     = get_string('groupname', 'scheduler', groups_get_group_name($appointment->groupid));
                    $vars['ATTENDANT_URL'] = $CFG->wwwroot.'/user/view.php?id='.$course->id.'&group='.$groupid;
                } elseif(($slot->exclusivity != 1) &&  ($appointments > 0)) {
                    $vars['ATTENDANT']     = get_string('multiplestudents', 'scheduler', $appointments);
                    $vars['ATTENDANT_URL'] =  $CFG->wwwroot.'/mod/scheduler/view.php?id='.$scheduler->get_cmid().
                                                        '&appointmentid='.$appointment->id.'&course='.$course->id.'&what=viewstudent&subpage=otherstudents';
                } 
                $msg += \scheduler_messenger::send_message_from_template('mod_scheduler', 'reminder', 1, $noreplyuser, $teacher, $course, 'reminder', $vars);

                if($msg) { 
                    $slot->emaildate = -$date;  // ecastro ULPGC store date sent 
                    $DB->update_record('scheduler_slots', $slot);
                }
                // ecastro ULPGC
            }
        }
        //cron_setup_user();
    }
}
