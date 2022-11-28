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
 * Utility class for the group / team sync feature.
 *
 * @package local_o365teams
 * @author Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2022 onwards Enrique Castro
 */

namespace local_o365teams\coursegroups;

use context_course;
use Exception;
use local_o365\httpclient;
use local_o365\oauth2\clientdata;
use local_o365teams\rest\unified;
use local_o365\feature\coursesync\main as coursesync;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * A utility class for the group / team sync feature.
 */
class utils extends \local_o365\feature\coursesync\utils {

    /**
     * Determine whether the course sync feature is enabled for teams or usergroups
     *
     * @param bool $both if check both teams & usergroups and return eith .     
     * @return mixed bool|array of bool, True if group creation is enabled. False otherwise.
     */
    public static function enabled_mode(bool $both = false) {
        $coursesyncsetting = get_config('local_o365', 'coursesync');
        $createteams = ($coursesyncsetting === 'oncustom' || $coursesyncsetting === 'onall');
        $createusergroups = get_config('local_o365teams', 'createusergroups');
        $teamsprivatechannels = get_config('local_o365teams', 'teamsprivatechannels');
        
        return [(bool)$createteams, (bool)$createusergroups, (bool)$teamsprivatechannels];
    }    
    
    /**
     * Create connection to graph.
     * @return false|object Graph api.
     */
    public static function get_graphclient() {
        if (\local_o365\utils::is_configured() !== true) {
            return false;
        }

        if (static::is_enabled() !== true) {
            return false;
        }

        $httpclient = new httpclient();
        $clientdata = clientdata::instance_from_oidc();
        $tokenresource = unified::get_tokenresource();
        $unifiedtoken = \local_o365\utils::get_app_or_system_token($tokenresource, $clientdata, $httpclient);

        if (empty($unifiedtoken)) {
            return false;
        }

        return new unified($unifiedtoken, $httpclient);
    }

    /**
     * Get a Microsoft Graph API instance.
     *
     * @param string $caller The calling function, used for logging.
     * @return unified|bool A Microsoft Graph API instance.
     */
    public static function get_unified_api(string $caller = 'local_o365/feature/coursesync/get_unified_api') {
        $clientdata = clientdata::instance_from_oidc();
        $httpclient = new httpclient();
        $tokenresource = unified::get_tokenresource();
        $token = \local_o365\utils::get_app_or_system_token($tokenresource, $clientdata, $httpclient);
        if (!empty($token)) {
            return new unified($token, $httpclient);
        } else {
            $msg = 'Couldn\'t construct Microsoft Graph API client because we didn\'t have a system API user token.';
            \local_o365\utils::debug($msg, $caller);
            return false;
        }
    }

    /**
     * Return the object IDs of users who have Team owner capability in the course with the given ID.
     * Modified from parent: check no owner and add a default owner if suitable
     *
     * @param int $courseid
     * @return array
     */
    public static function get_team_owner_object_ids_by_course_id(int $courseid) : array {
        mtrace(" This is local_o365teams XXXX version"); 
        \local_o365\utils::debug(" This is local_o365teams XXXX version", "get_team_owner_object_ids_by_course_id");
    
        $teamownerobjectids = parent::get_team_owner_object_ids_by_course_id($courseid);
    
        if (empty($teamownerobjectids)) {
            $createnoownerteams = get_config('local_o365teams', 'createnoownerteams');
            $defaultowner = get_config('local_o365teams', 'defaultowner');
            if($createnoownerteams && $defaultowner) {
                $teamownerobjectids[] = $defaultowner;
            } 
        }
        
        return $teamownerobjectids;
    }    
    

    /**
     * Return the object IDs of users who have Channel owner capability in the course and group with the given IDs.
     *
     * @param int $courseid
     * @param int $groupid ID of Moodle course group
     * @return array
     */
    public static function get_channel_owner_object_ids_by_coursegroup(int $courseid, int $groupid) : array {
        $channelownerobjectids = [];
        $channelowneruserids = static::get_channel_owner_user_ids_by_coursegroup($courseid, $groupid);
        if ($channelowneruserids) {
            $channelownerobjectids = static::get_user_object_ids_by_user_ids($channelowneruserids);
        }

        if (empty($channelownerobjectids)) {
            $createnoownerteams = get_config('local_o365teams', 'createnoownerteams');
            $defaultowner = get_config('local_o365teams', 'defaultowner');
            if($createnoownerteams && $defaultowner) {
                $channelownerobjectids[] = $defaultowner;
            } 
        }
        return $channelownerobjectids;
    }

    /**
     * Helper function to retrieve users who have Channel owner capability in the course and group with the given IDs.
     *
     * @param int $courseid ID of Moodle course
     * @param int $groupid ID of Moodle course group
     * @return array array containing IDs of teachers.
     */
    public static function get_channel_owner_user_ids_by_coursegroup(int $courseid, int $groupid) : array {
        $context = context_course::instance($courseid);
        // get users with both both teamowner and moodle/site:accessallgroups in course 
        $channelownerusers = get_users_by_capability($context, 'local/o365:teamowner', 'u.id, u.deleted', 
                                                     ''. ''. '', $groupid, '', null, null, true);
        $channelowneruserids = [];
        foreach ($channelownerusers as $user) {
            if (!$user->deleted) {
                $channelowneruserids[] = $user->id;
            }
        }

        return $channelowneruserids;
    }

    /**
     * Return the object IDs of users who have Team member capability in the course and group with the given IDs.
     *
     * @param int $courseid
     * @param int $groupid ID of Moodle course group
     * @return array
     */
    public static function get_channel_member_object_ids_by_coursegroup(int $courseid, int $groupid) : array {
        $channelmemberobjectids = [];
        $channelmemberuserids = static::get_channel_member_user_ids_by_coursegroup($courseid, $groupid);
        if ($channelmemberuserids) {
            $channelmemberobjectids = static::get_user_object_ids_by_user_ids($channelmemberuserids);
        }

        return $channelmemberobjectids;
    }

    /**
     * Helper function to retrieve users who have Team member capability in the course and group with the given IDs.
     *
     * @param int $courseid ID of the Moodle course
     * @param int $groupid ID of Moodle course group
     * @return array
     */
    public static function get_channel_member_user_ids_by_coursegroup(int $courseid, int $groupid) : array {
        $context = context_course::instance($courseid);
        $channelmemberusers = get_users_by_capability($context, 'local/o365:teammember', 'u.id, u.deleted');
        $channelmemberuserids = [];
        foreach ($channelmemberusers as $user) {
            if (!$user->deleted) {
                $channelmemberuserids[] = $user->id;
            }
        }

        return $channelmemberuserids;
    }    
    
///////////////////////////////////////////////////////////////////////////////    
//// Team / Group names related routines
//////////////////////////////////////////////////////////////////////////////    
    
    /**
     * Return the display name of Team for the given course according to configuration.
     *
     * @param stdClass $course
     * @param string $forcedprefix
     * @param stdClass $group (not used here)
     *
     * @return string
     */
    public static function get_team_display_name(stdClass $course, string $forcedprefix = '', stdClass $group = null) {
        if ($forcedprefix) {
            $teamdisplayname = $forcedprefix;
        } else {
            $teamdisplayname = '';
        }

        $teamnameprefix = get_config('local_o365', 'team_name_prefix');
        if ($teamnameprefix) {
            $teamdisplayname .= $teamnameprefix;
        }

        $sitelabel = get_config('local_o365teams', 'sitelabel');   
        
        $teamnamecourse = get_config('local_o365', 'team_name_course');
        switch ($teamnamecourse) {
            case coursesync::NAME_OPTION_FULL_NAME:
                if(!$sitelabel) {
                    $sitelabel = ' -';
                }
                // always use shortname
                $teamdisplayname .= $course->shortname.$sitelabel.' ';
                $teamdisplayname .= $course->fullname;
                // do not add sitelabel again below
                $sitelabel = '';
                break;
            case coursesync::NAME_OPTION_SHORT_NAME:
                $teamdisplayname .= $course->shortname;
                break;
            case coursesync::NAME_OPTION_ID:
                $teamdisplayname .= $course->id;
                break;
            case coursesync::NAME_OPTION_ID_NUMBER:
                $teamdisplayname .= $course->idnumber;
                break;
            default:
                $teamdisplayname .= $course->fullname;
        }
        
        $teamdisplayname .= $sitelabel; 

        $teamnamesuffix = get_config('local_o365', 'team_name_suffix');
        if ($teamnamesuffix) {
            $teamdisplayname .= $teamnamesuffix;
        }

        return substr($teamdisplayname, 0, 256);
    }


    /**
     * Return the display name of group for the given course according to configuration.
     *
     * @param stdClass $course
     * @param stdClass|null $group
     * @param string $forcedprefix
     *
     * @return string
     */
    public static function get_group_display_name(stdClass $course, stdClass $group = null, $forcedprefix = '') {
        if ($forcedprefix) {
            $groupdisplayname = $forcedprefix;
        } else {
            $groupdisplayname = '';
        }

        $groupdisplaynameprefix = get_config('local_o365', 'group_mail_alias_prefix');
        if ($groupdisplaynameprefix) {
            $groupdisplayname .= $groupdisplaynameprefix;
        }

        $sitelabel = get_config('local_o365teams', 'sitelabel'); 
        
        $groupdisplaynamecourse = get_config('local_o365', 'group_mail_alias_course');
        switch ($groupdisplaynamecourse) {
            case coursesync::NAME_OPTION_FULL_NAME:
                if (empty($group) ) {  
                    //this is a name for a course
                    $groupdisplayname .= $course->shortname;
                    if($sitelabel) {
                        $groupdisplayname .= $sitelabel.' ';
                        $sitelabel = ''; // do not duplicate below
                    } else {
                        $groupdisplayname .= '-';
                    }
                }
                
                $groupdisplayname .= $course->fullname;
                break;
            case coursesync::NAME_OPTION_SHORT_NAME:
                $groupdisplayname .= $course->shortname;
                break;
            case coursesync::NAME_OPTION_ID:
                $groupdisplayname .= $course->id;
                break;
            case coursesync::NAME_OPTION_ID_NUMBER:
                $groupdisplayname .= $course->idnumber;
                break;
            default:
                $groupdisplayname .= $course->fullname;
        }

        if ($group) {
            if($sitelabel) { 
                $groupdisplayname .= $sitelabel.' ';
            } else {
                $groupdisplayname .= '-';
            }
            $groupdisplayname .= $group->name;
        } else {
            $groupdisplayname .= $sitelabel; 
        } 
        
        $groupdisplaynamesuffix = get_config('local_o365', 'group_mail_alias_suffix');
        if ($groupdisplaynamesuffix) {
            $groupdisplayname .= $groupdisplaynamesuffix;
        }

        return substr($groupdisplayname, 0, 264);
    }

    /**
     * Return the email alias of group for the given course according to configuration.
     *
     * @param stdClass $course
     * @param stdClass|null $group
     *
     * @return string
     */
    public static function get_group_mail_alias(stdClass $course, stdClass $group = null) : string {
        $groupmailaliasprefix = get_config('local_o365', 'group_mail_alias_prefix');
        if ($groupmailaliasprefix) {
            $groupmailaliasprefix = static::clean_up_group_mail_alias($groupmailaliasprefix);
        }

        $groupmailaliassuffix = get_config('local_o365', 'group_mail_alias_suffix');
        if ($groupmailaliassuffix) {
            $groupmailaliassuffix = static::clean_up_group_mail_alias($groupmailaliassuffix);
        }

        $sitelabel = get_config('local_o365teams', 'sitelabel');
        
        $groupmailaliascourse = get_config('local_o365', 'group_mail_alias_course');
        switch ($groupmailaliascourse) {
            case coursesync::NAME_OPTION_FULL_NAME:
                if($sitelabel) {
                    $sitelabel .= '-';
                }
                $coursepart = $course->shortname.'-'.$sitelabel;
                $coursepart .= $course->fullname;
                $sitelabel = '';            
/*            
                if (empty($group) ) {   
                    //this is a name for a course
                    $coursepart = $course->shortname.'-';
                    if($sitelabel) {
                        $coursepart .= $sitelabel.'-';
                        $sitelabel = ''; // do not duplicate below
                    }
                } else {
                    $coursepart = $course->fullname;
                }
                */
                break;
            case coursesync::NAME_OPTION_SHORT_NAME:
                $coursepart = $course->shortname;
                break;
            case coursesync::NAME_OPTION_ID:
                $coursepart = $course->id;
                break;
            case coursesync::NAME_OPTION_ID_NUMBER:
                $coursepart = $course->idnumber;
                break;
            default:
                $coursepart = $course->shortname;
        }

        if (!empty($group)) {
            //$grouppart = $group->id . '_' . $group->name;
            $grouppart = $group->name;
            $grouppart = static::clean_up_group_mail_alias($grouppart);
            if (strlen($grouppart) > 16) {
                $grouppart = substr($grouppart, 0, 16);
            }
            if($sitelabel) { 
                $grouppart = $sitelabel.'-'.$grouppart;
            }            
            $grouppart = '-' . $grouppart;
            
        } else {
            $grouppart = '';
            if($sitelabel) {
                $coursepart .= '-'.$sitelabel; 
            }
        }

        
        
        $grouppart = static::clean_up_group_mail_alias($grouppart); 
        $coursepart = static::clean_up_group_mail_alias($coursepart);

        $coursepartmaxlength = 64 - strlen($groupmailaliasprefix) - strlen($groupmailaliassuffix) - strlen($grouppart);
        if (strlen($coursepart) > $coursepartmaxlength) {
            $coursepart = substr($coursepart, 0, $coursepartmaxlength);
        }
        
        return $groupmailaliasprefix . $coursepart . $grouppart . $groupmailaliassuffix;
    }

    /**
     * Remove unsupported characters from the mail alias parts, and return the result.
     *
     * @param string $mailalias
     *
     * @return string|string[]|null
     */
    public static function clean_up_group_mail_alias($mailalias) {
        
        // this step  removes any NON-utf8 chars
        $mailalias = mb_convert_encoding( $mailalias, "UTF-8", "UTF-8");
        // this step transliterate making plain ascii letters from UTF-8 symbols
        $mailalias = iconv("UTF-8", "ASCII//TRANSLIT", $mailalias); 
    
        return preg_replace('/[^a-z0-9-_]+/iu', '', $mailalias);
    }

    /**
     * Return the display name and the mail alias of the group of the sample course.
     *
     * @return array
     */
    public static function get_sample_usergroup_names() {
        $samplecourse = static::get_team_group_name_sample_course();
        
        return [static::get_group_display_name($samplecourse, $samplecourse), static::get_group_mail_alias($samplecourse, $samplecourse)];
    }

    /**
     * Return a stdClass object representing a course object to be used for Team / group naming convention example.
     *
     * @return stdClass
     */
    public static function get_team_group_name_sample_course() : stdClass  {
        $samplecourse = new stdClass();
        $samplecourse->fullname = 'Sample course dos grandes expressos européus y españoles';
        $samplecourse->shortname = '99456';
        $samplecourse->id = 253879;
        $samplecourse->idnumber = '4087_40_00_1_1_72954_365';
        $samplecourse->name = "Sample group Teoría 01 e@#€paÑOLA";

        return $samplecourse;
    }

    
    /**
     * Helper function to retrieve teams group object.
     *
     * @param int $courseid Id of Moodle course.
     * @param string $type either "courseteam" or "teamfromgroup" or empty for both
     * @return object Object containing o365 object id.
     */
    public static function get_teams_object(int $courseid, string $type = '') {
        global $DB;
        
        $subtypes = ['courseteam', 'teamforgroup'];
        if($type) {
            $subtypes = [$type];
        }
        [$insql, $params] = $DB->get_in_or_equal($subtypes);
        $select = "subtype $insql AND type = ? AND moodleid = ? ";
        $params[] = 'group';
        $params[] = $courseid;

        $object = $DB->get_record_select('local_o365_objects', $select, $params, IGNORE_MULTIPLE);
        if (empty($object)) {
            return false;
        }
        return $object;
    }    
    
    
    /**
     * Helper function to retrieve study group object.
     *
     * @param int $groupid Id of Moodle group.
     * @return object Object containing o365 object id.
     */
    public static function get_usergroup_object(int $groupid) {
        global $DB;
        $params = [
            'type' => 'group',
            'subtype' => 'usergroup',
            'moodleid' => $groupid
        ];
        $object = $DB->get_record('local_o365_objects', $params);
        if (empty($object)) {
            return false;
        }
        return $object;
    }    
    
    /**
     * Helper function to retrieve channel object associated to a moodle group.
     *
     * @param int $groupid Id of Moodle group.
     * @return object Object containing o365 object id.
     */
    public static function get_channel_object(int $groupid) {
        global $DB;
        $params = [
            'type' => 'group',
            'subtype' => 'teamchannel',
            'moodleid' => $groupid
        ];
        $object = $DB->get_record('local_o365_objects', $params);
        if (empty($object)) {
            return false;
        }
        return $object;
    }       
    
    
    
    
}
