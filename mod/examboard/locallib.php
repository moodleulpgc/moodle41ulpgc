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
 * Library of functions used by the examboard module.
 *
 * This contains functions that are called from within the examboard module only
 * Functions that are also called by core Moodle are in {@link lib.php}
 *
 * @package     mod_examboard
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/tablelib.php');

/**
 * Finds the boards belonging in this module and  accesible to this user
 * either the user is participating or can view all
 *
 * @param object $cm Course Module cm_info
 * @param object $examboard record conting examboard data
 * @param bool $viewallgroups flag set if user can access all course groups
 * @param bool $viewallboards flag set if user can access all course groups
 * @param int $userid of the user checked, if 0 used $USER
 * @param bool $viewallboards flag set if user can access all course groups  
 * @param array of exam records from db
 * @return array of DB records or menu
*/
function examboard_get_boards($cm, $examboard, $viewallgroups, $viewallboards, $userid = 0, $menu = false) {
    global $DB, $USER; 
    
    if(!$userid) {
        $userid = $USER->id;
    }
    
    $params = array('examboardid' => $examboard->id);
    
    if(!$viewallgroups && $groupid = groups_get_activity_group($cm)) {
        $groupwhere = ' AND b.groupid = :groupid ';
        $params['groupid'] = $groupid;
    }
    
    if(!$viewallboards) {

        $tutorwhere = '';
        if($examboard->usetutors) {
            $tutorwhere = ' OR  EXISTS(SELECT 1 FROM {examboard_tutor} t WHERE t.examid = e.id AND t.tutorid = :tutor)   ';
            $params['tutor'] = $userid;
        }

        $params['member'] = $userid;
        $params['user'] = $userid;
        
        $sql = "SELECT b.*, e.sessionname
                FROM {examboard_board} b
                LEFT JOIN {examboard_exam} e ON e.examboardid = b.examboardid AND e.boardid = b.id
                
                WHERE b.examboardid = :examboardid $groupwhere 
                AND ( EXISTS(SELECT 1 FROM {examboard_member} m WHERE m.boardid = e.boardid AND m.userid = :member) 
                        OR
                      EXISTS(SELECT 1 FROM {examboard_examinee} u WHERE u.examid = e.id AND u.userid = :user)   
                      $tutorwhere
                    )
                GROUP BY b.id
                ORDER BY b.title ASC, b.idnumber ASC, b.name ASC, e.sessionname DESC, b.active DESC
            ";
        $boards = $DB->get_records_sql($sql, $params);
    } else {
        $boards = $DB->get_records('examboard_board', $params);
    }
    
    if($menu) {
        foreach($boards as $key => $board) {
            $flag = $board->active ? '' : ' ('.get_string('inactive', 'examboard').')'; 
            $boards[$key] = $board->title.'-'.$board->idnumber.$flag;
        }
    }
    
    return $boards;
    
}




function examboard_get_exam_board_conflicts($examid, $boardids = array(), $userid = false) {
    global $DB;

    $params = array();
    $additionalwhere = '';
    if($boardids) {
        list($insql, $params) = $DB->get_in_or_equal($boardids, SQL_PARAMS_NAMED);
        $additionalwhere .= " AND m.boardid $insql ";
    }
    if($userid) {
        $additionalwhere .=' AND t.userid = :userid ';
        $params['userid'] = $userid;
    }
    $params['examid'] = $examid; 
    
    $sql = "SELECT t.id, m.boardid, t.examid, t.userid 
            FROM {examboard_tutor} t 
            JOIN {examboard_member} m ON m.userid = t.tutorid
            WHERE t.examid = :examid $additionalwhere
            GROUP BY m.boardid ";
            
    return $DB->get_records_sql_menu($sql, $params);
}


/**
 * Finds the users with grade capability than can access this board 
 * Board members cannot be tutors for users examined by their board
 *
 * @param object $cm Course Module cm_info
 * @param object $context the context fro this module
 * @param int/object $boardorid the board we are finding graders for
 * @param bool $usetutors if tutors ar in use in this module, tutors must me excluded 
 * @return array the list of users 
*/
function examboard_get_potential_board_graders($cm, $context, $boardorid, $usetutors = false) {
    global $DB, $SESSION;
    
    if(is_object($boardorid)) {
        $board = $boardorid;        
    } else {
        $board = $DB->get_record('examboard_board', array('id' => $boardorid), '*', MUST_EXIST);
    }

    $orderby = 'lastname ASC, firstname ASC';
    if($SESSION->nameformat == 'firstname') {
        $orderby = 'firstname ASC, lastname ASC';
    }
    $userfields = 'u.id, u.idnumber, '.get_all_user_name_fields(true, 'u');

    if(!$board->groupid) {
        $groupmode = groups_get_activity_groupmode($cm);
        $groupid = groups_get_activity_group($cm); 
    }
    $users = get_enrolled_users($context, 'mod/examboard:grade', $groupid, $userfields, $orderby); 
    
    if($cm->groupingid && $groupmode == SEPARATEGROUPS) {
        $gusers = groups_get_grouping_members($cm->groupingid, 'u.id, idnumber') + get_enrolled_users($context, 'moodle/site:accessallgroups', 0, 'u.id, u.idnumber');  
        $users = array_intersect_key($users, $gusers);
    }
    
    if($usetutors) {
        $sql = "SELECT DISTINCT t.tutorid, t.examid 
                    FROM {examboard_exam} e 
                    JOIN {examboard_tutor} t ON e.id = t.examid
                    WHERE e.boardid = :boardid  AND e.examboardid = :examboardid 
                    GROUP BY t.tutorid, t.examid ";
        $params = array('boardid' => $board->id, 'examboardid' => $board->examboardid);
        if($tutors = $DB->get_records_sql_menu($sql, $params)) { 
            // exclude any tutor 
            $users = array_diff_key($users, $tutors);
        }
    }
    
    foreach($users as $key => $user) {
        $users[$key] = fullname($user);
    }
    
    return $users;
}

/**
 * Finds the users with grade capability than can serve as tutors for users in this exam
 * Tutors cannot be board members for the same exam
 *
 * @param object $cm Course Module cm_info
 * @param object $context the context fro this module
 * @param int/object $examorid the exam we are finding users for for
 * @return array the list of users 
*/
 function examboard_get_potential_tutors($cm, $context, $examorid) {
    global $DB;
    
    if(is_object($examorid)) {
        $exam = $examorid;
    } else {
        $exam = $DB->get_record('examboard_board', array('id' => $examorid), '*', MUST_EXIST);
    }

    // this return all graders without removing tutors
    $graders = examboard_get_potential_board_graders($cm, $context, $exam->boardid, false);
    
    //remove known boardmembers  
    if($members = examboard_get_board_members($exam->boardid)) {
        foreach($members as $member) {
            if(isset($graders[$member->userid])) {
                unset($graders[$member->userid]);
            }
        }
    }
    
    return $graders;
}

/**
 * Finds the exams these board members are assigned or can be assigned to
 * Tutors cannot be board members for the same exam
 *
 * @param int $boardid the ID of the board committee
 * @param int $examboardid ID of the module instance
 * @param bool $usetutors if tutors used in this examboard
 * @param int $examperiod optional, if non-zero only exmas in the same examperiod
 * @return array the list of users 
*/
function examboard_get_board_exams($boardid, $examboardid, $usetutors, $examperiod = 0) {
    global $DB;

    // assigned = in members table
    $params = array('examboardid'=>$examboardid, 'boardid'=>$boardid);
    $sql = "SELECT e.id, b.idnumber, b.title, e.boardid, e.examperiod, e.sessionname 
                FROM {examboard_exam} e
                JOIN {examboard_board} b ON e.boardid = b.id AND e.examboardid = b.examboardid
            WHERE e.examboardid = :examboardid AND e.boardid = :boardid
            ORDER BY b.idnumber ASC, e.examperiod DESC, e.sessionname ASC";
    $assignedexams = $DB->get_records_sql($sql, $params);
    
    // other rest excluding those been tutors, if used
    // if a exam has users with grades, cannot assign a new board (grades would be lost)
    $sql = "SELECT e.id, b.idnumber, b.title, e.boardid, e.examperiod, e.sessionname
                FROM {examboard_exam} e
                JOIN {examboard_board} b ON e.boardid = b.id AND e.examboardid = b.examboardid
            WHERE e.examboardid = :examboardid AND e.boardid != :boardid
                AND NOT EXISTS(SELECT 1 FROM {examboard_grades} g 
                                        WHERE g.examid = e.id AND g.grade >= 0) ";
    if($usetutors) {
        $sql .= " AND NOT EXISTS(SELECT 1 FROM {examboard_member} m 
                                        JOIN {examboard_exam} ee ON ee.boardid = m.boardid
                                        JOIN {examboard_tutor} t ON t.examid = ee.id AND m.userid = t.tutorid
                                        WHERE m.boardid = ee.boardid AND t.examid = ee.id) ";
    }
    $sql .= "ORDER BY b.idnumber ASC, e.examperiod DESC, e.sessionname ASC";
    $otherexams = $DB->get_records_sql($sql, $params);

    return array($assignedexams, $otherexams);
}


function examboard_get_board_notifications($boardid) {
    global $DB;
    
    $notifications = array();
    
    $sql = "SELECT n.*, m.boardid   
            FROM {examboard_notification} n 
            JOIN {examboard_exam} e ON e.id = n.examid
            JOIN {examboard_member} m ON m.boardid = e.boardid AND m.userid = n.userid 
            WHERE m.boardid = :boardid 
            ORDER BY n.timeissued";
    $params = array('boardid' => $boardid);
    if($raw = $DB->get_records_sql($sql, $params)) {
        foreach($raw as $notify) {
            if(!isset($notifications[$notify->userid][$notify->examid])) {
                $notifications[$notify->userid][$notify->examid] = array();
            }
            $notifications[$notify->userid][$notify->examid][$notify->id] = $notify;
        }
    }
    
    return $notifications;            
}


function examboard_get_board_confirmations($boardid) {
    global $DB;
    
    $confirmations = array();
    
    $sql = "SELECT c.*, m.boardid   
            FROM {examboard_confirmation} c 
            JOIN {examboard_exam} e ON e.id = c.examid
            JOIN {examboard_member} m ON m.boardid = e.boardid AND c.userid = m.userid
            WHERE m.boardid = :boardid 
            ORDER BY c.timecreated";
    $params = array('boardid' => $boardid);
    if($raw = $DB->get_records_sql($sql, $params)) {
        foreach($raw as $confirm) {
            if(!isset($confirmations[$confirm->userid][$confirm->examid])) {
                $confirmations[$confirm->userid][$confirm->examid] = array();
            }
            $confirmations[$confirm->userid][$confirm->examid][$confirm->id] = $confirm;
        }
    }
    
    return $confirmations;            
}




function examboard_get_exam_examinees($examid, $withtutors = false, $excluded = null, $userid = 0) {
    global $DB;
    
    $params = array('examid'=>$examid);
    
    $where = ''; 
    if(isset($excluded)) {
        $where = ' AND e.excluded = :excluded ';
        $params['excluded'] = (int)boolval($excluded);
    }

    if($userid) {
        $where = ' AND e.userid = :userid ';
        $params['userid'] = $userid; 
    }
    
    if($withtutors) {
        $sql = "SELECT CONCAT_WS('-',e.id, t.id) AS idx,  e.*, t.tutorid, t.main, t.approved 
                FROM {examboard_examinee} e
                LEFT JOIN {examboard_tutor} t ON t.examid = e.examid AND e.userid = t.userid
                WHERE e.examid = :examid $where
                ORDER BY e.sortorder ASC, t.main DESC  ";
                
        return $DB->get_records_sql($sql, $params);
    }
    
    return $DB->get_records('examboard_examinee', $params, 'sortorder');
}

/**
 * Finds the users with submit capability than can serve as examinees in this exam
 *
 * @param object $cm Course Module cm_info
 * @param object $context the context fro this module
 * @param int/object $examorid the exam we are finding users for for
 * @return array the list of users 
*/
function examboard_get_potential_exam_users($cm, $context, $examorid) {
    global $DB, $SESSION;
    
    if(is_object($examorid)) {
        $exam = $examorid;        
    } else {
        $exam = $DB->get_record('examboard_board', array('id' => $examorid), '*', MUST_EXIST);
    }
    
    $groupid = $DB->get_field('examboard_board', 'groupid', array('examboardid' =>$exam->examboardid, 'id'=>$exam->boardid));

    $orderby = 'lastname ASC, firstname ASC';
    if($SESSION->nameformat == 'firstname') {
        $orderby = 'firstname ASC, lastname ASC';
    }
    $userfields = 'u.id, u.idnumber, '.get_all_user_name_fields(true, 'u');

    if(!$groupid) {
        $groupmode = groups_get_activity_groupmode($cm);
        $groupid = groups_get_activity_group($cm); 
    }
    
    $users = get_enrolled_users($context, 'mod/examboard:submit', $groupid, $userfields, $orderby); 
    
    // existing examinees must be remmoved to avoid duplications
    if($existing = examboard_get_exam_examinees($exam->id)) {
        foreach($existing as $examinee) {
            if(isset($users[$examinee->userid])) {
                unset($users[$examinee->userid]);
            }
        }
    }
    
    foreach($users as $key => $user) {
        $users[$key] = fullname($user);
    }
    
    
    return $users;
}


/**
 * Finds the boards belonging in this module and  accesible to this user
 * either the user is participating or can view all
 *
 * @param object $cm Course Module cm_info
 * @param object $context the context fro this module
 * @throw required_capability_exception
 * @return array of bools  $cangrade, $canallocate
*/
function examboard_action_access_helper($context, $requiremanage = false ) {
    $cangrade = has_capability('mod/examboard:grade', $context);
    $canallocate = has_capability('mod/examboard:allocate', $context);
    $canmanage = has_capability('mod/examboard:manage', $context); 
    if($requiremanage) {
        $cangrade = false;
        $canallocate = false;
    }
    if (!($cangrade || $canallocate || $canmanage)) {
        $capabilty = $canallocate ? 'mod/examboard:grade' : 'mod/examboard:allocate';   
        $capabilty = $requiremanage ? 'mod/examboard:manage' : $capabilty; 
        throw new required_capability_exception($context, $capability, 'nopermissions', '');
    }
    return array($cangrade, $canallocate, $canmanage);
}


/**
 * Finds the boards belonging in this module and  accesible to this user
 * either the user is participating or can view all
 *
 * @param object $cm Course Module cm_info
 * @param object $context the context fro this module
 * @param object $examboard record conting examboard data
 * @param object $cm Course Module cm_info
 * @param string $action the editing action to be performed
 * @param moodleform $mform moodleform class
 * @return void  the action is in setting mform
*/
function examboard_set_action_form($cm, $context, $examboard, $action, &$mform) {
    global $CFG, $DB, $PAGE, $USER;

    $capability = '';
    switch($action) {
        case 'addexam' :
        case 'updateexam' :
                //require_capability('mod/examboard:manage', $context);
                list($cangrade, $canallocate, $canmanage) = examboard_action_access_helper($context); 
                $manage = optional_param('manage', '-1', PARAM_INT);
                
                require_once($CFG->dirroot.'/mod/examboard/exam_form.php');
                
                $exam = 0;
                $examid = optional_param('exam', 0, PARAM_INT);
                $itemid = $examid ? $examid : optional_param('item', 0, PARAM_INT);
                
                $viewallgroups = has_capability('moodle/site:accessallgroups', $context);
                $viewallboards = has_capability('mod/examboard:viewall', $context);
                $boards = examboard_get_boards($cm, $examboard, $viewallgroups, $viewallboards, $USER->id, true);
                
                //eliminate all boards that have members in conflict with this users, their tutors
                if($examboard->usetutors) {
                    if($conflicts = examboard_get_exam_board_conflicts($examid, array_keys($boards))) {
                        foreach($conflicts as $bid => $value) {
                            unset($boards[$bid]);
                        }
                    }
                }
                
                $newexam = ($action === 'addexam') ? 0 : $examid;
                
                $mform = new examboard_addexam_form(null, array('cm'=>$cm, 'boards' => $boards, 'itemid' => $itemid, 'examid'=>$newexam, 'manage' => $canmanage));

                if($itemid) {
                    // if there is an examid we are updating
                    $exam = examboard_get_exam_with_board($itemid);
                    $exam->updateboardid = $exam->boardid; // needed for form processing 
                    $exam->exam = $examid;
                    $exam->name = array('text'=>$exam->name, 'format'=>1);
                    $exam->id = $cm->id;
                    if($action === 'addexam') {
                        unset($exam->exam);
                        unset($exam->sessionname);
                        unset($exam->accessurl);
                        unset($exam->venue);
                        unset($exam->examdate);
                        unset($exam->duration);
                    } else {
                        $exam->venue = array('text'=>$exam->venue, 'format'=>1);
                    }
                    $mform->set_data($exam);
                }
                break;
                
        case 'notify' :
                require_capability('mod/examboard:notify', $context);
                require_once($CFG->dirroot.'/mod/examboard/notify_form.php');
                
                $exams = array();
                $groupid = optional_param('group', 0, PARAM_INT);
                $data = new stdClass();

                if($examid = optional_param('exam', 0, PARAM_INT)) {
                    $exam = $DB->get_record('examboard_exam', array('id'=>$examid), '*', MUST_EXIST);
                    $exam->idnumber = $DB->get_field('examboard_board', 'idnumber', array('id' => $exam->boardid));
                    $exams[$exam->id] = $exam->idnumber. ' ('.$exam->sessionname.')'; 
                    $data->exams = $examid;
                }
                
                if($boardid = optional_param('board', 0, PARAM_INT)) {
                    $board = $DB->get_record('examboard_board', array('id'=>$boardid), '*', MUST_EXIST);
                    list($exams, $otherexams) = examboard_get_board_exams($boardid, $examboard->id, $examboard->usetutors);
                    unset($otherexams);
                    foreach($exams as $eid => $exam) {
                        $exams[$eid] = $exam->idnumber. ' ('.$exam->sessionname.')'; 
                    }
                    //$data->exams = array_keys($exams);
                }
                
                if(!$exams && !$examid && !$boardid) {
                    // notify all, from settings block
                    $viewall = has_any_capability(array('mod/examboard:viewall', 'mod/examboard:manage'), $context);
                    $exams = examboard_get_user_exams($examboard, $viewall, 0, $groupid, 'e.active DESC, b.title ASC, b.idnumber ASC');
                    foreach($exams as $eid => $exam) {
                        $exams[$eid] = $exam->idnumber. ' ('.$exam->sessionname.')'; 
                    }

                }
                
                $user = false;
                $usertype = optional_param('usertype', EXAMBOARD_USERTYPE_NONE, PARAM_INT);
                if($usertype > 0) { 
                    $user = $DB->get_record('user', array('id'=>$usertype), '*', MUST_EXIST);                                
                    $data->usertype = $usertype;
                } else {
                    $user = $usertype;
                }
                
                $mform = new examboard_notify_form(null, array('cmid'=>$cm->id, 'exams' => $exams, 'user'=>$user));
                
                $data->messagebody['text'] = get_string('defaultbody', 'examboard');
                $mform->set_data($data);
                
                break;
                
        case 'allocateboard' :
        case 'allocateusers' :
                require_capability('mod/examboard:manage', $context);
                require_once($CFG->dirroot.'/mod/examboard/allocation_form.php');

                $groupid = groups_get_activity_group($cm); 
                $groups = groups_list_to_menu(groups_get_activity_allowed_groups($cm));  

                $mform = new examboard_allocation_form(null, array('cmid'=>$cm->id, 'examboard'=>$examboard, 
                                                                    'groupid'=>$groupid, 'groups'=>$groups,
                                                                    'allocationmode'=>$action));
                break;
                
        case 'userassign' :                
                require_once($CFG->dirroot.'/mod/examboard/userassign_form.php');
                
                $mform = new examboard_userassign_form(null, array('cmid'=>$cm->id, 'examboard'=>$examboard));    
                break;
    
        case 'editmembers' :    
                require_capability('mod/examboard:allocate', $context);
                require_once($CFG->dirroot.'/mod/examboard/members_form.php');
                
                $boardid = required_param('board', PARAM_INT);
                $members = examboard_get_board_members($boardid);
                list($asignedexams, $otherexams) = examboard_get_board_exams($boardid, $examboard->id, $examboard->usetutors);
                
                $graders = examboard_get_potential_board_graders($cm, $context, $boardid, $examboard->usetutors);
                
                $mform = new examboard_members_form(null, array('cmid'=>$cm->id, 'examboard'=>$examboard, 'boardid' => $boardid, 
                                                                'users'=>$graders, 'members' => $members, 
                                                                'assigned' => $asignedexams, 'other' => $otherexams));
            
                if($boardid) {
                    $data = new stdClass();
                    foreach($members as $user) {
                        $index = $user->sortorder;
                        if($user->deputy) {
                            $data->{"deputyids[$index]"} = $user->userid;
                        } else {
                            $data->{"memberids[$index]"} = $user->userid;
                        }
                    }
                    $mform->set_data($data);
                }
                break;
                
        case 'updateuser' :      
                $cangrade = has_capability('mod/examboard:grade', $context);
                $canallocate = has_capability('mod/examboard:allocate', $context); 
                if (!($cangrade || $canallocate)) {
                    $capabilty = $canallocate ? 'mod/examboard:grade' : 'mod/examboard:allocate';       
                    throw new required_capability_exception($context, $capability, 'nopermissions', '');
                }
                    
                require_once($CFG->dirroot.'/mod/examboard/examinee_form.php');

                $examid = required_param('exam', PARAM_INT);
                $exam = $DB->get_record('examboard_exam', array('id'=>$examid), '*', MUST_EXIST);
                
                $users = examboard_get_potential_exam_users($cm, $context, $exam);
                
                $existingtutors = array();
                
                if($userid = optional_param('user', 0, PARAM_INT)) {
                    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                    $existingtutors = examboard_get_exam_examinees($exam->id, true, 0, $userid);
                    // ensures this user is in the list
                    $users[$userid] = fullname($user);
                }
                
                $tutors = array();
                if($examboard->usetutors) {
                    $tutors = examboard_get_potential_tutors($cm, $context, $exam);
                }
                
                $mform = new examboard_examinee_form(null, array('cmid'=>$cm->id, 'examboard'=>$examboard, 'examid' => $examid, 'canallocate' => $canallocate, 
                                                                    'existing' => $existingtutors, 'users'=>$users, 'tutors'=>$tutors));
                //set data if updating
                if($userid) {
                    $data = new stdClass();
                    $data->examinee = $userid;
                    $data->userlabel = $DB->get_field('examboard_examinee', 'userlabel', array('examid'=>$examid, 'userid'=>$userid));
                    $data->excluded = $DB->get_field('examboard_examinee', 'excluded', array('examid'=>$examid, 'userid'=>$userid));
                    foreach($existingtutors as $user) {
                        if(isset($user->tutorid) && $user->main == 1) {
                            $data->tutor = $user->tutorid;
                        }
                        if(isset($user->tutorid) && $user->main == 0) {
                            $data->others[] = $user->tutorid;
                        }
                    }
                    $mform->set_data($data);
                }
                break;
                
        case 'moveusers' :      
                $cangrade = has_capability('mod/examboard:grade', $context);
                $canallocate = has_capability('mod/examboard:allocate', $context); 
                if (!($cangrade || $canallocate)) {
                    $capabilty = $canallocate ? 'mod/examboard:grade' : 'mod/examboard:allocate';       
                    throw new required_capability_exception($context, $capability, 'nopermissions', '');
                }
                    
                require_once($CFG->dirroot.'/mod/examboard/user_session_form.php');

                $examid = required_param('exam', PARAM_INT);
                $exam = $DB->get_record('examboard_exam', array('id'=>$examid), '*', MUST_EXIST);
                $examination = \mod_examboard\examination::get_from_id($examid);
                
                $users = $examination->load_examinees();
                foreach($users as $uid => $user) {
                    $users[$uid] = fullname($user);
                }
                
                $examperiod = $canallocate ? 0 : $exam->examperiod;
                list($asignedexams, $otherexams) = examboard_get_board_exams($exam->boardid, $examboard->id, $examboard->usetutors, $examperiod);
                if($canallocate) {
                    $asignedexams = $asignedexams + $otherexams;
                }
                
                $renderer = $PAGE->get_renderer('mod_examboard');
                foreach($asignedexams as $eid => $aexam) {
                     $asignedexams[$eid] =  $renderer->format_exam_name($aexam);
                }
                $asignedexams = array('' => get_string('choose')) + $asignedexams;                
                
                
                $mform = new examboard_change_user_session_form(null, array('cmid'=>$cm->id, 
                                                                            'exam' => $exam,
                                                                            'targets' => $asignedexams,
                                                                            'users' => $users));
                break; 
        
        case 'deleteexam' :      
                require_capability('mod/examboard:manage', $context);
                require_once($CFG->dirroot.'/mod/examboard/delete_form.php');    
                
                $examid = required_param('exam', PARAM_INT);
                $exam = $DB->get_record('examboard_exam', array('id'=>$examid), '*', MUST_EXIST);
                $name = $DB->get_field('examboard_board', 'idnumber', array('id'=>$exam->boardid));
                $name .= ' ('.$exam->sessionname.')';
                $message = get_string('confirmdeleteexam', 'examboard', $name);
                $additionals = array('withboard' => get_string('deleteexamboard', 'examboard'),);
                
                $mform = new examboard_delete_form(null, array('cmid'=>$cm->id, 'warning'=> $message, 
                                                                    'confirmed' => $action,
                                                                    'exam' => $examid,
                                                                    'user' => '',
                                                                    'additionals' => $additionals));
                break;                                                    

        case 'deleteuser' :      
                require_capability('mod/examboard:manage', $context);
                require_once($CFG->dirroot.'/mod/examboard/delete_form.php');    
                
                $userid = required_param('user', PARAM_INT);
                $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);                
                $examid = required_param('exam', PARAM_INT);
                $exam = $DB->get_record('examboard_exam', array('id'=>$examid), '*', MUST_EXIST);
                $name = $DB->get_field('examboard_board', 'idnumber', array('id'=>$exam->boardid));
                $name .= ' ('.$exam->sessionname.')';
                $a = new stdClass();
                $a->exam = $name;
                $a->name = fullname($user);
                
                $message = get_string('confirmdeleteuser', 'examboard', $a);
                $additionals = array();                
                
                $mform = new examboard_delete_form(null, array('cmid'=>$cm->id, 'warning'=> $message, 
                                                                    'confirmed' => $action,
                                                                    'exam' => $examid,
                                                                    'user' => $userid,
                                                                    'additionals' => $additionals));
                break;

                
        case 'deleteall' :      
                require_capability('mod/examboard:manage', $context);
                require_once($CFG->dirroot.'/mod/examboard/delete_form.php');    
                
                $examid = required_param('exam', PARAM_INT);
                $exam = $DB->get_record('examboard_exam', array('id'=>$examid), '*', MUST_EXIST);
                $name = $DB->get_field('examboard_board', 'idnumber', array('id'=>$exam->boardid));
                $name .= ' ('.$exam->sessionname.')';
                
                $message = get_string('confirmdeleteallexaminees', 'examboard', $name);
                $additionals = array();                
                
                $mform = new examboard_delete_form(null, array('cmid'=>$cm->id, 'warning'=> $message, 
                                                                    'confirmed' => $action,
                                                                    'exam' => $examid,
                                                                    'user' => '',
                                                                    'additionals' => $additionals));
                break;

                
        case 'deletetutor' :      
                require_capability('mod/examboard:manage', $context);
                require_once($CFG->dirroot.'/mod/examboard/delete_form.php');    
                
                $userid = required_param('tutor', PARAM_INT);
                $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);                
                $examid = required_param('exam', PARAM_INT);
                $exam = $DB->get_record('examboard_exam', array('id'=>$examid), '*', MUST_EXIST);
                $name = $DB->get_field('examboard_board', 'idnumber', array('id'=>$exam->boardid));
                $name .= ' ('.$exam->sessionanme.')';
                $a = new stdClass();
                $a->exam = $name;
                $a->user = fullname($user);
                $message = get_string('confirmdeletetutor', 'examboard', $a);
                
                $additionals = array('deltutors' => get_string('deleteothertutors', 'examboard'),);
                
                $message = get_string('confirmdeleteuser', 'examboard', fullname($user));
                
                
                $mform = new examboard_delete_form(null, array('cmid'=>$cm->id, 'warning'=> $message, 
                                                                    'confirmed' => $action,
                                                                    'item' => $userid,
                                                                    'additionals' => $aditionals));
                break;
                
        case 'boardconfirm' :   
                require_capability('mod/examboard:grade', $context);
                require_once($CFG->dirroot.'/mod/examboard/confirmation_form.php');   
                
                $examid = required_param('exam', PARAM_INT);
                $userid = optional_param('user', $USER->id, PARAM_INT);
                $exam = $DB->get_record('examboard_exam', array('id'=>$examid), '*', MUST_EXIST);
                $board = $DB->get_record('examboard_board', array('id'=>$exam->boardid), '*', MUST_EXIST);
                
                $confirmations = examboard_get_board_confirmations($exam->boardid);

                $mform = new examboard_board_confirmation_form(null, array('cmid'=>$cm->id, 
                                                                    'exam' => $exam,
                                                                    'board' => $board,
                                                                    'user' => $userid,
                                                                    'canmanage' => has_capability('mod/examboard:manage', $context)));
                $confirm = null;
                if(isset($confirmations[$userid][$examid])) {
                    $confirm = end($confirmations[$userid][$examid]);
                }
                $data = new stdClass();
                $data->confirmed = isset($confirm) ? $confirm->confirmed : $examboard->confirmdefault;
                $data->exemption = isset($confirm) ? $confirm->exemption : 0;
                $data->discharge = isset($confirm) ? $confirm->discharge : '';
                $data->discharge_editor['text'] = isset($confirm) ? $confirm->dischargetext : '';
                $data->discharge_editor['format'] = isset($confirm) ? $confirm->dischargeformat : FORMAT_HTML;
                $data->available = isset($confirm) ? $confirm->available : 1;
                $mform->set_data($data);
                
                break;
                
        case 'import' :      
                require_capability('mod/examboard:manage', $context);
                require_once($CFG->dirroot.'/mod/examboard/import_form.php');    
                
                list($mandatory, $optional) = examboard_import_export_fields($examboard);

                $mform = new examboard_import_form(null, array('cmid'=>$cm->id, 
                                                                    'mandatory'=> $mandatory, 
                                                                    'optional' => $optional));
                break;

        case 'export' :      
                require_capability('mod/examboard:manage', $context);
                require_once($CFG->dirroot.'/mod/examboard/export_form.php');    
                
                list($mandatory, $optional) = examboard_import_export_fields($examboard, true);
                
                $groupid = optional_param('group', 0, PARAM_INT);
                $exams = examboard_get_user_exams($examboard, true, 0, $groupid, ' idnumber ASC ');
                foreach($exams as $eid => $exam) {
                    $exams[$eid] = $exam->idnumber;
                    if($exam->name) {
                        $exams[$eid] .= ' ('.$exam->name.')';
                    }
                    $exams[$eid] .= ' - '.$exam->sessionname;
                }

                $mform = new examboard_export_form(null, array('cmid'=>$cm->id, 
                                                                    'exams' => $exams,
                                                                    'mandatory'=> $mandatory, 
                                                                    'optional' => $optional));
                $data =  new stdClass();
                $course = $cm->get_course();
                $data->filename = clean_filename($course->shortname.'-'.$examboard->name.'_'.userdate(time(), '%Y%m%d-%H%M'));
                $mform->set_data($data);
                
                break;
        
        case 'upload_examination' :      
        case 'upload_board' :
        case 'upload_member' :      
                $capability = 'mod/examboard:grade';
        case 'upload_tutor' :
        case 'upload_user' :
                if($action == 'upload_tutor') {
                    $capability = 'mod/examboard:tutorize';
                    $table = 'tutor';
                } elseif($action == 'upload_user') {
                        $capability = 'mod/examboard:submit';
                } elseif($action == 'upload_member') {
                    $table = 'grades';                
                }
                if(empty($table)) {
                    $table = 'examinee';
                }
        
                require_capability($capability, $context);
                require_once($CFG->dirroot.'/mod/examboard/upload_form.php');    
                
                $exam = 0;
                $itemid = optional_param('item', 0, PARAM_INT);
                $userid = optional_param('user', 0, PARAM_INT);        
                $rec = $DB->get_record('examboard_'.$table, array('id' => $itemid, 'userid' => $userid), '*', MUST_EXIST);                
                $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
        
                $mform = new examboard_upload_form(null, array('cmid'=>$cm->id, 
                                                                    'action' => $action,
                                                                    'item' => $itemid, 
                                                                    'user' => $user,
                                                                    'examid' => $rec->examid,
                                                                    ));

                $upload = new stdClass;
                $upload->id = $cm->id;
                $draftitemid = file_get_submitted_draft_itemid('attachments');
                $area = substr($action, 7);
                $maxfiles = get_config('examboard', 'uploadmaxfiles');
                file_prepare_draft_area($draftitemid, $context->id, 'mod_examboard', $area, $itemid,
                                            array('subdirs' => 0, 'maxfiles' => $maxfiles));
                $upload->attachments = $draftitemid;
                $upload->itemid = $itemid;
                $upload->exam = $rec->examid;
                $mform->set_data($upload);                                                                    
        
                break;
        
        default : 
                break;
    }

}

function examboard_process_add_update_exam($examboard, $fromform) {
    global $CFG, $DB;

    $success = true;
    
    // $fromform->id id is the cmid for other uses, delete 
    // $fromform->exam is != 0 only if updating a an existing exam
    unset($fromform->id);
    if($fromform->exam) {
//        $fromform->id = $fromform->exam; // this ensures id exists only if updating
    }
    
    $now = time();
    // sanitization
    foreach(array('title', 'idnumber', 'sessionname') as $field) {
        $fromform->{$field} = format_string(trim($fromform->{$field}));
    }
    foreach(array('name', 'venue') as $field) {
        $value = $fromform->{$field};
        $fromform->{$field} = format_text(trim($value['text']), $value['format']);
    }
    
    $context = context_module::instance($examboard->cmid);
    $eventparams = array(
        'context' => $context,
        'other' => array('examboardid'=>$examboard->id),
    );
    
    $board = new stdClass();
        $board->examboardid = $examboard->id;
        $board->title = $fromform->title;
        $board->name = $fromform->name;
        $board->groupid = $fromform->groupid;
        $board->active = $fromform->boardactive;
        $board->timemodified = $now;
    
    $exam = new stdClass();
        $exam->examboardid = $examboard->id;
        $exam->examperiod = $fromform->examperiod;
        $exam->sessionname = $fromform->sessionname;
        $exam->venue  = $fromform->venue;
        $exam->accessurl  = $fromform->accessurl;
        $exam->examdate  = $fromform->examdate;
        $exam->duration  = $fromform->duration;
        $exam->active = $fromform->examactive;
        $exam->timemodified  = $now;
    
    $start = 0;
    $end = 1;
    $idnumber = '';
    $format = '';
    if(!$fromform->exam && isset($fromform->submitbulkadd) && $fromform->bulkaddnum && $fromform->bulkaddreplace) {
        $start = $fromform->bulkaddstart;
        $end = $start + $fromform->bulkaddnum; 
        $n = strlen("$end");
        $format = "%'.0{$n}d";
        $idnumber = $fromform->idnumber;
    }
   
    for($i = $start; $i < $end; $i++) {
        if($idnumber) {
            //$board->idnumber = $idnumber.sprintf("%'.0{$n}d", $i);
            $replace = sprintf("%'.0{$n}d", $i);
            $board->idnumber = str_replace($fromform->bulkaddreplace, sprintf("%'.0{$n}d", $i), $idnumber);
        } else {
            $board->idnumber = $fromform->idnumber;
        }
        if(!$fromform->boardid) {
            // we need first to save board data to get the id
            if($fromform->exam) {
                // this dance allows to user fromforn for updating, without creating other temporal objects
                $board->id = $fromform->updateboardid; 
                $success = $success && $DB->update_record('examboard_board', $board);
                unset($board->id);
                $fromform->boardid = $fromform->updateboardid;
            } else {
                $fromform->boardid = $DB->insert_record('examboard_board', $board);
                $success = $success && $fromform->boardid;
            }
        } else {
            $board->id = $fromform->boardid; 
            $success = $success && $DB->update_record('examboard_board', $board);
            unset($board->id);
        }
        
        // if there is a boardid, we can save exam    
        if($fromform->boardid) {
            //simply save exam data
            $exam->boardid = $fromform->boardid;
            if(isset($fromform->exam) && $fromform->exam) {
                $exam->id = $fromform->exam;
                $success = $success && $DB->update_record('examboard_exam', $exam);
            } else {
                $exam->id =  $DB->insert_record('examboard_exam', $exam);
                $success = $success && $exam->id;
            }
            
            $eventparams['objectid'] = $exam->id;
            $event = \mod_examboard\event\exam_updated::create($eventparams);
            $event->trigger();
            unset($exam->id);
        }
        
        $fromform->boardid = 0;
    }
    
    if($success) {
        $message = get_string('changessaved');
        
    } else {
        $message = get_string('cannotsavedata', 'error');   
    }
    
    return $message;
}


function examboard_save_update_member($newuser, $member, $params) {
    global $DB;
    
    $success = true;
    
    if(!$newuser) {
        // delete this member
        $success = $DB->delete_records('examboard_member', $params); 
    } else {
        // update / insert member in this position
        if($oldrec = $DB->get_record('examboard_member', $params, 'id, boardid, userid, sortorder')) {
            $oldrec->userid = $newuser;
            $oldrec->role = $member->role;
            $oldrec->timemodified = $member->timemodified;
            $success = $success && $DB->update_record('examboard_member', $oldrec);
        } else {
            $member->userid = $newuser;
            $success = $success && $DB->insert_record('examboard_member', $member);
        }
    }

    return $success;
}

/**
 * Assign / Unassign board members in a given board/exam 
 * from a manually input form
 *
 * @param stdClass $examboard instance record
 * @param stdClass $fromform user input from mform
 * @return string success/error notification message 
*/
function examboard_process_editmembers($examboard, $fromform) {
    global $DB; 
    
    $success = true;
    $message = '';
    
    $now = time();
    $member = new stdClass();
    $member->boardid =  $fromform->board;
    $member->timecreated = $now;
    $member->timemodified = $now;
    $member->role = '';
    $params = array('boardid'=>$fromform->board, 'deputy'=> 0);
    
    $context = context_module::instance($examboard->cmid);
    // event params.
    $eventparams = array(
        'context' => $context,
        'objectid' => $member->boardid,
        'other' => array('examboardid'=>$examboard->id),
    );
    
    foreach(range(0, $examboard->maxboardsize -1) as $index) {
        if($index === 0) {
            $member->role = $examboard->chair;
        } elseif($index === 1) {
            $member->role = $examboard->secretary;
        } else { 
            $member->role = $examboard->vocal;
        }
    
        $params['sortorder'] = $index;
        $member->sortorder = $index;
        
        $eventparams['other']['sortorder'] = $index;
        
        $params['deputy'] = 0;
        $member->deputy = 0;
        $newuser =  isset($fromform->memberids[$index]) ? $fromform->memberids[$index] : 0; 
        $success = $success && examboard_save_update_member($newuser, $member, $params);

        $eventparams['relateduserid'] = $newuser;
        $eventparams['other']['deputy'] = 0;
        $event = \mod_examboard\event\member_updated::create($eventparams);
        $event->trigger();
        
        $params['deputy'] = 1;
        $member->deputy = 1;
        $newuser =  isset($fromform->deputyids[$index]) ? $fromform->deputyids[$index] : 0;              
        $success = $success && examboard_save_update_member($newuser, $member, $params);
        
        $eventparams['relateduserid'] = $newuser;
        $eventparams['other']['deputy'] = 1;
        $event = \mod_examboard\event\member_updated::create($eventparams);
        $event->trigger();
        $member->role = '';
    }

    list($assignedexams, $otherexams) = examboard_get_board_exams($fromform->board, $examboard->id, false);
    
    if($fromform->assignedexams) {
        foreach($fromform->assignedexams as $examid) {
            if(isset($assignedexams)) {
                //already assigned, do nothing
                unset($assignedexams[$examid]);
            } else {
                //not assigned, do it
                if($success = $success && $DB->set_field('examboard_exam', 'boardid', $fromform->board, array('id' => $examid))) {
                    // now set confirmation, if required
                    // not needed better allow users to confirm manually
                    /*
                    if($examboard-> requireconfirm && $examboard->confirmdefault) {
                        $confirm = new stdClass();
                        $confirm->examid = $examid;
                        $confirm->confirmed = 1;
                        $confirm->available = 1;
                        $confirm->dischargeformat = FORMAT_MOODLE;
                        foreach(($fromform->memberids + $fromform->deputyids) as  $userid) {
                            if(!$DB->record_exists('examboard_confirmation', array('examid'=>$examid, 'userid'=>$userid))) { 
                                $DB->insert_record('examboard_confirmation', $confirm);
                            }
                        }
                    }
                    */
                }
            }
        }
    } 
    
    if($assignedexams) {
        // if we are here, some assigned exams where unassigned
        list($insql, $params) = $DB->get_in_or_equal(array_keys($assignedexams));
        $DB->set_field_select('examboard_exam', 'boardid', 0, " id $insql ", $params);
    }
    
    if($success) {
        $message = get_string('changessaved');
        
        $eventparams['objectid'] = $fromform->board;
        unset($eventparams['relateduserid']);
        unset($eventparams['other']['deputy']);
        unset($eventparams['other']['sortorder']);
        $event = \mod_examboard\event\board_updated_members::create($eventparams);
        $event->trigger();
            
    } else {
        $message = get_string('cannotsavedata', 'error');   
    }

    return $message;
} 

/**
 * Assign / Unassign students and tutors in a given exam
 * from a manually input form
 *
 * @param stdClass $examboard instance record
 * @param stdClass $fromform user input from mform
 * @return string success/error notification message 
*/
function examboard_process_updateuser($examboard, $fromform) {
    global $DB;
    
    $success = true;
    $message = '';
    
    $examid = $fromform->exam;
    $userid = 0;
    $params = array('examid' => $examid);
    $sortorder =  0;
    if($max = $DB->get_records_menu('examboard_examinee', $params, 'sortorder DESC', 'id, sortorder', 0, 1)) {
        $sortorder = reset($max) + 1;
    }

    $now = time();
    $user = new stdClass();
    $user->examid =  $examid;
    $user->timecreated = $now;
    $user->timemodified = $now;
    
    $tutor = new stdClass();
    $tutor->examid =  $examid;
    $tutor->timecreated = $now;
    $tutor->timemodified = $now;

    $context = context_module::instance($examboard->cmid);
    // event params.
    $eventparams = array(
        'context' => $context,
        'objectid' => $examid,
        'other' => array('examboardid'=>$examboard->id, 
                        'examinee' => $fromform->examinee),
    );
    
    $params['userid'] = $fromform->examinee;
    if(!$oldexaminee = $DB->get_record('examboard_examinee', $params)) {
    //if($fromform->examinee && !$DB->record_exists('examboard_examinee', $params)) {
        $user->sortorder = $sortorder;
        $user->userid = $fromform->examinee;
        $user->userlabel = $fromform->userlabel;
        $user->excluded = $fromform->excluded;
        $eid = $DB->insert_record('examboard_examinee', $user);
        $success = $success && $eid;
        //$userid = $fromform->examinee;
    } else {
        $oldexaminee->userlabel = $fromform->userlabel;
        $oldexaminee->excluded = $fromform->excluded;
        $oldexaminee->timemodified = $now;
        $eid = $DB->update_record('examboard_examinee', $oldexaminee);
        $success = $success && $eid;
    }
    
    $eventparams['relateduserid'] = $fromform->examinee;
    $event = \mod_examboard\event\examinee_updated::create($eventparams);
    $event->trigger();
    
    $oldtutor = $DB->get_record('examboard_tutor', $params + array('main' => 1)); 
    // if main tutor has changed or deleted, remove
    if(!(isset($fromform->tutor) && $fromform->tutor) || (isset($oldtutor->id) && $fromform->tutor != $oldtutor->tutorid )) {
        // deleted on form
        if(isset($fromform->others) && isset($oldtutor->tutorid) && in_array($oldtutor->tutorid, $fromform->others)) {
            //now is a other tutor, update it
            $oldtutor->main = 0;
            $oldtutor->timemodified = $now;
            $success = $success && $DB->update_record('examboard_tutor', $oldtutor);
        } elseif(isset($oldtutor->id)) {
            // should be deleted
            $DB->delete_records('examboard_tutor', $params + array('id' => $oldtutor->id));
        }
    }
    // if main tutor has changed is managed above, here we can add safely without duplication
    if(isset($fromform->tutor) && $fromform->tutor && (!$oldtutor || ($fromform->tutor != $oldtutor->tutorid))) {
        if($DB->record_exists('examboard_tutor', $params + array('tutorid'=>$fromform->tutor))) {
            $tid = $DB->set_field('examboard_tutor', 'main', 1, $params + array('tutorid'=>$fromform->tutor));
        } else {
            // we need to insert
            $tutor->userid = $fromform->examinee;
            $tutor->tutorid = $fromform->tutor;
            $tutor->main = 1;
            $tid = $DB->insert_record('examboard_tutor', $tutor);
            $eventparams['relateduserid'] = $tutor->tutorid;
            $event = \mod_examboard\event\tutor_updated::create($eventparams);
            $event->trigger();
        }
        $success = $success && $tid;
    }

    
    
    // this include old main tutor changed to other
    $oldothers = $DB->get_records_menu('examboard_tutor', $params + array('main' => 0), '', 'id,tutorid'); 
    
    if(isset($fromform->others)) {
        $tutor->main = 0;
        $tutor->userid = $fromform->examinee;
        foreach($fromform->others as $otherid) {
            $key = array_search($otherid, $oldothers);
            if($key !== false) {
                // existing do nothing
                unset($oldothers[$key]);
            } else {
                // is new, insert
                $tutor->tutorid = $otherid;
                $tid = $DB->insert_record('examboard_tutor', $tutor);
                $success = $success && $tid;
                $eventparams['relateduserid'] = $tutor->userid;
                $eventparams['other']['tutor'] = $tutor->tutorid;
                $event = \mod_examboard\event\tutor_updated::create($eventparams);
                $event->trigger();
            }
        }
    }
    
    if($oldothers) {
        $DB->delete_records_list('examboard_tutor', 'id', array_keys($oldothers));
    }

    $success = $success && examboard_reorder_examinees($examid, $fromform->userorder);
        
    if($success) {
        $message = get_string('changessaved');
        
        $eventparams['relateduserid'] = $fromform->examinee;
        unset($eventparams['other']['tutor']);
        $event = \mod_examboard\event\exam_updated_users::create($eventparams);
        $event->trigger();
        
    } else {
        $message = get_string('cannotsavedata', 'error');   
    }

    return $message;
} 



function examboard_reorder_examinees($examid, $userorder) {
    global $DB;

    $success = true;
    
    //ordering  of users fromform
    if($users = examboard_get_exam_examinees($examid)) {
    
        switch($userorder) {
            case EXAMBOARD_ORDER_RANDOM : // order randomize
                    shuffle($users);
                    break;
            case EXAMBOARD_ORDER_ALPHA  : // order alphabetic
                    //names are not in the array, use DB to sort
                    $names = array();
                    foreach($users as $user) {
                        $names[$user->userid] = $user;
                    }
                    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($names));
                    $users = $DB->get_records_select_menu('user', " id $insql ", $inparams, 'lastname, firstname', 'id, idnumber'); 
                    foreach($users as $id => $idnumber) {
                        $users[$id] = $names[$id];
                    }
                    // now reindex base zero
                    $users = array_values(array_filter($users)); 
                    break;
            case EXAMBOARD_ORDER_LABEL  : // order alphabetic by label     
                    $params = array('examid' => $examid);
            
                    $sql = "SELECT e.id, e.examid, e.userid, e.sortorder
                            FROM {examboard_examinee} e 
                            JOIN {user} u ON u.id = e.userid 
                            WHERE e.examid = :examid 
                            ORDER BY e.userlabel, u.lastname, u.firstname, u.idnumber
                            ";
                    $users = $DB->get_records_sql($sql, $params);
            
/*                    
                    $names = array();
                    foreach($users as $user) {
                        $names[$user->userid] = $user;
                    }              
                    $users = $DB->get_records_menu('examboard_examinee', $params, 'userlabel ASC', 'userid, sortorder');
                    
                    foreach($users as $id => $idnumber) {
                        $users[$id] = $names[$id];
                    }
                
  */                  
                    // now reindex base zero
                    $users = array_values(array_filter($users));
                    break;
            case EXAMBOARD_ORDER_TUTOR  : // order alphabetic by tutor
                    //names are not in the array, use DB to sort
                    $names = array();
                    foreach($users as $user) {
                        $names[$user->userid] = $user;
                    }
                    list($insql, $params) = $DB->get_in_or_equal(array_keys($names), SQL_PARAMS_NAMED, 'n');
                    
                    $sql = "SELECT e.userid, u.idnumber 
                            FROM {examboard_examinee} e  
                            JOIN {examboard_tutor} t ON e.examid = t.examid AND e.userid = t.userid AND main = 1
                            JOIN {user} u ON u.id = t.tutorid
                            WHERE e.examid = :examid AND e.userid $insql
                            ORDER BY u.lastname ASC, u.firstname ASC, u.idnumber ASC ";
                    $params['examid'] = $examid;
                    $users = $DB->get_records_sql_menu($sql, $params); 
                    foreach($users as $id => $idnumber) {
                        $users[$id] = $names[$id];
                    }
                    // now reindex base zero
                    $users = array_values(array_filter($users)); 
                    break;
            case EXAMBOARD_ORDER_KEEP   : // keep order. remove holes
            default : $users = array_values(array_filter($users));                    
        }
        
        // array must have keys indexed from 0 (done by shuffle or array_values() )
        
        foreach($users as $order => $user) {
            $user->sortorder = $order;
            $success = $success && $DB->update_record('examboard_examinee', $user);
        }
    }

    return $success;
}

/**
 * Move students and tutors in a given exam to a difefrente exam (session)
 * from a manually input form
 *
 * @param stdClass $examboard instance record
 * @param stdClass $fromform user input from mform
 * @return string success/error notification message 
*/
function examboard_process_change_user_session($examboard, $fromform) {
    global $DB;
    
    $success = true;
    $message = '';
    
    if(!$fromform->users || !$fromform->targetexam) {
        \core\notification::add(get_string('movetoerror', 'examboard'), \core\output\notification::NOTIFY_ERROR);
        return;
    }
    
    $params = array('id' => $fromform->targetexam, 'examboardid' => $examboard->id);
    $targetexam = $DB->get_record('examboard_exam', $params);
    $now = time();
    unset($params['examboardid']);
    $last = $DB->count_records('examboard_examinee', $params);
    
    
    $context = context_module::instance($examboard->cmid);
    // event params.
    $eventparams = array(
        'context' => $context,
        'objectid' => $targetexam->id,
        'other' => array('examboardid'=>$examboard->id)
    );    
    
    $moved = 0;
    $conflicts = array();
    
    foreach($fromform->users as $userid) {
        // checkconflicts 
        $params = array('examid'=>$fromform->exam , 'userid' => $userid);
        $examinee = $DB->get_record('examboard_examinee', $params);
        
        if(!$c = examboard_get_exam_board_conflicts($fromform->exam, array($targetexam->boardid), $userid)) {
            $examinee->examid = $targetexam->id;
            $examinee->sortorder = $last + $moved;
            $examinee->timemodified = $now;
            if($DB->update_record('examboard_examinee', $examinee)) {
                $moved++;
                $DB->set_field('examboard_tutor', 'examid', $targetexam->id, $params);
                $eventparams['relateduserid'] = $userid;
                $event = \mod_examboard\event\examinee_updated::create($eventparams);
                $event->trigger();
            }
        } else {
            $conflicts[] = $userid;
        }
    }
    // now event in original exam
    $eventparams['objectid'] = $fromform->exam;
    $eventparams['relateduserid'] = 0;
    $event = \mod_examboard\event\exam_updated_users::create($eventparams);
    $event->trigger();
    
    if($conflicts) {
        $examination = \mod_examboard\examination::get_from_id($fromform->exam);
        $users = $examination->load_examinees();
        foreach($users as $uid => $user) {
            $users[$uid] = fullname($user);
        }
        \core\notification::add(get_string('movetoconflicts', 'examboard', html_writer::alist($users)), \core\output\notification::NOTIFY_ERROR);
    }
    if($moved) {
        $message = get_string('movetomoved', 'examboard', $moved);
    }
    
    return $message;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////



//////// older code



/////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Construct SQl to find the boards belonging in this module and  accesible to this user
 * either the user is participating or can view all
 *
 * @param stdClass $examboard the instance id, at least id & usetutors
 * @param bool $viewall if user can access all examinations or not
 * @param string $sortorder field to sort by
 * @param int $onlyuser if only exams where this user participate
 * @param int $groupid if only exams of users belonging to this group
 * @param bool $count if used to count records rather than retrieving them 
 * @return array tuple of $sql, params
 */
function examboard_get_user_exams_sql($examboard, $viewall, $sortorder, $onlyuser = 0, $groupid = 0, $extrafilters = '', $count = false) {
    global $DB, $USER; 
    $params = array('examboardid' => $examboard->id);
    
    if(!$onlyuser) {
        $userid = $USER->id;
    } else {
        $userid = $onlyuser;
    }
    
    $whereuserparticipate = '';
    if(!$viewall || $groupid || $onlyuser) {
        $where = array();
        foreach(array('m' => 'member', 'u' => 'examinee') as $k => $user) {
            $join = '';
            if($groupid) {
                $join = "JOIN {groups_members} gm ON gm.groupid = :groupid$k AND gm.userid = $k.userid";
                $params["groupid$k"] = $groupid;
            }
            if(!$viewall || $onlyuser) {
                $condition = " = :$user ";
                $params[$user] = $userid;
            } else {
                $condition = " != 0 ";
            }
             
            $wherejoin = ($k == 'm') ? 'm.boardid = e.boardid' : $k.'.examid = e.id'; 
            
            $where[] = "EXISTS(SELECT 1 FROM {examboard_$user} $k 
                        $join
                        WHERE $wherejoin AND $k.userid $condition ) ";
        }
            
        if($examboard->usetutors) {
            $join = '';
            if($groupid) {
                $join = "JOIN {groups_members} gm ON gm.groupid = :groupidt AND gm.userid = t.userid ";
                $params["groupidt"] = $groupid;
            }
            if(!$viewall || $onlyuser) {
                $condition = " = :tutor ";
                $params['tutor'] = $userid;
            } else {
                $condition = " != 0 ";
            }
            
            $where[] = "EXISTS(SELECT 1 FROM {examboard_tutor} t 
                        $join
                        WHERE t.examid = e.id AND t.tutorid $condition ) ";
        }
        $whereuserparticipate = ' AND  ( '.implode(' OR ', $where). ' ) ';
    }    
    
    $onlyactive = '';
    if(!$viewall) {
        $onlyactive = ' AND e.active = 1 ';
    }
    
    $wherefilters = array();
    if($extrafilters) {
        $i = 1;
        foreach($extrafilters as $field => $value) {
            $param = "f$i";
            $wherefilters[] = " $field = :$param ";
            $params[$param] = $value;
            $i++;
        }
    }
    $wherefilters = $wherefilters ? ' AND '.implode(' AND ', $wherefilters) : '';
    
    if($sortorder) {
        $sortorder = " $sortorder , ";
    }
 
    if($count) {
        $fields = "SELECT COUNT(e.id) ";
    } else {
        $fields = "SELECT e.*, e.active AS examactive, b.id AS boardid, b.title, b.groupid, b.name, b.idnumber, b.active AS boardactive ";
    }
    
    $sql = "$fields
            FROM {examboard_exam} e
            JOIN {examboard_board} b ON e.examboardid = b.examboardid AND e.boardid = b.id
        WHERE e.examboardid = :examboardid 
        $whereuserparticipate $onlyactive $wherefilters
        ORDER BY $sortorder e.id, e.timemodified ASC  ";

    return array($sql, $params);

}


/**
 * Finds the boards belonging in this module and  accesible to this user
 * either the user is participating or can view all
 *
 * @param object $examboard record conting examboard data, at least id & usetutors
 * @param bool $viewall if iser can access all examinations or not
 * @param string $sort field to sort by
 * @param int $onlyuser if only exams where this user participate
 * @param int $groupid if only examd of users belonging to this group
 * @param bool $returnobj true to make & return examination objects
 * @return array of DB records or examination objects
 */
function examboard_count_user_exams($examboard, $viewall, $onlyuser = 0, $groupid = 0, $extrafilters = '') {
    global $DB;
    
    list($sql, $params) = examboard_get_user_exams_sql($examboard, $viewall, '',  $onlyuser, $groupid, $extrafilters, true);
    return $DB->count_records_sql($sql, $params);
}

/**
 * Finds the boards belonging in this module and  accesible to this user
 * either the user is participating or can view all
 *
 * @param object $examboard record conting examboard data
 * @param bool $viewall if iser can access all examinations or not
 * @param string $sort field to sort by
 * @param int $onlyuser if only exams where this user participate
 * @param int $groupid if only examd of users belonging to this group
 * @param bool $returnobj true to make & return examination objects
 * @return array of DB records or examination objects
 */
function examboard_get_user_exams($examboard, $viewall, $onlyuser = 0, $groupid = 0, $sortorder, $extrafilters = '', $limitfrom = 0, $limitnum = 0, $returnobj = false) {
    global $DB, $USER; 
    
    list($sql, $params) = examboard_get_user_exams_sql($examboard, $viewall, $sortorder, $onlyuser, $groupid, $extrafilters);
    
    if($returnobj) {
        $exams = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
        foreach($exams as $key => $examdata) {
            $exams[$key] = new \mod_examboard\examination($examdata);
        }
        return $exams;
    }
    
    return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
}


/**
 * Retrieve tutors of a given examid as array indexed by userid
 *
 * @param int $examid the exam id in BD
 * @return object a mix of exam and board records
 */
function examboard_get_exam_tutors($examid) {
    global $DB;
    
    return $DB->get_records_menu('examboard_tutor', array('examid' => $examid, 'main' => 1), 'id', 'userid, tutorid');
}


/**
 * Retrieve exam records qualified with board data
 *
 * @param int $examid the exam id in BD
 * @return object a mix of exam and board records
 */
function examboard_get_exam_with_board($examid) {
    global $DB;
    
    $params = array('examid' => $examid);
    $sql = "SELECT e.*, e.active AS examactive, b.title, b.name, b.idnumber, b.active AS boardactive
            FROM {examboard_exam} e
            JOIN {examboard_board} b ON e.examboardid = b.examboardid AND e.boardid = b.id

        WHERE e.id = :examid";

    return $DB->get_record_sql($sql, $params, MUST_EXIST);
}


/**
 * Assign students/tutors from a textfield input to existing exams
 *
 * @param stdClass $examboard instance record
 * @param stdClass $fromform user input from mform
 * @return string success/error notification message 
*/
function examboard_process_userassign($examboard, $fromform) {
   global $DB;

   
    $lines = explode("\n", $fromform->userassignation);
   
    if(!$fromform->userassignation || count($lines) < 2) {
        \core\notification::error(get_string('noinputdata', 'examboard'));
        return; 
    }

    $errors = false;
    
    $delimiter = array("|",",",";");
    $line = array_shift($lines);
    $replace = str_replace($delimiter, $delimiter[0], trim($line));
    $items = array_unique(explode($delimiter[0], $replace));
    $fields = array('user' => get_string('userid', 'examboard'),
                    'boardidnumber' => get_string('boardidnumber', 'examboard'),
                    'tutor' => get_string('maintutor', 'examboard'),
                    'othertutors' => get_string('othertutors', 'examboard'),
                    );
    foreach($items as $k => $item) {
        if($field = array_search(trim($item), $fields)) {
            $items[$k] = $field;
        } else {
            $errors = true; // if key not known is an error
        }
    }
    
    if($errors) {
        \core\notification::error(get_string('errorfieldcolumns', 'examboard'));
        return;
    }
   
    $examsuseradded = array();
    $skipped = array();
    $now = time();
    $examinee = new stdClass();
    $examinee->timecreated = $now;
    $examinee->timemodified = $now;
    $count = 0;

    $context = context_module::instance($examboard->cmid);
    // event params.
    $eventparams = array(
        'context' => $context,
        'other' => array('examboardid'=>$examboard->id),
    );

    
    foreach($lines as $line) {
        $fields = array();
        $replace = str_replace($delimiter, $delimiter[0], trim($line));
        $parts = array_unique(explode($delimiter[0], $replace));
        foreach($parts as $k => $part) {
            $fields[$items[$k]] = trim($part);
        }
        
        // do not check for MUST_EXIST, simply ignore bad values, not stop processing
        // do not test empty values, may produce duplicated
        if(isset($fields['user']) && $fields['user'] &&
                        $userid = $DB->get_field('user', 'id', array($fromform->uidfield => $fields['user']))) {
            
            if(isset($fields['boardidnumber']) && 
                        $boardid = $DB->get_field('examboard_board', 'id', 
                                        array('examboardid' => $examboard->id,  'idnumber' => $fields['boardidnumber']))) {
                                        
                $members =  $DB->get_records_menu('examboard_member', array('boardid' => $boardid), 'id', 'id, userid');                       
                $tutors = array();
                if(isset($fields['tutor']) && $fields['tutor'] &&
                            $tutorid = $DB->get_field('user', 'id', array($fromform->uidfield => $fields['tutor']))) {
                    $user = new stdClass();        
                    $user->tutorid = $tutorid;
                    $user->main = 1;
                    $tutors[$tutorid] = $user;        
                }
                if(isset($fields['othertutors'])) {
                    foreach(explode(' ', $fields['othertutors']) as $tutor) {
                        $tutor = trim($tutor); 
                        if($tutor && $tutorid = $DB->get_field('user', 'id', array($fromform->uidfield => $tutor))) {
                            $user = new stdClass();
                            $user->tutorid = $tutorid;
                            $user->main = 0;
                            if(!isset($tutors[$tutorid])) {
                                $tutors[$tutorid] = $user;     
                            }
                        }
                    }
                }
                $skip = false;
                if($fromform->tutorcheck && array_intersect($members, array_keys($tutors))) { 
                    $skip = true;
                    $skipped[] = $line;
                }
                
                if(!$skip) { 
                    //assign this user to all exams using this boardid             
                    $params = array('examboardid' => $examboard->id, 'boardid' => $boardid);
                    foreach(array('examperiod', 'sessionanme') as $extra) {
                        if(isset($fields[$extra])) {
                            $params[$extra] = $fields[$extra];
                        }
                    }
                    
                    $exams = $DB->get_records_menu('examboard_exam', $params, 'id', 'id, boardid');
                    foreach($exams as $examid => $boardid) {
                        $params = array('userid' => $userid, 'examid' =>$examid);
                        if(!$DB->record_exists('examboard_examinee', $params)) {
                            $examinee->userid = $userid;
                            $examinee->examid = $examid;
                            
                            if($DB->insert_record('examboard_examinee', $examinee)) {
                                $examsuseradded[$examid][] = $userid;
                                $count++;
                                
                                $eventparams['objectid'] = $examid;
                                $eventparams['relateduserid'] = $userid;
                                $event = \mod_examboard\event\examinee_updated::create($eventparams);
                                $event->trigger();  
                                
                            }
                        }
                        // now tutors, if existing
                        foreach($tutors as $tutor) {
                            $params['tutorid'] = $tutor->tutorid;
                            if(!$rec = $DB->record_exists('examboard_tutor', $params)) {
                                $tutor->userid = $userid;
                                $tutor->examid = $examid;
                                $tutor->timecreated = $now;
                                $tutor->timemodified = $now;
                                $tutor->id = $DB->insert_record('examboard_tutor', $tutor);
                            } else {
                                $DB->set_field('examboard_tutor', 'main', $tutor->main, $params);
                                $DB->set_field('examboard_tutor', 'timemodified', $now, $params);
                            }
                            
                            
                        }
                    }
                }
                
                
            }
        } elseif(trim($line)) {
            $skipped[] = $line;
        }
    }
    
    // OK, now take care of sortorder
    $students = array();
    foreach($examsuseradded as $examid => $users) {
        examboard_reorder_examinees($examid, $fromform->userorder);
        $students += $users;
        $eventparams['objectid'] = $examid;
        unset($eventparams['relateduserid']);
        $event = \mod_examboard\event\exam_updated_users::create($eventparams);
        $event->trigger();

    }

    if($skipped) {
        $message = get_string('skippedlines', 'examboard').
                    html_writer::nonempty_tag('blockquote', implode('<br />', $skipped));
        \core\notification::error($message);
    }
    
    $info = new stdClass();
    $info->count = $count;
    $info->exams = count($examsuseradded);
    $info->users = count(array_unique($students));
    $message = get_string('assignednusers', 'examboard', $info);
    
    
    
    return $message;
}

/**
 * Sets the confirmation status for board members 
 *
 * @param stdClass $examboard instance record
 * @param stdClass $fromform user input from mform
 * @return string success/error notification message 
*/
function examboard_process_toggleconfirm($examboard, $fromform) {
    global $DB;
    
    $un = $fromform->confirmed ? '' : 'un';
    $now = time();
    
    if($confirmation = $DB->get_record('examboard_confirmation', 
                                    array('examid'=>$fromform->exam, 'userid'=>$fromform->user))) {
    } else {
        $confirmation = new stdClass();
        $confirmation->examid = $fromform->exam;
        $confirmation->userid = $fromform->user;
        $confirmation->timecreated = $now;
    }
    
    $confirmation->confirmed = $fromform->confirmed;
    $confirmation->exemption = $fromform->exemption;
    if(!$fromform->confirmed) {
        $confirmation->discharge = $fromform->discharge;
        $confirmation->dischargetext = $fromform->discharge_editor['text'];
        $confirmation->dischargeformat = $fromform->discharge_editor['format'];
        $confirmation->available = $fromform->available;
    }
    
    if($fromform->confirmed) {
        $confirmation->timeconfirmed = $now;
    } else {
        $confirmation->timeunconfirmed = $now;
    }
    
    if(isset($confirmation->id)) {
        $success = $DB->update_record('examboard_confirmation', $confirmation);
    } else {
        $success = $DB->insert_record('examboard_confirmation', $confirmation);
    }
    
    $message = '';
    if($success) {
        $message = get_string($un.'confirmexam', 'examboard');
        
        $context = context_module::instance($examboard->cmid);
        // event params.
        $eventparams = array(
            'context' => $context,
            'objectid' => $confirmation->examid,
            'other' => array('examboardid'=>$examboard->id),
        );
        $event = \mod_examboard\event\member_confirmed::create($eventparams);
        $event->trigger();
        
    } else {
        \core\notification::error(get_string('noconfirmsave', 'examboard'));
    }
    
    return $message;
}    

function  examboard_process_reminders($examboard) {
    global $CFG, $DB, $USER;

    if(!$examboard->usewarnings) {
        mtrace("    ...Examboard instance without active warnings:  {$examboard->id} '{$examboard->id}' "); 
        return 0;
    }
    
    $now = time();
    $params = array('examboardid' => $examboard->id, 
                    'now' => $now, 
                    'limit' => ($now + $examboard->warntime),
                    );
    $sql = "SELECT e.*, e.active As examactive,  b.title, b.idnumber, b.	name, b.groupid, b.active AS boardactive 
            FROM {examboard_exam} e
            JOIN {examboard_board} b ON b.examboardid = e. examboardid AND b.id = e.boardid
            WHERE e.examboardid = :examboardid AND e.examdate > :now AND e.examdate < :limit  ";
            
    $exams = $DB->get_records_sql($sql, $params);
    
    if(empty($exams)) {
        mtrace("    ...Examboard instance without target exams:  {$examboard->id} '{$examboard->id}' "); 
        return 0;
    }
    
    $sent = array();
    $noreplyuser = core_user::get_noreply_user();

    list ($course, $cm) = get_course_and_cm_from_instance($examboard->id, 'examboard'); 
    $context = context_module::instance($cm->id);
    $url = new moodle_url('/mod/examboard/view.php', array('id' => $cm->id));
    
    $info = new stdClass();
    $info->shortname = $course->shortname;
    $info->modname = format_string($examboard->name);
    $info->link = html_writer::link($url, $info->modname);
   
    foreach($exams as $examid => $examrec) {
        $exam = new \mod_examboard\examination($examrec);
        $usertypes = array();
        $info->title = $exam->title;
        $info->idnumber = $exam->idnumber;
        $subject = get_string('remindersubject', 'examboard', $info);
        
        $info->examdate = userdate($exam->examdate);
        $info->venue = $exam->venue;
       
        switch($examboard->usewarnings) {
            case EXAMBOARD_USERTYPE_USER: 
                    $label = get_string('examinees', 'examboard');
                    $usertypes[EXAMBOARD_USERTYPE_USER] = $exam->load_examinees(' e.excluded = 0 ');
                    
                    break;
                    
            case EXAMBOARD_USERTYPE_MEMBER: 
                    $label = get_string('examiners', 'examboard');
                    $usertypes[EXAMBOARD_USERTYPE_MEMBER] = $exam->load_board_members(" (c.exemption = 0 OR c.exemption IS NULL) AND m.deputy = 0 ");
                    
                    break;
                    
            case EXAMBOARD_USERTYPE_TUTOR: 
                    $label = get_string('tutors', 'examboard');
                    $usertypes[EXAMBOARD_USERTYPE_TUTOR] = $exam->load_tutors(' t.approved <> 0 ');
                    
                    break;
                    
            case EXAMBOARD_USERTYPE_STAFF: 
                    $label = get_string('staff', 'examboard');
                    $usertypes[EXAMBOARD_USERTYPE_MEMBER] = $exam->load_board_members(" (c.exemption = 0 OR c.exemption IS NULL) AND m.deputy = 0 ");
                    $usertypes[EXAMBOARD_USERTYPE_TUTOR] =  $exam->load_tutors(' t.approved <> 0 ');
                    
                    break;
                    
            case EXAMBOARD_USERTYPE_ALL: 
                    $label = get_string('allusers', 'examboard');
                    $usertypes[EXAMBOARD_USERTYPE_USER] = $exam->load_examinees(' e.excluded = 0 ');
                    $usertypes[EXAMBOARD_USERTYPE_MEMBER] = $exam->load_board_members(" (c.exemption = 0 OR c.exemption IS NULL) AND m.deputy = 0 ");
                    $usertypes[EXAMBOARD_USERTYPE_TUTOR] = $exam->load_tutors(' t.approved <> 0 ');
                    
                    break;
        }
        $info->usertype = $label;
        
        // we need to reformat turtors array
        $users = array();
        if(isset($usertypes[EXAMBOARD_USERTYPE_TUTOR])) {
            foreach($usertypes[EXAMBOARD_USERTYPE_TUTOR] as $uid => $tutors) {
                foreach($tutors as $tutor) {
                    $users[$tutor->tid] = $tutor;
                }
            }
            $usertypes[EXAMBOARD_USERTYPE_TUTOR] = $users;
        }
        
        foreach($usertypes as $usertype => $users) {
            $role = examboard_usertype_string($usertype);
            $asrole = get_string('reminderas', 'examboard', $role); 
            $info->role = $role;
            $messagehtml = get_string('reminderbody', 'examboard', $info);
            
            foreach($users as $user) {
                $name = fullname($user);
                $username = get_string('remindername', 'examboard', $name);
                $messagetext = html_to_text($username.$messagehtml);                
                if(email_to_user($user, $noreplyuser, $subject.$asrole, $messagetext, $username.$messagehtml)) {
                    $error = '';
                } else {
                    $error = get_string('emailfail'. 'error');
                }
                $sent[] = $name.' - '.$exam->idnumber." ($role)".'  '.$error;  
            }
        }
    }
    
    // send control email to manager user
    $info->count = count($sent);
    $subject = get_string('remindercontrolsubject', 'examboard', $info);
    $messagehtml = get_string('remindercontrolbody', 'examboard',  $info);
    $messagehtml .= '<br />'.html_writer::link($url, format_string($examboard->name)); 
    $messagehtml .= "<br />\n".implode("<br />\n", $sent);
    $messagetext = html_to_text($messagehtml);

    $users = get_users_by_capability($context, 'mod/examboard:manage');
    foreach($users as $user) {
        email_to_user($user, $noreplyuser, $subject, $messagetext, $messagehtml);
    }
    
    return $info->count;
}


function  examboard_process_notifications($examboard, $course, $cm, $context, $fromform) {
    global $CFG, $DB, $USER;
    
    $replaceable = array_fill_keys(array('firstname', 'lastname', 'fullname', 'role', 'idnumber', 
                                        'sessionname', 'accessurl', 'examdate', 'examdatetime', 'examtime', 'venue', 'duration', 'date',
                                        'students', 'committee'),
                                        '');
    foreach($replaceable as $key => $value) {
        $replaceable[$key] = '%%'.get_string('replace_'.$key, 'examboard').'%%';
    }
   
    $users = array();
    $attachname = '';
    $attachment =  '';
    
    $notification = new stdClass();
    $notification->managerid = $USER->id; 
    $notification->timeissued = time();
    $attachname = '';
    $sent = array();
    $usernames = get_all_user_name_fields();
    $noreplyuser = core_user::get_noreply_user();
        foreach($usernames as $field) {
        $noreplyuser->{$field} = $USER->{$field};
    }

    $url = new moodle_url('/mod/examboard/view.php', array('id' => $cm->id));
    
    $strnotifications = get_string('boardnotify', 'examboard');
    $fs = get_file_storage(); 
    $filerecord = array(
        'contextid' => $context->id,
        'component' => 'mod_examboard',
        'filearea'  => 'notification',
        'itemid'    => '',
        'filepath'  => '/',
        'filename'  => '',
        'source'  => $course->shortname. ' - '. $examboard->name,  
        'author'  => $strnotifications.' - '.get_string('pluginname', 'examboard'),
    );                    
    
    foreach($fromform->exams as $examid) {
        $exam = \mod_examboard\examination::get_from_id($examid);

        // fill board & students
        $deputy = $fromform->includedeputy ? '' : ' AND m.deputy = 0 ';
        $members = $exam->load_board_members(" (c.exemption = 0 OR c.exemption IS NULL) $deputy ");
        foreach($members as $key => $user) {
            $role = '';
            $members[$key] = fullname($user);
            if($user->sortorder == 0) {
                $role = $examboard->chair;
            } elseif($user->sortorder == 1) {
                $role = $examboard->secretary;
            } else {
                $role = $examboard->vocal.' '.($user->sortorder - 1);
            }
            if($role) {
                $members[$key] .= get_string('roletag', 'examboard', $role);
            }
            if($user->deputy) {
                $members[$key] .= get_string('deputytag', 'examboard');
            }
        }
        $board = html_writer::alist($members).html_writer::empty_tag('br');;
        
        $tutors = $exam->load_tutors();
        
        
        $examinees = $exam->load_examinees(' e.excluded = 0 ');
        foreach($examinees as $key => $user) {
            $examinees[$key] = fullname($user);
            if($tutors[$key]) {
                $tutornames = array();
                foreach($tutors[$key] as $tid =>$tutor) {
                    $tutornames[$tid] = fullname($tutor);
                }
                $examinees[$key] .= get_string('tutortag', 'examboard', implode(', ', $tutornames));
            }
        }
        
        $students = html_writer::alist($examinees).html_writer::empty_tag('br');
        
        $replaces = array($replaceable['idnumber'] => $exam->idnumber,
                            $replaceable['sessionname'] => $exam->sessionname,
                            $replaceable['accessurl'] => \html_writer::link( $exam->accessurl, get_string('accessurltext', 'examboard')),
                            $replaceable['examdatetime'] => userdate($exam->examdate),
                            $replaceable['examdate'] => userdate($exam->examdate, get_string('strftimedate', 'langconfig')),
                            $replaceable['examtime'] => userdate($exam->examdate, get_string('strftimetime24', 'langconfig')),
                            $replaceable['duration'] => format_time($exam->duration),
                            $replaceable['venue'] => $exam->venue,
                            $replaceable['students'] => $students,
                            $replaceable['committee'] => $board,
                            $replaceable['date'] => userdate(time(), get_string('strftimedate')),
                            );
                            
        $messagehtml = format_text($fromform->messagebody['text'],  $fromform->messagebody['format']);
        $messagehtml = str_replace(array_keys($replaces), array_values($replaces), $messagehtml);
        
        $link = html_writer::link($url, $examboard->name);
        $messagehtml .= get_string('notification_moreinfo', 'examboard', $link);
        
        // OK, now define this exam users to notify
        $users = array();
                            
        if($fromform->usertype == EXAMBOARD_USERTYPE_MEMBER ||
            $fromform->usertype == EXAMBOARD_USERTYPE_STAFF ||
            $fromform->usertype == EXAMBOARD_USERTYPE_ALL ) { 
            $deputy = $fromform->includedeputy ? '' : ' AND m.deputy = 0 ';
            $members = $exam->load_board_members(" (c.exemption = 0 OR c.exemption IS NULL) $deputy ");
            foreach($members as $key => $user) {
                $user->type = 'board';
                $users[$key] = $user;
            }
        }
        if($fromform->usertype == EXAMBOARD_USERTYPE_TUTOR ||
            $fromform->usertype == EXAMBOARD_USERTYPE_STAFF ||
            $fromform->usertype == EXAMBOARD_USERTYPE_ALL ) { 
            $tutors = $exam->load_tutors();
            foreach($tutors as $key => $usertutors) {
                foreach($usertutors as $user) {
                    $user->type = 'tutor';
                    $users[$tutor->tutorid] = $user;
                }
            }
        }
        if($fromform->usertype == EXAMBOARD_USERTYPE_USER ||
            $fromform->usertype == EXAMBOARD_USERTYPE_ALL ) { 
            $examinees = $exam->load_examinees(' e.excluded = 0 ');
            
            $users += $examinees;
        }
        
        $subject = $course->shortname.': '.$fromform->messagesubject.'  ('.$exam->idnumber.')';

        foreach($users as $user) {
            $role = $examboard->examinee;
            if($user->type == 'board') { 
                if($user->sortorder == 0) {
                    $role = $examboard->chair;
                } elseif($user->sortorder == 1) {
                    $role = $examboard->secretary;
                } else {
                    $role = $examboard->vocal.' '.($user->sortorder - 1);
                }
                if($user->deputy) {
                    $role .= ' '.get_string('deputy', 'examboard'); 
                }
            } elseif($user->type == 'tutor') { 
                $role = $examboard->tutor;
            }
        
            $replaces = array($replaceable['firstname'] => $user->firstname, 
                                $replaceable['lastname'] => $user->lastname,
                                $replaceable['fullname'] => fullname($user, false, 'firstname'),
                                $replaceable['role'] => $role,);
        
            $messagehtmluser = str_replace(array_keys($replaces), array_values($replaces), $messagehtml);
            
        
            // process file attachment en tempdir
            $attachment = '';
            $attachname = '';
            $attachpath = '';
            
            if($fromform->includepdf) {
                $filename = clean_filename($course->shortname.'-'.
                                                $exam->idnumber.'-'.$exam->sessionname.'_'.
                                                $user->idnumber.'_'.$fromform->attachname.'.pdf');
                
                list($attachment, $attachpath) = examboard_generate_notification_pdf($examboard, $course, 
                                                        $messagehtmluser, $fromform->messagesender,
                                                        $fromform->logofile, $fromform->logowidth, $fromform->signaturefile, 
                                                        $fromform->tempdir.'/'. $filename);
                if($attachment) {
                    $attachname = clean_filename($fromform->attachname.'.pdf');
                }
            }

            $messagehtmluser .= '<br />--<br />'.$fromform->messagesender;
            $messagetext = html_to_text($messagehtmluser);
        
            if(email_to_user($user, $noreplyuser, $subject, $messagetext, $messagehtmluser, $attachment, $attachname)) {
                $notification->examid = $exam->id;
                $notification->userid = $user->id;
                $notification->role = $role;
                $error = '';
                if($nid = $DB->insert_record('examboard_notification', $notification)) {
                    if($attachment) {
                        // everything OK, now store file for user
                        $filerecord['itemid'] = $nid;
                        $filerecord['filename'] = basename($attachment);
                        $filerecord['userid'] = $notification->userid;
                        
                        $fs->create_file_from_pathname($filerecord, $attachpath.$attachment);
                    
                    } elseif($fromform->includepdf) {
                        $error = get_string('nofileattachment', 'examboard');
                    }
                } else {
                    $error = get_string('cannotinsertrecord', 'error');
                }
            } else {
                $error = get_string('emailfail'. 'error');
            }
            $sent[] = fullname($user).' - '.$exam->idnumber." ($role)".'  '.$error;  
        }
    }
    
    // send control email to manager user
    $info = new stdClass();
    $info->shortname = $course->shortname;
    $info->modname = $examboard->name;
    $info->usertype = examboard_usertype_string($fromform->usertype);
    $info->count = count($sent);
    $subject = get_string('controlemailsubject', 'examboard', $info);
    $messagehtml = get_string('controlemailbody', 'examboard',  $info);
    $messagehtml .= '<br />'.html_writer::link($url, format_string($examboard->name)); 
    $messagehtml .= "<br />\n".implode("<br />\n", $sent);
    $messagetext = html_to_text($messagehtml);
    
    email_to_user($USER, $noreplyuser, $subject, $messagetext, $messagehtml);
}


function examboard_usertype_string($usertype) {
    $label = '';
    switch($usertype) {
        case EXAMBOARD_USERTYPE_USER: $label = get_string('examinees', 'examboard');
                                    break;
        case EXAMBOARD_USERTYPE_MEMBER: $label = get_string('examiners', 'examboard');
                                    break;
        case EXAMBOARD_USERTYPE_TUTOR: $label = get_string('tutors', 'examboard');
                                    break;
        case EXAMBOARD_USERTYPE_STAFF: $label = get_string('staff', 'examboard');
                                    break;
        case EXAMBOARD_USERTYPE_ALL: $label = get_string('allusers', 'examboard');
                                    break;
    }
    return $label;
}

function  examboard_generate_notification_pdf($examboard, $course, 
                                                $maintext, $sender, $logo, $logowidth, $signature, $filename) {
    global $CFG, $DB, $USER;
    require_once($CFG->libdir.'/pdflib.php');
    
    $strnotifications = get_string('boardnotify', 'examboard');
    $titlename = $course->shortname.' - '.$course->fullname;

    $subject = $examboard->name .' - '. $strnotifications; 

    $attachpath = '';
    // this is need because email_to_user will add dataroot as prefix if not in tempdir
    if (strpos($filename, realpath($CFG->tempdir)) !== 0) {
        if (strpos($filename, realpath($CFG->dataroot)) === 0) {
            $filename = str_replace($CFG->dataroot.'/', '', $filename);
            $attachpath = $CFG->dataroot.'/';
        }
    }
    
    $pdf = new pdf();
    // set document information
    $pdf->SetCreator('Moodle '.get_string('pluginname', 'examboard'));
    $pdf->SetAuthor(fullname($USER));
    $pdf->SetTitle($titlename);
    $pdf->SetSubject($subject);
    $pdf->SetKeywords('moodle, examboard '.$strnotifications );
    
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);

    // set default header data
    //K_PATH_IMAGES.$headerdata['logo']
    $logoname = '';
    $newlogo = '';
    if($logo) {
        $logoname = 'mod/examboard/pix/temp/'.basename($logo);
        $newlogo = K_PATH_IMAGES.$logoname;
        copy($logo, $newlogo);
        if(!$logowidth) {
            $logowidth = 20; 
        }
    } else {
        $logowidth = 0;
    }

    $pdf->SetHeaderData($logoname, $logowidth, $titlename, $subject);

    // set header and footer fonts
    $pdf->setHeaderFont(array('helvetica', '', 12));
    $pdf->setFooterFont(array('helvetica', '', 8));
    
    // set header margins
    $topmargin = 25;
    $leftmargin = 10;
    $rightmargin = 25;
    $pdf->SetMargins($leftmargin, $topmargin, $rightmargin, true);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // set image scale factor
    $pdf->setImageScale(1.25);
    
    // set font
    $pdf->SetFont('freeserif', '', 12);
    
    // add content
    $pdf->AddPage('', '', true);
    
    // set margins
    $topmargin = 25;
    $leftmargin = 25;
    $rightmargin = 25;
    $pdf->SetMargins($leftmargin, $topmargin, $rightmargin, true);
    
    $pdf->Ln(10);
    $pdf->Ln(10);
    
    $pdf->writeHTML($maintext, false, false, true, false, '');
    $pdf->Ln(10);
    $pdf->Ln(10);
    
    
    $x = $pdf->getPageWidth()/2;
    $pdf->setX($x);
    $y = $pdf->GetY();
    if($signature) {
        $pdf->Image($signature, $x, $y, 0, 0, '', '', 'N');
    }
    $pdf->setX($x);
    $pdf->write(10, $sender, '', false, '', true);
    
    $success = $pdf->Output($attachpath.$filename, 'F');

    if($newlogo) {
        unlink($newlogo);
    }
    
    if(file_exists($attachpath.$filename)) {
        return array($filename, $attachpath);
    }
    
    return array();
}

function examboard_remove_exam($examid, $withboard = false) {
    global $DB;

    if($DB->record_exists_select('examboard_grades', ' examid = ? AND grade > 0 ', array($examid))) {
        return get_string('examhasgrades', 'examboard');
    }
    
    // delete records in dependent tables 
    $count = new stdClass();
    
    $DB->delete_records('examboard_notification', array('examid' => $examid));
    $DB->delete_records('examboard_confirmation', array('examid' => $examid));
    $count->tutors = $DB->count_records('examboard_tutor', array('examid' => $examid)); 
    $DB->delete_records('examboard_tutor', array('examid' => $examid));
    $count->users = $DB->count_records('examboard_examinee', array('examid' => $examid)); 
    $DB->delete_records('examboard_examinee', array('examid' => $examid));

    $deleted = get_string('deletedexam', 'examboard', $count);
    
    if($withboard) {
        $boardid = $DB->get_field('examboard_exam', 'boardid', array('id' => $examid));
        
        $sql = "SELECT 1
                FROM {examboard_exam} e 
                JOIN {examboard_grades} g ON e.id = g.examid
                WHERE e.id != :examid AND e.boardid = :boardid AND g.grade > 0";
        $params = array('examid' => $examid, 'boardid' => $boardid);
        if(!$DB->record_exists_select('examboard_exam', " id != :examid AND boardid = :boardid", $params)) {
            //OK, the board is not used in other exams, we can delete 
            $count->members = $DB->count_records('examboard_member', array('boardid' => $boardid)); 
            $deleted .= get_string('deletedboard', 'examboard', $count->members);
            $DB->delete_records('examboard_member', array('boardid' => $boardid));
            $DB->delete_records('examboard_board', array('id' => $boardid));
        }
    }
    
    $DB->delete_records('examboard_exam', array('id' => $examid));

    return $deleted;
}


function examboard_remove_user_from_exam($examboard, $examid, $userid) {
    global $DB;

    $success = true;
    
    $params = array('examid' => $examid, 'userid' => $userid);
    if(!$DB->record_exists_select('examboard_grades',  ' examid = :examid AND userid = :userid AND grade > 0 ', $params)) {
        $DB->delete_records('examboard_tutor', $params);
        
        $sortorder = $DB->get_field('examboard_examinee', 'sortorder', $params);
        $success = $success && $DB->delete_records('examboard_examinee', $params);
        
        // now set order, remove holes
        $users = $DB->get_records_select_menu('examboard_examinee', 'examid = :examid AND userid = :userid AND sortorder > :order ', 
                                                    $params + array('order'=>$sortorder), '', 'id,sortorder');
        foreach($users as $eid => $order) {
            $DB->set_field('examboard_examinee', 'sortorder', ($order - 1), array('id' => $eid));
        }
        
        $context = context_module::instance($examboard->cmid);
        // event params.
        $eventparams = array(
            'context' => $context,
            'objectid' => $examid,
            'other' => array('examboardid'=>$examboard->id),
        );
    
        $eventparams['relateduserid'] = $userid;
        $event = \mod_examboard\event\examinee_removed::create($eventparams);
        $event->trigger();
        
    
    } else {
        $success = false;
    }
    
    return $success;
}

function examboard_calculate_grades($grademode, $mingraders, $rawgrades) {
   
    if(!$rawgrades) {
        return -1;
    }
   
    if(count($rawgrades) < $mingraders) {
        return -2;
    }
    
    $grades = array();
    foreach($rawgrades as $grade) {
        $grades[] = $grade->grade;
    }
    
    if($grademode == EXAMBOARD_GRADING_MAX) {
        $grade = max($grades);
    } elseif($grademode == EXAMBOARD_GRADING_MIN) {
        $grade = min($grades);
    } else {
        $grade = array_sum($grades)/count($grades);
    }
    
    return $grade;
}



function examboard_process_save_submission($examboard, $itemid, $userid) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot . '/mod/examboard/submission_form.php');
    
    $params = array('userid' => $userid,
                    'cmid' => $examboard->cmid,
                    'action' => 'upload_submission', 
                    'item' => $itemid,
            );    
    $mform = new \examboard_submission_form(null, $params); //), 'post', '', array('class'=>'gradeform'));
    
    if ($mform->is_cancelled()) {
        return;
    }
    
    if ($formdata = $mform->get_data()) {
        $params = ['id' => $itemid, 'userid' => $userid];
        $submission = $DB->get_record('examboard_examinee', $params, 'id, userid, onlinetext, onlineformat', MUST_EXIST);
        
        $submission->onlinetext = $formdata->online['text'];
        $submission->onlineformat = $formdata->online['format'];
        $submission->timesubmitted = time();
        
        if($DB->update_record('examboard_examinee', $submission)) {
            \core\notification::add(get_string('submissionsaved', 'examboard'), \core\output\notification::NOTIFY_SUCCESS);
            $context = context_module::instance($examboard->cmid);
            file_save_draft_area_files($formdata->attachments, $context->id, 
                                        'mod_examboard', 'user', $itemid);
            
            // event params.
            $eventparams = array(
                'objectid' => $itemid,
                'context' => $context,
                'relateduserid' => $userid,
                'other' => array('examboardid '=> $examboard->id, 'examid' => $submission->examid),
            );            
            $event = \mod_examboard\event\user_submitted::create($eventparams);
            $event->trigger();
            
            // return now, not afterwards, meaning not saved.
            return; 
        }
    }
    
    \core\notification::add(get_string('submissionnotsaved', 'examboard'), \core\output\notification::NOTIFY_ERROR);
}


function examboard_process_save_grade($examboard, $examid, $userid) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot . '/mod/examboard/grading_form.php');
    $grade = examboard_get_grader_grade($examid, $userid);

    $gradingdisabled = examboard_grading_disabled($examboard, $userid);
    $gradinginstance = examboard_get_grading_instance($examboard, $userid, $grade, $gradingdisabled);
    
    $params = array('userid' => $userid,
                    'gradingdisabled' => $gradingdisabled,
                    'gradinginstance' => $gradinginstance,
                    'examboard' => $examboard,
                    'currentgrade' => $grade,
    );
    $mform = new \mod_examboard_grade_form(null, $params, 'post', '', array('class'=>'gradeform'));
    
    if ($mform->is_cancelled()) {
        return;
    }
    
    if ($formdata = $mform->get_data()) {
        if (!$gradingdisabled) {
            if ($gradinginstance) {
                $grade->grade = $gradinginstance->submit_and_get_grade($formdata->advancedgrading,
                                                                    $grade->id);
            } else {
                // Handle the case when grade is set to No Grade.
                if (isset($formdata->grade)) {
                    $grade->grade = grade_floatval(unformat_float($formdata->grade));
                }
            }
        }
        $grade->grader= $USER->id;
        $grade->timemodified = time();
    
        if($DB->update_record('examboard_grades', $grade)) {
            \core\notification::add(get_string('gradesaved', 'examboard'), \core\output\notification::NOTIFY_SUCCESS);
            
            $context = context_module::instance($examboard->cmid);
            // event params.
            $eventparams = array(
                'context' => $context,
                'other' => array('examboardid'=>$examboard->id),
            );            

            $event = \mod_examboard\event\user_graded::create_from_grade($eventparams, $grade);
            $event->trigger();      
            
            // return now, not afterwards, meaning not saved.
            return; 
        }
    }
    
    \core\notification::add(get_string('gradenotsaved', 'examboard'), \core\output\notification::NOTIFY_ERROR);
}



function examboard_import_export_fields($examboard, $export = false) {

    $mandatory = array('idnumber'       => get_string('boardidnumber', 'examboard'),
                        'examperiod'   => get_string('examperiod', 'examboard'), );
    
    $optional = array('title'           => get_string('boardtitle', 'examboard'),
                        'name'          => get_string('boardname', 'examboard'),
                        'group'         => get_string('accessgroup', 'examboard'),
                        'venue'         => get_string('examvenue', 'examboard'),
                        'accessurl'     => get_string('url', 'examboard'),
                        'sessionname'   => get_string('examsession', 'examboard'),
                        'examdate'      => get_string('examdate', 'examboard'),
                        'duration'      => get_string('examduration', 'examboard'),
                        'member'        => get_string('member', 'examboard'),
                        'role'          => get_string('memberrole', 'examboard'),
                        'deputy'        => get_string('deputy', 'examboard'),
                        'examinee'      => $examboard->examinee,
                        'userlabel'     => get_string('userlabel', 'examboard'),
                        'tutor'         => $examboard->tutor,
                        'othertutors'   => get_string('othertutors', 'examboard'),
                        'excluded'      => get_string('excluded', 'examboard'),
                        );

    if($export) {
        $export = array('boardactive'   => get_string('boardactivevis', 'examboard'),
                        'examactive'    => get_string('examactivevis', 'examboard'),
                        'exemption'     => get_string('exemption', 'examboard'),
                        'confirmed'     => get_string('boardstatus', 'examboard'),
                        //'notifications' => get_string('boardnotify', 'examboard'),
                        'sortorder'     => get_string('order', 'examboard'),
                        'grades'        => get_string('assessment', 'examboard'),
                        'excluded'      => get_string('excluded', 'examboard'),
                        'approved'      => get_string('approved', 'examboard'),
                       );
        $optional += $export;
    }

    return array($mandatory, $optional);
}

function examboard_process_csv_record($record, $columns, $fieldnames, $examboard, $periods) {
    global $DB;
    
    $texts = array('idnumber', 'examperiod', 'sessionname', 'title', 'name', 'venue', 'accessurl', 'userlabel');
    $users = array('member', 'examinee', 'tutor', 'othertutors');
    
    $data = (object)array_fill_keys(array_keys($fieldnames), '');
    
    foreach($record as $idx => $value) {
        // find if column name is in fieldnames
        if($key = array_search($columns[$idx], $fieldnames)) {
            if($value != '') {
                if(in_array($key, $texts)) {
                    $value = trim(format_string($value));
                    if($key == 'examperiod') {
                        $value = core_text::strtolower($value);
                        if(!isset($periods[$value]) &&  !$value = array_search($value, $periods)) {
                            $value = '-';
                        } 
                    }
                } elseif(in_array($key, $users)) {
                    if($key == 'othertutors') {
                    $value = str_replace(array(';','|',' '), ',', $value);
                    $idnumbers = explode(',', $value); 
                    } else {
                        $idnumbers = array($value);
                    }
                    foreach($idnumbers as $i => $idnumber) {
                        $idnumber = trim($idnumber);
                        if($idnumber && $userid = $DB->get_field('user', 'id', array('idnumber' => $idnumber))) {
                            $idnumbers[$i] = $userid;
                        } else {
                            unset($idnumbers[$i]);
                        }
                    }
                    $value = '';
                    if($idnumbers) {
                        $value = implode(',', $idnumbers);
                    }
                } elseif($key == 'examdate') {
                    $value = strtotime($value);
                } elseif($key == 'duration') {
                    $values = explode(':', str_replace(array(',','.'), ':', $value));
                    $value = $values[0] * 3600;
                    if(isset($values[1])) {
                        $value += $values[1] * 60;
                    }
                    if(isset($values[2])) {
                        $value += $values[2];
                    }
                } elseif($key == 'group') {
                    $value = $DB->get_field('groups', 'id', array('courseid'=>$examboard->course, 'name' => trim($value)));
                } elseif($key == 'deputy') {
                    if(($value == 1) || ($value == '1') || (core_text::strtolower($value) == core_text::strtolower(get_string('yes')))) {
                        $value = 1;
                    } elseif(($value == 0) || ($value == '0') || (core_text::strtolower($value) == core_text::strtolower(get_string('no')))) { 
                        $value = 0; 
                    } else {
                        $value = '';
                    }
                } elseif($key == 'role') {
                    if(core_text::strtolower($value) == core_text::strtolower($examboard->chair)) {
                        $data->sortorder = 0;
                    } elseif(core_text::strtolower($value) == core_text::strtolower($examboard->secretary)) {
                        $data->sortorder = 1;
                    } elseif(strpos(core_text::strtolower($value), core_text::strtolower($examboard->vocal)) !== false) {
                        // takes care of cases vocal1, Vocal2 etc
                        $order = (int)trim(str_replace(core_text::strtolower($examboard->vocal), '', core_text::strtolower($value)));
                        // just in case "vocal" without numeral
                        $order = max(1, $order); 
                        $data->sortorder = min(2, ($order + 1));
                    } else {
                        $value = '';
                    }
                }
            }
            $data->{$key} = $value;
        }
    }
    if(!$data->examinee) {
        //there MUST be an examinee to assign a tutor
        $data->tutor = '';
        $data->othertutors = '';
    }
    
    unset($record);
    return $data;
}


function examboard_insert_update_tabledata($table, $data) {
    global $DB;
    if(isset($data->id)) {
        // we are updating
        $DB->update_record($table, $data);
    } else {
        // we are inserting
        $data->id = $DB->insert_record($table, $data);
    }
    
    return $data->id;
}

function examboard_insert_update_data(&$data, $record, $now, $update, $table, $fields) {
    if(isset($data->id) && !$update) {
        return $data->id;
    }

    $data->timemodified = $now;
    foreach($fields as $field) {
        if(isset($record->{$field})) {
            $data->{$field} = $record->{$field};
        }
    }
    
    return examboard_insert_update_tabledata($table, $data);
}

function examboard_import_examinations($examboard, $returnurl, $csvreader,  $fromform) {
    global $CFG, $DB;
    
    $recordsadded = 0;

    //OK, go with importing
    if (!$columns = $csvreader->get_columns()) {
        return get_string('cannotreadtmpfile', 'error');
    }
    
    // check the fieldnames are valid
    list($mandatory, $optional) = examboard_import_export_fields($examboard);
    if($failures = array_diff($mandatory, $columns)) {
        return get_string('cannotreadtmpfile', 'examboard');
    }
    $fieldnames = $mandatory + $optional;
    $now = time();
    $deletedexams = array();
    $deletedboards = array();
    $options = get_config('examboard', 'examperiods');
    $examperiods = array();
    foreach(explode("\n", $options) as $conv) {
        $key = strstr(trim($conv), ':', true);
        $examperiods[$key] = ltrim(strstr($conv, ':'), ':');
    }
 
    $csvreader->init();
    while ($record = $csvreader->next()) {
        // Fill data_content with the values imported from the CSV file:
        // convert raw data to an object with cleaned & formatted data
        $record = examboard_process_csv_record($record, $columns, $fieldnames, $examboard, $examperiods);

        if(!$record->idnumber || !$record->examperiod) {
            //these fields are mandatory, if not present skip line
            continue;
        }
        
        //check if this board idnumber already exists, and add/update add needed
        $params = array('idnumber' => $record->idnumber, 'examboardid' => $examboard->id);
        //if exists & update //if nor exists insert
        $board = $DB->get_record('examboard_board', $params);
        if(!$board) {
            $board = new stdClass();
            $board->examboardid = $examboard->id;
            $board->idnumber = $record->idnumber;
        } else {
            if($fromform->deleteprevious && !isset($deletedboards[$board->id])) {
                if($DB->delete_records('examboard_member', array('boardid' => $board->id))) {
                    $deletedboards[$board->id] = 1;
                }
            }
        }
        $board->id = examboard_insert_update_data($board, $record, $now, $fromform->ignoremodified, 
                                                    'examboard_board', array('title', 'name', 'groupid'));
        
        //check if this examination already exists, , and add/update add needed
        $params = array('boardid' => $board->id, 'examperiod' => $record->examperiod, 'examboardid' => $examboard->id);
        if(isset($record->sessionname)) {
            $params['sessionname'] = $record->sessionname;
        }
        $exam = $DB->get_record('examboard_exam', $params);
        if(!$exam) {
            $exam = new stdClass();
            $exam->examboardid = $examboard->id;
            $exam->boardid = $board->id;
            $exam->examperiod = $record->examperiod;
        } else {
            if($fromform->deleteprevious && !isset($deletedexams[$exam->id])) {
                if($DB->delete_records('examboard_examinee', array('examid' => $exam->id))) {
                    $deletedboards[$board->id] = 1;
                }
            }
        }

        $exam->id = examboard_insert_update_data($exam, $record, $now, $fromform->ignoremodified, 
                                                    'examboard_exam', array('examperiod', 'sessionname', 'venue', 'accessurl', 'examdate', 'duration'));
       
        if($record->member) {
            $member = null;
            //the user is known 
            // is already assigned to this exam?
            $params = array('boardid' => $board->id);
            if(isset($record->deputy)) {
                $params['deputy'] = $record->deputy ? 1 : 0;
            }
            if($member = $DB->get_record('examboard_member', $params + array('userid' => $record->member))) {
                if($fromform->ignoremodified) {
                    if(isset($record->sortorder) && ($member->sortorder != $record->sortorder)) {
                        //delete any user in this position
                        $DB->delete_records('examboard_member', $params + array('sortorder' => $record->sortorder));
                        // TODO  // TODO  // TODO  // TODO  
                        //what happend with other tables ?? confirmation , notification??
                    }
                    if(isset($record->role) && ($member->role != $record->role)) {
                        //delete any user in this position
                        $DB->delete_records('examboard_member', $params + array('role' => $record->role));
                        // TODO  // TODO  // TODO  // TODO  
                        //what happend with other tables ?? confirmation , notification??
                    }
                }
            } else {
                // we are adding a new member
                if($examboard->maxboardsize > $DB->count_records('examboard_member', $params)) {                
                    $member = new stdClass();
                    $member->boardid = $board->id;
                    $member->userid = $record->member;
                    $member->timecreated = $now;
                }
            }
            
            if(isset($member->boardid) && !empty($member->userid)) {
                $member->id = examboard_insert_update_data($member, $record, $now, $fromform->ignoremodified, 
                                                                'examboard_member', array('sortorder', 'deputy'));
            }
        }

        if($record->examinee) {
            //the user is known 
            // is already assigned to this exam?
            $params = array('examid' => $exam->id, 'userid' => $record->examinee);
            $fields = array();
            if($user = $DB->get_record('examboard_examinee', $params)) {
                if($fromform->ignoremodified) {
                    if(isset($record->userlabel) && $record->userlabel && ($record->userlabel != $user->userlabel)) {
                        $fields[] = 'userlabel';
                    }
                    if(isset($record->excluded) && $record->excluded && ($record->excluded != $user->excluded)) {
                        $fields[] = 'excluded';
                    }
                }
            } else {
                $sortorder = 0;
                if($max = $DB->get_records_menu('examboard_examinee', array('examid' => $exam->id), 'sortorder DESC', 'id, sortorder', 0,1)) {
                    $sortorder = reset($max) + 1; 
                }
                $user = new stdClass();
                $user->examid = $exam->id;
                $user->userid = $record->examinee;
                $user->sortorder = $sortorder;
                if(isset($record->excluded)) {
                    $fields[] = 'excluded';
                }
                if(isset($record->userlabel)) {
                    $fields[] = 'userlabel';
                }
                $user->timecreated = $now;
            }
            $user->id = examboard_insert_update_data($user, $record, $now, $fromform->ignoremodified, 
                                                            'examboard_examinee', $fields);
        }

        if($record->tutor && $record->examinee ) {        
            // do exists a tutor for this examinee
            $params = array('examid' => $exam->id, 'userid' => $record->examinee, 'main' => 1);
            if($tutor = $DB->get_record('examboard_tutor', $params)) {
                if($fromform->ignoremodified && $tutor->tutorid != $record->tutor) {
                    $tutor->tutorid = $record->tutor;
                    $tutor->timemodified = $now;
                    $DB->update_record('examboard_tutor', $tutor);
                }
            } else {
                $tutor = new stdClass();
                $tutor->examid = $exam->id;
                $tutor->userid = $record->examinee;
                $tutor->tutorid = $record->tutor;
                $tutor->main = 1;
                $tutor->timecreated = $now;
                $tutor->timemodified = $now;
                $tutor->id = $DB->insert_record('examboard_tutor', $tutor);
            }
        }
        
        if($record->othertutors && $record->examinee ) {  
            $record->othertutors = explode(',', $record->othertutors);
            $params = array('examid' => $exam->id, 'userid' => $record->examinee, 'main' => 0);
            if($existing = $DB->get_records_menu('examboard_tutor', $params, '', 'id, tutorid')) {
                if($fromform->ignoremodified) {
                    // update those in othertutors, delete others
                    if($deletes = array_diff($existing, $record->othertutors)) {
                        $DB->delete_records_list('examboard_tutor', 'id', array_keys($deletes));
                    }
                    // we don't update, nothing to change excepto tiemmodified, just delete from insert list
                    $record->othertutors = array_diff($record->othertutors, $existing);
                } else {
                    //do nothig else
                    $record->othertutors = array();
                }
            }
            if($record->othertutors) {
                $tutor = new stdClass();
                $tutor->examid = $exam->id;
                $tutor->userid = $record->examinee;
                $tutor->main = 0;
                $tutor->timecreated = $now;
                $tutor->timemodified = $now;
                foreach($record->othertutors as $tutorid) {
                    $tutor->tutorid = $tutorid;
                    $DB->insert_record('examboard_tutor', $tutor);
                }
            }
        }
        $recordsadded++;
    }
    $csvreader->close();
    $csvreader->cleanup(true);

    // message n imported
    return get_string('importedrecords', 'examboard', $recordsadded);
}


function examboard_export_exam_row(&$row) {
   global $CFG, $DB, $SESSION, $PAGE;

    $columns = $SESSION->mod_examboard_export_columns;
    $listby = $SESSION->mod_examboard_export_listby;
    $lastexam = $SESSION->mod_examboard_export_examid;
    $rolestr = $SESSION->mod_examboard_export_rolestr;
    list($skipped, $examineefields, $memberfields) = $SESSION->mod_examboard_export_fieldtypes;
    
    $names = get_all_user_name_fields();
    $user = core_user::get_support_user(); 

    $renderer = $PAGE->get_renderer('mod_examboard');
    
    $examination = new \mod_examboard\examination($row);
    
    if(isset($columns['memberidnumber'])) {
        $memberfields[] = 'memberidnumber';
    }
    if(isset($columns['examineeidnumber'])) {
        $examineefields[] = 'examineeidnumber';
    }
    if(isset($columns['tutoridnumber'])) {
        $examineefields[] = 'tutoridnumber';
    }
    
    if($listby == EXAMBOARD_USERTYPE_MEMBER) {
        $skipped += $examineefields;
    } elseif($listby == EXAMBOARD_USERTYPE_USER) {
        $skipped += $memberfields;
    }

    $members = array();
    if($listby == EXAMBOARD_USERTYPE_USER || isset($columns['member'])) {
        $search = '';
        if(!isset($columns['deputy'])) {
            $search = 'deputy = 0 ';
        }
        $members = $examination->load_board_members($search);
    }
    
    $examinees = array();
    if($listby == EXAMBOARD_USERTYPE_MEMBER || isset($columns['examinee'])) {
        $examinees = $examination->load_examinees();
    }
    
    $tutors = array();
    if(isset($columns['tutor']) || isset($columns['othertutors'])) {
        $tutors = $examination->load_tutors();
    }
    
    $newline = "<br />\n";
    $newrow = array();
    foreach($columns as $col => $name) {
        
        if(in_array($col, $skipped) && ($row->examid == $lastexam)) {
            $newrow["$col"] = '';
            continue;
        }    
         
        if(in_array($col, $examineefields)) { 
            if($listby != EXAMBOARD_USERTYPE_USER) {
                $data = array();
                foreach($examinees as $k => $examinee) {
                    if($col == 'examinee') {
                        $data[$k] = fullname($examinee);
                    } elseif($col == 'tutor') { 
                        if(isset($tutors[$k]) && is_array($tutors[$k])) {
                            $tutor = reset($tutors[$k]);
                            $data[$k] = fullname($tutor);
                        } else {
                            $data[$k] = '';
                        }
                    } elseif($col == 'othertutors') {
                        $others = (isset($tutors[$k])) ? $tutors[$k] : false;
                        if(is_array($others)) {
                            array_shift($others);
                            $data[$k] = implode(', ', array_map('fullname', $others));
                        } else {
                            $data[$k] = '';
                        }
                    } else {
                        $data[$k] = isset($examinee->{$col}) ? $examinee->{$col} : '';
                    }
                }
                $newrow["$col"] = implode($newline, $data);
            } else {
                $examinee = $examinees[$row->examinee];
                if($col == 'examinee') {
                    $newrow["$col"] = fullname($examinee);
                } elseif($col == 'tutor') {
                    if(isset($tutors[$row->examinee][$row->tutorid])) {
                        $newrow["$col"] = fullname($tutors[$row->examinee][$row->tutorid]);
                    } else {
                        $newrow["$col"] = '';
                    }
                } elseif($col == 'othertutors') { 
                    $others = (isset($tutors[$row->examinee])) ? $tutors[$row->examinee]: false;
                    if(is_array($others)) {
                        array_shift($others);
                        $newrow["$col"] = implode(', ', array_map('fullname', $others));
                    } else {
                        $newrow["$col"] = '';
                    }
                } else {
                    $newrow["$col"] = isset($examinee->{$col}) ? $examinee->{$col} : '';
                }
            }
        } 
         
        if(in_array($col, $memberfields)) {
            if($listby != EXAMBOARD_USERTYPE_MEMBER) {
                //we need to complete $row info //if we are here, this is a first row for this exam
                $data = array();
                foreach($members as $k => $member) {
                    if($col == 'member') {
                        $data[$k] = $rolestr[$member->sortorder].$newline.fullname($member);
                    } else {
                        $data[$k] = isset($member->{$col}) ? $member->{$col} : '';
                    }
                }
                $newrow["$col"] = implode($newline, $data);
            } else {
                $member = $members[$row->member];
                if($col == 'member') {
                    $newrow["$col"] = fullname($member);
                } else {
                    $newrow["$col"] = isset($member->{$col}) ? $member->{$col} : '';
                }
            }
        }         
         
        /* 
        if(($listby != EXAMBOARD_USERTYPE_USER) && in_array($col, $examineefields)) {
            //we need to complete $row info  //if we are here, this is a first row for this exam
            $data = array();
            foreach($examinees as $k => $examinee) {
                if($col == 'examinee') {
                    $data[$k] = fullname($examinee);
                } else {
                    $data[$k] = $examinee->{$col};
                }
            }
            $newrow["$col"] = implode($newline, $data);
        }
        if(($listby != EXAMBOARD_USERTYPE_MEMBER) && in_array($col, $memberfields)) {
            //we need to complete $row info //if we are here, this is a first row for this exam
            $data = array();
            foreach($members as $k => $member) {
                if($col == 'member') {
                    $data[$k] = $rolestr[$member->sortorder].$newline.fullname($member);
                } else {
                    $data[$k] = $member->{$col};
                }
            }
            $newrow["$col"] = implode($newline, $data);
        }
*/
            
        if($col == 'groupid') {
            $newrow["$col"] = $DB->get_field('groups', 'name', array('id' =>$row->{$col})) ;
            
        } elseif($col == 'examdate') {
            $newrow["$col"] = userdate($row->{$col});
        
        } elseif($col == 'duration') {
            $newrow["$col"] = format_time($row->{$col});
        /*
        } elseif($col == 'examinee') {
            if(!isset($newrow["$col"]) && isset($row->{$col})) {
                if($examinees) {
                    $newrow["$col"] = fullname($examinees[$row->{$col}]);
                }
            } else {
                $data = array();
                foreach($examinees as $k => $examinee) {
                    $data[$k] = fullname($examinee);
                }
                $newrow["$col"] = implode($newline, $data);
            }
          
        } elseif($col == 'tutor') {
            if(!isset($newrow["$col"]) && isset($row->tutorid)) {
                $newrow["$col"] = fullname($tutors[$row->userid][$row->tutorid]);
            }
        
        } elseif($col == 'othertutors') {
            if(!isset($newrow["$col"]) && $others = $tutors[$row->userid]) {
                array_shift($others);
                foreach($others as $k => $tutor) {
                    $others[$k] = fullname($tutor);
                }
                $newrow["$col"] = implode($newline, $others);
            }
          */  
        } elseif(!isset($newrow["$col"])) {
            $newrow["$col"] = isset($row->{$col}) ? $row->{$col} : '';
        }
/*        
        if(!isset($newrow["$col"])) {
            $newrow["$col"] = '';
        }
  */      
    }
    
    return $newrow;
}

/**
 * Exports exam data to a file 
 *
 * @param stdClass $examboard instance record
 * @param stdClass $fromform user input from mform
 * @return string success/error notification message 
*/
function examboard_export_examinations($examboard, $fromform) {
    global $CFG, $DB, $SESSION;
    
    require_once($CFG->libdir . '/dataformatlib.php');
    
    $message = '';
    $classname = 'dataformat_' . $fromform->dataformat . '\writer';
    if (!class_exists($classname)) {
        throw new coding_exception("Unable to locate dataformat/{$fromform->dataformat}/classes/writer.php");
    }
    $dataformat = new $classname;
    
    $filename = clean_filename($fromform->filename);

    list($mandatory, $optional) = examboard_import_export_fields($examboard, true);
    $columns = $mandatory;
    foreach($optional as $key => $value) {
        if($fromform->{$key}) {
            $columns[$key] = $value;
            if($fromform->useridnumber && (($key == 'member') || ($key == 'examinee') ||($key == 'tutor') )) {
                $columns[$key.'idnumber'] = get_string('useridnumbercsv', 'examboard');
            }
        }
    }
    if($fromform->includedeputy) {
        $columns['deputy'] = get_string('deputy', 'examboard');
    }
    
    if($fromform->exportedexams) {
        list($inexams, $params) = $DB->get_in_or_equal($fromform->exportedexams, SQL_PARAMS_NAMED);
        
        $index = 'e.id';
        $fields = '';
        $join = '';
        $names = '';
        if($fromform->exportlistby == EXAMBOARD_USERTYPE_MEMBER) {
        
            $includedeputy = '';
            if(!$fromform->includedeputy) {
                $includedeputy = ' AND m.deputy = 0 ';
            }
            $index = "CONCAT_WS('-', e.id, m.id)";
            $fields = ', m.userid, m.sortorder, m.role, m.deputy, um.idnumber AS memberidnumber, m.userid AS member '; 
            $join = " LEFT JOIN {examboard_member} m ON m.boardid = e.boardid  $includedeputy 
                        LEFT JOIN {user} um ON m.userid = um.id  ";
            $names = 'um';
                        
        } elseif($fromform->exportlistby == EXAMBOARD_USERTYPE_USER) {
            unset($columns['role']);
            $index = "CONCAT_WS('-', e.id, ee.id)";
            $fields =  ', ee.userid, ee.sortorder, ee.userlabel, ee.excluded, ue.idnumber AS examineeidnumber, 
                            t.tutorid, ee.userid AS examinee '; 
            $join = 'LEFT JOIN {examboard_examinee} ee ON ee.examid = e.id   
                        LEFT JOIN {user} ue ON ee.userid = ue.id  
                    LEFT JOIN {examboard_tutor} t ON t.examid = e.id AND t.userid = ee.userid AND t.main = 1 ';
            $names = 'ue';
        }
        
        if($names) {
            $names = ', '.get_all_user_name_fields(true, $names);
        }
        
        $sql = "SELECT $index AS idx, e.*, e.id AS examid, e.active AS examactive, b.title, b.name, b.idnumber, b.active as boardactive, b.groupid 
                        $fields $names
                FROM {examboard_exam} e 
                JOIN {examboard_board} b ON b.id = e.boardid AND b.examboardid = e.examboardid
                $join
                WHERE e.examboardid = :examboardid AND e.id $inexams
                ORDER BY b.idnumber ASC, e.examperiod DESC, e.sessionname ASC, e.examdate ";
        $params['examboardid'] = $examboard->id;       
                
        $rolestr = array();
        $rolestr[0] = $examboard->chair;
        $rolestr[1] = $examboard->secretary;
        foreach(range(2, $examboard->maxboardsize -1 ) as $idx) {
            $rolestr[$idx] = $examboard->vocal.' '.($idx-1);
        }
        
        $skipped = array('idnumber', 'sessionname', 'title', 'name', ' 	sessionname ', 'venue', 'accessurl', 'examdate', 'duration', 'group', 'boardactive', 'examactive');
        $examineefields = array('examinee', 'userlabel', 'tutor', 'othertutors', 'examineesortorder', 'grades', 'excluded', 'approved');
        $memberfields = array('member', 'role', 'deputy', 'exemption', 'confirmed', 'notifications');  

        $SESSION->mod_examboard_export_columns = $columns;
        $SESSION->mod_examboard_export_listby = $fromform->exportlistby;
        $SESSION->mod_examboard_export_examid = 0;
        $SESSION->mod_examboard_export_rolestr = $rolestr;
        $SESSION->mod_examboard_export_fieldtypes = array($skipped, $examineefields, $memberfields);
    
        $rs_exams = $DB->get_recordset_sql($sql, $params); 
        if($rs_exams->valid() && $columns) {
            if (!headers_sent() && error_get_last()==NULL ) {
                download_as_dataformat($filename, $fromform->dataformat, $columns, $rs_exams, 'examboard_export_exam_row');
            } else {
                $message = get_string('headersent', 'error');
            }
        } else {
            if(!$columns) {
                $message = "No columns";
            } else {
                $message = "No valid data";
            }
        }
        $rs_exams->close();
        unset($SESSION->mod_examboard_export_columns);
        unset($SESSION->mod_examboard_export_listby);
        unset($SESSION->mod_examboard_export_examid);
        unset($SESSION->mod_examboard_export_rolestr);
        unset($SESSION->mod_examboard_export_fieldtypes);
    } else {
        $message = "No valid exams selected";
    }

    return $message;
}


function examboard_get_exam_userids($exam, $withtutors = true,  $withexaminees = true) {
    global $DB; 
    
    $members = $DB->get_records_menu('examboard_member', 
                                        array('boardid'=> $exam->boardid, 'deputy' => 0),
                                        '',
                                        'id, userid');

    if($withtutors) {
        $members = array_merge($members, $DB->get_records_menu('examboard_tutor', 
                                                                array('examid'=> $exam->id),
                                                                '',
                                                                'id, tutorid'));
    }
    
    if($withexaminees) {
        $members = array_merge($members, $DB->get_records_menu('examboard_examinee', 
                                                                array('examid'=> $exam->id),
                                                                '',
                                                                'id, userid'));
    }
                                        
    $members = array_filter(array_unique($members));

    return $members;
}


/**
 * Finds complementary trackers and enforce rules to allow/restrict access
 * to those modules by graders & students
 *
 * @param array $trackers collection of $cm_info objects to synchronize
 * @param stdclass $exam 
 * @return void
*/
function examboard_synchronize_trackers($trackers, $exam) {
    global $CFG, $DB;

    if(!$trackers) {
        return;
    }
    
    $members = examboard_get_exam_userids($exam, false, false);
    $examinees = examboard_get_exam_tutors($exam->id); 
    $others = array();
    
    if(!$examinees || !$members) {
        return;
    }
    
    include_once($CFG->dirroot.'/mod/tracker/locallib.php');
    
    foreach($trackers as $key => $cm) {
        $tids[$cm->instance] = $key;
    }

    list($intsql, $tparams) = $DB->get_in_or_equal(array_keys($tids), SQL_PARAMS_NAMED, 't');
    list($inusql, $uparams) = $DB->get_in_or_equal(array_keys($examinees), SQL_PARAMS_NAMED, 'u');
    
    $select = " trackerid $intsql AND reportedby $inusql ";
    if($issues = $DB->get_records_select('tracker_issue', $select, $tparams + $uparams)) {
        $tracker = new stdClass();
        foreach($issues as $issue) {
            $tracker->id = $issue->trackerid;
            $issueccs = $DB->get_records_menu('tracker_issuecc', 
                                                array('issueid' => $issue->id, 'trackerid' => $issue->trackerid),
                                                '', 'id,userid');
            $others = $DB->get_records_menu('examboard_tutor', array('examid' => $exam->id, 'userid' =>$issue->reportedby, 'main' => 0), 
                                                'id', 'id, tutorid') + array($examinees[$issue->reportedby]);
            if($deletes = array_diff($issueccs, $members, $others)) {
                list($insql, $params) = $DB->get_in_or_equal($deletes, SQL_PARAMS_NAMED, 'd');
                $params['trackerid'] = $issue->trackerid;
                $params['issueid'] = $issue->id;
                $select = "trackerid = :trackerid AND issueid = :issueid AND userid $insql";
                $DB->delete_records_select('tracker_issuecc', $select, $params);
            }
            $adds = array_diff($members + $others, $issueccs);
            foreach($adds as $userid) {
                tracker_register_cc($tracker, $issue, $userid); 
            }
            
            if(($tids[$issue->trackerid] == 'gradeable' OR $tids[$issue->trackerid] == 'defense') && 
                ($issue->assignedto != $examinees[$issue->reportedby])){
                    $DB->set_field('tracker_issue', 'assignedto', $examinees[$issue->reportedby], 
                                        array('id' => $issue->id, 'trackerid' => $tracker->id, 'reportedby' => $issue->reportedby));

            }
        }
    }
}


/**
 * Finds complementary assigns and enforce rules to allow/restrict access
 * to those modules by graders & students
 *
 * @param array $trackers collection of $cm_info objects to synchronize
 * @param int $groupingid
 * @return void
*/
function examboard_config_complementary_assigns($assigns, $groupingid = -1) {
    global $CFG, $DB;
    
    include_once($CFG->dirroot.'/mod/assign/lib.php');
    
    $update = false;
    
    foreach($assigns as $assignmod) {
        $modupdate = new stdClass();
        
        if($groupingid >= 0 && (($assignmod->groupmode != SEPARATEGROUPS) || ($assignmod->groupingid != $groupingid))){
            $modupdate->groupmode = SEPARATEGROUPS;
            $modupdate->groupingid = $groupingid;
            $modupdate->id = $assignmod->id;
            $DB->update_record('course_modules', $modupdate);
        }

        if($instance = $DB->get_record('assign', array('id'=>$assignmod->instance))) {
            $instance->coursemodule = $assignmod->id;
            $instance->instance = $assignmod->instance;
            if(!$instance->markingworkflow) {
                $instance->markingworkflow = 1;
                $instance->markingallocation = 0;
                $update = true;
            }
        }
    
        if($update) {
            assign_update_instance($instance, null);
        }
    }
}


/**
 * Finds complementary assigns and enforce rules to allow/restrict access
 * to those modules by graders & students
 *
 * @param array $trackers collection of $cm_info objects to synchronize
 * @param array $tutors associative array userid=>tutorid
 * @return void
*/
function examboard_allocate_assign_graders($assigns, $tutors) {
    global $CFG, $DB;

}


/**
 * Random allocation of board members in exams
 * Given students already assigned to exams
 *
 * @param stdClass $examboard instance record
 * @param stdClass $fromform user input from mform
 * @return string success/error notification message 
*/
function examboard_process_allocateboard($examboard, $fromform) {
    global $CFG, $DB, $SESSION;

    $context = context_module::instance($fromform->id);
    // form arrays of potential members (those that can grade)
    foreach($fromform->choosegroup as $sortorder => $groups) {
        $potential[$sortorder] = array();
        //$groups = explode(',', $groups);
        foreach($groups as $groupid) {
            $users = array_keys(get_enrolled_users($context, 'mod/examboard:grade', $groupid, 'u.id, u.idnumber')); 
            $potential[$sortorder] = array_unique(array_merge($potential[$sortorder], $users));
        }
        shuffle($potential[$sortorder]);
    }
    
    $allocatedexams =  $fromform->allocatedexams;//$allocatedexams =  explode(',',$fromform->allocatedexams);
    
    if($fromform->delexisting && $fromform->allocatedexams) {
        // delete
        list($insql, $params) = $DB->get_in_or_equal($fromform->allocatedexams, SQL_PARAMS_NAMED, 'e');
        $boards = array_unique($DB->get_records_select_menu('examboard_exam', "id $insql ", $params, '', 'id,boardid'));
        $DB->delete_records_list('examboard_member', 'boardid',  $boards);
    }
    
    if($fromform->excludeexisting) {
        $boards = array();
        switch($fromform->excludeexisting) {
            case 'any' : $boards = array_unique($DB->get_records_menu('examboard_exam', array('examboardid'=>$examboard->id, 'active'=>1), '', 'id,boardid'));
                        break;

            case 'exams' : list($insql, $params) = $DB->get_in_or_equal($fromform->allocatedexams, SQL_PARAMS_NAMED, 'e');
                        $params['examboardid'] = $examboard->id;
                        $select = " examboardid = :examboardid AND active = 1 AND id $insql ";
                        $boards = array_unique($DB->get_records_select_menu('examboard_exam', $select, $params, '', 'id,boardid'));
                        break;
                        
            default : $boards = array_unique($DB->get_records_menu('examboard_exam', array('examboardid'=>$examboard->id, 
                                                            'examperiod' => $fromform->excludeexisting, 'active'=>1), '', 'id,boardid'));
                        break;
        }
    
        if($boards) {
            list($insql, $params) = $DB->get_in_or_equal($boards, SQL_PARAMS_NAMED, 'b');
            if($users = array_unique($DB->get_records_select_menu('examboard_member', "boardid $insql ", $params, '', 'id,userid'))) {
                foreach($fromform->choosegroup as $sortorder => $groups) {
                    $potential[$sortorder] = array_diff($potential[$sortorder], $users);
                }
            }
        }
    }
    
    $initial = $potential;
    /*
    // change for deep copy
    $initial = array();
    foreach(range(0, $examboard->maxboardsize -1) as $sortorder) {
        $initial[$sortorder] = array();
        foreach($potential[$sortorder] as $i => $userid) {
            $initial[$sortorder][$i] = $userid;
        }
    }
    */
    $boards = array();
    // walk through exams to set all boards  with tutors & members
    foreach($allocatedexams as $examid) {
        $exam = examboard_get_exam_with_board($examid);
       
        if(!isset($boards[$exam->boardid])) {
            //this board is not set, add it 
            $board = new \stdClass();
            $board->id = $exam->boardid;
            $board->idnumber = $exam->idnumber;
            $board->members = array();
            $board->excluded = array();
            $board->exams = array();
            
            // load members is needed
            if($fromform->delexisting) {
                $DB->delete_records('examboard_member', array('boardid'=>$exam->boardid));
            } else {
                if($users = examboard_get_board_members($exam->boardid)) {
                    foreach($users as $user) {
                        $board->members[$user->sortorder][$user->deputy] = $user->userid;
                        $board->excluded[] = $user->userid;
                    }
                }
            }
            $boards[$exam->boardid] = $board;
        }
        
        // now $exam->boardid board exists in array
        // if existing, board is set, only add tutors to excluded users
        if($examboard->usetutors) {
            // get all other tutor for any exam for this board for this board
            $sql = "SELECT DISTINCT t.id, t.tutorid   
                    FROM {examboard_tutor} t
                    JOIN {examboard_exam} e ON t.examid = e.id 
                    WHERE e.examboardid = :examboardid AND e.boardid = :boardid 
                    GROUP BY t.tutorid ";
            $params = array('examboardid' =>$examboard->id, 'boardid' => $exam->boardid);
            if($tutors = $DB->get_records_sql_menu($sql, $params)) {
                $boards[$exam->boardid]->excluded = array_unique(array_merge($boards[$exam->boardid]->excluded, array_unique($tutors)));
            }
        }
        
        // we add this exam to the board's list
        $boards[$exam->boardid]->exams[] = $examid;
    }
    
    $maxallocations = array();
    $allocations = array();
    $total = array();
    $totaloccupied = 0;
    foreach(range(0, $examboard->maxboardsize -1) as $sortorder) {
        foreach(range(0, $fromform->deputy) as $deputy) {
            $occupied[$sortorder][$deputy] = 0;
            foreach($boards as $board) {
                $set = false; 
                if(isset($board->members[$sortorder][$deputy])){ 
                    $occupied[$sortorder][$deputy] += 1;
                    $set = true;
                }
                foreach($potential[$sortorder] as $i => $userid) {
                    $allocations[$userid][$sortorder][$deputy] = 0;
                    if($set && $userid == $board->members[$sortorder][$deputy]) { 
                        $allocations[$userid][$sortorder][$deputy] += 1; 
                    }
                }
            }
        }
        $maxallocations[$sortorder] = $potential[$sortorder] ? ceil( (count($boards) - $occupied[$sortorder][0]) / count($potential[$sortorder])) : 1;
        $total = array_merge($total, $potential[$sortorder]);
        $totaloccupied += $occupied[$sortorder][0];
    }
    //print_object($fromform);
    //print_object($maxallocations);

    
    $totalmaxallocations = ceil((count($boards) * $examboard->maxboardsize - $totaloccupied) / count(array_unique($total)));
    $totalmaxallocations = $fromform->deputy ? 2 * $totalmaxallocations : $totalmaxallocations;
    //print_object("totalmaxallocations = $totalmaxallocations             from boards:".count($boards)."  - occupied=$totaloccupied   users =".count(array_unique($total)));
    
    $now = time();
    $synchflag = false;
    $boardsllocated = 0;
    $examsllocated = 0;
    
    $newmember = new \stdClass();
    $newmember->role = '';
    $newmember->timecreated = $now;
    $newmember->timemodified = $now;
    
    $roles = array(0 => $examboard->chair, 1 => $examboard->secretary, 2 => $examboard->vocal);
    if($examboard->maxboardsize > 3) {
        foreach(range(2, $examboard->maxboardsize -1) as $idx) {
          $roles[$idx] =  $examboard->vocal.' '.$idx;
        }
    }
    $vacant = array();
    $added = array();

    // now go through boards,  allocating members where needed
    foreach($boards as $board) {
        // if repeatable, we refill any array without users after all used
        foreach(range(0, $examboard->maxboardsize -1) as $sortorder) {
            if(empty($potential[$sortorder])) {
                if($fromform->repeatable) {
                    $potential[$sortorder] = $initial[$sortorder];
                    // eliminate those with max allowable allocations; 
                    foreach($potential[$sortorder] as $i => $userid) {
                        $allocs = 0;
                        foreach(range(0, $fromform->deputy) as $deputy) {
                            if($allocations[$userid][$sortorder][$deputy] >= $maxallocations[$sortorder]) { 
                                unset($potential[$sortorder][$i]);
                                //print_object("eliminado por max  $sortorder  $userid has {$allocations[$userid][$sortorder][$deputy]} allocations");
                            }
                        }
                        foreach($allocations[$userid] as $s => $darr) {
                            foreach($darr as $d => $num) {
                               $allocs += $allocations[$userid][$s][$d];
                            }
                        }
                        if($allocs >= $totalmaxallocations) {
                            unset($potential[$sortorder][$i]);
                            //print_object("eliminado  por maxglobal $userid ");
                        }
                    }
                    shuffle($potential[$sortorder]);
                    //print_object("Al procesar {$board->idnumber}   Deben quedar en $sortorder unos".count($potential[$sortorder]));
                }
            }
        }
        
        // now we can test if really sold out
        $allempty = true; 
        foreach(range(0, $examboard->maxboardsize -1) as $sorder) {
            // after loops end thsi is only true if all emptied 
            $allempty = $allempty && empty($potential[$sorder]);
        }
        if($allempty) {
            // if one of the potential members arrays is empty we cannot continue allocating 
            // notice
            \core\notification::error(get_string('allocemptied', 'examboard'));
            break;
        }

        $added = array();
        foreach(range(0, $examboard->maxboardsize -1) as $sortorder) {
            shuffle($potential[$sortorder]);
            foreach(range(0, $fromform->deputy) as $deputy) {
                if(!isset($board->members[$sortorder][$deputy]) || !$board->members[$sortorder][$deputy]) {
                    // not exists, allocate member now
                    // get a potential user
                    // only if there are potential users NOT excluded (avoid infinite loops)
                    $userid = 0;
                    if($available = array_diff($potential[$sortorder], $board->excluded)) {
                        $key = array_rand($available);
                        $userid = $available[$key];                    
                        if($userid > 0) {
                            //OK, this is a non-existing user, can be added
                            $added[$sortorder][$deputy] = $userid;
                            $board->excluded[] = $userid;
                        }
                    } else {
                        $vacant[] = $board->idnumber;
                    } 
                }
            }
        }
    
        if(!empty($added)) {
            $newmember->boardid = $board->id;
            $boardsllocated++;
            $examsllocated += count($board->exams); 
            foreach($added as $sortorder => $adds) {
                foreach($adds as $deputy => $userid) {
                    $newmember->userid = $userid;
                    $newmember->sortorder = $sortorder;
                    $newmember->role = $roles[$sortorder];
                    $newmember->deputy = $deputy;
                    if($DB->insert_record('examboard_member', $newmember)) {
                        $synchflag = true;
                        $allocations[$userid][$sortorder][$deputy] += 1; 
                        // once added, remove from all potential, so no repeat unless all used up
                        foreach(range(0, $examboard->maxboardsize -1) as $sorder) {
                            $key = array_search($userid, $potential[$sorder]);
                            if($key !== false) {
                                unset($potential[$sorder][$key]);
                                shuffle($potential[$sorder]);
                            }
                        }
                    }
                }
            }
        }
        $userid = 0;
        $newmember->userid = null;
        // allocation for this boardid is done
    
        // time to reorder exam student users, if asked
        if($fromform->userorder) {
            foreach($board->exams as $examid) {
                examboard_reorder_examinees($examid, $fromform->userorder);
            }
        }    
    
    }
    
    // now call synchronizing as needed
    if($synchflag) {
        examboard_synchronize_groups($examboard);
        examboard_synchronize_gradeables($examboard, false, false);
    }
    
    if($vacant) {
        \core\notification::add(get_string('allocvacant', 'examboard', \html_writer::alist(array_unique($vacant))), \core\output\notification::NOTIFY_ERROR);
    }
    
    $count = new \stdClass();
    $count->boards = $boardsllocated;
    $count->exams =  $examsllocated;
    return get_string('allocnumexams', 'examboard', $count);
}


/**
 * Random allocation of examinees in exams
 * Given students already assigned to exams
 *
 * @param stdClass $examboard instance record
 * @param stdClass $fromform user input from mform
 * @return string success/error notification message 
*/
function examboard_process_allocateusers($examboard, $fromform) {
    global $CFG, $DB;

    $context = context_module::instance($fromform->id);

    $sortby = '';
    if($fromform->userorder == EXAMBOARD_ORDER_ALPHA) {
        $sortby = isset($SESSION->nameformat) ?  $SESSION->nameformat : 'lastname';
        $sortby = 'u.'.$sortby;
    }
    
    // form arrays of potential members (those that can submit)
    $potential[] = array();
    foreach(explode(', ', $fromform->sourcegroups) as $groupid) {
        $tutors = array_keys(get_enrolled_users($context, 'mod/examboard:grade', $groupid, 'u.id, u.idnumber'));
        if($users = array_keys(get_enrolled_users($context, 'mod/examboard:submit', $groupid, 'u.id, u.idnumber', $sortby))) {
            foreach($users as $userid) {
                $potential[$userid] = $tutors;
            }
        }
    }

    $now = time();
    $synchflag = false;
    
    $newmember = new stdClass();
    $newmember->userlabel = '';
    $newmember->timecreated = $now;
    $newmember->timemodified = $now;
    $newmember->timeexcluded = 0;
    $newmember->excluded = 0;
    
    $newtutor = new stdClass();
    $newtutor->timecreated = $now;
    $newtutor->timemodified = $now;
    $newtutor->approved = 1;
    
    $usersllocated = 0;
    
    $allocatedexams =  explode(', ',$fromform->allocatedexams);
    
    if(!$fromform->usersperexam) {
        list($insql, $params) = $DB->get_in_or_equal($allocatedexams, SQL_PARAMS_NAMED, 'e');
        $current = $DB->count_records_select('examboard_examinee', "examid $insql ", $params);
        
        $fromform->usersperexam = ceil((count($potential) + current) / count($allocatedexams));
    }
    
    shuffle($allocatedexams);
    foreach($allocatedexams as $examid) {
        $exam = examboard_get_exam_with_board($examid);
        if($users = examboard_get_board_members($exam->boardid)) {
            foreach($users as $user) {
                $members[] = $user->userid;
            }
        }
        $members = array_unique($members);
        
        $examinees = array();
        if($current = examboard_get_exam_examinees($examid)) {
            foreach($current as $user) {
                $examinees[] = $user->userid;
            }
            unset($curent);
        }
        
        $newmember->examid = $examid;
        $newtutor->examid = $examid;
        
        reset($potential);
        while(count($examinees) < $fromform->usersperexam) {
            list($userid, $tutors) = each($potential); 
            if($userid && !in_array($userid, $examinees) && !array_intersect($members, $tutors)) {
                // OK, this userid  is not listed, add to exam
                $newmember->userid = $userid;
                $newtutor->userid = $userid;
                $newmember->sortorder = count($examinees);
                if($DB->insert_record('examboard_examinee', $newmember)) {
                    $synchflag = true;
                    $usersllocated++;
                    // add to list to avoid duplicates 
                    // NO repeatables when assigning by users: each student in just one and only one exam
                    $examinees[] = $userid;
                    unset($potential[$userid]);
                    
                    // now add tutors
                    if($main = array_shift($tutors)) {
                        $newtutor->main = 1;
                        $newtutor->tutorid = $main;
                        $DB->insert_record('examboard_tutor', $newtutor);
                    }
                    if($tutors) {
                        $newtutor->main = 0;
                        foreach($tutors as $tutor) {
                            $newtutor->tutorid = $tutor;                        
                            $DB->insert_record('examboard_tutor', $newtutor);
                        }
                    }
                }
            }
            if(empty($potential)) {
                break 2;
            }
        }
        
        if($fromform->userorder) {
            examboard_reorder_examinees($examid, $fromform->userorder);
        }
        
        
    }
    
    // now call synchronizing as needed
    if($synchflag) {
        examboard_synchronize_groups($examboard);
        examboard_synchronize_gradeables($examboard, false, false);
    }
    
    return get_string('allocnumusers', 'examboard', $usersllocated);
}
