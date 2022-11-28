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
defined('MOODLE_INTERNAL') || die();

class examregistrar_room implements renderable {
    /** @var int $roomid the id of the room, must correspond to an ID on locations table */
    protected $roomid;
    /** @var string $name the name of the room */
    public $name = '';
    /** @var string $idnumber the idnumber code of the room */
    public $idnumber = '';
    /** @var string $locationtype as string name  */
    public $locationtype = '';
    /** @var string $address formatted address  */
    public $address = '';
    /** @var int $seats number of seats in room  */
    public $seats = 0;
    /** @var int $parent ID for parent room  */
    public $parent;
    /** @var int $depth parent tree depth  */
    public $depth;
    /** @var string $path tree path to room  */
    public $path;
    /** @var int $sortorder room ordering  */
    public $ortorder;
    /** @var bool $visible   */
    public $visible = true;

    public function __construct($room, $field='id') {
        $this->roomid = $room->$field;

        $params = array('name', 'idnumber', 'address', 'seats', 'parent', 'depth', 'path', 'sortorder', 'visible');
        foreach($params as $param) {
            if(isset($room->$param)) {
                $this->$param = $room->$param;
            }
        }

        if(isset($room->addressformat)) {
            $this->address = format_text($this->address, $room->addressformat, array('filter'=>false, 'para'=>false));
        }
        if(isset($room->locationtype)) {
            if(is_int($room->locationtype)) {
            } elseif(is_string($room->locationtype)) {
                $this->locationtype = $room->locationtype;
            }
        }
    }

    public function get_id() {
        return $this->roomid;
    }

    public function formatted_itemname($options=array()) {
        global $PAGE;
        $output = $PAGE->get_renderer('mod_examregistrar');
        return $output->formatted_itemname($this->name, $this->idnumber, $options);
    }

}


class examregistrar_exam implements renderable {
    /** @var int $examid the id of the exam, must correspond to an ID on exams table */
    protected $examid;
    /** @var int $courseid the ID of the course this exam is realated to, must correspond to an ID on course table */
    public $courseid;
    /** @var string $annuality the annuality this exam belongs to*/
    public $annuality = '';
    /** @var string $programme the programme/category of the course/exam */
    public $programme = '';
    /** @var string $shortname the shortname of the course */
    public $shortname = '';
    /** @var string $fullname the fullname of the course */
    public $fullname = '';
    /** @var int $period the ID of the period this exam is realated to, must correspond to an ID on period table */
    public $period = 0;
    /** @var int $callnum the N this call makes in total call for this exam & period */
    public $callnum;
    /** @var string $scope the name of examscope type for this exam */
    public $examscope = '';
    /** @var string $examsession the name of examssession type for this exam */
    public $examsession = '';
    /** @var int $examfile the row ID for an entry in examfiles table   */
    public $examfile;
    /** @var int $taken if this exam has been declared taken, in examfiles table   */
    public $taken;
    /** @var int $printmode if this exam has to be printed single/double page, in examfiles table   */
    public $printmode;
    /** @var array $allowedtools Instructions about devices allowed to use during examination    */
    public $allowedtools;
    /** @var int $quizplugincm the cm of the quiz instance associated with this exam   */
    public $quizplugincm;
    /** @var int $assignplugincm the the cm of the assign instance associated with this exam  */
    public $assignplugincm;
    /** @var int $status the defined status in examfiles table */
    public $status;

    
    
    /** @var bool $visible   */
    public $visible = true;

    public function __construct($exam, $field='id') {
        global $DB;
        $this->examid = $exam->$field;

        $params = array('annuality', 'courseid', 'programme', 'shortname', 'fullname', 
                        'period', 'callnum', 'examscope', 'examsession', 'visible', 
                        'assignplugincm', 'quizplugincm', 'deliveryid', 'helpermod', 
                        'helpercmid', 'timeopen', 'timeclose', 'timelimit', 'deliverysite');
        foreach($params as $param) {
            if(isset($exam->$param)) {
                $this->$param = $exam->$param;
            }
        }
        if($this->courseid && empty($this->shortname)) {
            $this->shortname = $DB->get_field('course', 'shortname', ['id' => $this->courseid]);
        }
        $this->examfile = false;

    }

    public function get_id() {
        return $this->examid;
    }

    public function get_exam_name($addprogramme=false, $addscope=false, $addfullname=false, $linkname=false) {
        global $DB;
        $examname = $addprogramme ? $this->programme.'-'.$this->shortname : $this->shortname;
        $space = $addfullname ? ' ' : '';
        if($addscope) {
            if($DB->count_records('examregistrar_exams', array('courseid'=>$this->courseid, 'examsession'=>$this->examsession, 'callnum'=>$this->callnum)) > 1 ) {
                $scope = $DB->get_field('examregistrar_elements', 'idnumber', array('id'=>$this->examscope));
                $examname .= $space."($scope)";
            }
        }
        if($addfullname) {
            $name = $this->fullname;
            if($linkname && $cmid = examregistrar_get_course_instance($this)) {
                $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cmid, 'tab'=>'view'));
                $name = html_writer::link($url, $name);
            }
            $examname .= ' - '.$name;
        }
        return $examname;
    }

    public function get_exam_deliver_helper($withicon=false, $withlink=false) {
        global $DB, $OUTPUT;

        $helperurl = new moodle_url('/mod/'.$this->helpermod.'/view.php', ['id' => $this->helpercmid]);
        $name = $this->get_helpermod_instance_name();
        if($withicon) {
            $icon = new pix_icon('icon', '', 'quiz', array('class'=>'icon', 'title'=>''));
        }
        
        return $OUTPUT->action_link($helperurl,$name, null, null, $icon); 
    }
    
    public function set_valid_file() {
        global $DB;

        $this->examfile = false;
        $this->printmode = null;
        $this->allowedtools = null;
        $message = false;
        $examfile = '';
        if($examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$this->examid, 'status'=>EXAM_STATUS_APPROVED),'timeapproved DESC, attempt DESC')) {
            if(count($examfiles) > 1) {
                // error, more than one attempt approved
                $message = get_string('error_manyapproved', 'examregistrar');
            }
            $examfile = reset($examfiles);
            $this->examfile = $examfile->id;
        } elseif($examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$this->examid, 'status'=>EXAM_STATUS_SENT),'timeapproved DESC, attempt DESC')) {
            if(count($examfiles) > 1) {
                // error, more than one attempt approved
                $message = get_string('error_manysent', 'examregistrar');
            }
            $examfile = reset($examfiles);
            $this->examfile = $examfile->id;
            $message = get_string('error_noneapproved', 'examregistrar');
        } else {
            $message = get_string('error_nonesent', 'examregistrar');
        }

        if($this->examfile && $examfile) {
            $this->taken = $examfile->taken;
            $this->printmode  = $examfile->printmode;
            $this->status = $examfile->status;
            if(isset($examfile->allowedtools) && !empty($examfile->allowedtools)) {
                if($permissions = json_decode($examfile->allowedtools)) {
                    $this->allowedtools = get_object_vars($permissions);
                }
            }               
        }
        
        return $message;
    }

    
    /**
     * Returns helper module instance.
     *
     * @return stdClass mod instance record as from database plus cmid & modname.
     */
    public function get_helpermod_instance() {
        if(isset($this->helper_instance) && $this->helper_instance) {
            return $this->helper_instance;
        }
        return $this->load_helpermod_instance();
    }
    
    
    /**
     * Loads helper module instance. 
     * Uses fast_modinfo is possible, if not retrieves form database
     *
     * @return stdClass mod instance record as from database plus cmid & modname.
     */
    public function load_helpermod_instance() {
        global $DB;
        
        $instance = '';
        
        if(!empty($this->helpermod) && isset($this->helpercmid) && $this->helpercmid) {
            if($cminfo = get_fast_modinfo($this->courseid)->cms[$this->helpercmid]) {
                if($cminfo->instance) {
                    if($instance = $DB->get_record($this->helpermod, ['id' => $cminfo->instance], '*', MUST_EXIST)) {
                        $instance->cmid = $this->helpercmid;
                        $instance->modname = $this->helpermod;
                    }
                }
                
            }
            if(!$instance) {
                $sql = 'SELECT h.*, cm.id AS cmid, m.name AS modname 
                        FROM {course_modules} cm  
                        JOIN {modules} m ON cm.module = m.id AND m.name = :helpermod
                        JOIN {'.$this->helpermod.'} h ON cm.course = h.course AND cm.instance = h.id
                        WHERE cm.id = :cmid ';
                $instance = $DB->get_record_sql($sql, array('cmid' => $this->helpercmid, 'helpermod' => $this->helpermod), '*', MUST_EXIST);
            }
        }

        if($instance) {
            $this->helper_instance = $instance;
            $this->helper_instanceid = $this->helper_instance->id;
        } else {
            $this->helper_instance = null;
            $this->helper_instanceid = null;
        }
        
        return $this->helper_instance;
    }    
    
    /**
     * Returns helpeer module instance.
     *
     * @return bool true of success, false otherwise.
     */
    public function get_helpermod_instance_name() {
        global $DB;
        
        if(!empty($this->helpermod) && isset($this->helpercmid) && $this->helpercmid) {
            if($cminfo = get_fast_modinfo($this->courseid)->cms[$this->helpercmid]) {
                return $cminfo->name;
            }
            $sql = 'SELECT h.name 
                    FROM {course_modules} cm  
                    JOIN {modules} m ON cm.module = m.id AND m.name = :helpermod
                    JOIN {'.$this->helpermod.'} h ON cm.course = h.course AND cm.instance = h.id
                    WHERE cm.id = :cmid ';
            
            $name = $DB->get_field_sql($sql, array('cmid' => $this->helpercmid, 'helpermod' => $this->helpermod));
        }
        return $name;
    }         
    
    /**
     * Returns helper module checking flags.
     *
     * @return bool true of success, false otherwise.
     */
    public function get_helper_taken_data() {  
        global $CFG, $DB;
        
        $numfinished = 0;
        $numattempts = 0;
    
        // only if exam has started
        if($this->timeopen < time()) {
            if(!isset($this->helper_instanceid)) {
                $this->get_helpermod_instance();
            }

            if(isset($this->helper_instanceid) && $this->helper_instanceid) {
                require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');            
                $numattempts = $DB->count_records('quiz_attempts', 
                                    array('quiz'=> $this->helper_instanceid, 'preview'=>0));
                $numfinished = $DB->count_records('quiz_attempts', 
                                    array('quiz'=> $this->helper_instanceid, 'preview'=>0, 'state' => \quiz_attempt::FINISHED));
            }
        }
        
        $this->taken = $numattempts;
        
        return [$numfinished, $numattempts];
    }
    
    /**
     * Returns helper module checking flags.
     *
     * @return bool true of success, false otherwise.
     */
    public function get_helper_flags() {
        global $DB;
        
        $instance = $this->get_helpermod_instance();
        $instances = [$instance];
        $flags = [];
        
        $isextracall = ($this->callnum < 0);
        
        if($isextracall) {
            // Is an extra call, nominal dates are irrelevant, 
            // we must check for user overrides 
            // TODO // TODO make polymorphic dependeing on helper mode
            $sql = "SELECT o.id AS oid, o.quiz AS id, 
                            o.timeopen, o.timeclose, o.timelimit, 
                            o.password, o.attempts  
                      FROM {quiz_overrides} o 
                      JOIN {examregistrar_bookings} b ON b.userid = o.userid AND b.examid = :examid AND b.booked = 1 AND b.bookedsite = :deliverysite
                     WHERE o.quiz = :instanceid  ";
            $params = ['instanceid' => $instance->id, 'examid' => $this->examid, 'deliverysite' => $this->deliverysite];
            
            $instances = $DB->get_records_sql($sql, $params);
        
            if(empty($instances)) { 
                $instances = [$instance];
                $flags['extranobook'] = 'warning';
            }
        }
        
        foreach($instances as $instance) {
            // dates (example is for quiz module)
            if($this->timeopen && ($instance->timeopen != $this->timeopen)) {
                $flags['timeopen'] = $isextracall ?  'warning' : 'danger';
                if(usergetmidnight($instance->timeopen) !=  usergetmidnight($this->timeopen)) {
                    $flags['datetime'] = $isextracall ?  'warning' : 'danger';
                }
            }
            if($this->timeclose && ($instance->timeclose != $this->timeclose)) {
                $flags['timeclose'] = $isextracall ?  'warning' : 'danger';
                if(usergetmidnight($instance->timeclose) !=  usergetmidnight($this->timeclose)) {
                    $flags['datetime'] = $isextracall ?  'warning' : 'danger';
                }
            }
            if($this->timelimit && ($instance->timelimit != $this->timelimit)) {
                $flags['timelimit'] = $isextracall ?  'warning' : 'danger';
            }
            if($instance->password) {
                $flags['password'] = $isextracall ?  'warning' : 'danger';
            }
        }

        // makexamlock 
        $mklock = $DB->get_field('quizaccess_makeexamlock', 'makeexamlock', ['quizid' => $instance->id]);
        if(empty($mklock)) {
            $flags['accessfree'] = 'danger';
        } elseif($mklock != $this->examid) {
            $flags['accesslocked'] = 'danger';
        }
        
        $controlquestion = false;
        $flags['questions'] = $this->has_valid_questions($controlquestion) ? 'success' : 'danger';
        
        return $flags;
    }
    
    
    public static function get_quiz_from_cmid($cmid) {
        global $DB;
        $sql = "SELECT q.*, cm.id AS cmid 
                FROM {quiz} q 
                JOIN {course_modules} ON cm.course = q.course AND cm.instance = q.id 
                WHERE cm.id = :cmid   ";
    
        return $DB->get_record_sql($sql, array('cmid' => $cmid));
    }
    
    
    public function get_associated_quiz() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
        require_once($CFG->dirroot . '/mod/quiz/report/default.php');
        include_once($CFG->dirroot.'/mod/quiz/report/makeexam/report.php');

        list ($course, $cm) = get_course_and_cm_from_cmid($this->quizplugincm, 'quiz');
        $quiz = $DB->get_record('quiz', array('id' => $cm->instance), '*', MUST_EXIST);
        $makeexam = new quiz_makeexam_report();
        $makeexam->init('makeexam', $quiz, $cm, $course);
    
        return array($quiz, $makeexam);
    }
    
    public function clear_quiz_questions() {
        list ($quiz, $makeexam) = $this->get_associated_quiz();
        $makeexam->clear_quiz($quiz);
    }
    
    public function set_valid_questions($insertcontrol, $notify = false) {
        global $CFG, $DB, $USER;    
        
        require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
        $message = '';
        
        if(!$this->quizplugincm) {
            $message = ' error no quizcm';
            return $message;
        }
        
        if(!$this->examfile && $this->callnum >= 0 ) {
            $message = ' error no examfile';
            return $message;
        }
        /*
        list ($course, $cm) = get_course_and_cm_from_cmid($this->quizplugincm, 'quiz');
        $quiz = $DB->get_record('quiz', array('id' => $cm->instance), '*', MUST_EXIST);
        $makeexam = new quiz_makeexam_report();
        $makeexam->init('makeexam', $quiz, $cm, $course);
        */
        list ($quiz, $makeexam) = $this->get_associated_quiz();
        
        if(quiz_has_attempts($quiz->id)) {
            $message = ' quiz hass attemps';
            $this->taken = 1; 
            if($this->examfile) {
                $DB->set_field('examregistrar_examfiles', 'taken', 1, array('id' => $this->examfile, 'examid' => $this->examid));
            }
            return $message;
        } 
        
        $quizobj = quiz::create($quiz->id, $USER->id);
        $quizobj->preload_questions();
        $quizobj->load_questions();
        $qzquestions = array_keys($quizobj->get_questions());
        
        //  restore quiz questions from stored ones
        $mkquestions = array();
        if($makeexamattempt = $this->get_makeexam_attempt()) {
            $mkquestions = explode(',', $makeexamattempt->questions);
            if($insertcontrol) {
                $mkquestions[] = $insertcontrol;
            }            
        }
        
        if($mkquestions && array_diff($mkquestions, $qzquestions) && (count($mkquestions) != count($qzquestions))) {   
            // change questions, from stored makeexam
            $makeexam->load_exam_questions($quiz, $makeexamattempt, true, $insertcontrol);
            if($notify) {
                $info = $this->get_exam_name(true, true);
                \core\notification::add(get_string('examquestionsloaded', 'examregistrar', $info), \core\output\notification::NOTIFY_SUCCESS);
            }
        }
        
        return $message;
    }
    
    public function get_makeexam_attempt($examfileid = 0, $withcm = false) {
        global $DB;

        $params = array('examid' => -1);
        if($this->callnum < 0 ) {
            // this is a "reserve", out of date exam, get questions from Extra1
            $sql = "SELECT e.id, ef.id AS examfileid, ef.idnumber
                    FROM {examregistrar_exams} e 
                    JOIN {examregistrar_examfiles} ef ON ef.examid = e.id
                    JOIN {examregistrar_periods} ep ON ep.id = e.period 
                    WHERE e.id <> :examid AND e.period <> :period AND ep.calls = 3 AND  e.courseid = :courseid 
                            AND ef.status = :status AND e.callnum > 0 AND e.visible = 1 
                    ORDER BY ef.idnumber ";
            $sqlparams = array('examid' => $this->examid, 'period' => $this->period, 
                                'courseid' =>$this->courseid , 'status' => EXAM_STATUS_APPROVED);
                    
            if($records = $DB->get_records_sql($sql, $sqlparams, 0, 1)) {
                $exam = reset($records);
                $params = array('examid' => $exam->id, 'examfileid' => $exam->examfileid, 'status' => 1);
            }
        } else {
            if(!$examfileid) {
                $examfileid = $this->examfile;
            }
            $params = array('examid' => $this->examid, 'examfileid' => $examfileid, 'status' => 1);
        }
        
        if($withcm) {
            $sql = "SELECT a.id AS review, cm.id AS cm, a.*
                    FROM {quiz_makeexam_attempts} a 
                    JOIN {course_modules} cm ON cm.instance = a.quizid AND cm.course = a.course AND cm.module = (SELECT id FROM {modules} WHERE name = :quiz) 
                    WHERE a.examid = :examid AND a.examfileid = :examfileid  AND a.status = :status ";
            $params['quiz'] = 'quiz';
            return $DB->get_record_sql($sql, $params);
        }
        
        return $DB->get_record('quiz_makeexam_attempts', $params, '*');
    }
    
    
    public function has_valid_questions($controlq = false) {
        global $CFG, $DB, $USER;    
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
    
        $quiz = $this->get_helpermod_instance();
        if(empty($quiz) || ($quiz->modname != 'quiz')){
            return false;
        } 
        $quizobj = quiz::create($this->helper_instanceid, $USER->id);

        if(quiz_has_attempts($quiz->id)) {
            $this->taken = 1; 
            if($this->examfile && $quizobj->has_capability('mod/quiz:grade')) {
                $DB->set_field('examregistrar_examfiles', 'taken', 1, array('id' => $this->examfile, 'examid' => $this->examid));
            }
        }         
        
        $quizobj->preload_questions();
        $quizobj->load_questions();
        $qzquestions = array_keys($quizobj->get_questions());    
        
        //  restore quiz questions from stored ones
        $mkquestions = array();
        if($makeexamattempt = $this->get_makeexam_attempt()) {
            $mkquestions = explode(',', $makeexamattempt->questions);
            if($controlq) {
                $mkquestions[] = $controlq;
            }
        }
        
        return ($qzquestions && empty(array_diff($qzquestions, $mkquestions)) && (count($qzquestions) == count($mkquestions)));            
    }
    
    public function get_examfile_file() {
        $context = context_course::instance($this->courseid);
        $file = false;
        $this->set_valid_file();
        if($this->examfile) {
            list($area, $path) = examregistrar_file_decode_type('exam');
            $fs = get_file_storage();
            if($files = $fs->get_directory_files($context->id, 'mod_examregistrar', $area, $this->examfile, $path, false, false, "filepath, filename")) {
                $file = reset($files);
            }
        }
        return $file;
    }

    public function get_formatted_teachers() {
        global $DB;

        $content = '';

        $select = ", " . context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT c.* $select
                FROM {course} c
                LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                WHERE c.id = :id ";
        $course = $DB->get_record_sql($sql, array('id'=>$this->courseid, 'contextlevel'=>CONTEXT_COURSE), MUST_EXIST);
        //$course = new course_in_list($course);
        $course = new \core_course_list_element($course);
        if($contacts = $course->get_course_contacts()) {
            $content = html_writer::start_tag('ul', array('class' => 'teachers'));
            foreach ($contacts as $userid => $coursecontact) {
                $name = $coursecontact['rolename'].': '.$coursecontact['username'];
                $content .= html_writer::tag('li', $name);
            }
            $content .= html_writer::end_tag('ul'); // .teachers
        }
        return $content;
    }

    public function get_teachers() {
        global $DB;

        $users = array();

        list($select, $join) =  context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
        $sql = "SELECT c.* $select
                FROM {course} c
                $join
                WHERE c.id = :id ";
        $course = $DB->get_record_sql($sql, array('id'=>$this->courseid), MUST_EXIST);
        //$course = new course_in_list($course);
        $course = new \core_course_list_element($course);
        if($contacts = $course->get_course_contacts()) {
            foreach ($contacts as $userid => $coursecontact) {
                $name = $coursecontact['rolename'].': '.$coursecontact['username'];
                $users[$userid] = $name;
            }
        }
        return $users;
    }

    public function get_examdate() {
        global $DB;
        return $DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$this->examsession));
    }

    public function get_print_mode() {
        if(!$this->examfile && !$this->printmode) {
            $this->set_valid_file();
        }
        
        return $this->printmode;
    }
    public function get_exam_instructions() {
        if(!$this->examfile && !$this->allowedtools) {
            $this->set_valid_file();
        }
        
        return $this->allowedtools;
    }
}

class examregistrar_roomexam extends examregistrar_exam implements renderable {
    /** @var int $session examsession ID for this allocation  */
    public $session;
    /** @var int $bookedsite ID for this allocation  */
    public $venue;
    /** @var int $roomid ID for this allocation  */
    public $roomid;
    /** @var int $seated number of students allocated to this room  */
    public $seated = 0;
    /** @var array $users students allocated to this exam  */
    public $users = array();
    /** @var array $users students allocated to this exam  */
    public $bookednotallocated = array();

    public function __construct($session, $venue, $roomid, $exam, $field='id') {
        $this->session = $session;
        $this->venue = $venue;
        $this->roomid = $roomid;
        $this->users = false;
        $this->bookednotallocated = false;
        parent::__construct($exam, $field);
        if(isset($exam->seated)) {
            $this->seated = $exam->seated;
        }
    }

    public function set_users($onlyadditionals=false) {
        global $DB;
        $this->users = array();

        $users = array();

        $fields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT u.id, u.username, u.idnumber, u.picture, $fields, ss.id AS allocid, 0 AS additional
                FROM {examregistrar_session_seats} ss
                JOIN {user} u ON ss.userid = u.id
                WHERE ss.examsession = :session AND ss.bookedsite = :bookedsite AND ss.examid = :exam AND ss.roomid = :room ";
        $order = ' ORDER BY u.lastname, u.firstname, u.idnumber ';

        if(!$onlyadditionals) {
            // users with this as main exam, then additional = 0
            $params = array('session'=>$this->session, 'bookedsite'=>$this->venue, 'room'=>$this->roomid, 'exam'=>$this->examid);
            $users = $DB->get_records_sql($sql.' AND ss.additional = 0 '.$order, $params);
        } else {
            // if only additionals, additionals != 0
            $params = array('session'=>$this->session, 'bookedsite'=>$this->venue, 'room'=>$this->roomid, 'exam'=>$this->examid);
            $users = $DB->get_records_sql($sql.' AND ss.additional = ss.examid '.$order, $params);
        }

        $sql = "SELECT ss.userid, COUNT(ss.additional) AS additional
                FROM {examregistrar_session_seats} ss
                WHERE ss.examsession = :session AND ss.bookedsite = :bookedsite AND ss.roomid = :room AND ss.additional > 0
                GROUP BY ss.userid ";
        $params = array('session'=>$this->session, 'bookedsite'=>$this->venue, 'room'=>$this->roomid);
        if($additionals = $DB->get_records_sql_menu($sql, $params)) {
            foreach($additionals as $uid => $additional) {
                if(isset($users[$uid])) {
                    $user = $users[$uid];
                    $user->additional =  $additional;
                    $users[$uid] = $user;
                }
            }
        }

        $this->users = $users;
        $this->seated = count($users);

        if(!$onlyadditionals) {
            $fields = get_all_user_name_fields(true, 'u');
            $sql = "SELECT  b.*, b.id AS bid, u.id, u.username, u.idnumber, $fields
                    FROM {examregistrar_bookings} b
                    JOIN {user} u ON b.userid = u.id
                    LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
                    WHERE b.examid = :exam AND b.bookedsite = :venue AND b.booked = 1 AND  ss.examid IS NULL
                    ORDER BY u.lastname, u.firstname, u.idnumber ";
            $params = array('exam'=>$this->examid, 'venue'=>$this->venue);
            $users = $DB->get_records_sql($sql, $params);
            $this->bookednotallocated = $users;
        }

        return $this->users;
    }

}

class examregistrar_allocatedroom extends examregistrar_room implements renderable {
    /** @var int $session examsession ID for this allocation  */
    public $session;
    /** @var int $bookedsite ID for this allocation  */
    public $venue;
    /** @var int $seated number of students allocated to this room  */
    public $seated = 0;
    /** @var array $exams colection of roomexams items  */
    public $exams = array();
    /** @var array $additionals colection of roomexams items  */
    public $additionals = array();
    /** @var int $additionalusers number of distinct users with additional exams in this room  */
    public $additionalusers = 0;

    public function __construct($session, $venue, $room, $field='id') {
        $this->session = $session;
        $this->venue = $venue;
        parent::__construct($room, $field);
        if(isset($room->seated)) {
            $this->seated = $room->seated;
        }
    }

    public function add_exam_fromrow($row, $field='id') {
        $exam = new examregistrar_roomexam($this->session, $this->venue, $this->roomid, $row, $field);
        $examid = $exam->get_id();
        $this->exams[$examid] = $exam;
        $this->seated += $exam->seated;
    }

    public function refresh_seated() {
        $seated = 0;
        foreach($this->exams as $exam) {
            $seated += $exam->seated;
        }
        $this->seated = $seated;
        return $this->seated;
    }

    public function set_additionals() {
        global $DB;
        $this->additionals = array();

        $sql = "SELECT ss.id AS ssid, ss.examid, ss.userid, c.shortname, c.fullname, e.*
                    FROM {examregistrar_session_seats} ss
                    JOIN {examregistrar_exams} e ON ss.examid = e.id
                    JOIN {course} c ON e.courseid = c.id
                    WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.roomid = :roomid AND ss.additional > 0
                    ORDER BY e.programme, c.shortname ";
        if($additionalexams = $DB->get_records_sql($sql, array('examsession'=>$this->session, 'bookedsite'=>$this->venue, 'roomid'=>$this->roomid))) {
            $users = array();
            $exams = array();
            $booked = array();
            foreach($additionalexams as $exam) {
                if(!isset($users[$exam->userid])) {
                    $users[$exam->userid] = 1;
                }
                if(!isset($exams[$exam->examid])) {
                    $booked[$exam->examid] = 1;
                    $exams[$exam->examid] = $exam;
                } else {
                    $booked[$exam->examid] += 1;
                }
            }

            foreach($exams as $eid => $additional) {
                $additional->seated = $booked[$additional->examid];
                $exam = new examregistrar_roomexam($this->session, $this->venue, $this->roomid, $additional, 'examid');
                $this->additionals[$eid] = $exam;
            }
            $this->additionalusers = count($users);
        }

        return $this->additionals;
    }

    public function lastallocated() {
        global $DB;
        $time = $DB->get_records_menu('examregistrar_session_seats', array('examsession'=>$this->session, 'bookedsite'=>$this->venue, 'roomid'=>$this->roomid),
                                                            'timemodified DESC', 'id, timemodified', 0, 1);
        if($time) {
            return reset($time);
        }
        return false;
    }
}

class examregistrar_allocatedexam extends examregistrar_exam implements renderable {
    /** @var int $session examsession ID for this allocation  */
    public $session;
    /** @var int $bookedsite ID for this allocation  */
    public $venue;
    /** @var int $seated number of students allocated to this room  */
    public $seated;
    /** @var array $users students allocated to this exam  */
    public $users = array();
    /** @var array $users students allocated to this exam  */
    public $bookednotallocated = array();

    public function __construct($session, $venue, $exam, $field='id') {
        $this->session = $session;
        $this->venue = $venue;
        $this->users = false;
        $this->bookednotallocated = false;
        parent::__construct($exam, $field);
        if(isset($exam->seated)) {
            $this->seated = $exam->seated;
        }
    }

    public function set_users($venue='') {
        global $DB;
        $this->users = array();

        $params = array('session'=>$this->session, 'exam'=>$this->examid);
        $where = '';
        if($venue) {
            $where = ' AND b.bookedsite = :venue ';
            $params['venue'] = $venue;
        }
        $fields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT  b.id AS bid, ss.*, ss.id AS sid, b.userid, b.bookedsite, u.id, u.username, u.idnumber, $fields
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
                JOIN {user} u ON b.userid = u.id
                LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
                WHERE b.examid = :exam AND b.booked = 1 $where
                ORDER BY u.lastname, u.firstname, u.idnumber ";

        $users = $DB->get_records_sql($sql, $params);
        $this->users = $users;
        $this->seated = count($users);
        return $this->users;
    }

    public function get_formatted_user_allocations($venue='') {
        global $DB;

        $params = array('session'=>$this->session, 'exam'=>$this->examid);
        $where = '';
        if($venue) {
            $where = ' AND b.bookedsite = :venue ';
            $params['venue'] = $venue;
        }
        $fields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT  b.id AS bid, ss.roomid, ss.additional, u.id, u.username, u.idnumber, $fields,
                        el.name AS venuename, el.idnumber AS venueidnumber,
                        el2.name AS roomname, el2.idnumber AS roomidnumber
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
                JOIN {examregistrar_locations} l ON l.id = b.bookedsite
                JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
                JOIN {user} u ON b.userid = u.id
                LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
                LEFT JOIN {examregistrar_locations} l2 ON l2.id = ss.roomid
                LEFT JOIN {examregistrar_elements} el2 ON l2.examregid = el2.examregid AND el2.type = 'locationitem' AND l2.location = el2.id
                WHERE b.examid = :exam AND b.booked = 1 $where
                ORDER BY u.lastname, u.firstname, u.idnumber ";

        $users = $DB->get_records_sql($sql, $params);
        return $users;
    }

    public function get_venue_bookings($venue='') {
        global $DB;

        $params = array('session'=>$this->session, 'exam'=>$this->examid);
        $where = '';
        if($venue) {
            $where = ' AND b.bookedsite = :venue ';
            $params['venue'] = $venue;
        }
        $sql = "SELECT  b.bookedsite, el.name AS venuename, COUNT(b.userid) AS booked, COUNT(ss.userid) AS allocated
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_locations} l ON l.id = b.bookedsite
                JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
                JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
                JOIN {user} u ON b.userid = u.id
                LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
                WHERE b.examid = :exam AND b.booked = 1 $where
                GROUP BY b.bookedsite
                ORDER BY el.name ";

        $venues = $DB->get_records_sql($sql, $params);
        return $venues;
    }

    public function get_room_allocations($venue = -1, $room = 0, $withresponses = false) {
        global $DB;

        if($venue < 0) {
            $venue = $this->venue;
        }

        $params = array('session'=>$this->session, 'exam'=>$this->examid);
        $where = '';
        if($venue) {
            $where = ' AND ss.bookedsite = :venue ';
            $params['venue'] = $venue;
        }
        if($room) {
            $where = ' AND ss.roomid = :room ';
            $params['room'] = $room;
        }
        
        $responses = '';
        $responsesjoin = '';
        if($withresponses) {
            $responses = ', r.id AS responseid, r.numfiles, r.showing, r.taken, r.status ';
            $responsesjoin = 'LEFT JOIN {examregistrar_responses} r ON r.examsession = ss.examsession AND r.examid = ss.examid AND r.roomid = ss.roomid ';
        }
        
        $sql = "SELECT  ss.roomid, el.name AS name, el.idnumber, ss. examid, COUNT(ss.userid) AS allocated $responses
                FROM {examregistrar_session_seats} ss
                JOIN {examregistrar_locations} l ON l.id = ss.roomid
                JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
                $responsesjoin
                WHERE ss.examid = :exam $where
                GROUP BY ss.roomid
                ORDER BY el.name ";

        $rooms = $DB->get_records_sql($sql, $params);
        return $rooms;
    }
    
    public function get_responses_status($room = 0, $venue = false) {
        global $DB;
        
        $rooms = array();
        
        if($room == 0) {
            $rooms = $this->get_room_allocations(0, 0, true);
        } elseif($venue) {
            $rooms = $this->get_room_allocations($room, 0, true);
        } else {
            $rooms = $this->get_room_allocations(-1, $room, true);
        }
    
        $max = 0;
        $min = 999;
    
        foreach($rooms as $rid => $room) {
            $status = isset($room->status) ? $room->status : 0; 
            $max = max($max, $status);
            $min = min($min, $status);
        }
        
        $status = ($max ==  $min) ? $max : EXAM_RESPONSES_WAITING;

        return $status;
    }
}



class examregistrar_exams_base implements renderable {
    /** @var int $courseid ID of the course the exam belongs to  */
    public $courseid;
    /** @var object $course object of the course the exam belongs to  */
    public $course;
    /** @var int $annuality ID for exams in this review  */
    public $annuality;
    /** @var int $periodid exam period ID for this review  */
    public $periodid;
    /** @var object $period exam period object for this review  */
    public $period;
    /** @var int $examreggistrar examgergistrar instance record */
    public $examregistrar;
    /** @var array $exams collection of exam objects existing for this courseid and period */
    public $exams;
    /** @var object $url moodle_url object for action icons and links */
    public $url;
    /** @var bool $single whether the course is unique or belong to a collection display   */
    public $single;

    public function __construct($examregistrar, $course, $period, $annuality, $baseurl, $single = false) {
        global $DB;
        if(!is_object($course)) {
            $course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
        }
        if(!is_object($period)) {
            $this->period = null;
            $this->periodid = 0;
            if($period > 0) {
                $period = $DB->get_record('examregistrar_periods', array('id'=>$period), '*', MUST_EXIST);
            }
        }
        $this->examregistrar = $examregistrar;
        $this->courseid = $course->id;
        $this->course = $course;
        $this->period = $period;
        if($period) {
            $this->periodid = $period->id;
        }
        $this->exams = array();
        $this->annuality = $annuality;
        $this->url = $baseurl;
        $this->single = $single;
    }

    protected function preload_exams() {
        global $DB;
        $params = array('courseid'=>$this->courseid);
        if($this->annuality) {
            $params['annuality'] = $this->annuality;
        }
        if($this->period) {
            $params['period'] = $this->period->id;
        }
        $params['visible'] = 1;
        if($exams = $DB->get_records('examregistrar_exams', $params, 'period ASC, examscope ASC, callnum ASC')) {
            $this->exams = $exams;
        }
        return $this->exams;
    }

    public function set_exams() {
        global $DB;

        $this->preload_exams();

        return $this->exams;
    }

}


class examregistrar_exams_course extends examregistrar_exams_base implements renderable {
    /** @var array $examfiles collection indexed by examid, only one (approved) examfile per exam  */
    public $examfiles;

    /** @var array $conflicts collection indexed by examsession, several sites in one session */
    public $conflicts;

    
    public function __construct($examregistrar, $course, $period, $annuality, $baseurl, $single = false) {
        parent::__construct($examregistrar, $course, $period, $annuality, $baseurl, $single);
        $this->examfiles = array();
        $this->conflicts = array();
    }

    public function get_approved_exam($exam) {
        global $DB;

        $message = '';
        $examfile = '';
        if($examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$exam->id, 'status'=>EXAM_STATUS_APPROVED),'timeapproved DESC, attempt DESC')) {
            if(count($examfiles) > 1) {
                // error, more than one attempt approved
                $message = get_string('error_manyapproved', 'examregistrar');
            }
            $examfile = reset($examfiles);
        }
        $this->examfiles[$exam->id] = $examfile;
        return $message;
    }

    public function get_exam_bookings($examid, $sort = '') {
        global $DB;
        $bookings = array();
        $allnames = get_all_user_name_fields(true, 'u');
        if(!$sort) {
            $sort = ' u.lastname ASC ';
        } else {
            $sort .= ' , u.lastname ASC ';
        }
        $sql = "SELECT b.id AS bid, b.userid, b.bookedsite, e.name AS sitename, e.idnumber AS siteidnumber, $allnames
                FROM {examregistrar_bookings} b
                JOIN {user} u ON b.userid = u.id
                JOIN {examregistrar_locations} l ON b.bookedsite = l.id
                LEFT JOIN {examregistrar_elements} e ON l.location = e.id AND e.type = 'locationitem'
                WHERE b.examid = :examid AND b.booked = 1
                ORDER BY $sort ";
        $bookings = $DB->get_records_sql($sql, array('examid'=>$examid));

        return $bookings;
    }

    public function set_exams() {
        global $DB, $USER;

        $this->preload_exams();

        foreach($this->exams as $eid => $exam) {
            $examsession = $DB->get_record('examregistrar_examsessions', array('id'=>$exam->examsession), 'id,examdate, duration, timeslot');
            if(!$examsession) {
                $examsession = new stdclass();
                $examsession->examdate = '';
                $examsession->duration= '';
                $examsession->timeslot = '';
            }
            $exam->examdate = $examsession->examdate;
            $exam->duration = $examsession->duration;
            $exam->timeslot = $examsession->timeslot;
            $exam->ownbook = $DB->get_field('examregistrar_bookings', 'bookedsite', array('examid'=>$exam->id, 'userid'=>$USER->id, 'booked'=>1));
            if($exam->ownbook) {
                $exam->ownroom = $DB->get_field('examregistrar_session_seats', 'roomid', array('examsession'=>$exam->examsession, 'examid'=>$exam->id, 'userid'=>$USER->id, 'bookedsite'=>$exam->ownbook));
            } else {
                $exam->ownroom = 0;
            }
            $exam->bookings = $DB->count_records('examregistrar_bookings', array('examid'=>$exam->id, 'booked'=>1));
            $this->exams[$eid] = $exam;
        }

        return $this->exams;
    }

    public function check_booked_exams() {
        global $DB, $USER;
        
        foreach($this->exams as $eid => $exam) {
            if($exam->ownbook) {
                if($othersinsession = $DB->get_records_menu('examregistrar_exams', array('examsession'=>$exam->examsession, 'visible'=>1), 'id,courseid')) {

                    list($insql, $params) = $DB->get_in_or_equal(array_keys($othersinsession), SQL_PARAMS_NAMED, 'sess'); 
                    $select = " examid $insql AND userid = :user AND booked = 1 AND bookedsite <> :bookedsite ";
                    $params['user'] = $USER->id;
                    $params['bookedsite'] = $exam->ownbook;
                    if($DB->record_exists_select('examregistrar_bookings', $select,  $params)) {
                        $this->conflicts[$exam->examsession] = 1;
                    }
                }
            }
        }
    }
}

class examregistrar_exam_attemptsreview implements renderable {
    /** @var int $course ID of course this exam belongs to   */
    public $courseid;
    /** @var int $examid ID of the exam that is reviewed  */
    public $examid;
    /** @var object $exam object of the exam these attemps belongs to  */
    public $exam;
    /** @var array $attempts collection of exam attempts for this exam */
    public $attempts;


    public function __construct($exam) {
        global $DB;
        if(!is_object($exam)) {
            $exam = $DB->get_record('examregistrar_exams', array('id'=>$exam), '*', MUST_EXIST);
        }
        $this->courseid = $exam->courseid;
        $this->examid = $exam->id;
        $this->exam = $exam;
        $this->attempts = array();
    }

    public function set_attempts() {
        global $DB;
        if(!$attempts = $DB->get_records('examregistrar_examfiles', array('examid'=>$this->examid), ' attempt ASC ')) {
            $attempts = array();
            $attempt = new stdClass;
            $attempt->id = 0;
            $attempt->examid = 0;
            $attempt->status = 0;
            $attempt->attempt = 0;
            $attempts[] = $attempt;
        }

        // unpack allowtools
        foreach($attempts as $attempt) {
            if(isset($attempt->allowedtools) && !empty($attempt->allowedtools)) {
                if($permissions = json_decode($attempt->allowedtools)) {
                    $attempt->allowedtools = get_object_vars($permissions);
                }
            }        
        }
        $this->attempts = $attempts;
        return $this->attempts;
    }

  
    // check exam file origin for special questions usage
    public static function warning_questions_used($examfile) {
        global $DB;

        $validquestions = get_config('quiz_makeexam', 'validquestions');
        if($validquestions) {
            $validquestions = explode(',', $validquestions);
        } else {
            return false;
        }

        if(!$validquestions) {
            $validquestions = array();
        }

        $warning = false;
        if($qme_attempt = $DB->get_record('quiz_makeexam_attempts', array('examid' =>$examfile->examid, 'examfileid'=>$examfile->id, 'status'=>1))) {
            $qids = explode(',', $qme_attempt->questions);
            if($usedquestions = $DB->get_records_list('question', 'id', $qids, '', 'id, name, qtype')) {
                foreach($usedquestions as $question) {
                    if(!in_array($question->qtype, $validquestions)) {
                        $warning = true;
                        break;
                    }
                }
            }
        }

        return $warning;
    }
        
    
    // checks if exists approved or sent
    public function can_send() {
        $cansubmit = false;
        $this->set_attempts();
        if($this->attempts) {
            $cansubmit = true;
            foreach($this->attempts as $attempt) {
                if($attempt->status == 1 || $attempt->status == 1) {
                    $cansubmit = false;
                    return $cansubmit;
                }
            }
        }

        return $cansubmit;
    }

    // checks tracker issue state associated to this exam
    public function get_review($examfile) {
        global $CFG, $DB;    
        include_once($CFG->dirroot.'/mod/tracker/lib.php');
        include_once($CFG->dirroot.'/mod/tracker/locallib.php');
        global $STATUSCODES;
        $review = '';
        if($examfile->reviewid) {
            if($issue = $DB->get_record('tracker_issue', array('id'=>$examfile->reviewid))) {
                $moduleid = $DB->get_field('modules', 'id', array('name'=>'tracker'), MUST_EXIST);
                $courseid = $DB->get_field('tracker', 'course', array('id'=>$issue->trackerid), MUST_EXIST);
                $lang = $DB->get_field('tracker_translation', 'forcedlang', array('trackerid'=>$issue->trackerid));
                $tcm = $DB->get_record('course_modules', array('course'=>$courseid, 'module'=>$moduleid, 'instance'=>$issue->trackerid), '*', MUST_EXIST);
                $status = $STATUSCODES[$issue->status];

                $statusmsg = html_writer::tag('span', '&nbsp;'.tracker_getstring($status, 'tracker', null, $lang).'&nbsp;', array('class'=>'status_'.$status));

                $trackerurl = new moodle_url('/mod/tracker/view.php', array('id'=>$tcm->id, 'issueid'=>$examfile->reviewid, 'view'=>'view', 'screen'=>'viewanissue'));
                $review = html_writer::link($trackerurl, $statusmsg);
            } else {
                $review = html_writer::span(get_string('missingreview', 'examregistrar'), ' error ');
            }
        }

        return $review;
    }

    
    // checks makeexamtable for attempt associated to this exam
    public function get_makeexam_attempt($examfile) {
        global $CFG, $DB;    
        //include_once($CFG->dirroot.'/mod/quiz/report/makeexam/report.php');
        
        $params = array('examid' => $examfile->examid, 'examfileid' => $examfile->id, 'attempt' => $examfile->attempt, 'quiz' => 'quiz');
        
        $sql = "SELECT a.id AS review, cm.id AS cm
                FROM {quiz_makeexam_attempts} a 
                JOIN {course_modules} cm ON cm.instance = a.quizid AND cm.course = a.course AND cm.module = (SELECT id FROM {modules} WHERE name = :quiz) 
                WHERE a.examid = :examid AND a.examfileid = :examfileid AND a.attempt = :attempt  ";
    
        return $mkattemp = $DB->get_record_sql($sql, $params);
    }
    
    // gets exam date from session
    public function get_examdate($exam) {
        global $DB;
        return $DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$exam->examsession, 'period'=>$exam->period));
    }

    // check exam file origin for special questions usage
//     public function warning_questions_used($examfile) {
//         global $DB;
//
//         $validquestions = get_config('quiz_makeexam', 'validquestions');
//         if($validquestions) {
//             $validquestions = explode(',', $validquestions);
//         } else {
//             return false;
//         }
//
//         if(!$validquestions) {
//             $validquestions = array();
//         }
//
//         $warning = false;
//         if($qme_attempt = $DB->get_record('quiz_makeexam_attempts', array('examid' =>$examfile->examid, 'examfileid'=>$examfile->id, 'status'=>1))) {
//             $qids = explode(',', $qme_attempt->questions);
//             if($usedquestions = $DB->get_records_list('question', 'id', $qids, '', 'id, name, qtype')) {
//                 foreach($usedquestions as $question) {
//                     if(!in_array($question->qtype, $validquestions)) {
//                         $warning = true;
//                         break;
//                     }
//                 }
//             }
//         }
//
//         return $warning;
//     }
}


class examregistrar_exams_coursereview extends examregistrar_exams_base implements renderable {

    public function set_exams() {
        global $DB;

        $this->preload_exams();
        foreach($this->exams as $id => $exam) {
            $this->exams[$id] = new examregistrar_exam_attemptsreview($exam);
        }

        return $this->exams;
    }
}
