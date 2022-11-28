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
 * Event observer for course format topicgroup plugin.
 *
 * @package    format_topicgroup
 * @copyright  2016 Enrique Castro @ ULPGC 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Event observer course format topicgroup.
 *
 * @package    format_topicgroup
 * @copyright  2016 Enrique Castro @ ULPGC 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_topicgroup_observer {

    /**
     * Triggered via course_module_created event.
     *
     * @param \core\event\user_enrolment_created $event
     * @return bool true on success.
     */
    public static function module_created(\core\event\base $event) {
        global $DB;
        // handle event
        $format = course_get_format($event->courseid)->get_format();
        if($format == 'topicgroup') {
            $section = $DB->get_field('course_modules', 'section', array('id'=>$event->objectid, 'course'=>$event->courseid));
            $groupingid = $DB->get_field('format_topicgroup_sections', 'groupingid', array('id'=>$section, 'course'=>$event->courseid));
            if($section && $groupingid) {
                require_once($CFG->dirroot.'/course/format/topicgroup/lib.php');
                format_topicgroup_mod_restrictions($section);
            }
        }
   
        return true;
    }

    /**
     * Triggered via course_module_updated event.
     *
     * @param \core\event\course_module_updated $event
     * @return bool true on success.
     */
    public static function module_updated(\core\event\base $event) {
    
        self::module_created($event);

        return true;
    }

    
    /**
     * Triggered via course_updated event.
     *
     * @param \core\event\course_updated $event
     * @return bool true on success.
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $DB;
        
        if(isset($event->other['updatedfields']['format'])) {
            $format = $event->other['updatedfields']['format'];
            if($format == 'topicgroup') {
                // managed by ::update_course_format_options()
                return;
            }

            $context = context_course::instance($event->courseid);
            if(empty($context)) {
                return;
            }
            
            $config = get_config('format_topicgroup');
            $editingroles = explode(',', $config->editingroles);
            $restrictedroles = explode(',', $config->restrictedroles);

            if(!$restrictedroles) {
                return;
            }
            
            $caps = ['moodle/course:setcurrentsection'];
            
            $select = "courseid = :courseid AND format = 'topicgroup'  AND name = 'accessallgroups' ";
            if($DB->record_exists_select('course_format_options', $select,  ['courseid' => $event->courseid] )) {
                //we need to restore permissions
                $caps[] = 'moodle/site:accessallgroups';
            }
            
            $select = "courseid = :courseid AND format = 'topicgroup'  AND name = 'manageactivities' ";
            if($DB->record_exists_select('course_format_options', $select,  ['courseid' => $event->courseid])) {
                //we need to restore permissions
                $caps =array_merge($caps,  ['moodle/course:manageactivities', 
                                                                'moodle/course:enrolconfig',
                                                                'moodle/course:movesections',
                                                                'moodle/course:sectionvisibility',
                                                                'moodle/course:update',
                                                                'moodle/filter:manage',
                                                                'moodle/grade:manage',
                                                                'moodle/competency:coursecompetencymanage',]);
            }

            foreach($restrictedroles as $role) {
                foreach($caps  as $cap) { 
                    // permission 0, inherit
                    role_change_permission($role, $context, $cap, CAP_INHERIT);
                }
            }
            
        }

        return true;
    }    
    
     
}
