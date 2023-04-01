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
 * Internal library of functions for management actions in module examregistrar
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
//require_once(__DIR__.'/renderable.php');


////////////////////////////////////////////////////////////////////////////////
// general                                                                    /
//////////////////////////////////////////////////////////////////////////////


/**
 * Returns a menu of table records items, (id, name) for selected table
 *
 * @param string $element element name / component table
 * @param object $formdata data submitted in an item-editing form
 * @return object properly built element
 */
function examregistrar_extract_edititem_formdata($element, $formdata) {
    global $USER;

//     //print_object($formdata);
//     //print_object("----  formdata ---------------");

    $data = new stdClass;
    $data = clone $formdata;

    if(isset($data->id)) {
        unset($data->id);
    }
    if(isset($data->edit)) {
        unset($data->edit);
    }
    if(isset($data->item)) {
        unset($data->item);
    }

    $data->display = '';
    if(isset($data->name)) {
        $data->display = $data->name;
    } elseif(isset($data->shortname)) {
        $data->display = $data->shortname;
    } elseif(isset($data->$element)) {
        $data->display = $DB->get_field('examregistrar_elements', 'name', array('id'=>$data->$element));
    }

    if($element == 'element') {
        $data->value = 0;
        if(isset($formdata->value)) {
            $data->value = $formdata->value;
        }
    }

    if($element == 'exam' || $element == 'exams') {
        $data->examscope = $formdata->scope;
        if($formdata->additional == 1) {
            $data->callnum = -($formdata->callnum);
        }
    }

    if($fields = get_object_vars($formdata)) {
        foreach($fields as $field => $value) {
            if(is_array($value) && isset($value['text'])) {
                $data->$field = $value['text'];
                $data->{$field.'format'} = $value['format'];
            }
        }
    }

    if(!isset($data->visible)) {
        $data->visible = 1;
    }
    if(!isset($data->component) || !$data->component) {
        $data->modifierid = $USER->id;
    }
    $data->timemodified = time();

    return $data;
}


////////////////////////////////////////////////////////////////////////////////
// edit exams & delivery modes                                                /
//////////////////////////////////////////////////////////////////////////////


function examregistrar_get_editable_item($edit, $itemtable, $itemid) {
    global $DB; 
    
    if($element = $DB->get_record($itemtable, array('id' => $itemid))) {
        if($edit == 'exams') {
            $element->scope = $element->examscope;
            if($element->callnum < 0) {
                $element->callnum = abs($element->callnum);
                $element->additional = 1;
            }
            examregistrar_exam_add_deliverymodes($element);
        }
        // take care of text editor fields: two fields as array in mform
        $editor = '';
        if($itemtable == 'examregistrar_locations') {
            $editor = 'address';
        }
        if($itemtable == 'examregistrar_printing') {
            $editor = 'content';
        }
        if($editor) {
            $t = $element->{$editor};
            $f = $element->{$editor.'format'};
            $element->{$editor} = [];
            $element->{$editor}['text'] = $t;
            $element->{$editor}['format'] = $f;
        }
    }
    return $element;
}


function examregistrar_process_addupdate_editable_item($edit, $itemtable, $primaryid, 
                                                        $formdata, $element, $eventdata) {
    global $DB; 

    $data = examregistrar_extract_edititem_formdata($edit, $formdata);

    unset($eventdata['other']['name']);
    if($element) { // this means itemid > 0 and record exists, over-write & update
        $data->id = $element->id;
        if($success = $DB->update_record($itemtable, $data)) {
            $eventdata['objectid'] = $data->id;
            $event = \mod_examregistrar\event\manage_updated::created($eventdata, $itemtable);
            $event->trigger();
        }

    } else {
        if($data->id = $DB->insert_record($itemtable, $data)) {
            $eventdata['objectid'] = $data->id;
            $event = \mod_examregistrar\event\manage_created::created($eventdata, $itemtable);
            $event->trigger();
        }
    }
    
    if($data->id && ($edit == 'exams') && ($itemtable == 'examregistrar_exams')) {
        //some data edited in exams table, manage examdelivery data
        $formdata->bookedsite = examregistrar_get_instance_config($primaryid, 'deliverysite');
        examregistrar_exam_addupdate_delivery_formdata($data->id, $formdata, $eventdata);
    }
    if($data->id && $itemtable == 'examregistrar_locations') {
        examregistrar_set_location_tree($data->id);
    }    
}

function examregistrar_format_delivery_name($cminfo, $withicon = false) {    
    global $OUTPUT;
    
    $name = \html_writer::link($cminfo->url, $cminfo->name); 
    
    if($withicon) {
        $name = $OUTPUT->pix_icon('icon', '', $cminfo->modname).$name;
    }
    if($cminfo->idnumber) {
        $name .= ' ('.$cminfo->idnumber.')';
    }

    return $name;
}

/**
    * Get the list of form elements to repeat, one for each answer.
    * @param object $mform the form being built.
    * @param $repeatedoptions reference to array of repeated options to fill
    * @return array of form fields.
    */
function examregistrar_get_per_delivery_fields($courseid, &$mform, &$repeatedoptions, $withhelper = true, $bookedsite = 0) {    
    $deliveryfields = [];

    if($withhelper) {
        $helpercmidmenu = array('' => array('' => get_string('chooseaparameter', 'examregistrar')),
                                'examregistrar' => array(0 => get_string('modulename', 'examregistrar')),
                                'quiz' => array(),
                                'offlinequiz' => array(),
                                'assign' => array(),
                                );
        
        $cmsinfo = (isset($courseid)) ? get_fast_modinfo($courseid)->cms : [];
        foreach($cmsinfo as $cm) {
            if(array_key_exists($cm->modname, $helpercmidmenu)) {
                $helpercmidmenu[$cm->modname][$cm->id] = examregistrar_format_delivery_name($cm);
            } 
        }
        
        foreach($helpercmidmenu as $key => $value) {
            if($value && $key) {
                $helpercmidmenu[get_string('modulename', $key)] = $value;
            }  
            if($key) {
                unset($helpercmidmenu[$key]);
            }
        }
    }

    $deliveryfields[] =  $mform->createElement('header', 'delivery', get_string('examdeliverymode', 'examregistrar', '{no}') );
    $deliveryfields[] =  $mform->createElement('hidden', 'deliveryid', 0);
    $repeatedoptions['deliveryid']['type'] = PARAM_INT;
    
    if($withhelper) {
        //$deliveryfields[] = $mform->createElement('select', 'helpermod', get_string('helpermod', 'examregistrar'), $deliverymenu);
        //$repeatedoptions['helpermod']['helpbutton'] = array('helpermod', 'examregistrar');        
        
        $deliveryfields[] = $mform->createElement('selectgroups', 'helpercmid', get_string('helpercmid', 'examregistrar'), $helpercmidmenu);
        if($bookedsite) {
            $repeatedoptions['helpercmid']['disabledif'] = array('bookedsite', 'neq', $bookedsite);  
        }
        
        $repeatedoptions['helpercmid']['helpbutton'] = array('helpercmid', 'examregistrar');        
    }
    
    // Open and close dates.
    $deliveryfields[] = $mform->createElement('date_time_selector', 'timeopen', get_string('helpertimeopen', 'examregistrar'),
            array('optional' => true));
    $repeatedoptions['timeopen']['disabledif'] = array('helpercmid', 'eq', '');  //array('timeopen[{no}][enabled]', 'eq', 0);
    $repeatedoptions['timeopen']['helpbutton'] = array('helpertimeopen', 'examregistrar');

    $deliveryfields[] = $mform->createElement('date_time_selector', 'timeclose', get_string('helpertimeclose', 'examregistrar'),
            array('optional' => true));
    $repeatedoptions['timeclose']['disabledif'] = array('helpercmid', 'eq', '');
            
    // Time limit.
    $deliveryfields[] = $mform->createElement('duration', 'timelimit', get_string('helpertimelimit', 'examregistrar'),
            array('optional' => true));
    $repeatedoptions['timelimit']['disabledif'] = array('helpercmid', 'eq', '');
    $repeatedoptions['timelimit']['helpbutton'] = array('helpertimelimit', 'examregistrar');
    
    $parcount = 5;
    $options = examregistrar_get_delivery_parameter_options();
    for ($i=0; $i < $parcount; $i++) {
        $parameter = "parameter_$i";
        $value  = "value_$i";
        $pargroup = "pargoup_$i";
        $group = array(
            $mform->createElement('selectgroups', $parameter, '', $options),
            $mform->createElement('text', $value, '', array('size'=>'12')),
        );
        $deliveryfields[] = $mform->createElement('group', $pargroup, get_string('deliveryparameters', 'examregistrar'), $group, ' ', false);
        //$mform->addGroup($group, $pargroup, get_string('parameterinfo', 'url'), ' ', false);
        $repeatedoptions[$value]['type'] = PARAM_RAW;
    }        
    $repeatedoptions["pargoup_0"]['helpbutton'] = array('deliveryparameters', 'examregistrar');        
    
    return $deliveryfields;
}




/**
 * Get the parameters that may be appended to examdelivery fields
 * @return array array describing opt groups
 */
function examregistrar_get_delivery_parameter_options() {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseaparameter', 'examregistrar'));

    $options[get_string('modulename', 'examregistrar')] = array(
        'printmode'        => get_string('printmode', 'examregistrar'),
        'randomize'        => get_string('randomize', 'examregistrar'),
        
    );

    $options[get_string('modulename', 'assign')] = array(
        'submissiondrafts'              => get_string('submissiondrafts', 'assign'),
        'requiresubmissionstatement'    => get_string('requiresubmissionstatement', 'assign'),
    );

    $options[get_string('modulename', 'quiz')] = array(
        'quizpassword'          => get_string('requirepassword', 'quiz'),
        'attempts'          => get_string('attempts', 'quiz'),
        'questionsperpage'  => get_string('questionsperpage', 'quiz'),
        //'repaginatenow'     => get_string('repaginatenow', 'quiz'),
        'shuffleanswers'     => get_string('shuffleanswers', 'quiz'),
        'shufflequestions' => get_string('shufflequestions', 'quiz'),
    );
    
    $options[get_string('modulename', 'offlinequiz')] = array( 
        'numgroups'          => get_string('numbergroups', 'offlinequiz'),
        'shufflequestions'          => get_string('shufflequestions', 'offlinequiz'),
        'shuffleanswers'          => get_string('shuffleanswers', 'offlinequiz'),
    );

    return $options;
}


/**
 * Returns a collection of examdelivery objects
 *
 * @param int $examid 
 * @param object $element exam element record
 * @return void of properly built examdelivery records
 */
function examregistrar_exam_add_deliverymodes(&$element) {
    global $DB;
    
    if($deliveryexams = $DB->get_records('examregistrar_examdelivery', array('examid' => $element->id))) {
        $element->deliveryid = [];
        $element->helpercmid = [];
        $element->timeopen = [];
        $element->timeclose = [];
        $element->timelimit = [];
        $parcount = 5;
        for ($i=0; $i < $parcount; $i++) {
            $element->{"parameter_$i"} = [];
            $element->{"value_$i"} = [];
        }
        
        foreach($deliveryexams as $delivery) {
            $element->deliveryid[] = $delivery->id;
            $element->helpercmid[] = $delivery->helpercmid;
            $element->timeopen[] = $delivery->timeopen;
            $element->timeclose[] = $delivery->timeclose;
            $element->timelimit[] = $delivery->timelimit;
            $parameters = unserialize($delivery->parameters);
            if(is_array($parameters)) {
                $i = 0;
                foreach($parameters as $parameter => $value) {
                    $element->{"parameter_$i"}[] = $parameter;
                    $element->{"value_$i"}[] = $value;
                    $i++;
                }
            }
        }
    }
}


/**
 * Performs processing of exams table setdelivery action 
 * Stores time and common data for all exams defined in items array 
 *
 * @param int $examid 
 * @param object $formdata data submitted in an item-editing form
 * @param array $eventdata fo logging
 * @return void of properly built examdelivery records
 */
function examregistrar_generate_delivery_formdata($examregprimaryid, &$formdata, $eventdata) {
    global $CFG, $DB; 

    $config = examregistrar_get_instance_config($examregprimaryid, 'quizexamprefix, assignexamprefix, deliverysite');
    $likecmidnumber = $DB->sql_like('cm.idnumber', ':idnumber'); 
    list($insql, $params) = $DB->get_in_or_equal($formdata->items, SQL_PARAMS_NAMED, 'exam');
    $params['helpermod'] = $formdata->helpermod;
    $prefix =  $formdata->helpermod.'examprefix';
    $params['idnumber'] = $config->$prefix;
    $params['idnumber'] .= '%';
    $sql = "SELECT cm.id AS cmid, e.id AS examid, e.examregid, e.courseid, cm.idnumber
              FROM {examregistrar_exams e}
              JOIN {course_modules} cm ON cm.course = e.courseid  
              JOIN {modules} m ON m.id = cm.modules AND m.name = :helpermod
              LEFT JOIN {examregistrar_examdelivery} d ON e.id = ed.examid AND ed.helpermod = :helpermod
              WHERE e.id $insql AND $likecmidnumber AND cm.score > 0 AND ed.id IS NULL  ";
    
    $candidates = $DB->get_records_sql($sql, $params);
    // now eliminate those not relevant for exam period, session
    foreach($candidates as $cmid => $exam) {
        if(!examregistrar_decode_examquiz_idnumber($exam->examregid, $exam->courseid, $exam->idnumber, $exam->examid)) {
            unset($candidates[$cmid]);
        } 
    }
    
    // if there are candidates, add them  
    $added = [];
    foreach($candidates as $cmid => $exam) {
        $data = clone $formdata;
        $data->helpercmid[0] = $cmid; 
        $data->courseid = $exam->courseid;
        $data->deliveryid[0] = ''; 
        $data->bookedsite = $config->deliverysite;
        $deliveries = examregistrar_exam_addupdate_delivery_formdata($exam->examid, $data, clone $eventdata);
        foreach($deliveries  as $delivery) {
            $added[] = $delivery->examid;
        }
        unset($data);
    }
 
    if($added) {
        \core\notification::add(get_string('addeddeliveryhelper', 'examregistrar', count($added)), 
                            \core\output\notification::NOTIFY_SUCCESS);    
    }
 
    // remove added from formdata list
    $formdata->items = array_diff($formdata->items, $added);
}

/**
 * Performs processing of exams table setdelivery action 
 * Stores time and common data for all exams defined in items array 
 *
 * @param int $examid 
 * @param object $formdata data submitted in an item-editing form
 * @param array $eventdata fo logging
 * @return void of properly built examdelivery records
 */
function examregistrar_process_setdelivery_formdata($examregprimaryid, $formdata, $eventdata) {
    global $CFG, $DB; 
    
    list($insql, $params) = $DB->get_in_or_equal($formdata->items, SQL_PARAMS_NAMED, 'exam');
    $select = "helpermod = :quiz AND bookedsite = :deliverysite AND examid $insql ";
    $params['quiz'] = 'quiz';
    $params['deliverysite'] = examregistrar_get_instance_config($examregprimaryid, 'deliverysite');

    $numexams = $DB->count_records_select('examregistrar_examdelivery', $select, $params); 
    
    if($numexams) {
        $updates = [];
        foreach(['timeopen', 'timeclose', 'timelimit'] as $field) {
            if($formdata->$field[0]) {
                $updates[$field] = $formdata->$field[0];
            }
        }
        if($parameters = examregistrar_pack_delivery_parameters($formdata, 0)) {
            $updates['parameters'] = $parameters;
        }
        
        foreach($updates as $field => $newvalue) {
            $DB->set_field_select('examregistrar_examdelivery', 
                                    $field, $newvalue, $select, $params); 
        }
    }
        
    $type = $numexams ? \core\output\notification::NOTIFY_SUCCESS : 
                        \core\output\notification::NOTIFY_WARNING;
    \core\notification::add(get_string('updateddeliverdata', 'examregistrar', $numexams), $type);
 
    if($numexams) {
        $eventdata['other']['action'] = 'setdeliverdata';
        $eventdata['other']['extra'] = implode(',', $items);
        $eventdata['other']['examregid'] = $examregprimaryid;
        $event = \mod_examregistrar\event\manage_action::create($eventdata);
        $event->trigger();  
    }
    return $numexams;
}

function examregistrar_pack_delivery_parameters($formdata, $index) {

    $parcount = 5;
    $parameters = [];
    foreach(range(0, $parcount-1) as $i) {
        $parameter = "parameter_$i";
        $value = "value_$i";
        if(!empty($formdata->{$parameter}[$index]) && isset($formdata->{$value}[$index])) {
            $parameters[$formdata->$parameter[$index]] = $formdata->$value[$index];
            $delivery->{$formdata->$parameter[$index]} = $formdata->$value[$index];
        }
    }
    if(!empty($parameters)) {
        $parameters = serialize($parameters);
    } else {
        $parameters = '';
    }

    return $parameters;
}


/**
 * Performs updating of examdelivery table 
 *   and updates helpermod instances  (both time settings and makeexamlock for quizzes, for instance)
 *
 * @param int $examid 
 * @param object $formdata data submitted in an item-editing form
 * @param array $eventdata fo logging
 * @return void of properly built examdelivery records
 */
function examregistrar_exam_addupdate_delivery_formdata($examid, $formdata, $eventdata, $update = true) {
    global $CFG, $DB; 
    
    $deliveryexams = [];
    $deliverymods = [];
    
    foreach(range(0, $formdata->deliver_repeats - 1) as $index) {
        if(isset($formdata->helpercmid[$index]) && ($formdata->helpercmid[$index] || $formdata->deliveryid[$index])) {
            $cminfo = get_fast_modinfo($formdata->courseid)->cms[$formdata->helpercmid[$index]];
            if(empty($cminfo)) {
                continue; // do not add if module do not exists
            }
            
            $delivery = new stdClass();
            $delivery->examid = $examid;
            $delivery->helpermod = $cminfo->modname;
            $deliverymods[$cminfo->modname] = $cminfo->modname;
            $delivery->deliveryid = $formdata->deliveryid[$index];
            $delivery->status = 0;
            $delivery->instanceid = $cminfo->instance;
            $delivery->component = '';
            // TODO must be dependent on delivery type & plugin
            $delivery->bookedsite = $formdata->bookedsite;
            
            
            if($formdata->deliveryid[$index]) {
                $delivery->id = $formdata->deliveryid[$index];
            }
            if($formdata->helpercmid[$index]) {
                $delivery->helpercmid = $formdata->helpercmid[$index];
            }
            if($formdata->timeopen[$index]) {
                $delivery->timeopen = $formdata->timeopen[$index];
            }
            if($formdata->timeclose[$index]) {
                $delivery->timeclose = $formdata->timeclose[$index];
            }
            if($formdata->timelimit[$index]) {
                $delivery->timelimit = $formdata->timelimit[$index];
            }
            
            $delivery->parameters = examregistrar_pack_delivery_parameters($formdata, $index);
            
            $deliveryexams[$index] = $delivery;
        }
    }
    
    // TODO  // TODO  // TODO  // TODO  // TODO  // TODO  
    // perform includes here, modify when plugind    
    foreach($deliverymods as $helpermod) {
        include_once($CFG->dirroot . '/mod/' . $helpermod . '/lib.php');
        if($helpermod == 'quiz') {
        include_once($CFG->dirroot . '/mod/quiz/accessmanager.php');
        }
    }
    
    $itemtable = 'examregistrar_examdelivery';
    foreach($deliveryexams as $delivery) {
        // first get database records inplace & log DB manipulation
        if(isset($delivery->id) && $delivery->id) {
            if($success = $DB->update_record($itemtable, $delivery)) {
                $eventdata['objectid'] = $delivery->id;
                $event = \mod_examregistrar\event\manage_updated::created($eventdata, $itemtable);
                $event->trigger();
            }        
        
        } elseif($delivery->helpermod) {
            if($delivery->id = $DB->insert_record($itemtable, $delivery)) {
                $delivery->deliveryid = $delivery->id;
                $eventdata['objectid'] = $delivery->id;
                $event = \mod_examregistrar\event\manage_created::created($eventdata, $itemtable);
                $event->trigger();
            }
        }
        
        // now manipulate and update helper mod
        examregistrar_delivery_update_helper_instance($delivery, $formdata->courseid, $update);

    }
    
    return $deliveryexams;
}



/**
 *  //This is done with class polymorfirsm when delivery plugins
 *
 * @param delivery $delivery record from examdelivery table 
 *
 * @return 
 */
function examregistrar_delivery_update_helper_instance($delivery, $courseid = 0, $update = true) {
    global $CFG, $DB;

    // ensure we have a module instanceid
    if(!isset($delivery->instanceid) || !$delivery->instanceid) {
        if(!$courseid) {
            $courseid = $DB->get_field('examregistrar_exams', 'courseid', ['id' => $delivery->examid]);
        }
    
        $cminfo = get_fast_modinfo($courseid)->cms[$delivery->helpercmid];
        if(empty($cminfo)) {
            return false;
        }
        $delivery->instanceid = $cminfo->instance;    
    }

    $modinstance = $DB->get_record($delivery->helpermod, ['id' => $delivery->instanceid]);
    
    if(empty($modinstance)) {
        return false;
    }
    
    $modinstance->coursemodule = $delivery->helpercmid;
    $modinstance->instance = $delivery->instanceid;

    if(!empty($delivery->parameters)) {
        $parameters = unserialize($delivery->parameters);
        foreach($parameters as $param => $value) {
            $modinstance->$param = $value;
        }
    }
    
    include_once($CFG->dirroot . '/mod/' . $delivery->helpermod . '/lib.php');
    if($delivery->helpermod == 'quiz') {
        include_once($CFG->dirroot . '/mod/quiz/accessmanager.php');    
    }
    
    // TODO  // TODO  // TODO  // TODO  
    //This is done with class polymorfirsm when delivery plugins
        $DB->set_field('examregistrar_exams', $delivery->helpermod.'plugincm', 
                            $delivery->helpercmid, ['id' => $delivery->examid]);   
    if($delivery->helpermod == 'assign') {
        if($update) {
            if($delivery->timeopen) {
                $modinstance->allowsubmissionsfromdate  = $delivery->timeopen;
            }
            if($delivery->timeclose) {
                // this ensures always  cutoff > duedate
                $cutoff = $delivery->timeopen + $delivery->timelimit;
                $modinstance->duedate = min($delivery->timeclose, $cutoff);
                $modinstance->cutoffdate = max($delivery->timeclose, $cutoff);
            }
        }
    
        assign_update_instance($modinstance, null);
    }

    if($delivery->helpermod == 'quiz') {
        $modinstance->quizpassword = $modinstance->password;
        $accesssettings = quiz_access_manager::load_settings($delivery->instanceid);
        foreach ($accesssettings as $name => $value) {
            $modinstance->$name = $value;
        }            

        if($update) {
            if($delivery->timeopen) {
                $modinstance->timeopen = $delivery->timeopen;
            }
            if($delivery->timeclose) {        
                $modinstance->timeclose = $delivery->timeclose;
            }
            if($delivery->timelimit) {
                $modinstance->timelimit = $delivery->timelimit;
            }
        }
        $modinstance->makeexamlock = $delivery->examid;    
        
        if(isset($modinstance->questionsperpage)) {
            $modinstance->repaginatenow = 1;
        } 
        quiz_update_instance($modinstance, null);
    }
    
    if($delivery->helpermod == 'offlinequiz') {
        $modinstance->time = $delivery->timeopen;
        
        offlinequiz_update_instance($modinstance);
    }
    
    return true;
}

/**
 * 
 *
 * @param object $exam exam record from database 
 * @param bool $withdate to indicate if the start date should be appended
 * @return 
 */
function examregistrar_exam_delivery_instances($exam, $manageurl, $withdate = false) {
    global $DB, $OUTPUT; 
    
    $deliveryexams = $DB->get_records('examregistrar_examdelivery', array('examid' => $exam->id));
    
    $deliveryinstances = [];
    $strdelete = get_string('delete');
    foreach($deliveryexams as $delivery) {
        $cminfo = get_fast_modinfo($exam->courseid)->cms[$delivery->helpercmid]; 
        $name = examregistrar_format_delivery_name($cminfo, true);
        
        $url = new moodle_url($manageurl, array('deldel'=>$delivery->id));
        $button = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete)));
        
        $name .= $button;
        
        $date = '';
        if($withdate) {
            $timeformat = get_string('strftimedaydatetime');
            if($delivery->timeopen && (usergetmidnight($delivery->timeopen) ==  usergetmidnight($exam->examdate))) {
                $timeformat = get_string('strftimetime24', 'langconfig');
            }
            
            if($delivery->timeopen) {
                $date = userdate($delivery->timeopen, $timeformat);
                if($delivery->timelimit) {
                    $date .= '; '.format_time($delivery->timelimit);
                }
                $date = \html_writer::div($date, ' reduced ');
            }
        }
        $deliveryinstances[$delivery->id] =  \html_writer::span($name. $date, 'deliveryitem');
    }
    
    if(count($deliveryinstances) > 1) {
        return \html_writer::alist($deliveryinstances, ['class' => 'deliveryinstances']);
    }  
    
    return reset($deliveryinstances);
}


/**
 * Checks if exam is an extracall (call < 1) 
 *   and return existing delivery mode helper data to apply user overrides, if needed
 *
 * @param object $delivery record from database with instanceid 
 * @param int $userid the user to apply override to
 * @return array of examdelivery table records
 */
function examregistrar_exam_has_extracall_delivery($examid, $bookedsite) {
    global $DB; 
    
    $sql = "SELECT d.*        
              FROM {examregistrar_examdelivery} d
              JOIN {examregistrar_exams} e ON d.examid = e.id
             WHERE d.examid = :examid AND e.callnum < 0 
                    AND d.bookedsite = :bookedsite AND d.helpercmid > 0 ";
    $params = ['examid' => $examid, 'bookedsite' => $bookedsite];
                
    return $DB->get_records_sql($sql, $params);
 }


/**
 * Add user override on delivery helper instance if applicable 
 *
 * @param object $delivery record from database with instanceid 
 * @param int $userid the user to apply override to
 * @return void of properly built examdelivery records
 */
function examregistrar_process_exam_delivery_user_override($delivery, $userid) {
    global $DB;
    
    if(!isset($delivery->helpermod) || !$delivery->helpermod || 
                (($delivery->helpermod != 'quiz') && ($delivery->helpermod != 'assign'))) {
        return;
    }
    
    if(!isset($delivery->instanceid)) {
        $delivery->instanceid = $DB->get_field('course_modules', 'instance', ['id' => $delivery->helpermod]);
    }
    
    $context = context_module::instance($delivery->helpercmid);
    // Set the common parameters for one of the events we may be triggering.
    $eventdata = array(
        'context' => $context,
        'other' => array(
            $delivery->helpermod.'id' => $delivery->instanceid,
        ),
        'relateduserid' => $userid,
    );    
    
    $done = '';
    if($delivery->helpermod == 'quiz') {
        if(!$override = $DB->get_record('quiz_overrides', ['quiz' => $delivery->instanceid, 'userid' => $userid])) {
            $override = new stdClass();
            $override->quiz = $delivery->instanceid;
            $override->userid = $userid;
        }
        if(isset($delivery->timeopen) && $delivery->timeopen) {
            $override->timeopen = $delivery->timeopen;
        }
        if(isset($delivery->timeclose) && $delivery->timeclose) {
            $override->timeclose = $delivery->timeclose;
        }
        if(isset($delivery->timelimit) && $delivery->timelimit) {
            $override->timelimit = $delivery->timelimit;
        }
        $parameters = unserialize($delivery->parameters);
        if(isset($parameters['quizpassword'])) {
            $override->password = $delivery->$parameters['quizpassword'];
        }
        if(isset($parameters['attempts'])) {
            $override->attempts = $delivery->$parameters['attempts'];
        }
    } elseif($delivery->helpermod == 'assign') {
        if(!$override = $DB->get_record('assign_overrides', ['assignid' => $delivery->instanceid, 'userid' => $userid])) {
            $override = new stdClass();
            $override->assignid = $delivery->instanceid;
            $override->userid = $userid;
        }
        if(isset($delivery->timeopen) && $delivery->timeopen) {
            $override->allowsubmissionsfromdate = $delivery->timeopen;
        }
        if(isset($delivery->timeclose) && $delivery->timeclose) {
            $override->cutoffdate = $delivery->timeclose;
        }
        if(isset($delivery->timelimit) && $delivery->timelimit) {
            $override->duedate = $delivery->timeopen + $delivery->timelimit;
        }
    }
    
    if(isset($override->userid)) {
        $table = $delivery->helpermod.'_overrides';
        if(isset($override->id)) {
            if($DB->update_record($table, $override)) {
                $done = 'updated';
            }
        } else {
            if($override->id = $DB->insert_record($table, $override)) {
                $done = 'created';
            }
        }    
    }
    
    // Trigger the override created event.
    if($override->id && $done) {
        $eventclass = '\mod_'.$delivery->helpermod.'\event\user_override_'.$done;
        $eventdata['objectid'] = $override->id;
        $event = $eventclass::create($eventdata);
        $event->trigger();
    }    
    
}

/////////////////////////////////////////////////////////////////////


/**
 * Validates data imported fron CSV file. Checks table colums & existing tdata
 *
 * @param object $examregistrar object
 * @param string $table table against to check records
 * @param array $record a processed data line from the CSV file, associative array with column names as keys
 * @param bool $ignoremodified
 * @return array validdata, update
 */
function examregistrar_validate_csvuploaded_data($examregistrar, $table, $record, $ignoremodified) {
    global $DB, $USER;

    $update = false;
    $data = new stdClass;
    $examregid = examregistrar_get_primaryid($examregistrar);

    $tablecolumns = $DB->get_columns('examregistrar_'.$table);
    $validcolumns = array_diff($tablecolumns, array('id', 'examregid', 'component', 'modifierid', 'timemodified'));

    if(!$validcolumns) {
        return array(false, false);
    }

    foreach($validcolumns as $col) {
        if(isset($record[$col])) {
            $item = $record[$col];

            if(!$DB->record_exists('examregistrar_elements', array('examregid'=>$examregid, 'type'=>'', 'idnumber'=>$item ))) {
            }
        }
    }

    $data->examregid = $examregid;
    $data->component = '';
    $data->modifierid = $USER->id;
    $data->timemodified = time();

    return array($data, $update);
}


/**
 * Validates csv uploaded data and verifies if is new or existing record
 *
 * @param array $data csv imported record
 * @param string $table the database table to verify on
 * @param array $uniquefields the fields used to verify the record exists in the DB (this combination is unique)
 * @param array $requiredfields field that are mandatory in $data record for succesfully storing data in DB
 * @param array $additionalfields other optional fileds in DB
 * @param bool $ignoremodified whether update existing data or not
 * @return array validdata, update
 */
function examregistrar_loadcsv_updaterecordfromrow($data, $table, $uniquefields, $requiredfields, $additionalfields, $ignoremodified) {
    global $DB, $USER;

    $record = new stdClass;
    foreach($uniquefields as $field) {
        $record->$field = $data[$field];
    }
    $params = get_object_vars($record);
    // do not add any other param before, or conflict with table names
    if(isset($record->idnumber)) {
        $record->idnumber = clean_param($record->idnumber, PARAM_ALPHANUMEXT);
    }

    $update = false;
    if($oldrecord = $DB->get_record('examregistrar_'.$table, $params)) {
        //we are updating
        if($ignoremodified) {
            // updating, if we are ignoring proposed changes, we won't update
            return array();
        }
        $update = true;
        $record = clone $oldrecord;
    }
    //we are inserting or we are allowed to update
    // add the remaining fields
    $fields = array_diff(array_merge($requiredfields, $additionalfields), $uniquefields);
    foreach($fields as $field) {
        if(isset($data[$field])) {
            $record->$field = $data[$field];
        }
    }

    $record->component = '';
    $record->modifierid = $USER->id;
    $record->timemodified = time();

    return array($record, $update);
}



/**
 * Checks if an object field contains a valid element idnumber
 *
 * @param stdClass $record csv imported record
 * @param string $field record field
 * @param string $elementtype  the element type this field should be related to
 * @param bool $editelements permission to whether update element data or not,
 * @param bool $ignoremodified whether update existing data or not
 * @param bool $neverupdate permission to disallow insert/update if not appropiate
 * @return array validdata, update
 */
function examregistrar_loadcsv_elementscheck($record, $field, $elementtype, $ignoremodified, $editelements, $neverupdate=false) {
    global $DB;

    $eventdata = array();
    //$eventdata['objecttable'] = 'examregistrar_elements';
    list($course, $cm) = get_course_and_cm_from_instance($record->examregid, 'examregistrar');
    $context = context_module::instance($cm->id);
    $eventdata['context'] = $context;
    $eventdata['other'] = array('edit'=>'elements');
    
    $elementid = 0;
    /// now integrity checks
    if(!$element = $DB->get_record('examregistrar_elements', array('examregid'=>$record->examregid, 'idnumber'=>$record->$field, 'type'=>$elementtype))) {
        if($editelements && !$neverupdate) {
            $element = clone $record;
            $element->type = $elementtype;
            if(isset($element->id)) {
                unset($element->id);
            }
            if($elementid = $DB->insert_record('examregistrar_elements', $element)) {
                //$eventdata['objectid'] = $element->id;
                $event = \mod_examregistrar\event\manage_created::created($eventdata);
                $event->trigger();
            }
        } else {
            return false;
        }
    } else {
        if(!$ignoremodified && $editelements && !$neverupdate) {
            //$DB->set_field('examregistrar_elements', 'name', $record->name; array('id'=>$element->id));
            $eid = $element->id;
            $element = clone $record;
            $element->type = $elementtype;
            $element->id = $eid;
            if($DB->update_record('examregistrar_elements', $element)) {
                //$eventdata['objectid'] = $element->id;
                //$eventdata['objecttable'] = 'examregistrar_elements';
                $event = \mod_examregistrar\event\manage_updated::created($eventdata);
                $event->trigger();
            }
        } else {
            //do nothing, Do not abort, allow loading csv row without updating elements
        }
        $elementid = $element->id;
    }
           
    
    
    return $elementid;
}



/**
 * Validates uploaded main elements data and stores in DB
 *
 * @param object $examregistrar object
 * @param array $data record the uploaded row
 * @param bool $ignoremodified whether update existing data or not
 * @return void or error message
 */
function examregistrar_loadcsv_elements($examregistrar, $data, $ignoremodified) {
    global $DB, $USER, $EXAMREGISTRAR_ELEMENTTYPES;

    $uniquefields = array('examregid', 'idnumber', 'type');
    $requiredfields = array('name', 'idnumber', 'type');
    $additionalfields = array('value', 'visible');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $data['examregid'] = $examregprimaryid;

    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'elements', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data['name'];
    }

    if(!in_array($record->type, $EXAMREGISTRAR_ELEMENTTYPES)) {
        return '  ignore updating: '.$data['type'];
    }
        
    return examregistrar_saveupdate_csvloaded_item($record, 'elements', $update);
}

/**
 * Validates uploaded room/venue data and stores in DB
 *
 * @param object $record, the item to sve or update 
 * @param string $table the data table where to put data
 * @param bool $update, save or update whether update existing data or not
 * @return mixed item string if error int > 0 insert,  < 0 update
 */
function examregistrar_saveupdate_csvloaded_item($record, $table, $update = false) {
    global $DB; 

    $item = false;
    $table = 'examregistrar_'.$table;
    if($update) {
        if($DB->update_record($table, $record)) {
            $item = -($record->id);
        }
    } else {
        $item = $DB->insert_record($table, $record);
    }

    return $item;
}


/**
 * Validates uploaded room/venue data and stores in DB
 *
 * @param object $examregistrar object
 * @param array $data record the uploaded row
 * @param bool $ignoremodified whether update existing data or not
 * @param bool $editelements whether update/insert new data on elements table
 * @return void or error message
 */
function examregistrar_loadcsv_locations($examregistrar, $data, $ignoremodified, $editelements=false) {
    global $DB, $USER;

    $message = '';
    $uniquefields = array('examregid', 'location', 'locationtype');
    $requiredfields = array('name', 'idnumber', 'locationtype', 'parent', 'parenttype');
    $additionalfields = array('seats', 'address', 'sortorder', 'visible');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $data['examregid'] = $examregprimaryid;

    $record = new stdclass;
    $record->examregid = $examregprimaryid;
    foreach($requiredfields as $field) {
        $record->$field = isset($data[$field]) ? $data[$field] : '';
    }


    /// now integrity checks
    if(!$data['location'] = examregistrar_loadcsv_elementscheck($record, 'idnumber', 'locationitem', $ignoremodified, $editelements)) {
        return ' not allowed to insert location: '.$data['name'];
    }
    if(!$data['locationtype'] = examregistrar_loadcsv_elementscheck($record, 'locationtype', 'locationtypeitem',  true, false, true)) {
        return ' not allowed to insert locationtype: '.$data['name'];
    }

    $parent = 0;
    if($record->parent) {
        $parent = examregistrar_loadcsv_elementscheck($record, 'parent', 'locationitem',  true, false, true);
        $parenttype = examregistrar_loadcsv_elementscheck($record, 'parenttype', 'locationtypeitem',  true, false, true);
        if(!$parent = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'location'=>$parent, 'locationtype'=>$parenttype))) {
            $parent = 0;
        }
    }
    $data['parent'] = $parent;

    /// now construct the true table record
    //$requiredfields = array('seats');
    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'locations', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data['name'];
    }
    
    if(isset($record->address) && $record->address) {
        $record->addressformat = 1;
        $record->address = format_text($record->address, $record->addressformat, array('filter'=>false, 'para'=>false));
    }

    if(isset($record->visible)) {
        $record->visible = (int)$record->visible;
    }

    if($record->id = examregistrar_saveupdate_csvloaded_item($record, 'locations', $update)) {
        examregistrar_set_location_tree($record->id);
    }

    return $record->id;
}


/**
 * Validates uploaded session data and and stores in DB
 *
 * @param object $examregistrar object
 * @param array $data record the uploaded row
 * @param bool $ignoremodified wether update existing data or not
 * @param bool $editelements whether update/insert new data on elements table
 * @return void or error message
 */
function examregistrar_loadcsv_sessions($examregistrar, $data, $ignoremodified, $editelements=false) {
    global $DB, $USER;

    $message = '';
    $uniquefields = array('examregid', 'examsession', 'period');
    $requiredfields = array('name', 'idnumber', 'period', 'annuality' );
    $additionalfields = array('examdate', 'timeslot', 'visible');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $data['examregid'] = $examregprimaryid;

    $record = new stdclass;
    $record->examregid = $examregprimaryid;
    foreach($requiredfields as $field) {
        $record->$field = $data[$field];
    }

    /// now integrity checks
    if(!$data['examsession'] = examregistrar_loadcsv_elementscheck($record, 'idnumber', 'examsessionitem', $ignoremodified, $editelements)) {
        return ' not allowed to insert examsessionitem: '.$data['name'];
    }
    if(!$period = examregistrar_loadcsv_elementscheck($record, 'period', 'perioditem', true, false, true)) {
        return ' not allowed to insert perioditem: '.$data['name'];
    }
    if(!$annuality = examregistrar_loadcsv_elementscheck($record, 'annuality', 'annualityitem', true, false, true)) {
        return ' not allowed to insert annualityitem: '.$data['name'];
    }

    if(!$period = $DB->get_field('examregistrar_periods', 'id', array('examregid'=>$examregprimaryid, 'period'=>$period, 'annuality'=>$annuality))) {
        return ' invalid period  '.$data['period'].'  at annuality '.$data['annuality'];
    }
    $data['period'] = $period;

    /// now construct the true table record
    $requiredfields = array('period');
    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'examsessions', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data['name'];
    }

    /// now specific items
    $tz = usertimezone();
    $record->examdate = strtotime($record->examdate.' '.$tz);
    if(isset($record->visible)) {
        $record->visible = (int)$record->visible;
    }
    if(!isset($record->duration)) {
        $record->duration = 2*60*60;
    }

    return examregistrar_saveupdate_csvloaded_item($record, 'examsessions', $update);
}


/**
 * Validates uploaded period data and and stores in DB
 *
 * @param object $examregistrar object
 * @param array $data record the uploaded row
 * @param bool $ignoremodified wether update existing data or not
 * @param bool $editelements whether update/insert new data on elements table
 * @return void or error message
 */
function examregistrar_loadcsv_periods($examregistrar, $data, $ignoremodified, $editelements=false) {
    global $DB, $USER;

    $message = '';
    $uniquefields = array('examregid', 'period', 'annuality');
    $requiredfields = array('name', 'idnumber', 'annuality', 'periodtype', 'term' );
    $additionalfields = array('calls', 'timestart', 'timeend', 'visible');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $data['examregid'] = $examregprimaryid;

    $record = new stdclass;
    $record->examregid = $examregprimaryid;
    foreach($requiredfields as $field) {
        $record->$field = $data[$field];
    }

    /// now integrity checks
    if(!$data['period'] = examregistrar_loadcsv_elementscheck($record, 'idnumber', 'perioditem', $ignoremodified, $editelements)) {
        return ' not allowed to insert perioditem: '.$data['name'];
    }

    if(!$data['annuality'] = examregistrar_loadcsv_elementscheck($record, 'annuality', 'annualityitem', true, false, true)) {
        return ' not allowed to insert annualityitem: '.$data['name'];
    }
    if(!$data['periodtype'] = examregistrar_loadcsv_elementscheck($record, 'periodtype', 'periodtypeitem', true, false, true)) {
        return ' not allowed to insert periodtypeitem: '.$data['name'];
    }
    if(!$data['term'] = examregistrar_loadcsv_elementscheck($record, 'term', 'termitem', true, false, true)) {
        return ' not allowed to insert termitem: '.$data['name'];
    }

    /// now construct the true table record
    $requiredfields = array('annuality', 'periodtype', 'term' );
    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'periods', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data['name'];
    }

    /// now specific items
    $tz = usertimezone();
    $record->timestart = strtotime($record->timestart.' '.$tz);
    $record->timeend = strtotime($record->timeend.' '.$tz);
    if(isset($record->visible)) {
        $record->visible = (int)$record->visible;
    }

    return examregistrar_saveupdate_csvloaded_item($record, 'periods', $update);
}


/**
 * Validates uploaded period data and and stores in DB
 *
 * @param object $examregistrar object
 * @param int $examsession the ID of the exam sesion this allocation data applies to
 * @param array $data record the uploaded row
 * @param bool $ignoremodified wether update existing data or not
 * @param bool $editelements whether update/insert new data on elements table
 * @return void or error message
 */
function examregistrar_loadcsv_staffers($examregistrar, $examsession, $data, $ignoremodified, $editelements=false) {
    global $DB, $USER;

    $message = '';
    $uniquefields = array('examsession', 'userid', 'locationid', 'role');
    $requiredfields = array('examsession', 'userid', 'locationid', 'role');
    $additionalfields = array('examregid', 'info', 'visible'); // examregid used by helper functions

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);


    $userid = 0;
    if(isset($data['idnumber'])) {
        $field = 'idnumber';
    } elseif(isset($data['username'])) {
        $field = 'username';
    }
    if(!$userid = $DB->get_field('user', 'id', array($field=>$data[$field]))) {
        return " Not found user $field: ".$data[$field];
    }

    if(!$roomid = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$data['room'], 'locationtype'=>$data['locationtype']))) {
        return ' invalid room  '.$data['room'].'  for type  '.$data['locationtype'];
    }
    $data['locationid'] = $roomid;
    $data['userid'] = $userid;
    $data['examsession'] = $examsession;
    $data['examregid'] = $examregprimaryid;

    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'staffers', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data[$field];
    }

    /// now integrity checks
    if(!examregistrar_loadcsv_elementscheck($record, 'role', 'roleitem', true, false, true)) {
        return ' not allowed to insert roleitem: '.$data['role'];
    }

    /// now specific items
    if(isset($record->visible)) {
        $record->visible = (int)$record->visible;
    }

    return examregistrar_saveupdate_csvloaded_item($record, 'staffers', $update);
}


/**
 * Validates uploaded data and perform seat allocations in designed rooms for exams
 *
 * @param object $examregistrar object
 * @param int $examsession the ID of the exam sesion this allocation data applies to
 * @param array $seatassigns the uploaded movement rules
 * @param bool $ignoremodified
 * @return array validdata, update
 */
function examregistrar_loadcsv_roomallocations($examregistrar, $examsession, $seatassigns) {
    global $DB, $USER;

    $validfields = array('city', 'num','shortname','fromoom','toroom');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $success = array();
    $fail = array();

/*
    // first a round to check that theer are no errors.
    // Better do not operate at all if theer are errors that will truncate execution in midterm
    foreach($seatassigns as $csvassign) {
        $allocation = new stdClass;
        if($csvassign['city']) {
            $allocation->bookedsite = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['city'], MUST_EXIST));
        }
        if($csvassign['fromroom']) {
            $allocation->fromroom = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['fromroom'], MUST_EXIST));
        }
        if($csvassign['toroom']) {
            $allocation->toroom = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['toroom'], MUST_EXIST));
        }
        if($csvassign['shortname']) {
            $allocation->fromexam = $DB->get_field('examregistrar_exams', 'id', array('examregid'=>$examregprimaryid, 'examsession'=>$examsession,
                                                                                      'annuality'=>$examregistrar->annuality, 'shortname'=>$csvassign['shortname'], MUST_EXIST));
        }
    }
*/
    // if we are here, there are no errors in uploaded data, proceed to execution
    foreach($seatassigns as $csvassign) {
        $allocation = new stdClass;

        if($csvassign['city']) {
            $allocation->bookedsite = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['city']));
            if(!$allocation->bookedsite || !$DB->record_exists('examregistrar_session_seats', array('examsession'=>$examsession, 'bookedsite'=>$allocation->bookedsite))) {
                $fail[] = ' invalid booked site for this exam session: '.$csvassign['city'];
                continue;
            }
        } else {
            $allocation->bookedsite = 0;
        }

        if($csvassign['num'] == '' || $csvassign['num'] == 'all' || $csvassign['num'] == 'any' || $csvassign['num'] == -1) {
            $allocation->numusers = -1;
        } else {
            $allocation->numusers = (int)$csvassign['num'];
        }

        if($csvassign['fromroom']) {
            $allocation->fromroom = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['fromroom']));
            if(!$allocation->fromroom || !$DB->record_exists('examregistrar_session_rooms', array('examsession'=>$examsession, 'roomid'=>$allocation->fromroom, 'available'=>1))) {
                $fail[] = ' invalid room for this exam session: '.$csvassign['fromroom'];
                continue;
            }
        } else {
            $allocation->fromroom = 0;
        }

        if($csvassign['toroom']) {
            $allocation->toroom = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['toroom']));
            if(!$allocation->toroom || !$DB->record_exists('examregistrar_session_rooms', array('examsession'=>$examsession, 'roomid'=>$allocation->toroom, 'available'=>1))) {
                $fail[] = ' invalid room for this exam session: '.$csvassign['toroom'];
                continue;
            }
        } else {
            $allocation->toroom = 0;
        }

        if($csvassign['shortname']) {
            $allocation->fromexam = $DB->get_field('examregistrar_exams', 'id', array('examregid'=>$examregprimaryid, 'examsession'=>$examsession,
                                                                                      'annuality'=>$examregistrar->annuality, 'shortname'=>$csvassign['shortname']));
            if(!$allocation->fromexam) {
                $fail[] = ' invalid exam for this session: '.$csvassign['shortname'];
                continue;
            }
        } else {
            $allocation->fromexam = 0;
        }

        if($allocation->bookedsite && $allocation->numusers && $allocation->fromexam && ($allocation->fromroom != $allocation->toroom)) {
            $params = array('examsession'=>$examsession, 'bookedsite'=>$allocation->bookedsite,
                            'examid'=>$allocation->fromexam, 'roomid'=>$allocation->fromroom, 'additional'=>0 );
            if($allocation->numusers < 0) {
                $allocation->numusers = 0;
            }
            $sort = ($allocation->fromroom) ? ' id DESC ' : ' id ASC';
            if(examregistrar_update_usersallocations($examsession, $allocation->bookedsite, $params, $allocation->toroom, $sort, 0, $allocation->numusers)) {
                $success[] = " Moved {$csvassign['city']} - {$csvassign['shortname']} ";
            } else {
                $fail[] = " NOT moved {$csvassign['city']} - {$csvassign['shortname']} ";
            }
        } else {
            $fail[] = " NOT moved {$csvassign['city']} - {$csvassign['shortname']} ";
        }
    }

    return implode('<br />', $success). implode('<br />', $fail);
}


////////////////////////////////////////////////////////////////////////////////
// Locations && venues management                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Sets hierarchy data (parent, depth, path) for a given location
 *
 * @param int $locationid the ID for room in Locations table
 * @return bool success
 */
function examregistrar_set_location_tree($locationid) {
    global $DB;

    $location = $DB->get_record('examregistrar_locations', array('id'=>$locationid), '*', MUST_EXIST);
    $oldpath = $location->path;
    $path = $location->path;
    $success = false;
    if($location->parent) {
        $parent = $DB->get_record('examregistrar_locations', array('id'=>$location->parent), '*', MUST_EXIST);
        // avoid circular references
        if(strpos($parent->path, '/'.$location->id) === false) {
            $path = $parent->path.'/'.$location->id;
            $depth = count(explode('/', $path)) - 1;
            $location->path = $path;
            $location->depth = $depth;
        } else {
            $parent = 0;
            if($location->depth > 1) {
                $parents = explode('/', $path);
                $parent = $parents[$location->depth];
            }
            $location->parent = $parent;
        }
    } else {
        $path = '/'.$location->id;
        $depth = count(explode('/', $path)) - 1;
        $location->path = $path;
        $location->depth = $depth;
    }

    if($location->depth && $location->path) {
        $success = $DB->update_record('examregistrar_locations', $location);
    }

    if($success && ($path != $oldpath)) {
        //rebuild children's paths
        examregistrar_rebuild_location_paths($location);
        
    }
    return $success;
}


/**
 * Recursive rebuild paths & depths for children of a given parent
 *
 * @param int $locationid the ID for room in Locations table
 * @return bool success
 */
function examregistrar_rebuild_location_paths($parent) {
    global $DB;

    if(!isset($parent->path) || !isset($parent->id)) {
        return;
    }
    if($parent->id == 0) {
        $parent->path = '';
    }

    if($children = $DB->get_records('examregistrar_locations', array('parent'=>$parent->id), '', 'id, parent, sortorder, path, depth')) {
        foreach($children as $child) {
            $child->path = $parent->path.'/'.$child->id;
            $child->depth = count(explode('/', $child->path)) - 1;
            $DB->update_record('examregistrar_locations', $child);
        }
        //now recursive part
        foreach($children as $child) {
            examregistrar_rebuild_location_paths($child);
        }
    }

    return;
}


////////////////////////////////////////////////////////////////////////////////
// Generate && Synchonize exams management                                    //
////////////////////////////////////////////////////////////////////////////////

/**
 * Creates entries in exams table for each course and period specified in $options form
 *
 * @param object $examregistrar object
 * @param object $formdata,  generating settings from user input
 */
function examregistrar_generateexams_fromcourses($examregistrar, $formdata) {
    global $DB, $USER;

    $options = new stdClass;
    foreach($formdata as $key => $value) {
        if(substr($key, 0, 2) == '__') {
            $k = substr($key, 2);
            $options->$k = $value;
        }
    }    
    
    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $now = time();

    list($insql, $params) = $DB->get_in_or_equal($options->periods);
    $sql = "SELECT p.*, ep.idnumber AS periodidnumber, ept.idnumber AS periodtypeidnumber, et.idnumber AS termidnumber, et.value AS termvalue
                FROM {examregistrar_periods} p
                JOIN {examregistrar_elements} ep ON p.examregid = ep.examregid AND p.period = ep.id
                JOIN {examregistrar_elements} ept ON p.examregid = ept.examregid AND p.periodtype = ept.id
                JOIN {examregistrar_elements} et ON p.examregid = ept.examregid AND p.term = et.id
            WHERE p.id $insql
            ORDER BY p.timestart ASC ";

    $periods = $DB->get_records_sql($sql, $params);

    $params = array();
    list($cparams, $wherecourse) = examregistrar_course_sqlselect($options);
    $params = array_merge($params, $cparams);

    $sql = "SELECT c.id, c.shortname, c.idnumber, c.category, c.startdate, uc.term, uc.credits, uc.department, uc.ctype, c.format, c.visible,
                   cc.idnumber AS catidnumber, ucc.degree
                FROM {course} c
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
                JOIN {course_categories} cc ON c.category = cc.id
                LEFT JOIN {local_ulpgccore_categories} ucc ON cc.id = ucc.categoryid
                WHERE 1 $wherecourse
                ORDER BY c.category ASC, c.shortname ASC ";

    $rs_courses = $DB->get_recordset_sql($sql, $params);

    $modified = array();
    $coursecount = array();
    $coursefail = array();
    $unrecognized = array();
    $keep = array();
    foreach($rs_courses as $course) {
        $category = new stdClass;
        $category->id = $course->category;
        $category->idumber = $course->catidnumber;
        $category->degree = $course->degree;
        $programme = examregistrar_programme_fromcourse($examregistrar, $course, $category, $options);
        $examperiods = examregistrar_examperiods_fromcourse($examregistrar, $course, $periods, $options);
        $keep = array();


//         print_object("course: {$course->shortname}; {$course->idnumber} / programme: $programme / term: {$course->term} / credits: {$course->credits}");
//         print_object($periods);
//         print_object($examperiods);
//         print_object(" --- examperiods -------------------");
        if($examperiods = examregistrar_examperiods_fromcourse($examregistrar, $course, $periods, $options)) {
            foreach($examperiods as $period) {
                if($period->examscope) {
                    $record = new stdClass;
                    $record->examregid = $period->examregid;
                    $record->annuality = $period->annuality;
                    $record->courseid = $course->id;
                    $record->period = $period->id;
                    $record->examscope = $period->examscope;

                    $calls = range(1,$period->calls);
                    $uniqueexam = get_object_vars($record);
                    $record->programme = $programme;

                    foreach($calls as $callnum) {
                        $record->callnum = $callnum;
                        $uniqueexam['callnum'] = $callnum;
                        $record->assignplugincm = 0;
                        if(isset($period->examinstance)) {
                            $record->assignplugincm = $period->examinstance;
                            $record->assigninstance = $period->assigninstance;
                        }
                        $record->visible = $options->examvisible;
                        if($record->visible == 2) {
                            $record->visible = $course->visible;
                        }
                        $record->examsession = 0;
                        $record->modifierid = $USER->id;
                        $record->timemodified = $now;

                        if($exam = $DB->get_record('examregistrar_exams', $uniqueexam)) {
                            $examid = $exam->id;
                            $modified[$examid] = 0;
                            $keep[$examid] = $examid;
                            $record->id = $examid;
                            if($exam->examsession) { // keep examsession if available
                                $record->examsession = $exam->examsession;
                            }
                            if($options->updateexams) {
                                $DB->update_record('examregistrar_exams', $record);
                                $modified[$examid] = 1;
                                $keep[$examid] = $examid;
                                //print_object("Update exam  {$course->shortname} ; period  {$record->period} ; id=  $examid   " );
                            }
                        } else {
                            $examid = $DB->insert_record('examregistrar_exams', $record);
                            $modified[$examid] = 2;
                            $keep[$examid] = $examid;
                            //$examid = $course->id.'_'.$record->period.'_'.$record->examscope.'_'.$callnum;
                            //print_object("Added exam  {$course->shortname} ; period  {$record->period} ; id=  $examid   " );
                        }
                        if($examid) {
                            $coursecount[$course->id] = 1;
                            // update plugin assignsubmission  exam instance
                            if(($options->generatemode == 1) && ($record->assignplugincm)) {
                                if($pluginuses = $DB->get_record('assign_plugin_config', array('assignment'=>$record->assigninstance, 'plugin'=>'exam', 'subtype'=>'assignsubmission', 'name'=>'registrarexams'))) {
                                    $uses = explode(',', $pluginuses->value);
                                    $uses[] = $examid;
                                    $pluginuses->value = implode(',', array_unique($uses));
                                    $DB->update_record('assign_plugin_config', $pluginuses);
                                } else {
                                    $pluginuses = new stdClass;
                                    $pluginuses->assignment = $record->assignplugincm;
                                    $pluginuses->plugin = 'exam';
                                   global $DB;     $pluginuses->subtype = 'assignsubmission';
                                    $pluginuses->name = 'registrarexams';
                                    $pluginuses->value = $examid;
                                    $DB->insert_record('assign_plugin_config', $pluginuses);
                                }
                            }
                        }
                    }
                } else {
                    $coursefail[$course->id] = 1;
                    $period->shortname = $course->shortname;
                    $unrecognized[] = $period;
                }
            }
        }
        if($options->deleteexams) {
            $params = array('courseid'=>$course->id);
            $select = " courseid = :courseid ";
            if($options->periods) {
                list($insql, $pparams) = $DB->get_in_or_equal($options->periods, SQL_PARAMS_NAMED, 'p_');
                $select .= " AND period $insql ";
                $params = $params + $pparams;
            }
            if($keep) {
                list($notinsql, $eparams) = $DB->get_in_or_equal($keep, SQL_PARAMS_NAMED, 'e_', false);
                $select .= " AND id $notinsql ";
                $params = $params + $eparams;
            }
            //$DB->delete_records_select('examregistrar_exams', $select, $params);
            $deletes = $DB->get_records_select_menu('examregistrar_exams', $select, $params, '', 'id, courseid');
            $DB->delete_records_list('examregistrar_exams', 'id', array_keys($deletes));
            foreach($deletes as $key => $del) {
                $modified[$key] = 3;
            }
        }
    }
    $rs_courses->close();

    $message1 = '';
    $message2 = '';
    if($modified) {
        $eventdata = array();
        $eventdata['objecttable'] = 'examregistrar_exams';
        list($course, $cm) = get_course_and_cm_from_instance($examregistrar, 'examregistrar');
        $context = context_module::instance($cm->id);
        $eventdata['context'] = $context;
        $eventdata['other'] = array('edit'=>'exams');
    
        $updated = 0;
        $added = 0;
        $deleted = 0;
        foreach($modified as $key => $item) {
            $eventdata['objectid'] = $key;
            switch($item) {
                case 2 : $event = \mod_examregistrar\event\manage_created::created($eventdata, 'examregistrar_exams');
                        break;
                case 3 : $event = \mod_examregistrar\event\manage_deleted::created($eventdata, 'examregistrar_exams');
                        break;
                default: $event = \mod_examregistrar\event\manage_updated::created($eventdata, 'examregistrar_exams');
            }
            $event->trigger();
            
            $updated = ($item == 1) ? $updated + 1 : $updated;
            $added = ($item == 2) ? $added + 1 : $added;
                        
            $deleted = ($item == 3) ? $deleted + 1 : $deleted;
        }
        $count = new stdClass;
        $count->courses = count($coursecount);
        $count->added = $added;
        $count->updated = $updated;
        $count->deleted = $deleted;

        $message1 = get_string('generatemodcount', 'examregistrar', $count);
    }
    if($unrecognized) {
        foreach($unrecognized as $key=>$period) {
            $unrecognized[$key] = get_string('generateunrecognizedexam', 'examregistrar', $period);
        }
        $message2 = get_string('generateunrecognized', 'examregistrar', count($unrecognized)).'<br />'.implode('<br />', $unrecognized);
    }

    return $message1.'<br /><br />'.$message2;
}

/**
 * Determines examperiod, scope & call from coursemodule idnumber
 * @param object $form post data including course selection settings as courseXXX fields
 * @return array ($select, params) tuple for get_records_xx database functions
 */
function examregistrar_decode_examquiz_idnumber($exregid, $courseid, $idnumber, $checkexamid = 0) {
    global $DB;
    
    $prefix = examregistrar_get_instance_config($exregid, 'quizexamprefix');
    // remove prefix & extrachars
    $idnumber = trim(str_replace($prefix, '', $idnumber), "_- \t\n\r\0\x0B"); 
    
    $periodname= '';
    $callnum = 0;
    $scope = '';
    if($parts = explode('_', $idnumber)) {
        $periodname = $parts[0];    
        //scope are numeric parts, if any
        if($scope = strpbrk($periodname, '0123456789')) {
            $periodname = strstr($periodname, $scope, true);
        }
        
        $callnum = isset($parts[1]) ? (int)$parts[1] : 1;
    }
    $scope = (int)$scope;
    if(!$scope) {
        $scope = 0;
    }
    
    //get applicable exams ie, course exams
    $params = array('examregid' => $exregid, 'courseid' => $courseid,
                    'period' => $periodname,  'callnum' => $callnum, 'scope' =>$scope);
    // check if a given examid correspond to $idnumber encoded period, scope
    // if matched, an exam is returned
    $checkexamwhere = '';
    if($checkexamid) {
        $checkexamwhere = ' AND e.id = :examid ';
        $params['examid'] = $checkexamid;
    }
    
    
    $sql = "SELECT e.*, ep.idnumber AS periodtypename, es.idnumber AS scopename, es.value AS scopevalue
            FROM {examregistrar_exams} e 
            JOIN {examregistrar_periods} p ON e.period = p.id
            JOIN {examregistrar_elements} ep ON p.periodtype = ep.id 
            JOIN {examregistrar_elements} es ON e.examscope = es.id 
            WHERE e.examregid = :examregid AND e.courseid = :courseid 
                    AND ep.idnumber = :period  AND e.callnum = :callnum AND es.value = :scope ";
    $exams = $DB->get_records_sql($sql, $params); 
    
    if(empty($exams)) {
        return false;
    }
    
    return reset($exams);
} 

/**
 * Add makeexamlock to quizzes associated to exams by idnumber 
 *
 * @param object $examregistrar object
 * @param array $options generating settings array with courseid or quizid fields
 */
function examregistrar_add_quizzes_makexamlock($examregistrar, $options = null) {
    global $DB;

    if(!get_config('quizaccess_makeexamlock', 'enabled')) {
        return;
    }

    $exregid = examregistrar_check_primaryid($examregistrar->id);
    $prefix = examregistrar_get_instance_config($exregid, 'quizexamprefix');
    
    $params = array('mod'=>'quiz', 'idnumber' => $prefix.'%');   
    $extrawhere = '';
    if(isset($options['courseid']) && $options['courseid']) {
        $extrawhere .= ' AND cm.course = :courseid ';
        $params['courseid'] = $options['courseid'];
    }
    if(isset($options['quizid']) && $options['quizid']) {
        $extrawhere .= ' AND mk.quizid = :quizid ';
        $params['quizid'] = $options['quizid'];
    }     
    
    $like_idnumber = $DB->sql_like('cm.idnumber', ':idnumber');
    
    $sql = "SELECT cm.id, cm.course, cm.instance, cm.idnumber, uc.term, mk.id AS mkid
            FROM {course_modules} cm 
            JOIN {local_ulpgccore_course} uc ON uc.courseid = cm.course
            JOIN {modules} md ON cm.module = md.id AND md.name = :mod 
            LEFT JOIN {quizaccess_makeexamlock} mk ON mk.quizid = cm.instance
            WHERE ((mk.makeexamlock < 1) OR mk.makeexamlock IS NULL) AND $like_idnumber $extrawhere ";
 
    $quizzes = $DB->get_recordset_sql($sql, $params);
    
    $record = new stdClass();
    $record->makeexamlock = 0;
    $num = 0;
    
    foreach($quizzes as $mod) {
        if($exam = examregistrar_decode_examquiz_idnumber($exregid, $mod->course, $mod->idnumber)) {
            //OK, we have an exam, add 
            $record->quizid = $mod->instance;
            $record->makeexamlock = $exam->id;
            if(isset($mod->mkid)) {
                // records exists, update
                $record->id = $mod->mkid;
                if($DB->update_record('quizaccess_makeexamlock', $record)) {
                    $num++;
                }
            } else {
                // records doesn't exist, insert
                unset($record->id);
                if($DB->insert_record('quizaccess_makeexamlock', $record)) {
                    $num++;
                }
            }
        }
    }
    $quizzes->close();
    
    $type = $num ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING;
    \core\notification::add(get_string('addedquizexamcm', 'examregistrar', $num), $type);
}


/**
 * Add quizexamcm to exams that has associated with quizzes by makeexamlock
 *
 * @param object $examregistrar object
 * @param array $options generating settings array with courseid or quizid fields
 */
function examregistrar_synch_exam_quizzes($examregistrar, $options = null) {
    global $DB, $USER;

    
    if(!get_config('quizaccess_makeexamlock', 'enabled')) {
        return;
    }
    
    $exregid = examregistrar_check_primaryid($examregistrar->id);
    $params = array('examregid'=> $exregid, 'mod' => 'quiz');  
    $extrawhere = '';
    if(isset($options['courseid']) && $options['courseid']) {
        $extrawhere .= ' AND e.courseid = :courseid ';
        $params['courseid'] = $options['courseid'];
    }
    if(isset($options['quizid']) && $options['quizid']) {
        $extrawhere .= ' AND mk.quizid = :quizid ';
        $params['quizid'] = $options['quizid'];
    }    
    
    $sql = "SELECT e.id, cm.id AS quizplugincm, q.timeopen, q.timeclose, q.timelimit, 
                    d.id AS deliveryid, d.helpercmid, d.bookedsite   
            FROM {examregistrar_exams} e 
            JOIN {quizaccess_makeexamlock} mk ON mk.makeexamlock = e.id
            JOIN {course_modules} cm ON cm.course = e.courseid AND cm.instance = mk.quizid
            JOIN {modules} md ON cm.module = md.id AND md.name = :mod
            JOIN {quiz} q ON q.id = cm.instance
       LEFT JOIN {examregistrar_examdelivery} d ON e.id = d.examid AND d.helpermod = md.name
            WHERE e.quizplugincm = 0 AND e.examregid = :examregid $extrawhere";

    $exams = $DB->get_recordset_sql($sql, $params);
    
    $delivery = new \stdClass();
    $delivery->helpermod = 'quiz';
    $delivery->component = 'synch_exam_quizzes';
    $delivery->modifierid = $USER->id;
    $delivery->bookedsite = examregistrar_get_instance_config($exregid, 'deliverysite');
    
    $num = 0;
    $deliverynum = 0;
    $errors = [];
    foreach($exams as $exam) {
        $DB->update_record('examregistrar_exams', $exam, true);
        $num++;
        
        if(!$exam->deliveryid) {
            //there is no helper yet, add one
            $delivery->examid = $exam->id;
            $delivery->helpercmid = $exam->quizplugincm;
            $delivery->timeopen = $exam->timeopen;
            $delivery->timeclose = $exam->timeclose;
            $delivery->timelimit = $exam->timelimit;
            if($DB->insert_record('examregistrar_examdelivery', $delivery)) {
                $deliverynum++;
            }
        } else {
            if($exam->helpercmid != $exam->quizplugincm) {
                $errors[] = $exam->quizplugincm;
            } elseif($exam->bookedsite != $delivery->bookedsite) {
                $DB->set_field('examregistrar_examdelivery', 'bookedsite', $delivery->bookedsite, 
                                ['id' => $exam->deliveryid]);
            }
        }
    }
    $exams->close();

    $type = $num ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING;
    \core\notification::add(get_string('addedquizexamcm', 'examregistrar', $num), $type);    
    
    $type = $deliverynum ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING;
    \core\notification::add(get_string('addeddeliveryhelper', 'examregistrar', $deliverynum), $type);    
    
    if($errors) {
        \core\notification::add(get_string('wrongquizcmhelper', 'examregistrar', implode(', ', $errors)),
            \core\output\notification::NOTIFY_ERROR);    
    }
    
}


/**
 * Update data in Quizzes associated with exams in the registrar
 *
 * @param object $examregistrar object
 * @param array $options generating settings array with courseid or quizid fields
 */
function examregistrar_update_exam_quizzes($examregistrar, $options = null) {
    global $CFG, $DB;

    $exregid = examregistrar_check_primaryid($examregistrar->id);
    $params = array('examregid'=> $exregid);  
    
    $extrawhere = '';
    if(isset($options['courseid']) && $options['courseid']) {
        $extrawhere .= ' AND e.courseid = :courseid ';
        $params['courseid'] = $options['courseid'];
    }
    
    if(isset($options['session']) && $options['session']) {
        $extrawhere .= ' AND e.examsession = :session ';
        $params['session'] = $options['session'];
    }
    
    if(isset($options['examid']) && $options['examid']) {
        $extrawhere .= ' AND e.id = :examid ';
        $params['examid'] = $options['examid'];
    }
    
    /* not used
    if(isset($options['quizid']) && $options['quizid']) {
        $extrawhere .= ' AND q.id = :quizid ';
        $params['quizid'] = $options['quizid'];
    }
    */   
    
    $sql = "SELECT d.*, e.courseid
              FROM {examregistrar_examdelivery} d
              JOIN {examregistrar_exams} e ON e.id = d.examid 
             WHERE e.examregid = :examregid AND d.helpermod = 'quiz' AND d.helpercmid > 0 $extrawhere ";
              
    $examdeliveries =  $DB->get_recordset_sql($sql, $params);
    
    include_once($CFG->dirroot . '/mod/quiz/lib.php');
    include_once($CFG->dirroot . '/mod/quiz/accessmanager.php');    

    $num = 0;
    foreach($examdeliveries as $delivery) {
        if(examregistrar_delivery_update_helper_instance($delivery, $delivery->courseid)) {
            $num++;
        }
    }
    $examdeliveries->close();    
    
    /// NOT USED  ANYMORE WITH DELIVERY  /// NOT USED  ANYMORE WITH DELIVERY 
    
/*    
    $sql = "SELECT e.id, q.id AS qid, cm.id AS cmid, s.examdate, s.duration, s.timeslot
            FROM {examregistrar_exams} e 
            JOIN {examregistrar_examsessions} s ON s.id = e.examsession
            JOIN {course_modules} cm ON cm.id = e.quizplugincm 
            JOIN {quiz} q ON q.id = cm.instance
            WHERE e.quizplugincm > 0 AND e.examregid = :examregid $extrawhere ";
    
    $exams = $DB->get_recordset_sql($sql, $params);
    
    $quiz = new stdClass();
    $quiz->timemodified = time();
    
    $lock = new stdClass();
    $lock->makeexamlock = 0;
    $lockenabled = get_config('quizaccess_makeexamlock', 'enabled');
    $num = 0;
    $aftertime = get_config('examregistrar', 'quizexamafter');
    
    foreach($exams as $exam) {
        $quiz->id = $exam->qid;
        
        if(!$exam->examdate) {
            continue;
        }
        
        $midnight = usergetmidnight($exam->examdate); 
        $open = str_replace(array(' ', '.'), ':', $exam->timeslot);
        $open = strtotime("1970-01-01 $open UTC");

        $quiz->timelimit = $exam->duration;
        $quiz->timeopen = $midnight+$open;
        $quiz->timeclose = $quiz->timeopen + $exam->duration + $aftertime;
        
        if($DB->update_record('quiz', $quiz)) {
            $num++;
        }
        
        // now ensure makeexamlock is on
        if($lockenabled) {
            $mklock = $DB->get_record('quizaccess_makeexamlock', array('quizid' => $exam->qid));
            if(!$mklock) {
                unset($lock->id);
                $lock->quizid = $exam->qid;
                $lock->makeexamlock = $exam->id;
                $DB->insert_record('quizaccess_makeexamlock', $lock);
            } else {
                if($mklock->makeexamlock != $exam->id) {
                    $mklock->makeexamlock = $exam->id;
                    $DB->update_record('quizaccess_makeexamlock', $lock);
                }
            }
        }
    }
    $exams->close();
    */
    
    $type = $num ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING;
    \core\notification::add(get_string('updatedquizdates', 'examregistrar', $num), $type);
}


/**
 * Update data in Quizzes associated with exams in the registrar
 *
 * @param object $examregistrar object
 * @param array $options generating settings array with courseid or quizid fields
 */
function examregistrar_exam_quizzes_mklock($examregistrar, $options = null) {
    global $CFG, $DB;

    $exregid = examregistrar_check_primaryid($examregistrar->id);
    $params = array('examregid'=> $exregid);  
    
    $extrawhere = '';
    if(isset($options['courseid']) && $options['courseid']) {
        $extrawhere .= ' AND e.courseid = :courseid ';
        $params['courseid'] = $options['courseid'];
    }
    
    if(isset($options['session']) && $options['session']) {
        $extrawhere .= ' AND e.examsession = :session ';
        $params['session'] = $options['session'];
    }
    
    if(isset($options['examid']) && $options['examid']) {
        $extrawhere .= ' AND e.id = :examid ';
        $params['examid'] = $options['examid'];
    }

    $sql = "SELECT d.id, d.examid, cm.instance, d.helpercmid 
              FROM {examregistrar_examdelivery} d
              JOIN {examregistrar_exams} e ON e.id = d.examid 
              JOIN {modules} m ON m.name LIKE d.helpermod              
              JOIN {course_modules} cm ON cm.id = d.helpercmid AND cm.course = e.courseid AND cm.module = m.id
             WHERE e.examregid = :examregid AND d.helpermod = 'quiz' AND d.helpercmid > 0 $extrawhere ";
              
    $cms =  $DB->get_records_sql($sql, $params);
    
    include_once($CFG->dirroot . '/mod/quiz/lib.php');
    include_once($CFG->dirroot . '/mod/quiz/accessmanager.php');    

    $num = 0;
    foreach($cms as $delivery) {
        if($modinstance = $DB->get_record('quiz', ['id' => $delivery->instance])) {
            $modinstance->instance = $delivery->instance;
            $modinstance->coursemodule = $delivery->helpercmid;
            $modinstance->quizpassword = $modinstance->password;
            $accesssettings = quiz_access_manager::load_settings($delivery->instance);
            foreach ($accesssettings as $name => $value) {
                $modinstance->$name = $value;
            }            
            
            $modinstance->makeexamlock = $delivery->examid;    
            if(quiz_update_instance($modinstance, null)) {
                $num++;
            }
        }
    }
    
    $type = $num ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING;
    \core\notification::add(get_string('updatedquizmklock', 'examregistrar', $num), $type);
}


/**
 * Update data in Quizzes associated with exams in the registrar
 *
 * @param object $examregistrar object
 * @param array $options generating settings array with courseid or quizid fields
 */
function examregistrar_exam_quizzes_remove_password($examregistrar, $options = null) {
    global $DB;

    $exregid = examregistrar_check_primaryid($examregistrar->id);
    $params = array('examregid'=> $exregid);  
    
    $extrawhere = '';
    if(isset($options['courseid']) && $options['courseid']) {
        $extrawhere .= ' AND e.courseid = :courseid ';
        $params['courseid'] = $options['courseid'];
    }
    
    if(isset($options['session']) && $options['session']) {
        $extrawhere .= ' AND e.examsession = :session ';
        $params['session'] = $options['session'];
    }
    
    if(isset($options['examid']) && $options['examid']) {
        $extrawhere .= ' AND e.id = :examid ';
        $params['examid'] = $options['examid'];
    }
    
    $sql = "SELECT d.id, cm.instance
              FROM {examregistrar_examdelivery} d
              JOIN {examregistrar_exams} e ON e.id = d.examid 
              JOIN {modules} m ON m.name LIKE d.helpermod              
              JOIN {course_modules} cm ON cm.id = d.helpercmid AND cm.course = e.courseid AND cm.module = m.id
             WHERE e.examregid = :examregid AND d.helpermod = 'quiz' AND d.helpercmid > 0 $extrawhere ";
              
    $cms =  $DB->get_records_sql_menu($sql, $params);

    if(!empty($cms)) {
        list($insql, $params) = $DB->get_in_or_equal($cms); 
        $DB->set_field_select('quiz', 'password', '', "id $insql ", $params);
    }
    $num = count($cms);
    $type = $num ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING;
    \core\notification::add(get_string('updatedquizpasswords', 'examregistrar', $num), $type);
    
}

/**
 * Update data in Quizzes associated with exams in the registrar
 *
 * @param object $examregistrar object
 * @param array $options generating settings array with courseid or quizid fields
 */
function examregistrar_delete_exams_dependencies(array $examids) {
    global $DB;
 
    $taken = [];
    $deleted = [];
 
    $params = ['taken' => 1];
    foreach($examids as $examid) {
        $params['examid'] = $examid;
        foreach(['examfiles', 'responses', 'session_seats'] as $table) {
            if($DB->record_exists('examregistrar_'.$table, $params)) {
                $taken[$examid] = $examid;
                break;
            }
        }
        
        if(empty($taken[$examid])) {
            // this exam is not taken, we can safely delete
            // delete bookings & vouchers if exist
            $bookings = $DB->get_records_menu('examregistrar_bookings', ['examid' => $examid], '', 'id,userid');
            if(!empty($bookings)) {
                $DB->delete_records_list('examregistrar_vouchers', 'bookingid', array_keys($bookings));
                $DB->delete_records('examregistrar_bookings', ['examid' => $examid]);
            }
            
            // TODO // TODO // TODO // TODO 
            // remove overrides if callnum < 0 & users in bookings
            
            // delete examregistrar_seatings 
            $DB->delete_records('examregistrar_seating_rules', ['examid' => $examid]);
            $DB->delete_records('examregistrar_session_seats', ['examid' => $examid]);
            $DB->delete_records('examregistrar_examfiles', ['examid' => $examid]);
            $DB->delete_records('examregistrar_examdelivery', ['examid' => $examid]);
            // TODO TODO move to class 
            $DB->delete_records('quizaccess_makeexamlock', ['makeexamlock' => $examid]);
            $deleted[$examid] = $examid;
        }
    }
    
    return [$deleted, $taken];
} 
