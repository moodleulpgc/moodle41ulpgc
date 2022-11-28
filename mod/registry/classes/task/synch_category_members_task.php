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
 * A scheduled task for registry reminders.
 *
 *
 * @package    mod_registry
 * @copyright  2018 Enrique castro  @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_registry\task;

/**
 * Simple task to run the cron.
 */
class synch_category_members_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('synchtask', 'registry');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;

        if($registries = $DB->get_records('registry', array('syncroles'=>1))) {
            include_once($CFG->dirroot.'/mod/registry/locallib.php');
            
            foreach($registries as $registry) {
                $category = registry_get_coursecategory($registry);
                $catcontext = \context_coursecat::instance($category);
                $coursecontext = \context_course::instance($registry->course);
                $config = get_config('registry');
                $roles = explode(',', $config->rolesreviewers);
                if($users = get_role_users($roles, $coursecontext, false, 'u.id ', null, false)) {
                    if($assigned = get_role_users($config->reviewerrole, $catcontext, false, 'u.id ', null, true)) {
                        foreach($assigned as $user) {
                            unset($users[$user->id]);
                        }
                    }
                    $now = time();
                    foreach($users as $user) {
                        role_assign($config->reviewerrole, $user->id, $catcontext, '', 0, $now);
                    }
                }
            }
        }

        return true;
    }
}
