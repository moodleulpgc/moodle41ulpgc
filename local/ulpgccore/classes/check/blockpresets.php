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
 * Verifies installed custom ULPGC blocks.
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
 * Verifies installed custom ULPGC blocks.
 *
 * @copyright  2023 Enriqe Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class blockpresets extends check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_blockpresets', 'local_ulpgccore');
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     */
    public function get_action_link(): ?\action_link {
        global $CFG;
        return new \action_link(
            new \moodle_url('/local/ulpgccore/blockpresets.php'),
            get_string('blockpresets', 'local_ulpgccore'));
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        global $DB, $CFG;
        $details = '';

        $status = null;
        $expectedblocks = get_config('local_ulpgccore', 'blockpresets');
        
        if(!$expectedblocks) {
            $status  = result::NA;
            $summary = get_string('check_blockpresets_notset', 'local_ulpgccore');
            return new result($status, $summary, $details);
        }

        $expectedblocks = array_map(trim, explode(',', $expectedblocks));
        $blocks = $DB->get_records_menu('block_instances', ['parentcontextid'    => 1, // only in system
                                                                                    'showinsubcontexts'  => 1, // only those in multiple pages
                                                                                    'requiredbytheme' => 0], 
                                                        '', 'id, blockname');
        $missing = [];
        foreach($expectedblocks as $block) {
            if(!in_array($block, $$blocks)) {
                $status  = result::ERROR;
                $summary = get_string('check_blockpresets_missing', 'local_ulpgccore');
                $missing[] = $block;
            }
        }

        if(!empty($missing)) {
            $details = get_string('check_blockpresets_missinglist', 'local_ulpgccore');
            return new result($status, $summary, $details);
        }
        
        // TODO // check for incorrect positions
        
        $status = result::OK;
        $summary = get_string('presetblockstable', 'local_ulpgccore');
        $details = get_string('check_blockpresets_details', 'report_security');
        return new result($status, $summary, $details);
    }
}
