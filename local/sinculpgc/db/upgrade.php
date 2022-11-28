<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage sinculpgc
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file keeps track of upgrades to
// the ulpgccore plugin
//


function xmldb_local_sinculpgc_upgrade($oldversion) {

    global $CFG, $DB;

    $dbman = $DB->get_manager();

    /// just a mockup
    if ($oldversion < 0) {
        throw new upgrade_exception('local_sinculpgc', $oldversion, 'Can not upgrade such an old plugin');
    }

    if ($oldversion < 2016030500) {

    // rename existing user helper table
        $table = new xmldb_table('user_ulpgc');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'local_sinculpgc_user');
        }

    // create new user helper table
        $table = new xmldb_table('local_sinculpgc_user');
        // Conditionally launch create table for local_sinculpgc_user
        if (!$dbman->table_exists($table)) {
            // Adding fields to table local_sinculpgc_user.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('category', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
            $table->add_field('dedication', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('totaldedication', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');

            // Adding keys to table local_sinculpgc_user.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

            $dbman->create_table($table);
        }


    // create new user helper table
        $table = new xmldb_table('local_sinculpgc_units');
        // Conditionally launch create table for local_sinculpgc_units
        if (!$dbman->table_exists($table)) {
            // Adding fields to table local_sinculpgc_units.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('idnumber', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, null);
            $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('director', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, null);
            $table->add_field('secretary', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, null);

            // Adding keys to table local_sinculpgc_units.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_index('idnumber', XMLDB_INDEX_NOTUNIQUE, array('idnumber'));
            $table->add_index('type', XMLDB_INDEX_NOTUNIQUE, array('type'));

            $dbman->create_table($table);
        }

        $table = new xmldb_table('centros');
        if ($dbman->table_exists($table)) {
            $data = $DB->get_recordset('centros', null);
            if($data->valid()) {
                foreach($data as $rec) {
                    $rec->type = 'centre';
                    $rec->idnumber = $rec->code;
                    unset($rec->id);
                    if(!$old = $DB->get_record('local_sinculpgc_units', array('idnumber'=>$rec->idnumber, 'type'=>$rec->type))) {
                        $DB->insert_record('local_sinculpgc_units', $rec);
                    } else {
                        $rec->id = $old->id;
                        $DB->update_record('local_sinculpgc_units', $rec);
                    }
                }
            }
            $data->close();

            $dbman->drop_table($table);
        }

        $table = new xmldb_table('departamentos');
        if ($dbman->table_exists($table)) {
            $data = $DB->get_recordset('departamentos', null, '', 'id, codigo AS idnumber, departamento AS name, director, secretario AS secretary');
            if($data->valid()) {
                foreach($data as $rec) {
                    $rec->type = 'department';
                    $rec->idnumber = $rec->code;
                    unset($rec->id);
                    if(!$old = $DB->get_record('local_sinculpgc_units', array('idnumber'=>$rec->idnumber, 'type'=>$rec->type))) {
                        $DB->insert_record('local_sinculpgc_units', $rec);
                    } else {
                        $rec->id = $old->id;
                        $DB->update_record('local_sinculpgc_units', $rec);
                    }
                }
            }
            $data->close();

            $dbman->drop_table($table);
        }

         upgrade_plugin_savepoint(true, 2016030500, 'local', 'sinculpgc');
    }

    if ($oldversion < 2019102500) {
        $table = new xmldb_table('local_sinculpgc_user');
        $field = new xmldb_field('dni');
        if ($dbman->field_exists($table, $field)) {
            //$dbman->drop_field($table, $field);
        }
   
        upgrade_plugin_savepoint(true, 2019102500, 'local', 'sinculpgc');
    }

    if ($oldversion < 2021062600) {    
        $table = new xmldb_table('local_sinculpgc_units');    

        $field = new xmldb_field('director', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, null);
        // Add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } else {
            $dbman->change_field_type($table, $field);
        }
        
        $field = new xmldb_field('secretary', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, null);
        // Add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } else {
            $dbman->change_field_type($table, $field);
        }
        
        $field = new xmldb_field('coord', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, null);        
        // Add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } else {
            $dbman->change_field_type($table, $field);
        }
        
        upgrade_plugin_savepoint(true, 2021062600, 'local', 'sinculpgc');
    }
        
    
    if ($oldversion < 2022080800) {    
        $table = new xmldb_table('local_sinculpgc_units');    

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');        
        // Add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    // create new rules helper table
        $table = new xmldb_table('local_sinculpgc_rules');
        // Conditionally launch create table for local_sinculpgc_units
        if (!$dbman->table_exists($table)) {
            // Adding fields to table local_sinculpgc_units.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('enrol', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
            $table->add_field('roleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('searchfield', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
            $table->add_field('searchpattern', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('enrolparams', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('groupto', XMLDB_TYPE_CHAR, '255', null, null, null, null);            
            $table->add_field('useidnumber', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');            
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');        

            // Adding keys to table local_sinculpgc_rules.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('roleid', XMLDB_KEY_FOREIGN, array('roleid'), 'role', array('id'));
            $table->add_index('enrol', XMLDB_INDEX_NOTUNIQUE, array('enrol'));
            $table->add_index('enabled', XMLDB_INDEX_NOTUNIQUE, array('enabled'));
            $table->add_index('enrol-enabled', XMLDB_INDEX_NOTUNIQUE, array('enrol, enabled'));
            
            $dbman->create_table($table);
        }
        
        upgrade_plugin_savepoint(true, 2022080800, 'local', 'sinculpgc');
    }
    
    if ($oldversion < 2022080802) {    
        $table = new xmldb_table('local_sinculpgc_rules');    
        $field = new xmldb_field('togroup', XMLDB_TYPE_CHAR, '255', null, null, null, null);            
        // rename field 
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'groupto');
        }    
        
        upgrade_plugin_savepoint(true, 2022080802, 'local', 'sinculpgc');
    }    
    
    return true;
}
