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
 * @package    tool_ulpgccore
 * @category   check
 * @copyright  2023 Enriqe Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_ulpgcqc\check\courses;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use tool_ulpgcqc\check\courses\empty_result;

/**
 * Verifies installed custom ULPGC roles.
 *
 * @copyright  2023 Enriqe Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class empty extends check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_emptycourses_name', 'tool_ulpgcqc');
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     */
    public function get_action_link(): ?\action_link {
        global $CFG;
        
        $dir = core_component::get_component_directory('managejob_coursetemplate'); 
        if(empty($dir))  {
            $url = '/admin/tool/batchmanage/index.php';
            $action = ['job' => 'coursetemplate'];
            $str = get_string('pluginname', 'managejob_coursetemplate');
        } else {
            $url = '/course/index.php';
            $action = [];
            $str = get_string('allcourses');
        }
        
        return new \action_link(, 
            new \moodle_url($url, $action),
                    get_string($str, 'tool_ulpgcqc'));
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        return new empty_result();
    }
}

