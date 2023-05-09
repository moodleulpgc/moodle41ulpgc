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

namespace profilefield_callsummons\local;
use html_writer;

/**
 * Helper class for the profilefield_callsummons plugin.
 *
 * @package    profilefield_callsummons
 * @copyright  ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /** @var array Groups to check */
    private $groupstocheck = [];

    /**
     * Returns the callsummons enabled fields.
     *
     * @return array Callsummons fields indexed by id or empty array if no callsummons fields enabled.
     */
    public function get_enabled_fields(): array {
        global $DB;

        $select = $DB->sql_compare_text('param1') . ' = ' . $DB->sql_compare_text(':param1');

        $params = ['datatype' => 'callsummons', 'param1' => '1'];
        $fields = $DB->get_records_select('user_info_field', $select, $params);

        return $fields;
    }

    /**
     * Returns the callsummons user data field.
     *
     * @return string contente  Callsummons fields indexed by id or empty array if no callsummons fields enabled.
     */
    public function get_field_data(int $userid, int $fieldid): string {
        global $DB;
        $data = $DB->get_field('user_info_data', 'data', array('userid' => $userid,  'fieldid' => $fieldid));
        
        return $data;
    }
    
    
    /**
     * Returns the groups defined for a callsummons profile field.
     *
     * @param \stdClass $profilefield The callsummon profile field.
     * @return array Group defined for a callsummons profile field.
     */
    public function get_groups_lastcalls($profilefield) {
        global $DB;

        $id = $profilefield->id;

        if (empty($this->groupstocheck[$id])) {
            $field = $DB->get_record('user_info_field', array('id' => $id));
            if (!empty($field)) {
                $this->groupstocheck[$id] = explode(',', $field->param2);
            }
        }
        return $this->groupstocheck[$id];
    }

    /**
     * Content to display in the profile page
     *
     * @param int|null $userid
     * @param \stdClass $profilefield
     * @return string
     */
    public function get_profile_content(int $userid,  bool $iscurrentuser, \stdClass $profilefield) {
        global $USER;

        $data = '';
        $profileshortname = $profilefield->shortname;
        if ($iscurrentuser && isset($USER->profile[$profileshortname])) {
            $data = $USER->profile[$profileshortname];
        } else {
            $data = $this->get_field_data($userid, $profilefield->id);
        }
        
        $courselisting = '';
        if(!empty($data)) {
            $courses = unserialize($data);
            if (is_array($courses) && !empty($courses)) {
                    $courselisting = html_writer::start_tag('ul');
                    foreach ($courses as $courseid => $timedismissed) {
                        $course = get_fast_modinfo($courseid)->get_course();
                        $context = \context_course::instance($courseid);
                        if ($iscurrentuser || has_capability('moodle/grade:manage', $context)  ) {
                            $linkattributes = null;
                            if($course->visible == 0) {
                                if(!has_capability('moodle/course:viewhiddencourses', $context)) {
                                    continue;
                                }
                                $linkattributes['class'] = 'dimmed';
                            } 
                            $url = new \moodle_url('/user/view.php', ['id' => $userid, 'course' => $courseid]);
                            $dismissed = $timedismissed ?  get_string('userdismissed', 'profilefield_callsummons', userdate($timedismissed)) : '';
                            $courselisting .= html_writer::tag('li', 
                                                            html_writer::link($url, $context->get_context_name(false),$linkattributes).$dismissed);
                        }
                    }
                    $courselisting .= html_writer::end_tag('ul');
            }            
        }
        
        return $courselisting;
    }

    
    
    /**
     * Unserializse and formats data to human readable content
     *
     * @param int $userid
     * @param stdClass $data
     * @return string
     */
    public function get_content_data(int $userid, bool $iscurrentuser, string $data) {

        return $courselisting;
    }
    
    
    
    /**
     * Returns all the users in the groups of a callsummon profile field.
     *
     * @param \stdClass $profilefield The profile field.
     * @return array Courses in which the students are members of any of the groups of the profile field.
     */
    public function get_users_courses(\stdClass $profilefield) {
        global $DB;

        $users = [];
        $lastcallgroups = $this->get_groups_lastcalls($profilefield);
        [$insql, $inparams] = $DB->get_in_or_equal($lastcallgroups);
        $sql = "SELECT * FROM {groups} WHERE name $insql";
        $allgroups = $DB->get_records_sql($sql, $inparams);
        foreach ($allgroups as $group) {
            $groupmembers = groups_get_members($group->id);
            foreach ($groupmembers as $member) {
                $users[$member->id][$group->courseid] = null;
            }
        }

        return $users;
    }

    /**
     * Records when a user dismisses a course warning.
     *
     * @param int|null $userid Id of the user who dismiss the course warning.
     * @param int $profilefieldid Id of the profile field for which the warning is dismissed.
     * @param int $contextid Context id of the course where the warning is dismissed.
     * @return bool
     */
    public function set_time_dismiss(?int $userid, int $profilefieldid, int $contextid): bool {
        global $DB, $USER;

        if ($userid === null) {
            $userid = $USER->id;
        }

        $context = \context::instance_by_id($contextid);
        if ($context instanceof \context_course) {
            $record = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $profilefieldid]);
            $data = unserialize($record->data);
            $data[$context->instanceid] = time();
            $data = serialize($data);
            return $DB->set_field('user_info_data', 'data', $data, ['userid' => $userid, 'fieldid' => $profilefieldid]);
        }

        return false;
    }

    /**
     * Reactivates the display of the course warnings for all the users.
     *
     * @param int $profilefieldid Id of the profile field whose warnings are reactivated.
     * @return bool
     */
    public function unset_time_dimiss(int $profilefieldid): bool {
        global $DB;
        // Get all the fields.
        $records = $DB->get_records('user_info_data', ['fieldid' => $profilefieldid], 'id ASC');
        foreach ($records as $record) {
            $courses = unserialize($record->data);
            $data = array_fill_keys($courses, null);
            $data = serialize($data);
            $DB->set_field('user_info_data', 'data', $data, ['userid' => $record->userid, 'fieldid' => $record->fieldid]);
        }
        return true;
    }
}
