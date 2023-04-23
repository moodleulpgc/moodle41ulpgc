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
 * Prints a list of courses from another Moodle instance.
 *
 * @package   block_remote_courses
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block definition.
 *
 * @package   block_remote_courses
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_remote_courses extends block_base {

    /**
     * Sets the block title.
     */
    public function init() {
        $this->title = get_string('remote_courses', 'block_remote_courses');
    }

    /**
     * Returns supported formats.
     * @return array
     */
    public function applicable_formats() {
        return array(
            'site-index' => true, 'my' => true //ecastro ULPGC
        );
    }

    /**
     * Returns the block content.
     * @return string
     */
    public function get_content() {
        global $USER, $OUTPUT; //ecastro ULPGC
        
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass();
        $this->content->text   = '';
        $this->content->footer = '';

        // only show courses if user id logged in
        if(isloggedin()) {
            // Quit if remote URL and token aren't set.
            if (empty($this->config->wstoken) || empty($this->config->remotesite)) {
                $this->content->text = get_string('unconfigured', 'block_remote_courses');
                return $this->content;
            }
            
            $this->showncourses = [];
            
            // Fist recall common courses list
            if($this->config->courselist) {
            /*
                // using ULPGC call
                // Function call is hard-coded.
                $url = $this->config->remotesite
                    . '/webservice/rest/server.php?wstoken='
                    . $this->config->wstoken . '&wsfunction=local_ulpgccore_get_remote_courses_by_field';
                $format = 'json';
                // Params: we use the username to retrieve recent activity
                $params = array('searchlist' => $this->config->courselist, 
                                           'field' => $this->config->coursefield);
                if($this->config->recentactivity) {
                    $params['username'] = $USER->username;
                    //$params['username'] = '42810976';
                }
                // Retrieve data.
                $curl = new curl;
                $resp = json_decode($curl->post($url. '&moodlewsrestformat='.$format.'&'.http_build_query($params, '', '&')));
               */ 
                $resp = $this->get_remote_courses_list();
                
                if (!is_null($resp) && is_array($resp) && count($resp) > 0) {
                    if(!empty($this->config->listheader)) {
                        $this->content->text .= $OUTPUT->heading($this->config->listheader, 5, 'listheader');
                    }
                    $this->print_courses($resp, 'list-courses');
                }
            }
            
            if($this->config->usercourses) {
                // using ULPGC call
                // Function call is hard-coded.
                $url = $this->config->remotesite
                    . '/webservice/rest/server.php?wstoken='
                    . $this->config->wstoken . '&wsfunction=local_ulpgccore_get_remote_courses_by_username';
                $format = 'json';
                $params = array('username' => $USER->username, 
                                           'catidnumber' => $this->config->catidnumber);
                //$params['username'] = '42810976';
                // Retrieve data.
                $curl = new curl;
                $resp = json_decode($curl->post($url. '&moodlewsrestformat='.$format.'&'.http_build_query($params, '', '&')));
                
                if (!is_null($resp) && is_array($resp) && count($resp) > 0) {
                    if(!empty($this->config->coursesheader)) {
                        $this->content->text .= $OUTPUT->heading($this->config->coursesheader, 5, 'listheader');
                    }
                    $this->print_courses($resp, 'user-courses', $this->config->numcourses);
                }
            }

        }
        
        // Default content. Shown even if usser is NOT logged in
        if (!empty($this->config->introtext)) {
            $this->content->text .= $this->config->introtext['text'];
        }
        
        return $this->content;
        
    }

    /**
     * Multiple instances are not supported.
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Returns the block title.
     * @return string
     */
    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->title = get_string('remote_courses', 'block_remote_courses');
        }
    }
    
    
    public function  get_remote_courses_list(): array  {
        global $USER;
        
        $courses = [];
        
        if($this->config->courselist) {
        
            // using ULPGC call
            // Function call is hard-coded.
            $url = $this->config->remotesite
                . '/webservice/rest/server.php?wstoken='
                . $this->config->wstoken . '&wsfunction=local_ulpgccore_get_remote_courses_by_field';
            $format = 'json';
            // Params: we use the username to retrieve recent activity
            $params = array('searchlist' => $this->config->courselist, 
                                        'field' => $this->config->coursefield);
            if($this->config->recentactivity) {
                $params['username'] = $USER->username;
                //$params['username'] = '42810976';
            }
            // Retrieve data.
            $curl = new curl;
            $courses = json_decode($curl->post($url. '&moodlewsrestformat='.$format.'&'.http_build_query($params, '', '&')));
                    
        }
        return $courses;
    }
    
    
    private function print_courses($courses, $listclass, $limit = 0) {
        global $OUTPUT; 
        $this->content->text .= '<ul class="'. $listclass  .'">';
        $coursesprinted = 0;
        
        $courseurl = new moodle_url($this->config->remotesite.'/course/view.php', array());
        foreach ($courses as $course) {
            //do not duplicate courses in lists
            if(isset($this->showncourses[$course->id])) {
                continue;
            } 
            // ecastro ULPGC
            $attributes = array('title' => format_string($course->fullname), 'class' => 'my-course-name'  );
            if (empty($course->visible)) {
                $attributes['class'] .= ' dimmed ';
            }            
        
            $coursename = $this->config->showshortname ? $course->shortname.' - '.$course->fullname : $course->fullname;
            if($course->recentactivity) {
                $coursename .= ' '.html_writer::tag('i', '', array('class'=>'fa fa-dot-circle-o'));
            }
            
            $courseurl->param('id', $course->id);
            $output = $OUTPUT->container(html_writer::link($courseurl, $coursename, $attributes));
        
            $this->content->text .= html_writer::tag('li', $output, array('class' => 'remote_courses'));
            $coursesprinted++;
            $this->showncourses[$course->id] = $course->id;
            if ($limit && $coursesprinted == $limit) {
                break;
            }
        }
        $this->content->text .= '</ul>';
    }
    
    
}
