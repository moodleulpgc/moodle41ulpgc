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
 * mod Tracker update priority  task.
 *
 * @package   mod_tracker
 * @author    Enrique Castro <@ULPGC>
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tracker\task;

defined('MOODLE_INTERNAL') || die();

/**
 * mod Tracker update priority task.
 *
 * @package   mod_tracker
 * @author    Enrique Castro <@ULPGC>
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_priority_stack extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('updateprioritytask', 'mod_tracker');
    }

    /**
     * Run task for syncing metacat enrolments.
     */
    public function execute() {
        global $CFG, $DB;
        
        list($insql, $params) = $DB->get_in_or_equal(array('usersupport', 'boardreview'));
        $select = "supportmode $insql ";
        if($trackers = $DB->get_records_select('tracker', $select, $params)) {
            require_once("$CFG->dirroot/mod/tracker/locallib.php");
            mtrace("Processing tracker priority updates ");
            foreach($trackers as $tracker) {
                tracker_update_priority_stack($tracker);
                mtrace("   ... done tracker priority updates on tracker {$tracker->id} ");
            }        
        }
    }
}
