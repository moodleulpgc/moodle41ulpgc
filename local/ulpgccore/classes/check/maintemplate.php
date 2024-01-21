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
 * Verifies installed custom ULPGC roles.
 *
 * @package    local_ulpgccore
 * @category   check
 * @copyright  2023 Enriqe Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ulpgccore\check;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;

/**
 * Verifies installed custom ULPGC roles.
 *
 * @copyright  2023 Enriqe Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customroles extends check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_customroles', 'local_ulpgccore');
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     */
    public function get_action_link(): ?\action_link {
        global $CFG;
        return new \action_link(
            new \moodle_url('/local/ulpgccore/customroles.php'),
            get_string('customroles', 'local_ulpgccore'));
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        global $DB, $CFG;
        $details = '';

//         Se puede hacer varios chequeos y asignar estado según
//         Si todo pasa: result::OK;
//         si un resultado: result::WARNING;
//         si otro resultado result::ERROR o  result::CRITICAL;

        $status = null;
        $expectedroles = get_config('local_ulpgccore', 'rolepresets');
        
        if(!$expectedroles) {
            $status  = result::NA;
            $summary = get_string('check_customroles_notset', 'local_ulpgccore');
            return new result($status, $summary, $details);
        }
        
        $expectedroles = array_map('trim', explode(',', $expectedroles));
        $roles = $DB->get_records_menu('role', [], 'sortorder ASC', 'id, shortname');
        
        $missing = [];
        foreach($expectedroles as $role) {
            if(!in_array($role, $$roles)) {
                $status  = result::ERROR;
                $summary = get_string('check_customroles_missing', 'local_ulpgccore');
                $missing[] = $role;
            }
        }

        if(!empty($missing)) {
            $details = get_string('check_customroles_missinglist', 'local_ulpgccore');
            return new result($status, $summary, $details);
        }
        
        $status = result::OK;
        $summary = get_string('presetrolestable', 'local_ulpgccore');
        $details = get_string('check_customroles_details', 'local_ulpgccore');
        return new result($status, $summary, $details);
    }
}
