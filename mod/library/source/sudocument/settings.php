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
 * This file defines the admin settings for this plugin
 *
 * @package   librarysource_sudocument
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$settings->add(new admin_setting_configcheckbox('librarysource_sudocument/enabled',
                   new lang_string('enabled', 'library'),
                   new lang_string('enabled_help', 'library'), 1));

$settings->add(new admin_setting_configtext('librarysource_sudocument/apiurl',
                    get_string('settings_apiurl', 'librarysource_sudocument'),
                    get_string('settings_apiurl_help', 'librarysource_sudocument'),
                    '', PARAM_URL
                ));

$settings->add(new admin_setting_configtext('librarysource_sudocument/linkurl',
                    get_string('settings_linkurl', 'librarysource_sudocument'),
                    get_string('settings_linkurl_help', 'librarysource_sudocument'),
                    '', PARAM_URL
                ));

$settings->add(new admin_setting_configtext('librarysource_sudocument/handleurl',
                    get_string('settings_handleurl', 'librarysource_sudocument'),
                    get_string('settings_handleurl_help', 'librarysource_sudocument'),
                    '', PARAM_URL
                ));

$settings->add(new admin_setting_configtext('librarysource_sudocument/separator',
                    get_string('settings_separator', 'librarysource_sudocument'),
                    get_string('settings_separator_help', 'librarysource_sudocument'),
                    '', PARAM_ALPHANUMEXT
                ));

