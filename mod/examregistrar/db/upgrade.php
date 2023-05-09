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
 * This file keeps track of upgrades to the examregistrar module
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute examregistrar upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_examregistrar_upgrade($oldversion) {
    global $DB, $USER;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2013122110) {

        // Define field assignplugincm to be added to forum.
        $table = new xmldb_table('examregistrar_exams');
        $field = new xmldb_field('assignplugincm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'visible');

        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('examregistrar_bookings');
        $index = new xmldb_index('examid-userid', XMLDB_INDEX_UNIQUE, array('examid', 'userid'));
        // Conditionally launch drop index name
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $index = new xmldb_index('examid-userid', XMLDB_INDEX_NOTUNIQUE, array('examid', 'userid'));
        // Conditionally launch add index examid-userid
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('examid-userid-booked-bookedsite', XMLDB_INDEX_NOTUNIQUE, array('examid', 'userid', 'booked', 'bookedsite'));
        // Conditionally launch add index examid-userid-booked-bookedsite
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('examregistrar_examfiles');
        $field = new xmldb_field('reviewid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'attempt');
        // Conditionally launch add field reviewid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('idnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'attempt');
        // Conditionally launch add field idnumber.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'attempt');
        // Conditionally launch add field name.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        // Conditionally launch add field component.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('taken', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'idnumber');
        // Conditionally launch add field taken.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'taken');
        // Conditionally launch add field userid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('modifierid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'reviewerid');
        }

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'reviewerid');
        // Conditionally launch add field userid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timeapproved', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');
        // Conditionally launch add field timeapproved.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timerejected', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timeapproved');
        // Conditionally launch add field timerejected.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('reviewid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'attempt');
        // Conditionally launch add field reviewid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }



        $table = new xmldb_table('examregistrar');
        $field = new xmldb_field('reviewmod', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'workmode');
        // Conditionally launch add field reviewmod.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2013122110, 'examregistrar');
    }

    if ($oldversion < 2013122113) {
        $table = new xmldb_table('examregistrar_bookings');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'modifierid');
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            if ($dbman->field_exists($table, $field)) {
                $sql = "UPDATE {examregistrar_bookings}
                        SET timecreated = timemodified
                        WHERE timecreated = 0 " ;
                $DB->execute($sql);
            }

        }

        upgrade_mod_savepoint(true, 2013122113, 'examregistrar');
    }

    if ($oldversion < 2015041600) {
        $table = new xmldb_table('examregistrar_examfiles');
        $field = new xmldb_field('printmode', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'reviewid');
        // Conditionally launch add field printmode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2015041600, 'examregistrar');
    }

    if ($oldversion < 2015041601) {
        $table = new xmldb_table('examregistrar_examfiles');

        $field = new xmldb_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }

        $field = new xmldb_field('reviewid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'reviewerid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }

        upgrade_mod_savepoint(true, 2015041601, 'examregistrar');
    }

    if ($oldversion < 2015101600) {

        $table = new xmldb_table('examregistrar_examfiles');

        $field = new xmldb_field('taken', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'idnumber');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timerejected', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timeapproved');
        // Conditionally launch add field timerejected.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2015101600, 'examregistrar');
    }
    
    if ($oldversion < 2018051800) {

        $table = new xmldb_table('examregistrar_session_seats');
        $field = new xmldb_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field reviewerid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('certified', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field certified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('taken', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field taken.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('showing', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field showing.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define table messageinbound_datakeys to be created.
        $table = new xmldb_table('examregistrar_responses');
        // Adding fields to table session responses.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examsession', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('roomid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('additional', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('examfile', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('numfiles', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('showing', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('taken', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('modifierid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timefiles', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timeuserdata', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        
        // Adding keys to table session responses.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('examsession', XMLDB_KEY_FOREIGN, array('examsession'), 'examregistrar_sessions', array('id'));
        $table->add_key('examid', XMLDB_KEY_FOREIGN, array('examid'), 'examregistrar_exams', array('id'));
        $table->add_key('roomid', XMLDB_KEY_FOREIGN, array('roomid'), 'examregistrar_locations', array('id'));
        $table->add_key('examfile', XMLDB_KEY_FOREIGN, array('examfile'), 'examregistrar_examfile', array('id'));

        // Adding indexes to table session responses.
        $table->add_index('examsession-examid-roomid-examfile', XMLDB_INDEX_UNIQUE, array('examsession', 'examid', 'roomid', 'examfile'));

        // Conditionally launch create table for exam student response files.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    
        upgrade_mod_savepoint(true, 2018051800, 'examregistrar');
    }
    
    
    if ($oldversion < 2018051803) {
        $table = new xmldb_table('examregistrar_responses');
        
        foreach(array('numfiles', 'showing', 'taken') as $name) {
            $field = new xmldb_field($name, XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');
            if ($dbman->field_exists($table, $field)) {
                $dbman->change_field_default($table, $field);
            }
        }
        
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }
        
        foreach(array('additional', 'modifierid', 'reviewerid', 'timefiles', 'timeuserdata', 'timemodified') as $name) {
            $field = new xmldb_field($name, XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            if ($dbman->field_exists($table, $field)) {
                $dbman->change_field_default($table, $field);
            }
        }

        $index = new xmldb_index('examsession-bookedsite-examid-roomid-examfile', XMLDB_INDEX_UNIQUE, array('examsession', 'bookedsite', 'examid', 'roomid', 'examfile'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        $index = new xmldb_index('examsession-examid-roomid-examfile', XMLDB_INDEX_UNIQUE, array('examsession', 'examid', 'roomid', 'examfile'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        $key = new xmldb_key('bookedsite', XMLDB_KEY_FOREIGN, array('bookedsite'), 'examregistrar_locations', array('id'));
        $dbman->drop_key($table, $key);

        $field = new xmldb_field('bookedsite', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        $table = new xmldb_table('examregistrar_session_seats');
        $field = new xmldb_field('timereviewed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        // Conditionally launch add field timereviewed.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2018051803, 'examregistrar');
    }

    if ($oldversion < 2019080100) {    
    
                // Define table messageinbound_datakeys to be created.
        $table = new xmldb_table('examregistrar_vouchers');
        // Adding fields to table session vouchers.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examregid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('bookingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('uniqueid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        
        // Adding keys to table session vouchers.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('bookingid', XMLDB_KEY_FOREIGN, array('bookingid'), 'examregistrar_sessions', array('id'));

        // Adding indexes to table session vouchers.
        $table->add_index('examregid-uniqueid', XMLDB_INDEX_UNIQUE, array('examregid, uniqueid'));    
    
        // Conditionally launch create table for exam student response files.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    
        upgrade_mod_savepoint(true, 2019080100, 'examregistrar');
    }
    
    if ($oldversion < 2019080200) {    
    
        // Define field assignplugincm to be added to forum.
        $table = new xmldb_table('examregistrar');
        $field = new xmldb_field('configdata', XMLDB_TYPE_TEXT, null, null, null, null, null, 'lagdays');

        // Conditionally launch add field configdata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        upgrade_mod_savepoint(true, 2019080200, 'examregistrar');
    }
    

    if ($oldversion < 2020082300) {
        // Define field quizplugincm to be added to forum.
        $table = new xmldb_table('examregistrar_exams');
        $field = new xmldb_field('quizplugincm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'assignplugincm');

        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        upgrade_mod_savepoint(true, 2020082300, 'quizplugincm');
    }    

    // major change to several exam delivery methods
    if ($oldversion < 2021021501) {
        // Define table plugin_config to be created.
        $table = new xmldb_table('examregistrar_plugin_config'); 
        // Adding fields to table plugin_config.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examregid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('plugin', XMLDB_TYPE_CHAR, '28', null, null, null, null);
        $table->add_field('subtype', XMLDB_TYPE_CHAR, '28', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '28', null, null, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, null, null, null);
        // Adding keys to table examregistrar_plugin_config.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('examregid', XMLDB_KEY_FOREIGN, array('examregid'), 'examregistrar', array('id'));
        // Adding indexes to table examregistrar_plugin_config.
        $table->add_index('plugin', XMLDB_INDEX_NOTUNIQUE, array('plugin'));
        $table->add_index('subtype', XMLDB_INDEX_NOTUNIQUE, array('subtype'));
        $table->add_index('name', XMLDB_INDEX_NOTUNIQUE, array('name'));        
        
        // Conditionally launch create table for exam examregistrar_plugin_config.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }       

        // now populate new table with existing data
        if ($dbman->table_exists($table)) {
            $select = $DB->sql_isnotempty('examregistrar', 'configdata', true, true);
            if($examregistrars = $DB->get_records_select_menu('examregistrar', 
                                            $select, null, '', 'id, configdata')) {
                $record = new \stdClass();
                $record->examregid = 0;
                $record->plugin = '';
                $record->subtype = 'examregistrar';
                $record->name = '';
                $record->value = '';
                foreach($examregistrars as $regid => $data) {
                    $record->examregid = $regid; 
                    $data = unserialize(base64_decode($data));
                    foreach($data as $name => $value) {
                        $record->name = $name;
                        if(is_array($value)) {
                            $value = implode(',', $value);
                        }
                        $record->value = $value;
                        $params = ['examregid' => $regid, 'plugin' => '', 
                                    'subtype' => 'examregistrar', 'name' => $record->name];
                        if($DB->record_exists('examregistrar_plugin_config', $params)) {
                            $DB->set_field('examregistrar_plugin_config', 'value', $record->value, $params);
                        } else {
                            $DB->insert_record('examregistrar_plugin_config', $record);
                        }
                    }
                }
            }
        }
    
        upgrade_mod_savepoint(true, 2021021501, 'examregistrar');
    }      
    
    // ensure a deliverysite is defined, if existing
    if ($oldversion < 2021021502) {
    
        $sql = "SELECT l.id, l.examregid
                FROM {examregistrar_locations} l 
                JOIN {examregistrar_elements} e ON e.id = l.location AND l.examregid = e.examregid
                WHERE e.type = 'locationitem' AND e.idnumber = 'online' ";
        $sites = $DB->get_records_sql_menu($sql, null);
        
        $record = new stdClass();
        $record->examregid = 0;
        $record->plugin = '';
        $record->subtype = 'examregistrar';
        $record->name = 'deliverysite';
        $record->value = '';                        
        foreach($sites as $site => $exregid) {
            $params = ['examregid' => $exregid, 'plugin' => '', 
                        'subtype' => 'examregistrar', 'name' => 'deliverysite'];
            if($DB->record_exists('examregistrar_plugin_config', $params)) {
                $DB->set_field('examregistrar_plugin_config', 'value', $site, $params);
            } else {
                $record->value = $site;
                $DB->insert_record('examregistrar_plugin_config', $record);
            }
        }
    
        upgrade_mod_savepoint(true, 2021021502, 'examregistrar');
    }      

    // ensure there is only a copy of configdata
    if ($oldversion < 2021021503) {

        $config = $DB->get_records('examregistrar_plugin_config', null, 'id ASC');
        
        if(!empty($config)) {
            foreach($config as $cid => $config) {
                $params = ['examregid' => $config->examregid, 
                            'plugin' => $config->plugin, 
                            'subtype' => $config->subtype, 
                            'name' => $config->name,
                            'id' => $cid];
                $select = "examregid = :examregid AND plugin = :plugin AND subtype = :subtype AND name = :name
                            AND id > :id";
                $DB->delete_records_select('examregistrar_plugin_config', $select, $params); 
            }    
        }
    
        upgrade_mod_savepoint(true, 2021021503, 'examregistrar');
    }        
    
    
    // major change to several exam delivery methods
    if ($oldversion < 2021032902) {    
        
        // Define table examdelivery to be created.
        $table = new xmldb_table('examregistrar_examdelivery');
        // Adding fields to table examregistrar_examdelivery.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('helpermod', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('helpercmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timeopen', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timeclose', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timelimit', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('parameters', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('bookedsite', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('component', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('modifierid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
       
        // Adding keys to table examregistrar_examdelivery.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('examid', XMLDB_KEY_FOREIGN, array('examid'), 'examregistrar_exams', array('id'));
        $table->add_key('helpercmid', XMLDB_KEY_FOREIGN, array('helpercmid'), 'course_modules', array('id'));
        $table->add_key('bookedsite', XMLDB_KEY_FOREIGN, array('bookedsite'), 'examregistrar_locations', array('id'));

        // Adding indexes to table examregistrar_examdelivery.
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, array('status'));
        $table->add_index('exam-helper-booked', XMLDB_INDEX_NOTUNIQUE, array('examid, helpercmid, bookedsite'));

        // Conditionally launch create table for exam student response files.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $field = new xmldb_field('bookedsite', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'parameters');
        // Conditionally launch add field bookedsite.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        // Define field deliveryid to be added to responses table.
        $table = new xmldb_table('examregistrar_responses');
        $field = new xmldb_field('deliveryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'examid');
        // Conditionally launch add field deliveryid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        // field deliveryid to be added to session_seats table.
        $table = new xmldb_table('examregistrar_session_seats');
        // Conditionally launch add field deliveryid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Populate new table from exams table 
        $delivery = new \stdClass();
        $delivery->timemodified = time();
        $delivery->modifierid = $USER->id;
        $delivery->component = 'upgrade';
        $bookedsites = [];
    
        $sql = "SELECT e.id AS examid, e.examregid, e.assignplugincm, e.quizplugincm, e.component, e.modifierid, e.timemodified,
                        ef.id AS examfileid, ef.taken
                  FROM {examregistrar_exams} e  
             LEFT JOIN {examregistrar_examfiles} ef ON ef.examid = e.id AND ef.status = :status
                 WHERE  (e.assignplugincm <> 0 OR e.quizplugincm <> 0) AND e.visible = 1";
        if($exams = $DB->get_records_sql($sql, array('status' => EXAM_STATUS_APPROVED))) {
            foreach($exams as $exam) {
                if(!isset($exam->examfileid)) {
                    $exam->examfileid = 0;
                }
                $params = ['examregid' => $exam->examregid, 'plugin' => '', 
                        'subtype' => 'examregistrar', 'name' => 'deliverysite'];
                if(!isset($bookedsites[$exam->examregid])) {
                    if(!$site = $DB->get_field('examregistrar_plugin_config', 'value', $params)) {
                        $site = 0;
                    }
                    $bookedsites[$exam->examregid] = $site;
                }
                $delivery->bookedsite = $bookedsites[$exam->examregid];
                
                $exam->status = 0;
                $delivery->examid = $exam->examid;
                $delivery->helpermod = '';
                if($exam->assignplugincm > 0) {
                    $delivery->helpermod = 'assign';
                    $delivery->helpercmid = $exam->assignplugincm;
                    $assign = $DB->get_record('assign', array('id' => $DB->get_field('course_modules', 'instance', array('id' => $exam->assignplugincm))));
                    $delivery->timeopen = $assign->allowsubmissionsfromdate;
                    $delivery->timeclose = $assign->duedate;
                    $delivery->timelimit = $assign->duedate - $assign->allowsubmissionsfromdate;
                }  
                if($exam->quizplugincm > 0) {
                    $quiz = $DB->get_record('quiz', array('id' => $DB->get_field('course_modules', 'instance', array('id' => $exam->quizplugincm))));
                    $delivery->helpermod = 'quiz';
                    $delivery->helpercmid = $exam->quizplugincm;
                    $delivery->timeopen = $quiz->timeopen;
                    $delivery->timeclose = $quiz->timeclose;
                    $delivery->timelimit = $quiz->timelimit;
                }
                if($delivery->helpermod && !$DB->record_exists('examregistrar_examdelivery', 
                        ['examid' => $delivery->examid, 'helpercmid' =>$delivery->helpercmid, 'bookedsite' =>$delivery->bookedsite])) {
                    $DB->insert_record('examregistrar_examdelivery', $delivery);
                }
            }
        }
    
        // Define field deliveryid to be added to responses table.
        $table = new xmldb_table('examregistrar_exams');
        $field = new xmldb_field('assignplugincm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        // Conditionally launch drop field assignplugincm.
        if ($dbman->field_exists($table, $field)) {
            //$dbman->drop_field($table, $field);
        }    
        
        $field = new xmldb_field('quizplugincm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        // Conditionally launch drop field quizplugincm.
        if ($dbman->field_exists($table, $field)) {
            //$dbman->drop_field($table, $field);
        }    

        upgrade_mod_savepoint(true, 2021032902, 'examregistrar');
    }        
    
    // field deliveryid to be added to session_seats table with default.
    if ($oldversion < 2021032905) {        
        $table = new xmldb_table('examregistrar_session_seats');
        $field = new xmldb_field('deliveryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'examid');
        // Conditionally launch change field deliveryid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }    
    
        upgrade_mod_savepoint(true, 2021032905, 'examregistrar');
    }

    /* 
     examregistrar_examdelivery.bookedsite may be takingmode (rooms, online, other) NO directly a bookedsite
     then associate delivery with a takingmode 
     
     In session, associate bookedsites with takingmodes
    */

    // field allowedtools in examfiles table, for examiner's instructions.
    if ($oldversion < 2021032906) {        
        $table = new xmldb_table('examregistrar_examfiles');    
        $field = new xmldb_field('allowedtools', XMLDB_TYPE_TEXT, null, null, null, null, null, 'printmode');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    return true;
}
