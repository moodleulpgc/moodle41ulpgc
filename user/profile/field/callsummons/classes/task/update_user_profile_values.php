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

namespace profilefield_callsummons\task;

defined('MOODLE_INTERNAL') || die();

use profilefield_callsummons\local\helper;

require_once($CFG->dirroot . "/user/profile/lib.php");
/**
 * Task function to update the value of the call summons profile field.
 *
 * @package    profilefield_callsummons
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_user_profile_values extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('updatetaskname', 'profilefield_callsummons');
    }

    /**
     * Execute the task
     *
     * This task fill up the user profile data. It's an array with course ids as keys and NULL as value.
     * A null value means that the warning has not been dismissed.
     */
    public function execute() {
        global $DB;

        $helper = new helper();
        $profilefields = $helper->get_enabled_fields();

        foreach ($profilefields as $profilefield) {

            $usersinfo = $helper->get_users_courses($profilefield);
            $records = $DB->get_records('user_info_data', ['fieldid' => $profilefield->id]);
            $usersdb = [];
            foreach ($records as $record) {
                $usersdb[$record->userid] = unserialize($record->data);
            }

            $users = array_unique(array_merge(array_keys($usersinfo), array_keys($usersdb)));
            // Add new group members to the profile field.
            // Time stamps of dismissed warnings preserved.
            $adding = 0;
            $removing = 0;

            foreach ($users as $userid) {
                $toremove = [];
                $toreadd = [];
                if (empty($usersdb[$userid])) {
                    $toadd = array_keys($usersinfo[$userid]);
                } else {
                    if (isset($usersinfo[$userid])) {
                        $toadd = array_diff(array_keys($usersinfo[$userid]), array_keys($usersdb[$userid]));
                    }
                }
                if (empty($usersinfo[$userid])) {
                    $toremove = array_keys($usersdb[$userid]);
                } else {
                    if (isset($usersdb[$userid])) {
                        $toremove = array_diff(array_keys($usersdb[$userid]), array_keys($usersinfo[$userid]));
                    }
                }

                if($toadd) {
                    $adding++;
                }
                foreach ($toadd as $course) {
                    $usersdb[$userid][$course] = null;
                }
                foreach ($toremove as $course) {
                    unset($usersdb[$userid][$course]);
                }
                // Delete users that don't have any course for the current profile field.
                if (empty($usersdb[$userid])) {
                    unset($usersdb[$userid]);
                    $DB->delete_records('user_info_data', ['userid' => $userid, 'fieldid' => $profilefield->id]);
                    $removing++;
                }
                if (!empty($usersdb[$userid])) {
                    $profiledata = serialize($usersdb[$userid]);
                    profile_save_custom_fields($userid, ["$profilefield->shortname" => $profiledata]);
                }
            }
            mtrace("    ... {$profilefield->shortname}: adding $adding, removing  $removing. ");
        }
    }
}
