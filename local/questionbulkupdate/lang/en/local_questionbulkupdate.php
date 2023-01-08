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
 * Tool for questions bulk update.
 *
 * @package    local_questionbulkupdate
 * @copyright  2021 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['commonoptionsheader'] = 'Common options';
$string['donotupdate'] = 'Do not update';
$string['navandheader'] = 'Questions bulk update';
$string['pluginname'] = 'Questions bulk update';
$string['privacy:metadata'] = 'The plugin does not store any personal data.';
$string['processed'] = '{$a} questions processed';
$string['selectcategoryheader'] = 'Select category to update questions';
$string['updatequestions'] = 'Update questions';

// ecastro ULPGC 
$string['status'] = 'Visibility status';
$string['status_help'] = 'Change ready/draft/hidden status for all questions. 

Toggle will invert the current status. ';
$string['statusdraft'] = 'Draft';
$string['statushidden'] = 'Hidden';
$string['statusready'] = 'Ready';
$string['statustoggle'] = 'toggle';
$string['ownership'] = 'Ownership';
$string['ownership_help'] = 'The user question creation wil be attributed to. 
The tool only changes ownership of questions that do not have an owner, 
or the owner is not enrolled in the course anymore, unless otherwhise instructed. ';
$string['applyenrolled'] = 'Apply to enrolled teachers'; 
$string['applyenrolled_help'] = 'If marked, then the specified user will be added as question creator 
even for questions having an owner that is currently enrolled in the course. ';
$string['answergrade'] = 'Wrong answers grade';
$string['answergrade_help'] = 'This tool modify the grading fraction of answers having the selected value. 
Those answers will be set to a common grade. Either a fixed value set below or a negative weighting based 
on the number of wrong options (formula weighting): fraction = -1/(n)  where n is the number of non-right answers 
(right answers are those a non-zero positive fraction value). ';
$string['answerwrong'] = 'Answers taken as wrong';
$string['answerwrong_help'] = 'Those answer choices that have a grading fraction selected here 
will be considered wrong and the grading fraction modified as specified below. 

Yoy may select just one or several values. ';
$string['tagsadd'] = 'Add';
$string['tagsremove'] = 'Remove';
$string['tagsmanage'] = 'Tags update';
$string['tagsmanage_help'] = 'Management of question tagging. You can add or remove tags to every question in the affected category. 

The tags adedd or removed are specified below.';
$string['weightfixed'] = 'Specified value';
$string['weightformula'] = '1/n Formula weighting';
$string['updatedquestions'] = '{$a} questions updated';
$string['updatedoptions'] = 'Updated options in {$a} questions';
$string['updatedanswers'] = 'Updated answers grading in {$a} questions';
$string['updatedtags'] = 'Updated tags in {$a} questions';
$string['nothingupdated'] = 'No questions were updated with these parameters.';
