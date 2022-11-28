<?php

/**
 * ULPGC specific customizations admin tree pages & settings
 *
 * @package    local
 * @subpackage sinculpgc
 * @copyright  2022 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $temp = new admin_category('local_sinculpgc_adminsettings', 
                        get_string('pluginname','local_sinculpgc')); 
    
    $ADMIN->add('localplugins', $temp);    

    $temp = new admin_settingpage('local_sinculpgc_settings', 
                        get_string('sinculpgcsettings','local_sinculpgc')); 

    $temp->add(new admin_setting_configcheckbox('local_sinculpgc/enablesynchrules', 
                        get_string('enablesynchrules','local_sinculpgc'), 
                        get_string('enablesynchrules_help','local_sinculpgc'), 0));    

    $temp->add(new admin_setting_configcheckbox('local_sinculpgc/removeondisabling', 
                        get_string('removeondisabling','local_sinculpgc'), 
                        get_string('removeondisabling_help','local_sinculpgc'), 0));    

    $temp->add(new admin_setting_configcheckbox('local_sinculpgc/forcegroup', 
                        get_string('forcegroup','local_sinculpgc'), 
                        get_string('forcegroup_help','local_sinculpgc'), 0));    
                        
    $temp->add(new admin_setting_configcheckbox('local_sinculpgc/forcereset', 
                        get_string('forcereset','local_sinculpgc'), 
                        get_string('forcereset_help','local_sinculpgc'), 0));    
                        
    $temp->add(new admin_setting_configcheckbox('local_sinculpgc/lazydelete', 
                        get_string('lazydelete','local_sinculpgc'), 
                        get_string('lazydelete','local_sinculpgc'), 1));    
                        
                        
    $temp->add(new admin_setting_configtext('local_sinculpgc/referencecourse', 
                        get_string('referencecourse', 'local_sinculpgc'), 
                        get_string('referencecourse_help', 'local_sinculpgc'), 'PTF-01'));
 
    $ADMIN->add('local_sinculpgc_adminsettings', $temp); 
    
    $managerules = new admin_externalpage('local_sinculpgc_managerules',
        get_string('managerules', 'local_sinculpgc', null, true),
        new moodle_url('/local/sinculpgc/managerules.php'), 'local/sinculpgc:manage');
    
    $ADMIN->add('local_sinculpgc_adminsettings', $managerules); 

}
