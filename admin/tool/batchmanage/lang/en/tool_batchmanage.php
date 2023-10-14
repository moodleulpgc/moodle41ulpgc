<?php
/**
 * Strings for component 'tool_batchmanage', language 'en', branch 'MOODLE_22_STABLE'
 *
 * @package    tool
 * @subpackage batchmanage
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Batch management tools';
$string['batchmanage'] = 'Batch management tools';
$string['managejobsettings'] = 'Batch management tool settings ';
$string['managejobs'] = 'Manage batch jobs';

$string['batchmanage:apply'] = 'Apply batch management job';
$string['batchmanage:manage'] = 'Manage batch management jobs';



$string['errorpluginnotfound'] = 'Manage job plugin named "{$a}" not found';

$string['coursesettings'] = 'Course selection criteria';
$string['coursecategories'] = 'Into courses of categories: ';
$string['coursecategories_help'] = '
Course categories defined in the system. You may select several or ALL categories by marking them.';
$string['coursevisible'] = 'Course visibility: ';
$string['hidden'] = 'Hidden';
$string['department'] = 'Department';
$string['term'] = 'Semester';
$string['term00'] = 'Anual';
$string['term01'] = 'First Semester';
$string['term02'] = 'Second Semester';
$string['ctype'] = 'Course type';
$string['ctype_'] = 'Vacío';
$string['ctype_B'] = 'B Básica';
$string['ctype_D'] = 'D Obligatoria';
$string['ctype_L'] = 'L Libre Conf.';
$string['ctype_O'] = 'O Optativa';
$string['ctype_R'] = 'R Básica de Rama';
$string['ctype_T'] = 'T Troncal';
$string['cstatus'] = 'Course status';
$string['cstatus_'] = 'Vacío';
$string['cstatus_001'] = '001 Se imparte';
$string['cstatus_00B'] = '00B No se imparte';
$string['cstatus_00C'] = '00C Se impartirá';
$string['cstatus_00D'] = '00D En extinción';
$string['cstatus_00E'] = '00E Solo repetidores';
$string['coursetoshortnames'] = 'Specific shortnames';
$string['coursetoshortnames_help'] = '
Comma separated list of course shortnames, without spaces.';
$string['courseidnumber'] = 'Course IDnumber pattern';
$string['courseidnumber_help'] = '
You can specify a pattern to match course IDnumber field.

The search will use SQL LIKE features, you must include wilcards in the pattern if you want to use them.';
$string['coursefullname'] = 'Course fullname pattern';
$string['coursefullname_help'] = '
You can specify a pattern to match course fullname field.

The search will use SQL LIKE features, you must include wilcards in the pattern if you want to use them.';
$string['nonzero'] = 'non zero';
$string['credit'] = 'Courses with credits';
$string['reviewconfirm'] = 'Review & confirm';
$string['excludeshortnames'] = 'Exclude shortnames';
$string['excludeshortnames_help'] = 'The courses with shortname in this list wil be skipped.

Comma separated list of course shortnames, without spaces.';

$string['courses_selector'] = 'Select courses to operate';
$string['scheduledtask'] = 'Set the job to execute at this time';
$string['scheduledat'] = 'Job scheduled to execute on {$a}';
$string['eventjobdone'] = 'Management job executed';
$string['modify'] = 'Modificar ajuste';
$string['actbatchmanageshdr'] = 'Available management jobs';
$string['configbatchmanageplugins'] = 'Enable/Disable and organize the plugins as desired';

$string['adminincluded'] = 'Include';
$string['adminexcluded'] = 'Exclude';
$string['adminonly'] = 'Admin only';

$string['applymodconfig'] = 'Apply module config';
$string['applymodconfig_help'] = '
Allows to specify module configuration settings in a form and then apply those setting values to modules containes in courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['modselectorsettings'] = 'Module selection details';

$string['modconfigsettings'] = 'Settings to apply';
$string['modname'] = 'Module name: ';
$string['instancename'] = 'Instance name: ';
$string['instancename_help'] = 'Name of the module instance you want to modify <br />(verbatim, including HTML tags).<br />
May use SQL LIKE wildcards if next option checked. You need to explicty include "%" or "_" wildcards. ';
$string['uselike'] = 'Use SQL LIKE for name search';
$string['uselike_help'] = '
If enabled, then the above term will allow SQL search wildcards like "%" and "_".';

$string['instanceid'] = 'Module instance IDs ';
$string['instanceid_help'] = '
Comma separated list of module instance ID values as existing in prefix_module DB table';
$string['modinstanceid'] = 'Module instance IDs ';
$string['modinstanceid_help'] = '
Comma separated list of module instance ID values as existing in prefix_module DB table';
$string['modcoursemoduleid'] = 'Course module IDs ';
$string['modcoursemoduleid_help'] = '
Comma separated list of course module ID values as existing in prefix_course_modules DB table and shown in url addresses (...view.php?id=xxx).';
$string['modvisible'] = 'Instance visibility';
$string['modidnumber'] = 'Instance grade ID number';
$string['insection'] = 'Course section containing the instance';
$string['modindent'] = 'Instance indentation';
$string['adminrestricted'] = 'Select admin-restricted instances';
$string['adminrestricted_help'] = 'How admin restriction options are used.

* Include: those modules are included in the processing, as well as non-restricted modules.
* Exclude: those modules are excluded, only non-restricted modules are considered.
* Admin only: Only admin restricted (any restriction) modules are considered, non-restricted modules are excluded.
';

$string['sectionsettings'] = 'Section selection options';
$string['sectionname'] = 'Section name';
$string['sectionname_help'] = 'Name of the course section you want to modify <br />
(verbatim, including HTML tags).<br />
May use SQL LIKE wildcards if next option checked. You need to explicty include "%" or "_" wildcards. <br />
If you target sections which name is empty, please specify the word "null".
';
$string['sectioninstanceid'] = 'Course Section IDs ';
$string['sectioninstanceid_help'] = '
Comma separated list of course sections ID values as existing in prefix_cousre_sections DB table';
$string['sectioninsection'] = 'Section number within course';
$string['selectsectionconfig'] = 'Define section config';
$string['setasmarker'] = 'Set as current section';
$string['emptyform'] = 'Form empty. Need some specified data to operate';
$string['notset'] = 'Not set';
$string['referencecourse'] = 'Reference course idnumber';
$string['configreferencecourse'] = 'IDnumber of an existing course that may be used as reference or template.';
$string['notallowedwords'] = 'You have included a NON allowed word in an SQL query';
$string['nosemicolon'] = 'You have included a semicolon ";" in an SQL query';
$string['nomodule'] = 'You must select a module name';
$string['norefcourse'] = 'The reference course is not found. Please configure a reference course for batch jobs.';
$string['configrefcourse'] = 'Config ref course';
