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
 * Ad-hoc task to sync group and channel memberships in connected courses.
 *
 * @package local_o365teams
 * @author Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2022 onwards Enrique Castro

 */

namespace local_o365teams\task;

use core\task\adhoc_task;
use local_o365teams\coursegroups\teamschannels;
use local_o365teams\coursegroups\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * Ad-hoc task to sync group memberships in connected courses.
 */
class teamsmembershipsync extends adhoc_task {
    /**
     * Check if the course sync feature is enabled, get all courses that are enabled for sync, and resync owners and members.
     *
     * @return false|void
     */
    public function execute() {
        if (utils::is_connected() !== true || \local_o365\feature\coursesync\utils::is_enabled() !== true) {
            return false;
        }

        try {
            $graphclient = utils::get_graphclient();
            //TODO Unified de o365teams
            
        } catch (\Exception $e) {
            utils::debug('Exception: ' . $e->getMessage(), __METHOD__, $e);
            return false;
        }

        $teamschannelsync = new teamschannels($graphclient, true);
        $groupsenabled = $teamschannelsync->get_enabled_groups();
        foreach ($groupsenabled as $groupid) {
            $teamschannelsync->resync_channel_owners_and_members($groupid);
        }        
    }
}
