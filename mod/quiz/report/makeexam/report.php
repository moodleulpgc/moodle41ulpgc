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
 * Quiz makeexam report class.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_question\local\bank\question_version_status;
use qbank_editquestion\external\update_question_version_status;
use mod_quiz\question\bank\qbank_helper;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/makeexam/makeexam_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/makeexam/makeexam_table.php');
require_once($CFG->dirroot . '/mod/quiz/report/makeexam/lib.php');
require_once($CFG->dirroot . '/mod/examregistrar/lib.php');

/**
 * The quiz makeexam report provides summary information about each question in
 * a quiz, compared to the whole quiz. It also provides a drill-down to more
 * detailed information about each question.
 *
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_makeexam_report extends quiz_default_report {

    /** @var string the mode this report is. */
    protected $mode;

    /** @var object the quiz context. */
    protected $context;

    /** @var object the course object. */
    protected $course;

    /** @var object the course module object. */
    protected $cm;

/*
    /** @var quiz_makeexam_table instance of table class used for main questions stats table.
    protected $table;
*/
    /** @var array of examregistrar_exams table records. These are the exams managed by this report in this course  */
    protected $exams;

    /** @var int ID of current attempt, if any, its questions are set as quiz questions */
    protected $currentattempt;


    /**
     *  Initialise various aspects of this report.
     *
     * @param string $mode
     * @param object $quiz
     * @param object $cm
     * @param object $course
     */
    public function init($mode, $quiz, $cm, $course) {
        $this->mode = $mode;

        $this->context = context_module::instance($cm->id);

        $this->course = $course;

        $this->cm = $cm;

        $reporturl = $this->get_base_url();

        require_capability('quiz/makeexam:view', $this->context);

        //$includecheckboxes = false;
        //$this->table = new quiz_makeexam_table($quiz, $this->context, $reporturl, $includecheckboxes);

        return;
    }

    /**
     * Get the base URL for this report.
     * @return moodle_url the URL.
     */
    protected function get_base_url() {
        return new moodle_url('/mod/quiz/report.php',
                array('id' => $this->context->instanceid, 'mode' => $this->mode));
    }


    /**
     * Initialise some parts of $PAGE and start output.
     *
     * @param object $cm the course_module information.
     * @param object $coures the course settings.
     * @param object $quiz the quiz settings.
     * @param string $reportmode the report name.
     */
    public function print_header_and_tabs($cm, $course, $quiz, $reportmode = 'makeexam') {
        global $PAGE, $OUTPUT;

        // Print the page header.
        $PAGE->set_title($quiz->name);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        $context = context_module::instance($cm->id);
        //echo $OUTPUT->heading(format_string($quiz->name, true, array('context' => $context)));
        echo $OUTPUT->heading(get_string('createexams', 'quiz_makeexam'));
    }

    /**
     * Display the report.
     */
    public function display($quiz, $cm, $course) {
        global $CFG, $DB, $OUTPUT, $PAGE, $USER;

        $this->init('makeexam', $quiz, $cm, $course);

        $reporturl = $this->get_base_url();

        // Find out current groups mode.
        $currentgroup = $this->get_current_group($cm, $course, $this->context);

        // load data
        $this->load_exams($cm, $course);

        //$quizobj = quiz::create($quiz->id, $USER->id);
        //$hasquestions = $quizobj->has_questions();
        unset($quizobj);
        $hasquestions = quiz_has_questions($quiz->id);

        $mform = new quiz_makeexam_settings_form($reporturl, array('exams'=>$this->exams,
                                                                   'quiz'=>$quiz,
                                                                   'questions'=>$hasquestions,
                                                                   'current'=>$this->get_current_attempt($quiz->id)));

        $this->install_official_tags(); // just in case not installed yet

        // Process any submitted actions in the report.
        //Any optional params & action goes inside
        $this->process_actions($quiz, $cm, $course, $currentgroup, $mform);

        $output = $PAGE->get_renderer('quiz_makeexam');

        $PAGE->set_pagelayout('incourse');

        // Now starts output
        $this->print_header_and_tabs($cm, $course, $quiz, 'makeexam');

        if(!$this->exams) {
            echo $output->heading(get_string('nothingtodisplay'));
            return true;
        }

        if (groups_get_activity_groupmode($cm)) {
            groups_print_activity_menu($cm, $reporturl->out());
        }

        // On-screen display of report.
        $mform->display();

        // new quiz reset button
        if(has_capability('quiz/makeexam:submit', $this->context)) {
            $disabled = (!$hasquestions && !$this->get_current_attempt($quiz->id)) ? true : false;
            echo $output->print_clearquiz_button($disabled);
        }

        echo $output->container('', 'clearfix');

        /// TODO here can be placed options
        /// TODO options = period select form

        $examregistrar = $this->get_examregistrar_instance($cm, $this->course);
        foreach($this->exams as $exam) {
            echo $output->container_start(' examcoursereview'  );
                echo $output->print_exam_header($exam);

                //load check_attempt_questions data into exam for displaying
                foreach($exam->attempts as $key => $attempt) {
                     $attempt->check_questions_results = $this->check_attempt_questions($quiz, $attempt);
                     $exam->attempts[$key] = $attempt;
                }

                echo $output->print_exam_attempts($quiz, $exam, $examregistrar);

            echo $output->container_end();
        }

        return true;
    }

//// LOAD PART /////////////////////////////////////////////////////////////


    /**
     * Get the examregistrar instance to use for accessing examregistrar tables
     *
     * @param object $cm the course module object of this quiz
     * @param object $course the course settings object.
     * @return object examregistrar instance with cmid.
     */
    protected function get_examregistrar_instance($cm, $course) {
        global $DB;

        $exregcm = '';
        $examregistrar = '';
        $moduleid = $DB->get_field('modules', 'id', array('name'=>'examregistrar'), MUST_EXIST);

        $params = array('course'=>$course->id, 'module'=>$moduleid);
        //first check an instance in the same section, then any on course, if not, default
        if($cms = $DB->get_records('course_modules', $params + array('section'=>$cm->section))) {
            $exregcm = reset($cms);
        } elseif($cms = $DB->get_records('course_modules', $params)) {
            $exregcm = reset($cms);
        }

        if($exregcm) {
            $examregistrar = $DB->get_record('examregistrar', array('id'=>$exregcm->instance), '*', MUST_EXIST);
            $examregistrar->cmid = $exregcm->id;
        }else {
            $idnumber = get_config('quiz_makeexam', 'examregistrar');
            $examregistrar = $DB->get_record('examregistrar', array('primaryidnumber'=>$idnumber), '*', MUST_EXIST);
        }

        return $examregistrar;
    }

    /**
     * Get an array of exams from Examregistrar correponding to this course's exams
     * Reads examregistrar_exams to know about needed exams.
     *
     * @param object $cm the course module object of this quiz
     * @param object $course the course settings object.
     * @param int $period optional, get exams from only this period
     * @return array of attempts, including empty ones.
     */
    protected function load_exams($cm, $course, $period = 0) {
        global $CFG, $DB;

        $this->exams = array();

        $examregistrar = $this->get_examregistrar_instance($cm, $course);

        $exregid = examregistrar_get_primaryid($examregistrar);

        $annuality = examregistrar_get_annuality($examregistrar);

        $params = array('examregid'=>$exregid, 'courseid'=>$course->id);
        if($annuality) {
            $params['annuality'] = $annuality;
        }
        if($period) {
            $params['period'] = $period;
        }

        $exams = array();
        if($exams = $DB->get_records('examregistrar_exams', $params)) {
            $this->exams = $exams;
            $this->reload_attempts();
        }

        return $this->exams;
    }


    /**
     * Given an array of exams from Examregistrar correponding to this course's exams
     * Reads examregistrar examfiles and makeexam_attempts to update stored info
     *
     * @param object $cm the course module object of this quiz
     * @param object $course the course settings object.
     * @param int $period optional, get exams from only this period
     * @return array of attempts, including empty ones.
     */
    protected function reload_attempts() {
        global $CFG, $DB;

        $update = array();
        foreach($this->exams as $eid => $exam) {
            $examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$eid));
            $exam->examfiles = $examfiles;

            $attempts = $DB->get_records('quiz_makeexam_attempts', array('course'=>$this->course->id, 'examid'=>$eid));

            // check integrity of attempt examfile (may have been deleted)
            foreach($attempts as $aid => $attempt) {
                if($attempt->examfileid && !isset($exam->examfiles[$attempt->examfileid])) {
                    //this id has been deleted deleted, update
                    $update[] = $aid;
                    $attempt->examfileid = 0;
                    $attempt->status = 0;
                    $attempts[$aid] = $attempt;
                }
            }
            $exam->attempts = $attempts;
            $this->exams[$eid] = $exam;
        }

        if($update) {
            list($insql, $params) = $DB->get_in_or_equal($update);
            $DB->set_field_select('quiz_makeexam_attempts', 'examfileid', 0, " id $insql ", $params);
            $DB->set_field_select('quiz_makeexam_attempts', 'status', 0, " id $insql ", $params);
        }

        return $this->exams;
    }



//// LOAD PART END /////////////////////////////////////////////////////////////

    protected function install_official_tags() {
        global $CFG;

        // install official tags
        require_once($CFG->dirroot . '/tag/lib.php');
        $tags[] = get_string('tagvalidated', 'quiz_makeexam');
        $tags[] = get_string('tagrejected', 'quiz_makeexam');
        $tags[] = get_string('tagunvalidated', 'quiz_makeexam');

        $tags = core_tag_tag::create_if_missing(1, $tags, true);
    }

//// ACTIONS PART  /////////////////////////////////////////////////////////////


    protected function data_submitted() {

        $data = false;
        if (!empty($_POST)) {
            $data = $_POST;
        } elseif(!empty($_GET)) {
            $data = $_GET;
        }

        if($data) {
            $return = false;
            foreach($data as $key => $value) {
                if(($key != 'mode') && ($key != 'id') && ($key != 'sesskey')) {
                    $return = true;
                    break;
                }
            }
            if($return) {
                return (object)fix_utf8($data);
            }
        }

        return false;
    }

    /**
     * Pretty prints a message informing of an error and button to continue.
     *
     * @param string $pagename the quiz name, usually.
     * @param string $coursename 
     * @param moodle_url $reporturl url to go to after this page.
     * @param string $error a notification to show to user
     * @param string $message a confirmation message to show to user      
     * @param array $confirmparams for confirmation action, if any
     * @return void;
     */
    protected function print_error_continue($pagename, $coursename, $reporturl, $error, 
                                                $message = false, $confirmparams = false) {
        global $PAGE;
    
        $PAGE->set_title($pagename);
        $PAGE->set_heading($coursename);
        //$PAGE->navbar->add(get_string('makeexam', 'quiz_makeexam'));
        $output = $PAGE->get_renderer('mod_quiz');
        echo $output->header();
        
        if(!empty($confirmparams)) {
            $confirmurl = new moodle_url($reporturl, $confirmparams + array('confirm' => 1));
            echo $output->confirm($message, $confirmurl, $reporturl);
        } else {
            if($message) {
                echo $output->notification($message, 'notifysuccess');
            }
            echo $output->notification($error);
            echo $output->continue_button($reporturl);
        }
        
        echo $output->footer();
        die;
    }

    /**
     * Process any submitted actions.
     * @param object $quiz the quiz settings.
     * @param object $cm the cm object for the quiz.
     * @param int $currentgroup the currently selected group.
     * @param object $mform the settings form
     */
    protected function process_actions($quiz, $cm, $course, $currentgroup, $mform) {
        global $CFG, $DB, $PAGE, $USER, $OUTPUT;

        $now = time();
        $reporturl = $this->get_base_url();
        $reviewurl = new moodle_url('/mod/quiz/review.php',  array('id' => $this->context->instanceid, 'mode' => $this->mode));

        if (empty($currentgroup)) {
            if (optional_param('delete', 0, PARAM_BOOL) && confirm_sesskey()) {
                if ($attemptids = optional_param_array('attemptid', array(), PARAM_INT)) {
                    require_capability('mod/quiz:deleteattempts', $this->context);

                    //print_object("aqui");
                    //die;
                    $this->delete_selected_attempts($quiz, $cm, $attemptids, $allowed);
                    redirect($reporturl);
                }
            }
        }

        /// We have a form, a new preview/review has been requested
        if(($fromform = $mform->get_data()) && (isset($fromform->action) && ($fromform->action == 'newattempt'))) {
            if($fromform->attemptn) {
                if(!$examattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'examid'=>$fromform->examid, 'attempt'=>$fromform->attemptn))) {
                    // does not exist this attempt for this exam
                    $this->print_error_continue($quiz->name, $course->fullname, $reporturl, 
                                                get_string('errornoattempt', 'quiz_makeexam', $fromform->attemptn));
                }
                $this->restore_quiz_from_attempt($quiz, $examattempt);
            }
            $warnings = 0;
            if(!has_capability('quiz/makeexam:anyquestions', $this->context)) {
                $slots = qbank_helper::get_question_structure($quiz->id, $this->context);
                $warnings = $this->check_attempt_valid_questions($slots);
            }
            if($warnings) {
                $editurl = new moodle_url('/mod/quiz/edit.php', array('cmid'=>$cm->id));
                $this->print_error_continue($quiz->name, $course->fullname, $editurl, 
                                            get_string('generate_errors', 'quiz_makeexam').'<br />'.
                                            get_string('errorinvalidquestions', 'quiz_makeexam', $warnings));
            
            } else {
                $newattemptid = $this->start_new_preview_attempt($quiz);
                $url = new moodle_url($reviewurl,  array('action'=>'newattempt', 'examid'=>$fromform->examid,
                                                            'name'=>$fromform->name, 'attempt'=>$newattemptid));
                if($fromform->currentattempt) {
                      $url->param('action', 'continueattempt');
                      $url->param('currentattempt', $fromform->currentattempt);
                }
                redirect($url);
            }

        } elseif(($fromform = $this->data_submitted())) {
            $info = new stdClass;
            if(isset($fromform->examid) && $fromform->examid) {
                $exam = $this->exams[$fromform->examid];
                $items = array();
                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
                $items[] = $idnumber;

                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
                $items[] = $idnumber;

                $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
                $items[] = ' ('.$idnumber.')';

                $info->exam = implode(', ', $items);
            }

            if(isset($fromform->delete) && $fromform->delete) {
                $attempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$fromform->delete), '*', MUST_EXIST);
                $info->name = $attempt->name;
                $info->num = $attempt->attempt;
                $message = get_string('delete_confirm', 'quiz_makeexam', $info);
                $strnav = get_string('deleteattempt', 'quiz_makeexam');
            }

            if(isset($fromform->submit) && $fromform->submit) {
                $attempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$fromform->submit), '*', MUST_EXIST);
                $info->name = $attempt->name;
                $info->num = $attempt->attempt;
                $message = get_string('submit_confirm', 'quiz_makeexam', $info);
                $strnav = get_string('submitattempt', 'quiz_makeexam');
            }

            if(isset($fromform->unsend) && $fromform->unsend) {
                $attempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$fromform->unsend), '*', MUST_EXIST);
                $info->name = $attempt->name;
                $info->num = $attempt->attempt;
                $message = get_string('unsend_confirm', 'quiz_makeexam', $info);
                $strnav = get_string('unsendattempt', 'quiz_makeexam');
            }

            if(isset($fromform->newattempt) && $fromform->newattempt &&
               (isset($fromform->action) && ($fromform->action == 'continueattempt')) &&
               (isset($fromform->currentattempt) && $fromform->currentattempt)) {
                $fromform->confirm = 1;
            }

            $error = false;
            if(isset($fromform->newattempt) && $fromform->newattempt &&
               ((isset($fromform->action) && ($fromform->action == 'newattempt')) || $fromform->attemptn)) {
                $message = get_string('generate_confirm', 'quiz_makeexam', $info);
                $strnav = get_string('generateexam', 'quiz_makeexam');
                //$error = $this->check_attempt_questions($quiz);
            }

            // review
            if(isset($fromform->review) &&  $fromform->review &&
                isset($fromform->confirm) && $fromform->confirm) {
                // review, start a new quiz attempt from stored one
                $this->exam_version_preview($quiz, $fromform->review);
                
            } elseif(!isset($fromform->confirm) || !$fromform->confirm) {
                $this->print_error_continue($quiz->name, $course->fullname, $reporturl, 
                                            $error, $message, get_object_vars($fromform));

            } elseif(confirm_sesskey()){
                // confirmed, perform real actions

                $message = '';
                // delete
                if(isset($fromform->delete) &&  $fromform->delete) {
                    $this->delete_attempt($quiz, $fromform->delete);
                    redirect($reporturl);
                }
/*
                // review
                if(isset($fromform->review) &&  $fromform->review) {
                    // review, start a new quiz attempt from stored one
                    $this->exam_version_preview($quiz, $fromform->review);
                }
*/
                // prepare eventdata
                $eventdata = array();
                $eventdata['context'] = $this->context;
                $eventdata['userid'] = $USER->id;
                $eventdata['other'] = array();
                $eventdata['other']['quizid'] = $quiz->id;
                
                // setquestions
                if(isset($fromform->setquestions) &&  $fromform->setquestions) {
                    //  restore quiz questions from stored ones
                    $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$fromform->setquestions), '*', MUST_EXIST);

                    // change quiz state, questions, from stored makeexam; this clears previous questions, if existing
                    $this->restore_saved_attempt($quiz, $examattempt->id);
                    $this->set_current_attempt($quiz->id, $examattempt->id);
                    $eventdata['objectid'] = $examattempt->id;
                    $eventdata['other']['examid'] = $examattempt->examid;
                    $event = \quiz_makeexam\event\exam_recalled::create($eventdata);
                    $event->trigger();
                    // Redirect to the edit page.
                    $url = new moodle_url('/mod/quiz/edit.php', array('cmid'=>$cm->id, 'mode'=>$this->mode,
                                                                        'qbanktool' => 1));
                    redirect($url);
                }

                // pdfpreview
                if(isset($fromform->pdf) &&  $fromform->pdf) {
                    // Generate PDF and present in browser / download
                    //First load questions into quiz from pdf attempt
                    $this->restore_saved_attempt($quiz, (int)$fromform->pdf);

                    // now we have questions, we can create a quiz attempt
                    $quizattemptid = $this->start_new_preview_attempt($quiz);

                    // and generate PDF
                    $message = $this->generate_pdf($quiz, $quizattemptid,  $fromform->examid, $fromform->pdf, 'exam', false);
                }

                // submit
                if(isset($fromform->submit) &&  $fromform->submit) {
                    // Submits exam verson to examregistrar
                    require_capability('quiz/makeexam:submit', context_module::instance($cm->id));

                    //First load questions into quiz from pdf attempt
                    $this->restore_saved_attempt($quiz, (int)$fromform->submit);

                    // now we have questions, we can create a quiz attempt
                    $quizattemptid = $this->start_new_preview_attempt($quiz);

                    $message = $this->submit_attempt($cm, $quiz, $course, $quizattemptid, $fromform->examid, $fromform->submit);

                    $eventdata['objectid'] = $fromform->submit;
                    $eventdata['other']['examid'] = $fromform->examid;
                    $eventdata['other']['message'] = $message;
                    $event = \quiz_makeexam\event\exam_recalled::create($eventdata);
                    $event->trigger();
                }

                if(isset($fromform->unsend) && $fromform->unsend) {
                    // reset status to unsend
                    require_capability('mod/examregistrar:manageexams', context_course::instance($this->course->id));
                    $DB->set_field('quiz_makeexam_attempts', 'status', 0, array('id'=>$fromform->unsend));
                    $DB->set_field('quiz_makeexam_attempts', 'examfileid', 0, array('id'=>$fromform->unsend));
                }

                // generate/continue new exam attempt
                if(isset($fromform->newattempt) && $fromform->newattempt && $fromform->examid) {
                    if(!isset($fromform->currentattempt)) {
                        $fromform->currentattempt = 0;
                    }
                    $continueattempt = ($fromform->action == 'continueattempt') ? true : false;
                    $attemptid = $this->make_new_attempt($quiz, $fromform->examid, $fromform->name, $fromform->newattempt, $fromform->currentattempt, $continueattempt);

                    $eventdata['objectid'] = $attemptid;
                    $eventdata['other']['examid'] = $fromform->examid;
                    $eventdata['other']['continue'] = $continueattempt;
                    $event = \quiz_makeexam\event\exam_created::create($eventdata);
                    $event->trigger();
                }

                if(isset($fromform->clearquiz) && $fromform->clearquiz == 1 ) {
                    if($this->clear_quiz($quiz)) {
                        $eventdata['objectid'] = $quiz->id;
                        $event = \quiz_makeexam\event\exam_cleared::create($eventdata);
                        $event->trigger();
                        $editurl = new moodle_url('/mod/quiz/edit.php', array('cmid'=>$cm->id));
                        redirect($editurl, get_string('cleared', 'quiz_makeexam'), 5);
                    } else {
                        \core\notification::add(get_string('delexistingattempts', 'quiz_makeexam'),
                                                    \core\output\notification::NOTIFY_ERROR);
                    }
                }

                // other actions
                if($message) {
                    redirect($reporturl, $message, 5);
                }
                $this->reload_attempts();
            }
        }
    }

//// ACTIONS PART END /////////////////////////////////////////////////////////////

//// Makeexam proper report PART //////////////////////////////////////////////////

    /**
     * Sets the makeexam_attemp that is currently in use, and return it
     *
     * @param int $quizid the ID for a quiz instance
     * @param int $attemptid the ID for a makeexam_attempt
     * @return int currentattempt
     */
    protected function set_current_attempt(int $quizid, int $attemptid): int {
        global $DB;

        // ensure there is only one record with currentattempt set
        $DB->set_field('quiz_makeexam_attempts', 'currentattempt', 0, ['quizid'=>$quizid]);
        // if $attemptid = 0 or not in table, not set field = no current attempt
        $DB->set_field('quiz_makeexam_attempts', 'currentattempt', 1, ['quizid'=>$quizid, 'id'=>$attemptid]);

        return $this->get_current_attempt($quizid);
    }

    /**
     * Returns the makeexam_attemp that is currently in use
     *
     * @param int $quizid the ID for a quiz instance
     * @return int currentattempt
     */
    protected function get_current_attempt(int $quizid): int {
        global $DB;

        $id = $DB->get_field('quiz_makeexam_attempts', 'id', ['quizid'=>$quizid, 'currentattempt'=> 1]);
        $this->currentattempt = (int)$id;
        return $this->currentattempt;
    }

    /**
     * Checks if questions belong to configured valid types
     *
     * @param array $questions array of slot => question data. Required type is question.qtype
     * @return int number of invalid questions (not in valid types)
     */
    protected function check_attempt_valid_questions(array $questions): int {
        $config = get_config('quiz_makeexam');

        $validquestions = $config->validquestions;
        if($validquestions) {
            $validquestions = explode(',', $validquestions);
        } else {
            $validquestions = array();
        }

        $warnings = 0;
        foreach($questions as $slot => $question) {
            if(!in_array($question->qtype, $validquestions)) {
                $warnings +=1;
            }
        }

        return $warnings;
    }

    /**
     * Checks if current questions ar compliant with configured requirements
     *
     * @param object $quiz a record from quiz table
     * @param object $mkattempt the makeexam_attempt with ID for a makeexam_slots attempt
     * @return array array($numquestions, $invalid, $success)
     */
    protected function check_attempt_questions($quiz, $mkattempt): array {
        global $DB, $USER;

        $config = get_config('quiz_makeexam');

        $success = false;
        $info = new stdClass;
        $errors = array();
        $invalid = false;

        $fields = 'qms.slot, qv.questionid, q.qtype, q.defaultmark, qms.questionbankentryid, qbe.questioncategoryid AS category';
        $extrajoins = "JOIN {question} q ON q.id = qv.questionid
                       JOIN {question_bank_entries} qbe ON qbe.id = qms.questionbankentryid";
        $questions = $this->attempt_real_questions($quiz->id, $mkattempt->id, $fields, $extrajoins);

        $warning = $this->check_attempt_valid_questions($questions);

        // Only consider true questions, not descriptions or other non graded
        foreach($questions as $qid => $question) {
            if($question->defaultmark == 0) {
                unset($questions[$qid]);
            }
        }

        if($warning) {
            $invalid = get_string('error_invalidquestions', 'quiz_makeexam', $warning);
        }

        $numquestions = count($questions);
        if($config->numquestions && ($config->numquestions != $numquestions)) {
            $info->confignum = $config->numquestions;
            $info->num = $numquestions;
            $errors[] = get_string('error_numquestions', 'quiz_makeexam', $info);
        }

        if($config->categorysearch) {
            switch($config->contextlevel) {
                case CONTEXT_SYSTEM     : $context = context_system::instance();
                                            break;
                case CONTEXT_COURSECAT  : $context = context_coursecat::instance($this->course->category);
                                            break;
                case CONTEXT_COURSE     : $context = context_course::instance($quiz->course);
                                            break;
                case CONTEXT_MODULE     : $context = $this->context;
            }
            $select = ' contextid = :contextid AND '.$DB->sql_like('name', ':pattern');
            if($config->excludesubcats) {
                $select .= ' AND parent = 0 ';
            }
            if($categories = $DB->get_records_select('question_categories', $select, array('contextid'=>$context->id, 'pattern'=>$config->categorysearch))) {
                $categorysums = array_combine(array_keys($categories), array_fill(0, count($categories), 0));
                $othercategories = array();
                foreach($questions as $question) {
                    if(array_key_exists($question->category, $categorysums)) {
                        $categorysums[$question->category] += 1;
                    } else {
                        if(!isset($othercategories[$question->category])) {
                            $othercategories[$question->category] = 0;
                        }
                        $othercategories[$question->category] += 1;
                    }
                }
                foreach($categorysums as $catid => $num) {
                    $used = true;
                    if($config->excludeunused) {
                        $used = $DB->record_exists_select('question', ' category = ? AND (length > 0) ', array($catid));
                    }
                    if($used && $config->questionspercategory && ($num < $config->questionspercategory)) {
                        $info->confignum = $config->questionspercategory;
                        $info->num = $num;
                        $info->name = $categories[$catid]->name;
                        $errors[] = get_string('error_percategory', 'quiz_makeexam', $info);
                    }
                }
                if($othercategories) {
                    $num = array_sum($othercategories);
                    $errors[] = get_string('error_othercategories', 'quiz_makeexam', $num);
                }
            }
        }

        if($errors) {
            $success = get_string('generate_errors','quiz_makeexam').'<br />'.implode('<br />', $errors);
        }

        return array($numquestions, $invalid, $success);
    }

    /**
     * Gets the module instance empty and ready for new uses
     * Removes all attempts, questions and sections from a quiz instance
     *
     * @param object $quiz a record from quiz table
     * @return void
     */
    public function clear_quiz($quiz, bool $allusers = false): bool {
        global $DB, $USER;

        $user = $USER;
        if($allusers == true) {
            $user = null;
        }

        // delete all previous attempts & previews
        if(!$this->deleted_existing_attempts($quiz, $user)) {
            // if keeping attempts, cannot delete questions & usages
            return false;
        }

        $this->set_current_attempt($quiz->id, 0);
        // now we can proceed with questions & slots removal
        $this->delete_quiz_slots_sections($quiz->id);
        $this->add_first_section($quiz->id);
        quiz_update_sumgrades($quiz);
        return true;
    }

    /**
     * Deletes any previous quiz_attempts
     *
     * @param object $quiz a quiz instance
     * @param objet $user the  user the attempts belong to, or any
     * @return bool if completely deleted (true) or has any remaining attempts (false)
     */
    protected function deleted_existing_attempts($quiz, $user = null): bool {
        global $DB;

        // To force the creation of a new preview, we mark the current attempt (if any)
        // as finished. It will then automatically be deleted below.
        $params = ['quiz' => $quiz->id];
        if(isset($user)) {
            $params['userid'] = $user->id;

        }
        $DB->set_field('quiz_attempts', 'state', quiz_attempt::FINISHED, $params);

        if(isset($user)) {
            // Delete any previous preview attempts belonging to this user.
            // This deletes question_usages for user
            $quizobj = quiz::create($quiz->id);
            quiz_delete_user_attempts($quizobj, $user);
        } else {
            // This deletes any question_usages for all users
            quiz_delete_all_attempts($quiz);
        }

        // This deletes question_usages for any preview user, all users
        quiz_delete_previews($quiz);

        return !quiz_has_attempts($quiz->id);
    }

    /**
     * Removes all question slots & secctions from current quiz
     *
     * @param int $quizid the ID for a quiz instance
     * @return void
     */
    public function delete_quiz_slots_sections(int $quizid) {
        global $DB;

        $trans = $DB->start_delegated_transaction();
        $deletesql = "SELECT qr.id AS qrid
                        FROM {question_references} qr
                        JOIN {quiz_slots} qs ON qs.id = qr.itemid AND qr.component = 'mod_quiz' AND qr.questionarea = 'slot'
                    WHERE qr.usingcontextid = :quizcontextid AND qs.quizid = :quizid";
        $DB->delete_records_subquery('question_references', 'id', 'qrid', $deletesql,
                                        ['quizcontextid' => $this->context->id, 'quizid' => $quizid]);

        $deletesql = str_replace('question_references', 'question_set_references', $deletesql);
        $DB->delete_records_subquery('question_set_references', 'id', 'qrid', $deletesql,
                                        ['quizcontextid' => $this->context->id, 'quizid' => $quizid]);

        $DB->delete_records('quiz_slots', array('quizid'=>$quizid));
        $DB->delete_records('quiz_sections', array('quizid'=>$quizid));
        $this->add_first_section($quizid);

        $trans->allow_commit();
    }

    /**
     * Makes sure there is a section 1 in the quiz
     *
     * @param int $quizid the ID for a quiz instance
     * @return void
     */
    public function add_first_section(int $quizid) {
        global $DB;
        if(!$DB->record_exists('quiz_sections', ['quizid' => $quizid, 'firstslot' => 1])) {
            $firstsection = new stdClass();
            $firstsection->quizid = $quizid;
            $firstsection->firstslot = 1;
            $firstsection->shufflequestions = 0;
            $DB->insert_record('quiz_sections', $firstsection);
        }
    }

    /**
     * Construct array or slot questions from makeexam_slots questionbankentryids
     *
     * @param int $quizid ID of a record from quiz table
     * @param int $attemptid the ID of a makeexam_attempt entry
     * @param strig $fields a list of qualified field names to use in a SQL SELECT
     * @param strig $extrajoins an SQL join for another tabl eto get data from
     * @return array with question objects,
     */
    protected function attempt_real_questions($quizid, int $attemptid,
                                              $fields = '',
                                              $extrajoins = ''): array {
        global $DB;

        if(empty(trim($fields))) {
            $fields = 'qms.id, qv.questionid, qms.slot, qms.page, qms.maxmark, qms.questionbankentryid';
        }

        $versionjoin = quiz_makeexam_question_version_sqljoin('quiz_makeexam_slots', 'qms.questionbankentryid');
        $sql = "SELECT $fields
                FROM {quiz_makeexam_slots} qms
                    $versionjoin
                    $extrajoins
                WHERE qms.quizid = :quizid1 AND qms.mkattempt = :attempt
                ORDER BY qms.slot ";
        $params = ['draft' => question_version_status::QUESTION_STATUS_DRAFT,
                    'quizid1' => $quizid,
                    'quizid2' => $quizid,
                    'attempt' => $attemptid,
                    ];
        return $DB->get_records_sql($sql, $params);
    }


    /**
     * Checks if current questions ar compliant with configured requirements
     *
     * @param object $quiz a record from quiz table
     * @param int $attemptid the ID of a makeexam_attempt entry
     * @return bool
     */
    protected function delete_attempt($quiz, int $attemptid): bool {
        global $DB, $USER;

        $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$attemptid), '*', MUST_EXIST);

        if($quizattempt = $DB->get_record('quiz_attempts', array('id'=>$examattempt->attemptid))) {
            quiz_delete_attempt($quizattempt, $quiz);
        }

        // slos data must be recovered before deletion
        $fields = 'qms.id, qv.questionid, qms.slot, qms.questionbankentryid';
        $slots = $this->attempt_real_questions($quiz->id, $attemptid, $fields);

        $DB->delete_records('quiz_makeexam_slots', array('mkattempt'=>$attemptid));
        $DB->delete_records('quiz_makeexam_sections', array('mkattempt'=>$attemptid));

        // unhide used questions. Check first if used in other attempts
        foreach($slots as $slot) {
            if(!$DB->record_exists('quiz_makeexam_slots',
                                    ['questionbankentryid'=>$slot->questionbankentryid, 'inuse'=>1])) {
                update_question_version_status::execute($slot->questionid,
                                                        question_version_status::QUESTION_STATUS_READY);
            }
        }

        $success = true;
        if($success = $DB->delete_records('quiz_makeexam_attempts', array('id'=>$attemptid))) {
            // if this is current attempt, unset as current and clear quiz
            if($attemptid == $this->get_current_attempt($quiz->id)) {
                $this->clear_quiz($quiz);
                $this->set_current_attempt($quiz->id, 0);
            }

            $eventdata = array();
            $eventdata['objectid'] = $attemptid;
            $eventdata['context'] = $this->context;
            $eventdata['other'] = array();
            $eventdata['other']['quizid'] = $quiz->id;
            $eventdata['other']['examid'] = $examattempt->examid;
            $event = \quiz_makeexam\event\exam_deleted::create($eventdata);
            $event->trigger();
        }

        return $success;
    }

    /**
     * Delete the quiz attempts
     * @param object $quiz the quiz settings. Attempts that don't belong to
     * this quiz are not deleted.
     * @param object $cm the course_module object.
     * @param array $attemptids the list of attempt ids to delete.
     * @param array $allowed This list of userids that are visible in the report.
     *      Users can only delete attempts that they are allowed to see in the report.
     *      Empty means all users.
     */
    protected function delete_selected_attempts($quiz, $cm, $attemptids, $allowed) {
        global $DB;

        foreach ($attemptids as $attemptid) {
            $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
            if (!$attempt || $attempt->quiz != $quiz->id || $attempt->preview != 0) {
                // Ensure the attempt exists, and belongs to this quiz. If not skip.
                continue;
            }
            if ($allowed && !in_array($attempt->userid, $allowed)) {
                // Ensure the attempt belongs to a student included in the report. If not skip.
                continue;
            }

            $this->delete_attempt($quiz, $attemptid);
        }
    }

    /**
     * Checks if current questions ar compliant with configured requirements
     *
     * @param object $quiz a record from quiz table
     * @param int $userid the ID of a user, 0 defaults to current user
     * @return int quiz attempt id
     */
    protected function start_new_preview_attempt($quiz, $userid = 0): int {
        global $CFG, $DB, $PAGE, $USER;

        if(!$userid) {
            $userid = $USER->id;
        }
        $quizobj = quiz::create($quiz->id, $userid);
        // This script should only ever be posted to, so set page URL to the view page.
        $PAGE->set_url($quizobj->view_url());

        // Check login and sesskey.
        require_login($this->course, false, $quizobj->get_cm());
        $PAGE->set_heading($this->course->fullname);

        // If no questions have been set up yet redirect to edit.php or display an error.
        if (!$quizobj->has_questions()) {
            if ($quizobj->has_capability('mod/quiz:manage')) {
                redirect($quizobj->edit_url());
            } else {
                print_error('cannotstartnoquestions', 'quiz', $quizobj->view_url());
            }
        }

        // Check capabilities.
        if (!$quizobj->is_preview_user()) {
            $quizobj->require_capability('mod/quiz:manage');
        } else {
            $quizobj->require_capability('quiz/makeexam:submit');
        }

        quiz_delete_previews($quiz, $userid);

        $attemptnumber = 1;
        $attempts = quiz_get_user_attempts($quiz->id, $userid, 'all', false);
        $lastattempt = end($attempts);
        if($lastattempt) {
            $attemptnumber = $lastattempt->attempt + 1;
        }
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $timenow = time(); // Update time now, in case the server is running really slowly.
        $attempt = quiz_create_attempt($quizobj, $attemptnumber, $lastattempt, $timenow, $quizobj->is_preview_user());
        $attempt = quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $timenow);
        $attempt->preview = 1;
        $attempt = quiz_attempt_save_started($quizobj, $quba, $attempt);
        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_finish($timenow+1, true);
        return $attempt->id;
    }

    protected function make_new_attempt($quiz, $examid, $name, $newattemptid, $currentattempt = 0, $continueattempt = false) {
        global $DB, $USER;

        $now = time();
        $maxattempt = 0;
        $newid = 0;
        $oldattempt = 0;
        if($attempts = $DB->get_records_menu('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'examid'=>$examid), ' attempt DESC', 'id, attempt', 0, 1)) {
            $maxattempt= reset($attempts);
        }
        $quizattempt = $DB->get_record('quiz_attempts', array('id'=>$newattemptid), '*', MUST_EXIST);

        // prevents bug by having no questions in quiz_attempt
        if(!$qbankentries = $this->get_quiz_questions_and_entries($quiz, $quizattempt)) {
            print_error('noquestionsinquiz', 'quiz', $this->get_base_url());
            return false;
        }

        if($currentattempt && $continueattempt) {
            $examattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'id'=>$currentattempt), '*', MUST_EXIST);
            $oldattempt = clone $examattempt;
            $examattempt->attemptid = $quizattempt->id;
            $examattempt->qbankentries  = implode(',', $qbankentries);
            $examattempt->timemodified = $now;
            $examattempt->userid = $USER->id;
            if($DB->update_record('quiz_makeexam_attempts', $examattempt)) {
                $newid = $examattempt->id;

            }
        } else {
            $examattempt = new stdClass;
            $examattempt->course = $quiz->course;
            $examattempt->quizid = $quiz->id;
            $examattempt->attemptid = $quizattempt->id;
            $examattempt->qbankentries  = implode(',', $qbankentries);
            $examattempt->attempt = $maxattempt + 1;
            if(!$name) {
                $name = get_string('attemptn', 'quiz_makeexam', $examattempt->attempt);
            }
            $examattempt->name = $name;
            $examattempt->userid = $USER->id;
            $examattempt->status = 0;
            $examattempt->examid = $examid;
            $examattempt->examfileid = 0;
            $examattempt->timecreated = $now;
            $examattempt->timesubmitted = 0;
            $examattempt->timemodified = $now;
            $newid = $DB->insert_record('quiz_makeexam_attempts', $examattempt);
        }

        // newid is a quiz_makeexam_attempts ID
        if($newid) {
            // save quiz question instances
            $this->save_quiz_sections_slots($quiz, $newid);

            // Now proceed to HIDE the used questions, cannot be reused
            $fields = 'qms.slot, qv.questionid, qms.questionbankentryid';
            $slots = $this->attempt_real_questions($quiz->id, $newid, $fields);
            foreach($slots as $slot) {
                //print_object("Hiding question with qid: {$slot->questionid}  qbeid: {$slot->questionbankentryid}");
                update_question_version_status::execute($slot->questionid,
                                                        question_version_status::QUESTION_STATUS_HIDDEN);
            }

            // now empty quiz questions to avoid reuse of questions
            $this->clear_quiz($quiz);
        }

        return $newid;
    }

    /**
     * Collects slots from current quiz questions and makes a map questionid - questionbankentryid.
     *
     * @param object $quiz the quiz intance record.
     * @param object $quizattempt a quiz_attempt, for question usage
     * @return array a map questionid - questionbankentryid
     */
    protected function get_quiz_questions_and_entries($quiz, $quizattempt): array {
        global $DB;

        $slots = qbank_helper::get_question_structure($quiz->id, $this->context);
        $questions = $DB->get_records_menu('question_attempts', array('questionusageid'=>abs($quizattempt->uniqueid)), 'slot ASC ', 'slot,questionid');

        $newquestions = [];
        $notfound = [];
        $slotnum = 0;
        foreach($slots as $question) {
            $slot = array_search($question->questionid, $questions);
            if($slot == $question->slot) {
                $newquestions[$question->questionid] = $question->questionbankentryid;
            } else {
                $notfound[] = $question->name;
            }
        }

        if(!empty($notfound)) {
            \core\notification::add(get_string('slotsnotusage', 'quiz_makeexam', implode(',<br />', $notfound)),
                                        \core\output\notification::NOTIFY_ERROR);
        }

        return $newquestions;
    }

    /**
     * Gets slots from current quiz questions and store into makeexam_slots & sections
     *
     * @param object $quiz the quiz intance record.
     * @param int $examattemptid ID of a quiz_makeexam_attempt, slots stored with mkattempt = $examattemptid.
     * @return void;
     */
    protected function save_quiz_sections_slots($quiz, int $examattemptid) {
        global $DB;

        if($slots = qbank_helper::get_question_structure($quiz->id, $this->context)) {
            $mkslots = $DB->get_records_menu('quiz_makeexam_slots', array('quizid'=>$quiz->id, 'mkattempt'=>$examattemptid), 'slot', 'id,slot');
            $slotfields = ['inuse', 'slot', 'page', 'requireprevious', 'maxmark', 'questionbankentryid', 'version'];
            foreach($slots as $slot) {
                $slot->inuse = 1;
                $slot->version = null;
                $sv = $DB->get_field('question_references', 'version', ['component' => 'mod_quiz',
                                                                        'questionarea' => 'slot',
                                                                        'itemid' => $slot->slotid,
                                                                        'questionbankentryid' => $slot->questionbankentryid]);
                if(!empty($sv)) {
                    $slot->version = $sv;
                }
                $slot->mkattempt = $examattemptid;
                $slot->quizid = $quiz->id;
                if(!$mkslot = $DB->get_record('quiz_makeexam_slots', array('quizid'=>$quiz->id, 'mkattempt'=>$examattemptid, 'slot'=>$slot->slot))) {
                    unset($slot->id);
                    $newid = $DB->insert_record('quiz_makeexam_slots', $slot);
                } else {
                    $update = false;
                    foreach($slotfields as $field) {
                        if($mkslot->{$field} != $slot->{$field}) {
                            $update = true;
                        }
                        $mkslot->{$field} = $slot->{$field};
                    }
                    if($update) {
                        $DB->update_record('quiz_makeexam_slots', $mkslot);
                    }
                    unset($mkslots[$mkslot->id]);
                }

            }
            // delete remaining, not used slots (provide support for updating);
            if($mkslots) {
                $DB->delete_records_list('quiz_makeexam_slots', 'id', array_keys($mkslots));
            }
        }
        if($sections = $DB->get_records('quiz_sections', array('quizid'=>$quiz->id))) {
            $mksections = $DB->get_records_menu('quiz_makeexam_sections', array('quizid'=>$quiz->id, 'mkattempt'=>$examattemptid), 'firstslot', 'id,firstslot');
            $mkfields = ['inuse', 'firstslot', 'heading', 'shufflequestions'];
            foreach($sections as $section) {
                $section->mkattempt = $examattemptid;
                $section->inuse = 1;
                if(!$mksection = $DB->get_record('quiz_makeexam_sections', array('quizid'=>$quiz->id, 'mkattempt'=>$examattemptid, 'firstslot'=>$section->firstslot))) {
                    unset($section->id);
                    $newid = $DB->insert_record('quiz_makeexam_sections', $section);
                } else {
                    $update = false;
                    foreach($mkfields as $field) {
                        if($mksection->{$field} != $section->{$field}) {
                            $update = true;
                        }
                        $mksection->{$field} = $section->{$field};
                    }
                    if($update) {
                        $DB->update_record('quiz_makeexam_sections', $mksection);
                    }
                    unset($mksections[$mksection->id]);
                }
            }
            if($mksections) {
                $DB->delete_records_list('quiz_makeexam_sections', 'id', array_keys($mksections));
            }
        }
    }

    /**
     * Restore questions in stored mkattemp and launches a quiz review attemp.
     *
     * @param object $quiz the quiz settings.
     * @param int $attemptid the makeexam  attempt to show.
     * @return void;
     */
    protected function exam_version_preview($quiz, int $attemptid) {
        require_capability('mod/quiz:preview', $this->context);
        // start a new quiz attempt from stored one

        //First load questions into quiz from
        $this->restore_saved_attempt($quiz, $attemptid);

        $this->set_current_attempt($quiz->id, $attemptid);

        // now we have questions, we can create a quiz attempt
        $quizattemptid = $this->start_new_preview_attempt($quiz);

        $url = new moodle_url('/mod/quiz/review.php', array('id'=>$this->context->instanceid, 'mode'=>$this->mode,
                                                            'attempt' => $quizattemptid, 'review'=>$attemptid, 'showall'=>1));
        redirect($url);
    }

    /**
     * Restore questions in stored mkattemp and .
     *
     * @param object $quiz the quiz settings.
     * @param int $examattemptid the makeexam  attempt to restore slots from.
     * @return void;
     */
    protected function restore_saved_attempt($quiz, int $examattemptid) {
        global $DB;

        $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$examattemptid), '*', MUST_EXIST);

        // we always restore into an empty quiz
        $this->clear_quiz($quiz);

        $this->load_slots_sections_from_attempt($quiz, $examattempt);
    }

    /**
     * Load stored questions in makeexam_slots into a quiz module instance .
     * OLD restore_quiz_from_attempt
     *
     * @param object $quiz the quiz settings.
     * @param int/object $examattempt the makeexam  attempt to restore slots from, either ID or full record.
     * @param bool $move to indicate if questions restored form a mkattempt from other quiz
     *              (used to copy questions to exam delivery intances).
     * @param bool $shuffle if sections suffled. Used for actual  exam delivery intances.
     * @param bool  $insertcontrol wheter insert the control question, proctoring
     * @return void;
     */
    protected function load_slots_sections_from_attempt($quiz, $examattempt, $move = false, $shuffle = false, $insertcontrol = false ) {
        global $DB;

        if(!is_object($examattempt)) {
            $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$examattempt), '*', MUST_EXIST);
        }

        if(($quiz->id != $examattempt->quizid) && !$move) {
            \core\notification::add(get_string('differentsourcetarget', 'quiz_makeexam'),
                                        \core\output\notification::NOTIFY_SUCCESS);
            return false;
        }
        $sourcequizid = $examattempt->quizid;

        // ensure current quiz is empty, no questions or sections (only first)
        $this->delete_quiz_slots_sections($quiz->id);

        $fields = 'qms.id, qv.questionid, qms.slot, qms.page, qms.maxmark, qms.questionbankentryid ';
        $slots = $this->attempt_real_questions($sourcequizid, $examattempt->id, $fields, '');

        // actually adding questions to quiz
        foreach($slots as $slot) {
            $mark = empty($slot->maxmark) ? null : $slot->maxmark;
            quiz_add_quiz_question((int)$slot->questionid, $quiz, (int)$slot->page, $mark);
        }

        $DB->delete_records('quiz_sections', array('quizid'=>$quiz->id));
        if($sections = $DB->get_records('quiz_makeexam_sections', array('quizid'=>$sourcequizid, 'mkattempt'=>$examattempt->id), 'firstslot ASC')) {
            foreach($sections as $section) {
                $section->quizid = $quiz->id;
                if($shuffle) {
                    $section->shufflequestions = 1;
                }
                unset($section->id);
                $newid = $DB->insert_record('quiz_sections', $section);
            }
        }

        quiz_update_sumgrades($quiz);
    }

    /**
     * Generate a PDF file from current attempt. Sends/store as indicated
     * @param object $quiz the quiz settings.
     * @param int $quizattemptid ID of quiz_attempt table entry
     * @param int $examid ID of Exam_ table entry
     * @param int $examattemptid ID of quiz_makeexam_attempt table entry
     * @param string $type the content of the PDF generated should included questions && correct answers & feedback or keys
     * @param bool $store If the PDF is sent to browser or stored in moodle files
     * @return mixed true or files as string
     */
    protected function generate_pdf($quiz, $quizattemptid, $examid , $examattemptid, $type = 'exam', $store = false) {
        global $CFG, $DB, $PAGE, $USER;
        require_once($CFG->dirroot.'/tag/lib.php');
        require_once($CFG->dirroot.'/local/ulpgccore/lib.php');
        require_once('pdf.class.php');

        $exam = $this->exams[$examid];
        $examattempt = $this->exams[$examid]->attempts[$examattemptid];

        if(!$exam || !$examattempt) {
            return get_string('noexamorattempt', 'quiz_makeexam');
        }

        $attemptobj = quiz_attempt::create($quizattemptid);
        $page = 0;

        // Check login.& capabilities
        require_login($attemptobj->get_course(), false, $attemptobj->get_cm());
        $attemptobj->require_capability('mod/quiz:manage');

        // Check the access rules.
        $accessmanager = $attemptobj->get_access_manager(time());
        $accessmanager->setup_attempt_page($PAGE);
        $output = $PAGE->get_renderer('mod_quiz');

        // Get the list of questions needed by this page. // first done with all
        $slots = $attemptobj->get_slots('all');

        // Check.
        if (empty($slots)) {
            throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'noquestionsfound');
        }

        // get exam data
        $examcourses = local_ulpgccore_load_courses_details(array($exam->courseid), 'c.id, c.fullname, c.idnumber, c.shortname, uc.department, uc.credits, uc.term');
        $examcourse = reset($examcourses);
        unset($examcourses);
        $categoryid = $DB->get_field('local_ulpgccore_categories', 'categoryid', array('degree'=>$exam->programme));
        $filename = $examcourse->shortname.'-';
        $programme = $exam->programme.' - '.$DB->get_field('course_categories', 'name', array('id'=>$categoryid));
        $coursename = $examcourse->shortname.' - '.$examcourse->fullname;
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
        $period = $name.' ('.$idnumber.')';
        $filename .= $idnumber;
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
        $scope = $name;
        $filename .= '-'.$idnumber.'-'.$exam->callnum;
        $callnum = get_string('callnum', 'examregistrar').': '.$exam->callnum;
        $scope .= ', '.$callnum;
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->annuality);
        $annuality = $name;
        $examname = $period.', '.$scope.' ('.$annuality.')';

        // PDF title section
        $pdf = new makeexam_pdf();
        $pdf->print_exam_header($programme, $coursename, $annuality, $period, $examname, $scope);

        // set font
        $pdf->SetFont('helvetica', '', 9);
        if($type == 'key') {
            $pdf->SetFont('helvetica', '', 10);
        }

        // PDF questions
        $lastpage = 0;
        $options = $attemptobj->get_display_options(false);
        $options->rightanswer = ($type == 'answers') ? 1 : 0;
        $number = 1;
        foreach ($slots as $slot) {
            $qa = $attemptobj->get_question_attempt($slot);
            $question = $qa->get_question();
            if($type == 'key') {

            } else {
                $page = $attemptobj->get_question_page($slot);
                if($page != $lastpage) {
                    $pdf->AddPage('', '', true);
                    $lastpage = $page;
                }

                $qtoutput = $question->get_renderer($PAGE);
                $html = $qtoutput->formulation_export($qa, $options);
                if(strpos($html, '/filter/tex/pix.php/') !== false) {
                    //$html .= '</p> TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX </p>';
                                   //print_object($html);
                }

                if($html[0] === '<') {
                    $p = strpos($html, '>');
                    $html = substr_replace($html, '>'.$number.'. ', $p,1);
                } else {
                    $html = $number.'. '.$html;
                }
                $pdf->writeHTML($html, false, false, true, false, 'J');
                if($type == 'answers' && !is_a($question, 'qtype_description_question')) {
                    //$right = $question->get_right_answer_summary();
                    $feedback = '';
                    if(method_exists($question, 'format_generalfeedback')) {
                        $feedback = $question->format_generalfeedback($qa);
                    }
                    //$tags = tag_get_tags_csv('question', $question->id, TAG_RETURN_HTML, 'official');
                    $tags = core_tag_tag::get_item_tags_array('', 'question', $question->id, core_tag_tag::STANDARD_ONLY);

                    $category = format_string($DB->get_field('question_categories', 'name', array('id'=>$question->category)));
                    $info = get_string('feedback', 'quiz_makeexam').strip_tags($feedback, '<a><sup><sub><strong><b><i><em><small>').' / '.
                                get_string('category', 'quiz_makeexam').$category.' / '.
                                get_string('tags', 'quiz_makeexam').implode(', ', $tags);
                    $html = $output->container($info, 'questioninfo');
                    $pdf->writeHTML($html, false, false, true, false, 'J');
                }
            }

            $pdf->Ln(8);
            $number++;
        }

        $pdf->Ln(10);

        $filename = clean_filename($filename).'.pdf';

        if(!$store) {
            $pdf->Output($filename, 'I');
            die;
        } else {
            return $pdf->Output($filename, 'S');
        }
    }

    /**
     * Generate a PDF file from current attempt. Sends/store as indicated
     * @param object $cm the course module object
     * @param object $quiz the quiz settings.
     * @param object $course the course record
     * @param int $quizattemptid ID of quiz_attempt table entry
     * @param int $examid ID of the exam table entry
     * @param int $examattemptid ID of quiz_makeexam_attempt table entry
     * @return string error message, if any
     */
    protected function submit_attempt($cm, $quiz, $course, $quizattemptid, $examid, $examattemptid) {
        global $CFG, $DB, $USER;

        $message = get_string('sent', 'quiz_makeexam');
        $exam = $this->exams[$examid];
        $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$examattemptid, 'examid'=>$examid, 'quizid'=>$quiz->id), '*', MUST_EXIST);

        if(!$exam || !$examattempt) {
            return get_string('noexamorattempt', 'quiz_makeexam');
        }

        $registrarattempts = $this->exams[$examid]->examfiles;
        $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);

        require_capability('mod/examregistrar:submit', context_course::instance($examcourse->id));
        // check we can indeed submit a new
        if($examattempt->status && !has_capability('mod/examregistrar:manageexams', $this->context)) {
            return get_string('alreadysent', 'quiz_makeexam');
        }
        //if($examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$exam->id))) {
        $numattempts = 0;
        if($exam->examfiles) {
            foreach($exam->examfiles as $item) {
                if($item->status >= EXAM_STATUS_APPROVED) {
                    $cansubmit = false;
                    return get_string('alreadyapproved', 'quiz_makeexam');
                }
                if($item->attempt > $numattempts) {
                    $numattempts = $item->attempt;
                }
            }
        }


        // no attempt, we are adding
        $now = time();
        $examfile = new stdClass;
        $examfile->examid = $exam->id;
        $examfile->status = EXAM_STATUS_SENT;
        $examfile->attempt = $registrarattempts ? max(count($registrarattempts), $numattempts) + 1 : 1; // ensure allways greater number even if deleted any examfile
        $examfile->name = $examattempt->name;
        $examfile->idnumber = examregistrar_examfile_idnumber($exam, $examcourse->idnumber);
        $examfile->userid = $USER->id;
        $examfile->printmode = 0;
        $examfile->timecreated = $now;
        $examfile->timemodified = $now;

        $newid = $DB->insert_record('examregistrar_examfiles', $examfile);

        if($newid) {
            require_once($CFG->dirroot . '/mod/examregistrar/renderable.php');
            $examregistrar = $DB->get_record('examregistrar', array('id' => $exam->examregid), '*', MUST_EXIST);
            $examfile->id = $newid;
            // update examattemp
            $examattempt->status = 1;
            $examattempt->examfileid = $newid;
            $examattempt->timesubmitted = $now;
            $DB->update_record('quiz_makeexam_attempts', $examattempt);

            // now we can generate & store exam PDF  files
            $fs = get_file_storage();
            $filecontext = context_course::instance($examcourse->id);
            $fileinfo = array(
                'contextid' => $filecontext->id, // ID of exam course context
                'component' => 'mod_examregistrar',
                'filearea' => 'exam',
                'itemid' => $newid,               // the id of the new examfile entry
                'filepath' => '/',
                'filename' => examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'exam'));

            // Create file containing no responses
            $fs->create_file_from_string($fileinfo, $this->generate_pdf($quiz, $quizattemptid, $examid, $examattempt->id, 'exam', true));
            // Create file containing  correct answers
            $fileinfo['filepath'] = '/answers/';
            $fileinfo['filename'] = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'answers');
            $fs->create_file_from_string($fileinfo, $this->generate_pdf($quiz, $quizattemptid, $examid, $examattempt->id, 'answers', true));
            // Create file containing  correct answers
            $fileinfo['filepath'] = '/key/';
            $fileinfo['filename'] = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'key');

            $eventdata = array();
            $eventdata['objectid'] = $examattempt->id;
            $eventdata['context'] = $this->context;
            $eventdata['other'] = array();
            $eventdata['other']['quizid'] = $quiz->id;
            $eventdata['other']['examid'] = $examattempt->examid;
            $eventdata['other']['examfileid'] = $examattempt->examfileid;
            $eventdata['other']['idnumber'] = $examfile->idnumber;
            $event = \quiz_makeexam\event\exam_submitted::create($eventdata);
            $event->trigger();

            // now create tracker issue for examfile
            $examregistrar = $this->get_examregistrar_instance($cm, $course);
            $issueid = examregistrar_review_addissue($examregistrar, $course, $examfile);
            if($issueid == 0 ) {
                $message = get_string('noreviewmod', 'quiz_makeexam');
            } elseif($issueid == -1 ) {
                $message = get_string('notracker', 'quiz_makeexam');
            }
            if(\examregistrar_exam_attemptsreview::warning_questions_used($examfile)) {
                $examfile->printmode = 1;
                $DB->set_field('examregistrar_examfiles', 'printmode', 1, array('id'=>$newid));
            }

            $this->clear_quiz($quiz);
        }
        return $message;
    }

}
