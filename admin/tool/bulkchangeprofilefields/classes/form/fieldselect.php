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
 * Select Update user's profile field to change form.
 *
 * @package     tool_bulkchangeprofilefields
 * @copyright   2022 Daniel Neis Araujo <daniel@adapta.online>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_bulkchangeprofilefields\form;

class fieldselect extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        $categories = profile_get_user_fields_with_data_by_category(0);
        foreach ($categories as $categoryid => $fields) {
            // Check first if *any* fields will be displayed.
            $fieldstodisplay = [];

            foreach ($fields as $formfield) {
                if ($formfield->is_editable()) {
                    $fieldstodisplay[] = $formfield;
                }
            }

            if (empty($fieldstodisplay)) {
                continue;
            }

            $optgroup = format_string($fields[0]->get_category_name());
            foreach ($fieldstodisplay as $formfield) {
                $options[$optgroup][$formfield->fieldid] = $formfield->field->name;
            }
        }

        $attributes = ['multiple' => true, 'size' => 10];
        $mform->addElement('selectgroups', 'field', get_string('selectfield', 'tool_bulkchangeprofilefields'), $options, $attributes);
        $mform->addRule('field', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true, get_string('next', 'tool_bulkchangeprofilefields'));
    }
}
