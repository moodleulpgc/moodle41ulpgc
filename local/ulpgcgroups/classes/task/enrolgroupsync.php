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
 * @package local_ulpgcgroups
 * @author James McQuillan <james.mcquillan@remote-learner.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_ulpgcgroups\task;

/**
 * Scheduled task to sync users with Azure AD.
 */
class enrolgroupsync extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_enrolgroupsync', 'local_ulpgcgroups');
    }


    protected function mtrace($msg) {
        mtrace('...... '.$msg);
    }

    /**
     * Do the job.
     */
    public function execute() {
        global $CFG;
        
        $enrol_groupsync = get_config('enrol_groupsync');
        if(!empty($enrol_groupsync)) {
            $config = get_config('local_ulpgcgroups');
            if($config->enrolgroupsyncenabled) {
                require_once("$CFG->dirroot/enrol/groupsync/locallib.php");
                mtrace('local_ulpgcgroups enrolgroupsync call to enrol_groupsync');
                enrol_groupsync_sync(null,  true);            
            } else {
                mtrace('local_ulpgcgroups enrolgroupsync NOT enabled');
            }
        } else {
            mtrace('enrol_groupsync NOT installed');
        }
        return true;
    }
}
