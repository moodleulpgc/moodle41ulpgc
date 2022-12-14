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
 * metapatternegory enrolment plugin settings and presets.
 *
 * @package    enrol
 * @subpackage metapattern
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------


    $settings->add(new admin_setting_heading('enrol_metapattern_settings', '', get_string('pluginname_desc', 'enrol_metapattern')));

    if (!during_initial_install()) {
        $allroles = array();
        foreach (get_all_roles() as $role) {
                $rolename = strip_tags(format_string($role->name)) . ' ('. $role->shortname . ')';
                $allroles[$role->id] = $rolename;
        }
        $settings->add(new admin_setting_configmultiselect('enrol_metapattern/nosyncroleids', get_string('nosyncroleids', 'enrol_metapattern'), get_string('nosyncroleids_desc', 'enrol_metapattern'), array(), $allroles));

        $options = array(
            ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
            ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
            ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'));
        $settings->add(new admin_setting_configselect('enrol_metapattern/unenrolaction', get_string('extremovedaction', 'enrol'), get_string('extremovedaction_help', 'enrol'), ENROL_EXT_REMOVED_UNENROL, $options));
    }
}
