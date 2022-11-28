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
 * This is the external method for getting the list of users for the dialogue conversation form.
 *
 * @package    mod_dialogue
 * @copyright  2021 Dan Marsden <dan@danmarsden.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_dialogue\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

use stdClass;
use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use core_user_external;
use context_module;
use moodle_exception;
use course_enrolment_manager;
use core\session\exception;

/**
 * This is the external method for getting the information needed to present an attempts report.
 *
 * @copyright  2021 Dan Marsden <dan@danmarsden.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_users extends external_api {
    /**
     * Webservice parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'cmid' => new external_value(PARAM_INT, 'course module id'),
                'search' => new external_value(PARAM_RAW, 'query'),
                'searchanywhere' => new external_value(PARAM_BOOL, 'find a match anywhere, or only at the beginning'),
                'page' => new external_value(PARAM_INT, 'Page number'),
                'perpage' => new external_value(PARAM_INT, 'Number per page'),
            ]
        );
    }

    /**
     * Return user attempts information in a h5p activity.
     *
     * @param int $cmid Course module id
     * @param string $search The query
     * @param bool $searchanywhere Match anywhere in the string
     * @param int $page Page number
     * @param int $perpage Max per page
     * @return array report data
     */
    public static function execute(int $cmid, string $search, bool $searchanywhere, int $page, int $perpage): array {
        global $PAGE, $CFG, $USER, $DB;

        require_once($CFG->dirroot.'/enrol/locallib.php');
        require_once($CFG->dirroot.'/user/lib.php');

        // ecastro ULPGC courseid is really cmid passed from form 
        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'dialogue');
        $courseid = $course->id;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'cmid'           => $cmid,
                'search'         => $search,
                'searchanywhere' => $searchanywhere,
                'page'           => $page,
                'perpage'        => $perpage
            ]
        );
        list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'dialogue');
        $context = context_module::instance($cm->id);
        try {
            self::validate_context($context);
        } catch (Exception $e) {
            $exceptionparam = new stdClass();
            $exceptionparam->message = $e->getMessage();
            $exceptionparam->courseid = $params['courseid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }
        course_require_view_participants($context);
        if (!has_capability('moodle/site:accessallgroups', $context) &&
            $DB->record_exists('dialogue', ['id' => $cm->instance, 'usecoursegroups' => 1])) {

            // When a student is in multiple groups, the core filters don't support this easily so we have to check each user
            // This is in-efficient but hopefully not too nasty.

            // Try to filter based on group.
            $groups = groups_get_activity_allowed_groups($cm);

            $manager = new \mod_dialogue\local\course_enrolment_manager($PAGE, $course);
            $users = $manager->search_users_with_groups($params['search'],
                                                        $params['searchanywhere'],
                                                        $params['page'],
                                                        $params['perpage'],
                                                        $groups);

        } else {
            $manager = new course_enrolment_manager($PAGE, $course);
            $users = $manager->search_users($params['search'],
                                            $params['searchanywhere'],
                                            $params['page'],
                                            $params['perpage']);
        }

        $results = [];
        // Add also extra user fields.
        $requiredfields = array_merge(
            ['id', 'fullname', 'profileimageurl', 'profileimageurlsmall'],
            // TODO Does not support custom user profile fields (MDL-70456).
            \core_user\fields::get_identity_fields($context, false)
        );
        // ecastro ULPGC
        $potentialusers = self::potential_users($context, $cm);
        // ecastro ULPGC
        foreach ($users['users'] as $user) {
            // Don't include logged in user as a possible user.
            if ($user->id == $USER->id) {
                continue;
            }
            if (!has_capability('mod/dialogue:receive', $context, $user)) {
                // User does not have the ability to receive so remove them.
                continue;
            }

            // ecastro ULPGC 
            if(is_array($potentialusers) && !in_array($user->id, $potentialusers)) {
                // User is not in allowed userids
                continue;
            }
            // ecastro ULPGC 

            if ($userdetails = user_get_user_details($user, $course, $requiredfields)) {
                $results[] = $userdetails;
            }
        }
        return $results;
    }

    /**
     * Returns description of method result value
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        global $CFG;
        require_once($CFG->dirroot . '/user/externallib.php');
        return new external_multiple_structure(core_user_external::user_description());
    }

    /**
     * Potential users.
     *
     * @author Enrique Castro @ULPGC     
     * @param int $courseid Course id
     * @return array of userids
     */
    public static function potential_users(\context_module $context, \cm_info $cm) {
        global $DB, $USER;

        $dialogue = $DB->get_record('dialogue', array('id' => $cm->instance), '*', MUST_EXIST);
        $alternatemode = $dialogue->alternatemode;
        $singleconversations = (!$dialogue->multipleconversations && 
                                !has_capability('mod/dialogue:openasstaff', $context));
        $usecoursegroups = $dialogue->usecoursegroups;
        $groupmode = ($cm->groupmode && 
                        !has_capability('moodle/site:accessallgroups', $context));
        $receivingcap = 'mod/dialogue:receive';
        if($alternatemode) {
            if(has_capability('mod/dialogue:openasstaff', $context)) {
                $receivingcap = 'mod/dialogue:receive';
            } else {
                $receivingcap = 'mod/dialogue:receiveasstaff';
            }
        }
        
        if(!$alternatemode && !$singleconversations && !$groupmode) {
            return false;
        }
        
        $params = array();
        $wheres = array();
        $wheresql  = '';
    
        list($esql, $eparams) = get_enrolled_sql($context, $receivingcap, 0, true, false, 0); // ecastro to allow alternate opening teacheres/students
        $params = array_merge($params, $eparams);    
    
        $basesql = "FROM {user} u
                    JOIN ($esql) je ON je.id = u.id";

        // current user doesn't need to be in list
        $wheres[] = "(u.id != {$USER->id})";
                    
        if ($groupmode && $usecoursegroups) { // ecastro ULPGC
            $allgroups = array_keys(groups_get_activity_allowed_groups($cm)); // ecastro ULPGC
            if ($allgroups) {
                list($ingmsql, $ingmparams) = $DB->get_in_or_equal($allgroups, SQL_PARAMS_NAMED, 'gm');
                $groupsql = "u.id IN (SELECT gm.userid FROM {groups_members} gm WHERE gm.groupid $ingmsql)";
                $params = array_merge($params, $ingmparams);
                $viewallgroupsusers = get_users_by_capability($context, 'moodle/site:accessallgroups', 'u.id');
                if (!empty($viewallgroupsusers)) {
                    list($agsql, $agparams) = $DB->get_in_or_equal(array_keys($viewallgroupsusers), SQL_PARAMS_NAMED, 'ag');
                    $params = array_merge($params, $agparams);
                    $wheres[] =  "($groupsql OR u.id $agsql)";
                } else {
                    $wheres[] =  "($groupsql)";
                }

            }
        }
        
        // if not multiple conversations, exclude all users with existing open conversations
        if($singleconversations) { // ecastro ULPGC tu enforce single conversation by user
            $sql = "SELECT dp2.id, dp2.userid
                    FROM {dialogue_participants} dp 
                    JOIN {dialogue_participants} dp2 ON dp2.dialogueid = dp.dialogueid AND dp2.conversationid = dp.conversationid  AND dp2.userid <> dp.userid  
                    WHERE dp.dialogueid = :dialogue AND dp.userid = :user 
                    AND NOT EXISTS( SELECT 1 FROM {dialogue_messages} dm 
                                    WHERE dm.dialogueid = dp.dialogueid AND dm.conversationid = dp.conversationid AND dm.state = 'closed')
                        ";
            if($others = $DB->get_records_sql_menu($sql, array('dialogue' =>$dialogue->id  , 'user'=>$USER->id))) {
                list($ocsql, $ocparams) = $DB->get_in_or_equal($others, SQL_PARAMS_NAMED, 'oc'); 
                $params = array_merge($params, $ocparams);
                $wheres[] =  " NOT (u.id $ocsql) ";                    
            }
        }    
        
        if (!empty($wheres)) {
            $wheresql = " WHERE " . implode(" AND ", $wheres);
        }
        
        $sql = "SELECT u.id, u.username " . $basesql. $wheresql;
        $userids = $DB->get_records_sql_menu($sql, $params);
        return array_keys($userids);
    }
}
