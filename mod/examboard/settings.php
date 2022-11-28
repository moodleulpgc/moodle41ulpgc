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
 * Plugin administration pages are defined here.
 *
 * @package     mod_examboard
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings = null;

$ADMIN->add('modsettings', new admin_category('modexamboardfolder', new lang_string('pluginname', 'mod_examboard'), $module->is_enabled() === false));
$settings = new admin_settingpage('examboardgeneral', get_string('settingsgeneral', 'examboard'), 'moodle/site:config', $module->is_enabled() === false);

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtextarea('examboard/examperiods', 
                                                        get_string('examperiods', 'examboard'), 
                                                        get_string('examperiods_help', 'examboard'), 
                                                        '-:Exams', PARAM_TEXT, 20, 4));

    $modules = new admin_setting_configmultiselect_modules('examboard/gradeables', 
                                                        get_string('gradeablemods', 'examboard'), 
                                                        get_string('gradeablemods_help', 'examboard'), 
                                                        array('assign'));
    $modules->load_choices();
    
    $settings->add($modules);

    $discharges = array();
    foreach(array('holidays','illness', 'study', 'service', 'leave','maternal','congress', 'other', 'other1', 'other2', 'other3') as $motive) {                                        
        $discharges[$motive] = get_string('discharge_'.$motive, 'examboard');
    }
    $settings->add(new admin_setting_configmultiselect('examboard/discharges', 
                                                        get_string('discharges', 'examboard'), 
                                                        get_string('discharges_help', 'examboard'), 
                                                        array('holidays','illness', 'study', 'service', 'leave','maternal','congress', 'other'),
                                                        $discharges));
                                                        
    $settings->add(new admin_setting_configtext('examboard/uploadmaxfiles', 
                                                        get_string('uploadmaxfiles', 'examboard'),
                                                        get_string('uploadmaxfiles_help', 'examboard'), 
                                                        5, PARAM_INT));
                                                        
}


$ADMIN->add('modexamboardfolder', $settings);      

$settings = null;
$settings = new admin_settingpage('examboardplagiarism', get_string('settingsplagtask', 'examboard'), 'moodle/site:config', $module->is_enabled() === false);
               
if ($ADMIN->fulltree) {               
    $settings->add(new admin_setting_configcheckbox('examboard/plagtaskenabled',
                                                    get_string('plagtaskenabled', 'examboard'),
                                                    get_string('plagtaskenabled_help', 'examboard'),
                                                    0));

    $settings->add(new admin_setting_configtext('examboard/plagtasksource',
                                                    get_string('plagtasksource', 'examboard'),
                                                    get_string('plagtasksource_help', 'examboard'),
                                                    '', PARAM_ALPHANUMEXT));

    $settings->add(new admin_setting_configtext('examboard/plagtaskfield',
                                                    get_string('plagtaskfield', 'examboard'),
                                                    get_string('plagtaskfield_help', 'examboard'),
                                                    '', PARAM_ALPHANUMEXT));
                                                    
    $settings->add(new admin_setting_configtext('examboard/plagtasktarget',
                                                    get_string('plagtasktarget', 'examboard'),
                                                    get_string('plagtasktarget_help', 'examboard'),
                                                    '', PARAM_ALPHANUMEXT));

                                                    
                                                    
}
$ADMIN->add('modexamboardfolder', $settings);       

$settings = null;
