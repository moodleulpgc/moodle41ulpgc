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
 * Authentication Plugin: CAS Authentication
 *
 * Authentication using CAS (Central Authentication Server).
 *
 * @author Victor Deniz / Enrique castro
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth_casulpgc
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/auth/cas/auth.php');

/*
 * Proceso los comandos single sign-out enviados por CAS.
 * Busca en los archivos de sesión en disco el que coincide con el ticket
 * recibido desde CAS.
 */
function casSingleSignOut($ticket2logout) {
    global $CFG;

    // File session
    $dir = $CFG->dataroot . '/sessions';
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            // Read all session files
            while (($file = readdir($dh)) !== false) {
                // Check if it is a file
                if (is_file($dir . '/' . $file)) {
                    $content = file($dir . '/' . $file);
                    if (preg_match('/'.$ticket2logout.'/', $content[0])) {
                        unlink($dir . '/' . $file);
                    }
                }
            }
        }
        closedir($dh);
    }
}

/**
 * CAS authentication plugin.
 */
class auth_plugin_casulpgc extends auth_plugin_cas {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'casulpgc';
        $this->roleauth = 'auth_casulpgc';
        $this->errorlogtag = '[AUTH CASULPGC] ';
        $this->init_plugin('cas');
        $localconfig = get_config('auth_casulpgc');
        if(!empty($localconfig)) {
            foreach($localconfig as $key => $value) {
                $this->config->{$key} = $value;
            }
        }
        $this->pluginconfig = 'auth/' . $this->authtype;
    }

    /**
     * Old syntax of class constructor.
     * Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_casulpgc() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Authentication choice (CAS or other)
     * Redirection to the CAS form or to login/index.php
     * for other authentication
     */
    function loginpage_hook() {
        global $frm;
        global $user;
        global $CFG;

        // Return if CAS enabled and settings not specified yet
        if (empty($this->config->hostname)) {
            return;
        }

        // If the multi-authentication setting is used, check for the param before connecting to CAS.
        if ($this->config->multiauth) {
            // If there is an authentication error, stay on the default authentication page.
            if (!empty($SESSION->loginerrormsg)) {
                return;
            }
            $authCAS = optional_param('authCAS', '', PARAM_RAW);
            if ($authCAS != 'CASULPGC') {
                return;
            }
        }

        parent::loginpage_hook();

        $casinit = phpCAS::isInitialized();

        if($this->config->lockauth) {
            if($casinit) {                
                if (phpCAS::checkAuthentication()) {
                    if(!isset($frm->username)) {
                        $frm = new stdClass();
                        $frm->username = phpCAS::getUser();
                    }
                    $user = get_complete_user_data('username', $frm->username, $CFG->mnet_localhost_id);
                    if ((!$user) || (($user->auth != 'casulpgc') && ($user->auth != 'manual'))) {

                        $this->nonexisting($frm->username);
                        die();
                    }
                    return;
                }
            }
        }
    }


    /**
     * Connect to the CAS (clientcas connection or proxycas connection)
     */
    function nonexisting($username) {
        global $CFG, $PAGE, $OUTPUT;

        $context = context_system::instance();
        $PAGE->set_url("$CFG->wwwroot/login/index.php");
        $PAGE->set_context($context);
        $PAGE->set_pagelayout('login');

        /// Define variables used in page
        $site = get_site();

        // Ignore any active pages in the navigation/settings.
        // We do this because there won't be an active page there, and by ignoring the active pages the
        // navigation and settings won't be initialised unless something else needs them.
        $PAGE->navbar->ignore_active();
        $loginsite = get_string("loginsite");
        $PAGE->navbar->add($loginsite);

        $PAGE->set_title("$site->fullname: $loginsite");
        $PAGE->set_heading("$site->fullname");

        echo $OUTPUT->header();

        echo $OUTPUT->box_start('loginform casulpgc nonexisting');
        
        echo $OUTPUT->heading("$site->fullname");
        $errormsg = get_string('unauthorisedlogin', '', $username);
        echo $OUTPUT->box($errormsg, 'generalbox  alert alert-danger', 'intro');

        $errormsg = get_string('nonexistentmsg', 'auth_casulpgc');
        echo $OUTPUT->box($errormsg, 'generalbox', 'intro');

        $strreturn = get_string('noaccessreturn', 'auth_casulpgc');
        $url = 'https://www.ulpgc.es/';
        if(!empty($this->config->logout_return_url)) {
            $url = new moodle_url('https://'.$this->config->logout_return_url);
        }

        echo $OUTPUT->single_button($url, $strreturn, 'get', ['class' => 'continuebutton']);
        
        echo $OUTPUT->box_end();

        echo $OUTPUT->footer();
        
        die;
    }


    /**
     * Connect to the CAS (clientcas connection or proxycas connection)
     */
    function connectCAS() {
        global $CFG;
        static $connected = false;

        parent::connectCAS();

        // Función invocada para tratar un petición de Single Sign Out
        phpCAS::setSingleSignoutCallback('casSingleSignOut', array(session_id()));

        // Recoge las peticiones Single Sign Out, invocando la función especificada en setSingleSignoutCallback
        phpCAS::handleLogoutRequests(false);
    }


    /**
     * Returns true if user should be coursecreator.
     * here returns always false, auth casulpgc not checking user attributes in LDAP
     *
     * @param mixed $username    username (without system magic quotes)
     * @return boolean result
     */
    function iscreator($username) {
        return false;
    }

    /**
     * Syncronizes users from ULPGC CAS.
     *
     * Actually do nothig, this function relocated to local/sinculpgc/cli/sincusuarios.php
     *
     * @param bool $do_updates will do pull in data updates from LDAP if relevant
     * @return nothing
     */
    function sync_users($do_updates=true) {
        // this function relocated to local/sinculpgc/cli/sincusuarios.php
        // preserved here for documentation purposes
        mtrace("... function relocated to local/sinculpgc/cli/sincusuarios.php ");
    }


    /**
     * Return a list of identity providers to display on the login page.
     *
     * @param string|moodle_url $wantsurl The requested URL.
     * @return array List of arrays with keys url, iconurl and name.
     */
    public function loginpage_idp_list($wantsurl) {
        if ($this->config->lockauth) {
            // no other CAS login
            return [];
        }

        $idp_list = parent::loginpage_idp_list($wantsurl);
        if(isset($idp_list[0]['name'])) {
            $idp_list[0]['name'] = get_string('pluginname', 'auth_casulpgc');
        }
        
        return $idp_list;
    }
}
