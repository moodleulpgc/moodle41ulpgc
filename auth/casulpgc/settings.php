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
 * Admin settings and defaults.
 *
 * @package auth_casulpgc
 * @copyright  2023 Enrique Castro@ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    if (!function_exists('ldap_connect')) {
        $notify = new \core\output\notification(get_string('auth_casnotinstalled', 'auth_cas'),
            \core\output\notification::NOTIFY_WARNING);
        $settings->add(new admin_setting_heading('auth_casnotinstalled', '', $OUTPUT->render($notify)));
    } else {
        // Include needed files.
        //require_once($CFG->dirroot.'/auth/cas/auth.php');

        // Introductory explanation.
        $settings->add(new admin_setting_heading('auth_casulpgc/pluginname', '',
                new lang_string('auth_casulpgcdescription', 'auth_casulpgc')));

        // CAS server configuration label.
        $settings->add(new admin_setting_heading('auth_casulpgc/casserversettings',
                new lang_string('auth_cas_server_settings', 'auth_cas'), ''));        
        
        // Check if CAS configured 
        $casconfig = get_config('auth_cas');
        $caserror = false;
        foreach(['hostname', 'baseuri', 'port'] as $key) {
            $classes = 'alert alert-success';
            if(empty($casconfig->{$key})) {
                $classes = 'alert alert-danger';
                $caserror = true;
            }
            $value = html_writer::span($casconfig->{$key}, $classes);
            $settings->add(new admin_setting_description("auth_casulpgc/$key",
                    get_string("auth_cas_{$key}_key", 'auth_cas'), $value));
        }
        
        if($caserror) {
            $caserror = html_writer::span(get_string("auth_casulpgc_caserror", 'auth_casulpgc'), 'alert alert-danger');
            $settings->add(new admin_setting_description("auth_casulpgc/caserror",
                                        get_string("auth_casulpgc_caserror_key", 'auth_casulpgc'), $caserror));
        }
        
        // casulpgc settings label.
        $settings->add(new admin_setting_heading('auth_casulpgc/casulpgcsettings',
                new lang_string('auth_casulpgc_settings', 'auth_casulpgc'), ''));        
        
        // Block other auths.
        $yesno = array(
            new lang_string('no'),
            new lang_string('yes'),
        );
        $settings->add(new admin_setting_configselect('auth_casulpgc/lockauth',
                new lang_string('auth_casulpgc_lockauth_key', 'auth_casulpgc'),
                new lang_string('auth_casulpgc_lockauth', 'auth_casulpgc'), 0 , $yesno));

        // Alt Logout URL.
        $settings->add(new admin_setting_configtext('auth_casulpgc/logout_return_url',
                get_string('auth_casulpgc_nonexistent_return_url_key', 'auth_casulpgc'),
                get_string('auth_casulpgc_nonexistent_return_url', 'auth_casulpgc'), '', PARAM_URL));

    }

}
