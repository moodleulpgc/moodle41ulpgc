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
class referencecourse extends check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_referencecourse', 'local_ulpgccore');
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     */
    public function get_action_link(): ?\action_link {
        global $CFG;
        return new \action_link(
            new \moodle_url('/admin/settings.php', ['section' => 'local_ulpgccore_config_settings']),
            get_string('referencecourse', 'local_ulpgccore'));
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        global $DB, $CFG;
        $details = '';

        $status = null;
        $refcourse = trim(get_config('local_ulpgccore', 'referencecourse'));
        
        if(!$refcourse) {
            $status  = result::NA;
            $summary = get_string('check_referencecourse_notset', 'local_ulpgccore');
            return new result($status, $summary, $details);
        }
        
        $course = $course = $DB->get_record('course', ['shortname' => $refcourse], 'id, shortname, idnumber, category', IGNORE_MISSING);
        
        if(empty($course)) {
            $status  = result::CRITICAL;
            $summary = get_string('check_referencecourse_missing', 'local_ulpgccore');
            return new result($status, $summary, $details);
        }        

        $url = new \moodle_url('/course/view.php', ['id' => $course->id]);        
        $courselink = \html_writer::link($url , get_course_display_name_for_list($course));
        
        if($DB->count_records('course_modules', ['course' => $course->id]) < 4 ) {
            $status  = result::ERROR;
            $summary = get_string('check_referencecourse_empty', 'local_ulpgccore', $courselink);
            return new result($status, $summary, $details);            
        }
        
        $status = result::OK;
        $summary = get_string('referencecourse', 'local_ulpgccore');
        $details = \html_writer::link get_string('check_referencecourse_details', 'local_ulpgccore', $courselink);
        return new result($status, $summary, $details);
    }
}
