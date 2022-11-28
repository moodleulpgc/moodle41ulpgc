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
 * @package   videolibsource_office365
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$settings->add(new admin_setting_configcheckbox('videolibsource_office365/default',
                   new lang_string('default', 'videolibsource_office365'),
                   new lang_string('default_help', 'videolibsource_office365'), 0));
                   
$settings->add(new admin_setting_configcheckbox('videolibsource_office365/updategraded',
                   new lang_string('updategraded', 'videolibsource_office365'),
                   new lang_string('updategraded_help', 'videolibsource_office365'), 0));

$settings->add(new admin_setting_configtext('videolibsource_office365/remotefolder',
                   new lang_string('remotefolder', 'videolibsource_office365'),
                   new lang_string('remotefolder_help', 'videolibsource_office365'), ''));
