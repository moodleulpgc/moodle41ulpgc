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
 * Strings for component 'auth_casulpgc', language 'en'.
 *
 * @package   auth_casulpgc
 * @copyright 2023 Enrique Castro @ULPGC 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'CAS ULPGC (SSO)';
$string['auth_casulpgcdescription'] = 'This method uses a CAS server (Central Authentication Service) to authenticate users in a Single Sign On environment (SSO).
This plugin depends on internal moodle CAS auth plugin. <strong>CAS server settings need to be configured in the regular CAS plugin</strong>.';
$string['auth_casulpgc_settings'] = 'Settings CAS ULPGC ';
$string['auth_casulpgc_lockauth_key'] = 'Lock login to CAS ';
$string['auth_casulpgc_lockauth'] = 'If enabled, then users can ONLY authenticate using the CAS server.
If not authenticated an error message is displayed and no other option to login is provided. ';
$string['auth_casulpgc_caserror_key'] = 'CAS unconfigured';
$string['auth_casulpgc_caserror'] = 'Some important parameters for connection with CAS server are missing.  
This casulpgc authentication method will NOT work. ';
$string['auth_casulpgc_nonexistent_return_url_key'] = 'Return URL for nonexistent';
$string['auth_casulpgc_nonexistent_return_url'] = 'The URL  to redirect users authenticated in ULPGC CAS but non-existing in the current platform. <br />
This is a url (without the https:// part) where users will be redirected. If left empty, go to ULPGC web site';
$string['nonexistentmsg'] = 'The user doesn\'t exist in this platform. Please contact ULPGC Campus virtual support.'; 
$string['noaccessreturn'] = 'Return to ULPGC';
