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
 * Class definition for mod_examboard exams_table viewer
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examboard\output;

use renderable;                                                                                                                     
 
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

/**
 * The Examinees_table class holds data to display, manipulate and grade the users that ara been examined
 * keeps track of examinees, their tutors and grades
 *
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examinees_table extends \flexible_table implements renderable {
    /** @var int the cmid of this instance. */
    public $cmid;
    
    /** @var int the id of the examboardmodule this data belongs to. */
    public $examboardid;

    /** @var Examination class, the exam object that this instance manage. */
    public $examination;
    
    /** @var int the id of the group used in the page. */
    public $groupid = 0;
    
    /** @var object the url to perform modifications on data. */
    public $editurl = false;

    /** @var bool the capabilities in this viewer. */
    public $canviewall = false;
    
    /** @var bool the capabilities in this viewer. */
    public $canmanage = false;

    /** @var bool the capabilities in this viewer. */
    public $canedit = false;

    /** @var bool the capabilities in this viewer. */
    public $cangrade = false;
    
    /** @var bool the capabilities in this viewer. */
    public $cansubmit = false;

    /** @var bool the capabilities in this viewer. */
    public $istutor = false;
    
    /** @var bool the capabilities in this viewer. */
    public $isexaminee = false;
    
    /** @var int the timestamp to start submissions. */
    public $allowsubmissionsfromdate = 0;
    
    /** @var bool if this instance use any advanced grading method. */
    public $advancedgrading = false;
    
    /** @var bool if this examboard uses tutors or requires them. */
    public $usetutors = false;

    /** @var int grading estrategy from examboard instance . */
    public $grademode = false;

    /** @var int the maximum grade to be used. 0 is no grade, negative use scale. */
    public $grademax = false;

    /** @var int minumun number of separate grades to calculate final grade. */
    public $mingraders = 0;

    /** @var object the gradebook grade item for this instance of examboard PLUS scale . */
    public $gradeitem = false;
    
    /** @var bool if the activity links to other modules containing user deliverables. */
    public $hasexternalactivity = false;
    
    /** @var modinfo the course module containing gradeable && submission data . */
    public $gradeable = false;
    
    /** @var modinfo the course module containing proposal complementary data . */
    public $proposal = false;

    /** @var modinfo the course module containing defense complementary data . */
    public $defense = false;

    /** @var string the word used . */
    public $chair = '';
    
    /** @var string the word used . */
    public $secretary = '';
    
    /** @var string the word used . */
    public $vocal = '';
    
    /** @var string the word used . */
    public $examinee = '';

    /** @var string the word used . */
    public $tutor = '';
    
    
    
    /**
     * Constructor
     * @param moodle_url $url
     * @param object $examboard the examboard record from database
     */
    public function __construct(\moodle_url $url, $examination, $examboard) {
        global $USER;
        
        parent::__construct('examboard_examinees_table_viewer');
        $this->baseurl = clone $url;
        $this->cmid = $url->get_param('id');
        $this->groupid = $url->get_param('group');
        $this->examination = $examination;
        
        $this->examboardid  = $examboard->id;
        $this->usetutors    = $examboard->usetutors;
        $this->grademode    = $examboard->grademode;
        $this->grademax     = $examboard->grade;
        $this->mingraders   = $examboard->mingraders;
        $this->allowsubmissionsfromdate   = $examboard->allowsubmissionsfromdate;
        $this->gradeitem    = examboard_get_grade_item($examboard->id, $examboard->course);
        
        $this->gradeitem->scale = examboard_get_scale($examboard->grade);
        
        $this->hasexternalactivity = ($examboard->gradeable || $examboard->proposal || $examboard->defense);
        
        $this->chair        = $examboard->chair;
        $this->secretary    = $examboard->secretary;
        $this->vocal        = $examboard->vocal;
        $this->examinee     = $examboard->examinee;
        $this->tutor        = $examboard->tutor;
        
        if(isset($examination->id) && $examination->id) {
            $this->cangrade = $examination->is_grader($USER->id);
            $this->canedit = $examination->is_active_member($USER->id);
            $this->is_tutor = $examination->is_tutor($USER->id);        
        }
        
        $mods = get_fast_modinfo($examboard->course)->get_cms();
        
        foreach(array('gradeable', 'proposal', 'defense') as $type) {
            if($examboard->{$type}) {
                foreach($mods as $cmid => $cm) {
                    if($cm->idnumber == $examboard->{$type}) {
                        $this->{$type} = $cm;
                        break;
                    }
                }
            }
        }
        
        $this->check_advanced_grading();
    }
    
    
    public static function get_from_url($url) {
        global $DB;
        
        $cmid = $url->get_param('id');
        list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'examboard');
        
        $examboard = $DB->get_record('examboard', array('id'=>$cm->instance, 'course' =>$cm->course));
        $examboard->cmid = $cmid;
        $examboard->cmidnumber = $cm->idnumber;
        
        return new examinees_table($url, null, $examboard); 
    }
    
    public function can_access() { 
        return ($this->canviewall || 
                $this->canmanage || 
                $this->canedit || 
                $this->cangrade || 
                $this->isexaminee || 
                $this->istutor);
    }

    public function submissionsopen($cansubmit = true) { 
        if(!(isset($this->examination->id) && $this->examination->id)) {
            return false;
        }

        $now = time(); 
        return ($this->allowsubmissionsfromdate && 
                ($now > $this->allowsubmissionsfromdate) &&
                ($now < $this->examination->examdate));
    }
    
    public function access_search_term() {
        global $USER;
        
        if($this->canviewall || $this->canmanage || $this->cangrade) {
            return '';
        }
        
        if($this->istutor) {
            return ['tutorid' => $USER->id];
        }
        
        if($this->isexaminee) {
            return ['userid' => $USER->id];
        }
                
        return '';
    }
    
    
    private function check_advanced_grading() {
        global $CFG;
        
        require_once($CFG->dirroot . '/grade/grading/lib.php');
    
        $context = \context_module::instance($this->cmid);
        $gradingmanager = get_grading_manager($context, 'mod_examboard', 'usergrades');
        $hasgrade = ($this->grademax != GRADE_TYPE_NONE );
        if ($hasgrade) {
            if ($controller = $gradingmanager->get_active_controller()) {
                $this->advancedgrading = true;
            }
        }
    }
}
