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
 * @package local_o365teams
 * @author Enrique Castro <ULPGC>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020 Enrique Castro
 */

namespace local_o365teams\task;


use local_o365\utils;
use local_o365\rest\unified;
use local_o365\feature\usersync\main;
use local_o365\task\processmatchqueue;
use core_text;
use stdClass;

/**
 * Scheduled task to sync users with Azure AD.
 */
class usersmatch extends processmatchqueue {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() : string {
        return get_string('task_matchusers', 'local_o365teams');
    }

    protected function mtrace($msg) {
        mtrace('...... '.$msg);
    }

    /**
     * Do the job.
     */
    public function execute() : bool {
        global $DB;
        
        if (utils::is_connected() !== true) {
            $this->mtrace('Microsoft 365 not configured');

            return false;
        }

        if (main::is_enabled() !== true) {
            $this->mtrace('Azure AD cron sync disabled. Nothing to do.');
            
            return true;
        }
        
        $usersmatching = get_config('local_o365teams', 'usersmatching'); 
        if(!$usersmatching) {
            $this->mtrace('Users matching disabled in config.');        
        
            return true;
        }
        
        $usersmaildomains = get_config('local_o365teams', 'usersmaildomains'); 
        $aadsync = main::get_sync_options();
        $matchbyemail = isset($aadsync['emailsync']);
        $usermatchname = $matchbyemail ? 'email' : 'username';
        $this->mtrace('Starting matching with auth '.$usersmatching.'  by '. $usermatchname);
        
        // Do not time out when syncing users.
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);
        
        $params = array('type' => 'user');
        $userswhere = '';
        if($usersmatching != 'any') {
            $userswhere = ' AND u.auth = :auth ';
            $params['auth'] = $usersmatching;
        }
        
        $mailwhere = 1;
        if($usersmaildomains) { 
            if($domains = array_map('trim', explode(',',$usersmaildomains))) {
                foreach($domains as $key => $domain) {
                    $domains[$key] =  $DB->sql_like('u.email', ":email$key");
                    $params["email$key"] = '%'.$domain;
                }
                array_filter($domains);
            }
            if($domains) {
                $mailwhere = '( ' . implode(' OR ', $domains) .  ' )' ; 
            }
        } elseif($matchbyemail) {
            $mailwhere = $DB->sql_isnotempty('user', 'u.email', true, false);
        }
        
        $sql = "SELECT u.id, u.username, u.idnumber, u.email,  ocon.id AS connid, ocon.aadupn, oobj.id AS oid, oobj.objectid, oobj.o365name 
                FROM {user} u 
                LEFT JOIN {local_o365_connections}  ocon ON ocon.muserid = u.id 
                LEFT JOIN {local_o365_objects}  oobj ON oobj.moodleid = u.id AND oobj.type = :type
                
                WHERE (u.deleted = 0 AND u.suspended = 0 AND $mailwhere $userswhere )  
                        AND (ocon.id IS NULL OR oobj.id IS NULL) 
                        AND NOT u.username LIKE 'tool_generator%' 
                        AND u.username != '' AND u.username != 'admin' AND u.username != 'guest' "; 
        $users = $DB->get_recordset_sql($sql, $params);
        if($users->valid()) {
            $apiclient = $this->get_api();
            $matchrec = new stdClass();
            $o365object = new stdClass();
            $o365object->type = 'user';
            $o365object->subtype = '';
            $o365object->tenant = '';

            $now = time();
            foreach($users as $user) {
                if(strpos($user->username, 'demostudent') !== false) {
                    continue;
                }
                // Check o365 username.
                $user->email = \core_text::strtolower($user->email);
                $usermatchname = $matchbyemail ? $user->email : $user->username;
                try {
                    $o365user = $apiclient->get_user_by_upn($usermatchname);
                } catch (\Exception $e) {
                    $o365user = false;
                }            
            
                if(!empty($o365user)) {
                    // manage connections table
                    $matchrec->muserid = $user->id;
                    $matchrec->aadupn = $usermatchname;
                    if($user->aadupn && $user->aadupn != $usermatchname) {
                        // user exists in o365, with other email, update aadupn e-mail
                        $matchrec->id = $user->connid;
                        if($DB->update_record('local_o365_connections', $matchrec)) {
                            $this->mtrace('    updated matching user '. $user->id);
                        }
                        unset($matchrec->id);                        
                    } elseif(empty($user->aadupn)) {
                        // not matched user, insert
                        $matchrec->uselogin = 0;                        
                        unset($matchrec->id);
                        if($DB->insert_record('local_o365_connections', $matchrec)) {
                            $this->mtrace('    matched user '. $user->id);
                        }
                    }
                    
                    // manage local_o365_objects table
                    if (unified::is_configured()) {
                        $o365object->objectid = $o365user['id'];
                    } else {
                        $o365object->objectid = $o365user['objectId'];
                    }         
                    $o365object->moodleid = $user->id;
                    $o365object->o365name = \core_text::strtolower($o365user['userPrincipalName']);
                    $o365object->timemodified = $now;
                    if($user->oid && (($user->o365name != $usermatchname) || empty($user->objectid))) {
                        // update entry 
                        $o365object->id = $user->oid;
                        if($DB->update_record('local_o365_objects', $o365object)) {
                            $this->mtrace('    updated o365 object for user '. $user->id);
                        }
                        unset($o365object->id);                        
                    } elseif(empty($user->oid)) {
                        // insert new entry
                        unset($o365object->id);
                        $o365object->timecreated = $now;
                        $o365object->tenant = isset($o365user['tenant']) ? $o365user['tenant'] : '';   
                        if($DB->insert_record('local_o365_objects', $o365object)) {
                            $this->mtrace('    added o365 object record for user '. $user->id);
                        }
                    }

                } else {
                    $this->mtrace('    ... Skipping user ' . $user->id. ': NOT existing in o365 by email.');
                    if($user->oid) {
                        $DB->delete_records('local_o365_objects', ['id' => $user->oid]);
                    }
                    if($user->connid) {
                        $DB->delete_records('local_o365_connections', ['id' => $user->connid]);
                    }
                }
            }
            $users->close();
        } else {
            $this->mtrace('    ... no users to match.');
        }

        $this->mtrace('Matching process finished.');
        return true;
    }
}
