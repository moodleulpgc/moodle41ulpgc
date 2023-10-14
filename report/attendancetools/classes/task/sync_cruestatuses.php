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
 * sync attendancetools memberships  task.
 *
 * @package   report_attendancetools
 * @author    Enrique Castro <@ULPGC>
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_attendancetools\task;

use \local_attendancewebhook\lib as attwebhook;

defined('MOODLE_INTERNAL') || die();

/**
 * Meta sync enrolments task.
 *
 * @package   report_attendancetools
 * @author    Enrique Castro <@ULPGC>
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_cruestatuses extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('synccruestatusestask', 'report_attendancetools');
    }

    /**
     * Run task for syncing metacat enrolments.
     */
    public function execute() {
        global $CFG, $DB;

        //include_once($CFG->dirroot.'/local/attendancewebhook/classes/lib.php');
        $statuses = array_combine(attwebhook::STATUS_ACRONYMS,
                                  attwebhook::STATUS_DESCRIPTIONS);

        list($insql, $params) = $DB->get_in_or_equal($statuses);
        $sql = "SELECT cm.id, cm.course, cm.instance
                FROM {course_modules} cm
                JOIN {attendance} a ON cm.course = a.course AND a.id = cm.instance
                WHERE cm.idnumber = 'local_attendancewebhook'
                        AND NOT EXISTS (SELECT 1
                                            FROM {attendance_statuses} s
                                            WHERE s.attendanceid = cm.instance AND s.description $insql) ";
        $rs = $DB->get_recordset_sql($sql, $params);

        $count = 0;
        if($rs->valid()) {
            foreach($rs as $cm) {
                $status = new \stdClass();
                $status->studentavailability = 0;
                $status->availablebeforesession = 0;
                $status->setunmarked = 0;
                $status->visible = 1;
                $status->deleted = 0;
                $status->setnumber = 0;
                $attid = $cm->instance;
                $added = false;
                foreach($statuses as $acronym => $statusdesc) {
                    $params = ['attendanceid' => $cm->instance,
                            'description' => $statusdesc];
                    if(!$DB->record_exists('attendance_statuses', $params)) {
                        $grade = ($acronym == 'A') ? 1 : 2;
                        $status->attendanceid = $attid;
                        $status->acronym = $acronym;
                        $status->description = $statusdesc;
                        $status->grade = $grade;

                        if($id = $DB->insert_record('attendance_statuses', $status)) {
                            $added = true;
                        }

                    }
                }
                if($added) {
                    $count++;
                }
            }
            $rs->close();
        }
        $rs->close();

        mtrace("   ...report_attendancetools sync CRUE statuses done for $count instances");
    }
}
