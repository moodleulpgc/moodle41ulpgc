<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_o365teams
 * @category    string
 * @copyright   2022 Enrique Castro @ULPGC
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['o365teams:local/o365teams:manage'] = 'Gestionar sincronización de Teams';
$string['pluginname'] = 'Office 365 Extended Teams Integration';

$string['settings_createnoownerteams'] = 'Create no-owner Teams';
$string['settings_createnoownerteams_details'] = 'Office 365 requires at least one owner for a Teams. 
That means that o365 will not create a Teams resource for moodle courses without any suitable teacher enrolled. 
This setting allows Teams creation and user synchronization even on those cases, using a default owner below.';

$string['settings_defaultowner'] = 'Default Team owner';
$string['settings_defaultowner_details'] = 'The azureID of a default user to assign as Team owner 
and ensure team creation when NO moodle users are defined as owners in a Team.';

$string['settings_group_mail_alias_prefix'] = 'Group mail alias prefix';
$string['settings_group_mail_alias_prefix_desc'] = '';
$string['settings_group_mail_alias_course'] = 'Course part of the group mail alias';
$string['settings_group_mail_alias_course_desc'] = '';
$string['settings_group_mail_alias_suffix'] = 'Group mail alias suffix';
$string['settings_group_mail_alias_suffix_desc'] = '';
$string['settings_group_mail_alias_sync'] = 'Update usergroup name on group update';
$string['settings_group_mail_alias_sync_desc'] = 'If enabled, when Moodle group is updated, the name of the o365 usergroup and mail alias will be updated according to the latest group name settings.';

$string['settings_testdata_name_sample'] = 'Assume a course group has:
<ul>
<li>Full name: <b>{$a->fullname}</b></li>
<li>Short name: <b>{$a->shortname}</b></li>
<li>Moodle ID: <b>{$a->id}</b></li>
<li>Moodle ID number: <b>{$a->idnumber}</b></li>
<li>Moodle Group name: <b>{$a->name}</b></li>
</ul> ';

$string['settings_team_name_sample'] = 'Your current setting will create a Teams or Group with name <br />
"<b>{$a->teamname}</b>" <br />
and using mail alias <br /> 
"<b>{$a->mailalias}</b>". <br/>
Click "Save changes" button below to see how your settings will change this. <br /> <br /> <br />';
$string['settings_usergroup_name_sample'] = 'Your current setting will create a usergroup with name <br />
"<b>{$a->displayname}</b>" <br />
and using mail alias <br /> 
"<b>{$a->mailalias}</b>". <br/>
Click "Save changes" button below to see how your settings will change this. <br />  <br />';
$string['settings_header_groupnames'] = 'Groups name samples';
$string['settings_header_groupnames_desc'] = 'These following texts show how the current name settings 
in local_o365 and sitelabel combine to produce Teams and usergroups names.';
$string['settings_header_groupssync'] = 'Groups Sync options';
$string['settings_header_groupssync_desc'] = 'These following settings control group synchronization between Moodle and Microsoft Office365.';



$string['settings_header_teamssync'] = 'Teams & Channels options';
$string['settings_header_teamssync_desc'] = 'These following settings control course and group synchronization between Moodle and Microsoft Teams and channels.';
$string['settings_header_usersmatch'] = 'User Matching';
$string['settings_header_usersmatch_desc'] = 'The following settings control user matching synchronization between Microsoft 365 and Moodle.';
$string['settings_sitelabel'] = 'Site label';
$string['settings_sitelabel_details'] = 'A  label to include in all Group/Teams names. Allow to identify site/annuality when several moodle platforms connected to the same MS-Office tenant.';

$string['settings_teams_privatechannels'] = 'Create private channels';
$string['settings_teams_privatechannels_details'] = 'If enabled, a private channel wil be created for each moodle group.';
$string['settings_teams_channel_pattern'] = 'IDnumber pattern for channel';
$string['settings_teams_channel_pattern_details'] = 'Only course groups with non null IDnumber that matches the introduced pattern will have associated channels created in the course Team. 

The text is used as an SQL LIKE pattern.
';
$string['settings_team_names_update'] = 'Enhanced Teams/Groups names';
$string['settings_team_names_update_desc'] = 'If enabled, then names of all Teams/Groups will be enhanced with explicit shortname and sitelabel tags.';
$string['settings_usergroups_create'] = 'Synchronize moodle groups';
$string['settings_usergroups_create_details'] = 'If enabled a user group (mailing list) will be created in Office365 matching each moodle group.';
$string['settings_usersmatching'] = 'Users matching mode';
$string['settings_usersmatching_details'] = 'If enabled, the auth method used to find moodle users to match with o365 accounts.';
$string['settings_usersmaildomains'] = 'Users email domains';
$string['settings_usersmaildomains_details'] = 'May contain a comma-separated list of email domains. <br />
Users to match will be searched only within those having emails ending in such domains.';

$string['syncallcourses'] = 'Update users in all courses';
$string['syncallcourses_desc'] = 'If checked, then ALL courses with an o365 objects will be included in every user synchronization round, not just those changed from last run.';

$string['task_groupmembersync'] = 'Teams & channel membership synchronization with moodle courses/groups.';
$string['task_matchusers'] = 'Users matching moodle-o365 task';
$string['task_teamschannelsync'] = 'Teams & channels extended synchronization o365';
$string['task_teamnamesync'] = 'Extended Teams names sync';
