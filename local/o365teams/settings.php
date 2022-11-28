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
 * Plugin administration pages are defined here.
 *
 * @package     local_o365teams
 * @category    admin
 * @copyright   2022 Enrique Castro @ULPGC
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // New settings page.
    $settings = new admin_settingpage('local_o365teams',
            get_string('pluginname', 'local_o365teams', null, true));
            
    if ($ADMIN->fulltree) {

        $label = new lang_string('settings_header_usersmatch', 'local_o365teams');
        $desc = new lang_string('settings_header_usersmatch_desc', 'local_o365teams');
        $settings->add(new admin_setting_heading('local_o365_section_usersmatch', $label, $desc));
    
        $menu = array('' => get_string('none'),
                        'any' => get_string('any'),);
        foreach(get_plugin_list('auth') as $key => $val) {
            $menu[$key] = $key;
        }
                                
        $label = new lang_string('settings_usersmatching', 'local_o365teams');
        $desc = new lang_string('settings_usersmatching_details', 'local_o365teams');
        $settings->add(new admin_setting_configselect('local_o365teams/usersmatching', $label, $desc, 'auto', $menu));          
        
        $label = new lang_string('settings_usersmaildomains', 'local_o365teams');
        $desc = new lang_string('settings_usersmaildomains_details', 'local_o365teams');
        $settings->add(new \admin_setting_configtext('local_o365teams/usersmaildomains', $label, $desc, '', PARAM_NOTAGS, 24));        
        
        // Course sync section.
        $label = new lang_string('settings_header_teamssync', 'local_o365teams');
        $desc = new lang_string('settings_header_teamssync_desc', 'local_o365teams');
        $settings->add(new admin_setting_heading('local_o365_section_teamssync', $label, $desc));

        $label = new lang_string('settings_createnoownerteams', 'local_o365teams');
        $desc = new lang_string('settings_createnoownerteams_details', 'local_o365teams');
        $settings->add(new \admin_setting_configcheckbox('local_o365teams/createnoownerteams', $label, $desc, 1));        
        
        $label = new lang_string('settings_defaultowner', 'local_o365teams');
        $desc = new lang_string('settings_defaultowner_details', 'local_o365teams');
        $settings->add(new \admin_setting_configtext('local_o365teams/defaultowner', $label, $desc, '', PARAM_TEXT, 24));

        $label = new lang_string('settings_teams_privatechannels', 'local_o365teams');
        $desc = new lang_string('settings_teams_privatechannels_details', 'local_o365teams');
        $settings->add(new \admin_setting_configcheckbox('local_o365teams/teamsprivatechannels', $label, $desc, 0));    
        
        $label = new lang_string('settings_teams_channel_pattern', 'local_o365teams');
        $desc = new lang_string('settings_teams_channel_pattern_details', 'local_o365teams');
        $settings->add(new \admin_setting_configtext('local_o365teams/channelpattern', $label, $desc, '', PARAM_TEXT, 8));

        // Synchronization options group name.
        $label = new lang_string('settings_header_groupssync', 'local_o365teams');
        $desc = new lang_string('settings_header_groupssync_desc', 'local_o365teams');
        $settings->add(new admin_setting_heading('local_o365_section_groupssync', $label, $desc));

        $label = new lang_string('syncallcourses', 'local_o365teams');
        $desc = new lang_string('syncallcourses_desc', 'local_o365teams');
        $settings->add(new admin_setting_configcheckbox('local_o365teams/syncall', $label, $desc, 0));
        
        $label = new lang_string('settings_usergroups_create', 'local_o365teams');
        $desc = new lang_string('settings_usergroups_create_details', 'local_o365teams');
        $settings->add(new \admin_setting_configcheckbox('local_o365teams/createusergroups', $label, $desc, 0));

        // Sync group name.
        $label = new lang_string('settings_group_mail_alias_sync', 'local_o365teams');
        $desc = new lang_string('settings_group_mail_alias_sync_desc', 'local_o365teams');
        $settings->add(new admin_setting_configcheckbox('local_o365teams/group_mail_alias_sync', $label, $desc, 0));
        
        // Group names samples.
        $label = new lang_string('settings_header_groupnames', 'local_o365teams');
        $desc = new lang_string('settings_header_groupnames_desc', 'local_o365teams');
        $settings->add(new admin_setting_heading('local_o365_section_groupnames', $label, $desc));
      
        
        // Sample group names.
        // Change teams names.
        $label = new lang_string('settings_team_names_update', 'local_o365teams');
        $desc = new lang_string('settings_team_names_update_desc', 'local_o365teams');
        $settings->add(new admin_setting_configcheckbox('local_o365teams/advancedteamnames', $label, $desc, 0));
        
        $label = new lang_string('settings_sitelabel', 'local_o365teams');
        $desc = new lang_string('settings_sitelabel_details', 'local_o365teams');
        $settings->add(new \admin_setting_configtext('local_o365teams/sitelabel', $label, $desc, '', PARAM_TEXT, 8));

        // Sample Team / group name.testdata
        $testdata = \local_o365teams\coursegroups\utils::get_team_group_name_sample_course();
        $settings->add(new admin_setting_heading('local_o365teams_section_coursegroup_sample', '',
            get_string('settings_testdata_name_sample', 'local_o365teams', $testdata)));
        
        [$sampleteamname, $samplegroupalias] = \local_o365teams\coursegroups\utils::get_sample_team_group_names();
        $settings->add(new admin_setting_description('local_o365_section_team_name_sample', '',
            get_string('settings_team_name_sample', 'local_o365teams',
                ['teamname' => $sampleteamname, 'mailalias' => $samplegroupalias])));

        [$samplegroupname, $samplegroupalias] = \local_o365teams\coursegroups\utils::get_sample_usergroup_names();
        $settings->add(new admin_setting_description('local_o365teams_section_group_mail_alias_sample', '',
            get_string('settings_usergroup_name_sample', 'local_o365teams', 
                ['displayname' => $samplegroupname, 'mailalias' => $samplegroupalias])));

    }
    
    $ADMIN->add('localplugins', $settings);  
}
