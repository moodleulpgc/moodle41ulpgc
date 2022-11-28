<?php
/**
 * Cadenas de texto de la extensión de sincronización de la ULPGC
 *
 * @package    local
 * @subpackage sinculpgc
 * @copyright  2014 Víctor Déniz, SI@ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
$string['pluginname'] = 'ULPGC units synch'; 
$string['sinculpgc:manage'] = 'Manage ULPGC units '; 
$string['eventruleupdated'] = 'sinculpgc enrol rule updated';
$string['eventunitupdated'] = 'ULPGC unit updated'; 
$string['eventruledeleted'] = 'sinculpgc enrol rule deleted';
$string['eventunitdeleted'] = 'ULPGC unit deleted'; 
$string['managerules'] = 'Manage enrol rules';
$string['sinculpgcsettings'] = 'Config ULPGCunits synch';

$string['columnsmissing'] = 'Missing required columns in file';
$string['columnsinvalid'] = 'Invalid column names in file';

$string['enablesynchrules'] = 'Enable enrol rules synch';
$string['enablesynchrules'] = 'Enable enrol rules synch';
$string['enablesynchrules_help'] = 'When checked, the rules defined will be applied 
to create automatically enrol instances in courses with matching parameters.';
$string['enabled'] = 'Rule enabled';
$string['enrolas'] = 'Enrol role';
$string['enrolmethod'] = 'Enrol method';
$string['enrolparams'] = 'Enrol params';
$string['error:noenrol'] = 'An enrol method must be selected to create a new rule';
$string['director'] = 'Dean/Director';
$string['secretary'] = 'Secretary';
$string['coordinator'] = 'Coordinator';
$string['existinggroup'] = 'Course group (below)';
$string['forcegroup'] = 'Force group';
$string['forcegroup_help'] = 'When marked, if the specified group 
(by either group name or group idnumber) doesn\'t exists in the course, 
it will be created by enrol instance and enrolled users added as members.';

$string['forcereset'] = 'Force instance reset';
$string['forcereset_help'] = 'Usually, synchronization will skip courses/instances associated to a rule and modifiede afterwards. 
When marked, existing enrol instances will be reset, returning their parameters to those indicated in teh rule definition.';
$string['importfailures'] = 'These lines in the uploaded file could not be imported: <br>
{$a} ';
$string['importrules'] = 'Import rules';
$string['importedcount'] = '{$a} new rules imported from file. ';
$string['lazydelete'] = 'Lazy instance removal';
$string['lazydelete_help'] = 'Usually when a rule is deleted, 
the removal of the associated enrol instances will take place with next cron task, so removal is delayed . 
When marked, all enrol instances associated with a rule will be removed inmediatly, 
without waiting for net run of the synch task.'; 
$string['numused'] = 'N Used';
$string['numinstances'] = 'Total: {$a}';
$string['numdisabled'] = 'Disabled: {$a}';
$string['referencecourse'] = 'Reference course';
$string['referencecourse_help'] = 'The Shortname of a course to use as reference one to get context, 
standard groups and roles etc.

Just to to make the form work smoothly.';
$string['referencecoursenotexists'] = 'Reference course not exists. Using site course instead.';
$string['removeondisabling'] = 'Remove on disabling'; 
$string['removeondisabling_help'] = 'Normally, when a rule is disabled those enrol instances 
in courses associated to the rule are maintained, keeping user enrolments.
If marked, when a rule is disabled, automatically the existing enrol instances in courses are removed.';
$string['rule'] = 'Enrol rule';
$string['rulesettings'] = 'Rule params';
$string['ruledeleted'] = 'Rule with ID {$a} deleted  successfully.';
$string['rulegroup'] = 'Course Group';
$string['rulegroup_help'] = 'The name of a Group to add users enroled by this method/rule in the target course. 
If the group name is existing in the course target, then that group wil be used, not creating a new one. 

If the checkbox below is marked, then the checking form existing course group will be performed 
looking for grouo idnumber rather tha group name. ';
$string['rulenum'] = 'Rule';
$string['ruleadd'] = 'Add rule';
$string['importfile'] = 'Import file';
$string['importfile_help'] = 'A CSV text file with columns for: 

    * enrol
    * roleid: 
    * searchfield
    * searchpattern
    * ruleparams
    * groupto
    * useidnumber
 
The names of the columns are fixed, do not translate them.
Rules in the file will be added after existing ones, never updating current rules. 
All imported rules will be set as DISABLED on import, you may enable then afterwards. ';
   
$string['instancesadded'] = 'Added/Updated {$a} course enrol instances.' ;
$string['instancesremoved'] = 'Removed {$a} course enrol instances.' ;    
$string['ruleupdate'] = 'Update rule';
$string['rule:create'] = 'New rule';
$string['rule:delete'] = 'Delete rule';
$string['rule:edit'] = 'Edit rule';
$string['rule:disable'] = 'Disable rule';
$string['rule:enable'] = 'Enable rule';
$string['rule:import'] = 'Import rules';
$string['rule:remove'] = 'Remove enrol instances';
$string['rule:reset'] = 'Reset modified instances to rule';
$string['rule:run'] = 'Run task for this rule';
$string['confirm:delete'] = 'Asked to delete the rule {$a->id}. 
This will remove all enrol instances associated  in matching courses 
and unenrol users from potentially many courses. 

Do you want to proceed ?';
$string['confirm:run'] = 'Asked to launch enrol instances synchonization by rule {$a->id}. 
This will add potentially a large number or course enrol instances affecting many users.  
This action may take a long time. 

Do you want to proceed ?';
$string['confirm:reset'] = 'Asked to reset enrol instances associated to rule {$a->id}. 
This will reset all associated "{$a->enrol}" instances to rule default, 
dismissing any manual modifications introduced by teachers.  
This action may take a long time. 

Do you want to proceed ?';


$string['confirm:remove'] = 'Asked to remove "{$a->enrol}" course enrol instances associated to this rule {$a->id}. 
This will unenrol users from potentially many courses. 

Do you want to proceed ?';
$string['confirm:statuson'] = 'Asked to ENABLE {$a->numdisabled} "{$a->enrol}" course enrol instances actually disabled. 

Do you want to proceed ?';
$string['confirm:statusoff'] = 'Asked to DISABLE {$a->numenabled} "{$a->enrol}" course enrol instances actually enabled. 

Do you want to proceed ?';

$string['searchfield'] = 'Search field';
$string['searchfield_help'] = 'The field where to search for the below pattern, either

 * Course fullname
 * Course shortname
 * Course idnumber
 * Course category (id)
 * Course category name
 * Course category idnumber 
';
$string['searchpattern'] = 'Search pattern';
$string['searchpattern_help'] = 'A text to be used as an SQL search pattern to find courses. <br /> 
Text will be used in SQL LIKE statements, wildcards "_" or "%" accepted. No need to type quotes (added afterwards). <br />
Each line will be an additional pattern search (with AND).
Several OR statements may be introducen in each line separated with "|" character.
';
$string['statusupdated'] = 'Updated instance status in {$a} course enrol instances.';
$string['status:on'] = 'Enable enrol instances';
$string['status:off'] = 'Disable enrol instances';
$string['syncedrole'] = 'Synced';
$string['useidnumber'] = 'Group by idnumber';
$string['useidnumber_help'] = 'Mark to specify group by matching text above with group idnumber in course.';

$string['task_rulesenrolsync'] = 'ULPGC rules enrol synch';

//======================================================
$string['usertypes'] = 'Unit users to enrol';
$string['usertypes_help'] = ' All selected will be enrolled, if non-empty';
$string['enrolas_help'] = 'The specified users will we enrolled into the courses with this role.';
