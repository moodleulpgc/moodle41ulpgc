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
 * Verifies installed custom ULPGC user fields.
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
 * Verifies installed custom ULPGC fields.
 *
 * @copyright  2023 Enriqe Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class presetprofilefields extends check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_presetprofilefields', 'local_ulpgccore');
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     */
    public function get_action_link(): ?\action_link {
        global $CFG;
        return new \action_link(
            new \moodle_url('/local/ulpgccore/profilefieldspresets.php'),
            get_string('presetprofilefields', 'local_ulpgccore'));
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        global $DB, $CFG;
        $details = '';

        $status = null;
        $expectedfields = get_config('local_ulpgccore', ' profilefieldpresets');
        
        if(!$expectedfields) {
            $status  = result::NA;
            $summary = get_string('check_presetprofilefields_notset', 'local_ulpgccore');
            return new result($status, $summary, $details);
        }

        $expectedfields = array_map(trim, explode(',', $expectedfields));

        
        $fields = $DB->get_records_menu('user_info_field', [], 'sortorder ASC', 'id, shortname');

        $missing = [];
        foreach($expectedfields as $field) {
            if(!in_array($field, $$fields)) {
                $status  = result::ERROR;
                $summary = get_string('check_presetprofilefields_missing', 'local_ulpgccore');
                $missing[] = $field;
            }
        }

        if(!empty($missing)) {
            $details = get_string('check_presetprofilefields_missinglist', 'local_ulpgccore');
            return new result($status, $summary, $details);
        }
        
        // TODO // check for incorrect positions
        
        $status = result::OK;
        $summary = get_string('presetprofilefieldstable', 'local_ulpgccore');
        $details = get_string('check_presetprofilefields_details', 'report_security');
        return new result($status, $summary, $details);
    }
}
