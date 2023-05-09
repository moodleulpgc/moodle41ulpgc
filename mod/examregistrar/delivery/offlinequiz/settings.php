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
 * Settings for examdelivery method offlinequiz.
 *
 * @package examdelivery_offlinequiz
 * @copyright 2023 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('examdelivery_offlinequiz/enabled',
                    new lang_string('enabled', 'examdelivery_offlinequiz'),
                    new lang_string('enabled_help', 'examdelivery_offlinequiz'), 1));

    $settings->add(new admin_setting_configtext('examdelivery_offlinequiz/examprefix', get_string('examprefix', 'examdelivery_offlinequiz'),
                       get_string('examprefix_help', 'examdelivery_offlinequiz'), 'EXAM', PARAM_ALPHANUMEXT, '8'));
    
}
