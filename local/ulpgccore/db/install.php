<?php
/**
 * ULPGC specific customizations (changes to core tables)
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_local_ulpgccore_install() {
    global $CFG, $DB;

        $dbman = $DB->get_manager();
    /// Install modifications into core tables

    /// mods to user table
    /// Define table course to be modified
        $table = new xmldb_table('user');

        /// Changing type of field name on table user_info_field to text
        $field = new xmldb_field('institution');
        $field->set_attributes(XMLDB_TYPE_CHAR, '80', null, null, null, '');
    /// Launch change of existing field
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }

        $field = new xmldb_field('department');
        $field->set_attributes(XMLDB_TYPE_CHAR, '80', null, null, null, '');
    /// Launch change of existing field
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }
        
        // Added index to comments table to speedup question table backup/retore
        $table = new xmldb_table('comments');
        $index = new xmldb_index('area-component-item', XMLDB_INDEX_NOTUNIQUE, array('commentarea', 'component', 'itemid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Added index to logstore_standard_log table to speedup reports and backup/retore
        $table = new xmldb_table('logstore_standard_log');
        $index = new xmldb_index('course-ctxlevel-ctxinstance', XMLDB_INDEX_NOTUNIQUE, array('courseid', 'contextlevel', 'contextinstanceid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }        
}
