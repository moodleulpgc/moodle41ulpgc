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
 * Strings for component 'enrol_ulpgcunits', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    enrol
 * @subpackage ulpgcunits
 * @copyright  2022 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['ulpgcunits:config'] = 'Configure meta enrol instances';
$string['ulpgcunits:unenrol'] = 'Unenrol suspended users';
$string['ulpgcunits:selectaslinked'] = 'Select as linked';
$string['privacy:metadata'] = 'The  ULPGC Units Metaenrol enrolment plugin does not store any personal data.';
$string['privacy:metadata:core_group'] = 'Enrol ULPGC Units Metaenrol plugin can create a new group or use an existing group to add all the members of the ULPGC unit.';
$string['pluginname'] = 'ULPGC Units Metaenrol';
$string['pluginname_desc'] = 'ULPGC Units Metaenrol enrolment plugin synchronises enrolments and roles in a course 
from users existing in ULPGC units table. ';
$string['assignrole'] = 'Assign role';
$string['autocategory'] = '[auto]';
$string['refreshautocategory'] = 'Refresh unit from course category';
$string['refreshautocategory_help'] = 'If enabled, the Unit used will be updated if this course is moved to a new category.

If not set, the Unit will remain fixed even when the course is moved to a different category.';

$string['syncgroup'] = 'Add to group';
$string['syncgroup_help'] = 'If indicated, in addition to enrolled, users will be added to the selected group.

If an existing course group is specified, then all users will be added to that group. 

If the option "Group by unit" is selected, a group named as the ULPGC unit idnumber will be created. 
';
$string['creategroup'] = 'Group by unit';
$string['currentunit'] = 'Current Unit';
$string['errorunitnotexists'] = 'The selected or calculated Unit doesn\'t exist in the Units table. ';
$string['errormultipleunits'] = 'Several Units selected, you must indicate just one. ';
$string['linkedunits'] = 'Unit to link';
$string['linkedunits_help'] = 'You may select one Unit of the list 
or may indicate to obtain it from course or category data: 

 * ULPGC Faculty: Field "faculty" in matching local_ulpgccore_categories
 * ULPGC degree: Field "degree" in matching local_ulpgccore_categories
 * Category idnumber: plain course category idnumber
 * Category idnumber - centro: If course category idnumber is ccc_tttt_00_00, returns ccc 
 * Category idnumber - degree: If course category idnumber is ccc_tttt_00_00, returns tttt
 * Course idnumber - centre: If course idnumber is tttt_pp_00_s_y_aaaaa_ccc, returns last ccc
 * Course idnumber - degree: If course idnumber is tttt_pp_00_s_y_aaaaa_ccc, returns first tttt

';
$string['director'] = 'Dean/Director';
$string['secretary'] = 'Secretary';
$string['coordinator'] = 'Coordinator';
$string['usertypes'] = 'Unit users to enrol';
$string['usertypes_help'] = 'Multiple choices allowed. All selected will be enrolled, if non-empty';
$string['unitnameformat'] = '{$a->type} - {$a->name} ({$a->idnumber})';
$string['unit_centro'] = 'Center'; 
$string['unit_departamento'] = 'Department';
$string['unit_instituto'] = 'Institute';
$string['unit_degree'] = 'Degree';
$string['fromfaculty'] = 'ULPGC Faculty';
$string['fromdegree'] = 'ULPGC degree';
$string['fromcategory'] = 'Category idnumber';
$string['fromcatfaculty'] = 'Category idnumber - centro';
$string['fromcatdegree'] = 'Category idnumber - degree';
$string['fromidnfaculty'] = 'Course idnumber - centre';
$string['fromidndegree'] = 'Course idnumber - degree';
$string['syncenrolmentstask'] = 'Synch enrol  for users in ULPGC units';
