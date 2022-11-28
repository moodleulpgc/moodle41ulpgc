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
 * Displays the interface for download & printing of room documentation and exams
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_capability('mod/examregistrar:download',$context);

$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cm->id,'tab'=>'printrooms'));
$tab = 'printrooms';
$output = $PAGE->get_renderer('mod_examregistrar', 'printrooms');
/*
    Lista de sedes/aulas por examsession : menu for selecting session default = next session

    filtrar por degree, examen, sede

    sedes aulas: user is assigned as staffer in a room show those rooms ,
    user is coordinator: show all rooms of degree, show appendix of additional exams


*/
$SESSION->nameformat = 'lastname';
$period   = optional_param('period', '', PARAM_INT);
$session   = optional_param('session', '', PARAM_INT);
$bookedsite   = optional_param('venue', '', PARAM_INT);
$programme   = optional_param('programme', '', PARAM_ALPHANUMEXT);
$courseid   = optional_param('course', '', PARAM_ALPHANUMEXT);
$room   = optional_param('room', '', PARAM_INT);

$now = time();
//$now = strtotime('4 may 2014') + 3605;

if(!$period) {
    $periods = examregistrar_current_periods($examregistrar, $now);
    if($periods) {
        $period = reset($periods);
        $period = $period->id;
    }
}

if(!$session) {
    $session = examregistrar_next_sessionid($examregistrar, $now);
}

if(!$bookedsite) {
    $bookedsite = examregistrar_user_venueid($examregistrar, $USER->id);
}

$term   = optional_param('term', 0, PARAM_INT);
$searchname = optional_param('searchname', '', PARAM_TEXT);
$searchid = optional_param('searchid', '', PARAM_INT);
$sort = optional_param('sorting', 'shortname', PARAM_ALPHANUM);
$order = optional_param('order', 'ASC', PARAM_ALPHANUM);
$baseparams = array('id'=>$cm->id, 'tab'=>$tab);
$printparams = array('period'=>$period,
                        'session'=>$session,
                        'venue'=>$bookedsite,
                        'term'=>$term,
                        'searchname'=>$searchname,
                        'searchid'=>$searchid,
                        'programme'=>$programme,
                        'sorting'=>$sort,
                        'order'=>$order,
                        'user'=>$userid);

$printurl = new moodle_url($baseurl, $printparams);

$annuality =  examregistrar_get_annuality($examregistrar);

$canviewall = has_capability('mod/examregistrar:viewall', $context);

// check permissions, acces by room assigned in this session
$rooms = examregistrar_get_user_rooms($examregistrar, 0, 0, $session);

//echo $output->exams_courses_selectorform($examregistrar, $course, $printurl, $printparams, 'period, session, venue');
echo $output->exams_item_selection_form($examregistrar, $course, $printurl, $printparams, 'period, session, venue');
if($canviewall) {
    echo $output->exams_courses_selector_form($examregistrar, $course, $printurl, $printparams);
}

/// get session name & code
list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period, 'periods', 'period');
list($sessionname, $sessionidnumber) = examregistrar_get_namecodefromid($session, 'examsessions', 'examsession');
$name = " $sessionname ($sessionidnumber) [$periodidnumber] ";
if($bookedsite) {
    list($venuename, $venueidnumber) = examregistrar_get_namecodefromid($bookedsite, 'locations', 'location');
    $name .= " in $venuename ($venueidnumber)";
}

echo $output->heading(get_string('examsforsession', 'examregistrar', $name));

$params = array('period'=>$period, 'session'=>$session, 'bookedsite'=>$bookedsite,
                'room'=>$room, 'programme'=>$programme, 'course'=>$courseid);
$rooms = array();
$allocations = examregistrar_get_roomallocations_byroom($params, array_keys($rooms));

/// now print the list of rooms and exams
foreach($allocations as $allocroom) {
    echo $output->listdisplay_allocatedroom($allocroom, $baseurl);
}






























