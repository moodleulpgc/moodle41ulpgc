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
 * Form for uploading and inspecting responses saved from the emergency download link.
 *
 * @package   quizaccess_wifiresilience
 * @copyright 2017 ETH Zurich (amr.hourani@let.ethz.ch)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_wifiresilience\form;
defined('MOODLE_INTERNAL') || die();

use \moodleform;


/**
 * Form for uploading and inspecting  responses saved from the emergency download link.
 *
 * @copyright  2014 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class inspect_responses extends moodleform {

    /**
     * Define the form.
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('filemanager', 'responsefiles', get_string('responsefiles', 'quizaccess_wifiresilience'),
                null, array('subdirs' => 0, 'return_types' => FILE_INTERNAL));
        $mform->addRule('responsefiles', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('responsefiles', 'responsefiles', 'quizaccess_wifiresilience');

        $this->add_action_buttons(true, get_string('uploadinspection', 'quizaccess_wifiresilience'));
    }
}
