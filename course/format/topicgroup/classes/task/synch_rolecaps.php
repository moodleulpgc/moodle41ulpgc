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

namespace format_topicgroup\task;
defined('MOODLE_INTERNAL') || die();

/**
 * A schedule task for updating role permission restricted in course format topicgroup
 *
 * @package   format_topicgroup
 * @copyright 2022 Enrique Castro a
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class synch_rolecaps extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('synchrolecaps', 'format_topicgroup');
    }

    /**
     * Run assignment cron.
     */
    public function execute() {
        global $CFG, $DB;

        $config = get_config('format_topicgroup');

        if($config->synchrolecaps) {
            if($restrictedroles = explode(',', $config->restrictedroles)) {
                //load all courses in this format and with role permissions set to chane ( !=0)
                $select = "format = 'topicgroup' AND ( (name = 'accessallgroups') OR (name = 'manageactivities') ) 
                                AND value  != 0 ";
                $courses = $DB->get_records_select('course_format_options', $select, [],
                                                                            '', 'id, courseid, name, value' );
                //reorganize array to get options per course
                $topicgroups = [];
                foreach($courses as $course) {
                    if(!isset($topicgroups[$course->courseid])) {
                        $topicgroups[$course->courseid] = [];
                    }
                    $topicgroups[$course->courseid][$course->name] = $course->value;
                }
                
                // If there is someting to to, perform change_role_permissions
                if(!empty($topicgroups)) {
                    require_once($CFG->dirroot. '/course/format/topicgroup/lib.php');
                    mtrace('Starting role permission updating for topicgroup courses');
                    foreach($topicgroups as $courseid => $options) {
                        $context = \context_course::instance($courseid);    
                        \format_topicgroup::change_role_permissions($options, $context, $restrictedroles);
                    }
                    $num = count($topicgroups);
                    mtrace("Finished role permission updating for topicgroup courses. Processed $num courses");
                }
            }
        }

        return true;
    }
}
