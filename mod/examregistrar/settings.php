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
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot."/question/engine/bank.php");
//require_once($CFG->dirroot."/mod/examregistrar/classes.php");

/*
// First get a list of examdelivery plugins with there own settings pages. If there none,
// we use a simpler overall menu structure.
$deliveries = core_component::get_plugin_list_with_file('examdelivery', 'settings.php', false);
$deliveriesbyname = array();
foreach ($deliveries as $delivery => $deliverydir) {
    $strdeliveryname = get_string('pluginname', 'examdelivery_' . $delivery);
    $deliveriesbyname[$strdeliveryname] = $delivery;
}
core_collator::ksort($deliveriesbyname);
*/
$ADMIN->add('modsettings', new admin_category('examregistrarfolder', new lang_string('pluginname', 'mod_examregistrar'), $module->is_enabled() === false));

$settings = new admin_settingpage($section, get_string('examregistrarsettings', 'examregistrar'), 'moodle/site:config', $module->is_enabled() === false);


if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtime('examregistrar/runtimestarthour', 'runtimestartminute', get_string('cronruntimestart', 'examregistrar'), get_string('configcronruntimestart', 'examregistrar'), array('h' => 3, 'm' => 30)));

                       
    $settings->add(new admin_setting_configtext('examregistrar/responsesfolder', get_string('responsesfolder', 'examregistrar'),
                       get_string('configresponsesfolder', 'examregistrar'), '', PARAM_PATH, 20));

    $settings->add(new admin_setting_configtext('examregistrar/responsessheeturl', get_string('responsessheeturl', 'examregistrar'),
                       get_string('configresponsessheeturl', 'examregistrar'), '', PARAM_INT, 10));

    $settings->add(new admin_setting_configtext('examregistrar/sessionsfolder', get_string('sessionsfolder', 'examregistrar'),
                       get_string('configsessionsfolder', 'examregistrar'), '', PARAM_PATH, 20));

    $settings->add(new admin_setting_configtext('examregistrar/distributedfolder', get_string('distributedfolder', 'examregistrar'),
                       get_string('configdistributedfolder', 'examregistrar'), 'distributed', PARAM_PATH, 20));

    $settings->add(new admin_setting_configstoredfile('examregistrar/headerlogoimage', get_string('logoimage', 'examregistrar'), 
                        get_string('configlogoimage', 'examregistrar'), 'settings', 0, 
                                    array('maxfiles' => 1, 'accepted_types' => 'web_image', 'subdirs' => 0)));

    $name = new lang_string('defaultsettings', 'block_examswarnings');
    $description = new lang_string('defaultsettings_help', 'block_examswarnings');
    $settings->add(new admin_setting_heading('defaultsettings', $name, $description));
    
    $settings->add(new admin_setting_configcheckbox('examregistrar/pdfwithteachers', get_string('pdfwithteachers', 'examregistrar'), 
                        get_string('pdfwithteachers_help', 'examregistrar'), 0));

    $settings->add(new admin_setting_configcheckbox('examregistrar/pdfaddexamcopy', get_string('pdfaddexamcopy', 'examregistrar'), 
                        get_string('pdfaddexamcopy_help', 'examregistrar'), 0));

    $settings->add(new admin_setting_configtext('examregistrar/selectdays', get_string('selectdays', 'examregistrar'),
                        get_string('selectdays_help', 'examregistrar'), 30, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/cutoffdays', get_string('cutoffdays', 'examregistrar'),
                       get_string('cutoffdays_help', 'examregistrar'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/extradays', get_string('extradays', 'examregistrar'),
                       get_string('extradays_help', 'examregistrar'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/lockdays', get_string('lockdays', 'examregistrar'),
                       get_string('lockdays_help', 'examregistrar'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/approvalcutoff', get_string('approvalcutoff', 'examregistrar'),
                       get_string('approvalcutoff_help', 'examregistrar'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/printdays', get_string('printdays', 'examregistrar'),
                       get_string('printdays_help', 'examregistrar'), 3, PARAM_INT));

    $categories =  core_course_category::make_categories_list('', 0, ' / ');
    $settings->add(new admin_setting_configmultiselect('examregistrar/staffcats', get_string('staffcategories', 'examregistrar'), 
                        get_string('staffcategories_help', 'examregistrar'), null, $categories));

    $settings->add(new admin_setting_configcheckbox('examregistrar/excludecourses', get_string('excludecourses', 'examregistrar'),
                    get_string('excludecourses_help', 'examregistrar'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/venuelocationtype', get_string('venuelocationtype', 'examregistrar'),
                       get_string('venuelocationtype_help', 'examregistrar'), '', PARAM_ALPHANUMEXT, '8'));

    $settings->add(new admin_setting_configtext('examregistrar/defaultrole', get_string('defaultrole', 'examregistrar'),
                       get_string('defaultrole_help', 'examregistrar'), '', PARAM_ALPHANUMEXT, '8'));

    $settings->add(new admin_setting_configtext('examregistrar/extanswers', get_string('extensionanswers', 'examregistrar'),
                       get_string('extensionanswers_help', 'examregistrar'), '', PARAM_FILE, 10));

    $settings->add(new admin_setting_configtext('examregistrar/extkey', get_string('extensionkey', 'examregistrar'),
                       get_string('extensionkey_help', 'examregistrar'), '', PARAM_FILE, 10));

    $settings->add(new admin_setting_configtext('examregistrar/extresponses', get_string('extensionresponses', 'examregistrar'),
                       get_string('extensionresponses_help', 'examregistrar'), '', PARAM_FILE, 10));
                       
    $settings->add(new admin_setting_configtext('examregistrar/assignexamprefix', get_string('assignexamprefix', 'examregistrar'),
                       get_string('assignexamprefix_help', 'examregistrar'), 'EXAM', PARAM_ALPHANUMEXT, '8'));

    $settings->add(new admin_setting_configtext('examregistrar/quizexamprefix', get_string('quizexamprefix', 'examregistrar'),
                       get_string('quizexamprefix_help', 'examregistrar'), 'EXAMQUIZ', PARAM_ALPHANUMEXT, '8'));
                       
    $settings->add(new admin_setting_configduration('examregistrar/quizexamafter', get_string('quizexamafter', 'examregistrar'),
                        get_string('quizexamafter_help', 'examregistrar'), 15*60));

    $settings->add(new admin_setting_configcheckbox('examregistrar/insertcontrolq', get_string('insertcontrolq', 'examregistrar'),
                       get_string('insertcontrolq_help', 'examregistrar'), 0, PARAM_INT));
                        
    $settings->add(new admin_setting_configtext('examregistrar/controlquestion', get_string('controlquestion', 'examregistrar'),
                       get_string('controlquestion_help', 'examregistrar'), '', PARAM_ALPHANUM));
                        
    $settings->add(new admin_setting_configtext('examregistrar/optionsinstance', get_string('optionsinstance', 'examregistrar'),
                       get_string('optionsinstance_help', 'examregistrar'), '', PARAM_ALPHANUM));

    $settings->add(new admin_setting_configtext('examregistrar/quizoptions', get_string('quizoptions', 'examregistrar'),
                       get_string('quizoptions_help', 'examregistrar'), '', PARAM_TEXT));
                        
}

$ADMIN->add('examregistrarfolder', $settings);
// Tell core we already added the settings structure.
$settings = null;
/*
$ADMIN->add('examregistrarfolder', new admin_category('examdeliveryplugins',
    new lang_string('examdeliveryplugins', 'examregistrar'), !$module->is_enabled()));
$ADMIN->add('examdeliveryplugins', new admin_externalpage('manageexamdeliveryplugins', get_string('manageexamdeliveryplugins', 'examregistrar'),
                                                            new moodle_url('/mod/examregistrar/manageplugins.php', array('subtype'=>'examdelivery'))));
*/
    $temp = new admin_settingpage('examdeliveryplugins', new lang_string('examdeliveryplugins', 'examregistrar'));
    $temp->add(new mod_examregistrar\setting_examdeliveries());

    $ADMIN->add('examregistrarfolder', $temp);


foreach (core_plugin_manager::instance()->get_plugins_of_type('examdelivery') as $plugin) {
    /** @var \mod_examregistrar\plugininfo\examdelivery $plugin */
    $plugin->load_settings($ADMIN, 'examdeliveryplugins', $hassiteconfig);
}
