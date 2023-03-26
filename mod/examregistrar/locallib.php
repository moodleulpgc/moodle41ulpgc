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
 * Internal library of functions for module examregistrar
 *
 * All the examregistrar specific functions, needed to implement the module
 * logic, are placed here.
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/renderable.php');
require_once($CFG->dirroot . '/user/selector/lib.php');

/**
 * Returns a particular array value for the named variable, taken from
 * POST or GET, otherwise returning a given default.
 *
 * This function should be used to initialise all optional values
 * in a script that are based on parameters.  Usually it will be
 * used like this:
 *    $ids = optional_param('id', array(), PARAM_INT);
 *
 *  Note: arrays of arrays are not supported, only alphanumeric keys with _ and - are supported
 *
 * @param string $parname the name of the page parameter we want
 * @param mixed  $default the default value to return if nothing is found
 * @param string $type expected type of parameter
 * @return array
 */
function optional_param_array_array($parname, $default, $type) {
    if (func_num_args() != 3 or empty($parname) or empty($type)) {
        throw new coding_exception('optional_param_array() requires $parname, $default and $type to be specified (parameter: '.$parname.')');
    }

    if (isset($_POST[$parname])) { // POST has precedence
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        return $default;
    }
    if (!is_array($param)) {
        debugging('optional_param_array() expects array parameters only: '.$parname);
        return $default;
    }

    return clean_param_array($param, $type, true);
}


/**
 * Returns ID of element instance of type Location that will be used as high order Venue locations
 * Velue locations are specified wuen booking an exam and hold rooms for exam allocations
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_venue_element($examregistrar) {
    global $DB;

    $exregid = examregistrar_get_primaryid($examregistrar);
    $venuecode = examregistrar_get_instance_config($exregid, 'venuelocationtype');
    return $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$exregid, 'type'=>'locationtypeitem', 'idnumber'=>$venuecode));
}


/**
 * Returns ID of element role instance to be used as default
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_default_role($examregistrar) {
    global $DB;

    $exregid = examregistrar_get_primaryid($examregistrar);
    $rolecode = examregistrar_get_instance_config($exregid, 'defaultrole');
    return $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$exregid, 'type'=>'roleitem', 'idnumber'=>$rolecode));
}



/**
 * Returns the first venue ID associated with this user, if any
 *
 * @param object $examregistrar object
 * @param int $userid
 * @param int $sessionid
 * @return int
 */
function examregistrar_user_venueid($examregistrar, $userid = 0, $session = 0) {
    global $DB; $USER;

    if(!$userid) {
        $userid = $USER->id;
    }

    $venueid = 0 ;

    $venuetype = examregistrar_get_venue_element($examregistrar);

    if($venues = examregistrar_get_user_rooms($examregistrar, $userid, 0, $session)) {
        foreach($venues as $venue) {
            if($venueid = examregistrar_get_room_venue($venue, $venuetype)) {
                break;
                //$venue = reset($venues);
                //$venueid = $venue->id;
            }
        }
    }

    return $venueid;
}


/**
 * Returns the venues the user is assigned as staffer.
 * If the user has several roles in the same room only one, first, is returned
 *
 * @param object $examregistrar object
 * @param int $userid
 * @param int $type locationtype to search for (either veues, roooms, etc, by elementID)
 * @param int $session exam session to look for room assignation (0 means any)
 * @param int $role roleID of the role assigned in that room
 * @return array of rooms with room & role names
 */
function examregistrar_get_user_rooms($examregistrar, $userid = 0, $type = 0, $session = 0, $role = 0  ) {
    global $DB, $USER;

    if(!$userid) {
        $userid = $USER->id;
    }

    $params = array('examregid' => examregistrar_get_primaryid($examregistrar),
                    'userid' => $userid);

    $typewhere = '';
    if($type) {
        $typewhere = ' AND l.locationtype = :type ';
        $params['type'] = $type;
    }

    $sessionwhere = '';
    if($session) {
        $sessionwhere = ' AND s.examsession = :examsession ';
        $params['examsession'] = $session;
    }

    $rolewhere = '';
    if($role) {
        $rolewhere = ' AND s.role = :role ';
        $params['role'] = $role;
    }

    $sql = "SELECT l.*, el.name AS roomname, el.idnumber AS roomidnumber, er.name AS rolename, er.idnumber AS roleidnumber
            FROM {examregistrar_locations} l
            JOIN {examregistrar_staffers} s ON s.locationid = l.id AND s.visible = 1
            JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
            JOIN {examregistrar_elements} er ON er.type = 'roleitem' AND s.role = er.id
            WHERE l.examregid = :examregid AND s.userid = :userid AND l.visible = 1 $typewhere $sessionwhere $rolewhere
            GROUP BY l.id
            ORDER BY el.name ASC ";

    return $DB->get_records_sql($sql, $params);
}


/**
 * Returns strings with template fields substitude with actual data
 *
 * @param array $replaces  an associative array of key (replace codes) / values (actual data)
 * @param string/array $subject where substitutions are performed, may be a string or an array of strings
 * @return string/array depends on subject type
 */
function examregistrar_str_replace($replaces, $subject) {
    foreach($replaces as $key => $value ){
        $subject = str_replace("%%$key%%", $value, $subject);
    }
    return $subject;
}


//////////////////////////////////////////////////////////////////////////////////
// Utility functions to get data fron tables                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the display name and idnumber of an item as stored in elements table
 *
 * @param object $item the record item from an examregisrar table
 * @param string $field the name of the field that stored the element ID
 * @return array (name, idnumber)
 */
function examregistrar_item_getelement($item, $field='element') {
    global $DB;

    if(!$item) {
        return array('', '');
    }
    
    if($field == 'stafferitem') {
        $user = $DB->get_record('user', array('id'=>$item->userid), 'id, firstname, lastname, idnumber');
            $element = new stdClass();
            $element->name = fullname($user);
            $element->idnumber = $user->idnumber;
    } else { 
        if(!$field || $field == 'element' ) {
            $eid = $item->id;
        } else {
            $eid = $item->$field;
        }
        if(!$element = $DB->get_record('examregistrar_elements', array('id'=>$eid))) {
            $element = new stdClass();
            $element->name = '';
            $element->idnumber = '';
        }
    }
    
    return array($element->name, $element->idnumber);
}


/**
 * Returns a menu of elements by type
 *
 * @param int $itemid the ID if the item in the table
 * @param string $table table where this ID is located
 * @param string $field of element type
 * @return array element name, idnumber
 */
function examregistrar_get_namecodefromid($itemid, $table = '', $field = '') {
    global $DB;

    if($table === '') {
        if(!$element = $DB->get_record('examregistrar_elements', array('id'=>$itemid), 'name,idnumber')) {
            $element = new stdClass();
            $element->name = '';
            $element->idnumber = '';
        }

        return array($element->name, $element->idnumber);
    }

    if(!$field) {
        $field = substr($table, 0, -1);
    }
    $item = $DB->get_record('examregistrar_'.$table, array('id' => $itemid));

    if($table == 'exams') {
        $period = new stdClass;
        list($period->name,  $period->idnumber) = examregistrar_get_namecodefromid($item->period, 'periods', 'period');
        $scope = $DB->get_record('examregistrar_elements', array('id'=>$item->examscope), 'name,idnumber');
        $name = $item->programme.'_'.$DB->get_field('course', 'shortname', array('id'=>$item->courseid)).
                '_'.$period->idnumber.'_'.$scope->idnumber.'_'.$item->callnum;
        $idnumber = '';
        return array($name, $idnumber);
    }

    return examregistrar_item_getelement($item, $field);
}


/**
 * Returns a menu of elements by type fron elements table
 *
 * @param object $examregistrar the examregistrar object
 * @param string $type element type
 * @param int $id the examregistrar ID
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_elements_getvaluesmenu($examregistrar, $type, $id = 0, $any = 'any') {
    global $DB;

    $menu = array();

    if(!$id) {
        $id = examregistrar_get_primaryid($examregistrar);
    }

    $params = array('examregid' => $id, 'type' => $type, 'visible'=>1);

    if($type == 'annualityitem' && $examregistrar->annuality) {
        $any = false;
        $params['idnumber'] = $examregistrar->annuality;
    }

    if($any) {
        $menu = array('' => get_string($any));
    }
    if($elements = $DB->get_records('examregistrar_elements', $params)) {
        foreach($elements as $key => $element) {
            $menu[$element->id] = $element->name.' ('.$element->idnumber.')';
        }
    }
    return $menu;
}


/**
 * Returns a menu of table records items, (id, name) for selected table
 *
 * @param object $examregistrar the examregistrar object
 * @param int $id the examregistrar ID
 * @param string $table component table
 * @param string $field the name of the field to look for in table
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_elements_get_fieldsmenu($examregistrar, $table, $field, $id =0, $any = 'any') {
    global $DB;

    if(!$id) {
        $id = examregistrar_get_primaryid($examregistrar);
    }

    $params = array('examregid' => $id);

    if($field == 'annuality' && $examregistrar->annuality) {
        $params['annuality'] = $examregistrar->annuality;
        $any = false;
    }

    if($field == 'programme' && $examregistrar->programme) {
        $params['programme'] = $examregistrar->programme;
        $any = false;
    }

    $menu = array();
    if($any) {
        $menu = array('' => get_string($any));
    }

    if($elements = $DB->get_records('examregistrar_'.$table, $params, " $field ASC ", "id, $field")) {
        foreach($elements as $key => $element) {
            $menu[$element->$field] = $element->$field;
        }
    }
    return $menu;
}


/**
 * Returns a menu of table records items, (id, name) for a tablefield referenced as idnumber
 *
 * @param object $examregistrar the examregistrar object
 * @param int $examregid the examregistrar ID
 * @param string $table element name & table
 * @param string $type element type
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @param string $field the name of the field to look for in table, usually 'idnumber'
 * @param array $params additionals params for query table associative array (field, value)
 * @param string $sort qualified SQL order snippet, with t.
 * @return array element id, element name
 */
function examregistrar_get_referenced_namesmenu($examregistrar, $table, $type, $exregid = 0, $any = 'any', $field = '', $params = array(), $sort='') {
    global $DB;

    if(!$exregid) {
        $exregid = examregistrar_get_primaryid($examregistrar);
    }

    if(!$field) {
        $field = substr($table, 0, -1);
    }

    $sqljoin = '';
    $sqlparams = array('examregid' => $exregid,
                       'type' => $type);

    $annualitywhere = '';
    if($examregistrar->annuality) {
        $annuality = $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$exregid, 'idnumber'=>$examregistrar->annuality));
        if($table == 'examsessions') {
            $sqljoin = 'JOIN {examregistrar_periods} p ON t.examregid = p.examregid AND t.period = p.id ';
            $anntable = 'examregistrar_periods';
        } else {
            $annuality = $DB->get_manager()->field_exists('examregistrar_'.$table, 'annuality') ? $annuality : 0;
        }
        if($annuality) {
            $sqlparams['annuality'] = $annuality;
            $annualitywhere = ' AND annuality = :annuality ';
            $any = false;
        }
    }

    $sql = "SELECT t.id, CONCAT(e.name,' (',e.idnumber,')')
            FROM {examregistrar_$table} t
            JOIN {examregistrar_elements} e ON t.examregid = e.examregid  AND  e.type = :type AND t.$field = e.id
            $sqljoin
            WHERE t.examregid = :examregid  AND e.visible = 1 AND t.visible = 1 ";


    $where = $annualitywhere;

    if($params) {
        foreach($params as $key => $value) {
            if($value) {
                $where .= " AND t.$key = :$key ";
                $sqlparams[$key] = $value;
            }
        }
    }

    if(!$sort) {
        $order = " ORDER BY e.name ASC ";
    } else {
        $order = ' ORDER BY '.$sort;
    }


    $menu = array();
    if($any) {
        $menu = array('' => get_string($any));
    }
    $items = $DB->get_records_sql_menu($sql.$where.$order, $sqlparams);

    /// TODO refactor in 2.6 construct name AFTER returning table, not in SQL TODO ///

    if($table == 'examsessions') {
        foreach($items as $id => $value) {
            $period = $DB->get_field('examregistrar_examsessions', 'period', array('id'=>$id));
            $element = $DB->get_field('examregistrar_periods', 'period', array('id'=>$period));
            $periodname = $DB->get_field('examregistrar_elements', 'idnumber', array('id'=>$element));
            $items[$id] = '['.$periodname.'] '.$value;
        }
    }

    $menu = $menu + $items;
    return $menu;
}


//////////////////////////////////////////////////////////////////////////////////
//   Exams submitting &a reviewing functions                                   //
////////////////////////////////////////////////////////////////////////////////


/**
 * Generates the exam idnumber identifier from course idnumber and exam period
 *
 * @param object $exam and exam record from examregistrar_exams
 * @param string $source initial name, tipically a course idnumber
 * @return string examfile idnumber string
 */
function examregistrar_examfile_idnumber($exam, $source) {

    $pieces = explode('_', $source);
    $examidnumber = $pieces[0].'-'.$pieces[5];
    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
    $examidnumber .= '-'. $idnumber;
    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
    $callnum = $exam->callnum > 0 ? $exam->callnum : 'R'.abs($exam->callnum);
    $examidnumber .= '-'. $idnumber.'-'.$callnum;

    return $examidnumber;
}


/**
 * Retrieves the instance config settings stored in plugin_config table
 *
 * @param int $examregid the ID if the examregistrar instance
 * @param mixed $fields either a string, name of a setting, 
 *               comma-separated list or an array of setting names to retrieve
 * @param string $prefix a prefix identifying config storable keys in object
 * @return mixed, object config data object or value if single field
 */
function examregistrar_get_instance_config($examregid, $fields = false, $prefix = '') {
    global $DB;

    //check if this is a primary instance
    $examregprimaryid = examregistrar_check_primaryid($examregid);
    
    $select = 'examregid = :examregid AND plugin = :plugin AND subtype = :subtype ';
    $params = ['examregid' => $examregprimaryid, 
                'plugin' => '',
                'subtype' => 'examregistrar'];
    if(is_string($fields)) {
        $fields = array_map('trim', explode(',', $fields));
    
    }
    if(is_array($fields) && !empty($fields)) {
        list($insql, $inparams) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED, 'name');
        $select .= " AND name $insql ";
        $params = $params + $inparams;
    }
           
    $config = false;       
    if($config = $DB->get_records_select_menu('examregistrar_plugin_config', $select, $params, '', 'name, value')) {
        if(isset($config['staffcats'])) {
            $config['staffcats'] = explode(',', $config['staffcats']);
        }
        if(count($config) == 1) {
            $config = reset($config);
        } else {
            if($prefix) {
                //used prefix to qualify keys for an user input form
                foreach($config as $key => $value) {
                    $config[$prefix.$key] = $value;
                    unset($config[$key]);
                }
            }
            $config = (object)$config;            
        }
    }    
   
   if(empty($config)) {
    $config = '';
   }
    return $config;
}

/**
 * Estores the instance config settings n plugin_config table
 *
 * @param int $examregid the ID if the examregistrar instance
 * @param object $config containing the key, value pairs, optionally with prefix in key
 * @param mixed $fields either a string, name of a setting, 
 *               comma-separated list or an array of setting names to save
                false means all fields
 * @param string $prefix a prefix identifying storable keys in input form
 * @return mixed, object config data object or value if single field
 */
function examregistrar_save_instance_config($examregid, $config, $fields = false, $prefix = '') { 
    global $DB;

    //check if this is a primary instance
    $examregprimaryid = examregistrar_check_primaryid($examregid);
    
    $select = 'examregid = :examregid AND plugin = :plugin AND subtype = :subtype ';
    $params = ['examregid' => $examregprimaryid, 
                'plugin' => '',
                'subtype' => 'examregistrar'];
    if(is_string($fields)) {
        $fields = array_map('trim', explode(',', $fields));
    
    }
    if(is_array($fields) && !empty(fields)) {
        list($insql, $inparams) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED, 'name');
        $select .= " AND name $insql ";
        $params = $params + $inparams;
    }
    
    $stored = $DB->get_records_select_menu('examregistrar_plugin_config', $select, $params, '', 'name, id');
    
    $all = empty($fields);
    $record = new stdClass();
    $record->examregid = $examregprimaryid;
    $record->plugin = '';
    $record->subtype = 'examregistrar';
    foreach($config as $name => $value) {
        if($prefix) {
            // use prefix when data comes from a form, input config form 
            if(strpos($name, $prefix) === 0) {
                $name = str_replace($prefix, '', $name);
            } else {
                continue;
            }
        }
        if(is_array($value)) {
            $value = implode(',', $value);
        }
        if($all || in_array($name, $fields)) {
            if(isset($stored[$name])) {
                // existing value, update
                $DB->set_field('examregistrar_plugin_config', 'value', $value, ['id' => $stored[$name]]);
            } else {
                //not existing,  insert new value
                $record->name = $name;
                $record->value = $value;
                $DB->insert_record('examregistrar_plugin_config', $record);
            }
        }
    }
}

function examregistrar_file_set_nameextension($examregistrar, $filename, $type, $ext='.pdf') {

    $filename = trim($filename);
    $ext = trim($ext);
    if(strpos($ext, '.') === false) {
        $ext = '.'.$ext;
    }

    $config = examregistrar_get_instance_config($examregistrar->id, 'extanswers, extkey, extresponses'); //config***

    $qualifier = '';
    if($type == 'answers') {
        $qualifier = $config->extanswers;
    } elseif($type == 'key') {
        $qualifier = $config->extkey;
    } elseif($type == 'responses') {
        $qualifier = $config->extresponses;
    }
    if($qualifier) {
        $qualifier = trim($qualifier);
    }

    return clean_filename($filename.$qualifier.$ext);
}

/**
 * Locates the Tracker issue associated to an examregistrar instance
 *  Returns de issueid of the issue creted for an exam file
 *
 * @param object $examregistrar the examregistrar object
 * @param object $examregistrar the examregistrar object
 * @param object $examregistrar the examregistrar object
 * @return int tracker issue ID
 */
function examregistrar_review_addissue($examregistrar, $course, $examfile, $tracker = false) {
    global $CFG, $DB, $OUTPUT;

    $issueid = 0;

    if(!$examregistrar->reviewmod) {
        return 0;
    }

    if(!$tracker) {
        $tracker = examregistrar_get_review_tracker($examregistrar, $course);
    }

    if(!$tracker) {
        return -1;
    }

    $exam = $DB->get_record('examregistrar_exams', array('id'=>$examfile->examid), '*', MUST_EXIST);
    $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);

    $examcoursename = $examcourse->shortname.' - '.format_string($examcourse->fullname);
    $summary = $examcoursename." \n".$examfile->idnumber.'  ('.$examfile->attempt.')' ;

    $items = array();
    $items[] = get_string('attemptn', 'examregistrar', $examfile->attempt);

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->annuality);
    $items[] = get_string('annualityitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    $items[] = get_string('programme', 'examregistrar').': '.$exam->programme;

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
    $items[] = get_string('perioditem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
    $items[] = get_string('scopeitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
    $items[] = get_string('examsessionitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    
    $examcontext = context_course::instance($examcourse->id);
    $filename = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'exam'); //$examfile->idnumber.'.pdf';
    $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$examcontext->id.'/mod_examregistrar/exam/rev/'.$tracker->course.'/'.$examfile->id.'/'.$filename);
    $mime = mimeinfo("icon", $filename);
    $icon = new pix_icon(file_extension_icon($filename), $mime, 'moodle', array('class'=>'icon'));
    $filelink = $OUTPUT->action_link($url, $filename, null, null, $icon); //   html_writer::link($ffurl, " $icon &nbsp; $filename ");
    $filelink .= '<br />';

    $filename = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'answers');//$examfile->idnumber.'_resp.pdf';
    $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$examcontext->id.'/mod_examregistrar/exam/rev/'.$tracker->course.'/'.$examfile->id.'/answers/'.$filename);
    $mime = mimeinfo("icon", $filename);
    $icon = new pix_icon(file_extension_icon($filename), $mime, 'moodle', array('class'=>'icon'));
    $filelink .= $OUTPUT->action_link($url, $filename, null, null, $icon); //   html_writer::link($ffurl, " $icon &nbsp; $filename ");
    
    if(isset($exam->quizplugincm) && $exam->quizplugincm) {
        $strexamfile = get_string('examfile', 'examregistrar');
        $examobj = new examregistrar_exam($exam);
        if($mkattempt = $examobj->get_makeexam_attempt($examfile->id, true)) {
            $filelink .= '<br />';
            $attemptname = $mkattempt->name .' ('.userdate($mkattempt->timecreated, get_string('strftimerecent')).') ';
            $url = new moodle_url('/mod/quiz/report.php', array('id' => $mkattempt->cm, 'mode' => 'makeexam', 'review' => $mkattempt->review, 'confirm' => 1));
            $icon = new pix_icon('icon', $strexamfile, 'quiz', array('class'=>'icon', 'title'=>$strexamfile));
            $filelink .= $OUTPUT->action_link($url,$attemptname, null, null, $icon);
        }
    }

    $description = html_writer::tag('h3', $examcoursename).html_writer::div($filelink, ' examreviewissuefilelink ').html_writer::div(implode('<br />', $items), ' examreviewissuebody ' );

    /// TODO use function tracker_submitanissue(&$tracker, &$data) TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO use function tracker_submitanissue(&$tracker, &$data) TODO

    $issue = new StdClass;
    $issue->datereported = time();
    $issue->summary = $summary;
    $issue->description = $description;
    $issue->descriptionformat = FORMAT_HTML;
    $issue->format = 1;
    $issue->assignedto = $tracker->defaultassignee;
    $issue->bywhomid = 0;
    $issue->trackerid = $tracker->id;
    $issue->status = 0;
    $issue->reportedby = $examfile->userid;
    $issue->usermodified = $issue->datereported;
    $issue->resolvermodified = $issue->datereported;
    $issue->userlastseen = 0;

    $issueid = $DB->insert_record('tracker_issue', $issue);
    if($issueid > 0) {
        if($DB->set_field('examregistrar_examfiles', 'reviewid', $issueid, array('id'=>$examfile->id))) {
            $eventdata = array();
            $eventdata['objectid'] = $examfile->id;
            list($course, $cm) = get_course_and_cm_from_instance($examregistrar, 'examregistrar', $examregistrar->course);
            $eventdata['context'] = context_module::instance($cm->id);
            $eventdata['other'] = array();
            $eventdata['other']['attempt'] = $examfile->attempt;
            $eventdata['other']['examid'] = $examfile->examid;
            $eventdata['other']['issueid'] = $issueid;
            $eventdata['other']['idnumber'] = $examfile->idnumber;
            $eventdata['other']['examregid'] = $examregistrar->id;
            $event = \mod_examregistrar\event\examfile_synced::create($eventdata);
            $event->trigger();
        }
    }
    return (int)$issueid;
}


/**
 * Synchronizes the status values of examfile entries that are associated with tracker issue
 * with the status values for those Tracker issues
 *
 * Status transfer in unidirectional Tracker -> Examfile, and only if examfile status is not set manually
 *
 * @param int $examstatus  examfile status field value
 * @param int $tracker1 Tracker issue status value
 * @param int $tracker2 Tracker issue status value
 * @param array $courses optional collection of course IDs to test in
 * @return bool true if the scale is used by the given examregistrar instance
 */
function examregistrar_examstatus_synchronize($examstatus, $tracker1, $tracker2, $courses=array()) {
    global $DB;

    $success = true;

    $coursejoin = '';
    $coursewhere = '';
    $courseparams = array();
    if($courses) {
        $coursejoin = ' JOIN {examregistrar_exams} e ON ef.examid = e.id ';
        list($insql, $courseparams) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'course_');
        $coursewhere = " AND e.courseid $insql ";
    }

    $sql = "SELECT ef.id, i.status
            FROM {examregistrar_examfiles} ef
            JOIN {tracker_issue} i ON ef.reviewid = i.id
            $coursejoin
            WHERE ef.status > 0 AND ef.status < :examstatus AND (i.status = :status1 OR i.status = :status2)
                 $coursewhere ";
    $params = array('examstatus'=>EXAM_STATUS_REJECTED);

    if($pass = $DB->get_records_sql_menu($sql, $params + array('status1'=>$tracker1, 'status2'=>$tracker2) + $courseparams )) {
        $chunks = array_chunk(array_keys($pass), 250);
        foreach($chunks as $chunk) {
            list($insql, $params) = $DB->get_in_or_equal($chunk);
            if(!$DB->set_field_select('examregistrar_examfiles', 'status', $examstatus, " id $insql ", $params)) {
                $success = false;
            }
        }
    }
/*
    if($success) {
        $eventdata = array();
        
        $eventdata['other'] = array();
        $eventdata['other']['action'] = 'Examfiles set status';
        $eventdata['other']['extra'] = $examstatus;
        $eventdata['other']['tab'] = 'review';
        $event = \mod_examregistrar\event\manage_action::create($eventdata);
        $event->trigger();
    }
*/    
    return $success;
}


/**
 * Locates the Tracker issue associated to an examregistrar instance
 *
 * @param object $examregistrar the examregistrar object
 * @param object $course the course object containing teh examregistrar instance
 * @return object tracker record
 **/
function examregistrar_get_review_tracker($examregistrar, $course) {
    global $DB;

    if(!$moduleid = $DB->get_field('modules', 'id', array('name'=>'tracker'))) {
        return false;
    }

    $params = array('course'=>$course->id, 'module'=>$moduleid, 'idnumber'=>$examregistrar->reviewmod);
    if(!$cms = $DB->get_records('course_modules', $params)) {
        $sql = "SELECT cm.*, c.category
                FROM {course_modules} cm
                JOIN {course} c  ON c.id = cm.course
                WHERE cm.module = :module AND cm.idnumber = :idnumber AND c.category = :category ";
        $params['category'] = $course->category;
        $cms = $DB->get_records_sql($sql, $params);
    }
    $cm = 0;
    if($cms) {
        $cm = reset($cms);
    }

    if(!$cm) {
        mtrace("... missing review Tracker instance for examregistrar {$examregistrar->name} ({$examregistrar->id})");
        return false;
    }
    /// OK, now we have the cm of a tracker instance
    return $DB->get_record('tracker', array('id' => $cm->instance));
}



/**
 * Add tracker review issues to orphan examfiles
 * Search if any examfiles of exams presented in an examregistrar programme
 * need an issue and creates it
 *
 * @param object $examregistrar the examregistrar object
 * @param object $course the course object containing teh examregistrar instance
 * @return object tracker record
 **/
function examregistrar_tracker_add_issues($examregistrar, $course = 0) {
    global $DB;

    $sql = "SELECT ef.*
            FROM {examregistrar_examfiles} ef
            JOIN {examregistrar_exams} e ON e.id = ef.examid
            WHERE e.programme = :programme AND ef.attempt > 0 AND ef.idnumber <> ''
                    AND ef.reviewid = 0 AND ef.timeapproved = 0 AND ef.timerejected = 0 ";

    if($examfiles = $DB->get_records_sql($sql, array('programme'=>$examregistrar->programme))) {
        if(!$course) {
            $course = $DB->get_record('course', array('id'=>$examregistrar->course));
        }
        if($course) {
            if($tracker = examregistrar_get_review_tracker($examregistrar, $course)) {
                foreach($examfiles as $examfile) {
                    $issueid = examregistrar_review_addissue($examregistrar, $course, $examfile, $tracker);
                }
            }
        }
    }
}


/**
 * delete tracker review issues orphan, without an examfile
 * Search if any examfiles of exams presented in an examregistrar programme
 * corresponds to the issueid, delete if not.
 *
 * @param object $examregistrar the examregistrar object
 * @param object $course the course object containing teh examregistrar instance
 * @return object tracker record
 **/
function examregistrar_tracker_delete_issues($examregistrar, $course = 0) {
    global $CFG, $DB;

    $tracker = '';
    if(!$course) {
        $course = $DB->get_record('course', array('id'=>$examregistrar->course));
    }
    if($course) {
        $tracker = examregistrar_get_review_tracker($examregistrar, $course);
    }
    
    if($tracker) {
        $sql = "SELECT ti.id, ti.summary, ef.idnumber  
                FROM {tracker_issue} ti 
                LEFT JOIN {examregistrar_examfiles} ef ON ef.reviewid = ti.id 
                WHERE ti.trackerid = :trackerid AND ti.status < 4 AND ef.idnumber IS NULL AND ef.examid IS NULL
                ";
        $params = array('trackerid' => $tracker->id);
        
        if($issues = $DB->get_records_sql($sql, $params)) {
            include_once($CFG->dirroot.'/mod/tracker/locallib.php');    
            $cm = get_coursemodule_from_instance('tracker', $tracker->id, $tracker->course, false, MUST_EXIST);
            $context = context_module::instance($cm->id);
            foreach($issues as $issue) {
                tracker_remove_issue($tracker, $issue->id, $context->id);
            }
        }
    }
}



/**
 * Perform synchronization with tracker issues for each exam 
 *
 * @param progress_trace $trace
 * @return void
 **/
function examregistrar_sync_tracker_issues(progress_trace $trace) {
    global $CFG, $DB;
    include_once($CFG->dirroot.'/mod/tracker/locallib.php');
    $success = examregistrar_examstatus_synchronize(EXAM_STATUS_APPROVED, TESTING, RESOLVED);
    $success = examregistrar_examstatus_synchronize(EXAM_STATUS_REJECTED, ABANDONNED, TRANSFERED);

    /// checking for examfiles without review instance
    $select = " workmode = :workmode AND (reviewmod <> '' AND reviewmod IS NOT NULL)
                    AND (programme <> 0 AND programme <> '' AND programme IS NOT NULL) ";
    if($registrars = $DB->get_records_select('examregistrar', $select, array('workmode'=>EXAMREGISTRAR_MODE_REVIEW))) {
        $trace->output('Starting Examregistrar - Tracker issues synchronisation...');
        foreach($registrars as $key => $registrar) {
            $trace->output("... adding missing review issues for examregistrar {$registrar->name} ({$registrar->id})");
            examregistrar_tracker_add_issues($registrar);
            examregistrar_tracker_delete_issues($registrar);
        }
    }
}



/**
 * Perform synchronization with tracker issues for each exam 
 *
 * @param progress_trace $trace
 * @return void
 **/
function examregistrar_update_session_seats_bookings(progress_trace $trace) {
    global $CFG, $DB;

    if($examregistrars = $DB->get_records('examregistrar', array('primaryreg'=>''))) {
        require_once($CFG->libdir .'/statslib.php');
        $today = stats_get_base_daily();    
        $trace->output('Starting Examregistrar booking allocation ');
    
        foreach($examregistrars as $examregistrar) {
            // allocate students in rooms for session
            $session = examregistrar_next_sessionid($examregistrar, $today, true);
            if($session && ($session->examdate > $today) && ($session->examdate < $today + 15*DAYSECS)) {
                $sql = "SELECT b.id, b.bookedsite
                            FROM {examregistrar_bookings} b
                            JOIN {examregistrar_exams} e ON e.id = b.examid
                            WHERE e.examsession = :session
                            GROUP BY b.bookedsite ";
                if($bookedsites = $DB->get_records_sql_menu($sql, array('session'=>$session->id))) {
                    foreach($bookedsites as $bookedsite) {
                        if(examregistrar_is_venue_single_room($bookedsite)) {
                            $trace->output('   ... allocating single venue bookings at site '.$bookedsite);
                            if(!$max = $DB->get_records('examregistrar_session_seats', array('examsession'=>$session->id, 'bookedsite'=>$bookedsite),
                                                            ' timemodified DESC ', '*', 0, 1)) {
                                examregistrar_session_seats_makeallocation($session->id, $bookedsite);
                            } else {
                                $lasttime = reset($max)->timecreated;
                                examregistrar_session_seats_newbookings($session->id, $bookedsite, $lasttime+1);
                            }
                        }
                    }
                }
            }
        }
    }
}























//////////////////////////////////////////////////////////////////////////////////
//   Session &  exams functions                                                //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns a menu of table records items, (id, name) for a tablefield referenced as idnumber
 *
 * @param object $examregistrar the examregistrar object
 * @param int $examregid the examregistrar ID
 * @param string $table element name & table
 * @param array $params additionals params for query table associative array (field, value)
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_get_referenced_examsmenu($examregistrar, $table, $params = array(), $exregid = 0, $any = '') {
    global $DB;

    if(!$exregid) {
        $exregid = examregistrar_get_primaryid($examregistrar);
    }

    $where = '';
    if($params) {
        foreach($params as $param => $value) {
            $where .= " AND t.$param = :$param ";
        }
    }


    if($table != 'exams') {
        $sql = "SELECT DISTINCT(e.id), e.programme, c.shortname, t.id AS tid
                FROM {examregistrar_$table} t
                JOIN {examregistrar_exams} e ON t.examid = e.id
                JOIN {course} c ON e.courseid = c.id
                WHERE 1 $where
                GROUP BY e.id ";
    } else {
        $sql = "SELECT t.id, t.programme, c.shortname
                FROM {examregistrar_$table} t
                JOIN {course} c ON t.courseid = c.id
                WHERE 1 $where ";

    }

    $sort = " ORDER BY programme ASC, shortname ASC ";

    $menu = array();
    if($any) {
        $menu[0] = get_string($any, 'examregistrar');
    }
    if($items = $DB->get_records_sql($sql.$sort, $params)) {
        foreach($items as $key => $exam) {
            $menu[$key] = $exam->programme.' '.$exam->shortname;
        }
    }
    return $menu;
}

/**
 * Returns a menu  (courseid, name) of exams table items
 *
 * @param object $examregistrar the examregistrar object
 * @param int $examregid the examregistrar ID
 * @param array $params additionals params for query table associative array (field, value)
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_get_courses_examsmenu($examregistrar,  $exregid = 0, $params = array(), $any = 'any', $field='shortname', $programme=true) {
    global $DB;

    if(!$exregid) {
        $exregid = examregistrar_get_primaryid($examregistrar);
    }

    $where = '';
    if($params) {
        foreach($params as $param => $value) {
            $prefix = '';
            if(strpos('.', $param) === false ) {
                $prefix = 'e.';
            }
            $where .= " AND $prefix"."$param = :$param ";
        }
    }

    if($examregistrar->programme) {
        $where .= " AND e.programme = :programme ";
        $params['programme'] = $examregistrar->programme;
        $programme = false;
    }

    $sql = "SELECT e.courseid, e.programme, c.shortname, c.fullname, c.idnumber
            FROM {examregistrar_exams} e
            JOIN {course} c ON e.courseid = c.id
            WHERE 1 $where
            GROUP BY e.courseid ";

    $sort = " ORDER BY e.programme ASC, c.shortname ASC ";

    $menu = array();
    if($any) {
        $menu[0] = get_string($any, 'examregistrar');
    }
    if($items = $DB->get_records_sql($sql.$sort, $params)) {
        foreach($items as $key => $exam) {
            $prefix = '';
            if($programme) {
                $prefix = $exam->programme.'-';
            }
            $menu[$key] = $prefix.$exam->$field;
        }
    }
    return $menu;
}


/**
 * Returns a menu of table records items, (id, name) for a tablefield referenced as idnumber
 *
 * @param object $examregistrar the examregistrar object
 * @param int $examregid the examregistrar ID
 * @param string $table element name & table
 * @param array $params additionals params for query table associative array (field, value)
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_get_referenced_roomsmenu($examregistrar, $table, $params = array(), $exregid = 0, $any = '') {
    global $DB;

    if(!$exregid) {
        $exregid = examregistrar_get_primaryid($examregistrar);
    }

    $where = '';
    if($params) {
        foreach($params as $param => $value) {
            $where .= " AND t.$param = :$param ";
        }
    }

    $sql = "SELECT DISTINCT(l.id), e.name, e.idnumber, t.id AS tid
            FROM {examregistrar_$table} t
            JOIN {examregistrar_locations} l ON t.roomid = l.id
            JOIN {examregistrar_elements} e ON l.examregid = e.examregid  AND  e.type = 'locationitem' AND l.location = e.id
            WHERE 1 $where
            GROUP BY l.id ";

    $sort = " ORDER BY name ASC ";

    $menu = array();
    if($any) {
        $menu[0] = get_string($any, 'examregistrar');
    }
    if($items = $DB->get_records_sql($sql.$sort, $params)) {
        foreach($items as $key => $room) {
            $menu[$key] = $room->name.' ('.$room->idnumber.')';
        }
    }
    return $menu;
}


/**
 * Gets a collection of rooms (with names) assigned to an exam session
 * optionally restricted for bookedsite and with allocation data
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @param string $sort  how to sort results, empty = roomname, others seats/booked/free
 * @param bool allocations include allocation occupancy on results
 * @param bool $visible availability of sesion room (should consider only those available, 1, not available, 0, or any: null)
 * @return array rooms data
 */
function examregistrar_get_session_rooms($sessionid, $bookedsite = 0, $sort = '',  $allocations=false, $visible = null) {
    global $DB;

    $sessionrooms = array();

    $params = array('examsession'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND sr.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }
    $visiblewhere = '';
    if(!is_null($visible)) {
        $visiblewhere = ' AND sr.available = :visible ';
        $params['visible'] = $visible;
    }
    $selectcount = ' 0 AS booked, r.seats AS freeseats ';
    $allocationjoin = '';
    if($allocations) {
        $selectcount = ' COUNT(DISTINCT ss.userid) AS booked, (r.seats - COUNT(DISTINCT ss.userid)) AS freeseats ';
        $allocationjoin = "LEFT JOIN {examregistrar_session_seats} ss ON sr.examsession = ss.examsession
                                                                            AND sr.bookedsite = ss.bookedsite AND sr.roomid = ss.roomid ";
    } elseif($sort) {
        $sort = 'seats';
    }
    $order = '';
    if($sort) {
        $order = " $sort "; // seats/booked/free
        if($sort == 'freeseats' || $rsort == 'seats') {
            $order .= ' DESC';
        }
        if(!$bookedsite) {
            $order = ' venueidnumber ASC, '.$order;
        }
        $order .= ', ';
    }

    $sql = "SELECT r.id, r.seats, sr.bookedsite, sr.examsession, sr.available, er.name AS name, er.idnumber AS idnumber,
                        ev.name AS venuename, ev.idnumber AS venueidnumber, $selectcount
            FROM {examregistrar_locations} r
            JOIN {examregistrar_session_rooms} sr ON sr.roomid = r.id
            JOIN {examregistrar_elements} er ON er.examregid = r.examregid AND r.location = er.id
            JOIN {examregistrar_locations} v ON sr.bookedsite = v.id AND v.visible = 1
            JOIN {examregistrar_elements} ev ON ev.examregid = v.examregid AND v.location = ev.id
            $allocationjoin
            WHERE sr.examsession = :examsession  AND r.visible = 1  $venuewhere $visiblewhere
            GROUP BY r.id
            ORDER BY $order name ASC ";

    $sessionrooms = $DB->get_records_sql($sql,$params);


    return $sessionrooms;
}


/**
 * Gets a collection of exams assigned to a room in this session
 * optionally restrited for bookedsite
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_get_sessionroom_exams($roomid, $sessionid, $bookedsite = 0) {
    global $DB;

    $exams = array();

    $params = array('roomid'=>$roomid, 'examsession'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND sr.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }

    $sql = "SELECT e.id, sr.roomid, ss.examid, e.programme, e.courseid, e.callnum, e.examsession, e.examscope, c.shortname, c.fullname, c.idnumber
            FROM {examregistrar_session_rooms} sr
            LEFT JOIN {examregistrar_session_seats} ss ON sr.roomid = ss.roomid AND sr.examsession = ss.examsession AND sr.bookedsite = ss.bookedsite
            LEFT JOIN {examregistrar_exams} e ON ss.examid = e.id AND e.visible = 1
            JOIN {course} c ON e.courseid = c.id
            WHERE sr.examsession = :examsession AND sr.roomid = :roomid $venuewhere
            GROUP BY ss.examid
            ORDER BY e.programme ASC, c.shortname ASC ";

    return $DB->get_records_sql($sql, $params);
}





/**
 * Gets a collection of users booked & allocated assigned to a venue in this session
 * optionally restrited for room
 *
 * @param int $session the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_get_session_venue_users($session, $bookedsite, $room = 0) {
    global $DB;

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);
    $roomwhere = '';
    if($room) {
        $roomwhere = ' AND ss.roomid = :room ';
        $params['room'] = $room;
    }

    // get data for usertable
    $userfieldsapi = \core_user\fields::for_name();
    $names = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
    $sql = "SELECT  b.id AS bid,  b.userid, b.examid, c.shortname, c.fullname,
                    ss.id as sid, ss.roomid, ss.seat, ss.showing, ss.taken, ss.certified, ss.status, 
                    u.username, u.idnumber, $names,
                    (SELECT COUNT(b2.examid)  FROM {examregistrar_bookings} b2
                                              JOIN {examregistrar_exams} e2 ON b2.examid = e2.id
                                                WHERE b2.userid = b.userid AND b2.bookedsite = b.bookedsite AND b2.booked = 1
                                                AND  e2.examsession = e.examsession
                                                GROUP BY b2.userid ) AS numexams
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id AND e.examsession = :examsession
            JOIN {user} u ON b.userid = u.id
            JOIN {course} c ON c.id = e.courseid
            LEFT JOIN {examregistrar_session_seats} ss ON  b.userid = ss.userid AND b.examid = ss.examid AND b.bookedsite = ss.bookedsite AND e.examsession = ss.examsession
            WHERE b.bookedsite = :bookedsite AND b.booked = 1 $roomwhere
            GROUP BY b.userid, b.examid
            ORDER BY u.lastname ASC, u.firstname ASC, u.idnumber ASC, c.shortname ASC";

    return $DB->get_records_sql($sql, $params);
}


/**
 * Looks for and get an instance of examregistrar in a course for an exam
 *
 * @param stdClass $exam object
 * @return mixed false if failed or int course module id for instance
 */
function examregistrar_get_course_instance($exam) {
    global $DB;
    $module = $DB->get_field('modules', 'id', array('name'=>'examregistrar'));

    $examregid = 0;
    if(isset($exam->examregid)) {
        $examregid = $exam->examregid;
    } elseif(isset($exam->examid)) {
        $examregid = $DB->get_field('examregistrar_exams', 'examregid', array('id'=>$exam->examid));
    } elseif(isset($exam->id)) {
        $examregid = $DB->get_field('examregistrar_exams', 'examregid', array('id'=>$exam->id));
    }
    $primary = $DB->get_field('examregistrar', 'primaryidnumber', array('id'=>$examregid));

    $sql = "SELECT e.id, cm.id as cmid
            FROM {course_modules} cm
            JOIN {examregistrar} e ON cm.instance = e.id AND cm.course = e.course
            JOIN {course_sections} cs ON cs.id = cm.section
            WHERE cm.module = :module AND cm.course = :course AND e.primaryreg = :primary
                    AND e.annuality = :annuality
            ORDER BY cs.section ASC ";
    $params = array('module'=>$module, 'primary'=>$primary, 'course'=>$exam->courseid, 'annuality'=>$exam->annuality);
    if($mods = $DB->get_records_sql_menu($sql, $params, 0, 1)) {
        return reset($mods);
    }
    return false;
}

/**
 * Gets a collection of exams assigned to an exam session
 * optionally restricted for bookedsite and with allocation data
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @param string $sort  how to sort results, empty = shortname, others fullname/booked
 * @param bool bookingss include booking data on results
 * @param bool allocations include allocation occupancy on results
 * @param bool withdelivery include delivery data, if a mode (quiz, assign) specified, only those are retrieved
 * @param bool $onlyspecial if true, only exams of special extra turns are returned
 * @return array rooms data
 */
function examregistrar_get_session_exams($sessionid, $bookedsite = 0, $sort = '', $bookings = false, $allocations=false, $withdelivery= false, $onlyspecial=false) {
    global $DB;

    $params = array('examsession'=>$sessionid);

    $order = '';
    if($sort) {
        $order = " $sort ";
        if($sort == 'booked' || $sort == 'allocated') {
            $order .= ' DESC';
        }
        $order .= ', ';
    }
    $order .= ' e.programme ASC, c.shortname  ASC ';

    $countbookings = '';
    if($bookings) {
        $venuewhere1 = '';
        if($bookedsite) {
            $venuewhere1 = ' AND b.bookedsite = :bookedsite1 ';
            $params['bookedsite1'] = $bookedsite;
        }
        $countbookings = ", (SELECT COUNT(b.userid)
                            FROM {examregistrar_bookings} b
                            WHERE b.examid = e.id AND b.booked = 1 $venuewhere1
                            GROUP BY b.examid
                            ) AS booked ";

    }

    $countallocated = '';
    $joinallocated = '';
    if($allocations) {
        $countallocated = ', COUNT(ss.userid) AS allocated ';
        $venuewhere2 = '';
        if($bookedsite) {
            $venuewhere2 = ' AND ss.bookedsite = :bookedsite2 ';
            $params['bookedsite2'] = $bookedsite;
        }
        $joinallocated = "LEFT JOIN {examregistrar_session_seats} ss ON ss.examid = e.id AND ss.roomid > 0  $venuewhere2 ";

    }

    $groupby = 'e.id';
    
    $joindelivery = '';
    $deliveryfields = '';
    $deliverygroup = '';
    if($withdelivery) {    
        $joindelivery = "JOIN {examregistrar_examdelivery} ed ON ed.examid = e.id ";
        if(in_array($withdelivery, ['quiz','assign', 'offlinequiz'], true)) {
            $joindelivery .=  ' AND ed.helpermod = :helpermod ';
            $params['helpermod'] = $withdelivery; 
        } else {
            $joindelivery = 'LEFT '.$joindelivery;
        }
        $deliveryfields = 'ed.id AS deliveryid, ed.helpermod, ed.helpercmid, ed.timeopen, ed.timeclose, ed.timelimit, 
                            ed.status, ed.parameters, ed.component, ed.bookedsite AS deliverysite, ';
        $groupby = 'ed.id';
        if($bookedsite) {
            $joindelivery .= ' AND ed.bookedsite = :deliverysite ';
            $params['deliverysite'] = $bookedsite;
        } else {
            $joindelivery .= ' AND ed.bookedsite > 0';
        }
        
    }
    
    $specialwhere = '';
    if($onlyspecial) {
        $specialwhere = ' AND e.callnum < 0 ';
    }

    $sql = "SELECT $deliveryfields e.id, e.id AS examid, e.programme, e.courseid, e.callnum, e.examsession, e.examscope, 
                    e.assignplugincm, e.quizplugincm, 
                    c.shortname, c.fullname, c.idnumber $countallocated  $countbookings
            FROM {examregistrar_exams} e
            JOIN {course} c ON e.courseid = c.id
            $joinallocated
            $joindelivery
            WHERE e.examsession = :examsession AND e.visible = 1 $specialwhere
            GROUP BY $groupby 
            ORDER BY $order ";

    return $DB->get_records_sql($sql, $params);
}

/**
 * Checks if any exam has a quiz associated via quizqplugincm
 *
 * @param array $sessionexams array of exams id 
 * @return bool
 */
function examregistrar_exams_have_quizzes($sessionexams) {
    global $DB;
    
    if(!$sessionexams) {
        return false;
    }
    
    list($insql, $params) = $DB->get_in_or_equal($sessionexams, SQL_PARAMS_NAMED, 'eid');
    $select = "id $insql AND helpermod = 'quiz' ";
    
    return $DB->record_exists_select('examregistrar_examdelivery', $select, $params);
}



/**
 * Gets a collection of rooms assigned to an exam in this session
 * optionally restrited for bookedsite
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_get_sessionexam_rooms($examid, $sessionid, $bookedsite = 0) {
    global $DB;

    $exams = array();

    $params = array('examid'=>$examid, 'examsession'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND sr.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }

    $order = '';
    if(!$bookedsite) {
        $order = ' venueidnumber ASC, '.$order;
    }

    $sql = "SELECT sr.roomid, ss.examid, e.name AS name, e.idnumber AS idnumber,
                                         ev.name AS venuename, ev.idnumber AS venueidnumber
            FROM {examregistrar_session_rooms} sr
            JOIN {examregistrar_locations} l ON sr.roomid = l.id
            JOIN {examregistrar_elements} e ON l.examregid = e.examregid AND e.type = 'locationitem' AND l.location = e.id
            JOIN {examregistrar_locations} v ON sr.bookedsite = v.id
            JOIN {examregistrar_elements} ev ON v.examregid = ev.examregid AND ev.type = 'locationitem' AND v.location = ev.id
            LEFT JOIN {examregistrar_session_seats} ss ON sr.roomid = ss.roomid AND sr.examsession = ss.examsession AND sr.bookedsite = ss.bookedsite
            WHERE sr.examsession = :examsession AND ss.examid = :examid $venuewhere
            GROUP BY sr.roomid
            ORDER BY $order e.name ASC ";

    return $DB->get_records_sql($sql, $params);
}

/**
 * Counts total number of exam bookings and total seated
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_qc_counts($sessionid, $bookedsite = 0) {
    global $DB;

    $params = array('examsession'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND b.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }

    $sql = "SELECT COUNT(b.id)
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON e.id = b.examid AND e.examsession = :examsession AND e.visible = 1
                WHERE b.booked = 1 $venuewhere ";
    $totalbooked = $DB->count_records_sql($sql, $params);

    if($bookedsite) {
        $venuewhere = ' AND bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }
    $select = " examsession = :examsession AND roomid > 0 $venuewhere ";
    $totalseated = $DB->count_records_select('examregistrar_session_seats', $select, $params);

    return array($totalbooked, $totalseated);
}

/**
 * Gets a collection of bookings not allocated in a session
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_booking_seating_qc($sessionid, $bookedsite = 0, $sort='') {
    global $DB;

    $params = array('session1'=>$sessionid, 'session2'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND b.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }

    $order = 'u.lastname ASC, ';
    if($sort) {
        $order .= " $sort ";
        if($sort == 'booked' || $sort == 'allocated') {
            $order .= ' DESC';
        }
        $order .= ', ';
    }
    $order .= ' e.programme ASC, c.shortname  ASC ';

    $userfieldsapi = \core_user\fields::for_name();
    $names = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
    $sql = "SELECT b.id, b.examid, b.userid, b.bookedsite, e.examsession, e.programme, e.callnum, c.shortname, c.fullname,
                u.idnumber, $names
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON e.id = b.examid AND e.examsession = :session1 AND e.visible = 1
            JOIN {course} c ON c.id = e.courseid
            JOIN {user} u ON b.userid = u.id
            LEFT JOIN {examregistrar_session_seats} ss ON (ss.examsession = e.examsession AND ss.examid = b.examid
                                                            AND ss.userid = b.userid AND ss.bookedsite = b.bookedsite AND ss.roomid > 0)
            WHERE b.booked = 1 AND e.examsession = :session2 $venuewhere
                    AND ss.id IS NULL
            ORDER BY $order";

    return $DB->get_records_sql($sql, $params);
}

/**
 * Searches Locations for children of a venue (and venue itself) with given type & seats
 *
 * @param int/object $venue the ID for room in Locations table or full record
 * @param int $type the locationtype for the desired rooms
 * @param int $seats minimum number of setas in the returned locations
 * @param int $returnids whether returning full objects of just IDs
 * @return array locations
 */
function examregistrar_get_venue_locations($venue, $type = '', $seats = -1, $returnids=false) {
    global $DB;

    if(!$venue) {
        return false;
    }

    if(is_numeric($venue)) {
        $path = $DB->get_field('examregistrar_locations', 'path', array('id'=>$venue));
        $venueid = $venue;
    } else {
        $path = $venue->path;
        $venueid = $venue->id;
    }

    $likepath = $DB->sql_like('path', ':path');
    $params['path'] = $path.'/%';
    $params['venue'] = $venueid;
    $select = " (id = :venue  OR $likepath ) ";

    if($seats) {
        $select .= ' AND seats >= :seats ';
        $params['seats'] = $seats;
    } else {
        $select .= ' AND seats = 0 ';
    }

    if($type) {
        $select .= ' AND locationtype = :type ';
        $params['type'] = $type;
    }

    $return = '';
    if($returnids) {
        return $DB->get_records_select_menu('examregistrar_locations', $select, $params, '', 'id, id AS ids');
    }
    return $DB->get_records_select('examregistrar_locations', $select, $params);
}

/**
 * Checks if venue has only one room
 *
 * @param int/object $venue the ID for room in Locations table or full record
 * @param int $returnids whether returning full objects of just IDs
 * @return mixed bool/int/object
 */
function examregistrar_is_venue_single_room($venue, $returnids=true) {
    $room = false;
    if($rooms = examregistrar_get_venue_locations($venue, '', -1, $returnids)) {
        if(count($rooms) === 1) {
            $room = reset($rooms);
        }
    } elseif(!empty($venue)) {
        \core\notification::error(get_string('venueerror', 'examregistrar'));
    }
    
    return $room;
}



/**
 * Returns list of venues the user has a room allocation in as staffer
 *
 * @param object $examregistrar
 * @param int $userid
 * @param int $session the session to check room allocation
 * @return array location IDs
 */
function examregistrar_get_user_venues($examregistrar, $userid, $session=0) {
    $venues = array();
    $venueelement = examregistrar_get_venue_element($examregistrar);
    // check assignation as staffer in venue level
    if($rooms = examregistrar_get_user_rooms($examregistrar, $userid, $venueelement)) {
        foreach($rooms as $room) {
            $venues[$room->id] = $room->id;
        }
    }
    // now other rooms
    if($rooms = examregistrar_get_user_rooms($examregistrar, $userid, 0, $session)) {
        foreach($rooms as $room) {
            if($venueid = examregistrar_get_room_venue($room, $venueelement)) {
                $venues[$venueid] = $venueid;
            }
        }
    }

    return $venues;
}



/**
 * Returns first location of the given type that is an ancestor or given room
 * Searches Location path for suitable venues
 *
 * @param int/object $roomid the ID for room in Locations table or full record
 * @param int venue type
 * @param boolean $returnid return full object or justs id
 * @return mixed int roomid/false
 */
function examregistrar_get_room_venue($roomid, $venuetype, $returnid=true) {
    global $DB;

    $venue = false;

    if(is_int($roomid)) {
        $path = $DB->get_field('examregistrar_locations', 'path', array('id'=>$roomid));
    } else {
        $path = $roomid->path;
    }

    if($paths = explode('/', $path)) {
        array_shift($paths);
        $parents = $DB->get_records_list('examregistrar_locations', 'id', $paths);
        foreach($paths as $pid) {
            $parent = $parents[$pid];
            if($parent->locationtype == $venuetype) {
                $venue = $parent;
                break;
            }
        }
        if($venue && $returnid) {
            $venue = $venue->id;
        }
    }
    return $venue;
}


/**
 * Returns menu of suitable room parents
 * Searches Locations for other locations that can serve as parent for a room (exlude its children & ancestors)
 *
 * @param object $examregistrar the examregistrar object
 * @param int $roomid the ID for room in Locations table
 * @param string $fields flag to set return format. 'name': menu id/roomname; 'ids': just rooms ids; other: rooms objects
 * @param boolean $choose if a choose items is first in menu
 * @return bool success
 */
function examregistrar_get_potential_parents($examregistrar, $roomid = 0, $fields = 'name', $choose = false) {
    global $DB;
    // potential parents are venues and seats=0 locations that are not children of this one

    $venueelement = examregistrar_get_venue_element($examregistrar);

    $select = '( locationtype = :venuetype  OR seats = 0 ) ';
    $params['venuetype'] = $venueelement;
    if($roomid > 0) {
        $select .= ' AND '.$DB->sql_like('path', ':path', false, false, true);
        $params['path'] = "%/$roomid/%";
    }

    if($fields == 'name') {
        $sql = "SELECT l.id, CONCAT(el.name,' (',el.idnumber,')') AS itemname
                FROM {examregistrar_locations} l
                JOIN {examregistrar_elements} el ON el.examregid = l.examregid AND el.type = 'locationitem' AND l.location = el.id
                WHERE $select
                ORDER BY l.parent ASC, itemname ASC ";
        $parents = $DB->get_records_sql_menu($sql, $params);
        if($choose) {
            $parents = array('0' => get_string('choose')) + $parents;
        }
        return $parents;
    } elseif($fields == 'ids') {
        return $DB->get_records_select_menu('examregistrar_locations', $select, $params, 'id ASC', 'id, id');
    }
    return $DB->get_records_select('examregistrar_locations', $select, $params, 'id ASC', $fields);
}


/**
 * Recall a room for a an exam session
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return bool success
 */
function examregistrar_addupdate_sessionroom($sessionid, $roomid, $bookedsite, $visible = null) {
    global $DB, $USER;
    if(!$bookedsite) {
        throw new moodle_exception('missingbookedsite', 'examregistrar');
    }
    $params = array('examsession'=>$sessionid, 'roomid'=>$roomid);
    if($record = $DB->get_record('examregistrar_session_rooms', $params)){
        $record->bookedsite = $bookedsite;
        $record->available = 1;
        $success = $DB->update_record('examregistrar_session_rooms', $record);
    } else {
        $record = new stdClass;
        $record->examsession = $sessionid;
        $record->bookedsite = $bookedsite;
        $record->roomid = $roomid;
        if(isset($visible)) {
            $record->available = $visible;
        }
        $success = $DB->insert_record('examregistrar_session_rooms', $record);
    }
    return $success;
}


/**
 * Releases a room from an exam session
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return bool success
 */
function examregistrar_remove_sessionroom($sessionid, $roomid, $visible = null) {
    global $DB, $USER;

    $success = false;
    $params = array('examsession'=>$sessionid, 'roomid'=>$roomid);
    if(isset($visible)) {
        $params['available'] = $visible;
    }
    if($record = $DB->get_record('examregistrar_session_rooms', $params)){
        $record->available = 0;
        $success = $DB->update_record('examregistrar_session_rooms', $record);
    }
    return $success;
}


/**
 * Adds (or updates if already existing) a user as staff in a room
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return bool success
 */
function examregistrar_addupdate_roomstaffer($sessionid, $roomid, $userid, $role, $info='', $visible = null) {
    global $DB, $USER;

    $success = false;
    $params = array('examsession'=>$sessionid, 'locationid'=>$roomid, 'userid'=>$userid, 'role'=>$role);
    if($record = $DB->get_record('examregistrar_staffers', $params)){
        if(isset($visible)) {
            $record->visible = $visible;
        }
        $record->modifierid = $USER->id;
        $record->timemodified = time();
        if($info) {
            $record->info = $info;
        }
        $success = $DB->update_record('examregistrar_staffers', $record);
    } else {
        $record = new stdClass;
        $record->examsession = $sessionid;
        $record->locationid = $roomid;
        $record->userid = $userid;
        $record->role = $role;
        if($info) {
            $record->info = $info;
        }
        if(isset($visible)) {
            $record->visible = $visible;
        }
        $record->modifierid = $USER->id;
        $record->timemodified = time();
        $success = $DB->insert_record('examregistrar_staffers', $record);
    }
    return $success;
}

/**
 * Returns a list of users that are assigned as staff in a room
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return bool success
 */
function examregistrar_remove_roomstaffers($sessionid, $roomid, $userid=0, $role='', $visible = null) {
    global $DB, $USER;

    $success = false;
    $params = array('examsession'=>$sessionid, 'locationid'=>$roomid);
    if($userid) {
        $params['userid'] = $userid;
    }
    if($role) {
        $params['role'] = $role;
    }
    if(isset($visible)) {
        $params['visible'] = $visible;
    }
    if($records = $DB->get_records('examregistrar_staffers', $params)){
        foreach($records as $record) {
        $record->visible = 0;
        $record->modifierid = $USER->id;
        $record->timemodified = time();
        $success = $DB->update_record('examregistrar_staffers', $record);
        }
    }
    return $success;
}



//////////////////////////////////////////////////////////////////////////////////
//   Booking functions                                                         //
////////////////////////////////////////////////////////////////////////////////


/**
 * Checks booked exams and make unique bookings, holds ALL users that booked, but only once each
 *
 * @param int $examregprimaryid 
 * @param int $bookingid the locationID for the booking
 * @param int $now timestamp
 * @return stadclass voucher object
 */
function examregistrar_set_booking_voucher($examregprimaryid, $bookingid, $now) {
    global $DB;
    $voucher = new stdClass();
    $voucher->examregid = $examregprimaryid;
    $voucher->bookingid = $bookingid;
    $voucher->uniqueid = strtoupper(base_convert(bin2hex(random_bytes_emulate(10)), 16, 36));
    $voucher->timemodified = $now;
    do {
        $voucher->uniqueid = strtoupper(base_convert(bin2hex(random_bytes(10)), 16, 36));
    } while ($DB->record_exists('examregistrar_vouchers', array('examregid'=>$examregprimaryid, 'uniqueid' => $voucher->uniqueid)));
    $voucher->id = $DB->insert_record('examregistrar_vouchers', $voucher);

    return $voucher;
}


/**
 * Checks booked exams and make unique bookings, holds ALL users that booked, but only once each
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $timelimit check only bookings mae after this datetime
 * @return array of uniquebookings, userid are uniques
 */
function examregistrar_get_unique_bookings($session, $bookedsite, $timelimit = 0) {
    global $DB;

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);
    $timewhere = '';
    if($timelimit) {
        $timewhere = ' AND b.timemodified > :timelimit ';
        $params['timelimit'] = $timelimit;
    }

    $sql = "SELECT b.*,
                (SELECT COUNT(userid)
                    FROM  {examregistrar_bookings} b2
                    WHERE b2.examid = b.examid AND b2.bookedsite = b.bookedsite AND b2.booked = 1
                    GROUP by b2.examid ) AS partners
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id
            WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite AND b.booked = 1 $timewhere
            ORDER BY b.userid ASC, partners ASC
            " ;

    $bookings = $DB->get_records_sql($sql, $params);

    $uniquebookings = array();
    foreach($bookings as $booking) {
        if(!isset($uniquebookings[$booking->userid])) {
            $uniquebookings[$booking->userid] = $booking;
        }
    }

    return $uniquebookings;
}


/**
 * Checks booked exams and returns bookings for users with several bookings
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $timelimit check only bookings mae after this datetime
 * @return array of bookings,
 */
function examregistrar_get_additional_bookings($session, $bookedsite, $timelimit = 0) {
    global $DB;

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);
    $timewhere = '';
    if($timelimit) {
        $timewhere = ' AND b.timemodified > :timelimit ';
        $params['timelimit'] = $timelimit;
    }

    $sql = "SELECT b.*, (SELECT  COUNT(b2.id)
                            FROM {examregistrar_bookings} b2
                            JOIN {examregistrar_exams} e2 ON b2.examid = e2.id
                            WHERE b2.bookedsite = b.bookedsite AND b2.userid = b.userid AND e2.examsession = e.examsession AND b2.booked = 1
                            GROUP BY b2.userid) AS numexams
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id
            WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite  AND b.booked = 1
            HAVING numexams > 1";

    $bookings = $DB->get_records_sql($sql, $params);

    return $bookings;
}


/**
 * Checks booked exams and create/update data in session_seats allocation table
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 */
function examregistrar_session_seats_makeallocation($session, $bookedsite) {
    global $DB, $USER;

    /// now update database, table session_seats
    /// delete non booked
    $sql = "SELECT id, userid
            FROM {examregistrar_session_seats} ss
            WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite
                AND NOT EXISTS (SELECT 1
                                FROM {examregistrar_bookings} b
                                JOIN {examregistrar_exams} e ON b.examid = e.id
                                WHERE e.examsession = ss.examsession AND b.bookedsite = ss.bookedsite AND b.userid = ss.userid AND b.booked = 1)
            ";
    if($notbooked = $DB->get_records_sql_menu($sql,  array('examsession'=>$session, 'bookedsite'=>$bookedsite))) {
        if($chunks = array_chunk($notbooked, 500)) {
            foreach($chunks as $notbooked) {
                $DB->delete_records_list('examregistrar_session_seats', 'userid', $notbooked);
            }
        }
    }

    /// delete all additionals
    $select = " examsession = :examsession AND bookedsite = :bookedsite AND additional > 0 ";
    $DB->delete_records_select('examregistrar_session_seats', $select, array('examsession'=>$session, 'bookedsite'=>$bookedsite));


    // first check for single room venue
    if($roomid = examregistrar_is_venue_single_room($bookedsite)) {
        // single room venues do not user unique/additional, all are additionals
        $sql = "SELECT b.id, b.userid, b.examid, b.booked, b.bookedsite
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON b.examid = e.id
                WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite AND b.booked = 1 ";
        if($bookings = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite))) {
            $now = time();
            $record = new stdclass;
            $record->examsession = $session;
            $record->bookedsite = $bookedsite;
            $record->additional = 0;
            $record->roomid = $roomid;
            $record->timecreated = $now;
            $record->timemodified = $now;
            $record->component =  '';
            $record->modifierid =  $USER->id;
            $record->reviewerid = 0;

            foreach($bookings as $booking) {
                $record->examid = $booking->examid;
                $record->userid = $booking->userid;
                $record->additional = $booking->examid;
                $DB->insert_record('examregistrar_session_seats', $record);
            }
        }

        return true;
    } else {
        // this is not a single room venue then REMOVE any room assignation to this venue as roomid
        $DB->delete_records('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'roomid'=>$bookedsite));
    }

/// We are here ONLY if bookedsite is not single room


    // now we process case for venues with multiple rooms
    /// get bookings
    $uniquebookings = examregistrar_get_unique_bookings($session, $bookedsite);
    $additionalbookings = examregistrar_get_additional_bookings($session, $bookedsite);

    /// compare new unique bookings with old allocations
    $deleting = array();
    if($rs = $DB->get_recordset('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite))) {
        foreach ($rs as $old) {
            if(isset($uniquebookings[$old->userid])) {
                $new = $uniquebookings[$old->userid];
                // only check this, additional is certain to be 0, deleted any others
                if($old->examid != $new->examid) {
                    $deleting[] = $old->id;
                } else {
                    unset($uniquebookings[$old->userid]);
                }
            } else {
                $deleting[] = $old->id;
            }

        }
        $rs->close();
    }
    if($deleting) {
        if($chunks = array_chunk($deleting, 500)) {
            foreach($chunks as $deleting) {
                $DB->delete_records_list('examregistrar_session_seats', 'id', $deleting);
            }
        }
    }

    /// if remain some $uniquebookings elements, there are new bookings
    $now = time();
    $record = new stdclass;
    $record->examsession = $session;
    $record->bookedsite = $bookedsite;
    $record->additional = 0;
    $record->roomid = 0;
    $record->timecreated = $now;
    $record->timemodified = $now;
    $record->component =  '';
    $record->modifierid =  $USER->id;
    $record->reviewerid = 0;

    if($uniquebookings) {
        foreach($uniquebookings as $booking) {
            $record->examid = $booking->examid;
            $record->userid = $booking->userid;
            $DB->insert_record('examregistrar_session_seats', $record);
        }
    }

    /// now process multiple bookings
    if($additionalbookings) {
        $users = array();
        foreach($additionalbookings as $booking) {
            if(isset($users[$booking->userid])) {
            $users[$booking->userid][$booking->examid] = $booking->examid;
            } else {
                $users[$booking->userid] = array($booking->examid=>$booking->examid);
            }
        }
        //print_object($users);
        //print_object("    users -----_");
        foreach($users as $userid => $exams) {
            if($mainalloc = $DB->get_record('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'userid'=>$userid, 'additional'=>0))) {
                $room = $mainalloc->roomid;
                unset($exams[$mainalloc->examid]);
            } else {
                $room = 0;
                $exam = array_shift($exams);
                $record->userid = $userid;
                $record->examid = $exam;
                $record->roomid = $room;
                $record->additional = 0;
                $DB->insert_record('examregistrar_session_seats', $record);
            }
            //print_object($exams);
            //print_object("  for user= $userid   exams");
            foreach($exams as $examid) {
                $record->userid = $userid;
                $record->examid = $examid;
                $record->roomid = $room;
                $record->additional = $examid;
                $DB->insert_record('examregistrar_session_seats', $record);
            }
        }
    }
}


/**
 * Checks booked exams and create/update data in session_seats allocation table
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $timelimit check only bookings mae after this datetime
 * @return array
 */
function examregistrar_session_seats_newbookings($session, $bookedsite, $timelimit) {
    global $DB, $USER;

    // check single room venue
    $singleroomid = examregistrar_is_venue_single_room($bookedsite);

    /// get dropped bookings
    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'timelimit'=> $timelimit);
    $sql = "SELECT b.id, b.userid, b.examid, b.booked
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id
            WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite AND b.timemodified > :timelimit AND b.booked = 0
                AND NOT EXISTS (SELECT 1
                                FROM {examregistrar_bookings} b2
                                WHERE b.bookedsite = b2.bookedsite AND b.userid = b2.userid AND b.examid = b2.examid AND b2.booked = 1)
             " ;
    if($droppedbookings = $DB->get_records_sql($sql, $params)) {

    //print_object($droppedbookings);
    //print_object(" -----  droppedbookings -------$timelimit----");
        $deleting = array();
        $refreshing = array();
        foreach($droppedbookings as $booking) {
            if($allocation = $DB->get_record('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                                                                  'userid'=>$booking->userid, 'examid'=>$booking->examid))) {
                $deleting[] = $allocation->id;
                if(!$allocation->additional) {
                    // anycase update additionals, after deleting all;
                    $refreshing[$allocation->userid] = $allocation->roomid;
                }
            }
        }
        if($deleting) {
            if($chunks = array_chunk($deleting, 500)) {
                foreach($chunks as $deleting) {
                    $DB->delete_records_list('examregistrar_session_seats', 'id', $deleting);
                }
            }
        }
        if($refreshing && !$singleroomid) {
            foreach($refreshing as $userid => $room) {
                examregistrar_update_additional_allocations($session, $bookedsite, $userid, $room, $timelimit);
            }
        }
    }

    /// get new positive bookings
    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'timelimit'=> $timelimit);
    $sql = "SELECT b.id, b.userid, b.examid, b.booked
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id
            WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite AND b.timemodified > :timelimit AND b.booked = 1
             " ;
    if($newbookings = $DB->get_records_sql($sql, $params)) {
    //print_object($newbookings);
    //print_object(" -----  newedbookings ------$timelimit-----");
        $now = time();
        $record = new stdclass;
        $record->examsession = $session;
        $record->bookedsite = $bookedsite;
        $record->additional = 0;
        $record->roomid = 0;
        $record->timecreated = $now;
        $record->timemodified = $now;
        $record->component =  '';
        $record->modifierid =  $USER->id;
        $record->reviewerid = 0;
        
        $refreshing = array();
        foreach($newbookings as $booking) {
            $room = 0;
            if($singleroomid) {
                $room = $singleroomid;
            } else {
                // add this as additional
                if($mainalloc = $DB->get_record('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'userid'=>$booking->userid, 'additional'=>0))) {
                    $room = $mainalloc->roomid;
                } else {
                    // add this as main exam
                    $room = 0;
                }
            }
            $record->roomid = $room;
            $record->userid = $booking->userid;
            // if records exists do not add again, error by duplicated in database
//                         if(!$DB->record_exists('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite,
//                                                                                   'userid'=>$booking->userid, 'examid'=>$booking->examid))) {
            if($seating = $DB->get_record('examregistrar_session_seats', array('userid'=>$booking->userid,'examid'=>$booking->examid))) {
                $seating->bookedsite = $record->bookedsite;
                $seating->roomid = $record->roomid;
                $seating->timemodified = $record->timemodified;
                $seating->component =  $record->component;
                $seating->modifierid =  $record->modifierid;
                $DB->update_record('examregistrar_session_seats', $seating);
            } else {
                $record->examid = $booking->examid;
                $record->additional = $booking->examid;
                $DB->insert_record('examregistrar_session_seats', $record);
            }
            // anycase update additionals, after adding all;
            if(!$singleroomid) {
                $refreshing[$record->userid] = $record->roomid;
            }
        }
        if($refreshing && !$singleroomid) {
            foreach($refreshing as $userid => $room) {
                examregistrar_update_additional_allocations($session, $bookedsite, $userid, $room, $timelimit);
            }
        }
    }
}


/**
 * Updates room allocation for selected users in session
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param array $params an associative array of additional search params suitable for get_records_menu
 * @param int $newroom check only bookings mae after this datetime
 * @param string $sort sortig for these users
 * @param int $limitfrom return a subset of records, starting at this point (optional).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return void
 */
function examregistrar_update_usersallocations($session, $bookedsite, $search, $newroom, $sort='', $limitfrom=0, $limitnum=0) {
    global $DB, $USER;

    $success = false;
    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);

    if(!$sort) {
        $sort = ' id ASC ';
    }

    if($users = $DB->get_records_menu('examregistrar_session_seats', $params + $search, $sort, 'id, userid', $limitfrom, $limitnum)) {
        $chunks = array_chunk($users, 500, true);
        foreach($chunks as $users) {
            list($insql, $inparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
            $select = "examsession = :examsession AND bookedsite = :bookedsite AND userid $insql ";
            if($DB->set_field_select('examregistrar_session_seats', 'roomid', $newroom, $select, $params + $inparams)) {
                $now = time();
                $DB->set_field_select('examregistrar_session_seats', 'timemodified', $now, $select, $params + $inparams);
                $DB->set_field_select('examregistrar_session_seats', 'modifierid', $USER->id, $select, $params + $inparams);
                $success = true;
            }
        }
    }

    if($newroom) {
        //$select = "   room = x AND additional > 0       "
        //$extrausers = get_fieldset_select('examregistrar_session_seats', 'userid', $select, array $params=null)
        //array_unique($extrausers)
        $sql = "SELECT  userid, COUNT(examid) AS numexams
                FROM {examregistrar_session_seats}
                WHERE examsession = :examsession AND bookedsite = :bookedsite  AND roomid = :roomid
                GROUP BY userid
                HAVING numexams > 1
                ORDER BY numexams DESC  ";
        if($extrausers = $DB->get_records_sql_menu($sql, $params + array('roomid'=>$newroom))){
            foreach($extrausers as $userid => $numexams) {
                examregistrar_update_additional_allocations($session, $bookedsite, $userid, $newroom);
            }
        }
    }
    return $success;
}


/**
 * Updates assignation of main/additional exam for user in a room
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $userid the user whose allocation is updated
 * @param int $roomid the room to check
 * @param int $timelimit used only when checking new bookings
 * @return void
 */
function examregistrar_update_additional_allocations($session, $bookedsite, $userid, $roomid, $timelimit = 0) {
    global $DB;

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);

    $sql = "SELECT ss.*,  (SELECT COUNT(p.userid)
                            FROM {examregistrar_session_seats} p
                            WHERE p.examsession = ss.examsession AND p.bookedsite = ss.bookedsite AND p.examid = ss.examid
                                    AND p.roomid = ss.roomid AND  p.userid <> ss.userid
                            GROUP BY p.examid) as partners
            FROM {examregistrar_session_seats} ss
            WHERE  ss.examsession = :examsession AND ss.bookedsite = :bookedsite  AND ss.roomid = :roomid AND ss.userid = :userid

            ORDER BY partners DESC  ";
    if($exams = $DB->get_records_sql($sql, $params + array('roomid'=>$roomid, 'userid'=>$userid))) {
        //print_object($exams);
        //print_object(" ---- extra exams for user= $userid, room= $roomid  ");
        $exam = reset($exams);
        if($exam->additional != 0) {
            // if first has additional != 0, there has been some reordering, then we need to update
            $main = clone $exam;
            foreach($exams as $exam) {
                $exam->additional = $exam->examid;
                $DB->update_record('examregistrar_session_seats', $exam);
            }
            if($timelimit) {
                $main->timecreated = $timelimit;
            }
            $main->additional = 0;
            $DB->update_record('examregistrar_session_seats', $main);
            //$DB->set_field('examregistrar_session_seats', 'additional', 0, array('id'=>$exam->id));
        }
    }
}


/**
 * Updates assignation of main/additional exam for user in a room
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $userid the user whose allocation is updated
 * @param int $roomid the room to check
 * @param int $timelimit used only when checking new bookings
 * @return string
 */
function examregistrar_verify_voucher($cmid, $vouchernum, $crccode, $canmanage) {
    global $DB, $OUTPUT, $USER;
    
    $output = '';
    list($rid, $uniqueid) = explode('-', $vouchernum);
    if(!$voucher = $DB->get_record('examregistrar_vouchers', array('examregid' => $rid, 'uniqueid' => $uniqueid))) {
        return $OUTPUT->box($OUTPUT->error_text(get_string('error_novoucher', 'examregistrar')), 'alert alert-danger');
    }
    if(!$booking = $DB->get_record('examregistrar_bookings', array('id' => $voucher->bookingid))) {
        return $OUTPUT->box($OUTPUT->error_text(get_string('error_nobooking', 'examregistrar')), 'alert alert-danger');
    }
    // Privacy, do not show booking data to non allowed users
    if(($USER->id != $booking->userid) && !$canmanage) {
        return $OUTPUT->box($OUTPUT->error_text(get_string('error_voucheruser', 'examregistrar')), 'alert alert-danger');
    }
    $newcrccode = crc32("{$voucher->id}/{$booking->id}");
    if($newcrccode != $crccode) {
        return $OUTPUT->box($OUTPUT->error_text(get_string('error_crccode', 'examregistrar')), 'alert alert-danger');
    }
    
    // by now we have an existing & valid booking & voucher
    // let's check booking?
    $user = $DB->get_record('user', array('id'=>$booking->userid), 'id, idnumber, firstname, lastname', MUST_EXIST);
    list($examname, $notused) = examregistrar_get_namecodefromid($booking->examid, 'exams');
    $attend = new stdClass();
    $attend->take = core_text::strtoupper($booking->booked ?  get_string('yes') :  get_string('no'));
    list($attend->site, $notused) = examregistrar_get_namecodefromid($booking->bookedsite, 'locations', 'location');
    $userbooking = $examname.
                    html_writer::div(get_string('voucheruser', 'examregistrar', $user), 'userbooking').
                    html_writer::div(get_string('takeonsite', 'examregistrar', $attend), 'booked');
    $booked = $booking->booked;
    
    // let's check time, is there a more recent voucher?
    $sql = "SELECT v.*, b.userid, b.examid, b.bookedsite, b.booked
            FROM {examregistrar_vouchers} v
            JOIN {examregistrar_bookings} b ON b.id = v.bookingid
            WHERE b.userid = :userid AND b.examid = :examid AND b.timemodified > :time 
            ORDER BY v.timemodified DESC";
    $params = array('userid'=>$booking->userid, 'examid'=>$booking->examid, 'time'=>$booking->timemodified);
    if($newer = $DB->get_records_sql($sql, $params)) {
        $a = new stdClass();
        $a->count = count($newer);
        $newer = reset($newer);

            $icon = new pix_icon('t/download', get_string('voucherdownld', 'examregistrar'), 'core', null); 
            $num = str_pad($newer->examregid, 4, '0', STR_PAD_LEFT).'-'.$newer->uniqueid;
            $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cmid, 'down'=>'voucher', 'v'=>$num));
            $num = $OUTPUT->action_link($downloadurl, $num, null, array('class'=>'voucherdownload'), $icon);
            $a->last = get_string('vouchernum', 'examregistrar',  $num);

        $output .= $OUTPUT->box($OUTPUT->error_text(get_string('error_latervoucher', 'examregistrar', $a)), 'alert alert-warning');
        $attend->take = core_text::strtoupper($newer->booked ?  get_string('yes') :  get_string('no'));
        list($attend->site, $notused) = examregistrar_get_namecodefromid($newer->bookedsite, 'locations', 'location');
        $userbooking =  $examname.
                        html_writer::div(get_string('voucheruser', 'examregistrar', $user), 'userbooking').
                        html_writer::div(get_string('takeonsite', 'examregistrar', $attend), 'booked');
        $booked = $newer->booked;
    }
    
    $alert = $booked ? 'success' : 'danger';
    
    $output .= $OUTPUT->box($userbooking, "alert alert-$alert");
    
    return $output;
    
}


//////////////////////////////////////////////////////////////////////////////////
//   Staffers functions                                                         //
////////////////////////////////////////////////////////////////////////////////

/**
 * Look for course teachers
 *
 * @param int $courseid Exam courseid
 * @return array userid, fullnames
 */
function examregistrar_get_teachers($courseid) {
    $teachers = array();
    $coursecontext = context_course::instance($courseid);
    $userfieldsapi = \core_user\fields::for_name();
    $fields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
    if($users = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.idnumber, u.picture, '.$fields, ' u.lastname ASC ')){
        foreach($users as $user) {
            $teachers[$user->id] = fullname($user) ;
        }
    }
    return $teachers;
}


/**
 * Look for course teachers and assign then as room staffers in rooms with allocated exam in a session
 *
 * @param array $examsessions a collection of examsessions where to look for allocated exams
 * @param int $bookedsite the venue wheer allocations take place
 * @param string $role the role to assign
 * @return void
 */
function examregistrar_assignroomstaff_fromexam($examsessions, $bookedsite, $role, $remove=false) {
    global $DB, $USER;

    list($insql, $params) = $DB->get_in_or_equal($examsessions, SQL_PARAMS_NAMED, 'sess');
    $sql = "SELECT ss.id, ss.examsession, ss.examid, ss.roomid, e.courseid, c.shortname
            FROM {examregistrar_session_seats} ss
            JOIN {examregistrar_exams} e ON ss.examid = e.id AND ss.examsession = e.examsession AND e.callnum > 0
            JOIN {course} c ON e.courseid = c.id
            WHERE ss.bookedsite = :bookedsite  AND ss.additional = 0 AND ss.examsession $insql
            GROUP BY ss.examsession, ss.examid, ss.roomid ";

    $params['bookedsite'] = $bookedsite;

    $visible = $remove ? 0 : 1;

    if(!$role && !$remove) {
        $role = 'RS'; /// TODO   TODO TODO
    }

    $errors = array();
    if($allocations = $DB->get_records_sql($sql, $params)) {
        foreach($allocations as $allocation) {
            $coursecontext = context_course::instance($allocation->courseid);
            if(!$coursecontext) {
                $errors[$allocation->shortname] = $allocation->shortname.' - No context';
            }
            if($users = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.idnumber', ' u.lastname ASC ')){
                foreach($users as $user) {
                    if(!$role && $remove) {
                        examregistrar_remove_roomstaffers($allocation->examsession,$allocation->roomid);
                    } else {
                        examregistrar_addupdate_roomstaffer($allocation->examsession,$allocation->roomid,
                                                            $user->id, $role, '', $visible);
                }
            }
        }
    }
    }

    if($errors){
        return html_writer::alist($errors);
    }

    return false;
}



/**
 * Look for staffers in this course & in all possible courses
 *
 * @param array $examsessions a collection of examsessions where to look for allocated exams
 * @param int $bookedsite the venue wheer allocations take place
 * @param string $role the role to assign
 * @return void
 */
function examregistrar_get_potential_staffers($examregistrar, $roomid, $newrole=true) {
    global $DB;

    $cm = get_coursemodule_from_instance('examregistrar', $examregistrar->id, $examregistrar->course, false, MUST_EXIST);
    $context = context_module::instance($cm->id);

    $config = examregistrar_get_instance_config($examregistrar->id, 'staffcats, excludecourses');

    $userfieldsapi = \core_user\fields::for_name();
    $fields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
    $fields = 'u.id, '.$fields;
    $users = get_users_by_capability($context, 'mod/examregistrar:beroomstaff', $fields, 'lastname ASC');
    $categories = null;
    $categories =  !is_array($config->staffcats) ? explode(',', $config->staffcats) : $config->staffcats;
    if($categories) {
        foreach($categories as $category) {
            $select = ' c.category = :category AND c.visible = 1 ';
            if($config->excludecourses) {
                $select .= ' AND uc.credits > 0 ';
            }
            $sql = "SELECT c.id, c.fullname, c.shortname, c.idnumber
                    FROM {course} c 
                    LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
                    WHERE $select ";
            
            if($courses = $DB->get_records_sql($sql, array('category'=>$category))) {
                foreach($courses as $course) {
                    $coursecontext = context_course::instance($course->id);
                    $courseusers = get_users_by_capability($coursecontext, 'mod/examregistrar:beroomstaff', $fields, 'lastname ASC');
                    $users =  $users + $courseusers;
                }
            }
        }
    }
}


/**
 * Returns an array of users that are assigned as staff in a room
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return array userids or objects
 */
function examregistrar_get_room_staffers($roomid, $sessionid='', $role='', $visible=1, $ids=false) {
    global $DB;

   // print_object("room: $roomid  session: $sessionid ");

    $params = array($roomid);
    $emptysession = $DB->sql_isempty('examregistrar_staffers', 'examsession', true, false);
    $sessionwhere = '';
    if($sessionid) {
        $sessionwhere = ' examsession = ? OR ';
        $params[] = $sessionid;
    }
    $select = " locationid = ? AND ( $sessionwhere $emptysession )  ";
    if($visible >= 0) {
        $visible = ($visible > 0) ? '1' : '0';
        $select .= " AND s.visible = $visible ";
    }

    if($role) {
        $select .= ' AND role = ? ';
        $params[] = $role;
    }

    if($ids) {
        $staffers = $DB->get_fieldset_select('examregistrar_staffers', 'userid', $select, $params);
    } else {
        $userfieldsapi = \core_user\fields::for_name();
        $fields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $sql = "SELECT s.*, es.name AS rolename, es.idnumber AS roleidnumber, $fields, u.username, u.idnumber, u.picture, u.email, u.phone1, u.phone2, u.city
                FROM {examregistrar_staffers} s
                JOIN {examregistrar_elements} es ON s.role = es.id
                JOIN {user} u ON s.userid = u.id
                WHERE $select
                ORDER BY u.lastname ASC, u.firstname ASC, s.role ASC ";

        $staffers = $DB->get_records_sql($sql, $params);
    }

    //print_object($staffers);
    //print_object(" -----   staffers  ------          ");

    return $staffers;
}


/**
 * Returns an HTML list of users that are assigned as staff in a room
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return array userids or objects
 */
function examregistrar_get_room_staffers_list($roomid, $sessionid='', $role='', $visible=1, $ids=false) {

    if($staffers =examregistrar_get_room_staffers($roomid, $sessionid, $role, $visible, $ids)) {
        $users = array();
        foreach($staffers as $staff) {
            $name = fullname($staff);
            $role = ' ('.$staff->roleidnumber.')';
            $users[] = $name.$role;
        }
        return html_writer::alist($users);
    }
    return '';
}

/**
 * Returns a list of
 *
 * @param array $staffers array of staff tableobjects
 * @param string $format ir true, only userids are returned
 * @return array element id, element name
 */
function examregistrar_format_room_staffers($staffers, $baseurl, $exregid, $downloading=false,  $return= true) {
    global $OUTPUT, $DB;

    if(!$staffers) {
        return;
    }

    /// TODO separate by roles

    $baseurl->param('edit', 'staffers');

    $roleusers = array();

    $stredit   = get_string('edit');
    $strdelete = get_string('delete');
    foreach($staffers as $staff) {
        $name = fullname($staff);
        $role = ' ('.$staff->roleidnumber.')';
        $data = $name.$role;
        $visible = -$staff->id;
        $visicon = 'show';
        $strvisible = get_string('hide');
        if(!$staff->visible) {
            $data = html_writer::span($name.$role, 'dimmed_text');
            $visible = $staff->id;
            $visicon = 'hide';
            $strvisible = get_string('show');
        }
        if(!$downloading) {
            $buttons = array();
            $url = new moodle_url($baseurl, array('show'=>$visible));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/'.$visicon, $strvisible, 'moodle', array('class'=>'iconsmall', 'title'=>$strvisible)));
            $url = new moodle_url($baseurl, array('del'=>$staff->id));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete)));
            $action = implode('&nbsp;', $buttons);
            $data .= '&nbsp;'.$action;
        }
        $roleusers[$staff->role][] = $data;
    }

    $output = '';
    if($roleusers) {
        foreach($roleusers as $role => $users) {
            $rolename = $DB->get_field('examregistrar_elements', 'name', array('examregid'=>$exregid, 'type'=>'roleitem', 'idnumber'=>$role));
            $output = $rolename;
            $output .= html_writer::alist($users);
        }
    }

    
    $baseurl->param('edit', 'locations');
    if($return) {
        return $output;
    }
    echo $output;
}


/**
 * Returns a collection of allocated exams organized by parent/room/exam/extras
 *
 * @param array $filters associative array of search fields
 *                array('session'=>$session, 'bookedsite'=>$bookedsite,
 *                      'room'=>$room, 'programme'=>$programme);
 * @param array $courseids optional array of course IDs to limit result to (those courses or less)
 * @return array of rooms classes
 */
function examregistrar_get_examallocations_byexam(array $filters, $courseids = array()) {
    global $DB;

    if(!$filters['session']) {
        return array();
    }

    $params = array();
    $where = '';
    if(isset($filters['session']) && $filters['session']) {
        $where .= ' AND e.examsession = :session ';
        $params['session'] = $filters['session'];
    }
    if(isset($filters['bookedsite']) && $filters['bookedsite']) {
        $where .= ' AND b.bookedsite = :bookedsite ';
        $params['bookedsite'] = $filters['bookedsite'];
    }

    if(isset($filters['programme']) && $filters['programme']) {
        $where .= ' AND e.programme = :programme ';
        $params['programme'] = $filters['programme'];
    }
    if(isset($filters['room']) && $filters['room']) {
        $where .= ' AND ss.roomid = :room ';
        $params['room'] = $filters['room'];
    }
    if(isset($filters['course']) && $filters['course']) {
        $where .= ' AND e.courseid = :course ';
        $params['course'] = $filters['course'];
    }
    if(isset($filters['exam']) && $filters['exam']) {
        $where .= ' AND e.id = :exam ';
        $params['exam'] = $filters['exam'];
    }
/*
    $sql = "SELECT e.*, c.shortname, c.fullname, c.idnumber
                FROM {examregistrar_exams} e
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {examregistrar_session_seats} ss ON e.id = ss.examid AND  ss.examsession = e.examsession
                WHERE 1 $where
                GROUP BY e.id
                ORDER BY c.shortname ASC, c.fullname ASC ";

*/
                
    $sql = "SELECT e.*, c.shortname, c.fullname, c.idnumber
                FROM {examregistrar_exams} e
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {examregistrar_bookings} b ON e.id = b.examid AND b.booked = 1
                LEFT JOIN {examregistrar_session_seats} ss ON e.id = ss.examid AND  ss.examsession = e.examsession AND b.bookedsite = ss.bookedsite
                WHERE e.visible = 1 $where
                GROUP BY e.id
                ORDER BY c.shortname ASC, c.fullname ASC ";
                
    $examallocations = array();

    if($allocations = $DB->get_records_sql($sql, $params)) {
    
        foreach($allocations as $allocation) {
            if($courseids && !in_array($allocation->courseid, $courseids)) {
                continue;
            }
            if(!isset($examallocations[$allocation->id])) {
                // this should create room and parent data
                $exam = new examregistrar_allocatedexam($filters['session'], $filters['bookedsite'], $allocation, 'id');
            } else {
                $exam = $examallocations[$allocation->id];
            }
            $examallocations[$allocation->id] = $exam;
        }
    }

    return $examallocations;
}




/**
 * Returns a collection of allocated rooms by parent/room/exam/extras for a given exam
 *
 * @param array $params associative array of search fields
 *                array('period'=>$period, 'session'=>$session, 'bookedsite'=>$bookedsite,
 *                      'room'=>$room, 'programme'=>$programme, 'shortname'=>$shortname);
 * @return array of rooms classes
 */
function examregistrar_get_roomallocations_byexam(array $params) {
    global $DB;

    $result = array();




    return $result;
}


/**
 * Returns a collection of allocated rooms organized by parent/room/exam/extras
 *
 * @param array $filters associative array of search fields
 *                array('session'=>$session, 'bookedsite'=>$bookedsite,
 *                      'room'=>$room, 'programme'=>$programme);
 * @param array $roomids optional array of room IDs to limit result to (those rooms or less)
 * @return array of rooms classes
 */
function examregistrar_get_roomallocations_byroom(array $filters, $roomids = array()) {
    global $DB;

    $params = array();

    if(!$filters['session'] ) {
        return array();
    }

    $where = '';
    if(isset($filters['session']) && $filters['session']) {
        $where .= ' AND ss.examsession = :session ';
        $params['session'] = $filters['session'];
    }
    if(isset($filters['bookedsite']) && $filters['bookedsite']) {
        $where .= ' AND ss.bookedsite = :bookedsite ';
        $params['bookedsite'] = $filters['bookedsite'];
    }

    if(isset($filters['programme']) && $filters['programme']) {
        $where .= ' AND e.programme = :programme ';
        $params['programme'] = $filters['programme'];
    }
    if(isset($filters['room']) && $filters['room']) {
        $where .= ' AND ss.roomid = :room ';
        $params['room'] = $filters['room'];
    }
    if(isset($filters['course']) && $filters['course']) {
        $where .= ' AND e.courseid = :course ';
        $params['course'] = $filters['course'];
    }


//     //print_object($filters);
//     //print_object($where);
//     //print_object("  ---- firltrsXXXX ---------------");

    $sort = '';
    if(isset($filters['sort']) && $filters['sort']) {
        $sort = ' '.$filters['sort'].' '; // seats/booked/free
        if($filters['sort'] == 'freeseats' || $filters['sort'] == 'seats') {
            $sort .= ' DESC';
        }
        $sort .= ', ';
    }

    $roomallocations = array();

    $sql = "SELECT ss.id, r.parent, el.name AS parentname, el.idnumber AS parentidnumber,
                    ss.bookedsite, ss.roomid, er.name, er.idnumber, r.path, r.depth, r.seats, (r.seats - COUNT(ss.id)) AS freeseats,
                    ss.examid, e.programme, e.courseid, e.examsession, e.annuality, e.examscope, e.callnum, c.shortname, c.fullname,  COUNT(userid) AS seated, COUNT(ss.id) AS booked
            FROM {examregistrar_session_seats} ss
            JOIN {examregistrar_locations} r ON ss.roomid = r.id
            JOIN {examregistrar_elements} er ON r.examregid = er.examregid AND er.type ='locationitem' AND r.location = er.id
            JOIN {examregistrar_exams} e ON ss.examid = e.id AND e.visible = 1
            JOIN {course} c ON e.courseid = c.id
            LEFT JOIN {examregistrar_locations} l ON r.parent = l.id
            LEFT JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type ='locationitem' AND l.location = el.id

            WHERE ss.additional = 0 $where
            GROUP BY ss.roomid, ss.examid
            ORDER BY $sort name ASC, c.shortname ASC ";

    if($allocations = $DB->get_records_sql($sql, $params)) {
        foreach($allocations as $allocation) {
            if($roomids && !in_array($allocation->roomid, $roomids)) {
                continue;
            }
            if(!isset($roomallocations[$allocation->roomid])) {
                // this should create room and parent data
                //$room = new examregistrar_allocatedroom($filters['session'], $filters['bookedsite'], $allocation, 'roomid');
                $room = new examregistrar_allocatedroom($filters['session'], $allocation->bookedsite, $allocation, 'roomid');

            } else {
                $room = $roomallocations[$allocation->roomid];
            }
            // add a new exm row and updates occupancy
            $room->add_exam_fromrow($allocation, 'examid');
            $room->refresh_seated();
            $roomallocations[$allocation->roomid] = $room;
        }
    }

    return $roomallocations;
}



////////////////////////////////////////////////////////////////////////////////
// Database & doing work functions                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Construct an SQL fragment to use in WHERE clause to search for course criteria
 * @param object $form post data including course selection settings as courseXXX fields
 * @return array ($select, params) tuple for get_records_xx database functions
 */
function examregistrar_course_sqlselect($formdata) {
    global $DB;

    $params = array();
    $wherecourse = '';
    if($formdata->coursevisible != -1) {
        $wherecourse .= " AND c.visible = ? ";
        $params[] = $formdata->coursevisible;
    }
    if(isset($formdata->courseformat) &&  $formdata->courseformat !='all') {
        $wherecourse .= " AND c.format = ? ";
        $params[] = $formdata->courseformat;
    }
    if(isset($formdata->courseterm) &&  $formdata->courseterm != -1 ) {
        $wherecourse .= " AND uc.term = ? ";
        $params[] = $formdata->courseterm;
    }
    if(isset($formdata->coursecredit) &&  $formdata->coursecredit != -1) {
        if($formdata->coursecredit == -2) {
            $wherecourse .= " AND uc.credits > 0 ";
        } else {
            $wherecourse .= " AND uc.credits = ? ";
            $params[] = $formdata->coursecredit;
        }
    }
    if(isset($formdata->coursedept) &&  $formdata->coursedept != -1) {
        $wherecourse .= " AND uc.department = ? ";
        $params[] = $formdata->coursedept;
    }
    if(isset($formdata->coursectype) &&  $formdata->coursectype !='all') {
        $wherecourse .= " AND uc.ctype = ? ";
        $params[] = $formdata->coursectype;
    }

    if(isset($formdata->coursecategories) &&  $formdata->coursecategories) {
        list($insql, $inparams) = $DB->get_in_or_equal($formdata->coursecategories);
        $wherecourse .= " AND c.category $insql ";
        $params = array_merge($params, $inparams);

    }

    if(isset($formdata->coursetoshortnames) && trim($formdata->coursetoshortnames) != '') {
        if($names = explode(',' , addslashes($formdata->coursetoshortnames))) {
            foreach($names as $key => $name) {
                $names[$key] = trim($name);
            }
            list($insql, $inparams) = $DB->get_in_or_equal($names);
            $wherecourse .= " AND c.shortname $insql ";
            $params = array_merge($params, $inparams);
        }
    }

    if (isset($formdata->courseidnumber) && $formdata->courseidnumber) {
        $wherecourse .= " AND ".$DB->sql_like('c.idnumber', '?');
        $params[] = $formdata->courseidnumber;
    }

    return array($params, $wherecourse);
}


/**
 * determines the exam programme setting for a course given generation options
 *
 * @param object $examregistrar object
 * @param object $course course to add exam entry
 * @param object $category category object of given course
 * @param array $options generating settings
 */
function examregistrar_programme_fromcourse($examregistrar, $course, $category, $options) {

    // TODO restrict by examregistrar programe settings

    $programme = '';
    switch($options->programme) {
        case 'courseidnumber':
                            $pieces = explode('_', $course->idnumber);
                            $programme = $pieces[0];
                            break;
        case 'courseshortname':
                            $pieces = explode('-', $course->shortname);
                            $programme = $pieces[1];
                            break;
        case 'coursecatid': $programme = $course->category;
                            break;
        case 'coursecatidnumber':
                            $pieces = explode('_', $category->idnumber);
                            $programme = $pieces[1];
                            break;
        case 'coursecatdegree': $programme = $category->degree;
                            break;
    }

    return $programme;
}

/**
 * determines the periods to generate exams for a course given generation options
 *
 * @param object $examregistrar object
 * @param object $course course to add exam entry
 * @param array $periods periods selected in settings
 * @param array $options generating settings
 */
function examregistrar_examperiods_fromcourse($examregistrar, $course, $periods, $options) {
    global $DB;

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $examperiods = array();
    $examinstances = array();
    if($options->generatemode == 1) {
        $assignmodid = $DB->get_field('modules', 'id', array('name'=>'assign'));
        $sql = "SELECT cm.id, cm.course, cm.instance, cm.section, cm.idnumber, a.allowsubmissionsfromdate
                FROM {course_modules} cm
                JOIN {assign} a ON cm.instance = a.id AND cm.course = a.course
                JOIN {assign_plugin_config} e ON cm.instance = e.assignment AND e.plugin = 'exam'
                                                 AND e.subtype = 'assignsubmission' AND e.name = 'enabled' AND e.value = 1
                WHERE cm.course = ? AND cm.module = ? AND cm.score > 0 ";
        $params = array($course->id, $assignmodid);
        $examinstances = $DB->get_records_sql($sql, $params);
    }

    $scopecodes = $DB->get_records_menu('examregistrar_elements', array('examregid'=>$examregprimaryid, 'type'=>'scopeitem') , 'idnumber ASC', 'id, idnumber');

    switch($options->assignperiod) {
        case 0 : /// as selected
                if($options->generatemode == 0) {
                    foreach($periods as $period) {
                        $item = clone $period;
                        $item->scope = 'F';
                        $item->examscope = array_search('F', $scopecodes);
                        $examperiods[] = $item;
                    }
                } elseif($options->generatemode == 1) {
                    foreach($examinstances as $exam) {
                        foreach($periods as $period) {
                            $p = strpos($exam->idnumber, $period->periodtypeidnumber);
                            if($p !== false) {
                                $scope = trim(substr($exam->idnumber, $p+strlen($period->periodtypeidnumber)));
                                $scope = ltrim($scope, '0-_');
                                if($period->periodtypeidnumber == 'ORD' && $scope) {
                                    $scope = 'P'.$scope;
                                } else {
                                    $scope = 'F'.$scope;
                                }
                                $item = clone $period;
                                $item->scope = $scope;
                                $item->examscope = array_search($scope, $scopecodes);
                                $item->examinstance = $exam->id;
                                $item->assigninstance = $exam->instance;
                                $examperiods[] = $item;
                            }
                        }
                    }
                }
                break;
        case 1 : /// from course start date
                if($options->generatemode == 0) {
                    foreach($periods as $period) {
                        if(($course->startdate >= $period->timestart) && ($course->startdate < $period->timeend)) {
                            $item = clone $period;
                            $item->scope = 'F';
                            $item->examscope = array_search('F', $scopecodes);
                            $examperiods[] = $item;
                        }
                    }
                } elseif($options->generatemode == 1) {
                    foreach($examinstances as $exam) {
                        foreach($periods as $period) {
                            if(($exam->allowsubmissionsfromdate >= $period->timestart) && ($exam->allowsubmissionsfromdate < $period->timeend)) {
                                $item = clone $period;
                                $item->scope = 'F';
                                $item->examscope = array_search('F', $scopecodes);
                                $item->examinstance = $exam->id;
                                $item->assigninstance = $exam->instance;
                                $examperiods[] = $item;
                            }
                        }
                    }
                }
                break;
        case 2 : /// from course term
                if($options->generatemode == 0) {
                    foreach($periods as $period) {
                        //print_object("periodtypeidnumber: {$period->periodtypeidnumber} / periodtermvalue: {$period->termvalue} / courseterm: {$course->term}  ");
                        $scopes = array();
                        if($period->periodtypeidnumber == 'ORD') {
                            if($course->term == 0) {
                                $scopes[] = 'P'.$period->termvalue;
                            } else {
                                if($course->term == $period->termvalue) {
                                    $scopes[] = 'F';
                                }
                            }
                        } else {
                            $scope = 'F';
                            if($course->term == 0) {
                                $scopes[] = $scope.'1';
                                $scopes[] = $scope.'2';
                            } else {
                                    $scopes[] = $scope;
                            }
                        }
                        //print_object($scopes);
                        //print_object(" --- scopes ---------");
                        foreach($scopes as $scope) {
                            $item = clone $period;
                            $item->scope = $scope;
                            $item->examscope = array_search($scope, $scopecodes);
                            $examperiods[] = $item;
                        }
                    }
                } elseif($options->generatemode == 1) {
                    foreach($examinstances as $exam) {
                        foreach($periods as $period) {
                            $p = strpos($exam->idnumber, $period->periodtypeidnumber);
                            if($p !== false) {
                                $scope = trim(substr($exam->idnumber, $p+strlen($period->periodtypeidnumber)));
                                $examscope = ltrim($scope, '0-_');
                                if($period->periodtypeidnumber == 'ORD' && $examscope) {
                                    $scope = 'P'.$examscope;
                                } else {
                                    $scope = 'F'.$examscope;
                                }
                                if(($period->periodtypeidnumber != 'ORD') ||
                                        (($period->periodtypeidnumber == 'ORD') && ($examscope == '') &&  $course->term == $period->termvalue) ||
                                        (($period->periodtypeidnumber == 'ORD') && ($examscope == $period->termvalue) && ($course->term == 0))) {
                                    $item = clone $period;
                                    $item->scope = $scope;
                                    $item->examscope = array_search($scope, $scopecodes);
                                    $item->examinstance = $exam->id;
                                    $item->assigninstance = $exam->instance;
                                    $examperiods[] = $item;
                                }
                            }
                        }
                    }
                }
                break;
    }

    return $examperiods;
}


/**
 * Saves response data creating entries in responses table 
 *
 * @param object $formdata object data from user input form
 * @param int $contextid context ID for counting & moving files 
 * @param object $eventdata 
 */
function examregistrar_save_attendance_responsedata($formdata, $contextid, $eventdata) {
    global $DB, $USER;

    $params = array('examsession' => $formdata->session,
                    'examid' => $formdata->examid,
                    'examfile' => $formdata->examfile,
                    );
    $now = time();
    $fs = get_file_storage(); 
    $updated = 0;
    
    foreach($formdata->roomdata as $rid => $allocated) {
        if(!$allocated) {
            continue;
        }
        
        $params['roomid'] = $rid;
        $response = $DB->get_record('examregistrar_responses', $params);
        if(!$response) {
            $response = (object)$params;
            $response->id = $DB->insert_record('examregistrar_responses', $params);
        }

        $response->modifierid = $USER->id;
        $response->timemodified = $now;
        $response->status = $formdata->roomstatus[$rid];
        $adding = ($response->status == EXAM_RESPONSES_ADDING);
        $response->showing = $adding ? $response->showing + $formdata->showing[$rid] : $formdata->showing[$rid];
        $response->taken = $adding ? $response->taken + $formdata->taken[$rid] : $formdata->taken[$rid];
        
        $files = $fs->get_directory_files($contextid, 'mod_examregistrar', 'examresponses', $response->id, '/', false, false);
        $numfiles = count($files);
        $response->numfiles = $adding ? $response->numfiles + $numfiles : $numfiles;
        
        $message = array();
        if($response->showing > $allocated) {
            $message[] = get_string('excessshowing', 'examregistrar',  $allocated);
        }
        if($response->taken > $allocated) {
            $message[] = get_string('excesstaken', 'examregistrar', $allocated);
        }
        if($response->taken > $response->showing) {
            $message[] = get_string('excesstakenshowing', 'examregistrar', $response->showing);
        }
        
        if($message) {
            list($roomname, $roomidnumber) = examregistrar_get_namecodefromid($rid, 'locations', 'location');
            array_unshift($message, get_string('roomerror', 'examregistrar', $roomname));
            $message = implode('<br />', $message);
            \core\notification::error($message);
        } else {
            if($DB->update_record('examregistrar_responses', $response)) {
                $eventdata['other']['room'] = $rid;
                $event = \mod_examregistrar\event\attendance_loaded::create($eventdata);
                $event->trigger();
                $updated++;
            }
        }
        
        /*
        if($oldrec) {
            $response->id = $oldrec->id;
            $DB->update_record('examregistrar_responses', $response);
        } else {
            $response->id = $DB->insert_record('examregistrar_responses', $response);
        }
        */
        //// TODO  //// TODO //// TODO //// TODO 
        // move files to new itemid if needed
    
        
    }
    
    return $updated;
}


/**
 * Saves response files and data for a room/venue, creating entries in responses table 
 *
 * @param object $formdata object data from user input form
 * @param int $contextid context ID for counting & moving files 
 * @param object $eventdata 
 */
function examregistrar_save_venue_attendance_files($formdata, $contextid, $eventdata) {
    global $DB, $USER;
    
    $params = array('examsession'   => $formdata->session,
                    'roomid'        => $formdata->room);

    $sql = "SELECT e.*, ef.id AS examfile, c.shortname, c.fullname 
            FROM {examregistrar_exams} e 
            JOIN {examregistrar_examfiles} ef ON e.id = ef.examid AND ef.status = :efstatus
            JOIN {course} c ON e.courseid = c.id
            WHERE e.id = :examid AND e.examsession = :examsession
            ORDER BY ef.timeapproved DESC, ef.attempt DESC  ";
    $sqlparams = array('examsession'=> $formdata->session,
                        'efstatus'    => EXAM_STATUS_APPROVED,
                        );
    $sessionroom =  (int)"{$formdata->session}0000{$formdata->venue}";   
    
    $fr = array('component' => 'mod_examregistrar', 
                'filearea'  =>'examresponses', 
                'filepath'  =>'/'
                );

    $now = time();
    $fs = get_file_storage(); 
    $updated = 0;
    
    foreach($formdata->examattendance as $examid => $attendance) {
        if(!$attendance['status']) {
            continue;
        }
        $sqlparams['examid'] = $examid;
        $exam = $DB->get_record_sql($sql, $sqlparams);
        
        if(!$exam) {
            continue;    
        }
        
        $params['examid'] = $examid;
        $params['examfile'] = $exam->examfile;

        $response = $DB->get_record('examregistrar_responses', $params);
        if(!$response) {
            $response = (object)$params;
            $response->id = $DB->insert_record('examregistrar_responses', $params);
        }
    
        $response->modifierid = $USER->id;
        $response->timemodified = $now;
        $response->status = $attendance['status'];
        $adding = ($response->status == EXAM_RESPONSES_ADDING);
        //$response->showing = $adding ? $response->showing + $attendance['showing'] : $attendance['showing'];
        $response->taken = $adding ? $response->taken + $attendance['taken'] : $attendance['taken'];
        
        
        if($DB->update_record('examregistrar_responses', $response)) {
            $eventdata['other']['room'] = $formdata->room;
            $eventdata['other']['examid'] = $examid;
            $event = \mod_examregistrar\event\attendance_loaded::create($eventdata);
            $event->trigger();
            $updated++;
                
            $ccontext = context_course::instance($exam->courseid);
            
            $fr['contextid'] = $ccontext->id;
            $fr['itemid'] = $response->id;
            
            $files = $fs->get_directory_files($contextid, 'mod_examregistrar', 'roomresponses', $sessionroom, '/', false, false);
            // TODO  
            // id delete, delete first 
            //$fs->delete_area_files($contextid, 'mod_examregistrar', 'examresponses', $response->id); 
            
            foreach($files as $key => $file) {
                $filename = basename($file->get_filename(), '.pdf');
                if(strpos($filename, $exam->shortname) === 0) {
                    // filename starts with shortname. Move file to examrespones
                    $count = 0;
                    $suffix = '';
                    $ext = '.pdf'; 
                    while($fs->file_exists($fr['contextid'], $fr['component'], $fr['filearea'], $fr['itemid'], $fr['filepath'], 
                                $filename.$suffix.$ext)) {
                        $count++;
                        $suffix = '_'.$count; 
                    }
                    $fr['filename'] = $filename.$suffix.$ext;
                    $fs->create_file_from_storedfile($fr, $file); 
                    $file->delete();
                    $files[$key] = $fr['filename'];
                } else {
                    unset($files[$key]);
                }
            }
            
            $numfiles = count($fs->get_directory_files($ccontext->id, 'mod_examregistrar', 'examresponses', $response->id, '/', false, false));
            $response->numfiles = $adding ? $response->numfiles + $numfiles : $numfiles;
                
                
            $eventdata['other']['files'] = implode(', ', $files);
            $event = \mod_examregistrar\event\responses_uploaded::create($eventdata);
            $event->trigger();
            $DB->set_field('examregistrar_responses', 'numfiles', $numfiles, array('id'=>$response->id));
        }
    }
    
    \core\notification::success(get_string('savedresponsefiles', 'examregistrar', $updated));
    
}

/**
 * Saves user attendance data in session_seats table 
 *
 * @param object $formdata object data from user input form
 */
function examregistrar_save_attendance_userdata($formdata, $examinattendance = false) {
    global $DB, $USER;

    $params = array('examsession'   => $formdata->session);
    if(!$examinattendance) {
        $params['examid'] = $formdata->examid;
    }
    $now = time();
    $updated = 0;
    
    foreach($formdata->userattendance as $sid => $attendance) {
        if(!$attendance['add']) {
            continue;
        }
        $params['userid'] = $attendance['add'];
        $params['id'] = $sid;
        if($examinattendance) {
            $params['examid'] = $attendance['examid'];
        }
        $userdata = $DB->get_record('examregistrar_session_seats', $params, '*', MUST_EXIST);
        $userdata->showing = $attendance['showing'];
        $userdata->taken = $attendance['taken'];
        $userdata->certified = $attendance['certified'];
        $userdata->status = $formdata->userstatus;
        $userdata->modifierid = $USER->id;
        $userdata->timemodified = $now;

        if($DB->update_record('examregistrar_session_seats', $userdata)) {
            $updated++;
        }
    }

    return $updated;
}


/**
 * Saves user attendance data in session_seats table 
 *
 * @param object $formdata object data from user input form
 * @param object $eventdata 
 */
function examregistrar_confirm_attendance_userdata($formdata) {
    global $DB, $USER;
    
    $params = array('examsession'   => $formdata->session,
                    'etakenxamid'        => $formdata->examid,
                    );
                    
    $select = '';
    foreach($params as $param) {
        $select .= " $param = :$param AND ";
    }
    foreach($formdata->userattendance as $sid => $uid) {
        if(!$uid) {
            unset($formdata->userattendance[$sid]);
        }
    }
    
    list($insqlid, $idparams) = $DB->get_in_or_equal(array_keys($formdata->userattendance), SQL_PARAMS_NAMED, 'id_');
    list($insqlu, $uparams) = $DB->get_in_or_equal($formdata->userattendance, SQL_PARAMS_NAMED, 'u_');
    $select .= " userid $insqlu ";
    $params = $params + $uparams;
    $select .= " id $insqlid ";
    $params = $params + $idparams;
    
    $success = $DB->set_field_select('examregistrar_session_seats', 'status', $formdata->userstatus, $select, $params); 
    $DB->set_field_select('examregistrar_session_seats', 'reviewerid', $USER->id, $select, $params);
    $now = time();
    $DB->set_field_select('examregistrar_session_seats', 'timereviewed', $now, $select, $params); 

    if($success) {
        return count($formdata->userattendance);
    }
    
    return false;
}




/**
 * Review && confirm response files  in session_seats table 
 *
 * @param object $formdata object data from user input form
 * @param string $filename new standarized filename for files
 * @param int $contextid course context containing files
 * @param object $eventdata 
 */
function examregistrar_confirm_attendance_roomdata($formdata, $shortname, $coursectxid, $primaryctxid, $eventdata) {
    global $DB, $USER;

    $params = array('examsession' => $formdata->session,
                    'examid' => $formdata->examid,
                    'examfile' => $formdata->examfile,
                    );
    $now = time();
    $fs = get_file_storage(); 
    $updated = 0;
    unset($eventdata['other']['files']);
    unset($eventdata['other']['users']);

    $fr = array('contextid' => $primaryctxid,
                'component' => 'mod_examregistrar', 
                'filearea'  =>'sessionresponses', 
                'itemid'    => $formdata->session,
                'filepath'  =>'/'
                );
    
    foreach($formdata->roomdata as $rid => $attandance) {
        if(!$attendance) {
            //means not checked by user, not saved
            continue;
        }
        
        $params['roomid'] = $rid;
        // get or create the table record
        if($formdata->response[$rid]) {
            $params['id'] = $formdata->response[$rid];
            $response = $DB->get_record('examregistrar_responses', $params, '*', MUST_EXIST);
        } else {
            if(!$response = $DB->get_record('examregistrar_responses', $params, '*', MUST_EXIST)) {
                $response = (object)$params;
                $response->id = $DB->insert_record('examregistrar_responses', $params);
            }
        }
    
        $response->showing = $formdata->showing[$rid];
        $response->taken = $formdata->taken[$rid];
        $response->status = $formdata->roomstatus[$rid];
        $response->reviewerid = $USER->id;
        $response->timereviewed = $now;
        
        $files = $fs->get_directory_files($coursectxid, 'mod_examregistrar', 'examresponses', $response->id, '/', false, false);
        $response->numfiles = count($files);

        $success = $DB->update_record('examregistrar_responses', $response);
        
        if($success) {
            $updated++;
            $eventdata['other']['room'] = $rid;
            $event = \mod_examregistrar\event\responses_approved::create($eventdata);
            $event->trigger;

            $roomname = $roomidnumber = ''; 
            if($rid) {
                list($roomname, $roomidnumber) = examregistrar_get_namecodefromid($rid, 'locations', 'location');
            }
            
            // now move files to session
            $num = 0;
            if($files) {
                foreach($files as $key => $file) {
                    $filename = $shortname;
                    if($roomidnumber) {
                        $filename .= '-'.$roomidnumber;
                    }
                    $count = 0;
                    $suffix = '';
                    $ext = '.pdf'; 
                    while($fs->file_exists($fr['contextid'], $fr['component'], $fr['filearea'], $fr['itemid'], $fr['filepath'], 
                                $filename.$suffix.$ext)) {
                        $count++;
                        $suffix = '_'.$count; 
                    }
                    $fr['filename'] = $filename.$suffix.$ext;
                    $fs->create_file_from_storedfile($fr, $file);
                    $num++;
                    $file->delete();                    
                    $files[$key] = $fr['filename'];
                }
            }
            $eventdata['other']['room'] = $rid;
            $eventdata['other']['files'] = implode(', ',$files);
            $event = \mod_examregistrar\event\attendance_approved::create($eventdata);
            $event->trigger;
        }
    
    
    }
    
    unset($eventdata['other']['files']);
    unset($eventdata['other']['room']);

}


////////////////////////////////////////////////////////////////////////////////
// Interface & presentation functions                                         //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns administration tabs above page
 *
 * @param int $cmid course module id value
 * @param string $currenttab the current highlighted used tab
 * @print tabs
 * @return void
 */
function examregistrar_print_tabs($cmid, $currenttab = 'view') {
    global $OUTPUT;

    $row = array();
    $row[] = new tabobject('view',
                           new moodle_url('/mod/examregistrar/view.php', array('id' => $cmid)),
                           get_string('view', 'examregistrar'));

    $row[] = new tabobject('manage',
                           new moodle_url('/mod/examregistrar/manage.php', array('id' => $cmid)),
                           get_string('manage', 'examregistrar'));

    echo '<div class="tabdisplay">';
    echo $OUTPUT->tabtree($row, $currenttab);
    echo '</div>';
}




/**
 * Returns a collection of courses that have associated exams defined
 *    and the user can access  (checking appropiate capability)
 *
 * @param object $examregistrar instance object
 * @param object $course record object for course calling this (where instance is placed)
 * @param array $searchparams parameteres need for the user course/exam searching
 * @param array $capabilities the permissions to check for user in the
 * @param bool $viewall permission to see all courses
 * @param bool $booking special provisions if searching bookable courses
 * @return array user courses
 */
function examregistrar_get_user_courses($examregistrar, $course, $searchparams, $capabilities, $canviewall, $booking = false) {
    global $DB, $USER;

    $courses = array();

    $term = isset($searchparams['term']) ? $searchparams['term'] : 0;
    $period = isset($searchparams['period']) ? $searchparams['period'] : 0;
    $searchname = isset($searchparams['searchname']) ? $searchparams['searchname'] : '';
    $searchid = isset($searchparams['searchid']) ? $searchparams['searchid'] : 0;
    $sort = isset($searchparams['sorting']) ? $searchparams['sorting'] : '';
    $order = isset($searchparams['order']) ? $searchparams['order'] : '';
    $programme = isset($searchparams['programme']) ? $searchparams['programme'] : '';

    $userid = isset($searchparams['user']) ? $searchparams['user'] : $USER->id;
    $session = isset($searchparams['session']) ? $searchparams['session'] : 0;
    $bookedsite = isset($searchparams['venue']) ? $searchparams['venue'] : 0;

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);

    $params = array();
    $coursewhere = '';
    $examwhere = '';
    if($period) {
        $examwhere .= ' AND e.period = :period ';
        $params['period'] = $period;
    }
    if($examregistrar->workmode == EXAMREGISTRAR_MODE_REGISTRAR) {
        // if examregistrar define a programme, use it
        if($programme) {
            $examwhere = " AND e.programme = :programme ";
            $params['programme'] = $programme;
        } elseif($examregistrar->programme) {
            $examwhere = " AND e.programme = :programme ";
            $params['programme'] = trim($examregistrar->programme);
        }
        // if not programme, not set search : all returned
    } elseif($examregistrar->workmode == EXAMREGISTRAR_MODE_REVIEW || $examregistrar->workmode == EXAMREGISTRAR_MODE_BOOK) {
        if($programme) {
            $examwhere = " AND e.programme = :programme ";
            $params['programme'] = $programme;
        } elseif($examregistrar->programme) {
            $examwhere = " AND e.programme = :programme ";
            $params['programme'] = trim($examregistrar->programme);
        } else {
        // if not programme, use course category
            $coursewhere = " AND c.category = :category ";
            $params['category'] = $course->category;
        }
    } else {
        $coursewhere = " AND c.id = :courseid ";
        $params['courseid'] = $course->id;
    }

    if($term && get_config('local_ulpgccore') ) {
        $termvalue = $DB->get_field('examregistrar_elements', 'value', array('id'=>$term, 'type'=>'termitem', 'examregid'=>$examregprimaryid));
        if($termvalue !== false) {
            if(($termvalue == 1) OR ($termvalue == 2)) {
                $coursewhere .= " AND ((uc.term = 0) OR (uc.term = :term)) ";
            } else {
                $coursewhere .= " AND (uc.term = :term) ";
            }
            $params['term']  = $termvalue;
        }
    }

    $searchwhere = '';
    if($searchname) {
        $searchwhere .= ' AND '.$DB->sql_like('c.fullname', ':fullname', false, false);
        $params['fullname'] = '%'.$searchname.'%';
    }
    if($searchid) {
        $searchwhere .= " AND c.id = :searchid";
        $params['searchid'] = $searchid;
    }
    if(!$sort) {
        $sort = 'shortname';
    }
    if(!$order) {
        $order = 'ASC';
    }

    $examregistrarwhere = '';
    if($booking) {
        if(isset($params['programme'])) {
            $params['programme'] = '%'.$params['programme'].'%';
            $coursewhere .= ' AND '.$DB->sql_like('c.idnumber', ':programme');
        }
        if($courses = enrol_get_users_courses($userid, true, null, 'category ASC, fullname ASC')) {
            list($insql, $cparams) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'course_');
            $coursewhere .= " AND c.id $insql ";
            $params = $params + $cparams;
        } else {
            return array();
        }
    } else {
        $examregistrarwhere = "AND EXISTS ( SELECT 1
                                            FROM {examregistrar_exams} e
                                            WHERE  e.courseid = c.id $examwhere  ) ";
    }


    $sql = "SELECT c.id, c.fullname, c.shortname, c.idnumber, uc.term, uc.credits
                    FROM {course} c
                    LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
                    WHERE 1 $coursewhere  $searchwhere $examregistrarwhere
                    ORDER BY c.$sort $order ";

    $courses = $DB->get_records_sql($sql, $params);

    foreach($courses as $cid => $examcourse) {
        // check review permissions in exam course
        $econtext = context_course::instance($examcourse->id);
        if(!has_any_capability($capabilities, $econtext, $userid) && !$canviewall) {
            unset($courses[$cid]);
        }
    }

    return $courses;
}
