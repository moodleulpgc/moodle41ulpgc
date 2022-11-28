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
 * Internal library of functions for review & print pages in module examregistrar
 *
 * All the examregistrar specific functions, needed to implement the module
 * logic, are placed here.
 *
 * @package    mod_examregistrar
 * @copyright  2012 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();



/**
 * Returns an object with properly formatted exam data fields
 *
 * @param int $element element name / component table
 * @param class $output a page renderer
 * @param bool $withsession whether to include session & exam date in object
 * @return object properly built exam data object
 */
function examregistrar_get_examdata($examid, $output, $withsession = false) {
    global $DB; 

    $exam = $DB->get_record('examregistrar_exams', array('id'=>$examid), '*', MUST_EXIST);
    $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);
    $examdata = new stdClass;
    $examdata->coursename = $examcourse->shortname.' - '.$examcourse->fullname;
    $examdata->annuality = $output->formatted_name_fromid($exam->annuality, '');
    $examdata->programme = $exam->programme;
    $examdata->period = $output->formatted_name_fromid($exam->period, 'periods');
    $examdata->examscope = $output->formatted_name_fromid($exam->examscope);
    $examdata->callnum = $exam->callnum;
    $examdata->courseid = $exam->courseid;
    $examdata->courseidnumber = $examcourse->idnumber;
    if($withsession) {
        $examdata->examsession = $output->formatted_name_fromid($exam->examsession, 'examsessions');
        $examdata->examdate = userdate($DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$exam->examsession)), get_string('strftimedaydate'));
    }

    return $examdata;
}


/**
 * Sets event data & trigger log event for examfile action
 *
 * @param object $examfile the examfile object that is subject on the action
 * @param string $eventtype the type of action
 * @param int $examregid the ID of the examregistrar instance
 * @param class $context the context object
 */
function examregistrar_examfile_trigger_event($examfile, $eventype, $examregid, $context) {

    $eventdata = array();
    $eventdata['objectid'] = $examfile->id;
    $eventdata['context'] = $context;
    $eventdata['other'] = array();
    $eventdata['other']['examregid'] = $examregid;
    $eventdata['other']['examid'] = $examfile->examid;
    $eventdata['other']['attempt'] = $examfile->attempt;
    $eventdata['other']['idnumber'] = $examfile->idnumber;
    $eventdata['other']['status'] = $examfile->status;
    
    $eventclass = '\mod_examregistrar\event\examfile_'.$eventype;
    
    $event = $eventclass::create($eventdata);
    $event->trigger();
}

