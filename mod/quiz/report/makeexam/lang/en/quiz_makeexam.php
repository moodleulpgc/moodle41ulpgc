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
 * Strings for component 'quiz_makeexam', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Make exam';
$string['makeexam'] = 'Make exam';
$string['makeexam:componentname'] = 'Quiz makeexam report';
$string['makeexamreport'] = 'Make exam report';
$string['makeexam:manage'] = 'Manage Make exam report';
$string['makeexam:view'] = 'View Make exam report';
$string['makeexam:submit'] = 'Make and Submit Exam versions';
$string['makeexam:delete'] = 'Delete Exam versions';
$string['makeexam:anyquestions'] = 'Use any question type';
$string['makeexam:nochecklimit'] = 'Not affected by question number limits';

//$string['coursename'] = 'Course name';
//$string['allattempts'] = 'all attempts';
//$string['attemptsall'] = 'all attempts';
$string['attempt'] = 'Attempt';
$string['attemptn'] = 'Attempt {$a}';
$string['attempts'] = 'Attempts';
$string['errornoattempt'] = 'There is no attempt no {$a} for the selected Exam.';

$string['registrarinuse'] = 'Deafult Exam Registrar to use';
$string['configregistrarinuse'] ='The default Examregistrar that contains the exams managed by Make Exam. Identified by the registrar Primary ID codename<br />
Initially Make Exam will check for instances of examregistrar located in the same course as the caller Quiz instance and use that examregistrar.<br />
This is a default to use when no other instance is found.
';
$string['numquestions'] = 'Number of questions per exam';
$string['confignumquestions'] = 'Make Exam may enforce a fixed number of questions in each exam. Leave these blank to eliminate any limit.';
$string['questionspercategory'] = 'Questions per Category';
$string['configquestionspercategory'] = 'Make Exam may enforce a minimum number of questions from each question category in each exam. Leave these blank to eliminate any limit.';
$string['categorysearch'] = 'Category search pattern';
$string['configcategorysearch'] = 'An pattern to identify question categories where to enforce minimum content in the Exam.<br />
The parameter will be used as an SQL pattern in a LIKE statement.';
$string['contextlevel'] = 'Category Context type';
$string['configcontextlevel'] = '

Questions categories are associated to contexts types. This setting allows to indicate the context level where the category search will be performed.

';
$string['excludesubcats'] = 'Exclude sub-categories';
$string['configexcludesubcats'] = '

If set only top categories (without a parent) will be included in the category search.
';
$string['excludeunused'] = 'Exclude unused categories';
$string['configexcludeunused'] = '

If set, only used categories (containing al least one question) will be tested .
';


$string['createexams'] = 'Generate and manage course exams';
$string['newattempt'] = 'Generate new preview as attempt';
$string['quizeditinghelp'] = 'Each exam is generated as a <strong>\'preview\'</strong> of the quiz. <br />
Once reviewed the <strong>\'preview\'</strong> you should <strong>\'Terminate the attempt\'</strong> and use de <strong>\'Make Exam\'</strong> button to mark questions as belonging to the exam.<br />';
$string['changedquestionshelp'] = 'This tool generate an exam using currently selected quiz questions, that may not correspond to the intended Exam period.
Please, edit manually the quiz content or use the icons below to generate previews using the questions associated to specific Exams. ';

$string['reportsettings'] = 'Exam generation settings';
$string['exam'] = 'Exam';
$string['exam_help'] = 'Exam

The Period, Scope and call that identifies a single exam

';
$string['attemptquestions'] = 'Questions from attempt';
$string['attemptquestions_help'] = '

Questions that will be used to generate this Exam.
The questions may be the currenty set questions in the quiz or those used in a previous attempt for the selected exam.

If you are generating a fresh new attempt for an Exam, you want to use current questions selected in quiz configuration.

If you are correcting failures in questions from a previus attemp, you shoud indicate that attempt here.

';
$string['currentquestions'] = 'Current questions in quiz';


$string['submittedby'] = 'Submitted by';
$string['generatinguser'] = 'Generated by';
$string['status'] = 'Sent';
$string['sent'] = 'Sent to Exam registrar';
$string['unsent'] = 'Unsent';
$string['cleared'] = 'Questions list cleared';
$string['submit'] = 'Send to Exam registrar';
$string['submitted'] = 'Submitted';
$string['reviewstatus'] = 'Review Status';
$string['generateexam'] = 'Generate Exam from preview';
$string['generate_confirm'] = 'You have asked to generate Exam {$a->exam} from this preview';
$string['errorinvalidquestions'] = 'There are {$a} questions of invalid, non-allowed, types in this exam.';
$string['generate_errors'] = 'There are problems to use this preview as an Exam:';
$string['error_invalidquestions'] = 'Invalid: {$a}.';
$string['error_numquestions'] = 'The Exam must have {$a->confignum} questions,<br />
but this quiz attempt has {$a->num} questions.';
$string['error_percategory'] = 'The Exam must have a minimum of {$a->confignum} questions per category,<br />
but this quiz attempt has only {$a->num} questions from category {$a->name}.';
$string['error_othercategories'] = 'There are {$a} questions from un-registered categories.';
$string['returnmakeexam'] = 'Return to Exams';
$string['delete_confirm'] = 'You have asked to delete attempt {$a->num}, {$a->name} <br />
corresponding to Exam {$a->exam} <br />

Do you want to continue? ';
$string['deleteattempt'] = 'Attempt delete';
$string['pdfpreview'] = 'PDF preview';
$string['feedback'] = 'Feedback: ';
$string['category'] = 'Category: ';
$string['tags'] = 'Tags: ';
$string['clear_confirm'] = 'You have asked to CLEAR questions list<br />

Do you want to continue? ';
$string['submit_confirm'] = 'You have asked to SEND attempt {$a->num}, {$a->name} <br />
corresponding to Exam {$a->exam} <br />

Do you want to continue? ';
$string['submitattempt'] = 'Submit attempt';
$string['noexamid'] = 'There is NO Exam record for stored ID: {$a}.';
$string['noexamorattempt'] = 'Cannot operate because missing specified Exam or Attempt.';
$string['noreviewmod'] = 'Exam created but no Review instance specified.';
$string['notracker'] = 'Exam created but Review instance not available.';
$string['alreadyapproved'] = 'Cannot submit because there is an Approved exam already.';
$string['alreadysent'] = 'Cannot submit because the Exam version is already sent.';
$string['gotoexamreg'] = 'Go to Exam registrar Review page';
$string['gotootherquiz'] = 'Go to other Quiz exam';
$string['setquestions'] = 'Reset quiz questions to this attempt';
$string['questiontags'] = 'Offical Board marking tags';
$string['configquestiontags'] = 'The tags that will be used to set questions in an exam as validaded or unvalidated when the exam is approved or rejected.
It must be a list of tag strings separated by commas. Just two tags separated by comma, please, leave no space between tags. Validated first.';
$string['tagvalidated'] = 'Board validaded';
$string['tagrejected'] = 'Board rejected';
$string['tagunvalidated'] = 'Not reviewed by Board';
$string['tagremove'] = 'Remove any tag validations';
$string['taginvalidationmsg'] = 'This question contains official Review Board Tags. <br />
If you edit or change anything the Review Board tags will be removed.';
$string['importoldquestions'] = 'Import old questions';
$string['clearattempts'] = 'Reset quiz';
$string['delexistingattempts'] = 'There are quiz attempts remaining. Questions cannot be removed.';
$string['continueattempt'] = 'Continue previous version';
$string['validquestions'] = 'Un-supervised Question types';
$string['configvalidquestions'] = 'These questions types will not raise a warning when used in an exam.
Any other question type used by a teacher and included into an exam will prompt a warning message to the teacher and the supervisor';
$string['validformats'] = 'Formats for import';
$string['configvalidformats'] = 'If set, only these question formats will be allowed to import questions from external files.';
$string['setpermissions'] = 'Set Make Exam extra capabilities';
$string['setcourses'] = 'Courses';
$string['setcourses_help'] = 'Courses to set extra capabilities into';
$string['setroles'] = 'Roles to change';
$string['setroles_help'] = 'Roles to modify with extra capabilities.
Roles listed are those with report Make Exam view capability';
$string['extracapabilities'] = 'Set extra capabilities in Make Exam';
$string['setcapabilities'] = 'Extra capabilities';
$string['setcapabilities_help'] = 'The selected extra capabilities will be set additionally to standard Make Exam capabilitios for indicated roles in selected courses.';
$string['assigncapabilities'] = 'Action on permissions';
$string['assigncapabilities_help'] = 'Choose if the extra capabilities is going to be either removed or added for the relevant roles.';
$string['permissionsset'] = 'Set {$a->caps} permissions on {$a->roles} roles in {$a->count} courses';
$string['unsend'] = 'Reset status to Unsent';
$string['unsend_confirm'] = 'You have asked to RESET status to UNSENT in attempt {$a->num}, {$a->name} <br />
corresponding to Exam {$a->exam} <br />

Do you want to continue? ';
$string['unsendattempt'] = 'Reset status';
$string['tex_density'] = 'TeX density';
$string['configtex_density'] = 'When converting TeX expressions to images, the point density to use. The larger, the bigger the image created.';
$string['tex_imagescale'] = 'TeX Image scale';
$string['configtex_imagescale'] = 'Scale factor (reducing) to apply to images generated from TeX expressions. The larger, the smaller the image in the PDF.';
$string['enabled'] = 'Use Make Exam';
$string['configenabled'] = 'If activated, the tools & utilities of Make Exam will be available in Quiz interface';
$string['uniquequestions'] = 'Prevent duplicate question use';
$string['configuniquequestions'] = 'If activated, then when a question has been added to a version of a exam it cannot be added to another exams of the same course. Prevent duplicate question use in several attempts';
$string['eventexamcleared'] = 'Questions list cleared in Make Exam';
$string['eventexamcreated'] = 'Exam version generated';
$string['eventexamdeleted'] = 'Exam version deleted';
$string['eventexamrecalled'] = 'Questions from Exam version recalled into quiz question list';
$string['eventexamsubmitted'] = 'Exam version submitted to Exam registrar';
