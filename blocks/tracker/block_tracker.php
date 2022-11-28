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

/** Block Tracker
 * A Moodle block to display tracker issus warnings
 * @package blocks
 * @author: Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/tracker/locallib.php');

class block_tracker extends block_list {

    /**
     * Sets the block name and version number
     *
     * @return void
     **/
    function init() {
        $this->title = get_string('blocktitle', 'block_tracker');
        $this->version = 2012042200;
    }
    function preferred_width() {
        return 210;
    }

    function applicable_formats() {
        return array('my' => true,  'site-index'=>true,  'course'=>true, 'tag' => false, 'mod' => false);
    }

    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return true;
    }

    function instance_allow_config() {
        return false;
    }

    function get_content() {
        global $CFG, $DB, $USER;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $config = get_config('block_tracker');
        $openstatus = get_config('tracker', 'openstatus');


        $localissues = [];        
        $remoteissues = [];        
        $remoteurl = '';
        // only get issues if user id logged in
        if(isloggedin()) {
            // Find local issues 
            $id = $config->tracker; 
            if ($tracker = $DB->get_record('tracker', array('id'=>$id))) {
                $levels = explode(',',$openstatus);
                list($insql, $params) = $DB->get_in_or_equal($levels, SQL_PARAMS_NAMED, 'st_');

                $select = " reportedby = :userid AND trackerid = :trackerid AND status $insql AND  usermodified < resolvermodified AND userlastseen < resolvermodified";
                $params['userid'] = $USER->id;
                $params['trackerid'] = $tracker->id;
                $fields = 'id, summary, status, resolution, userlastseen';

                $localissues = $DB->get_records_select('tracker_issue', $select, $params, 'usermodified DESC', $fields);
            }
            
            // Find remote issues 
            if($config->enabledremote && $config->remoteserver && $config->wstoken && $config->remoteinstance) {
                // Function call is hard-coded.
                $remoteurl = $config->remoteserver;
                if(substr($remoteurl, -1) != '/') {
                    $remoteurl .=  '/';
                }        
                $wsurl = $remoteurl.'webservice/rest/server.php?wstoken='.
                                                trim($config->wstoken) . 
                                                '&wsfunction=mod_tracker_get_recent_issues_by_username';
                $format = 'json';
                // Params: we use the username for consistency.
                $params = array('username' => $USER->username, 
                                            'trackerid' => $config->remoteinstance);
/*
                $params = array('username' => '42810976', 
                                            'trackerid' => $config->remoteinstance);
*/
                // Retrieve data.
                $curl = new curl;
                $remoteissues = json_decode($curl->post($wsurl. '&moodlewsrestformat='.$format.'&'.http_build_query($params, '', '&')));
                // remove error messages
                if(is_object($remoteissues) && isset($remoteissues->errorcode)) {
                    $remoteissues = [];
                }
            }
        }
        
        if($localissues || $remoteissues) {
            $statuskeys = array(POSTED => 'posted',
                        OPEN => 'open',
                        RESOLVING => 'resolving',
                        WAITING =>  'waiting',
                        TESTING => 'testing',
                        RESOLVED => 'resolved',
                        ABANDONNED => 'abandonned',
                        TRANSFERED => 'transfered',
                        PUBLISHED => 'published',
                        VALIDATED => 'validated',
                        );
            // cannot sum both because different data & keys (issueids) may collide on different servers
            $pixurl = $CFG->wwwroot.'/blocks/tracker/pix/';
            
            if($localissues) {
                $this->content->items[] = get_string('firstlinelocal', 'block_tracker');
                $this->content->icons[] = '';
                
                foreach ($localissues as $issue) {
                    $this->content->items[] = '<a href="'.$CFG->wwwroot.'/mod/tracker/view.php?t='.$tracker->id.'&amp;issueid='.$issue->id.'">'.
                                                                    ($tracker->ticketprefix.$issue->id.': '. shorten_text($issue->summary, 28)).'</a>';
                    $this->content->icons[] = '<img src="'.$pixurl.$statuskeys[$issue->status].'.gif" class="icon" alt="" />';
                }
            }
            
            if($remoteissues) {
                $this->content->items[] = get_string('firstlineremote', 'block_tracker');
                $this->content->icons[] = '';
                
                foreach ($remoteissues as $issue) {
                    $this->content->items[] = '<a href="'.$remoteurl.'mod/tracker/view.php?t='.$config->remoteinstance.'&amp;issueid='.$issue->id.'">'.
                                                                    ($issue->ticketprefix.$issue->id.': '.shorten_text($issue->summary, 28)).'</a>';
                    $this->content->icons[] = '<img src="'.$pixurl.$statuskeys[$issue->status].'.gif" class="icon" alt="" />';
                }
            }
        }
        
        return $this->content;
    }

}
