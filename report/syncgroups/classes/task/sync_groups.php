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
 * sync syncgroups memberships  task.
 *
 * @package   report_syncgroups
 * @author    Enrique Castro <@ULPGC>
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_syncgroups\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Meta sync enrolments task.
 *
 * @package   report_syncgroups
 * @author    Enrique Castro <@ULPGC>
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_groups extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('syncgroupstask', 'report_syncgroups');
    }

    /**
     * Run task for syncing metacat enrolments.
     */
    public function execute() {
        global $CFG;
        
        include_once($CFG->dirroot.'/report/syncgroups/locallib.php');
        syncgroups_sync();

        mtrace("   ...report_syncgroups cron done");
    }
}
