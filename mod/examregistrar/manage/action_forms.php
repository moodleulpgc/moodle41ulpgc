<?php

/**
 * This file contains form classes & form definios for Examregistrar manage interface
 *
 * @package   mod_examregistrar
 * @copyright 2014 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
//require_once($CFG->dirroot."/mod/examregistrar/lib.php");


abstract class examregistrar_actionform_base extends moodleform {

    function add_standard_hidden_fields() {
        $mform =& $this->_form;
    
        $action = $this->_customdata['action'];
        $edit = $this->_customdata['edit'];
        $id = $this->_customdata['id']; // course_module id
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);
    
        if($this->_customdata['venue'] && !$mform->elementExists('venue')) {
            $mform->addElement('hidden', 'venue', $this->_customdata['venue']);
            $mform->setType('venue', PARAM_INT);    
        } 
        
        if($this->_customdata['session'] && !$mform->elementExists('session')) {
            $mform->addElement('hidden', 'session', $this->_customdata['session']);
            $mform->setType('session', PARAM_INT);    
        } 
    
        $mform->addElement('hidden', 'edit', $edit);
        $mform->setType('edit', PARAM_ALPHANUMEXT);
        
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        
        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);
        
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);    
    }

    function get_venue_menu($choose) {
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);    
        $venueelement = examregistrar_get_venue_element($examreg);
        return examregistrar_get_referenced_namesmenu($examreg, 'locations', 'locationitem', $exreg, $choose, '', array('locationtype'=>$venueelement));
    }
}



class examregistrar_configparams_actionform extends examregistrar_actionform_base {

    function definition() {

        $mform =& $this->_form;

        $settings = get_config('examregistrar');
        
        $attributes=array('size'=>'3');
    
        $mform->addElement('header', 'headerdeadlines', get_string('headerdeadlines', 'examregistrar'));

        $mform->addElement('text', 'config_selectdays', get_string('selectdays', 'examregistrar'), $attributes);
        $mform->setType('config_selectdays', PARAM_INT);
        $mform->setDefault('config_selectdays', $settings->selectdays);
        $mform->addHelpButton('config_selectdays', 'selectdays', 'examregistrar');

        $mform->addElement('text', 'config_cutoffdays', get_string('cutoffdays', 'examregistrar'), $attributes);
        $mform->setType('config_cutoffdays', PARAM_INT);
        $mform->setDefault('config_cutoffdays', $settings->cutoffdays);
        $mform->addHelpButton('config_cutoffdays', 'cutoffdays', 'examregistrar');

        $mform->addElement('text', 'config_extradays', get_string('extradays', 'examregistrar'), $attributes);
        $mform->setType('config_extradays', PARAM_INT);
        $mform->setDefault('config_extradays', $settings->extradays);
        $mform->addHelpButton('config_extradays', 'extradays', 'examregistrar');
        
        $mform->addElement('text', 'config_lockdays', get_string('lockdays', 'examregistrar'), $attributes);
        $mform->setType('config_lockdays', PARAM_INT);
        $mform->setDefault('config_lockdays', $settings->lockdays);
        $mform->addHelpButton('config_lockdays', 'lockdays', 'examregistrar');
        
        $mform->addElement('text', 'config_approvalcutoff', get_string('approvalcutoff', 'examregistrar'), $attributes);
        $mform->setType('config_approvalcutoff', PARAM_INT);
        $mform->setDefault('config_approvalcutoff', $settings->approvalcutoff);
        $mform->addHelpButton('config_approvalcutoff', 'approvalcutoff', 'examregistrar');
        
        $mform->addElement('text', 'config_printdays', get_string('printdays', 'examregistrar'), $attributes);
        $mform->setType('config_printdays', PARAM_INT);
        $mform->setDefault('config_printdays', $settings->printdays);
        $mform->addHelpButton('config_printdays', 'printdays', 'examregistrar');
        
        $mform->setExpanded('headerdeadlines', true);

        // // TODO    TODO    TODO //
        //This  should be moved to plugin config or work in collaboration with plugin config
        $mform->addElement('header', 'headeronlinexams', get_string('headeronlinexams', 'examregistrar'));
        
        $venuemenu = $this->get_venue_menu('none');
        $mform->addElement('select', 'config_deliverysite', get_string('deliverysite', 'examregistrar'), $venuemenu);
        $mform->setDefault('config_deliverysite', '');
        $mform->addHelpButton('config_deliverysite', 'deliverysite', 'examregistrar');

        $mform->addElement('text', 'config_assignexamprefix', get_string('assignexamprefix', 'examregistrar'), ['size'=>'8']);
        $mform->setType('config_assignexamprefix', PARAM_ALPHANUMEXT);
        $mform->setDefault('config_assignexamprefix', $settings->assignexamprefix);
        $mform->addHelpButton('config_assignexamprefix', 'assignexamprefix', 'examregistrar');        

        $mform->addElement('text', 'config_quizexamprefix', get_string('quizexamprefix', 'examregistrar'), ['size'=>'8']);
        $mform->setType('config_quizexamprefix', PARAM_ALPHANUMEXT);
        $mform->setDefault('config_quizexamprefix', $settings->quizexamprefix);
        $mform->addHelpButton('config_quizexamprefix', 'quizexamprefix', 'examregistrar');        
        
        $mform->addElement('duration', 'config_quizexamafter', get_string('quizexamafter', 'examregistrar'));
        $mform->setDefault('config_quizexamafter', $settings->quizexamafter);
        $mform->setType('config_quizexamafter', PARAM_INT);
        $mform->addHelpButton('config_quizexamafter', 'quizexamafter', 'examregistrar');
        
        $mform->addElement('advcheckbox', 'config_insertcontrolq', get_string('insertcontrolq', 'examregistrar'));
        $mform->setDefault('config_insertcontrolq', $settings->insertcontrolq);
        $mform->setType('config_insertcontrolq', PARAM_INT);
        $mform->addHelpButton('config_insertcontrolq', 'insertcontrolq', 'examregistrar');
        
        $mform->addElement('text', 'config_controlquestion', get_string('controlquestion', 'examregistrar'), $attributes);
        $mform->setType('config_controlquestion', PARAM_INT);
        $mform->setDefault('config_controlquestion', $settings->controlquestion);
        $mform->addHelpButton('config_controlquestion', 'controlquestion', 'examregistrar');
        
        $mform->addElement('text', 'config_optionsinstance', get_string('optionsinstance', 'examregistrar'), $attributes);
        $mform->setType('config_optionsinstance', PARAM_INT);
        $mform->setDefault('config_optionsinstance', $settings->optionsinstance);
        $mform->addHelpButton('config_optionsinstance', 'optionsinstance', 'examregistrar');
        
        $mform->addElement('text', 'config_quizoptions', get_string('quizoptions', 'examregistrar'), array('size'=>80));
        $mform->setType('config_quizoptions', PARAM_TEXT);
        $mform->setDefault('config_quizoptions', $settings->optionsinstance);
        $mform->addHelpButton('config_quizoptions', 'quizoptions', 'examregistrar');
        
        $mform->addElement('header', 'headerallocation', get_string('headerallocation', 'examregistrar'));
        
        $categories =  core_course_category::make_categories_list('', 0, ' / ');
        $select = $mform->addElement('select', 'config_staffcats', get_string('staffcategories', 'examregistrar'), $categories);
        $select->setMultiple(true);
        $select->setSelected(explode(',', $settings->staffcats));
        $mform->addHelpButton('config_staffcats', 'staffcategories', 'examregistrar');
      
        
        $mform->addElement('advcheckbox', 'config_excludecourses', get_string('excludecourses', 'examregistrar'), '  ');
        $mform->setDefault('config_excludecourses', $settings->excludecourses);
        $mform->setType('config_excludecourses', PARAM_INT);
        $mform->addHelpButton('config_excludecourses', 'excludecourses', 'examregistrar');
        
        $attributes=array('size'=>'8');
      
        $mform->addElement('text', 'config_venuelocationtype', get_string('venuelocationtype', 'examregistrar'), $attributes);
        $mform->setType('config_venuelocationtype', PARAM_ALPHANUMEXT);
        $mform->setDefault('config_venuelocationtype', $settings->venuelocationtype);
        $mform->addHelpButton('config_venuelocationtype', 'venuelocationtype', 'examregistrar');        
        
        $mform->addElement('text', 'config_defaultrole', get_string('defaultrole', 'examregistrar'), $attributes);
        $mform->setType('config_defaultrole', PARAM_ALPHANUMEXT);
        $mform->setDefault('config_defaultrole', $settings->defaultrole);
        $mform->addHelpButton('config_defaultrole', 'defaultrole', 'examregistrar');            
        
        $mform->setExpanded('headerallocation', false);

        
        $mform->addElement('header', 'headerfilesuffix', get_string('headerfilesuffix', 'examregistrar'));
        
        $mform->addElement('text', 'config_extanswers', get_string('extensionanswers', 'examregistrar'), $attributes);
        $mform->setType('config_extanswers', PARAM_FILE);
        $mform->setDefault('config_extanswers', $settings->extanswers);
        $mform->addHelpButton('config_extanswers', 'extensionanswers', 'examregistrar');            
        
        $mform->addElement('text', 'config_extkey', get_string('extensionkey', 'examregistrar'), $attributes);
        $mform->setType('config_extkey', PARAM_FILE);
        $mform->setDefault('config_extkey', $settings->extkey);
        $mform->addHelpButton('config_extkey', 'extensionkey', 'examregistrar');            

        $mform->addElement('text', 'config_extresponses', get_string('extensionresponses', 'examregistrar'), $attributes);
        $mform->setType('config_extresponses', PARAM_FILE);
        $mform->setDefault('config_extresponses', $settings->extresponses);
        $mform->addHelpButton('config_extresponses', 'extensionresponses', 'examregistrar');           
        
        $mform->setExpanded('headerfilesuffix', false);

        
        $mform->addElement('header', 'headerprinting', get_string('headerprinting', 'examregistrar'));
        
        $mform->addElement('advcheckbox', 'config_pdfwithteachers', get_string('pdfwithteachers', 'examregistrar'), ' &nbsp; ');
        $mform->setDefault('config_pdfwithteachers', $settings->pdfwithteachers);
        $mform->setType('config_pdfwithteachers', PARAM_INT);
        $mform->addHelpButton('config_pdfwithteachers', 'pdfwithteachers', 'examregistrar');

        $mform->addElement('advcheckbox', 'config_pdfaddexamcopy', get_string('pdfaddexamcopy', 'examregistrar'), ' ');
        $mform->setDefault('config_pdfaddexamcopy', $settings->pdfaddexamcopy);
        $mform->setType('config_pdfaddexamcopy', PARAM_INT);
        $mform->addHelpButton('config_pdfaddexamcopy', 'pdfaddexamcopy', 'examregistrar');
        
        $mform->setExpanded('headerprinting', false);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));    
    }
}


class examregistrar_sessionrooms_actionform extends examregistrar_actionform_base {

    function definition() {
        global $COURSE, $DB;

        $mform =& $this->_form;
        
        $session = $this->_customdata['session'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        $mform->addElement('header', 'assignsessionrooms', get_string('sessionroomssettings', 'examregistrar'));

        $menu = examregistrar_get_referenced_namesmenu($examreg, 'examsessions', 'examsessionitem',1, 'choose', '', [], 't.examdate ASC');
        $mform->addElement('select', 'examsession', get_string('examsessionitem', 'examregistrar'), $menu);
        $mform->addHelpButton('examsession', 'examsessionitem', 'examregistrar');
        $mform->addRule('examsession', null, 'required', null, 'client');
        $mform->setDefault('examsession', $session);
        if($session) {
            $mform->freeze('examsession');
        }

        $parentmenu = $this->get_venue_menu('none');
        $mform->addElement('select', 'bookedsite', get_string('venue', 'examregistrar'), $parentmenu);
        $mform->addHelpButton('bookedsite', 'venue', 'examregistrar');
        $mform->addRule('bookedsite', null, 'required', null, 'client');

        $sql = "SELECT l.id, el.idnumber, el.name, l.locationtype, l.seats, l.visible, l.parent
                FROM {examregistrar_locations} l
                JOIN {examregistrar_elements} el ON l.examregid =  el.examregid AND el.type = 'locationitem' AND l.location = el.id
                WHERE l.examregid = ? AND l.seats > ? AND l.visible <> ? ";
        $sort = " ORDER BY el.name ";

        $totalrooms = $DB->get_records_sql($sql.$sort, array($exreg, 0, 0));

        $roomsmenu = array();

        foreach($totalrooms as $key => $room) {
            $roomsmenu[$key] = $room->name.' ('.$room->idnumber.')  '.$room->seats.'  ['.$room->locationtype.']';
        }

        $session = 1;
        $assignedrooms = array();
        if($session) {
            $assignedrooms = $DB->get_fieldset_select('examregistrar_session_rooms', 'roomid', 'examsession = ? AND available <> ?', array($exreg, 0));
        }

        $select = $mform->addElement('select', 'assignedrooms', get_string('assignedrooms', 'examregistrar'), $roomsmenu, 'size="12"' );
        $select->setMultiple(true);
        $select->setSelected($assignedrooms);
        //$mform->addHelpButton('assignedrooms', 'assignedrooms', 'examregistrar');

        $mform->addElement('static', 'description', '', get_string('assignedroomsclearmessage', 'examregistrar'));

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}

class examregistrar_stafffromexam_actionform extends examregistrar_actionform_base {

    function definition() {
        global $COURSE, $DB;

        $mform =& $this->_form;

        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);
        $session = $this->_customdata['session'];
        $bookedsite = $this->_customdata['venue'];

        $mform->addElement('header', 'assignsessionrooms', get_string('sessionroomssettings', 'examregistrar'));

        $menu = $this->get_venue_menu('choose');
        $mform->addElement('select', 'venue', get_string('locationitem', 'examregistrar'), $menu);
        $mform->addHelpButton('venue', 'locationitem', 'examregistrar');
        $mform->addRule('venue', null, 'required', null, 'client');
        $mform->setDefault('venue', $bookedsite);
        if($bookedsite) {
            $mform->freeze('venue');
        }

        $menu = examregistrar_get_referenced_namesmenu($examreg, 'examsessions', 'examsessionitem', $exreg, 'choose', '', [], 't.examdate ASC');
        $select = $mform->addElement('select', 'examsessions', get_string('examsessionitem', 'examregistrar'), $menu);
        $select->setMultiple(true);
        $mform->addHelpButton('examsessions', 'examsessionitem', 'examregistrar');
        $mform->addRule('examsessions', null, 'required', null, 'client');
        $select->setSelected(array($session));

        $menu = examregistrar_elements_getvaluesmenu($examreg, 'roleitem', $exreg);
        $mform->addElement('select', 'role', get_string('roleitem', 'examregistrar'), $menu);
        $mform->addHelpButton('role', 'roleitem', 'examregistrar');
        $mform->addRule('role', null, 'required', null, 'client');

        $menu = array(0=>get_string('addstaffer', 'examregistrar'), '1'=>get_string('removestaffer', 'examregistrar'));
        $mform->addElement('select', 'remove', get_string('action'), $menu);
        $mform->addHelpButton('remove', 'addstaffer', 'examregistrar');
        $mform->setDefault('remove', 0);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}



class examregistrar_seatstudent_actionform extends examregistrar_actionform_base {

    function definition() {
        global $COURSE, $DB, $PAGE;

        $mform =& $this->_form;
        $session = $this->_customdata['session'];
        $bookedsite = $this->_customdata['venue'];

        $mform->addElement('header', 'assignsessionrooms', get_string('sessionroomssettings', 'examregistrar'));

        $userfieldsapi = \core_user\fields::for_name();
        $names = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $sql = "SELECT ss.userid, u.id, u.idnumber, $names
                FROM {examregistrar_session_seats} ss
                JOIN {user} u ON ss.userid = u.id
                WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite
                GROUP BY ss.userid
                ORDER BY u.lastname ASC, u.firstname ASC, u.idnumber ASC
                ";
        $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);
        if($users = $DB->get_records_sql($sql, $params)) {
            foreach($users as $uid => $user) {
                $users[$uid] = fullname($user, false, 'lastname');
            }
        }
        $mform->addElement('select', 'userid', get_string('student', 'examregistrar'), $users);
        $mform->addHelpButton('userid', 'locationitem', 'examregistrar');
        $mform->addRule('userid', null, 'required', null, 'client');

        $rooms = examregistrar_get_session_rooms($session, $bookedsite, '', false, 1);
        $output = $PAGE->get_renderer('mod_examregistrar');
        foreach($rooms as $key => $room) {
            $rooms[$key] = $output->formatted_name($room->name, $room->idnumber);
        }
        $mform->addElement('select', 'room', get_string('locationitem', 'examregistrar'), $rooms);
        $mform->addHelpButton('room', 'locationitem', 'examregistrar');
        $mform->addRule('room', null, 'required', null, 'client');

        $mform->addElement('hidden', 'examsession', $session);
        $mform->setType('examsession', PARAM_INT);
        $mform->addElement('hidden', 'venue', $bookedsite);
        $mform->setType('venue', PARAM_INT);
        
        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_addextracall_actionform extends examregistrar_actionform_base {

    function add_booking_fields($courseid) {
        $mform =& $this->_form;    
        $bookedsite = $this->_customdata['venue'];
    
        $coursecontext = context_course::instance($courseid);
        $userfieldsapi = \core_user\fields::for_name();
        $names = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        if($users = get_enrolled_users($coursecontext, 'mod/examregistrar:book', 0, 'u.id, u.idnumber, '.$names, ' u.lastname ASC ')){
            foreach($users as $uid => $user) {
                $users[$uid] = fullname($user, false, 'lastname');
            }
        }
        //$users = array(0=>get_string('choose')) + $users;

        $select = $mform->addElement('select', 'userids', get_string('student', 'examregistrar'), $users);
        $select->setMultiple(true);
        //$mform->addHelpButton('userid', 'student', 'examregistrar');

        $venuemenu = $this->get_venue_menu('choose');
        $mform->addElement('select', 'bookedsite', get_string('venue', 'examregistrar'), $venuemenu);
        $mform->addHelpButton('bookedsite', 'venue', 'examregistrar');
        $mform->setDefault('bookedsite', $bookedsite);
        $mform->addRule('bookedsite', null, 'required', null, 'client');

        $menu = array(0=>get_string('notbooked', 'examregistrar'), 1=>get_string('booked', 'examregistrar'));
        $mform->addElement('select', 'booked', get_string('booking', 'examregistrar'), $menu);
        $mform->addHelpButton('booked', 'booking', 'examregistrar');
        $mform->setDefault('booked', 1);

        $mform->addElement('selectyesno', 'userexceptions', get_string('adduserexceptions', 'examregistrar')); 
        $mform->setDefault('userexceptions', 1);
        $mform->disabledIf('userexceptions', 'booked', 'neq', 1);
    }

    function add_delivery_fields($courseid, $deliverynum = 1) {    
        $mform =& $this->_form;    
        
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);    
        
        if(!$deliverynum) {
            $deliverynum = 1;
        }        
        
        $deliverysite = examregistrar_get_instance_config($exreg, 'deliverysite');
        $repeatedoptions = array();
        $repeated = examregistrar_get_per_delivery_fields($courseid, $mform, $repeatedoptions, true, $deliverysite);
        $this->repeat_elements($repeated, $deliverynum, $repeatedoptions, 
                                'deliver_repeats', 'deliver_add_fields', 1, 
                                get_string('adddelivery' , 'examregistrar'), false);        
        
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);        
    }

    function get_exam($examid) {
        global $DB;
    
        $sql =  "SELECT e.id AS examid, e.programme, c.shortname, c.fullname, e.courseid, e.examsession,
                    ea.name AS annuality, ep.name AS period, es.name AS examscope, esn.name AS sessionname, s.examdate
                FROM {examregistrar_exams} e
                    JOIN {course} c ON c.id = e.courseid
                    JOIN {examregistrar_elements} ea ON e.examregid =  ea.examregid AND ea.type = 'annualityitem' AND e.annuality = ea.id
                    JOIN {examregistrar_periods} p ON e.period = p.id AND e.examregid =  p.examregid
                    JOIN {examregistrar_elements} ep ON p.examregid =  ep.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                    JOIN {examregistrar_elements} es ON e.examregid =  es.examregid AND es.type = 'scopeitem' AND e.examscope = es.id
                    JOIN {examregistrar_examsessions} s ON e.examregid =  s.examregid AND e.examsession = s.id
                    JOIN {examregistrar_elements} esn ON s.examregid =  esn.examregid AND esn.type = 'examsessionitem' AND s.examsession = esn.id
                WHERE e.id = :id ";
    
        return $DB->get_record_sql($sql, array('id' => $examid));
    }
    
    function definition() {
        $mform =& $this->_form;
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);    

        $examid = $this->_customdata['exam'];

        $mform->addElement('header', 'addextracall', get_string('addextracall', 'examregistrar'));

        if($exam = $this->get_exam($examid)) {
            $mform->addElement('static', 'examname', get_string('exam', 'examregistrar'), $exam->shortname.' - '.$exam->fullname);
            $mform->addElement('static', 'programme', get_string('programme', 'examregistrar'), $exam->programme);
            $mform->addElement('static', 'annuality', get_string('annuality', 'examregistrar'), $exam->annuality);
            $mform->addElement('static', 'period', get_string('perioditem', 'examregistrar'), $exam->period);
            $mform->addElement('static', 'examscope', get_string('scopeitem', 'examregistrar'), $exam->examscope);
            $mform->addElement('static', 'examdate', get_string('examsessionitem', 'examregistrar'), $exam->sessionname.', '.userdate($exam->examdate, get_string('strftimedaydate')));
        }

        $mform->addElement('hidden', 'courseid', $exam->courseid);
        $mform->setType('courseid', PARAM_INT);
        
        $menu = examregistrar_get_referenced_namesmenu($examreg, 'examsessions', 'examsessionitem', $exreg, 'choose', '', [], 't.examdate ASC');
        $mform->addElement('select', 'examsession', get_string('examsessionitem', 'examregistrar'), $menu);
        $mform->addHelpButton('examsession', 'examsessionitem', 'examregistrar');
        $mform->addRule('examsession', null, 'required', null, 'client');
        $mform->addRule('examsession', null, 'nonzero', null, 'client');
        $mform->setDefault('examsession', $exam->examsession);
        
        $this->add_booking_fields($exam->courseid);
        
        $this->add_delivery_fields($exam->courseid);
        
        $mform->addElement('hidden', 'exam', $examid);
        $mform->setType('exam', PARAM_INT);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_addextrasessioncall_actionform extends examregistrar_addextracall_actionform {

    /**
     * Get exam & delivery mode defaults
     * @param int $item examid
     * @return array of exam, deliverymode.
     */
    protected function get_exam_delivery($item) {
        global $DB;
        $exam = false;
        $deliverynum = 1;
        if($item > 0) {
            $sql = "SELECT e.*, c.shortname, c.fullname, c.category
                    FROM {examregistrar_exams} e
                    JOIN {course} c ON e.courseid = c.id
                    WHERE e.id = :id ";
            $exam = $DB->get_record_sql($sql, array('id'=>$item), MUST_EXIST);
        }        
        if($exam) {
            $deliverynum = $DB->count_records('examregistrar_examdelivery', array('examid' => $exam->id));
        }
        return [$exam, $deliverynum];
    }

    function get_exam($courseid) {
        global $DB;
        
        $sql =  "SELECT e.id AS examid, e.programme, e.courseid, e.examsession, e.period, e.annuality,
                    ea.name AS annualityname, ep.name AS periodname, ep.idnumber AS periodidnumber,
                    es.name AS examscope, esn.name AS sessionname, esn.idnumber AS sessionidnumber, s.examdate 
                FROM {examregistrar_exams} e
                    JOIN {examregistrar_elements} ea ON e.examregid =  ea.examregid AND ea.type = 'annualityitem' AND e.annuality = ea.id
                    JOIN {examregistrar_periods} p ON e.period = p.id AND e.examregid =  p.examregid
                    JOIN {examregistrar_elements} ep ON p.examregid =  ep.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                    JOIN {examregistrar_elements} es ON e.examregid =  es.examregid AND es.type = 'scopeitem' AND e.examscope = es.id
                    JOIN {examregistrar_examsessions} s ON e.examregid =  s.examregid AND e.examsession = s.id
                    JOIN {examregistrar_elements} esn ON s.examregid =  esn.examregid AND esn.type = 'examsessionitem' AND s.examsession = esn.id
                WHERE e.courseid = :id AND e.callnum > 0 AND e.visible > 0
                ORDER BY s.examdate ASC" ;
        return $DB->get_records_sql($sql, array('id' => $courseid));
    }
    

    function definition() {
        global $DB;

        $mform =& $this->_form;
        $tab = $this->_customdata['tab'];
        $session = $this->_customdata['session'];
        $short = $this->_customdata['shortname'];

        $mform->addElement('header', 'addextracall', get_string('addextracall', 'examregistrar'));

        $course = $DB->get_record('course', array('shortname'=>$short), 'id, fullname, shortname, idnumber', MUST_EXIST);

        $sql = "SELECT  s.id, s.examregid, s.examsession, s.period, s.examdate, s.timeslot, s.duration, 
                        es.name AS sessionname, es.idnumber AS sessionidnumber, ep.name AS periodname, ep.idnumber AS periodidnumber
                    FROM {examregistrar_examsessions} s
                    JOIN {examregistrar_elements} es ON s.examregid =  es.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
                    JOIN {examregistrar_periods} p ON s.period = p.id
                    JOIN {examregistrar_elements} ep ON p.examregid =  ep.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                WHERE s.id = :id AND s.visible > 0 ";
        $sessiondata = $DB->get_record_sql($sql, array('id'=>$session));

        $exams = $this->get_exam($course->id);
        $exam = reset($exams);
        
        $mform->addElement('static', 'examname_st', get_string('exam', 'examregistrar'), $course->shortname.' - '.$course->fullname);
        $mform->addElement('static', 'programme_st', get_string('programme', 'examregistrar'), $exam->programme);
        $mform->addElement('static', 'annuality_st', get_string('annuality', 'examregistrar'), $exam->annualityname);
        $mform->addElement('static', 'session_st', get_string('session', 'examregistrar'), $sessiondata->periodidnumber.' - '.$sessiondata->sessionidnumber.', '.userdate($sessiondata->examdate, get_string('strftimedaydate')));

        $sql = "FROM {examregistrar_exams}
                        WHERE examregid = ? AND annuality = ? AND programme = ?
                                AND courseid = ? AND period = ? AND examsession = ? ";
                $params = array($sessiondata->examregid, $exam->annuality,
                                $exam->programme, $exam->courseid,
                                $sessiondata->period, $sessiondata->id);
                // first check if already exist an extracall for this session, and use it
        $extraexamfile = '';
        if($extraexamid = $DB->get_field_sql('SELECT id '.$sql.' AND callnum < 0 ', $params)) {
            $extraexamfile = $DB->get_record('examregistrar_examfiles', array('examid'=>$extraexamid, 'status'=>EXAM_STATUS_APPROVED));
        }

        $menu = array(''=>get_string('choose'));
        $examid = '';
        foreach($exams as $eid => $exam) {
            $menu[$eid] = $exam->periodidnumber.' - '. $exam->sessionidnumber.', '.userdate($exam->examdate, get_string('strftimedaydate'));
            if(!$examid && ($exam->period == $sessiondata->period)) {
                $examid = $eid;
            }
        }

        $mform->addElement('select', 'exam', get_string('examsessionitem', 'examregistrar'), $menu);
        $mform->addHelpButton('exam', 'examsessionitem', 'examregistrar');
        $mform->addRule('exam', null, 'required', null, 'client');
        $mform->addRule('exam', null, 'nonzero', null, 'client');
        $mform->setDefault('exam', $examid);

        $sql = "SELECT ef.id, ef.idnumber
                FROM {examregistrar_examfiles} ef
                JOIN {examregistrar_exams} e ON ef.examid = e.id
                WHERE e.courseid = :courseid AND status = :status AND e.callnum > 0
                ORDER BY ef.idnumber
                ";
        $menu = $DB->get_records_sql_menu($sql, array('courseid'=>$course->id, 'status'=>EXAM_STATUS_APPROVED));

        $default = !$extraexamfile ? '' : get_string('specialexamfileexists', 'examregistrar');
        $mform->addElement('advcheckbox', 'generateexamfile', get_string('generateextracallef', 'examregistrar'), $default);
        $mform->setType('generateexamfile', PARAM_INT);
        $default = $extraexamfile ? 0 : count($menu);
        $mform->setDefault('generateexamfile', $default);

        $mform->addElement('select', 'examfile', get_string('examfile', 'examregistrar'), $menu);
        $mform->addHelpButton('examfile', 'examsessionitem', 'examregistrar');
        $mform->disabledIf('examfile', 'generateexamfile', 'notchecked');

        $this->add_booking_fields($exam->courseid);        
        
        // delivery 
        $deliverynum = 0;
        if($extraexamid) {
            list($extraexam, $deliverynum) = $this->get_exam_delivery($extraexamid);
        }
        
        $this->add_delivery_fields($exam->courseid, $deliverynum);                
        
        $after = examregistrar_get_instance_config($sessiondata->examregid, 'quizexamafter'); 
        foreach(range(0, $deliverynum) as $repeat) {
            $start = $sessiondata->examdate + $sessiondata->timeslot * 60*60;
            $mform->setDefault("timeopen[$repeat]", $start); 
            $mform->setDefault("timeclose[$repeat]", $start + $sessiondata->duration + $after);
            $mform->setDefault("timelimit[$repeat]", $sessiondata->duration);
        }
        
        
        $mform->addElement('hidden', 'examsession', $session);
        $mform->setType('examsession', PARAM_INT);
        
        $mform->addElement('hidden', 'examshort', $short);
        $mform->setType('examshort', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'tab', $tab);
        $mform->setType('tab', PARAM_ALPHANUMEXT);
        
        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_roomprintoptions_actionform extends examregistrar_actionform_base {

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'roomprintoptions', get_string('roomprintoptions', 'examregistrar'));

        $mform->addElement('static', 'description', '', get_string('printingoptionsmessasge', 'examregistrar'));

        $mform->addElement('text', 'page_header', get_string('printingheader', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_header',PARAM_RAW);
        $mform->addHelpButton('page_header', 'printingheader', 'examregistrar');

        $mform->addElement('selectyesno', 'header_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('header_visible', 1);

        $mform->addElement('editor', 'page_roomtitle', get_string('printingroomtitle', 'examregistrar'));
        $mform->setType('page_roomtitle',PARAM_RAW);
        $mform->addHelpButton('page_roomtitle', 'printingroomtitle', 'examregistrar');

        $mform->addElement('selectyesno', 'roomtitle_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('roomtitle_visible', 1);

        $mform->addElement('editor', 'page_examtitle', get_string('printingexamtitle', 'examregistrar'));
        $mform->setType('page_examtitle',PARAM_RAW);
        $mform->addHelpButton('page_examtitle', 'printingexamtitle', 'examregistrar');

        $mform->addElement('selectyesno', 'examtitle_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('examtitle_visible', 1);

        $mform->addElement('text', 'page_listrow', get_string('printinglistrow', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_listrow',PARAM_RAW);
        $mform->addHelpButton('page_listrow', 'printinglistrow', 'examregistrar');

        $mform->addElement('selectyesno', 'listrow_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('listrow_visible', 1);

        $mform->addElement('text', 'page_colwidths', get_string('printingcolwidths', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_colwidths',PARAM_RAW);
        $mform->addHelpButton('page_colwidths', 'printingcolwidths', 'examregistrar');

        $mform->addElement('editor', 'page_additionals', get_string('printingadditionals', 'examregistrar'));
        $mform->setType('page_additionals',PARAM_RAW);
        $mform->addHelpButton('page_additionals', 'printingadditionals', 'examregistrar');

        $mform->addElement('selectyesno', 'additionals_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('additionals_visible', 1);

        $mform->addElement('text', 'page_footer', get_string('printingfooter', 'examregistrar'), array('size'=>60,'rows'=>2));
        $mform->setType('page_footer',PARAM_RAW);
        $mform->addHelpButton('page_footer', 'printingfooter', 'examregistrar');

        $mform->addElement('selectyesno', 'footer_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('footer_visible', 0);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_examprintoptions_actionform extends examregistrar_actionform_base {

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'examprintoptions', get_string('examprintoptions', 'examregistrar'));

        $mform->addElement('static', 'description', '', get_string('printingoptionsmessasge', 'examregistrar'));

        $mform->addElement('text', 'page_header', get_string('printingheader', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_header',PARAM_RAW);
        $mform->addHelpButton('page_header', 'printingheader', 'examregistrar');

        $mform->addElement('selectyesno', 'header_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('header_visible', 1);

        $mform->addElement('editor', 'page_examtitle', get_string('printingexamtitle', 'examregistrar'));
        $mform->setType('page_examtitle',PARAM_RAW);
        $mform->addHelpButton('page_examtitle', 'printingexamtitle', 'examregistrar');

        $mform->addElement('selectyesno', 'examtitle_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('examtitle_visible', 1);

        $mform->addElement('editor', 'page_venuesummary', get_string('printingvenuesummary', 'examregistrar'));
        $mform->setType('page_venuesummary',PARAM_RAW);
        $mform->addHelpButton('page_venuesummary', 'printingvenuesummary', 'examregistrar');

        $mform->addElement('selectyesno', 'venuesummary_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('venuesummary_visible', 1);

        $mform->addElement('text', 'page_colwidths', get_string('printingcolwidths', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_colwidths',PARAM_RAW);
        $mform->addHelpButton('page_colwidths', 'printingcolwidths', 'examregistrar');

        $mform->addElement('text', 'page_footer', get_string('printingfooter', 'examregistrar'), array('size'=>60,'rows'=>2));
        $mform->setType('page_footer',PARAM_RAW);
        $mform->addHelpButton('page_footer', 'printingfooter', 'examregistrar');

        $mform->addElement('selectyesno', 'footer_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('footer_visible', 0);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_binderprintoptions_actionform extends examregistrar_actionform_base {

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'examprintoptions', get_string('examprintoptions', 'examregistrar'));

        $mform->addElement('static', 'description', '', get_string('printingoptionsmessasge', 'examregistrar'));

        $mform->addElement('editor', 'page_examtitle', get_string('printingexamtitle', 'examregistrar'));
        $mform->setType('page_examtitle',PARAM_RAW);
        $mform->addHelpButton('page_examtitle', 'printingexamtitle', 'examregistrar');

        $mform->addElement('selectyesno', 'examtitle_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('examtitle_visible', 1);

        $mform->addElement('editor', 'page_venuesummary', get_string('printingvenuesummary', 'examregistrar'));
        $mform->setType('page_venuesummary',PARAM_RAW);
        $mform->addHelpButton('page_venuesummary', 'printingvenuesummary', 'examregistrar');

        $mform->addElement('selectyesno', 'venuesummary_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('venuesummary_visible', 1);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}



class examregistrar_userlistprintoptions_actionform extends examregistrar_actionform_base {

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'examprintoptions', get_string('examprintoptions', 'examregistrar'));

        $mform->addElement('static', 'description', '', get_string('printingoptionsmessasge', 'examregistrar'));

        $mform->addElement('text', 'page_header', get_string('printingheader', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_header',PARAM_RAW);
        $mform->addHelpButton('page_header', 'printingheader', 'examregistrar');

        $mform->addElement('selectyesno', 'header_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('header_visible', 1);

        $mform->addElement('editor', 'page_title', get_string('printinguserlisttitle', 'examregistrar'));
        $mform->setType('page_title',PARAM_RAW);
        $mform->addHelpButton('page_title', 'printinguserlisttitle', 'examregistrar');

        $mform->addElement('selectyesno', 'title_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('title_visible', 1);

        $mform->addElement('text', 'page_colwidths', get_string('printingcolwidths', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_colwidths',PARAM_RAW);
        $mform->addHelpButton('page_colwidths', 'printingcolwidths', 'examregistrar');

        $mform->addElement('text', 'page_footer', get_string('printingfooter', 'examregistrar'), array('size'=>60,'rows'=>2));
        $mform->setType('page_footer',PARAM_RAW);
        $mform->addHelpButton('page_footer', 'printingfooter', 'examregistrar');

        $mform->addElement('selectyesno', 'footer_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('footer_visible', 0);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_bookingprintoptions_actionform extends examregistrar_actionform_base {

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'examprintoptions', get_string('examprintoptions', 'examregistrar'));

        $mform->addElement('static', 'description', '', get_string('printingoptionsmessasge', 'examregistrar'));

        $mform->addElement('text', 'page_header', get_string('printingheader', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_header',PARAM_RAW);
        $mform->addHelpButton('page_header', 'printingheader', 'examregistrar');

        $mform->addElement('selectyesno', 'header_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('header_visible', 1);

        $mform->addElement('editor', 'page_bookingtitle', get_string('printingbookingtitle', 'examregistrar'));
        $mform->setType('page_bookingtitle',PARAM_RAW);
        $mform->addHelpButton('page_bookingtitle', 'printingbookingtitle', 'examregistrar');

        $mform->addElement('selectyesno', 'bookingtitle_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('booking_visible', 1);

        $mform->addElement('text', 'page_listrow', get_string('printinglistrow', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_listrow',PARAM_RAW);
        $mform->addHelpButton('page_listrow', 'printinglistrow', 'examregistrar');

        $mform->addElement('selectyesno', 'listrow_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('listrow_visible', 1);

        $mform->addElement('text', 'page_footer', get_string('printingfooter', 'examregistrar'), array('size'=>60,'rows'=>2));
        $mform->setType('page_footer',PARAM_RAW);
        $mform->addHelpButton('page_footer', 'printingfooter', 'examregistrar');

        $mform->addElement('selectyesno', 'footer_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('footer_visible', 0);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_venueprintoptions_actionform extends examregistrar_actionform_base {

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'examprintoptions', get_string('examprintoptions', 'examregistrar'));

        $mform->addElement('static', 'description', '', get_string('printingoptionsmessasge', 'examregistrar'));

        $mform->addElement('text', 'page_header', get_string('printingheader', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_header',PARAM_RAW);
        $mform->addHelpButton('page_header', 'printingheader', 'examregistrar');

        $mform->addElement('selectyesno', 'header_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('header_visible', 1);

        $mform->addElement('editor', 'page_title', get_string('printinguserlisttitle', 'examregistrar'));
        $mform->setType('page_title',PARAM_RAW);
        $mform->addHelpButton('page_title', 'printinguserlisttitle', 'examregistrar');

        $mform->addElement('selectyesno', 'title_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('title_visible', 1);

        $mform->addElement('text', 'page_colwidths', get_string('printingcolwidths', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_colwidths',PARAM_RAW);
        $mform->addHelpButton('page_colwidths', 'printingcolwidths', 'examregistrar');

        $mform->addElement('text', 'page_footer', get_string('printingfooter', 'examregistrar'), array('size'=>60,'rows'=>2));
        $mform->setType('page_footer',PARAM_RAW);
        $mform->addHelpButton('page_footer', 'printingfooter', 'examregistrar');

        $mform->addElement('selectyesno', 'footer_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('footer_visible', 0);

        $this->add_standard_hidden_fields();

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_venuefaxprintoptions_actionform extends examregistrar_actionform_base {

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'examprintoptions', get_string('examprintoptions', 'examregistrar'));

        $mform->addElement('static', 'description', '', get_string('printingoptionsmessasge', 'examregistrar'));

        $mform->addElement('text', 'page_header', get_string('printingheader', 'examregistrar'), array('size'=>60, 'rows'=>2));
        $mform->setType('page_header',PARAM_RAW);
        $mform->addHelpButton('page_header', 'printingheader', 'examregistrar');

        $mform->addElement('editor', 'page_venuesummary', get_string('printingvenuesummary', 'examregistrar'));
        $mform->setType('page_venuesummary',PARAM_RAW);
        $mform->addHelpButton('page_venuesummary', 'printingvenuesummary', 'examregistrar');

        $mform->addElement('selectyesno', 'venuesummary_visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('venuesummary_visible', 1);

        $this->add_standard_hidden_fields();
        
        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}

