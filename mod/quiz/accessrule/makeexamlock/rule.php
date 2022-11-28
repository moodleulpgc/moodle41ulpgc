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
 * Implementaton of the quizaccess_makeexamlock plugin.
 *
 * @package    quizaccess
 * @subpackage makeexamlock
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');


/**
 * A rule controlling the number of attempts allowed.
 *
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_makeexamlock extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        if (empty($quizobj->get_quiz()->makeexamlock) || 
                !get_config('quiz_makeexam', 'enabled') ) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    public function description() {
    
        $message = '';
        $context = $this->quizobj->get_context();
        $message .= get_string('makeexamlockingmsg', 'quizaccess_makeexamlock');
        if(has_capability('quizaccess/makeexamlock:viewdesc', $context)) {
            $url = new moodle_url('/mod/quiz/report.php',
                    array('id' => $this->quizobj->get_cmid(), 'mode' => 'makeexam'));

            $message .= html_writer::div(html_writer::link($url, get_string('gotomakeexam', 'quizaccess_makeexamlock'))); 
        }
        
        return html_writer::span($message, ' alert-info');
    }

    public function prevent_access() {
        global $CFG, $DB, $USER;
    
        $message = false;
        if($this->quiz->makeexamlock == 0) {
            return false;
        } elseif($this->quiz->makeexamlock < 0) {
            $message = html_writer::span(get_string('makeexamlockingmsg', 'quizaccess_makeexamlock'), ' alert-success');        
        } elseif($this->quiz->makeexamlock > 0) {
            include_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
            $context = $this->quizobj->get_context();
            $canmanage = has_capability('mod/quiz:manage', $context);
            
            // so, we have an instance locked for a single examid
            $config = get_config('quizaccess_makeexamlock');
            $courseid = $this->quizobj->get_courseid();
            $examregid = self::get_primary_examregid($courseid);
            $params = array('examregid' => $examregid, 
                            'courseid'  => $courseid,
                            'id'        => $this->quiz->makeexamlock);
            $examrec = $DB->get_record('examregistrar_exams', $params);
            $exam = new \examregistrar_exam($examrec);
            if($examfilemsg = $exam->set_valid_file()) {
                $examfilemsg = $canmanage ? $examfilemsg : '';
                $message = html_writer::span(get_string('notreadylockingmsg', 'quizaccess_makeexamlock', $examfilemsg), ' alert-danger') ;
                return $message;
            }
            $params = array('examid'=> $this->quiz->makeexamlock,
                            'id'    => $exam->examfile);
            $info =  $canmanage ? $DB->get_field('examregistrar_examfiles', 'idnumber', $params) : '';
            
            $makeexamattempt = $exam->get_makeexam_attempt();
            
            
            // set control question, if in use and ID exists
            $examregistrar = $DB->get_record('examregistrar', array('id' => $examregid));
            examregistrar_get_primaryid($examregistrar);
            $configdata = examregistrar_get_instance_config($examregistrar->id);
            $controlquestion = false;
            if($configdata->insertcontrolq && $DB->record_exists('question', array('id' => $configdata->controlquestion))) {
                $controlquestion = $configdata->controlquestion;
            }
            $validquestions = $exam->has_valid_questions($controlquestion); 
            //// TODO remove , transirnt for test, some course with control
            $validquestions = true;
            
            if($makeexamattempt) {
                if(($exam->callnum > 0) && ($makeexamattempt->examid != $this->quiz->makeexamlock)) {
                    $message = html_writer::span(get_string('wrongexammsg', 'quizaccess_makeexamlock', $info), ' alert-danger');
                } elseif(!$validquestions) {
                    $message = html_writer::span(get_string('examchangedmsg', 'quizaccess_makeexamlock', $info), ' alert-danger');
                } else {
                    if($exam->status >= EXAM_STATUS_APPROVED) { 
                        //return false;
                    } else {
                        $message = html_writer::span(get_string('notreadylockingmsg', 'quizaccess_makeexamlock', $info), ' alert-danger');
                    }
                }
            }
            //$message = html_writer::span(get_string('singleversionlockingmsg', 'quizaccess_makeexamlock', $info), ' alert-danger');
            //if($makeexamattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$this->quiz->id, 'currentattempt'=>1))) {
            
            // the questions are ready, lets see the user taker 
            if(!$message && !$canmanage) { 
                if($config->requirebooking) {
                    $params = array('examid'    =>$this->quiz->makeexamlock, 
                                    'userid'    => $USER->id, 
                                    'booked'    => 1 );
                    if(!$booking = $DB->get_record('examregistrar_bookings', $params)) {
                        $message = html_writer::span(get_string('notbookedlockingmsg', 'quizaccess_makeexamlock', $info), ' alert-danger');
                    }   
                }
            }
            
        }
        return $message;
    }
    
    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        return $this->prevent_access(); 
    }

    public function is_finished($numprevattempts, $lastattempt) {
        return false;
    }
    
   
    /**
     * Add any fields that this rule requires to the quiz settings form. This
     * method is called from {@link mod_quiz_mod_form::definition()}, while the
     * security seciton is being built.
     * @param mod_quiz_mod_form $quizform the quiz settings form that is being built.
     * @param MoodleQuickForm $mform the wrapped MoodleQuickForm.
     */
    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        global $CFG; 
            
        if(get_config('quiz_makeexam', 'enabled')) {    
            include_once($CFG->dirroot.'/mod/examregistrar/locallib.php');        
            $options = array(0 => get_string('no'), 
                            -1 => get_string('any')
                            );
            
            $cm = $quizform->get_coursemodule();
            $course = $quizform->get_course();
            
            $context = $quizform->get_context();
            $canmanage = has_capability('quizaccess/makeexamlock:manage', $context);
            $canunlock = has_capability('quizaccess/makeexamlock:unlock', $context);
            
            if($examregid = self::get_primary_examregid($course->id)) {
                if($exams = self::get_managed_exams($examregid, $course->id, $cm, $canmanage)) {
                    foreach($exams as $examid => $exam) {
                        $options[$examid] = $exam;
                    }
                }
            }
            
            $mform->addElement('select', 'makeexamlock', 
                    get_string('makeexamlock', 'quizaccess_makeexamlock'), $options);            
            $mform->addHelpButton('makeexamlock','makeexamlock', 'quizaccess_makeexamlock');
            
            if(!has_capability('quizaccess/makeexamlock:unlock', $context)) {
                $mform->freeze('makeexamlock');
            }
        }
    }
    
    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from {@link quiz_after_add_or_update()} in lib.php.
     * @param object $quiz the data from the quiz form, including $quiz->id
     *      which is the id of the quiz being saved.
     */
    public static function save_settings($quiz) {
        global $DB;
       
        if (empty($quiz->makeexamlock) || !get_config('quiz_makeexam', 'version') ) {
            $DB->delete_records('quizaccess_makeexamlock', array('quizid' => $quiz->id));
        } else {
            if (!$record = $DB->get_record('quizaccess_makeexamlock', array('quizid' => $quiz->id))) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->makeexamlock = $quiz->makeexamlock;
                $DB->insert_record('quizaccess_makeexamlock', $record);
            } else {
                $record->makeexamlock = $quiz->makeexamlock;
                $DB->update_record('quizaccess_makeexamlock', $record);
            }
        }
    }

    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from {@link quiz_delete_instance()} in lib.php.
     * @param object $quiz the data from the database, including $quiz->id
     *      which is the id of the quiz being deleted.
     * @since Moodle 2.7.1, 2.6.4, 2.5.7
     */
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_makeexamlock', array('quizid' => $quiz->id));
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probalby to read the code of {@link quiz_access_manager::load_settings()}.
     *
     * If you have some settings that cannot be loaded in this way, then you can
     * use the {@link get_extra_settings()} method instead, but that has
     * performance implications.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *     can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be alised
     *        if neccessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid) {
        return array(
            'makeexamlock',
            'LEFT JOIN {quizaccess_makeexamlock} makeexamlock ON makeexamlock.quizid = quiz.id',
            array());
    }

    protected static function get_primary_examregid($courseid) {
        global $DB; 
        
        $examregid = false;
        $config = get_config('quizaccess_makeexamlock');
        
        if($config->examregmode == 'examreg') {
            $examregid = $DB->get_field('examregistrar', 'id', array('primaryidnumber' => $config->examreg));
        } elseif($config->examregmode == 'idnumber') {
            $mods = get_coursemodules_in_course('examregistrar', $courseid);
            foreach($mods as $mod) {
                if($mod->idnumber == $config->examreg) {
                    if($examregistrar = $DB->get_record('examregistrar', array('id' => $mod->instance))) {
                        $examregid = examregistrar_get_primaryid($examregistrar);
                        break;
                    }
                }
            }
        }
        
        return $examregid;
    }
    
    protected static function get_managed_exams($examregid, $courseid, $cm, $canmanage) {
        global $CFG, $DB;
       
        $exams = false;
        $examregistrar = $DB->get_record('examregistrar', array('id'=>$examregid));
        $exams = examregistrar_get_referenced_examsmenu($examregistrar, 'exams', array('courseid' => $courseid), $examregid);
        $linkedexamid = false;
        if($cm && !$canmanage) {
            //check if instance vinculated 
            $linkedexamid = $DB->get_field('examregistrar_exams', 'id', 
                        array('examregid' => $examregid, 'courseid' => $cm->course, 'quizplugincm' => $cm->id));
        
        }
        
        if($linkedexamid && array_key_exists($linkedexamid, $exams)) {
            $exams = array($linkedexamid => $exams[$linkedexamid]);
        } 
        
        foreach($exams as $examid => $name) {
            list($examname, $notused) = examregistrar_get_namecodefromid($examid, 'exams');
            $exams[$examid] = $examname; 
        }
    
        return $exams;
    }
}
