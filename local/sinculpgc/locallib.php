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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Funciones necesarias para la sincronización con base de datos externa
 *
 * Este archivo contiene las funciones necesarias para conectarse con una base de datos
 * externa y obtener información de la misma.
 *
 * @package local_sinculpgc
 * @copyright 2014 Victor Deniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->libdir . '/adodb/adodb.inc.php');

/**
 * Codifica en UTF-8 las cadenas recibidas.
 * Acepta una cadena, un array o un objeto. En los dos últimos casos convierte todos
 * las cadenas del elemento pasado por parámetro.
 *
 * @param mixed $input
 *            Elemento cuyas cadenas se codificarán en UTF-8
 */
function utf8_encode_deep(&$input)
{
    if (is_string($input)) {
        $input = utf8_encode($input);
    } else
        if (is_array($input)) {
            foreach ($input as &$value) {
                utf8_encode_deep($value);
            }

            unset($value);
        } else
            if (is_object($input)) {
                $vars = array_keys(get_object_vars($input));

                foreach ($vars as $var) {
                    utf8_encode_deep($input->$var);
                }
            }
}

/**
 * Establece conexión con la base de datos externa.
 *
 * @return null|ADONewConnection se establece la conexión, devuelve un objeto que representa la conexión
 */
function db_init()
{
    global $CFG;

    // Conecta con la base de datos externa (fuerza una nueva conexión)
    $dbconexion = ADONewConnection($CFG->ulpgcdbtype);

    $dbconexion->charSet = $CFG->ulpgccharset;
    $dbconexion->Connect($CFG->ulpgcdbhost, $CFG->ulpgcdbuser, $CFG->ulpgcdbpass, $CFG->ulpgcdbname, true);

    $dbconexion->SetFetchMode(ADODB_FETCH_ASSOC);

    return $dbconexion;
}

/**
 * Ejecuta una sentencia en la base de datos externa. No devuelve registros.
 * Orientada a operaciones de inserción o actualización.
 *
 * @param ADONewConnection $database
 *            Objeto que representa la conexión a la base de datos externa
 * @param string $consulta
 *            Sentencia a ejecutar en la base de datos externa
 * @return boolean Devuelve true si se ejecuta con éxito, false en caso contrario
 */
function execute_external_db($database, $consulta)
{
    global $CFG;

    $rs = $database->Execute($consulta);

    if (! $rs) {
        print_error('auth_dbcantconnect', 'auth_db');
        return false;
    }

    return true;
}

/**
 * Devuelve los registros obtenidos al realizar una consulta en la base de datos externa.
 *
 * @param ADONewConnection $conexion Objeto que representa la conexión a la base de datos externa
 * @param string $consulta Consulta a realizar en la base de datos externa
 * @param string $id Columna que hace las veces de identificador único
 * @return object[] Array de objetos con los registros devueltos por la consulta
 */
function get_rows_external_db($conexion, $consulta, $id)
{
    global $CFG;

    $result = array();

    $rs = $conexion->Execute($consulta);

    if (! $rs) {
        print_error('auth_dbcantconnect', 'auth_db');
    } else
        if (! $rs->EOF) {
            while ($rec = $rs->FetchRow()) {
                $rec = (object) array_change_key_case((array) $rec, CASE_LOWER);
                utf8_encode_deep($rec);
                $result [$rec->$id]= $rec;
            }
        }

    return $result;
}

/**
 * Cierra la conexión con la base de datos externa
 *
 * @param ADONewConnection $conexion Objeto que representa la conexión a la base de datos
 * @return null
 */
function db_close($conexion)
{
    $conexion->Close();
    return;
}

/////////////////////////////////////////////////////////////////////////////////

/**
 * Search courses by rule criteria and apply enrol instance
 *
 * @param int $ruleid The sinculpgc rule to use,  means all rules
 * @param bool $forcereset whether to force reset of manually modified enrol instances
 * @return null
 */
function local_sinculpgc_rule_add_enrol_instances($ruleid = 0, $forcereset = false) {
    global $DB;

                //local_sinculpgc_synch_rule($rule);
                // search by enrol courses with SQL In rule & NOT enrol customint8 = rule or rule modifieed after enrol  
                //force : all updated, even those enrol timemodified after rule = sobreescribir cambios manuales Config???
                
                //only add /update
                
    // get the rules to work on
    $rules = [];
    if($ruleid) {
        $rule = \local_sinculpgc\sinculpgcrule::get_record(['id' => $ruleid]);
        if($rule->enabled) {
            $rules[$rule->id] = $rule;
        }
    } else {
        $rules = \local_sinculpgc\sinculpgcrule::get_enabled_rules();
    }
    
    $plugins = enrol_get_plugins(false);
    $config = get_config('local_sinculpgc');
    
    foreach($rules as $rule) {
    
        list($searchwhere, $params) = get_rule_search_sql($rule); 
        $params['ruleid'] = $rule->get('id');
        
        $resetwhere = '';
        if($forcereset) {
            $resetwhere = ' OR ( e.timemodified > r.timemodified ) ';
        }
       
        $sql = "SELECT c.id, c.idnumber, c.shortname, c.category, e.id AS enrolid, r.id AS ruleid  
                  FROM {course} c 
             LEFT JOIN {enrol} e ON e.courseid = c.id 
             LEFT JOIN {local_sinculpgc_rules} r ON r.id = e.customint8 AND e.enrol = r.enrol
                 WHERE $searchwhere AND 
                       ( (e.id IS NULL) OR ( (e.customint8 = :ruleid) AND (e.timemodified < r.timemodified) ) $resetwhere ) ";
        
        $rs = $DB->get_recordset_sql($sql, $params);
        
        $enrol = $rule->get('enrol');
    
        foreach($rs as $course) {
            
            $plugin = $plugins[$enrol];
            
            $instance = $rule->extract_enrol_instance($course->id);
            
            if(isset($course->customint8) && $course->customint8) {
                // we have an instance, we are updating
                $plugin->update_instance($course, $instance); 
                $instances[] = $course->enrolid;
            } else {
                // we are adding a new one
                $instances[] = $plugin->add_instance($course, $instance); 
            }
        }
        $rs->close();
        
        // make added or updated enrol instances have the same timemodified as rule
        $instances = array_unique(array_filter($instances));
        if($instances) {
            list($insql, $params) = $DB->get_in_or_equal($instances);
            $select = "id $insql ";
            $DB->set_field_select('enrol', 'timmodified', $rule->get('timemodified'), $select, $params);
        }
    }
                
                

}

/**
 * Search courses that hsve enrol instances associated to sinculpgc rules 
 * and remove if rule deleted or disabled, or cours eno longer in rule  search criteria 
 *
 * @param int $ruleid The sinculpgc rule to use,  means all rules
 * @param bool $withdisabled whether to include existing but disabled rules in the removal or not
 * @return null
 */
function local_sinculpgc_rule_remove_enrol_instances($ruleid = 0, $withdisabled = false) {
    global $DB; 
    // process deletion of enrol instances
    //Borrados & disabled not keep
    // SELECT enrol customint8 > 0 LEF JOIN rule On customint8 = ruleid NULL 
    // SELECT enrol customint8 > 0  and rule customint8 = ruleid disabled & notkeep

    $params = [];
    $rulewhere = '';
    if($ruleid) {
        $rulewhere = ' AND e.customint8 = :ruleid '; 
        $params['ruleid'] = $ruleid;    
    }
        
    $disabledwhere = '';
    if($withdisabled) {
        $disabledwhere = ' OR ( (r.id = e.customint8) AND (r.enabled = 0) ) ';
    }
    
    $sql = "SELECT e.* 
            FROM {enrol} e 
            LEFT JOIN {local_sinculpgc_rules} r ON r.id = e.customint8 AND e.enrol = r.enrol
            WHERE e.customint8 > 0  AND ( (r.id IS NULL) $disabledwhere ) $rulewhere "; 
    $rs = $DB->get_recordset_sql($sql, $params);
    
    $plugins = enrol_get_plugins(false);
    foreach($rs as $instance) {
        $plugin = $plugins[$instance->enrol];
        $plugin->delete_instance($instance);
        
        //TODO mtrace messages
    }
    $rs->close();
} 
