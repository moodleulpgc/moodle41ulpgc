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
 * Defines the renderer for the quiz_makeexam module.
 *
 * @package   quiz_makeexam
 * @copyright 2019 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_makeexam\output;

use context_module;
use html_writer;
use moodle_url;
use single_button;
use pix_icon;
use html_table_row;
use html_table;
use plugin_renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * The renderer for the quiz_makeexam report
 *
 * @copyright  2019 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Clears the quiz removing listed questions
     *
     * @param object $quiz the quiz instance this Make Exam is called from
     * @param object $cm the course module object of this quiz
     * @param object $coures the course settings.
     */
    public function print_clearquiz_button($disabled = false) {
        $url = new moodle_url($this->page->url, array('confirm'=>1, 'sesskey'=>sesskey(), 'clearquiz'=>1));
        $button = new single_button($url, get_string('clearattempts', 'quiz_makeexam'));
        $button->class = 'makeexambutton';

        if($disabled) {
            $button->disabled = 'disabled';
        }
        $button->add_confirm_action(get_string('clear_confirm', 'quiz_makeexam'));
        echo $this->container($this->render($button), ' makeexambuttonform clearfix ');
        echo $this->container('', 'clearfix');
    }

    /**
     * Prints header with exam identification data: Period, scope, call, session,
     *
     * @param object $exam the exam (single period, scope, call) being printed
     * @param object $course the course settings object.
     */
    public function print_exam_header($exam) {
        global $PAGE;

        $items = array();

        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
        $items[] = get_string('perioditem', 'examregistrar').': '.$name.' ('.$idnumber.')';

        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
        $items[] = get_string('scopeitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

        $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
        $items[] = get_string('examsessionitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

        echo $this->heading(implode('; ', $items), 3, ' makeexam_examheader'  );
    }

    /**
     * Prints the table of exam attempts existing for this exam
     *
     * @param object $quiz the quiz instance this Make Exam is called from
     * @param object $exam the exam (single period, scope, call) being printed
     */
    public function print_exam_attempts($quiz, $exam, $examregistrar) {
        global $CFG, $DB, $PAGE;

        $courseid = $this->page->course->id;
        $reporturl = $this->page->url;

        if($exam->attempts) {
            $table = new html_table();
            $table->attributes = array('class'=>'flexible makeexamreviewtable' );
            $tableheaders = array(get_string('attempt', 'quiz_makeexam'),
                                  get_string('generatinguser', 'quiz_makeexam'),
                                  get_string('questions', 'quiz'),
                                    get_string('status', 'quiz_makeexam'),
                                    get_string('attempts', 'examregistrar'),
                                    get_string('statereview', 'examregistrar'),
                                    get_string('action'),

                                    );
            $table->head = $tableheaders;
            $table->colclasses = array('colattempt', 'colgeneratinguser', 'colquestions', 'colstatus', 'colattempts', 'colstatereview', 'colqaction');

            $strdelete  = get_string('delete');
            $strsent    = get_string('sent', 'quiz_makeexam');
            $strunsent  = get_string('unsent', 'quiz_makeexam');
            $strunsend  = get_string('unsend', 'quiz_makeexam');
            $strsubmit  = get_string('submit', 'quiz_makeexam');
            $strnew     = get_string('newattempt', 'quiz_makeexam');
            $strpdf     = get_string('pdfpreview', 'quiz_makeexam');
            $strgoexreg = get_string('gotoexamreg', 'quiz_makeexam');
            $strgoattempt = get_string('setquestions', 'quiz_makeexam');
            $strgoreport = get_string('gotootherquiz', 'quiz_makeexam');

            $strapproved = get_string('approved', 'examregistrar');
            $strrejected = get_string('rejected', 'examregistrar');
            $strexamfile = get_string('examfile', 'examregistrar');

            $cansubmit = has_capability('quiz/makeexam:submit', $this->page->context);
            $candelete = has_capability('quiz/makeexam:delete', $this->page->context);
            $nochecklimit = has_capability('quiz/makeexam:nochecklimit', $this->page->context);
            $canuseany = has_capability('quiz/makeexam:anyquestions', $this->page->context);
            $canmanage = has_capability('mod/examregistrar:manageexams',
                                        $this->page->context->get_course_context());

            foreach($exam->attempts as $attempt) {
                $celln = $attempt->attempt;
                $user = $DB->get_record('user', array('id'=>$attempt->userid),
                                                        implode(',', \core_user\fields::get_name_fields()));
                $url = new moodle_url('/user/view.php', array('id'=>$attempt->userid, 'course'=>$courseid));
                $cellgeneratedby = html_writer::link($url, fullname($user));
                $cellgeneratedby .= '<br />'. userdate($attempt->timemodified, get_string('strftimerecent'));

                list($numquestions, $invalid, $errors) = $attempt->check_questions_results;
                $cellquestions = $numquestions;
                if($invalid OR $errors) {
                    $content = '';
                    if($invalid) {
                        $content .= $invalid;
                    }
                    if($errors) {
                        if($content) {
                            $content .= '<br />';
                        }
                        $content .= $errors;
                    }
                    $cellquestions .= '<br />'.print_collapsible_region($content, ' error ', 'showhideerror_'.$attempt->id,
                                                                        get_string('generate_errors','quiz_makeexam'), 'examattempterror_'.$attempt->id, true, true);
                }

                if($attempt->status) {
                    $icon = $this->pix_icon('i/completion-manual-enabled', $strsent, 'moodle', array('class'=>'icon', 'title'=>$strsent));
                } else {
                    $icon = $this->pix_icon('i/completion-manual-n', $strunsent, 'moodle', array('class'=>'icon', 'title'=>$strunsent));
                }
                $cellstatus = $icon;

                $name = $attempt->name.' ('.userdate($attempt->timecreated, get_string('strftimerecent')).') ';
                if($quiz->id == $attempt->quizid) {
                    //$url = new moodle_url($reporturl,  array('review'=>$attempt->id, 'confirm'=>1, 'sesskey'=>sesskey()));
                    $url = new moodle_url($reporturl,  array('review'=>$attempt->id, 'confirm'=>1));
                    $name = html_writer::link($url, $name);
                    $url = new moodle_url($reporturl,  array('examid'=>$attempt->examid, 'pdf'=>$attempt->id, 'confirm'=>1, 'sesskey'=>sesskey()));
                    $icon = new pix_icon('f/pdf-32', $strpdf, 'moodle', array('class'=>'iconlarge', 'title'=>$strpdf));
                    $cellattempt = $name.'&nbsp;   &nbsp;'.$this->action_icon($url, $icon);
                } else {
                    $cellattempt = $name;
                }
                if($attempt->timesubmitted) {
                    $cellattempt .= '<br />'.get_string('sent', 'examregistrar').': '.userdate($attempt->timesubmitted, get_string('strftimedaydatetime'));
                }

                // if ANY examfile for this exmid is approved, or sent without resolution then no more exam submitting allowed
                foreach($exam->examfiles as $item) {
                    if(($item->status != EXAM_STATUS_CREATED) && ($item->status != EXAM_STATUS_REJECTED) ) {
                        $cansubmit = false;
                    }
                }

                $attempt->reviewstatus = $attempt->examfileid ? $exam->examfiles[$attempt->examfileid]->status : 0;

                $icon = '';
                switch($attempt->reviewstatus) {
                    case EXAM_STATUS_SENT       : $icon = $this->pix_icon('sent', $strsent, 'mod_examregistrar', array('class'=>'iconlarge', 'title'=>$strsent));
                                                    break;
                    case EXAM_STATUS_WAITING    : $icon = $this->pix_icon('waiting', $strsent, 'mod_examregistrar', array('class'=>'iconlarge', 'title'=>$strsent));
                                                    break;
                    case EXAM_STATUS_REJECTED   : $icon = $this->pix_icon('rejected', $strrejected, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strrejected));
                                                  $time = $exam->examfiles[$attempt->examfileid]->timerejected;
                                                  $cellattempt .= '<br />'.get_string('rejected', 'examregistrar').': '.userdate($time, get_string('strftimedaydatetime'));
                                                    break;
                    case EXAM_STATUS_APPROVED   :
                    case EXAM_STATUS_VALIDATED  : $icon = $this->pix_icon('approved', $strapproved, 'mod_examregistrar', array('class'=>'iconlarge', 'title'=>$strapproved));
                                                  $time = $exam->examfiles[$attempt->examfileid]->timeapproved;
                                                  $cellattempt .= '<br />'.get_string('approved', 'examregistrar').': '.userdate($time, get_string('strftimedaydatetime'));
                                                    break;
                }
                $cellstatereview = $icon;

                $actions = array();

                if($quiz->id == $attempt->quizid) {
                    if($candelete && !$attempt->reviewstatus >= EXAM_STATUS_APPROVED) {
                        $icon = new pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete));
                        $url = new moodle_url($reporturl, array('delete'=>$attempt->id, 'examid'=>$attempt->examid, 'sesskey'=>sesskey()));
                        $actions[] = $this->action_icon($url, $icon);
                    }

                    if($cansubmit && $attempt->status == 0 && (!$invalid || $canuseany) && (!$errors || $nochecklimit) ) {
                        $icon = new pix_icon('i/completion-auto-pass', $strsubmit, 'moodle', array('class'=>'iconlarge', 'title'=>$strsubmit));
                        $url = new moodle_url($reporturl, array('submit'=>$attempt->id, 'examid'=>$attempt->examid, 'sesskey'=>sesskey()));
                        $actions[] = $this->action_icon($url, $icon);
                    }

                    if($attempt->examfileid && isset($examregistrar->cmid)) {
                        $icon = new pix_icon('i/checkedcircle', $strgoexreg, 'moodle', array('class'=>'icon', 'title'=>$strgoexreg));
                        $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$examregistrar->cmid, 'tab'=>'review', 'period'=>$exam->period, 'sesskey'=>sesskey()));
                        $actions[] = $this->action_icon($url, $icon);
                    }

                    if($canmanage && $attempt->status) {
                        $icon = new pix_icon('i/completion-manual-n', $strunsend, 'moodle', array('class'=>'iconsmall', 'title'=>$strunsend));
                        $url = new moodle_url($reporturl, array('unsend'=>$attempt->id, 'examid'=>$attempt->examid, 'sesskey'=>sesskey()));
                        $actions[] = $this->action_icon($url, $icon);
                    }
                } else {
                    $icon = new pix_icon('i/customfield', $strgoreport, 'moodle', array('class'=>'icon', 'title'=>$strgoreport));
                    $url = new moodle_url('/mod/quiz/report.php', array('q' => $attempt->quizid, 'mode' => 'makeexam'));
                    $actions[] = $this->action_icon($url, $icon);
                }
                if(($quiz->id == $attempt->quizid) || $canmanage) {
                    $icon = new pix_icon('i/reload', $strgoattempt, 'moodle', array('class'=>'icon', 'title'=>$strgoattempt));
                    $url = new moodle_url($reporturl, array('setquestions'=>$attempt->id, 'confirm'=>1, 'sesskey'=>sesskey()));
                    $actions[] = $this->action_icon($url, $icon);
                }



                $cellaction = implode('&nbsp;  ', $actions);

                $row = new html_table_row(array($celln, $cellgeneratedby, $cellquestions, $cellstatus, $cellattempt, $cellstatereview, $cellaction));
                $table->data[] = $row;
            }
            echo html_writer::table($table);
        }
    }
}
