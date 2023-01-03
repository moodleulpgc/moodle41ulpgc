<?php
/**
 * Cadenas de texto de la extensión de sincronización de la ULPGC
 *
 * @package local_ulpgcgroups
 * @copyright  2016 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'ULPGC groups';
$string['ulpgcgroups:manage'] = 'Manage ULPGC groups';
// settings
$string['groupssettings'] = 'ULPGC Groups settings';
$string['enabledadvancedgroups'] = 'Enable advanced groups interface';
$string['explainenabledadvancedgroups'] = 'If active the groups wil show additional tool buttons. ';
$string['forcerestrictedgroups'] = 'Enable group restrictions';
$string['explainforcerestrictedgroups'] = 'If enabled controled groups will be marked visually and deletion restricted.';
$string['onlyactiveenrolments'] = 'Only active enrolments';
$string['explainonlyactiveenrolments'] = 'If enabled, then only users with active enrolments will be listed in group related listings.';
$string['colorrestricted'] = 'Color for restrictions';
$string['explaincolorrestricted'] = 'Group and members names will be marked in this color on listings.';
$string['managegroups'] = 'Groups management';
$string['exportgroups'] = 'Export groups';
$string['controlledgroups'] = 'Los grupos y usuarios con nombre en color son gestionados por el sistema. Esos ítems no se pueden borrar.';
$string['anygrouping'] = 'Any grouping';
$string['groupingmenu'] = 'Groups in';
$string['groupingmenu_help'] = 'If a grouping is selected then only groups belonging to that grouping are listed.';
$string['exclusivegroupingconflict'] = 'Same users in several groups: {$a} ';
$string['exclusivegroupingconflict_help'] = 'This grouping is configured to allow each user to be assigned to just one single group in the grouping. 
However, the indicated groups contain one or more users in common, users that are members of several groups in the grouping.';

$string['userorder'] = 'Naming order preference';
$string['userorder_help'] = 'Indicate how users will be sorted in lists and name formating (starting by firstname or lastname).';
$string['emptygroup'] = 'Empty this group';
$string['emptygroupconfirm'] = 'Are you sure you want to empty users in group \'{$a}\'?';
$string['removeuser'] = 'Remove this user';
$string['removenotallowed'] = 'This group is managed by an external plugin. Removing this user is not allowed';
$string['deletenotallowed'] = 'This group is managed by an external plugin. Delete is not allowed';
$string['sourcegroup'] = 'Use as parent group';
$string['sourcegroup_help'] = 'Allow to specify a grouo to serve as parent group. 
Potential members will be found only among user belonging to this group. 
In this way the groups formed will be functional subgroups of thsi parent group.';
$string['forceexclusive'] = 'Force exclusive single group in grouping';
$string['forceexclusive_help'] = 'Allow to check and mark explicitly those users that belong to several groups in the selected grouping.';
$string['controlledgroupalert'] = 'This group is managed by an external plugin. Members in color cannot be deleted or moved';
$string['nogroupusers'] = 'Users without a group assigned';
$string['singlegroupmembership'] = 'Force single group membership';
$string['singlegroupmembership_help'] = 'For many activities the users should be distributed in such a way that each user belongs to just one and only one group. 
This setting allows to set a flag to activate checking of user memebrships to detect cases where a user belongs to several grpups of tis grouping, marking them.';
$string['explainsinglegroupmembership'] = 'Users should belong to just one group in grouping. Enable active checking.';
$string['exportgroupselector'] = 'Select groups to export';
$string['exportdataselector'] = 'Select data to collect for each group member';
$string['exportuserselector'] = 'Indicate users to include un each group listing';
$string['exportgroup'] = 'Export groups';
$string['exportgroup_help'] = 'If specified, then only the indicated group will be exported. 
There are two special options:

 * All: all groups, of any grouping
 * None: only export groups that are not members of the grouping specified below. 
';
$string['exportgrouping'] = 'Groups of Grouping';
$string['exportgrouping_help'] = 'If specified, only groups that belong to te indicated grouping will be exported. 
There are two special options:

 * None: Exported only groups that happen to be NOT members of any grouping.
 * Any: No limitation by grouping.
 ';
$string['exportuserroles'] = 'Users with roles';
$string['exportuserroles_help'] = 'Only enrolled users with any of these roles will be include in exportation';
$string['exportincludeuserroles'] = 'Include user roles';
$string['exportincludeuserroles_help'] = 'If checked, a column will hold the roles each user has in the course. ';
$string['exportusersdetails'] = 'User details to export';
$string['exportusersdetails_help'] = 'Extra user data to include in exportation. 

firstname, lastname and idnumber will be allways included for all users.';
$string['exportextracolumns'] = 'Extra columns';
$string['exportextracolumns_help'] = 'A comma separated list of column headings. 

If specified, then these extra columns will be added (with empty values) to the right of the table to accomodate further data.';
$string['exportformatselector'] = 'Export format';
$string['exportdownload'] = 'Download';
$string['groupmembershipexists'] = 'Already a member';
$string['notenrolledincourse'] = 'Not added, User not enrolled in course';
$string['groupmembershipfailed'] = 'Not added, failure on adding membership';
$string['groupmembershipadded'] = 'User added as group member';
$string['usernotfoundskip'] = 'User not found, skipped';
$string['enclosure'] = 'Field enclosure';
$string['enclosure_help'] = 'Character enclosing text fields in CSV texts and files. 

In CSV files multi word fields may be enclosed by a character that marks field start and end. 
It\'s an optional feature. If present it mustbe a single character, usually ["] or [\'].';
$string['task_rolesyncgroups'] = 'Synch frontpage groups by role';
$string['task_cohortsyncgroups'] = 'Synch frontpage groups by cohort';
$string['enablefpgroupsfromcohort'] = 'Enable frontpage groups synch from cohorts';
$string['explainenablefpgroupsfromcohort'] = 'If enabled, you may choose some cohorts and their members will be added as members of a frontpage group of the same name and idnumber';
$string['fpgroupscohorts'] = 'Cohorts to synch with frontpage groups';
$string['explainfpgroupscohorts'] = 'There will be a frontpage group for each selected cohort. The members will be synced. You may add o remove users to group manually.';
$string['enrolmentkey'] = 'Frontpage groups synch by role';
$string['explainenrolmentkey'] = 'An enrolment key to identify frontpage groups to be populated from users roles. 
Leave empty to disable and not use roles as frontpage group assignment mechanism.';
$string['grouproles'] = 'Roles para el grupo {$a}';
$string['explaingrouproles'] = 'The users with the selected roles, in any context, will be synched as group members in group {$a}. 
Leave empty to disable and not use roles as frontpage group assignment mechanism.';
$string['nonexportable'] = 'No exportable groups';
$string['nolinks'] = 'No groups tools to display';
