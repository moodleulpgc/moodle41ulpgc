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
use core\check\result;

/**
 * Verifies installed custom ULPGC roles.
 *
 * @copyright  2023 Enriqe Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class empty_result extends result {

    /**
     * Constructor
     */
    public function __construct() {
        global $DB;    
    
        $this->params = ['modlimit' => 3];
        $this->sqlfrom = " FROM {course} c
                                    JOIN {course_modules} cm ON cm.course = c.id  
                                    JOIN {course_categories} cc ON c.category = cc.id
                                    WHERE c.idnumber IS NOT NULL AND cc.idnumber IS NOT NULL
                                    GROUP BY c.id 
                                    HAVING COUNT(cm.module)  < :modlimit ";
                                    
        
        $count = $DB->count_records_sql("SELECT COUNT(DISTINCT c.id) $this->sqlfrom", $this->params);

        if ($count == 0) {
            $this->status = result::OK;
        } else {
            $this->status = result::WARNING;
        }

        $this->summary = get_string('check_emptycourses_warning', 'tool_ulpgcqc', $count);        
    }


    /**
     * Showing the full list of user may be slow so defer it
     *
     * @return string
     */
    public function get_details(): string {
        global $CFG, $DB;    
        
        $select = "SELECT c.id, c.fullname, c.shortname, c.idnumber, c.category, cc.idnumber AS catidnumber  ";
        
        $courses = $DB->get_records_sql($select.$this->sqlfrom,  $this->params); 
        
        $url = new \moodle_url('/course/view.php');
        
        foreach($courses as $key => $course) {
            $courses[$key] = $course->shortname;
        }
        
        return get_string('check_emptycourses_list', 'tool_ulpgcqc', implode("<br />\n", $courses);
    }
    
}
