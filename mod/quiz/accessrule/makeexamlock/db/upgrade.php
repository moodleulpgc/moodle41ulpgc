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
 * This file keeps track of upgrades to the makeexamlock module
 *
 * @package    quizaccess_makeexamlock
 * @copyright  2020 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute makeexamlock upgrade from the given old version
 * @global moodle_database $DB
 * @param int $oldversion
 * @return bool
 */
function xmldb_quizaccess_makeexamlock_upgrade($oldversion) {
    /** @global moodle_database $DB */
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2020082302) {

        // update new table: quizaccess_makeexamlock.
        $table = new xmldb_table('quizaccess_makeexamlock');
        $field = new xmldb_field('makeexamlock', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        
        // Add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } else {
            $dbman->change_field_type($table, $field);
        }

        // change default for existing records
        $DB->set_field('quizaccess_makeexamlock', 'makeexamlock', -1, array('makeexamlock' => 1));
        
        upgrade_plugin_savepoint(true, 2020082302, 'quizaccess', 'makeexamlock');
    }

    return true;
}
