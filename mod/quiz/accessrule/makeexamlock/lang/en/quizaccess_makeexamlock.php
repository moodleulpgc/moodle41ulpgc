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
 * Strings for the quizaccess_makeexamlock plugin.
 *
 * @package    quizaccess
 * @subpackage makeexamlock
 * @copyright  2016 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();



$string['makeexamlock:viewdesc'] = 'View teacher explanation';
$string['makeexamlock:manage'] = 'Manage exams locking';
$string['makeexamlock:editquestions'] = 'Edit exam questions when locked';
$string['makeexamlock:unlock'] = 'Unlock quiz';
$string['makeexamlockingmsg'] = 'No attempts allowed. Use Edit quiz to compose a quiz and then go to Make Exam.';
$string['pluginname'] = 'Locking by MakeExam';
$string['gotomakeexam'] = 'Use Make Exam to generate an exam version';
$string['makeexamlock'] = 'Make Exam lock';
$string['makeexamlock_help'] = 'The Make Exam lock allows to prevent any student accesss to this quiz. 
Make Exam is a quiz report to generate Exam PDFs from Moodle (working with Exams registrer module). 

In activated, then no user attempts will be allowed. Only teachers could generate previews.';
$string['explainmakeexamlock'] = 'NO user attempts allowed. Quiz used only to generate Make Exam versions.';
$string['enabled'] = 'Makeexam lock attempt enabled';
$string['allowdisable'] = 'The teachers are allowed to disable the rule';
$string['enabledbydefault'] = 'New quizzes will use this rule by default';
$string['examregmode'] = 'Exam registrar lookup mode';
$string['examregmode_desc'] = 'How to find the primary Exam Registrar to manage exams.';
$string['modeexamreg'] = 'Unique registrar primary ID';
$string['modeidnumber'] = 'From Exam registrar instance in the same course';
$string['examreginstance'] = 'Exam registrar instance';
$string['examreginstance_desc'] = 'Either a primary ID or the mod idnumber of an Exam registrar module in the course.';
$string['examprefix'] = 'Prefix in quiz idnumber';
$string['examprefix_desc'] = 'If used, a prefix in quiz instance mod idnumber to define a normalized idnumber to set exam type and call.';
$string['notbookedlockingmsg'] = 'You don\'t have a valid booking for this Exam: {$a}';
$string['notreadylockingmsg'] = 'The Exam is not ready to be taken: {$a}';
$string['singleversionlockingmsg'] = 'Quiz associated to Registered Exam: {$a}';
$string['wrongexammsg'] = 'Available questions do not correspond to this Exam: {$a}';
$string['examchangedmsg'] = 'The Exam has been modified after approval.';
$string['requirebooking'] = 'Require exam booking';
$string['requirebooking_desc'] = 'If enabled, students must have a valid booking for the exam on Exam Registar module for accessing the attenmpts.';
