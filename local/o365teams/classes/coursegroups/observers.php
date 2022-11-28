<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_o365teams\coursegroups;

/**
 * Event observer class.
 *
 * @package     local_o365teams
 * @category    event
 * @copyright   2022 Enrique Castro @ULPGC
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observers {

    /**
     * Get a Microsoft Graph API instance.
     *
     * @param string $caller The calling function, used for logging.
     * @return \local_o365\rest\unified A Microsoft Graph API instance.
     */
    public static function get_unified_api($caller = 'get_unified_api') {
        $clientdata = \local_o365\oauth2\clientdata::instance_from_oidc();
        $httpclient = new \local_o365\httpclient();
        $tokenresource = \local_o365\rest\unified::get_tokenresource();
        $token = \local_o365\utils::get_app_or_system_token($tokenresource, $clientdata, $httpclient);
        if (!empty($token)) {
            return new \local_o365teams\rest\unified($token, $httpclient);
        } else {
            $msg = 'Couldn\'t construct Microsoft Graph API client because we didn\'t have a system API user token.';
            $caller = '\local_o365teams\observers::'.$caller;
            \local_o365\utils::debug($msg, $caller);
            return false;
        }
    }   
    
    /**
     * Handle group_created event to create o365 groups.
     *
     * @param \core\event\group_created $event The triggered event.
     * @return bool Success/Failure.
     */
    public static function handle_group_created(\core\event\group_created $event) {
        $caller = '\local_o365teams\observers::handle_group_created';        
        [$syncenabled, $createchannels, $createusergroups] = utils::enabled_mode(true); 
        if (\local_o365\utils::is_configured() !== true || $syncenabled !== true) {
            \local_o365\utils::debug('\local_o365teams\coursegroups\ not configured', $caller);
            return false;
        }
        
        $apiclient = static::get_unified_api('handle_group_created');
        if (empty($apiclient)) {
            return false;
        }

        $usergroupid = $event->objectid;
        $courseid = $event->courseid;

        // Check if course is enabled.
        if (\local_o365\feature\coursesync\utils::is_course_sync_enabled($courseid)) !== true) {
            return false;
        }

        $teamschannels = new teamschannels($apiclient, false);

        $success = true;
        if($createusergroups) {
            try {
                $object = $teamschannels->create_usergroup($usergroupid);
                if (empty($object->objectid)) {
                    \local_o365\utils::debug('Couldn\'t create group '.$usergroupid, $caller, $object);
                }
            } catch (\Exception $e) {
                \local_o365\utils::debug('Couldn\'t create group '.$usergroupid.':'.$e->getMessage(), $caller, $e);
                $success = false;
            }
        }

        if($createchannels && $teamschannels->check_group_needs_channel($usergroupid)) {
            try {
                $channelobject = $teamschannels->add_channel_for_group($usergroupid);
                if (empty($channelobject->objectid)) {
                    \local_o365\utils::debug('Couldn\'t create channel '.$usergroupid, $caller, $channelobject);
                }
            } catch (\Exception $e) {
                \local_o365\utils::debug('Couldn\'t create channel '.$usergroupid.':'.$e->getMessage(), $caller, $e);
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Handle group_updated event to update o365 groups.
     *
     * @param \core\event\group_updated $event The triggered event.
     * @return bool Success/Failure.
     */
    public static function handle_group_updated(\core\event\group_updated $event) {
        $caller = '\local_o365teams\observers::handle_group_updated';        
        [$syncenabled, $createchannels, $createusergroups] = utils::enabled_mode(true); 
        if (\local_o365\utils::is_configured() !== true || $syncenabled !== true) {
            \local_o365\utils::debug('\local_o365teams\coursegroups\ not configured', $caller);
            return false;
        }
        
        $apiclient = static::get_unified_api('handle_group_updated');
        if (empty($apiclient)) {
            return false;
        }

        $usergroupid = $event->objectid;
        $teamschannels = new teamschannels($apiclient, false);
        if($createusergroups) {
            $teamschannels->update_usergroup($usergroupid);
            \local_o365\utils::debug('Updated group '.$usergroupid, 'handle_group_updated');
        }
        
        if($createchannels && $teamschannels->check_group_needs_channel($usergroupid, true)) {
            $teamschannels->update_channel_for_group($usergroupid); // ecastro ULPGC
        }
    }

    /**
     * Handle group_deleted event to delete o365 groups.
     *
     * @param \core\event\group_deleted $event The triggered event.
     * @return bool Success/Failure.
     */
    public static function handle_group_deleted(\core\event\group_deleted $event) {
        global $DB;

        $caller = '\local_o365teams\observers::handle_group_deleted';        
        [$syncenabled, $createchannels, $createusergroups] = utils::enabled_mode(true); 
        if (\local_o365\utils::is_configured() !== true || $syncenabled !== true) {
            \local_o365\utils::debug('\local_o365teams\coursegroups\ not configured', $caller);
            return false;
        }

        $apiclient = static::get_unified_api('handle_group_deleted');
        if (empty($apiclient)) {
            return false;
        }

        $usergroupid = $event->objectid;
        $courseid = $event->courseid;

        // Check if course is enabled.
        if (\local_o365\feature\coursesync\utils::is_course_sync_enabled($courseid)) !== true) {
            return false;
        }

        $todeletegroupids[];
        $todeleteo365recids[];
        $result = '';
        if($createusergroups) {
            // Look up group.
            if($groupobjectrec = utils::get_usergroup_object($usergroupid)) {
                // Delete o365 group.
                try {
                    if($result = $apiclient->delete_group($groupobjectrec->objectid)) {
                        $todeletegroupids[$usergroupid] = $usergroupid;
                        $todeleteo365recids[$groupobjectrec->id] = $groupobjectrec->id;
                    } else {
                        \local_o365\utils::debug('Couldn\'t delete usergroup', $caller, $result);                    
                    }
                } catch (\Exception $e) {
                    \local_o365\utils::debug('Couldn\'t delete usergroup', $caller, $result);
                }
            } else {
                \local_o365\utils::debug('No o365 object for group:'.$usergroupid, $caller);
            }
        }

        if($createchannels) {
            // Look up group.
            if($groupobjectrec = utils::get_channel_object($usergroupid)) {
                $teamschannels = new teamschannels($apiclient, false);
                if($teamschannels->remove_group_channel($courseid, $usergroupid)) {
                    $todeletegroupids[$usergroupid] = $usergroupid;
                    $todeleteo365recids[$groupobjectrec->id] = $groupobjectrec->id;
                }
            } else {
                \local_o365\utils::debug('No o365 channel object for group:'.$usergroupid, $caller);
            }
        }

        if($todeletegroupids) {
            $DB->delete_records_list('local_o365_coursegroupdata', 'groupid', $todeletegroupids);
        }
        if($todeleteo365recids) {
            $DB->delete_records_list('local_o365_local_o365_objects', 'id', $todeleteo365recids);
        }
        
        return true;
    }

    /**
     * Handle group_member_added event to add a user to an o365 group.
     *
     * @param \core\event\group_member_added $event The triggered event.
     * @return bool Success/Failure.
     */
    public static function handle_group_member_added(\core\event\group_member_added $event) {
        global $DB;
        
        $caller = '\local_o365teams\observers::handle_group_member_added';        
        [$syncenabled, $createchannels, $createusergroups] = utils::enabled_mode(true); 
        if (\local_o365\utils::is_configured() !== true || $syncenabled !== true) {
            \local_o365\utils::debug('\local_o365teams\coursegroups\ not configured', $caller);
            return false;
        }

        $newmemberid = $event->relateduserid;
        $usergroupid = $event->objectid; 
        $courseid = $event->courseid;

        // Check if course is enabled.
        if (\local_o365\feature\coursesync\utils::is_course_sync_enabled($courseid)) !== true) {
            return false;
        }
        
        
        // Look up user.
        $userobjectdata = $DB->get_record('local_o365_objects', ['type' => 'user', 'moodleid' => $newmemberid]);
        if (empty($userobjectdata)) {
            $msg = 'Not adding user "'.$newmemberid.'" to group "'.$usergroupid.'" because we don\'t have Azure AD data for them.';
            $caller = '\local_o365teams\observers::handle_group_member_added';
            \local_o365\utils::debug($msg, $caller);
            return false;
        }
        $context = \context_course::instance($courseid);
        $owner = has_capability('local/o365:teamowner', $context, $newmemberid);
        $apiclient = static::get_unified_api('handle_group_member_added');
        $userobjectid = $userobjectdata->objectid;
        $success = true;
        $result = false;
        $msgs = [];
        
        if($createusergroups) {
            // Look up group.
            if($groupobjectrec = utils::get_usergroup_object($usergroupid)) {
                if($owner) {
                    $apiclient->add_owner_to_group_using_group_api($groupobjectrec->objectid, $userobjectid);
                } else {
                    $apiclient->add_member_to_group_using_group_api($groupobjectrec->objectid, $userobjectid);
                }
            } else {
                \local_o365\utils::debug('No o365 object for group:'.$usergroupid, $caller);
            }
        }

        if($createchannels) {
            // Look up group.
            if($channelobjectrec = utils::get_channel_object($usergroupid)) {
                // get teams for course and channel
                $select = 'type = ? AND (subtype = ? OR  subtype = ?) AND moodleid = ? ';
                $params = ['group', 'courseteam', 'teamfromgroup', $courseid];
                $teamobjs = $DB->get_records_select('local_o365_objects', $select, $params):
                $teamsobjectrec = null;
                foreach($teamobjs as $team) {
                    if($apiclient->get_channel($team->objectid, $channelobjectrec->objectid)) {
                        $teamsobjectrec = $team;
                        break;
                    }
                }
                if(!empty($teamsobjectrec)) {
                    try {
                        // add owners as members and owners
                        if($owner) {
                            $result = $apiclient->add_member_to_channel($teamsobjectrec->objectid, $channelobjectrec->objectid, $userobjectid, true);
                        }
                        $result = $apiclient->add_member_to_channel($teamsobjectrec->objectid, $channelobjectrec->objectid, $userobjectid);
                        
                        if ($result !== true) {
                            $msg = 'Couldn\'t add user to channel.';
                            $caller = '\local_o365teams\observers::handle_group_member_added';
                            \local_o365\utils::debug($msg, $caller, $result);
                            $success = false;
                            $msgs[] = $msg; // ecastro ULPGC
                        }                        
                    } catch (\Exception $e) {
                        \local_o365\utils::debug('Exception: '.$e->getMessage(), $caller, $e);
                    }        
                } else {
                    \local_o365\utils::debug('No o365 Teams object with this channel for course:'.$courseid, $caller);                
                }
            } else {
                \local_o365\utils::debug('No o365 channel object for group:'.$usergroupid, $caller);
            }
        }
        
        if($success) {
            \local_o365\utils::debug('Added successfully', $caller, $result);
        } else {
            \local_o365\utils::debug(implode(', ', $msgs), $caller, $result);
        }
        return $success;
    }

    /**
     * Handle group_member_removed event to remove a user from an o365 group.
     *
     * @param \core\event\group_member_removed $event The triggered event.
     * @return bool Success/Failure.
     */
    public static function handle_group_member_removed(\core\event\group_member_removed $event) {
        global $DB;

        $caller = '\local_o365teams\observers::handle_group_member_removed';        
        [$syncenabled, $createchannels, $createusergroups] = utils::enabled_mode(true); 
        if (\local_o365\utils::is_configured() !== true || $syncenabled !== true) {
            \local_o365\utils::debug('\local_o365teams\coursegroups\ not configured', $caller);
            return false;
        }

        $newmemberid = $event->relateduserid;
        $usergroupid = $event->objectid;
        $courseid = $event->courseid;

        // Check if course is enabled.
        if (\local_o365\feature\coursesync\utils::is_course_sync_enabled($courseid)) !== true) {
            return false;
        }

       // Look up user.
        $userobjectdata = $DB->get_record('local_o365_objects', ['type' => 'user', 'moodleid' => $newmemberid]);
        if (empty($userobjectdata)) {
            $msg = 'Not removing azure user "'.$newmemberid.'" from group "'.$usergroupid.'" because we don\'t have Azure AD data for them.';
            $caller = '\local_o365teams\observers::handle_group_member_removed';
            \local_o365\utils::debug($msg, $caller);
            return false;
        }
        $context = \context_course::instance($courseid);
        $owner = has_capability('local/o365:teamowner', $context, $newmemberid);
        $apiclient = static::get_unified_api('handle_group_member_removed');
        $userobjectid = $userobjectdata->objectid;
        $success = true;
        $result = false;
        $msgs = [];
        
        if($createusergroups) {
            // Look up group.
            if($groupobjectrec = utils::get_usergroup_object($usergroupid)) {
                if($owner) {
                    $result = $apiclient->remove_owner_from_group_using_group_api($groupobjectrec->objectid, $userobjectid);
                } else {
                    $result = $apiclient->remove_member_from_group_using_group_api($groupobjectrec->objectid, $userobjectid);
                }
                if ($result != true) {
                    $msg = 'Couldn\'t remove user from group.';
                    $caller = '\local_o365teams\observers::handle_group_member_removed';
                    \local_o365\utils::debug($msg, $caller, $result);
                    $success = false;
                }
            } else {
                \local_o365\utils::debug('No o365 object for group:'.$usergroupid, $caller);
            }
        }
        
        if($createchannels) {
            // Look up group.
            if($channelobjectrec = utils::get_channel_object($usergroupid)) {
                // get teams for course and channel
                $select = 'type = ? AND (subtype = ? OR  subtype = ?) AND moodleid = ? ';
                $params = ['group', 'courseteam', 'teamfromgroup', $courseid];
                $teamobjs = $DB->get_records_select('local_o365_objects', $select, $params):
                $teamsobjectrec = null;
                foreach($teamobjs as $team) {
                    if($apiclient->get_channel($team->objectid, $channelobjectrec->objectid)) {
                        $teamsobjectrec = $team;
                        break;
                    }
                }
                if(!empty($teamsobjectrec)) {
                    try {
                        $result = $apiclient->remove_member_from_channel($teamsobjectrec->objectid, $channelobjectrec->objectid, $userobjectid);
                    } catch (\Exception $e) {
                        \local_o365\utils::debug('Exception: '.$e->getMessage(), $caller, $e);
                    }
                    if ($result !== true) {
                        $msg = 'Couldn\'t remove user from channel.';
                        $caller = '\local_o365teams\observers::handle_group_member_removed';
                        \local_o365\utils::debug($msg, $caller, $result);
                        $success = false;
                    }
       
                } else {
                    \local_o365\utils::debug('No o365 Teams object with this channel for course:'.$courseid, $caller);                
                }
            } else {
                \local_o365\utils::debug('No o365 channel object for group:'.$usergroupid, $caller);
            }
        }        
        
        if($success) {
            \local_o365\utils::debug('Removed successfully', $caller, $result);
        } else {
            \local_o365\utils::debug(implode(', ', $msgs), $caller, $result);
        }
        
        return $success;        
    }
}
