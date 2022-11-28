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
 * Meta category link enrolment plugin uninstallation.
 *
 * @package    enrol
 * @subpackage metapattern
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_metapattern_uninstall() {
    global $CFG, $DB;

    $metapattern = enrol_get_plugin('metapattern');
    $rs = $DB->get_recordset('enrol', array('enrol'=>'metapattern'));
    foreach ($rs as $instance) {
        $metapattern->delete_instance($instance);
    }
    $rs->close();

    role_unassign_all(array('component'=>'enrol_metapattern'));

    return true;
}
