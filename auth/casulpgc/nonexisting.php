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
 * Login failure page.
 *
 * @package    auth_casulpgc
 * @subpackage auth
 * @copyright  2023 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
//require_once('lib.php');

$context = context_system::instance();
$PAGE->set_url("$CFG->wwwroot/auth/casulpgc/nonexisting.php");
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

$username = '42810976';

echo $OUTPUT->header();

echo $OUTPUT->heading("$site->fullname");
$errormsg = get_string('unauthorisedlogin', '', $username);

echo $OUTPUT->box($errormsg, 'generalbox  alert alert-danger', 'intro');

$errormsg = get_string('nonexistentmsg', 'auth_casulpgc');
echo $OUTPUT->box($errormsg, 'generalbox', 'intro');

//echo 'No tiene acceso a esta plataforma. Póngase en contacto con el servicio de soporte del Campus Virtual';

$strreturn = get_string('noaccessreturn', 'auth_casulpgc');
$url = 'https://www.ulpgc.es/';
$config = get_config('auth_casulpgc');
if(!empty($config->logout_return_url)) {
    $url = new moodle_url('https://'.$config->logout_return_url);
}

echo $OUTPUT->single_button($url, $strreturn, 'get', ['class' => 'continuebutton']);

echo $OUTPUT->footer();
