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
 * Event observer for ulpgcunits enrolment plugin.
 *
 * @package    enrol_ulpgcunits
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/enrol/ulpgcunits/locallib.php');

/**
 * Event observer for enrol_ulpgcunits.
 *
 * @package    enrol_ulpgcunits
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_ulpgcunits_observer extends enrol_ulpgcunits_handler {

    /**
     * Triggered via course_created or course_restored or course_updated event.
     * Checks if course has ulpgcunits with autoupdated category
     *
     * @param \core\event\course_updated $event
     * @return bool true on success
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $DB;
        if (!enrol_is_enabled('ulpgcunits')) {
            // This is slow, let enrol_meta_sync() deal with disabled plugin.
            return true;
        }

        // Check if changed course has ulpgcunits enrol with autocategory 
        $customchar1_int = $DB->sql_cast_char2int('e.customchar1');
        $sql = "SELECT e.*
                      FROM {enrol} e 
                        JOIN {course} c ON c.id = e.courseid                    
                    WHERE e.enrol = 'ulpgcunits' AND e.customint3 = 1 AND e.courseid = :courseid AND ( $customchar1_int < 0 ) ";
        $params = array('courseid'=>$event->objectid);
        if(!$instances = $DB->get_records_sql($sql, $params)) {
            return true;
        }
        
        // if we are here, the course has changed ulpgcunits category source just sync
        $trace = new text_progress_trace();
        $plugin = enrol_get_plugin('ulpgcunits');
        foreach($instances as $instance) {
            $idnumber = $plugin->get_unit_from_input($instance->customchar1, $instance->courseid);
            if($idnumber != $instance->customchar2) {
                $data = new \stdClass();
                $data->customchar2 = $idnumber;
                $plugin->update_instance($instance, $data);
                enrol_ulpgcunits_sync($trace, $instance->courseid, $instance->category);
            }
        }
        $trace->finished();

        return true;
    }
    
}
