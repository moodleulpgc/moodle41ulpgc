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
 * Manage all calls to the Microsoft Graph API.
 *
 * @package local_o365teams
 * @author Enrique castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2022 onwards Enrique Castro
 */

namespace local_o365teams\rest;

use coding_exception;
use core_date;
use core_text;
use DateTime;
use Exception;
use local_o365\oauth2\clientdata;
use local_o365\obj\o365user;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();


/**
 * Extended Client for unified Microsoft 365 API.
 */
class unified extends \local_o365\rest\unified {

    /**
     * @param string API $response array
     *
     * @return nothing
     */
    public static function notify_error($response) {
        $response = json_decode($response, true);
        $message = $response['error']['code'] . '. ' . $response['error']['message'];
        \core\notification::add($message, 
                        \core\output\notification::NOTIFY_ERROR);    
    }    
    
    /**
     * Create a private channel for agroup in a Teams.
     *
     * @param string $teamsobjectid
     * @param array $owner team owners office3365 IDs
     * @param string $channel name
     * @param string $channel description
     *
     * @return mixed
     * @author Enrique Castro <@ULPGC>
     */
    public function create_group_channel($teamsobjectid, $owners, $name, $description) {
    
        $now = time();
        $members =  []; 
        foreach($owners as $ownerid) {
            $member = [
                        '@odata.type' => "#microsoft.graph.aadUserConversationMember",
                        'user@odata.bind' => "https://graph.microsoft.com/beta/users('$ownerid')",
                        'roles' => ["owner"]
                        ];
            $members[] = $member;
        }

        // channel displayNames must be 50 characters or less, and can't contain the characters # % & * { } / \ : < > ? + | ' "
        $notalowed = explode(' ', '# % & * { } / \ : < > ? + | "') + ["'"];
        $name = shorten_text(str_replace($notalowed, '', $name), 45, true, ' ...');
        
        // "#Microsoft.Teams.Core.channel",    "#Microsoft.Graph.channel"
        $channeldata = [
            '@odata.type' => "#Microsoft.Graph.channel", 
            'membershipType' => 'private',
            'displayName' => $name, 
            'description' => $description,
            'members' => $members, 
        ];
        
        // Create channel first.
        $response = $this->betaapicall('post', "/teams/$teamsobjectid/channels", json_encode($channeldata));
        $expectedparams = ['id' => null];
        return $this->process_apicall_response($response, $expectedparams);
        
        /*
        if ($this->httpclient->info['http_code'] == 201) {
            // If response is 201 Created, return response.
            $expectedparams = ['id' => null];
            return $this->process_apicall_response($response, $expectedparams);
        } else {
            // Error.
            self::notify_error($response);
            return $response;
        }
        */
    }
    
    /**
     * Get channel info.
     *
     * @param string $teamsobjectid The object ID of the teams.
     * @param string $channelobjectid The object ID of the channel.    
     * @return array Array of returned o365 group data.
     */
    public function get_channel($teamsobjectid, $channelobjectid, $notify = true) {
        $response = $this->betaapicall('get', "/teams/$teamsobjectid/channels/$channelobjectid");
        if ($this->httpclient->info['http_code'] == 200) {
            // If response is 200 OK, return response.
            $expectedparams = ['id' => null];
            return $this->process_apicall_response($response, $expectedparams);
        } else {
            // Error.
            if($notify) {
                self::notify_error($response);
            }
            return false;
        }
    }
    
    
    /**
     * update a private channel for a group in a Teams.
     *
     * @param string $teamsobjectid
     * @param string $channelobjectid
     * @param string $channel name
     * @param string $channel description
     *
     * @return mixed
     * @author Enrique Castro <@ULPGC>
     */
    public function update_group_channel($teamsobjectid, $channelobjectid, $name, $description) {
        $channeldata = [
            'displayName' => $name, 
            'description' => $description,
        ];
  
        $response = $this->betaapicall('patch', "/teams/$teamsobjectid/channels/$channelobjectid", json_encode($channeldata));
        $expectedparams = ['id' => null];
        return  $this->process_apicall_response($response, $expectedparams);        
    }
    
    /**
     * Delete a private channel for a group in a Teams.
     *
     * @param string $teamsobjectid
     * @param string $channelobjectid
     *
     * @return mixed
     * @author Enrique Castro <@ULPGC>
     */
    public function delete_group_channel($teamsobjectid, $channelobjectid) {
  
        $response = $this->betaapicall('delete', "/teams/$teamsobjectid/channels/$channelobjectid", '');
        return ($response === '') ? true : $response;
    }
    
    
    /**
     * Get a list of channel members.
     *
     * @param string $teamsobjectid     
     * @param string $channelobjectid The object ID of the channel.
     * @return mixed false in error, array Array of returned members.
     * @author Enrique Castro <@ULPGC>
     */
    public function get_channel_members($teamsobjectid, $channelobjectid) {
        //$endpoint = '/chats/'.$channelobjectid.'/members';
        $endpoint = '/teams/'.$teamsobjectid.'/channels/'.$channelobjectid.'/members/';
        $response = $this->betaapicall('get', $endpoint);
        $expectedparams = ['value' => null];
        return $this->process_apicall_response($response, $expectedparams);
    }


    /**
     * Add member to a teams channel
     *
     * @param string $teamsobjectid
     * @param string $channelobjectid
     * @param string $memberobjectid The object ID of the item to add (can be group object id or user object id).
     * @return bool|string True if successful, returned string if not (may contain error info, etc).
     */
    public function add_member_to_channel($teamsobjectid, $channelobjectid, $memberobjectid, $owner = false) {
        $endpoint = '/teams/'.$teamsobjectid.'/channels/'.$channelobjectid.'/members/';
        $data = [
                '@odata.type' => "#microsoft.graph.aadUserConversationMember",
                'user@odata.bind' => "https://graph.microsoft.com/beta/users('$memberobjectid')",
                'roles' => []
                ];
        if($owner) {
            $data['roles'] = ["owner"];
        }
        $response = $this->betaapicall('post', $endpoint, json_encode($data));
        if ($this->httpclient->info['http_code'] == 201) {
            // If response is 201 Created, return response.
            $expectedparams = ['id' => null];
            if($this->process_apicall_response($response, $expectedparams)) {
                return true;
            }
        } else {
            // Error.
            self::notify_error($response);
            return $response;
        }
    }

    
    /**
     * Change the role of a member in a teams channel
     *
     * @param string $teamsobjectid
     * @param string $channelobjectid
     * @param string $memberobjectid The object ID of the item to add (can be group object id or user object id).
     * @return bool|string True if successful, returned string if not (may contain error info, etc).
     */
    public function update_member_role_channel($teamsobjectid, $channelobjectid, $memberobjectid, $owner = false) {
        $endpoint = '/teams/'.$teamsobjectid.'/channels/'.$channelobjectid.'/members/'.$memberobjectid;
        $data = [
                '@odata.type' => "#microsoft.graph.aadUserConversationMember",
                'roles' => []
                ];
        if($owner) {
            $data['roles'] = ["owner"];
        }
        $response = $this->betaapicall('patch', $endpoint, json_encode($data));
        if ($this->httpclient->info['http_code'] == 200) {
            // If response is 201 Created, return response.
            $expectedparams = ['id' => null];
            if($this->process_apicall_response($response, $expectedparams)) {
                return true;
            }
        } else {
            // Error.
            self::notify_error($response);
            return $response;
        }
    }
    

    /**
     * Remove member from teams channel.
     *
     * @param string $teamsobjectid
     * @param string $channelobjectid
     * @param string $memberobjectid The object ID of the item to remove (can be group object id or user object id).
     * @return bool|string True if successful, returned string if not (may contain error info, etc).
     */
    public function remove_member_from_channel($teamsobjectid, $channelobjectid, $memberobjectid) {
        $endpoint = '/teams/'.$teamsobjectid.'/channels/'.$channelobjectid.'/members/'.$memberobjectid;
        $response = $this->betaapicall('delete', $endpoint);
        return ($response === '') ? true : $response;
    }


}
