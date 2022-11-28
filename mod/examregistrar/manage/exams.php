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
 * Prints the management interface for Exams table of an instance of examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_capability('mod/examregistrar:manageexams',$context);



/**
 * determines if the exam and course term values matches each other
 *
 * @param object $exam exam object with extrafields
 * @return string class name
 */
function examregistrar_check_exam_term($exam) {
    $class = '';

    if($exam->periodterm) {
        if($exam->courseterm) {
            if($exam->courseterm != $exam->periodterm) {
                $class = ' error ';
            }
        } else {
            if($exam->scopeterm != $exam->periodterm) {
                $class = ' error ';
            }
        }
    }
    return $class;
}

/**
 * determines if the exam session corresponds to indicated period
 *
 * @param object $exam exam object with extrafields
 * @return string class name
 */
function examregistrar_check_exam_session($exam) {
    global $DB;

    $class = '';

    if($exam->examsession && $exam->period) {
        $sessions = $DB->get_records_menu('examregistrar_examsessions', array('period'=>$exam->period), 'id', 'id, id');
        if(!in_array($exam->examsession, array_keys($sessions))) {
                $class = ' error ';
        }
    }
    return $class;
}


$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id'=>$cm->id,'edit'=>$edit));

/// filter form parameters

$sel_annuality  = optional_param('sannuality', '', PARAM_ALPHANUMEXT);
$sel_programme  = optional_param('sprogramme', '', PARAM_ALPHANUMEXT);
$sel_shortname  = optional_param('sshortname', '', PARAM_ALPHANUMEXT);
$sel_term  = optional_param('sterm', '', PARAM_ALPHANUMEXT);
$sel_period  = optional_param('speriod', 0, PARAM_INT);
$sel_scope  = optional_param('sscope', '', PARAM_ALPHANUMEXT);
$sel_callnum  = optional_param('scallnum', 0, PARAM_INT);
$sel_session  = optional_param('ssession', 0, PARAM_INT);
$sel_booked  = optional_param('sbooked', '', PARAM_ALPHANUMEXT);
$sel_delivery  = optional_param('sdelivery', null, PARAM_ALPHANUMEXT);
$bookedsite   = optional_param('venue', 0, PARAM_INT);

$selparams = array('id'=>$cm->id, 'edit' => $edit,
                      'sannuality' => $sel_annuality,
                      'sprogramme' => $sel_programme,
                      'sshortname' => $sel_shortname,
                      'sterm'      => $sel_term,
                      'speriod'    => $sel_period,
                      'sscope'     => $sel_scope,
                      'ssession'   => $sel_session,
                      'sbooked'    => $sel_booked,
                      'sdelivery'  => $sel_delivery,
                       'venue'     => $bookedsite,
                      );

$manageurl = new moodle_url($baseurl, $selparams);

/// Print heading & filter
if (!$table->is_downloading()) {
    echo $OUTPUT->heading(get_string('edit'.$edit, 'examregistrar'));


    echo $OUTPUT->container_start('examregistrarmanagefilterform clearfix ');
        echo $OUTPUT->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

        echo '<form id="examregistrarperiodsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage.php" method="post">'."\n";
        echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";

        $annualitymenu = examregistrar_elements_getvaluesmenu($examregistrar, 'annualityitem', $examregprimaryid);
        echo html_writer::label(get_string('annuality', 'examregistrar').': ', 'sannuality');
        echo html_writer::select($annualitymenu, "sannuality", $sel_annuality);
        echo ' &nbsp; ';


        //$programmemenu = examregistrar_elements_getvaluesmenu($examregistrar, 'annualityitem', $examregprimaryid);
//        $programmemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'exams', 'locationitem', $examregprimaryid, 'choose');
        $programmemenu = examregistrar_elements_get_fieldsmenu($examregistrar, 'exams', 'programme', $examregprimaryid);
        echo html_writer::label(get_string('programme', 'examregistrar').': ', 'sannuality');
        echo html_writer::select($programmemenu, "sprogramme", $sel_programme);
        echo ' &nbsp; ';

        $coursemenu = examregistrar_elements_get_fieldsmenu($examregistrar, 'exams', 'courseid', $examregprimaryid);
        $shortnamemenu = array();
        foreach($coursemenu as $courseid) {
            if($name = $DB->get_field('course', 'shortname', array('id'=>$courseid))) {
                $shortnamemenu[$courseid] = $name;
            }
        }
        natcasesort($shortnamemenu);
        echo html_writer::label(get_string('shortname', 'examregistrar').': ', 'sshortname');
        echo html_writer::select($shortnamemenu, "sshortname", $sel_shortname);
        echo ' &nbsp; ';

        //$periodmenu = examregistrar_elements_getvaluesmenu($examregistrar, 'perioditem', $examregprimaryid);
        $periodmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'periods', 'perioditem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('perioditem', 'examregistrar').': ', 'speriod');
        echo html_writer::select($periodmenu, "speriod", $sel_period);

        $termmenu = examregistrar_elements_getvaluesmenu($examregistrar, 'termitem', $examregprimaryid);
        echo html_writer::label(get_string('termitem', 'examregistrar').': ', 'sterm');
        echo html_writer::select($termmenu, "sterm", $sel_term);
        echo ' &nbsp; ';

        $scopemenu = examregistrar_elements_getvaluesmenu($examregistrar, 'scopeitem', $examregprimaryid);
        echo html_writer::label(get_string('scopeitem', 'examregistrar').': ', 'sscope');
        echo html_writer::select($scopemenu, "sscope", $sel_scope);
        echo ' &nbsp; ';

        $callmenu = array_combine(range(1,12), range(1,12));
        $callmenu[-1] = get_string('specialexam', 'examregistrar');
        echo html_writer::label(get_string('callnum', 'examregistrar').': ', 'scallnum');
        echo html_writer::select($callmenu, "scallnum", $sel_callnum);
        echo ' &nbsp; ';

        $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('examsessionitem', 'examregistrar').': ', 'ssession');
        echo html_writer::select($sessionmenu, "ssession", $sel_session);
        echo ' &nbsp; ';

        $deliverymenu = array(''=>get_string('choose'),
                            'examregistrar' => get_string('pluginname', 'examregistrar'),
                            'assign' => get_string('pluginname', 'assign'),
                            'quiz' => get_string('pluginname', 'quiz'),
                            'offlinequiz' => get_string('pluginname', 'offlinequiz'),
                            );
        echo html_writer::label(get_string('examdelivery', 'examregistrar').': ', 'sexamdelivery');
        echo html_writer::select($deliverymenu, "sdelivery", $sel_delivery);
        echo ' &nbsp; ';

        $bookedmenu = array(''=>get_string('choose'),
                            'booked' => get_string('booked', 'examregistrar'),
                            'notbooked' => get_string('notbooked', 'examregistrar'),
                            );
        echo html_writer::label(get_string('booked', 'examregistrar').': ', 'sbooked');
        echo html_writer::select($bookedmenu, "sbooked", $sel_booked);
        echo ' &nbsp; ';

        echo '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
        echo '</form>'."\n";
    echo $OUTPUT->container_end();

    $url = new moodle_url($baseurl, array('item'=>-1));
    echo $OUTPUT->heading(html_writer::link($url, get_string('add'.$itemname, 'examregistrar')));
}

$tablecolumns = array('checkbox', 'annualityname', 'shortname', 'periodname',
                      'scopename', 'callnum', 'sessionname', 'examdelivery', 'action');
$tableheaders = array(html_writer::checkbox('selectall', 1, false, '', array('id'=>'selectall')),
                        get_string('annualityitem', 'examregistrar'),
//                        get_string('programme', 'examregistrar'),
                        get_string('course', 'examregistrar'),
                        get_string('perioditem', 'examregistrar'),
                        get_string('scopeitem', 'examregistrar'),
                        get_string('callnum', 'examregistrar'),
                        get_string('examsessionitem', 'examregistrar'),
                        get_string('examdelivery', 'examregistrar'),
                        get_string('action'),
                        );
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($manageurl->out(false));
$table->set_wrapformurl($manageurl);

$actionsmenu = array('delete' => get_string('delete'),
                     'hide' => get_string('hide'),
                     'show' => get_string('show'),
                     'setsession' => get_string('setsession', 'examregistrar'),
                     'adddeliverhelper' => get_string('adddeliverhelper', 'examregistrar'),
                     'setdeliverdata' => get_string('setdeliverdata', 'examregistrar'),
                     );
$table->set_actionsmenu($actionsmenu);
$sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, 'choose', '', array('period'=>$sel_period));
$label = html_writer::label(get_string('examsessionitem', 'examregistrar').': ', 'setsession');
$select =  html_writer::select($sessionmenu, "setsession", 0);
$table->set_additionalfields('setsession', array($label.$select));

$table->sortable(true, 'annualityname, periodname', SORT_ASC);
$table->no_sorting('checkbox');
$table->no_sorting('examdelivery');
$table->no_sorting('action');

$table->set_attribute('id', 'examregistrar_'.$edit.$examregistrar->id);
$table->set_attribute('cellspacing', '0');
$table->set_attribute('class', 'flexible generaltable examregmanagementtable');

$table->setup();

    $select = "SELECT e.*, ea.name AS annualityname, ea.idnumber AS annualityidnumber,
                           ep.name AS periodname, ep.idnumber AS periodidnumber, ep.value AS periodvalue,
                           es.name AS scopename, es.idnumber AS scopeidnumber, es.value AS scopeterm,
                           et.name AS termname, et.idnumber AS termidnumber,et.value AS periodterm,
                           esn.name AS sessionname, esn.idnumber AS sessionidnumber, s.examdate,
                           c.shortname, c.idnumber AS courseidnumber, c.fullname, uc.term AS courseterm, uc.credits, uc.ctype, uc.department ";
    $count = "SELECT COUNT(e.id) ";
    $sql = "FROM {examregistrar_exams} e
                JOIN {course} c ON e.courseid = c.id
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
                JOIN {examregistrar_elements} ea ON e.examregid =  ea.examregid AND ea.type = 'annualityitem' AND e.annuality = ea.id
                JOIN {examregistrar_periods} p ON e.period = p.id AND e.examregid =  p.examregid
                JOIN {examregistrar_elements} ep ON p.examregid =  ep.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                JOIN {examregistrar_elements} es ON e.examregid =  es.examregid AND es.type = 'scopeitem' AND e.examscope = es.id
                JOIN {examregistrar_elements} et ON e.examregid =  et.examregid AND et.type = 'termitem' AND p.term = et.id
                LEFT JOIN {examregistrar_examsessions} s ON e.examregid =  s.examregid AND e.examsession = s.id
                LEFT JOIN {examregistrar_elements} esn ON s.examregid =  esn.examregid AND esn.type = 'examsessionitem' AND s.examsession = esn.id
                ";

    $where = " WHERE e.examregid = :examregid " ;
    $params = array('examregid'=>$examregprimaryid);
    if($sel_annuality) {
        $where .= ' AND e.annuality = :annuality ';
        $params['annuality'] = $sel_annuality;
    }
    if($sel_programme) {
        $where .= ' AND e.programme = :programme ';
        $params['programme'] = $sel_programme;
    }
    if($sel_shortname) {
        $where .= ' AND e.courseid = :courseid ';
        $params['courseid'] = $sel_shortname;
    }
    if($sel_term) {
        $where .= ' AND p.term = :term ';
        $params['term'] = $sel_term;
    }
    if($sel_period) {
        $where .= ' AND e.period = :period ';
        $params['period'] = $sel_period;
    }
    if($sel_scope) {
        $where .= ' AND e.examscope = :scope ';
        $params['period'] = $sel_period;
    }
    if($sel_callnum) {
        if($sel_callnum > 0) {
        $where .= ' AND e.callnum = :callnum ';
        } else {
            $where .= ' AND e.callnum <= :callnum ';
        }
        $params['callnum'] = $sel_callnum;
    }

    if($sel_session) {
        $where .= ' AND e.examsession = :session ';
        $params['session'] = $sel_session;
    }
    if($sel_booked) {
        $invenue = '';
        if($bookedsite) {
            $invenue = " AND b.locationid = :bookedsite ";
            $params['bookedsite'] = $bookedsite;
        }
        $booking = "(SELECT 1
                        FROM {examregistrar_bookings} b
                        WHERE b.examid = e.id AND b.booked = 1 $invenue )";

        if($sel_booked == 'booked') {
            $where .= " AND  EXISTS $booking ";
        } elseif($sel_booked == 'notbooked') {
            $where .= " AND NOT EXISTS $booking ";
        }
    }
    if($sel_delivery) {
        if($sel_delivery == 'examregistrar') {
            $delivery = "(SELECT 1
                            FROM {examregistrar_examdelivery} ed
                            WHERE ed.examid = e.id AND ed.helpermod IS NULL)";
            $where .= " AND  EXISTS $delivery ";
        } else {
            $delivery = "(SELECT 1
                            FROM {examregistrar_examdelivery} ed
                            WHERE ed.examid = e.id AND ed.helpermod = :helpermod )";
            $where .= " AND  EXISTS $delivery ";
            $params['helpermod'] = $sel_delivery;
        }
    }

$totalcount = $DB->count_records_sql($count.$sql.$where, $params);

$table->initialbars(false);
$table->pagesize($perpage, $totalcount);

if ($table->get_sql_sort()) {
    $sort = ' ORDER BY  '.$table->get_sql_sort();
} else {
    $sort = ' ORDER BY annualityname ASC, periodname ASC ';
}

$sort .= ', e.programme ASC,  c.shortname ASC ';

$stredit   = get_string('edit');
$strdelete = get_string('delete');
$straddcall = get_string('addextracall', 'examregistrar');
$stradddelivery = get_string('adddelivery', 'examregistrar');
$examurl = new \moodle_url('/course/view.php', array('id' => 0));

$elements = $DB->get_records_sql($select.$sql.$where.$sort, $params, $table->get_page_start(), $table->get_page_size());
if($elements) {
    foreach($elements as $element) {
        $data = array();
        $rowclass = '';
        $data[] = $table->col_checkbox($element);
        $data[] = $table->col_formatitem($element->annualityname, $element->annualityidnumber);
        //$data[] = $table->col_formatitem('', $element->programme);
        $rowdata = $table->col_formatitem('', $element->programme).' - '. $table->col_formatitem('', $element->shortname);
        $examurl->param('id', $element->courseid);
        $data[] = \html_writer::link($examurl, $rowdata).\html_writer::div($element->fullname, 'reduced');
        //$data[] = \html_writer::link($examurl, $table->col_formatitem('', $element->shortname)).' - '.$element->fullname;

        $rowclass = examregistrar_check_exam_term($element);
        $data[] = $table->col_formatitem($element->periodname, $element->periodidnumber, $rowclass);
        $data[] = $table->col_formatitem($element->scopename, $element->scopeidnumber);
        $data[] = $element->callnum;

        $rowclass = examregistrar_check_exam_session($element);
        $rowdata = $element->examsession ? $table->col_formatitem($element->sessionname, $element->sessionidnumber, $rowclass) : ''; 
        $rowdata .= $element->examdate ? \html_writer::div(userdate($element->examdate, get_string('strftimedaydate')), ' reduced '): '';
        $data[] = $rowdata;
        
        $rowdata = examregistrar_exam_delivery_instances($element, $manageurl, true);
        $data[] = $rowdata;
        
        $rowclass = '';
        if(!$element->visible) {
            $rowclass = 'dimmed_text';
        }

        $visible = -$element->id;
        $visicon = 'show';
        $strvisible = get_string('hide');
        if(!$element->visible) {
            foreach($data as $key => $value) {
                $data[$key] = html_writer::span($value, 'dimmed_text');
            }
            $visible = $element->id;
            $visicon = 'hide';
            $strvisible = get_string('show');
        }

        $action = '';
        if (!$table->is_downloading()) {
            $buttons = array();
            $url = new moodle_url($manageurl, array('show'=>$visible));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/'.$visicon, $strvisible, 'moodle', array('class'=>'iconsmall', 'title'=>$strvisible)));
            $url = new moodle_url($manageurl, array('item'=>$element->id));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit, 'moodle', array('class'=>'iconsmall', 'title'=>$stredit)));
            $url = new moodle_url($manageurl, array('del'=>$element->id));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete)));
            
            $actionurl = new moodle_url('/mod/examregistrar/manage/action.php', array('id'=>$cm->id,'edit'=>$edit)+$selparams);
            $actionurl->params(array('action'=>'addextracall', 'exam'=>$element->id));
            $buttons[] = html_writer::link($actionurl, $OUTPUT->pix_icon('i/addblock', $straddcall, 'moodle', array('class'=>'iconsmall', 'title'=>$straddcall)));
            
            /*
            //$actionurl = new moodle_url('/mod/examregistrar/manage/action.php', array('id'=>$cm->id,'edit'=>$edit));
            $actionurl->params(array('action'=>'adddelivery', 'exam'=>$element->id));
            $buttons[] = html_writer::link($actionurl, $OUTPUT->pix_icon('t/copy', $stradddelivery, 'moodle', array('class'=>'iconsmall', 'title'=>$stradddelivery)));
            */
            
            $action = implode('&nbsp;&nbsp;', $buttons);
        }
        $data[] = $action;

        $table->add_data($data, $rowclass);
    }

    $table->finish_output();

} else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
}


