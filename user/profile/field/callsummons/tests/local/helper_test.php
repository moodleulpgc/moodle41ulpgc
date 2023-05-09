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

/**
 * Task tests class
 *
 * @package profilefield_callsummons
 * @category test
 * @copyright
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \profilefield_callsummons\local\helper
 */
class helper_test extends \advanced_testcase {
    /**
     * Tests get_enabled_fields method.
     *
     * @return void
     */
    public function test_get_enabled_fields(): void {
        global $DB;
        // TODO Add provider.
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $group = $this->getDataGenerator()->create_group(array('courseid' => $course->id, 'name' => 'C56'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        groups_add_member($group->id, $user->id);

        // Add a custom field of callsummons type.
        $id1 = $this->getDataGenerator()->create_custom_profile_field([
            'shortname' => 'lastcalls', 'name' => 'Courses in latest calls',
            'datatype' => 'callsummons', 'param2' => 'C56'])->id;

        $helpertest = new helper();
        $fields = $helpertest->get_enabled_fields();

        $this->assertEquals([], $fields);

        $DB->set_field('user_info_field', 'param1', '1', ['id' => $id1]);

        $fields = $helpertest->get_enabled_fields();
        $this->assertEquals([$id1], array_keys($fields));
    }

    /**
     * Tests get_users_courses method.
     *
     * @return void
     */
    public function test_get_users_courses(): void {

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $group = $this->getDataGenerator()->create_group(array('courseid' => $course->id, 'name' => 'C56'));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course2->id, 'name' => 'C56'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->getDataGenerator()->enrol_user($user->id, $course2->id);
        groups_add_member($group->id, $user->id);
        groups_add_member($group2->id, $user->id);

        // Add a custom field of callsummons type.
        $id1 = $this->getDataGenerator()->create_custom_profile_field([
            'shortname' => 'lastcalls', 'name' => 'Courses in latest calls',
            'datatype' => 'callsummons', 'param1' => '1', 'param2' => 'C56'])->id;

        $profilefield = new \stdClass();
        $profilefield->id = $id1;
        $helpertest = new helper();
        $users = $helpertest->get_users_courses($profilefield);

        $this->assertEquals([$course->id, $course2->id], array_keys($users[$user->id]));
    }
}
