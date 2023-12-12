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
 * Examregistrar module renderer
 *
 * @package    mod
 * @subpackage examregistrar
 * @copyright  2014 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examregistrar\output;
 
defined('MOODLE_INTERNAL') || die();

use html_writer;
use context_course;
use context_module;
use html_table;
use html_table_row;
use html_table_cell;
use single_select;
use moodle_url;
use pix_icon;

require_once($CFG->dirroot . '/mod/examregistrar/locallib.php');

/// TODO Eliminar e incluir en otro sitio TODO ///
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_table.php");



class renderer extends \plugin_renderer_base {

    /**
     * Returns html to display a tuple of name/idnumber
     *
     * @param string $name
     * @param string $idnumber
     * @param array $filters array of filter params
     * @return string
     */
    public function formatted_name($name, $idnumber='', $options=array()) {
        $formattedname = '';
        $formattedtail = '';

        $formatoptions = array('filter'=>false, 'para'=>false);
        foreach($options as $key => $value) {
            $formatoptions[$key] = $value;
        }

        if($name) {
            $formattedname = format_text($name, FORMAT_MOODLE, array('filter'=>false, 'para'=>false));
        }
        if($idnumber) {
            $formattedtail = format_string($idnumber, FORMAT_MOODLE, array('filter'=>false, 'para'=>false));
            if($name) {
                $formattedtail = " ($formattedtail)";
            }
        }
        return $formattedname.$formattedtail;
    }

    /**
     * Returns html to display a tuple of name/idnumber from an object
     *
     * @param stdClass $item an object or record with two properties, name & idnumber
     * @param array $filters array of filter objects
     * @return string
     */
    public function formatted_itemname($item, $options=array()) {
        return $this->formatted_name($item->name, $item->idnumber, $options);
    }


    /**
    * Returns a formatted name { name (idnumber) } form an ID of al element
    *
    * @param int $itemid the ID if the item in the table
    * @param string $table table where this ID is located
    * @param string $field of element type
    * @param string $only return only name or idnumber
    * @return array element name, idnumber
    */
    public function formatted_name_fromid($itemid, $table = '', $field = '', $options=array(), $only=false) {
        list($name, $idnumber) = examregistrar_get_namecodefromid($itemid, $table, $field);
        if($only == 'name') {
            $idnumber = '';
        }
        if($only == 'idnumber') {
            $name = '';
        }
        return $this->formatted_name($name, $idnumber, $options);
    }


    
    /**
     * Returns html to display a tuple of name/idnumber from an object
     *
     * @param stdClass $item an object or record with two properties, name & idnumber
     * @param MoodleQuickForm $mform The user input form to display
     * @return void
     */
    public function print_input_form_and_die($mform, $heading) {
        echo $this->header();
        echo $this->heading($heading);
        $mform->display();
        echo $this->footer();
        die;    
    }
    
    
    
    
    
/**
 * Returns HTML suitable for an exam booking form, with presentation and venue data.
 *
 * @param object $examregistrar instance object
 * @param object $course record object for course calling this (where instance is placed)
 * @param class $baseurl moodle_url class
 * @param array $searchparams parameteres need for the user course/exam searching
 * @param string $selector containing a list period, session, venue of the posiibl emain selectors: single_select forms
 * @return string HTML
 */
    public function exams_item_selection_form($examregistrar, $course, $baseurl, $searchparams, $selectors = 'period', $nothing = true) {

        $period = isset($searchparams['period']) ? $searchparams['period'] : 0;
        $session = isset($searchparams['session']) ? $searchparams['session'] : 0;
        $bookedsite = isset($searchparams['venue']) ? $searchparams['venue'] : 0;

        if(!$nothing) {
            $nothing = array(0 => 'choosedots');
        } else {
            $nothing = '';
        }

        $examregprimaryid = examregistrar_get_primaryid($examregistrar);

        $output = '';

        $output .= $this->output->container_start('examregistrarfilterform clearfix ');
            if(strpos($selectors, 'period') !== false) {
                $periodmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'periods', 'perioditem', $examregprimaryid);
                $select = new single_select(new moodle_url($baseurl), 'period', $periodmenu, $period, $nothing);
                $select->set_label(get_string('perioditem', 'examregistrar'), array('class'=>'singleselect  filter'));
                $select->class .= ' filter ';
                $output .= $this->output->render($select);
            }
            if(strpos($selectors, 'session') !== false) {
                $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, '', '', array('period'=>$period), 't.examdate ASC');
                $select = new single_select(new moodle_url($baseurl), 'session', $sessionmenu, $session, '');
                $select->set_label(' &nbsp; &nbsp; '.get_string('examsessionitem', 'examregistrar'), array('class'=>' singleselect filter'));
                $select->class .= ' filter ';
                $output .= $this->output->render($select);
            }
            if(strpos($selectors, 'venue') !== false) {
                $venueelement = examregistrar_get_venue_element($examregistrar);
                $venuemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose', '', array('locationtype'=>$venueelement));
                if(!has_capability('mod/examregistrar:showvariants', context_course::instance($course->id))) {
                    $venues = examregistrar_get_user_venues($examregistrar, 0, $session);
                    foreach($venuemenu as $key => $venue) {
                        if(!isset($venues[$key])) {
                            unset($venuemenu[$key]);
                        }
                    }
                }
                //natcasesort($venuemenu);
                $select = new single_select(new moodle_url($baseurl), 'venue', $venuemenu, $bookedsite);
                $select->set_label(' &nbsp; &nbsp; '.get_string('venue', 'examregistrar'), array('class'=>'singleselect  filter'));
                $select->class .= ' filter ';
                $output .= $this->output->render($select);
            }

        $output .= $this->output->container_end();
        return $output;
    }


/**
 * Returns HTML suitable for an exam booking form, with presentation and venue data.
 *
 * @param object $examregistrar instance object
 * @param object $course record object for course calling this (where instance is placed)
 * @param class $baseurl moodle_url class
 * @param array $searchparams parameteres need for the user course/exam searching
 * @return string HTML
 */
    public function exams_courses_selector_form($examregistrar, $course, $baseurl, $searchparams) {
        global $DB, $USER;

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

        $output = '';

        // if many courses, print filter selector
        if($examregistrar->workmode != EXAMREGISTRAR_MODE_VIEW) {
            $output .= $this->output->container_start('examregistrarfilterform clearfix ');
                $output .= $this->output->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

                $output .= '<form id="examregistrarfilterform" action="'.$baseurl->out_omit_querystring().'" method="post">';
                $output .= html_writer::input_hidden_params($baseurl);

                if($examregistrar->workmode == EXAMREGISTRAR_MODE_REGISTRAR) {
                    if($examregistrar->programme) {
                        $programmemenu = array($examregistrar->programme=>$examregistrar->programme);
                    } else {
                        $programmemenu = examregistrar_elements_get_fieldsmenu($examregistrar, 'exams', 'programme', $examregprimaryid);
                    }
                    $output .= html_writer::label(get_string('programme', 'examregistrar').': ', 'programme');
                    $output .= html_writer::select($programmemenu, 'programme', $programme);
                    $output .= ' &nbsp; ';
                }

                if(get_config('local_ulpgcgcore')) {
                    $termmenu = examregistrar_elements_getvaluesmenu($examregistrar, 'termitem', $examregprimaryid);
                    $output .= html_writer::label(get_string('termitem', 'examregistrar').': ', 'term');
                    $output .= html_writer::select($termmenu, "term", $term);
                }

                $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'period', 'value'=>$period));

                $output .= html_writer::label(get_string('searchname', 'examregistrar'), 'searchname');
                $output .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'searchname', 'value'=>$searchname, 'size'=>20));
                $output .= ' &nbsp; ';

                if($examregistrar->workmode == EXAMREGISTRAR_MODE_REVIEW && !$examregistrar->programme) {
                    $coursemenu = $DB->get_records_menu('course', array('category'=>$course->category), 'shortname', 'id,shortname');
                } else {
                    $coursemenu = examregistrar_get_courses_examsmenu($examregistrar,  $examregprimaryid, array('period'=>$period), '', 'shortname', false);
                }
                $output .= html_writer::label(get_string('shortname', 'examregistrar').': ', 'searchid');
                $output .= html_writer::select($coursemenu, "searchid", $searchid);
                $output .= ' <br />';

                $sortmenu = array('fullname'=>get_string('resortbyfullname', 'examregistrar'),
                                'shortname'=>get_string('resortbyshortname', 'examregistrar'),
                                'idnumber'=>get_string('resortbyidnumber', 'examregistrar') );
                $output .= html_writer::label(get_string('sortby').': ', 'sorting');
                $output .= html_writer::select($sortmenu, "sorting", $sort);
                $output .= ' &nbsp; ';

                $ordermenu = array('ASC'=>get_string('asc', 'examregistrar'), 'DESC'=>get_string('desc', 'examregistrar'));
                $output .= html_writer::label(get_string('order').': ', 'order');
                $output .= html_writer::select($ordermenu, "order", $order);
                $output .= ' &nbsp;   &nbsp; ';

                $output .= '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
                $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'period', 'value'=>$period));
                $output .= '</form>'."\n";
                $output .= '<br  />';
            $output .= $this->output->container_end();
        }

        return $output;
    }


/**
 * Returns HTML suitable for an exam booking form, with presentation and venue data.
 *
 * @param object $examregistrar instance object
 * @param object $course record object for course calling this (where instance is placed)
 * @param class $baseurl moodle_url class
 * @param array $searchparams parameteres need for the user course/exam searching
 * @param string $selector containing a list period, session, venue of the posiibl emain selectors: single_select forms
 * @return string HTML
 */
    public function exams_courses_selectorform($examregistrar, $course, $baseurl, $searchparams, $selectors = 'period', $nothing = true) {
        global $USER;

        $period = isset($searchparams['period']) ? $searchparams['period'] : 0;
        $searchname = isset($searchparams['searchname']) ? $searchparams['searchname'] : '';
        $searchid = isset($searchparams['searchid']) ? $searchparams['searchid'] : 0;
        $sort = isset($searchparams['sorting']) ? $searchparams['sorting'] : '';
        $order = isset($searchparams['order']) ? $searchparams['order'] : '';
        $programme = isset($searchparams['programme']) ? $searchparams['programme'] : '';
        $userid = isset($searchparams['user']) ? $searchparams['user'] : $USER->id;
        $session = isset($searchparams['session']) ? $searchparams['session'] : 0;
        $bookedsite = isset($searchparams['venue']) ? $searchparams['venue'] : 0;

        if(!$nothing) {
            $nothing = array(0 => 'choosedots');
        } else {
            $nothing = '';
        }

        $examregprimaryid = examregistrar_get_primaryid($examregistrar);

        $output = '';

        $output .= $this->output->container_start('examregistrarfilterform clearfix ');
            if(strpos($selectors, 'period') !== false) {
                $periodmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'periods', 'perioditem', $examregprimaryid);
                $select = new single_select(new moodle_url($baseurl), 'period', $periodmenu, $period, $nothing);
                $select->set_label(get_string('perioditem', 'examregistrar'), array('class'=>'singleselect  filter'));
                $select->class .= ' filter ';
                $output .= $this->output->render($select);
            }
            if(strpos($selectors, 'session') !== false) {
                $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, '', '', array(), 't.examdate ASC');
                $select = new single_select(new moodle_url($baseurl), 'session', $sessionmenu, $session, '');
                $select->set_label(' &nbsp; &nbsp; '.get_string('examsessionitem', 'examregistrar'), array('class'=>' singleselect filter'));
                $select->class .= ' filter ';
                $output .= $this->output->render($select);
            }
            if(strpos($selectors, 'venue') !== false) {
                $venueelement = examregistrar_get_venue_element($examregistrar);
                $venuemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose', '', array('locationtype'=>$venueelement));
                if(!has_capability('mod/examregistrar:showvariants', context_course::instance($course->id))) {
                    $venues = examregistrar_get_user_rooms($examregistrar, 0, $venueelement);
                    foreach($venuemenu as $key => $venue) {
                        if(!isset($venues[$key])) {
                            unset($venuemenu[$key]);
                        }
                    }
                }
                //natcasesort($venuemenu);
                $select = new single_select(new moodle_url($baseurl), 'venue', $venuemenu, $bookedsite);
                $select->set_label(' &nbsp; &nbsp; '.get_string('venue', 'examregistrar'), array('class'=>'singleselect  filter'));
                $select->class .= ' filter ';
                $output .= $this->output->render($select);
            }

        $output .= $this->output->container_end();

        // if many courses, print filter selector
        if($examregistrar->workmode != EXAMREGISTRAR_MODE_VIEW) {
            $output .= $this->output->container_start('examregistrarfilterform clearfix ');
                $output .= $this->output->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

                $output .= '<form id="examregistrarfilterform" action="'.$baseurl->out_omit_querystring().'" method="post">';
                $output .= html_writer::input_hidden_params($baseurl);

                if($examregistrar->workmode == EXAMREGISTRAR_MODE_REGISTRAR) {
                    if($examregistrar->programme) {
                        $programmemenu = array($examregistrar->programme=>$examregistrar->programme);
                    } else {
                        $programmemenu = examregistrar_elements_get_fieldsmenu($examregistrar, 'exams', 'programme', $examregprimaryid);
                    }
                    $output .= html_writer::label(get_string('programme', 'examregistrar').': ', 'programme');
                    $output .= html_writer::select($programmemenu, 'programme', $programme);
                    $output .= ' &nbsp; ';
                }

                $output .= html_writer::label(get_string('searchname', 'examregistrar'), 'searchname');
                $output .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'searchname', 'value'=>$searchname, 'size'=>20));
                $output .= ' &nbsp; ';


                if($examregistrar->workmode == EXAMREGISTRAR_MODE_REVIEW && !$examregistrar->programme) {
                    $coursemenu = $DB->get_records_menu('course', array('category'=>$course->category), 'shortname', 'id,shortname');
                } else {
                    $coursemenu = examregistrar_get_courses_examsmenu($examregistrar,  $examregprimaryid, array('period'=>$period), '', 'shortname', false);
                }
                $output .= html_writer::label(get_string('shortname', 'examregistrar').': ', 'searchid');
                $output .= html_writer::select($coursemenu, "searchid", $searchid);
                $output .= ' <br />';

                $sortmenu = array('fullname'=>get_string('resortbyfullname'),
                                'shortname'=>get_string('resortbyshortname'),
                                'idnumber'=>get_string('resortbyidnumber') );
                $output .= html_writer::label(get_string('sortby').': ', 'sorting');
                $output .= html_writer::select($sortmenu, "sorting", $sort);
                $output .= ' &nbsp; ';

                $ordermenu = array('ASC'=>get_string('asc', 'examregistrar'), 'DESC'=>get_string('desc', 'examregistrar'));
                $output .= html_writer::label(get_string('order').': ', 'order');
                $output .= html_writer::select($ordermenu, "order", $order);
                $output .= ' &nbsp;   &nbsp; ';

                $output .= '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
                $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'period', 'value'=>$period));
                $output .= '</form>'."\n";
                $output .= '<br  />';
            $output .= $this->output->container_end();
        }

        return $output;
    }



    public function print_exams_courses_view($examregistrar, $cm, $course, $context, $baseurl) {

        require_capability('mod/examregistrar:view',$context);

        $baseurl = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cm->id,'tab'=>'view'));
        $tab = 'view';

        $now = time();
        //$now = strtotime('15 july 2014') + 3605;

        $period   = optional_param('period', '', PARAM_INT);
        $term   = optional_param('term', 0, PARAM_INT);
        $programme   = optional_param('programme', '', PARAM_ALPHANUMEXT);
        $searchname = optional_param('searchname', '', PARAM_TEXT);
        $searchid = optional_param('searchid', '', PARAM_INT);
        $sort = optional_param('sorting', 'shortname', PARAM_ALPHANUM);
        $order = optional_param('order', 'ASC', PARAM_ALPHANUM);
        $baseparams = array('exreg' => $examregistrar, 'id'=>$cm->id, 'tab'=>$tab);
        $viewparams = array('period'=>$period,
                            'term'=>$term,
                            'searchname'=>$searchname,
                            'searchid'=>$searchid,
                            'programme'=>$programme,
                            'sorting'=>$sort,
                            'order'=>$order );

        $viewurl = new moodle_url($baseurl, $viewparams);

        $annuality =  examregistrar_get_annuality($examregistrar);

        $canviewall = has_capability('mod/examregistrar:viewall', $context);

        $courses = examregistrar_get_user_courses($examregistrar, $course, $viewparams, array('mod/examregistrar:view'), $canviewall);

        if ($examregistrar->intro) { // Conditions to show the intro can change to look for own settings or whatever
            echo $this->output->box(format_module_intro('examregistrar', $examregistrar, $cm->id), 'generalbox mod_introbox', 'examregistrarintro');
        }

        echo $this->exams_item_selection_form($examregistrar, $course, $baseurl, $viewparams, 'period', false);
        if($canviewall) {
            echo $this->exams_courses_selector_form($examregistrar, $course, $baseurl, $viewparams);
        }

        // print table header
        /// get period name & code

        $periodname = '';
        if($period) {
            list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period, 'periods', 'period');
        }
        echo $this->output->heading(get_string('examsforperiod', 'examregistrar', $periodname));

        $single = count($courses) > 1 ? false : true;
        foreach($courses as $examcourse) {
            $courseview = new \examregistrar_exams_course($examregistrar, $examcourse, $period, $annuality, $viewurl, $single);
            echo $this->render($courseview);
        }
    }


    public static function get_responses_icon($status, $url = null) {

        if($status == EXAM_RESPONSES_UNSENT) {
            $icon = 'circle-o';
            $statusclass = 'unsent';
        } elseif($status == EXAM_RESPONSES_WAITING) {
            $icon = 'info-circle';
            $statusclass = 'waiting';
        } elseif($status == EXAM_RESPONSES_REJECTED) {
            $icon = 'exclamation-triangle';
            $statusclass = 'rejected';
        } elseif($status < EXAM_RESPONSES_WAITING) {
            $icon = 'dot-circle-o';
            $statusclass = 'sent';
        } elseif($status > EXAM_RESPONSES_REJECTED) {
            $icon = 'check-circle';
            $statusclass = 'approved';
        }
        
        $title = get_string('response_'.$statusclass, 'examregistrar');        
                            
        $icon = html_writer::tag('i', '', array('class' => "fa fa-$icon responseicon $statusclass",
                                                'title' => $title,
                                                'aria-label' => $title,
                                                ));
                                                
        if($url) {
            $icon = html_writer::link($url, $icon, array('aria-label' => get_string('responsesupload', 'examregistrar'),
                                                            'title' => ' xxx'));
        } else {
            $icon = html_writer::span($icon, 'fa-pull-left');
        }
    
        return $icon; 
    }
    

    public function list_allocatedroomexam(\examregistrar_roomexam $exam, $downloading=false) {
        $output = '';
        //$output .= "{$exam->programme}-{$exam->shortname} - {$exam->fullname} ({$exam->seated}) ";
        $output .= $exam->get_exam_name(true, true, true, false). " ({$exam->seated}) "; ;

        if(!$downloading) {
            $url = new moodle_url('/mod/examregistrar/view.php');
            //$icon = $this->pix_icon('t/restore', get_string('edit'), '', array('class' => 'iconlarge'));
            $icon = new pix_icon('i/cohort', get_string('downloaduserlist', 'examregistrar'), 'core', array('class' => 'iconlarge'));
            $output .=  '&nbsp; '.$this->output->action_icon($url, $icon);

            if($message = $exam->set_valid_file()) {
                $icon = new pix_icon('i/risk_xss', $message, '', array('class' => 'iconlarge'));
                $output .= '&nbsp; '.$this->output->render($icon);
            } else {
                $context = context_course::instance($exam->courseid);
                $url = examregistrar_file_encode_url($context->id, $exam->examfile, 'exam');
                $icon = new pix_icon('t/print', get_string('printexam', 'examregistrar'), '', array('class' => 'iconlarge'));
                $output .= '&nbsp; '.$this->output->action_icon($url, $icon);
            }
            
            $label = '';
            $printmode = $exam->get_print_mode();
            $examinstructions = $exam->get_exam_instructions(); 
            if(!$printmode) {
                $label .= '<i class="fa fa-copy"></i>  '.get_string('printdouble', 'examregistrar');
            }
            if(!empty($examinstructions)) {
                $label .= '<br /> &nbsp; <i class="fa fa-calculator"> </i> '.get_string('examinstructions', 'examregistrar');
            }
            if($label) {
                $label = html_writer::span($label, 'alert-danger  fa-pull-right');
            }
            
            if(!empty($examinstructions)) {
                $attempt = new \stdClass();
                $attempt->printmode = $printmode;
                $attempt->allowedtools = $examinstructions;
                $content = $this->print_examiner_instructions($attempt, true, 'bottom');
                //$content = $this->output->container($label.$content, 'withinstructions');
                $output .= $this->output->container($label.$content, 'withinstructions');
            }
        }
        return $output;
    }    
    
    public function render_exam_user_list(exam_user_list $tree) {
        static $treecounter = 0;

        $nameformat = 'lastname firstname';
        $content = '';
        $id = 'folder_tree'. ($treecounter++);
        $content .= '<div id="'.$id.'" class="filemanagerWWWWWW">';
/*
        $items = array();
        $image = $this->output->pix_icon(file_folder_icon(24), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', s($subdir['dirname']), array('class' => 'fp-filename'));
            $filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
        $items[] = $filename;
        foreach($tree->users as $user) {
            if(!$user->idnumber) {
                $user->idnumber = '________';
            }
            $items[] = $user->idnumber. ' '.fullname($user, true, $nameformat);
        }
        $content .= html_writer::alist($items, array('class'=>' examuserslist '));*/
        $content .= $this->htmllize_tree($tree, array('files' => array(), 'subdirs' => array($tree->dir)));
        $content .= '</div>';
        $showexpanded = true;
        if (empty($tree->folder->showexpanded)) {
            $showexpanded = false;
        }
        $showexpanded = false;
        $this->page->requires->js_init_call('M.mod_examregistrar.init_tree', array($id, $showexpanded));
        return $content;
    }


    public function render_examregistrar_allocatedroom(\examregistrar_allocatedroom $room) {

        $name = $this->formatted_itemname($item);
        $this->output->heading($roomname, 3, ' leftalign ');

    }
    
    
    public function render_examregistrar_exams_course(\examregistrar_exams_course $courseview) {
        global $CFG, $DB;

        $baseurl = $courseview->url;
        $course = $courseview->course;
        $course->context = context_course::instance($course->id);

        $now = time();
        //$now = strtotime('15 july 2014') + 3605;

        $strexamfile = get_string('examfile', 'examregistrar');
        $strexamfileanswers = get_string('examfileanswers', 'examregistrar');
        $strexamfilekey = get_string('printexamkey', 'examregistrar');
        $struserlist = get_string('downloaduserlist', 'examregistrar');
        $strexamresponses = get_string('examresponses', 'examregistrar');
        $strexamrespfiles = get_string('examresponsefiles',  'examregistrar');
        $strdownload = get_string('examresponsesdown', 'examregistrar');

        $candownload = has_capability('mod/examregistrar:download', $course->context);
        $canmanage = has_capability('mod/examregistrar:manageexams', $course->context);
        $canbook = has_any_capability(array('mod/examregistrar:book', 'mod/examregistrar:bookothers'), $course->context);

        $output = '';
        $output .=  $this->output->container_start(' examcoursereview'  );

        if(!$courseview->single) {
            $url = new moodle_url('/course/view.php', array('id'=>$course->id));
            $examname = html_writer::link($url, $course->shortname.' - '.$course->fullname);
            $output .=  $this->output->heading($examname, 3, ' examheader ');
        }

        if($candownload && $courseview->single && $cmid = get_config('examregistrar', 'responsessheeturl')) {
            include_once($CFG->dirroot.'/mod/resource/lib.php');
            $message = get_string('printresponsessheet', 'examregistrar');
            $icon = new pix_icon('f/pdf', $message);

            $url = new moodle_url('/pluginfile.php/'.$course->context->id.'/mod_examregistrar/sheet/'.$cmid);

            $output .=  html_writer::link($url, $this->output->render($icon).'&nbsp;'.get_string('printresponsessheet', 'examregistrar'), array('class'=>' learge bold examreviewsynchronize '));
        }

        $courseview->set_exams();

        if($courseview->exams) {
            $table = new html_table();
            $table->attributes = array('class'=>'flexible examcourseviewtable' );
            $tableheaders = array(get_string('perioditem', 'examregistrar'),
                                  get_string('exam', 'examregistrar'),
                                    get_string('callnum', 'examregistrar'),
                                    get_string('examdate', 'examregistrar'),
                                    get_string('booked', 'examregistrar'));
            $table->head = $tableheaders;
            $table->colclasses = array('colperiod', 'colexam', 'colcall', 'coldate', 'colbooked');
            if($candownload) {
                $table->head = array_merge($tableheaders, array(get_string('exams', 'examregistrar'),get_string('taken', 'examregistrar')));
                $table->colclasses = array_merge($table->colclasses, array('colexamfile', 'coltaken'));
            } else {
                $table->head = array_merge($tableheaders, array(get_string('room', 'examregistrar')));
                $table->colclasses = array_merge($table->colclasses, array('colroom'));
            }
            $strdelete = get_string('delete');
            
            if($canbook && !$candownload) {
                $courseview->check_booked_exams();
            }
            
            foreach($courseview->exams as $exam) {
                if(($exam->callnum < 0) && !($exam->ownbook || $candownload)) {
                   continue; // do not show reserve exams to regular students
                }
                $message = $courseview->get_approved_exam($exam);
                $examfile = $courseview->examfiles[$exam->id];

                $cellperiod = $this->formatted_name_fromid($exam->period, 'periods');
                $cellcall = ($exam->callnum > 0) ? $exam->callnum : 'R'.abs($exam->callnum);
                $examscope =  $this->formatted_name_fromid($exam->examscope);
                $examsession = $exam->examsession ? $this->formatted_name_fromid($exam->examsession, 'examsessions') : '';
                $cellexam = $examscope.',  '.$examsession;
                $celldate = $exam->examdate ? userdate($exam->examdate, get_string('strftimedaydate')) : ''; //.'; '.$exam->timeslot : ''; // ecastro ULPGC eliminado por COVID, volver a poner XXXXXXXX XXXXXX

                $cellbooked = '';
                $cellexams = '';
                $celltaken = '';
                if($candownload) {
                    if($exam->bookings) {
                        $cellbooked = $exam->bookings;
                        $url = new moodle_url($baseurl, array('status'=>'send', 'attempt'=>$exam->id));
                        $icon = new pix_icon('i/cohort', $struserlist, 'moodle', array('class'=>'iconsmall', 'title'=>$struserlist));
                        $cellbooked .= ' &nbsp; '.$this->action_icon($url, $icon);
                        $userlist = array();
                        if($bookings = $courseview->get_exam_bookings($exam->id, ' siteidnumber ASC ')) {
                            foreach($bookings as $booking) {
                                if(!isset($userlist[$booking->sitename])) {
                                    $userlist[$booking->sitename] = array();
                                }
                                $userlist[$booking->sitename][] = fullname($booking, false, 'lastname');
                            }
                            foreach($userlist as $site => $names) {
                                $userlist[$site] = $site.' ('.count($userlist[$site]).') '.html_writer::alist($userlist[$site]);
                            }
                        }
                        $cellbooked = print_collapsible_region(html_writer::div(implode("\n", $userlist), ' userlist intable'), '', 'showhideexamuserlist'.$exam->id, $cellbooked, '', true, true) ;
                    } else {
                        $cellbooked = get_string('none');
                    }
                    if($examfile) {
                        $url = examregistrar_file_encode_url($course->context->id, $examfile->id, 'exam');
                        $icon = new pix_icon('f/pdf', $strexamfile, 'moodle', array('class'=>'iconsmall', 'title'=>$strexamfile));
                        $cellexams = $this->output->action_link($url,$examfile->idnumber, null, null, $icon);

                        $url = examregistrar_file_encode_url($course->context->id, $examfile->id, 'answers');
                        $icon = new pix_icon('i/permissions', $strexamfileanswers, 'moodle', array('class'=>'iconlarge', 'title'=>$strexamfileanswers));
                        $cellexams .= ' &nbsp;  &nbsp; '.$this->action_icon($url, $icon);

                        if($filename = examregistrar_file_get_filename($course->context->id, $examfile->id, 'key')) {
                            $url = examregistrar_file_encode_url($course->context->id, $examfile->id, 'key');
                            $icon = new pix_icon('i/completion-manual-y', $strexamfilekey, 'moodle', array('class'=>'icon', 'title'=>$strexamfilekey));
                            $cellexams .= ' &nbsp;  &nbsp; '.$this->action_icon($url, $icon);
                        }
                        $now = time() + 1;
                        $now = $exam->examdate + 1;
                        if($exam->examdate > $now) {
                            $celltaken = get_string('nottakenyet', 'examregistrar');
                        } elseif($examfile->taken < 0 ) {
                            $celltaken = get_string('nottaken', 'examregistrar');
                        } elseif($examfile->taken > 0) {
                            if($filenames = examregistrar_file_get_filename($course->context->id, $examfile->id, 'responses', true)) {
                                $celltaken = '';
                                if(count($filenames) > 25) {
                                    $icon = new pix_icon('a/download_all', $strdownload, 'moodle', array('class'=>'icon', 'title'=>$strdownload));
                                    $url = new moodle_url($baseurl, array('action'=>'download_files', 'exam'=>$examfile->id));
                                    $celltaken .= '&nbsp; '.$this->output->action_icon($url,$icon);

                                } else {
                                    foreach($filenames as $filename) {
                                        $url = examregistrar_file_encode_url($course->context->id, $examfile->id, 'responses', $filename);
                                        $icon = new pix_icon('t/download', $strexamresponses.' - '.$filename, 'moodle', array('class'=>'iconsmall', 'title'=>$strexamresponses.' - '.$filename));
                                        $celltaken .= ' '.$this->output->action_icon($url,$icon);
                                    }
                                }
                            } else {
                                $celltaken = get_string('filemissing', 'moodle', get_string('file'));
                            }
                        }
                        if($canmanage) {
                            $url = new moodle_url('view.php', $baseurl->params()+array('action'=>'response_files', 'exam'=>$examfile->id));
                            $icon = new pix_icon('t/edit', $strexamrespfiles, '', array('class' => 'iconsmall', 'title'=>$strexamrespfiles));
                            $item = $this->output->action_icon($url, $icon); //$output->render($icon);
                            $celltaken .= $item;
                        }
                    }
                } else {
                    $cellbooked = $exam->ownbook ?  $this->formatted_name_fromid($exam->ownbook, 'locations') : get_string('no');
                    if($exam->ownbook && isset($courseview->conflicts[$exam->examsession]) && $courseview->conflicts[$exam->examsession]) {
                        $cellbooked = html_writer::span($cellbooked, ' errorbox alert-error ');
                    }
                    $noroom = $exam->ownbook ? get_string('unallocatedyet', 'examregistrar') : '';
                    $cellroom = $exam->ownroom ?  $this->formatted_name_fromid($exam->ownroom, 'locations') : $noroom;
                }
                if($candownload) {
                    $row = new html_table_row(array($cellperiod, $cellexam, $cellcall, $celldate, $cellbooked, $cellexams, $celltaken));
                } else {
                    $row = new html_table_row(array($cellperiod, $cellexam, $cellcall, $celldate, $cellbooked, $cellroom));
                }
                $table->data[] = $row;

            }
            $output .= html_writer::table($table);
        }

        $output .=  $this->output->container_end();

        return $output;
    }


    public function print_examiner_instructions($attempt, $withprintmode = false, $direction = 'right') {
        $output = '';
    
        $output .= $this->container_start("examinstructions $direction  ");
        $output .= $this->container_start('instructionscontent');
            $output .= $this->output->heading(get_string('examinstructions', 'examregistrar'), 5);
            $content = [];
            if($withprintmode && isset($attempt->printmode)) {
                if(!$attempt->printmode) {
                    $content[] = '<i class="fa fa-copy"></i> '. get_string('printdouble', 'examregistrar');
                } 
            }
            
            if(isset($attempt->allowedtools) && !empty($attempt->allowedtools) && is_array($attempt->allowedtools)) {
                $last = '';
                foreach($attempt->allowedtools as $allowed => $value) {
                    if($allowed == 'textinstructions') {
                        $last = nl2br("$value");
                    } else {
                        $content[] = '<strong><i class="fa fa-check-square-o "></i> '. get_string('examallow_'.$allowed, 'examregistrar') . 
                                        '</strong><br />'.get_string('examallow_'.$allowed.'_help', 'examregistrar');
                    }
                } 
                if($last) {
                    $content[] = '<strong>' . get_string('examinstructionstext', 'examregistrar') . '</strong>' .
                                    '<br />'.$last;
                }
            }
            if($content) {
                $output .= html_writer::alist($content);
            }
        $output .= $this->container_end();
        $output .= '<i class="tip"></i>';
        $output .= $this->container_end();
    
        return $output;
    }



///////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////


    /**
     * Returns html to display the content of mod_folder
     * (Description, folder files and optionally Edit button)
     *
     * @param stdClass $folder record from 'folder' table (please note
     *     it may not contain fields 'revision' and 'timemodified')
     * @return string
     */
    public function display_folder(stdClass $folder) {
        $output = '';
        $folderinstances = get_fast_modinfo($folder->course)->get_instances_of('folder');
        if (!isset($folderinstances[$folder->id]) ||
                !($cm = $folderinstances[$folder->id]) ||
                !$cm->uservisible ||
                !($context = context_module::instance($cm->id)) ||
                !has_capability('mod/folder:view', $context)) {
            // some error in parameters or module is not visible to the user
            // don't throw any errors in renderer, just return empty string
            return $output;
        }

        if (trim($folder->intro)) {
            if ($folder->display != FOLDER_DISPLAY_INLINE) {
                $output .= $this->output->box(format_module_intro('folder', $folder, $cm->id),
                        'generalbox', 'intro');
            } else if ($cm->showdescription) {
                // for "display inline" do not filter, filters run at display time.
                $output .= format_module_intro('folder', $folder, $cm->id, false);
            }
        }

        $foldertree = new folder_tree($folder, $cm);
        if ($folder->display == FOLDER_DISPLAY_INLINE) {
            // Display module name as the name of the root directory.
            $foldertree->dir['dirname'] = $cm->get_formatted_name();
        }
        $output .= $this->output->box($this->render($foldertree),
                'generalbox foldertree');

        // Do not append the edit button on the course page.
        if ($folder->display != FOLDER_DISPLAY_INLINE && has_capability('mod/folder:managefiles', $context)) {
            $output .= $this->output->container(
                    $this->output->single_button(new moodle_url('/mod/folder/edit.php',
                    array('id' => $cm->id)), get_string('edit')),
                    'mdl-align folder-edit-button');
        }
        return $output;
    }

    public function render_folder_tree(folder_tree $tree) {
        static $treecounter = 0;

        $content = '';
        $id = 'folder_tree'. ($treecounter++);
        $content .= '<div id="'.$id.'" class="filemanager">';
        $content .= $this->htmllize_tree($tree, array('files' => array(), 'subdirs' => array($tree->dir)));
        $content .= '</div>';
        $showexpanded = true;
        if (empty($tree->folder->showexpanded)) {
            $showexpanded = false;
        }
        $this->page->requires->js_init_call('M.mod_folder.init_tree', array($id, $showexpanded));
        return $content;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree2($tree, $dir) {
        global $CFG;

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(24), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', s($subdir['dirname']), array('class' => 'fp-filename'));
            $filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename. $this->htmllize_tree($tree, $subdir));
        }
        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                    $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $filename, false);
            if (file_extension_in_typegroup($filename, 'web_image')) {
                $image = $url->out(false, array('preview' => 'tinyicon', 'oid' => $file->get_timemodified()));
                $image = html_writer::empty_tag('img', array('src' => $image));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $filename, 'moodle');
            }
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', $filename, array('class' => 'fp-filename'));
            $filename = html_writer::tag('span',
                    html_writer::link($url->out(false, array('forcedownload' => 1)), $filename),
                    array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename);
        }
        $result .= '</ul>';

        return $result;
    }
    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree($tree, $dir) {
        global $CFG;

        $nameformat = 'lastname firstname';
        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(24), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', $tree->head, array('class' => 'fp-filename'));
            $filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename. $this->htmllize_tree($tree, $subdir));
        }
        foreach ($dir['files'] as $user) {
            if(!$user->idnumber) {
                $user->idnumber = '________';
            }
            $name = "[{$user->id}] ". $user->idnumber. ' '.fullname($user, false, $nameformat). " adds: [{$user->additional}]"  ;
            $result .= html_writer::tag('li', $name);
        }
        $result .= '</ul>';

        return $result;
    }

    /**
     * Helper method dealing with the fact we can not just fetch the output of flexible_table
     *
     * @param flexible_table $table The table to render
     * @param int $rowsperpage How many assignments to render in a page
     * @param bool $displaylinks - Whether to render links in the table
     *                             (e.g. downloads would not enable this)
     * @return string HTML
     */
    protected function flexible_table(flexible_table $table, $rowsperpage, $displaylinks) {

        $o = '';
        ob_start();
        $table->finish_output();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }


}

class folder_tree implements \renderable {
    public $context;
    public $folder;
    public $cm;
    public $dir;

    public function __construct($folder, $cm) {
        $this->folder = $folder;
        $this->cm     = $cm;

        $this->context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, 'mod_folder', 'content', 0);
    }
}

class exam_user_list implements \renderable {
    public $users;
    public $dir;

    public function __construct($users, $head) {
        $this->head = $head;
        $this->users = $users;
        $this->dir = array('dirname'=>'', 'dirfile'=>null, 'subdirs'=>array(), 'files'=>$users);
    }
}
