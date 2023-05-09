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
 * English strings for examdelivery_quiz
 *
 * @package    examdelivery_quiz
 * @copyright  2023 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Exam delivery Quiz';
$string['quiz:manage'] = 'Manage Quiz delivery method';
$string['quiz:view'] = 'View and use Quiz delivery method';
$string['enabled'] = 'Enabled';
$string['enabled_help'] = 'If active, this exam delivery methos using Quiz instances will be available 
for examns in the Exam Registrar.. ';
$string['examprefix'] = 'Quiz idnumber prefix';
$string['examprefix_help'] = 'If used, allows to locate instances to be associated with official exams. 
Quiz course modules identified with an idnumber starting by this text will be linked an Exam in the Registrar';
$string['examafter'] = 'Additional time after exam duration';
$string['examafter_help'] = 'An additional time to add to exam duration to allow for delayed acces to exam and allow manual sending after time limit.';
$string['insertcontrolq'] = 'Check & use Control question';
$string['insertcontrolq_help'] = 'Is enabled, then the check for valid questions will include the control question and will add the corntrol question when loading Exam questions.';
$string['controlquestion'] = 'Control question ID';
$string['controlquestion_help'] = 'If a non-zero value, the question with this question ID will be added to all online exams as a control question.';
$string['optionsinstance'] = 'Instance for Options';
$string['optionsinstance_help'] = 'If a non-zero value, the configuration options for all Exam quizzes will be taken from this instance. ';
$string['quizoptions'] = 'Quiz Options fields';
$string['quizoptions_help'] = 'The quiz configuration options to be set automatically for all Exam Quizzes, a a comma-separated list. 
Include just "review" for all review options. ';
