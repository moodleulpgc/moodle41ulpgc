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
 * Course sync to group / team / channel / feature.
 *
 * @package local_o365teams
 * @author Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2022 onwards Enrique Castro
 */

namespace local_o365teams\coursegroups;

use context_course;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Course sync to group / team class.
 */
class teamschannels extends \local_o365\feature\coursesync\main {

    /**
     * Get the Group description by cleaning & shortening the course summary
     *
     * @param stdClass $course A course record.
     * @return array Array form of the created local_o365_objects record.
     * @author Enrique Castro <@ULPGC>
     */
    public function format_description($summary) {    
        $description = html_to_text($summary);
        if (strlen($description) > 1024) {
            $description = shorten_text($description, 1024, true, ' ...');
        }
        return $description;
    }
    
    
    /**
     * Checks a team exists, 
     *
     * @param string $teamsobjectid
     * @return mixed false or team data as object
     * @author Enrique Castro <@ULPGC>
     */
    public function get_team($teamsobjectid) {    
        try {
            [$team, $teamurl, $lockstatus] = $this->graphclient->get_team($teamsobjectid);
        } catch (\Exception $e) {
            $this->mtrace('Team with ID ' . $teamsobjectid . 
                            ' is not found in o365.  Reason: '.$e->getMessage());
            return false;
        }
    
        return (object)$team;
    }    
    
    
    /**
     * Get channel info.
     *
     * @param string $teamsobjectid The object ID of the team.
     * @param string $channelobjectid The object ID of the channel.    
     * @return mixed false or team data as object
     */
    public function get_channel($teamsobjectid, $channelobjectid, $notify = true) {
        try {
            if($response = $this->graphclient->get_channel($teamsobjectid, $channelobjectid, $notify)) {
                $response = (object)$response;    
            }
        } catch (\Exception $e) {
            $this->mtrace('Channel with ID ' . $channelobjectid . 
                            ' is not found withinin team ' . $teamsobjectid . '.  Reason: '.$e->getMessage());
            return false;
        }
    
        return $response;    
    }
    
    /**
     * Get group info.
     *
     * @param string $objectid The object ID of the user group.
     * @return mixed false or team data as object
     */
    public function get_usergroup($objectid) {
        try {
            if($response = $this->graphclient->get_group($objectid)) {
                $response = (object)$response;    
            }
        } catch (\Exception $e) {
            $this->mtrace('user group with ID ' . $objectid . 
                            ' is not found in o365 .  Reason: '.$e->getMessage());
            return false;
        }
    
        return $response;    
    }
    
    
    /**
     * Add private channels in a course Teams for each main group in course.
     *
     * @param string $teamsobjectid
     * @return mixed
     * @throws \moodle_exception
     * @author Enrique Castro <@ULPGC>
     */
    public function add_course_channels($courseid, $teamsobjectid, $owners = null) {    
        global $DB;
        
        // ecastro ULPGC add channels
        $addchannels = get_config('local_o365teams', 'teamsprivatechannels');
        if(!$addchannels) {
            return;
        }
        
        $params = ['type' => 'group', 'subtype' => 'teamchannel', 'courseid' => $courseid];
        $sql = "SELECT g.id, g.name, g.description, g.idnumber, g.courseid
            FROM {groups} g
            LEFT JOIN {local_o365_objects} obj ON obj.type = :type AND obj.subtype = :subtype AND obj.moodleid = g.id 
            WHERE g.courseid = :courseid AND g.idnumber != '' AND obj.objectid IS NULL  ";
        
        $groups = $DB->get_records_sql($sql, $params);
        if($channelpattern = get_config('local_o365teams', 'channelpattern')) {
            // this means regex is NOT performed
            foreach($groups as $gid => $group) {
                if(!empty(preg_replace('/'.trim($channelpattern).'/', '', $group->idnumber))) {
                    unset($groups[$gid]);
                }
            }
        }
  
        if($groups && (count($groups) > 1)) {
            // there are groups, create a channel for each
            foreach($groups as $group) {
                $owners = utils::get_team_owner_object_ids_by_course_id($courseid, $group->id);
                $channelobject = $this->create_group_channel($teamsobjectid, $owners, $group);
                if (!empty($channelobject->objectid)) {
                    $retrycounter = 0;
                    while ($retrycounter <= API_CALL_RETRY_LIMIT) {
                        if ($retrycounter) {
                            $this->mtrace('..... Retry #' . $retrycounter);
                            sleep(10);
                        }
                        try {
                            $this->resync_channel_membership($courseid, $group->id, $teamsobjectid, $channelobject->objectid); 
                            break;
                        } catch (\Exception $e) {
                            $this->mtrace('Could not sync users to Team channel for group ' . $group->id. 
                                            ' in course #' . $group->courseid . '. Reason: ' . $e->getMessage());
                            $retrycounter++;
                        }
                    }                    
                }
            }
        }
    }  
    

    /**
     * Create a private channel for a moodle group in a Teams.
     *
     * @param string $teamsobjectid
     * @param array $owner team owners office3365 IDs
     * @param stdClass $group a moodle group object
     *
     * @return mixed
     * @throws \moodle_exception
     * @author Enrique Castro <@ULPGC>
     */
    public function create_group_channel($teamsobjectid, $owners, $group) {
        global $DB;
        
        $this->mtrace('Create team channel for course #' . $group->courseid .', group: '. $group->name);
        $now = time();
        
        if(!is_array($owners)) {
            $owners = array($owners);
        }
        
        $defaultowner = get_config('local_o365teams', 'defaultowner');
        
        if(!$owners && $defaultowner) {
            $owners = [$defaultowner];
            $this->mtrace('    ... using default owner');
        }
        
        // Create channel first.
        $response = null;
        $retrycounter = 0;
        while ($retrycounter <= API_CALL_RETRY_LIMIT) {
            if ($retrycounter) {
                $this->mtrace('..... Retry #' . $retrycounter);
                sleep(10);
            }
            try {
                $response = $this->graphclient->create_group_channel($teamsobjectid, $owners, format_string($group->name), $this->format_description($group->description));
                break;
            } catch (\Exception $e) {
                $this->mtrace('Could not create channel for group ' . $group->id . 
                                ' in course #'.$group->courseid.'. Reason: '.$e->getMessage());
                $retrycounter++;
            }
        }        
        
        if(empty($response['id'])) { 
            return false;
        }
        $this->mtrace('Created channel ' . $response['id'] . ' for group ' . $group->id .
                        ' within Team ' . $teamsobjectid . ' in course #' . $group->courseid);
        $channelobjectrec = [
            'type' => 'group',
            'subtype' => 'teamchannel',
            'objectid' => $response['id'],
            'moodleid' => $group->id,
            'o365name' => $response['displayName'],
            'metadata' => json_encode($response),
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $channelobjectrec['id'] = $DB->insert_record('local_o365_objects', (object)$channelobjectrec);
        $this->mtrace('Recorded channel object (' . $channelobjectrec['objectid'] . ') into object table with record id '
            . $channelobjectrec['id']);

        // make owners also members, except defaultowner 
        //recommended to bypass membership search quirks in other APIs    
        unset($owners[$defaultowner]);
        $this->mtrace('Adding owners as members too, into newly created channel');
        foreach($owners as $memberid) {
            $this->graphclient->add_member_to_channel($teamsobjectid, $channelobjectrec['objectid'], $memberid);
            
            
            
            
            
        }
        
        
        
        foreach ($toadd as $userid => $userobjectid) {
            $this->mtrace('... Adding '.$userobjectid.' (muserid: '.$userid.')...', '');
            $retrycounter = 0;
            while ($retrycounter <= API_CALL_RETRY_LIMIT) {
                // Add member of o365 channel.
                if ($retrycounter) {
                    $this->mtrace('...... Retry #' . $retrycounter);
                    sleep(10);
                }
                $result = $this->graphclient->add_member_to_channel($teamsobjectid, $channelobjectrec['objectid'], $memberid);
                if ($result === true) {
                    break;
                } else {
                    $retrycounter++;
                    if (strpos($result, 'Request_ResourceNotFound') === false) {
                        break;
                    }
                }
            }
        }        
        
        
        
        return (object)$channelobjectrec;
    }
    
    
    /**
     * deletes a private channel for a group in a Course with Teams.
     *
     * @param mixed int $groupid or object group
     *
     * @return mixed
     * @throws \moodle_exception
     * @author Enrique Castro <@ULPGC>
     */
    public function check_group_needs_channel($grouporid, $update = false) {
        global $DB;
        
        if(is_int($grouporid)) {
            $group = $DB->get_record('groups', ['id' => $grouporid]);
        } elseif(is_object($grouporid) && isset ($grouporid->idnumber)) { 
            $group = $grouporid;
        } else {
            $caller = 'check_group_needs_channel';
            $msg = 'The object passed is not a valid group object';
            \local_o365\utils::debug($msg, $caller);
            return false;
        }
        
        $params = [
            'type' => 'group',
            'subtype' => 'teamchannel',
            'moodleid' => $group->id
        ];
        if($update &&  $DB->record_exists('local_o365_objects', $params)) {
            return true;
        }        
        $addteams = \local_o365\feature\coursesync\utils::is_course_sync_enabled($group->courseid);
        $pattern = get_config('local_o365teams', 'channelpattern');
        if(!$group->idnumber  || !$addteams || preg_replace("/$pattern/", '', $group->idnumber)) {
            return false;
        }    
    
        return true;
    }


    /**
     * Create a private channel for a group in a Course with Teams.
     *
     * @param int $groupid
     *
     * @return mixed
     * @throws \moodle_exception
     * @author Enrique Castro <@ULPGC>
     */
    public function add_channel_for_group($groupid) {
        global $DB;
    
        // a hack to avoid error message in observer when called
        $result = new \stdClass();
        $result->objectid =  -1;
        
        $teamschannelrec = $DB->get_record('local_o365_objects',
                ['type' => 'group', 'subtype' => 'teamchannel', 'moodleid' => $groupid]);
        if(isset($teamschannelrec->objectid)  && $teamschannelrec->objectid) {
            return $teamschannelrec;
        }
        
        $caller = 'create_channel_for_group';
        $group = $DB->get_record('groups', ['id' => $groupid]);
        if (empty($group)) {
            \local_o365\utils::debug('Could not find group with id "'.$groupid.'"', $caller);
            return false;
        }

        if (\local_o365\utils::is_configured() !== true || \local_o365\feature\coursesync\utils::is_enabled() !== true) {
            return false;
        }

        if (empty($this->graphclient)) {
            return false;
        }
        
        $courseteamsrec = $DB->get_record('local_o365_objects',
                ['type' => 'group', 'subtype' => 'courseteam', 'moodleid' => $group->courseid]);        
        if (empty($courseteamsrec)) {
            $msg = 'Could not find Team for course with id "'.$group->courseid.'" for group with id "'.$groupid.'"';
            \local_o365\utils::debug($msg, $caller);
            return false;
        }
    
        $owners = utils::get_team_owner_object_ids_by_course_id($group->courseid, $group->id);
        
        return  $this->create_group_channel($courseteamsrec->objectid, $owners, $group); 
    }
    
    /**
     * Update a private channel for a group in a Course with Teams.
     * Tipically, when group name o description has changed
     *
     * @param int $groupid
     *
     * @return mixed
     * @throws \moodle_exception
     * @author Enrique Castro <@ULPGC>
     */
    public function update_channel_for_group($groupid) {   
        global $DB;

        print_object("updating channel for group $groupid");
        
        $addchannels = get_config('local_o365teams', 'teamsprivatechannels');
        $createteams = get_config('local_o365teams', 'createteams');
        if(!$addchannels || !$createteams) {
            return false;
        }
        
        $teamschannelrec = $DB->get_record('local_o365_objects',
                ['type' => 'group', 'subtype' => 'teamchannel', 'moodleid' => $groupid]);
        if(!isset($teamschannelrec->objectid)  || !$teamschannelrec->objectid) {
            // this function only updates, if channel does not exist pr3eviously, do not create
            return false;
        }
        
        $group = $DB->get_record('groups', ['id' => $groupid]);
        if (empty($group)) {
            \local_o365\utils::debug('Could not find group with id "'.$groupid.'"', $caller);
            return $result;
        }
        $courserec = $DB->get_record('course', ['id' => $group->courseid]);
        $addteams = \local_o365\feature\coursesync\utils::is_course_sync_enabled($group->courseid);
        $newgroupname = format_string($group->name); 
        
        $courseteamsrec = $DB->get_record('local_o365_objects',
        ['type' => 'group', 'subtype' => 'courseteam', 'moodleid' => $group->courseid]);        
        
        $response = $this->graphclient->update_group_channel($courseteamsrec->objectid, $teamschannelrec->objectid, $newgroupname, format_text($group->description));

        if ($response) {
            $teamschannelrec->o365name = $newgroupname;
            $teamschannelrec->timemodified = time();
            
            $DB->update_record('local_o365_objects', $teamschannelrec);
                
            // update membership, temporal
            if (!empty($teamschannelrec->objectid)) {
                $retrycounter = 0;
                while ($retrycounter <= API_CALL_RETRY_LIMIT) {
                    if ($retrycounter) {
                        $this->mtrace('..... Retry #' . $retrycounter);
                        sleep(10);
                    }
                    try {
                        $this->resync_channel_membership($group->courseid, $group->id, $courseteamsrec->objectid,  $teamschannelrec->objectid); 
                        break;
                    } catch (\Exception $e) {
                        $this->mtrace('Could not sync users to Team channel for group ' . $group->id. 
                                        ' in course #' . $group->courseid . '. Reason: ' . $e->getMessage());
                        $retrycounter++;
                    }
                }                 
            }                
            return $teamschannelrec;
        }
        
        return false;
    }
    
    /**
     * deletes a private channel for a group in a Course with Teams.
     *
     * @param int $groupid
     * @param string $teamsobjectid, the azure ID of the course Team
     *
     * @return mixed
     * @throws \moodle_exception
     * @author Enrique Castro <@ULPGC>
     */
    public function remove_group_channel($courseid, $groupid, $teamsobjectid = null) {
        global $DB;

        $params = [
            'type' => 'group',
            'subtype' => 'teamchannel',
            'moodleid' => $groupid,
        ];
        $channelobject = $DB->get_record('local_o365_objects', $params);
        if (empty($channelobject)) {
            $errmsg = 'Could not find channel object ID in local_o365_objects for moodle group # '.$groupid.'. ';
            $errmsg .= 'Please ensure channel exists first.';
            $this->mtrace($errmsg);
            return false;
        }
        $channelobjectid = $channelobject->objectid;

        if ($teamsobjectid === null) {
            $courseteamsrec = $DB->get_record('local_o365_objects',
                    ['type' => 'group', 'subtype' => 'courseteam', 'moodleid' => $courseid]);        
            if (empty($courseteamsrec)) {
                $msg = 'Could not find Team for course with id "'.$group->courseid.'" for group with id "'.$groupid.'"';
                \local_o365\utils::debug($msg, $caller);
                return false;
            }
            $teamsobjectid = $courseteamsrec->objectid;
        }    
    
        $deleted = $this->graphclient->delete_group_channel($teamsobjectid, $channelobjectid);
        $success = false;
        if(($deleted === true) || strpos($deleted, '"error":{"code":"NotFound"')) {
            // teams channel successfully removed, delete table row associated
            $params = [
                'type' => 'group',
                'subtype' => 'teamchannel',
                'moodleid' => $groupid,
                'objectid' => $channelobjectid,
            ];
            $success = $DB->delete_records('local_o365_objects', $params);
        }
    
        return $success;
    }
    
    
    /**
     * Obtain current channel owners azure ids from remote o365 site
     *
     * @param string $teamsbjectid  The object ID of the office 365 Team group.     
     * @param string $channelbjectid The object ID of the office 365 Channel.
     */
    public function get_channel_owners($teamsobjectid, $channelobjectid) {
        $owners = [];
        $users = $this->get_channel_users($teamsobjectid, $channelobjectid);
        foreach($users as $user) {
            if(in_array('owner', $user['roles'])) {
                $owners[] = $user['userId'];
            }
        }
        return $owners;
    }

    /**
     * Obtain current channel members azure ids from remote o365 site
     *
     * @param string $teamsbjectid  The object ID of the office 365 Team group.     
     * @param string $channelbjectid The object ID of the office 365 Channel.
     */
    public function get_channel_members($teamsobjectid, $channelobjectid) {
        $members = [];
        $users = $this->get_channel_users($teamsobjectid, $channelobjectid);
        foreach($users as $user) {
            if(!in_array('owner', $user['roles'])) {
                $members[] = $user['userId'];
            }
        }
        return $members;
    }
    
    
    /**
     * Obtain current channel members, with role, from remote o365 site
     *
     * @param string $teamsbjectid  The object ID of the office 365 Team group.     
     * @param string $channelbjectid The object ID of the office 365 Channel.
     */
    public function get_channel_users($teamsobjectid, $channelobjectid) {
    
        $user = new \stdClass();
        $members = [];
    
        try {
            $response = $this->graphclient->get_channel_members($teamsobjectid, $channelobjectid);
            if(isset($response['value'])) {
                foreach($response['value'] as $member) {
                    $user->id = $member['userId'];
                    $user->displayname = $member['displayName'];
                    $user->roles = $member['roles'];
                    $user->email = $member['email'];
                    $members[$member['userId']] = clone $user;
                }
            } else {
                $this->mtrace('Could not obtain channel members for channel. Response '.var_export($response, true));
            }
        } catch (\Exception $e) {
            $this->mtrace('Could not obtain channel members for channel ' . $channelobjectid . '. Reason: ' . $e->getMessage());
        }    
        
        return $members;
    }
    
    
    /**
     * Resync the membership of a teams channel based on the users enrolled in the associated group for the channel
     *
     * @param int $courseid The ID of the course.
     * @param int $groupid The ID of the group.
     * @param string $teamsobjectid  The object ID of the office 365 Team group.          
     * @param string $channelobjectid The object ID of the office 365 Channel.
     */
    public function resync_channel_membership($courseid, $groupid, $teamsobjectid = null, $channelobjectid = null) {
        global $DB;

        $this->mtrace('Syncing channel membership for group #'.$groupid);
    
        if ($channelobjectid === null) {
            $params = [
                'type' => 'group',
                'subtype' => 'teamchannel',
                'moodleid' => $groupid,
            ];
            $channelobject = $DB->get_record('local_o365_objects', $params);
            if (empty($channelobject)) {
                $errmsg = 'Could not find channel object ID in local_o365_objects for moodle group # '.$groupid.'. ';
                $errmsg .= 'Please ensure channel exists first.';
                $this->mtrace($errmsg);
                return false;
            }
            $channelobjectid = $channelobject->objectid;
        }

        if ($teamsobjectid === null) {
            $courseteamsrec = $DB->get_record('local_o365_objects',
                    ['type' => 'group', 'subtype' => 'courseteam', 'moodleid' => $courseid]);        
            if (empty($courseteamsrec)) {
                $msg = 'Could not find Team for course with id "'.$group->courseid.'" for group with id "'.$groupid.'"';
                \local_o365\utils::debug($msg, $caller);
                return false;
            }
            $teamsobjectid = $courseteamsrec->objectid;
        }
        
        $currentowners $this->get_channel_owners($teamsobjectid, $channelobjectid));
        $currentmembers = $this->get_channel_members($teamsobjectid, $channelobjectid));
        
        $intendedowners = utils::get_channel_owner_object_ids_by_coursegroup($courseid, $groupid);
        $intendedmembers = utils::get_channel_member_object_ids_by_coursegroup($courseid, $groupid);
        
        if (!empty($currentowners)) {
            $toaddowners = array_diff($intendedteamowners, $currentowners);
            $toremoveowners = array_diff($currentowners, $intendedteamowners);
        } else {
            $toaddowners = $intendedteamowners;
            $toremoveowners = [];
        }

        if (!empty($currentmembers)) {
            $toaddmembers = array_diff($intendedteammembers, $currentmembers);
            $toremovemembers = array_diff($currentmembers, $intendedteammembers);
        } else {
            $toaddmembers = $intendedteammembers;
            $toremovemembers = [];
        }

        //Check if channel object is created
        $this->mtrace('... Checking if channel is setup ...', '');
        $retrycounter = 0;
        while ($retrycounter <= API_CALL_RETRY_LIMIT) {
            try {
                if ($retrycounter) {
                    $this->mtrace('...... Retry #' . $retrycounter);
                    sleep(10);
                }
                $result = $this->graphclient->get_channel($teamsobjectid, $channelobjectid);
                if (!empty($result['id'])) {
                    $this->mtrace('Success!');
                    break;
                } else {
                    $this->mtrace('Error!');
                    $this->mtrace('...... Received: ' . \local_o365\utils::tostring($result));
                    $retrycounter++;
                }
            } catch (\Exception $e) {
                $this->mtrace('Error!');
                $this->mtrace('...... Received: ' . $e->getMessage());
                $retrycounter++;
            }
        }        
        
        $added = 0;
        $removed = 0;
        
        // Remove users.
        $toremove = array_unique($toremoveowners + $toremovemembers); 
        unset($toremoveowners);
        unset($toremovemembers);
        $this->mtrace('Users to remove: '.count($toremove));
        // we dot not have moodle userid for remote current users
        foreach ($toremove as $userobjectid) {
            $this->mtrace('... Removing '.$userobjectid.'...', '');
            $result = $this->graphclient->remove_member_from_channel($teamsobjectid, $channelobjectid, $userobjectid);
            if ($result === true) {
                $this->mtrace('Success!');
                $removed ++;
            } else {
                $this->mtrace('Error!');
                $this->mtrace('    ... Received: '.\local_o365\utils::tostring($result));
            }
        }        
        
        // Add users.
        // recommendedd to add owners also as members to allow searching in Teams native interface
        $toaddusers = ['owner' => array_unique($toaddowners), 
                       'member' => array_unique($toaddmembers + $toaddowners)];
        unset($toaddowners);
        unset($toaddmembers);                       
        foreach($toaddusers as $addtype => $toadd) {
            $this->mtrace($addtype.'s to add: '.count($toadd));
            $owner = ($addtype == 'owner');
            foreach ($toadd as $userobjectid) {
                $this->mtrace('... Adding '.$userobjectid.' as '.$addtype.' ...', '');
                $retrycounter = 0;
                while ($retrycounter <= API_CALL_RETRY_LIMIT) {
                    // Add member of o365 channel.
                    if ($retrycounter) {
                        $this->mtrace('...... Retry #' . $retrycounter);
                        sleep(10);
                    }
                    $result = $this->graphclient->add_member_to_channel($teamsobjectid, $channelobjectid, $userobjectid, $owner);
                    if ($result === true) {
                        $this->mtrace('Success!');
                        $added++; 
                        break;
                    } else {
                        $this->mtrace('Error!');
                        $this->mtrace('...... Received: '.\local_o365\utils::tostring($result));
                        $retrycounter++;

                        if (strpos($result, 'Request_ResourceNotFound') === false) {
                            break;
                        }
                    }
                }
            }
        }
        $this->mtrace('Done');

        $done = new \stdClass();
        $done->toadd = count($toaddusers['owner']) + count($toaddusers['member']);
        $done->added = $added;
        $done->toremove = count($toremove);
        $done->removed = $removed;              
        
        return $done;        
    }   
    
    /**
     * Update group name for the given course using this extented settings 
     *
     * @param stdClass $course
     * @param string $groupobjectid azure ID for the course in local_o365_objects table
     * @param string $courseo365name current name for course in local_o365_objects table
     *
     * @return bool
     */    
    public function update_course_group_name_extended(\stdClass $course, string $groupobjectid, string $groupo365name = null, \stdClass $group = null) : bool {
        global $DB
    
        $groupname = utils::get_group_display_name($course, $group);
        $mailalias utils::get_group_mail_alias($course, $group);
        // only update if need
        if($groupname != $groupo365name) {
            $updatedexistinggroup = [
                'id' => $groupobjectid,
                'displayName' => $groupname,
                'mailNickname' => $mailalias,
            ];
        
            $this->graphclient->update_group($updatedexistinggroup);
            
            $objectrecord = new \stdClass;
            $objectrecord->id = $course->oid;
            $objectrecord->o365name = $teamname;
            $objectrecord->timemodified = time();
            $DB->update_record('local_o365_objects', $objectrecord);
        }

        return true;    
    }
    
    
    /**
     * Update group name for the given course using this extented settings 
     *
     * @param stdClass $course
     * @param string $courseobjectid azure ID for the course in local_o365_objects table
     * @param string $courseo365name current name for course in local_o365_objects table
     *
     * @return bool
     */    
    public function update_course_team_name_extended(\stdClass $course, string $courseobjectid, string $courseo365name = null) : bool {
        global $DB;
        
        $teamname = utils::get_team_display_name($course);
        // only updat eid need
        if($teamname != $courseo365name) {
            $this->graphclient->update_team_name($courseobjectid, $teamname);    
            $objectrecord = new \stdClass;
            $objectrecord->id = $course->oid;
            $objectrecord->o365name = $teamname;
            $objectrecord->timemodified = time();
            $DB->update_record('local_o365_objects', $objectrecord);
        }

        return true;    
    }
    
    
///////////////////////////////////////////////////////////////////////////////    
//// User groups (mail groups) related routines
//////////////////////////////////////////////////////////////////////////////    

    /**
     * Helper function to retrieve users who have Team member capability in the course with the given ID.
     *
     * @param int $courseid
     *
     * @return array
     */
    public function get_team_member_ids_by_course_id($courseid) {
        $context = context_course::instance($courseid);
        $teammemberusers = get_users_by_capability($context, 'local/o365:teammember', 'u.id, u.deleted');
        $teammemberuserids = [];
        foreach ($teammemberusers as $user) {
            array_push($teammemberuserids, $user->id);
        }

        return $teammemberuserids;
    }

    /**
     * Update a study group from a Moodle group.
     *
     * @param int $moodlegroupid Id of Moodle  group.
     * @return boolean True on success.
     */
    public function update_usergroup($moodlegroupid) {
        global $DB;
        $caller = 'update_usergroup';
        if (\local_o365\utils::is_configured() !== true || \local_o365\feature\coursesync\utils::is_enabled() !== true) {
            return false;
        }

        if (empty($this->graphclient)) {
            return false;
        }

        $grouprec = $DB->get_record('groups', ['id' => $moodlegroupid]);
        if (empty($grouprec)) {
            \local_o365\utils::debug('Could not find group with id "' . $moodlegroupid . '"', $caller);
            return false;
        }

        $courserec = $DB->get_record('course', ['id' => $grouprec->courseid]);
        if (empty($courserec)) {
            $msg = 'Could not find course with id "' . $grouprec->courseid . '" for group with id "' . $moodlegroupid . '"';
            \local_o365\utils::debug($msg, $caller);
            return false;
        }

        // Keep local_o365_coursegroupdata in sync with groups table.
        $o365grouprec = $DB->get_record('local_o365_coursegroupdata',
            ['groupid' => $moodlegroupid, 'courseid' => $grouprec->courseid]);
        if (empty($o365grouprec)) {
            $msg = 'Could not find local_o365_coursegroupdata record with with course "' . $grouprec->courseid .
                '" for group with id "' . $moodlegroupid . '"';
            \local_o365\utils::debug($msg, $caller);
            return false;
        }
        $o365grouprec->displayname = $grouprec->name;
        $o365grouprec->description = $grouprec->description;
        $o365grouprec->descriptionformat = $grouprec->descriptionformat;
        $o365grouprec->timemodified = $grouprec->timemodified;
        $updatephoto = false;
        if ($o365grouprec->picture != $grouprec->picture) {
            // Picture has changed.
            $updatephoto = true;
            $o365grouprec->picture = $grouprec->picture;
        }
        $DB->update_record('local_o365_coursegroupdata', $o365grouprec);

        $object = utils::get_usergroup_object($moodlegroupid);
        if (empty($object->objectid)) {
            \local_o365\utils::debug('Could not find o365 object for moodle group with id "' . $moodlegroupid . '"', $caller);
            return false;
        }

        $course = get_course($grouprec->courseid);
        $o365groupname = utils::get_group_display_name($course, $grouprec);
        $o365mailalias = utils::get_group_mail_alias($course, $grouprec);
        
        $groupdata = [
            'id'          => $object->objectid,
            'displayName' => $o365groupname,
            'description' => $grouprec->description,
            'mailNickname'=> $o365mailalias,
        ];

        // Update o365 group.
        try {
            $o365group = $this->graphclient->update_group($groupdata);
        } catch (\Exception $e) {
            \local_o365\utils::debug('Updating of study group for Moodle group "' . $moodlegroupid . '" failed: ' .
                $e->getMessage(), $caller);
            return false;
        }

        if ($updatephoto) {
            $this->update_usergroup_photo($grouprec, $object->objectid);
        }
        return true;
    }

    /**
     * Update study group photo.
     *
     * @param object $group Moodle group object.
     * @param string $o365groupid Microsoft 365 object id for group to update.
     * @return boolean True on success.
     */
    public function update_usergroup_photo($group, $o365groupid) {
        $caller = 'update_usergroup_photo';
        // Update o365 group photo.
        try {
             // Get file.
            $context = context_course::instance($group->courseid);
            $fs = get_file_storage();
            $fileinfo = [
                'component' => 'group',
                'filearea' => 'icon',
                'itemid' => $group->id,
                'contextid' => $context->id,
                'filepath' => '/',
                'filename' => 'f3.jpg'
            ];
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                                  $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
            if ($file) {
                $photo = $file->get_content();
            } else {
                $fileinfo['filename'] = 'f3.png';
                $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                                      $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
                if ($file) {
                    $photo = $file->get_content();
                } else {
                    // Photo will be set to the default.
                    $photo = '';
                }
            }

            $result = $this->graphclient->upload_group_photo($o365groupid, $photo);
            if (!empty($result)) {
                // If a response has returned than an error has occured.
                \local_o365\utils::debug('Update study group photo: "'.$group->id.'" '.$result, $caller);
                return false;
            }
        } catch (\Exception $e) {
            \local_o365\utils::debug('Update study group photo: "'.$group->id.'" Error:'.$e->getMessage(), $caller);
            return false;
        }
        return true;
    }

    /**
     * Create a study group from a Moodle group.
     *
     * @param int $moodlegroupid Id of Moodle course group.
     *
     * @return object|boolean False on failure, o365 object on success.
     */
    public function create_usergroup($moodlegroupid) {
        global $DB;

        $caller = 'create_usergroup';
        if (\local_o365\utils::is_configured() !== true || \local_o365\feature\coursesync\utils::is_enabled() !== true) {
            return false;
        }

        if (empty($this->graphclient)) {
            return false;
        }

        $grouprec = $DB->get_record('groups', ['id' => $moodlegroupid]);
        if (empty($grouprec)) {
            \local_o365\utils::debug('Could not find group with id "' . $moodlegroupid . '"', $caller);
            return false;
        }

        $courserec = $DB->get_record('course', ['id' => $grouprec->courseid]);
        if (empty($courserec)) {
            $msg = 'Could not find course with id "' . $grouprec->courseid . '" for group with id "' . $moodlegroupid . '"';
            \local_o365\utils::debug($msg, $caller);
            return false;
        }

        $o365groupdisplayname = \local_o365\feature\coursesync\utils::get_group_display_name($courserec, $grouprec);
        $o365groupmailalias = \local_o365\feature\coursesync\utils::get_group_mail_alias($courserec, $grouprec);

        $extra = [
            'description' => $grouprec->description
        ];

        // Create o365 group.
        try {
            $o365group = $this->graphclient->create_group($o365groupdisplayname, $o365groupmailalias, $extra);
        } catch (\Exception $e) {
            $this->mtrace('Could not create group for course group #' . $moodlegroupid.' in course #' . $courserec->id . '. ' .
                'Reason: '.$e->getMessage());
            return false;
        }

        // Create course group data.
        $data = new \stdClass();
        $now = time();
        $data->displayname = $o365groupdisplayname;
        $data->description = $grouprec->description;
        $data->descriptionformat = $grouprec->descriptionformat;
        $data->groupid = $grouprec->id;
        $data->courseid = $grouprec->courseid;
        // Pictures will be synced on a cron job after the group is provisioned on Microsoft 365.
        $data->picture = 0;
        $data->timecreated = $now;
        $data->timemodified = $now;
        $DB->insert_record('local_o365_coursegroupdata', $data);

        // Store group in database.
        $now = time();
        $rec = [
            'type' => 'group',
            'subtype' => 'usergroup',
            'moodleid' => $moodlegroupid,
            'objectid' => $o365group['id'],
            'o365name' => '',
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $DB->insert_record('local_o365_objects', $rec);
        return (object)$rec;
    }

    /**
     * When a Moodle group is created the profile photo cannot be uploaded as the group is not provisioned.
     */
    public function sync_usergroup_profile_photo() {
        global $DB;

        $sql = 'SELECT g.*, obj.objectid, cgd.id cgdid
                  FROM {groups} g,
                       {local_o365_objects} obj,
                       {local_o365_coursegroupdata} cgd
                 WHERE obj.type = ?
                       AND obj.subtype = ?
                       AND obj.moodleid = g.id
                       AND cgd.groupid = g.id
                       AND cgd.picture != g.picture';
        $params = ['group', 'usergroup'];
        $courselimit = get_config('local_o365', 'courses_per_task');
        if (!$courselimit) {
            $courselimit = 5;
        }
        $groups = $DB->get_recordset_sql($sql, $params, 0, $courselimit);
        $count = 0;
        foreach ($groups as $group) {
            // If the upload fails, it will not reattempt unless user modifies the photo.
            if ($this->update_usergroup_photo($group, $group->objectid)) {
                $count++;
            }
            $DB->set_field('local_o365_coursegroupdata', 'picture', $group->picture, array('id' => $group->cgdid));
        }
        if ($count) {
            $this->mtrace('Synced '.$count.' group profile photos.');
        }
    }

    /**
     * Get urls for Moodle group by api call.
     *
     * @param string $group group object plus usergroupobjectid
     *
     * @return string[]|null
     */
    public function get_usergroup_urls($group) {
        global $DB;

        if (empty($group->usergroupobjectid)) {
            return null;
        }
        try {
            $urls = $this->graphclient->get_group_urls($group->usergroupobjectid);
            if (!empty($urls)) {
                return $urls;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            $caller = 'groupcp.php';
            \local_o365\utils::debug('Exception while retrieving group urls: groupid ' . $group->id . ' ' .
                $e->getMessage(), $caller);
            return '-';
        }
    }    
    
    /**
     * Get urls for Moodle group by api call.
     *
     * @param string $group group object plus usergroupobjectid
     *
     * @return string[]|null
     */
    public function delete_usergroup($groupid) {
        global $DB;

        $params = [
            'type' => 'group',
            'subtype' => 'usergroup',
            'moodleid' => $groupid,
        ];
        
        if($groupobject = $DB->get_record('local_o365_objects', $params)) {
            // Delete o365 group.
            try {
                $result = $this->graphclient->delete_group($groupobject->objectid);
            } catch (\Exception $e) {
                return false;
            }

            if ($result !== true) {
                $caller = '\local_o365teams\coursegroups\delete_usergroup';
                \local_o365\utils::debug('Couldn\'t delete group', $caller, $result);
                return false;
            } else {
                // Clean up course group data.
                $DB->delete_records('local_o365_coursegroupdata', ['groupid' => $groupid]);
                $DB->delete_records('local_o365_objects', ['id' => $groupobject->id]);
                return true;
            }
        }    
        return false;
    }

    
///////////////////////////////////////////////////////////////////////////////    
//// Cron Tasks related routines    
//////////////////////////////////////////////////////////////////////////////    

    /**
     * Create Channels and populate membership for all courses that have an associated team recorded.
     */
    public function sync_channels() : bool {
    
        $coursesyncsetting = get_config('local_o365', 'coursesync');
        if ($coursesyncsetting !== 'onall' && $coursesyncsetting !== 'oncustom') {
            $this->mtrace('Teams Group creation is disabled.');
            return false;
        }
        $addchannels = get_config('local_o365teams', 'teamsprivatechannels');
        if (!$addchannels) {
            $this->mtrace('Team Channels creation from Groups is disabled.');
            return false;
        }

        $this->mtrace('Start syncing channels.');
        $this->mtrace('Tenant has education license: ' . ($this->haseducationlicense ? 'yes' : 'no'));

        // Process courses with channels that have been "soft-deleted".
        //$this->restore_soft_deleted_channels();

        // Process courses having teams but not channels.
        $this->process_courses_without_channels();

        return true;
    }
    
    
    /**
     * Process courses with Teams but not channels:
     *  - Create Channels if appropriate.
     */
    public function process_courses_without_channels() {
        global $DB;
    
        $this->mtrace('Processing courses without channels...');    
    
        // only groups with non null idnumbel will be selected 
        $params = [ 'type1' => 'group', 'subtype1' => 'teamchannel', 
                    'type2' => 'group', 'subtype2' => 'courseteam', 
                    'type3' => 'group', 'subtype3' => 'teamfromgroup', 
                    'type4' => 'sdssection', 'subtype4' => 'course', 
                    'siteid' => SITEID];
        $sql = "SELECT g.*, ct.objectid AS courseteamid, tg.objectid AS teamfromgroup
                  FROM {groups} g
             LEFT JOIN {local_o365_objects} obj ON obj.type = :type1 AND obj.subtype = :subtype1 AND obj.moodleid = g.id 
             LEFT JOIN {local_o365_objects} ct ON ct.type = :type2 AND ct.subtype = courseteam AND ct.moodleid = g.courseid
             LEFT JOIN {local_o365_objects} tg ON tg.type = :type3 AND tg.subtype = teamfromgroup AND tg.moodleid = g.courseid
             LEFT JOIN {local_o365_objects} sds ON sds.type = :type4 AND sds.subtype = ? AND sds.moodleid = g.courseid
                 WHERE g.idnumber != '' AND obj.objectid IS NULL AND sds.id IS NULL  
                   AND (ct.objectid IS NOT NULL OR tg.objectid IS NOT NULL) 
                   AND NOT (ct.objectid IS NULL AND tg.objectid IS NULL)
                   AND g.courseid != :siteid 
              ORDER BY g.id ASC, ct.objectid ASC, tg.objectid ASC";

        $groups = $DB->get_recordset_sql($sql, $params);    
        
        $channelpattern = get_config('local_o365teams', 'channelpattern');
        $added = []
        foreach ($groups as $group) {
            // to ensure both courseteamid and teamfromgroup are processed but only once
            $process = [];
            if($group->courseteamid) {
                $teamobjectid = $group->courseteamid;
                $process[$teamobjectid] = $group;
            }
            if($group->teamfromgroup) {
                $teamobjectid = $group->teamfromgroup;
                $process[$teamobjectid] = $group;
            }
            foreach($process as $teamobjectid => $group) {
                if (isset($added[$group->id][$teamobjectid])) {
                    // if set, this has been created
                    continue;
                }
                if($channelpattern && 
                    !empty(preg_replace('/'.trim($channelpattern).'/', '', $group->idnumber))) {
                    // only add if group idnumber matches pattern
                    continue;
                }
        
                $owners = utils::get_team_owner_object_ids_by_course_id($group->courseid, $group->id);
                if($channelobject = $this->create_group_channel($teamsobjectid, $owners, $group)) {
                    // ensures this groupid is not added to the same teamobject
                    $added[$group->id][$teamobjectid] = $channelobject->objectid;
                }
            }
        }
    }
    


    /**
     * Check & resync membership in Teams to courses.
     *
     * @param int $lastrun timestamp to select changed courses
     */
    public function process_resync_membership_courses(int $lastrun) {
        global $DB;
    
        $changed = '';
        // Synchonize course teams 
        //list($insql, $params) = $DB->get_in_or_equal(['course','courseteam', 'teamfromgroup']);
        // asume o365 synchs by course group, not needed teams ???
        // TODO is this true ???
        list($insql, $params) = $DB->get_in_or_equal(['course']);
        
        if($lastrun) {
            $this->mtrace("... Syncing courses updated from $lastrun");
            $changed = " AND EXISTS (SELECT 1 
                                       FROM {logstore_standard_log} l 
                                      WHERE l.courseid = c.id 
                                        AND (l.target = 'user_enrolment' OR l.target = 'role') 
                                        AND l.timecreated >= ?) ";
            $params[] = $lastrun;
        }
        
        $sql = "SELECT o.id, o.objectid, o.moodleid AS courseid
                  FROM {local_o365_objects} o 
                  JOIN {course} c ON c.id = o.moodleid
                 WHERE o.type = 'group' AND o.subtype $insql $changed
                 GROUP BY o.objectid, o.moodleid ";

        $courses = $DB->get_recordset_sql($sql, $params);
        if($courses->valid()) {
            foreach($courses as $course) {
                try {
                    $this->resync_group_owners_and_members($course->courseid, $course->objectid);    
    
                } catch (\Exception $e) {
                    // Do nothing.
                    $this->mtrace("    ... Course/Teams resync failed.  " . $e->getMessage());
                }                
            }
            $courses->close();
        } else {
            $this->mtrace("    NO courses to update from last run.");
        }    
    }
        
    /**
     * Check & resync membership in Teams to courses
     *
     * @param int $lastrun timestamp to select changed groups
     */
    public function process_resync_membership_channels(int $lastrun) {
        global $DB;
        $changed = '';
        // Synchonize course teams 
        //list($insql, $params) = $DB->get_in_or_equal(['course','courseteam', 'teamfromgroup']);
        // asume o365 synchs by course group, not needed teams ???
        // TODO is this true ???
        list($insql, $params) = $DB->get_in_or_equal(['course']);

        $this->mtrace("... Syncing courses updated from $lastrun");        
        if($lastrun) {
            $changed = " AND EXISTS (SELECT 1 
                                       FROM {logstore_standard_log} l 
                                      WHERE l.courseid = c.id 
                                        AND l.target = 'group_member'  
                                        AND l.objectid = g.id
                                        AND l.timecreated >= ?) ";
            $params[] = $lastrun;
        }
        
        $sql = "SELECT o.id, o.objectid AS channelobjectid, o.moodleid AS groupid, c.id AS courseid, t.objectid AS teamsobjectid
                  FROM {local_o365_objects} o 
                  JOIN {groups} g ON g.id = o.moodleid AND o.type = 'group' AND o.subtype = 'teamchannel'
                  JOIN {course} c ON c.id = g.courseid
                  JOIN {local_o365_objects} t ON t.moodleid = c.id AND t.type = 'group' AND (t.subtype = 'courseteam' OR t.subtype = 'teamfromgroup')
                WHERE o.type = 'group' AND o.subtype = 'teamchannel' $changed
                GROUP BY o.objectid, o.moodleid ";

        $groupchannels = $DB->get_recordset_sql($sql, $params);
        if($groupchannels->valid()) {
            foreach($groupchannels as $channel) {
                try {
                    $this->resync_channel_membership($channel->courseid, $channel->groupid, $channel->teamsobjectid, $channel->channelobjectid)
                } catch (\Exception $e) {
                    // Do nothing.
                    $this->mtrace("    ... Group/Channel resync failed.  " . $e->getMessage());
                }                
            }
            $groupchannels->close();
        } else {
            $this->mtrace("    NO groups/channels to update from last run.");
        }    
    }
    
    
    /**
     * Check & resync membership in Teams to courses.
     *
     * @param int $lastrun timestamp to select changed courses
     */
    public function process_resync_membership_usergroups(int $lastrun) {
        global $DB;
    
        $changed = '';
        $params = [];
        $this->mtrace("... Syncing usergroups in courses updated from $lastrun");                
        if($lastrun) {
            $changed = "AND EXISTS (SELECT 1 
                        FROM {logstore_standard_log} l 
                        WHERE l.courseid = c.id AND l.target = 'group_member' AND l.objectid = g.id AND l.timecreated >= ?) ";
            $params = [$lastrun];
        }
        // Synchonize usergroups
        $sql = "SELECT o.id, o.objectid, o.moodleid AS groupid, c.id AS courseid
                  FROM {local_o365_objects} o 
                  JOIN {groups} g ON g.id = o.moodleid AND o.type = 'group' AND o.subtype = 'usergroup'
                  JOIN {course} c ON c.id = g.courseid
                 WHERE o.type = 'group' AND o.subtype = 'usergroup' $changed  
                 ";
        $usergroups = $DB->get_recordset_sql($sql, $params);
        if($usergroups->valid()) {
            foreach($usergroups as $usergroup) {
                try {
                    $this->resync_group_membership($usergroup->courseid, $usergroup->objectid); 
                    
                } catch (\Exception $e) {
                    // Do nothing.
                    $this->mtrace("    ... usergroups resync failed.  " . $e->getMessage());
                }                
            }
            $usergroups->close();
        } else {
            $this->mtrace("    NO groups with usergoups updated from last run.");
        }    
    
    }    
    
    /**
     * Process courses with Teams but not channels:
     *  - Create Channels if appropriate.
     */
    public function process_courses_team_name_update() {
        global $DB;    
    
        $sitelabel = get_config('local_o365teams', 'sitelabel');
        $this->mtrace("Changing o365 course/groups names to extended names");            
    
        if(!$sitelabel) {
            mtrace("    NO sitealbel, nothing to do.");        
            return true
        }
    
        // Get course groups without sitelabel in o365 name
        list($insql, $params) = $DB->get_in_or_equal(['course','courseteam', 'teamfromgroup']);
        $notsitelabel = $DB->sql_like('o.o365name', '?', true, true, true);
        $params[] = "%$sitelabel%";
        $sql = "SELECT o.id as oid, o.objectid, o.subtype, o.o365name, o.moodleid AS id, c.fullname, c.shortname, c.idnumber
                  FROM {local_o365_objects} o 
                  JOIN {course} c ON c.id = o.moodleid
                 WHERE o.type = 'group' AND o.subtype $insql 
                   AND $notsitelabel ";
    
        $courses = $DB->get_recordset_sql($sql, $params);    
        if($courses->valid()) {
            foreach($courses as $course) {
                try {
                    if($course->subtype == 'course') {
                        $this->update_course_group_name_extended($course, $course->objectid, $course->o365name); 
                    } elseif($course->subtype == 'courseteam' || $course->subtype == 'teamfromgroup') { 
                        $this->update_course_team_name_extended($course, $course->objectid, $course->o365name); 
                    }
                    
                } catch (\Exception $e) {
                    // Do nothing.
                    $this->mtrace("    ...   name change failed.  " . $e->getMessage());
                }                
            }
            $courses->close();
        } else {
            $this->mtrace("    NO groups with usergoups updated from last run.");
        }         
    
    }
    
}
