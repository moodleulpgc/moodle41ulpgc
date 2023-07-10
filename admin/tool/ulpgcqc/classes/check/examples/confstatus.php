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
 * Checking XXX
 *
 * text
 * text explain
 *
 * @package    tool_ulpgccore
 * @category   check
 * @copyright  2023 Enrique castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_ulpgcqc\check\config;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;

/**
 * Checking XXX
 *
 * text
 * text explain
 *
 * @package     tool_ulpgcqc
 * @copyright   2023 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confstatus extends check {
    /**
    * Get the short check name
    *
    * @return string
    */
    public function get_name(): string {
        return get_string('check_crawlers_name', 'report_security');
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     */
    public function get_action_link(): ?\action_link {
        $url = new \moodle_url('/mod/myplugin/dosomething.php');
        return new \action_link($url, get_string('sitepolicies', 'admin'));
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        if (some_check()) {
            $status = result::ERROR;
            $summary = get_string('check_foobar_error', 'mod_myplugin');
        } else {
            $status = result::OK;
            $summary = get_string('check_foobar_ok', 'mod_myplugin');
        }
        $details = get_string('check_details', 'mod_myplugin');
        return new result($status, $summary, $details);
    }
}
