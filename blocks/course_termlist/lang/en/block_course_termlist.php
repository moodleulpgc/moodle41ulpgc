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
 * Block "course course tem list" - Language pack
 *
 * @package    block_course_termlist
 * @copyright  Enrique castro <@ULPGC>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['all'] = 'All';
$string['blocktitle'] = 'Block title';
$string['blocktitle_desc'] = 'This display name is shown as the title of the block';
$string['blocktitledefault'] = 'Course overview';

$string['category'] = 'Parent category';
$string['categorycoursefilter'] = 'Activate parent category filter';
$string['categorycoursefilter_desc'] = 'Allow users to filter courses by parent category';
$string['categorycoursefilterdisplayname'] = 'Display name for parent category filter';
$string['categorycoursefilterdisplayname_desc'] = 'This display name is shown above the parent category filter<br /><em>This setting is only processed when the parent category filter is activated</em>';
$string['categorycoursefiltersettingheading'] = 'Parent category filter: Filter activation';

$string['enablehidecourses'] = 'Enable course hiding';
$string['enablehidecourses_desc'] = 'Enable course hiding, which lets users hide courses from the course overview list';

$string['hidecourse'] = 'Hide course in course overview list';
$string['hiddencourses'] = 'You have {$a} hidden courses';
$string['hideonphones'] = 'Hide on phones';
$string['hideonphones_desc'] = 'Hide the second row on mobile phones to save space';

$string['managehiddencourses'] = 'Manage hidden courses';

$string['pluginname'] = 'Course list by term';
$string['excluded'] = 'Excluded courses';
$string['configexcluded'] = 'A comma separeted list of courses, by shortname, that will not be listed in this block.';
$string['showcategorieslink'] = 'Link to category courses';
$string['configshowcategorieslink'] = 'If set, the categories titles will become links to access category course listing,
provided the user has role assignment at category level.';
$string['showdepartmentslink'] = 'Link to department courses';
$string['configshowdepartmentslink'] = 'If set, a new line appears with a link to a listing of all courses by department,
provided the user has role assignment at department level.';
$string['hideallcourseslink'] = 'Hide link to all courses';
$string['confighideallcourseslink'] = 'If set, the link to the listing of all courses in the site will be hided';



$string['settingspage_general'] = 'General';
$string['settingspage_viewlist'] = 'Course list format';
$string['settingspage_categoryfilter'] = 'Category filter';
$string['settingspage_termfilter'] = 'Term filter';

$string['showcourse'] = 'Show course';
$string['showrecentactivity'] = 'Show recent activity';
$string['showrecentactivity_desc'] = 'In enabled, then custom course recent activity is calculated and shown as a mark with course name.';
$string['showshortname'] = 'Show short name';
$string['showshortname_desc'] = 'Show the course\'s short name with the fullname';
$string['showteachername'] = 'Show teachers names';
$string['showteachername_desc'] = 'Show the teacher\'s name(s) in a second row of the course overview list entry. 
If there is more then one teacher, the names will be sorted first by the <a href="/admin/roles/manage.php">global order of roles</a> 
and second by the teachers\' last names';
$string['showteachernamestyle'] = 'Style of teacher name';
$string['showteachernamestyle_desc'] = 'Define how the teacher\'s name should be displayed in the second row of the course overview list entry<br />
<em>This setting is only processed when show teacher name is activated</em>';
$string['stopmanaginghiddencourses'] = 'Stop managing hidden courses';
$string['submitfilter'] = 'Filter my courses!';
$string['teachersheading'] = 'Teacher settings';
$string['teachernamestylefullname'] = 'Firstname Lastname';
$string['teachernamestylefirstname'] = 'Firstname';
$string['teachernamestylelastname'] = 'Lastname';
$string['teachernamestylefullnamedisplay'] = 'Use core setting "fullnamedisplay" (Currently "{$a}")';
$string['teacherrolessettingheading'] = 'Teacher roles';
$string['teacherroles'] = 'Teacher roles';
$string['teacherroles_desc'] = 'Define which roles are handled as teacher roles by this plugin <br />
<em>This setting is only processed when show teacher name is activated or when the teacher filter 
is activated or when the priorization of courses in which I teach is activated</em>';

$string['term00'] = 'Anual';
$string['term01'] = 'First semester';
$string['term02'] = 'Second semester';
$string['term03'] = 'Third semester';
$string['term04'] = 'Fourth semester';
$string['newactivity'] = 'New activity detected in the course';
$string['useallowedcats'] = 'Only allowed categories';
$string['configuseallowedcats'] = 'When enabled, only courses in the ULPGC allowed caregories are listed';
$string['onlyactiveenrol'] = 'Only active enrol';
$string['configonlyactiveenrol'] = 'When enabled, only active enrolments are considerdd to construct the course list';
$string['nocourses'] = 'You are not enroled in any course';
$string['course_termlist:myaddinstance'] = 'Add a new course term list block to My home';
$string['course_termlist:addinstance'] = 'Add a new course term list block';
$string['teacheretal'] = ' ... ($a more)';
$string['term'] = 'Term';
$string['term1'] = 'Term 1';
$string['term1name'] = 'Term 1 name';
$string['term1name_desc'] = 'Descriptive name for term 1, please rename it according to your campus terminology 
(or leave it empty if you want to use only year numbes in Academic year mode) 
<br /><em>This setting is only processed when the term filter is activated</em>';
$string['term2'] = 'Term 2';
$string['term2name'] = 'Term 2 name';
$string['term2name_desc'] = 'Descriptive name for term 2, please rename it according to your campus terminology 
<br /><em>This setting is only processed when the term filter is activated and when term mode is set to "Semester", "Tertial" or "Trimester"</em>';
$string['term3'] = 'Term 3';
$string['term3name'] = 'Term 3 name';
$string['term3name_desc'] = 'Descriptive name for term 3, please rename it according to your campus terminology 
<br /><em>This setting is only processed when the term filter is activated and when term mode is set to "Tertial" or "Trimester"</em>';
$string['term4'] = 'Term 4';
$string['term4name'] = 'Term 4 name';
$string['term4name_desc'] = 'Descriptive name for term 4, please rename it according to your campus terminology 
<br /><em>This setting is only processed when the term filter is activated and when term mode is set to "Trimester"</em>';
$string['termcoursefilter'] = 'Activate term filter';
$string['termcoursefilter_desc'] = 'Allow users to filter courses by term';
$string['termcoursefilterdisplayname'] = 'Display name for term filter';
$string['termcoursefilterdisplayname_desc'] = 'This display name is shown above the term filter 
<br /><em>This setting is only processed when the term filter is activated</em>';
$string['termcoursefiltersettingheading'] = 'Term filter: Filter activation';
$string['termnamesettingheading'] = 'Term filter: Term names';
$string['timelesssettingheading'] = 'Timeless terms / courses';
$string['timelessenabled'] = 'Timeless courses';
$string['timelessenabled_desc'] = 'Enable support for timeless courses in the term filter. 
Timeless courses seem to be not associated to a specific term 
<br /><em>This setting is only processed when the term filter is activated</em>';
$string['timelessname'] = 'Display name for timeless courses';
$string['timelessname_desc'] = 'This display name is shown in the term filter for courses which are timeless 
<br /><em>This setting is only processed when the term filter is activated and when timeless courses are activated</em>';
$string['timelessterms'] = 'Timeless terms';
$string['timelessterms_desc'] = 'The term values that are considered timeless. 
<br /><em>This setting is only processed when the term filter is activated and when timeless courses are activated</em>';
$string['timelesssettingheading'] = 'Term filter: Timeless courses';
$string['toplevelcategory'] = 'Top level category';
$string['toplevelcategorycoursefilter'] = 'Activate top level category filter';
$string['toplevelcategorycoursefilter_desc'] = 'Allow users to filter courses by top level category';
$string['toplevelcategorycoursefilterdisplayname'] = 'Display name for top level category filter';
$string['toplevelcategorycoursefilterdisplayname_desc'] = 'This display name is shown above the top level category filter 
<br /><em>This setting is only processed when the top level category filter is activated</em>';
$string['toplevelcategorycoursefiltersettingheading'] = 'Top level category filter: Filter activation';
$string['youhave'] = 'You have';
