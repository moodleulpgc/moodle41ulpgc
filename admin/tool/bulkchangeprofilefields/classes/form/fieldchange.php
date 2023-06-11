<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Update user's profile field form.
 *
 * @package     tool_bulkchangeprofilefields
 * @copyright   2022 Daniel Neis Araujo <daniel@adapta.online>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_bulkchangeprofilefields\form;

class fieldchange extends \moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        foreach ($this->_customdata['fieldids'] as $fieldid) {
            $mform->addElement('hidden', 'fieldids['.$fieldid.']', $fieldid);
            $mform->setType('fieldids['.$fieldid.']', PARAM_INT);
            $field = $DB->get_record('user_info_field', ['id' => $fieldid]);
            require_once($CFG->dirroot . '/user/profile/field/' . $field->datatype . '/field.class.php');
            $classname = 'profile_field_' . $field->datatype;
            $field->hasuserdata = !empty($field->hasuserdata);
            $fieldobject = new $classname($field->id, 0, $field);

            $fieldobject->edit_field($mform);
        }

        $this->add_action_buttons(true, get_string('change', 'tool_bulkchangeprofilefields'));
    }
}
