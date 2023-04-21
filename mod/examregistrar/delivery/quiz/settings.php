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
 * Settings for examdelivery method quiz.
 *
 * @package examdelivery_quiz
 * @copyright 2023 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('examdelivery_quiz/enabled',
                    new lang_string('enabled', 'examdelivery_quiz'),
                    new lang_string('enabled_help', 'examdelivery_quiz'), 1));

    $settings->add(new admin_setting_configtext('examdelivery_quiz/examprefix', get_string('examprefix', 'examdelivery_quiz'),
                       get_string('examprefix_help', 'examdelivery_quiz'), 'EXAM', PARAM_ALPHANUMEXT, '8'));
    
    $settings->add(new admin_setting_configduration('examdelivery_quiz/examafter', get_string('examafter', 'examdelivery_quiz'),
                        get_string('examafter_help', 'examdelivery_quiz'), 15*60));

    $settings->add(new admin_setting_configcheckbox('examdelivery_quiz/insertcontrolq', get_string('insertcontrolq', 'examdelivery_quiz'),
                       get_string('insertcontrolq_help', 'examdelivery_quiz'), 0, PARAM_INT));
                        
    $settings->add(new admin_setting_configtext('examdelivery_quiz/controlquestion', get_string('controlquestion', 'examdelivery_quiz'),
                       get_string('controlquestion_help', 'examdelivery_quiz'), '', PARAM_ALPHANUM));
                        
    $settings->add(new admin_setting_configtext('examdelivery_quiz/optionsinstance', get_string('optionsinstance', 'examdelivery_quiz'),
                       get_string('optionsinstance_help', 'examdelivery_quiz'), '', PARAM_ALPHANUM));

    $settings->add(new admin_setting_configtext('examdelivery_quiz/quizoptions', get_string('quizoptions', 'examdelivery_quiz'),
                       get_string('quizoptions_help', 'examdelivery_quiz'), '', PARAM_TEXT));

    
    
}
