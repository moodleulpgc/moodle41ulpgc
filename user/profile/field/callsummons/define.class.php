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
 * Contains definition of call summons user profile field.
 *
 * @package    profilefield_callsummons
 * @copyright  ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_define_callsummons extends profile_define_base {

    /**
     * Prints out the form snippet for the part of creating or editing a profile field common to all data types.
     *
     * @param moodleform $form instance of the moodleform class
     */
    public function define_form_common(&$form) {
        parent::define_form_common($form);

        $form->updateElementAttr('required', array('disabled' => true));
        $form->setDefault('required', 0);
        $form->updateElementAttr('locked', array('disabled' => true));
        $form->setDefault('locked', 1);
        $form->updateElementAttr('forceunique', array('disabled' => true));
        $form->updateElementAttr('signup', array('disabled' => true));
        $form->updateElementAttr('visible', array('disabled' => true));
        $form->setDefault('visible', PROFILE_VISIBLE_NONE);
    }

    /**
     * Prints out the form snippet for the part of creating or
     * editing a profile field specific to the current data type
     *
     * @param moodleform $form reference to moodleform for adding elements.
     */
    public function define_form_specific($form) {
        global $PAGE;

        $id = optional_param('id', 0, PARAM_INT);

        // Add elements, set default value and define type of data.
        $form->addElement('hidden', 'defaultdata');
        $form->setDefault('defaultdata', '');
        $form->setType('defaultdata', PARAM_TEXT);

        // Checkbox to enable/disable the plugin.
        $form->addElement('selectyesno', 'param1', get_string('enable', 'profilefield_callsummons'));
        $form->setDefault('param1', 0); // Defaults to 'no'.
        $form->setType('param1', PARAM_BOOL);

        $form->addElement('text', 'param2', get_string('group', 'profilefield_callsummons'));
        $form->setDefault('param2', ''); // Defaults to empty.
        $form->setType('param2', PARAM_TEXT);

        $form->addElement('selectyesno', 'param3', get_string('cancelnotifications', 'profilefield_callsummons'));
        $form->setDefault('param3', 0); // Defaults to 'no'.
        $form->setType('param3', PARAM_BOOL);

        $form->addElement('text', 'param4', get_string('icon', 'profilefield_callsummons'));
        $form->setDefault('param4', ''); // Defaults to empty.
        $form->setType('param4', PARAM_TEXT);

        $form->addElement('selectyesno', 'param5', get_string('iconalwayson', 'profilefield_callsummons'));
        $form->setDefault('param5', 0); // Defaults to empty.
        $form->setType('param5', PARAM_BOOL);

        if ($id) {
            $form->addElement('button', 'reset', get_string('reset', 'profilefield_callsummons'),
                ['data-region' => 'profilefield_callsummons/reset', 'data-profilefieldid' => $id]);
            $PAGE->requires->js_call_amd('profilefield_callsummons/resetcoursewarnings', 'registerEventListeners');
        }
    }
}
