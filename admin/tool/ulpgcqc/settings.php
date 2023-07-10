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
 * @package     tool_ulpgcqc
 * @category    admin
 * @copyright   2023 Enrique Castro @ ULPGC
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    
    // Site admin reports.
    $cat = new admin_category('tool_ulpgcqc_cat', new lang_string('pluginname', 'tool_ulpgcqc'));
    $ADMIN->add('reports', $cat, 'reportbuilder');    

    $ADMIN->add('tool_ulpgcqc_cat', new admin_externalpage('tool_ulpgcqc_summary',
                                           get_string('qcreport', 'tool_ulpgcqc'),
                                           $CFG->wwwroot . '/admin/tool/ulpgcqc/index.php') );

    $ADMIN->add('tool_ulpgcqc_cat', new admin_externalpage('tool_ulpgcqc_report_config',
                                           get_string('qcreport_config', 'tool_ulpgcqc'),
                                           $CFG->wwwroot . '/admin/tool/ulpgcqc/index.php?report=config') );
    
    $ADMIN->add('tool_ulpgcqc_cat', new admin_externalpage('tool_ulpgcqc_report_courses',
                                           get_string('qcreport_courses', 'tool_ulpgcqc'),
                                           $CFG->wwwroot . '/admin/tool/ulpgcqc/index.php?report=courses') );
    
    $ADMIN->add('tool_ulpgcqc_cat', new admin_externalpage('tool_ulpgcqc_report_users',
                                           get_string('qcreport_users', 'tool_ulpgcqc'),
                                           $CFG->wwwroot . '/admin/tool/ulpgcqc/index.php?report=users') );

    


    // Tools config settins in #Extensions.
    $settings = new admin_settingpage('tool_ulpgcqc_settings', new lang_string('pluginname', 'tool_ulpgcqc'));
    
    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.
    $settings = new theme_boost_admin_settingspage_tabs('tool_ulpgcqc_settings', new lang_string('pluginname', 'tool_ulpgcqc'));
    
    
    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    //if ($ADMIN->fulltree) {
            
        /*
        * ----------------------
        * General settings tab
        * ----------------------
        */
        $page = new admin_settingpage('qc_settings_general', get_string('generalsettings', 'tool_ulpgcqc'));
            
        $name = 'tool_ulpgcqc/enablecheck1';
        $title = get_string('enablecheck1', 'tool_ulpgcqc');
        $description = get_string('enablecheck1_desc', 'tool_ulpgcqc');
        $default = 1;
        $choices = array(0 => get_string('no'), 1 => get_string('yes'));
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $page->add($setting);

        // Must add the page after definiting all the settings!
        $settings->add($page);

        /*
        * ----------------------
        * Advanced settings tab
        * ----------------------
        */
        $page = new admin_settingpage('qc_settings_config', get_string('configsettings', 'tool_ulpgcqc'));
            
        // Google analytics block.
        $name = 'tool_ulpgcqc/enablecheck2';
        $title = get_string('enablecheck2', 'tool_ulpgcqc');
        $description = get_string('enablecheck2_desc', 'tool_ulpgcqc');
        $setting = new admin_setting_configtext($name, $title, $description, '');

        $page->add($setting);

        $settings->add($page);        
            
        // Add settings
        $ADMIN->add('tools', $settings);            
        
    //}
}
