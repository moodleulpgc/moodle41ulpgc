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
 * Create any needed Teams & channel groups in Microsoft 365.
 *
 * @package local_o365teams
 * @author Emrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2022 Enrique Castro
 */

namespace local_o365teams\task;

use local_o365teams\coursegroups\teamschannels;
use local_o365teams\coursegroups\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * Create any needed groups in Microsoft 365.
 */
class teamschannelsync extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_teamschannelsync', 'local_o365teams');
    }

    /**
     * Do the job.
     *
     * @return bool|void
     */
    public function execute() {
        if (utils::is_connected() !== true) {
            return false;
        }

        if (\local_o365\feature\coursesync\utils::is_enabled() !== true) {
            mtrace('Course synchronisation not enabled, skipping...');
            return true;
        }

        try {
            $graphclient = utils::get_graphclient();
            //TODO Unified de o365teams
        } catch (\Exception $e) {
            utils::debug('Exception: ' . $e->getMessage(), __METHOD__, $e);
            return false;
        }

        $teamsync = new teamschannels($graphclient, true);

        $teamsync->sync_channels();
    }
}
