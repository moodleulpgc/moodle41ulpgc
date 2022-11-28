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
 * Settings.
 * @package local_sitenotice
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('root', new admin_category('sitenotice', new lang_string('pluginname', 'local_sitenotice')));

if ($hassiteconfig) {
    $temp = new admin_settingpage('sitenoticesettings',
        new lang_string('setting:settings', 'local_sitenotice'));

    $temp->add(new admin_setting_configcheckbox('local_sitenotice/enabled',
        new lang_string('setting:enabled', 'local_sitenotice'),
        new lang_string('setting:enableddesc', 'local_sitenotice'), 0));

    $temp->add(new admin_setting_configcheckbox('local_sitenotice/allow_update',
        new lang_string('setting:allow_update', 'local_sitenotice'),
        new lang_string('setting:allow_updatedesc', 'local_sitenotice'), 0));

    $temp->add(new admin_setting_configcheckbox('local_sitenotice/allow_delete',
        new lang_string('setting:allow_delete', 'local_sitenotice'),
        new lang_string('setting:allow_deletedesc', 'local_sitenotice'), 0));

    $temp->add(new admin_setting_configcheckbox('local_sitenotice/cleanup_deleted_notice',
        new lang_string('setting:cleanup_deleted_notice', 'local_sitenotice'),
        new lang_string('setting:cleanup_deleted_noticedesc', 'local_sitenotice'), 0));

    $ADMIN->add('sitenotice', $temp);
    $settings = null;
}

$managenotice = new admin_externalpage('local_sitenotice_managenotice',
    get_string('setting:managenotice', 'local_sitenotice', null, true),
    new moodle_url('/local/sitenotice/managenotice.php'), 'local/sitenotice:manage');

$ADMIN->add('sitenotice', $managenotice);
