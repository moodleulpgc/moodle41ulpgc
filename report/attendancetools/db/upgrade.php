<?php
/**
 * ULPGC specific mod tracker customizations
 *
 * @package    report
 * @subpackage attendancetools
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file keeps track of upgrades to
// the ulpgccore plugin
//


function xmldb_report_attendancetools_upgrade($oldversion) {

    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023042400) {
    
        // Define table report_attendancetools_config to be created.
        $table = new xmldb_table('report_attendancetools_ds');

        // Adding fields to table report_attendancetools_devq.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sessionstart', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('sessionoffset', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '60');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table report_attendancetools_devq.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('cmkey', XMLDB_KEY_FOREIGN, array('cmid'), 'course_modules', array('id'));
        $table->add_key('userkey', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Conditionally launch create table for report_attendancetools_devq.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    
         upgrade_plugin_savepoint(true, 2023042400, 'report', 'attendancetools');
    }

    return true;
}
