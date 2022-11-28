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
 * Course termlist block
 *
 * A simpler course overview replacement with courses ordered by term
 *
 * @package    block
 * @subpackage course_termlist
 * @copyright  2012 onwards Enrique Castro at ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/lib/weblib.php');
//require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/local/ulpgccore/lib.php');
require_once($CFG->dirroot.'/local/supervision/locallib.php');

class block_course_termlist extends block_base {

    /** @var int the user selected course term */
    private $selectedterm;
    /** @var int the user selected course parent category */
    private $selectedcategory;
    /** @var int the user selected course top category */
    private $selectedtoplevelcategory;

    /** @var array filter items */
    private $filterterms = array();
    /** @var array filter items */
    private $filtercategories = array();
    /** @var array filter items */
    private $filtertoplevelcategories = array();

    /** @var int count of manually hidden courses */
    private $hiddencoursescounter = 0;
    /** @var int count of filtered out, hidden courses */
    private $filteredcoursescounter = 0;
    

    /**
     * block initializations
     */
    public function init() {
        $this->title   = get_string('pluginname', 'block_course_termlist');
    }

    /**
     * specialization function
     * @return void
     */
    public function specialization() {
        $this->title = format_string(get_config('block_course_termlist', 'blocktitle'));
    }
    
    /*
    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('moodle/site:config', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
    }
*/

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my'=>true, 'my-index'=>true, 'site-index'=>true,);
    }

    /**
     * Default return is false - header will be shown
     * @return boolean
     */
    function hide_header() {
        return false;
    }

    /**
     * Can be overridden by the block to prevent the block from being dockable.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        global $CFG;
        return false;
    }

    /**
     * If overridden and set to true by the block it will not be hidable when
     * editing is turned on.
     *
     * @return bool
     */
    public function instance_can_be_hidden() {
        return false;
    }

    /**
     * instance_allow_multiple function
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }
    
    
    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $USER, $DB, $CFG, $OUTPUT, $PAGE;
        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        $content = array();

        $config = get_config('block_course_termlist');
        $config->timelessterms = explode(',', str_replace(' ', '', $config->timelessterms));
        $this->config = $config;
        
        $supervisor = get_config('local_supervision');

        // Include local library.
        require_once(__DIR__ . '/locallib.php');
        
        // Process GET parameters for filters
        $this->set_selected_user_filters_prefs();
        
        
        $control_categories = array();
        if($this->config->showcategorieslink AND $supervisor) {
            $control_categories = supervision_get_reviewed_items($USER->id);
        }

        $control_departments = array();
        if($this->config->showdepartmentslink AND $supervisor) {
            $control_departments = supervision_get_reviewed_itemnames($USER->id, 'department');
        }

        $courses = enrol_get_users_courses($USER->id, $this->config->onlyactiveenrol, 'id, visible');
        $courses = local_ulpgccore_load_courses_details(array_keys($courses), 
                                                            'c.id, c.shortname, c.idnumber, c.fullname, c.category, c.visible, uc.term, uc.credits, uc.ctype, uc.department',
                                                            'c.category ASC, uc.term ASC, c.visible DESC, c.fullname ASC');            
        $site = get_site(); //just in case we need the old global $course hack

        if (array_key_exists($site->id,$courses)) {
            unset($courses[$site->id]);
        }

        $categories = array();
        $categorylist = $DB->get_records('course_categories', null, 'sortorder ASC', 'id, name, idnumber, path');
        $catorder = array_keys($categorylist);

        $excluded = array();
        if($this->config->excluded) {
            $excluded = explode(',', $this->config->excluded);
            foreach($excluded as $key=>$name) {
                $excluded[$key] = trim($name);
            }
        }

        $allowedcats = array();
        if($this->config->useallowedcats) {
            if($cats = trim(get_config('local_ulpgccore', 'allowedcoursecats'))) {
                $cats = str_replace(array("\r\n", "\r", ',', ';'), "\n", $cats);
                $allowedcats = explode("\n", $cats);
                foreach($allowedcats as $key=>$name) {
                    $allowedcats[$key] = trim($name);
                }
            }
        }
        
        foreach ($courses as $course) {
            if(in_array($course->shortname, $excluded)) {
                continue;
            }
            if($this->config->useallowedcats && !empty($allowedcats)) {
                if(!in_array($categorylist[$course->category]->idnumber, $allowedcats)) {
                    continue;
                }
            }
            
            // Adds info to course object, calculated hidden & filtered courses 
            $course = $this->check_course_filters($course, $categorylist);
            
            $order = array_search($course->category, $catorder);
            if(!isset($categories[$order])) {
                $cat = new stdClass();
                $cat->name = $categorylist[$course->category]->name;
                $cat->id = $course->category;
                $cat->term = array();
                $categories[$order] = $cat;
                unset($cat);
            }
            
            if(!isset($categories[$order]->term[$course->term])) {
                $term = new stdClass();
                $num = sprintf('%02d', $course->term);
                $term->name = get_string('term'.$num, 'block_course_termlist');
                $term->courses = array();
                $categories[$order]->term[$course->term] = $term;
                unset($term);
            }
            // wen need to ensure course object properties are set in the outer context. foreach makes a copy
            $courses[$course->id] = $course;
            $categories[$order]->term[$course->term]->courses[$course->id] = $course->shown;
        }
        ksort($categories);

        $content = array();
        // Create string to remember courses for JS processing.
        $js_courseslist = ' ';

        if (empty($categories)) {
            $content[] = $OUTPUT->heading(get_string('nocourses','block_course_termlist'), 4);
        } else {
        
            foreach($categories as $cat) {
                $content[] = $OUTPUT->container_start('coursebox categorybox');
                if(count($categories)>1) {
                    $cattitle = format_string($cat->name);
                    if(in_array($cat->id, $control_categories)) {  
                        $cattitle = html_writer::link($CFG->wwwroot.'/course/index.php?categoryid='.$cat->id, $cattitle);
                    }
                    $content[] = $OUTPUT->heading($cattitle, 2, 'row my-category');
                }
                foreach($cat->term as $tid => $term) {
                    $content[] = $OUTPUT->container_start('my-term');
                    if($tid && array_filter($term->courses)) {
                        $content[] = $OUTPUT->heading($this->config->{"term{$tid}name"}, 4, 'my-term termdiv ctl-term-'.$tid);
                    }
                    foreach($term->courses as $cid => $hidden){
                        // Remember course ID for JS processing.
                        $js_courseslist .= $cid.' ';
                        $content[] = $this->print_course_row($courses[$cid]);
                    }
                    $content[] = $OUTPUT->container_end(); // term my-trem
                }
                $content[] = $OUTPUT->container_end(); // categories, coursebox
            }
        }

        /// now supervisor links

        if($control_categories or $control_departments) {
            $content[] = $OUTPUT->container_start('coursebox');
            foreach($control_categories as $catid) {
                $content[] = html_writer::link($CFG->wwwroot.'/course/index.php?categoryid='.$catid, format_string($categorylist[$catid]->name), array('class'=>'my-supervisor-category'));
            }
            foreach($control_departments as $id=>$dept) {
                $content[] = html_writer::link($CFG->wwwroot.'/local/supervision/department.php?id='.$id, format_string($dept), array('class'=>'my-supervisor-department'));
            }
            $content[] = $OUTPUT->container_end();
        }

        $this->content->text = $this->print_filter_form();
        
        /***           GENERATE OUTPUT FOR HIDDEN COURSES MANAGEMENT TOP BOX       ***/
        // Do only if course hiding is enabled.
        if ($this->config->enablehidecourses) {
            // If hidden courses managing is active, output hidden courses management top box as visible.
            $this->content->text .= $this->print_manage_hidden_courses_top();
        }
        
        $this->content->text .= $OUTPUT->container(implode("\n", $content), 'container-fluid mb-3', 'ctl-courselist');
        
        $this->content->footer = '';
        /***           GENERATE OUTPUT FOR HIDDEN COURSES MANAGEMENT BOTTOM BOX       ***/
        // Do only if course hiding is enabled.
        if ($this->config->enablehidecourses) {
            // If hidden courses managing is active, output the box as visible.
            $this->content->footer .= $this->print_manage_hidden_courses_bottom();
        }
        
        if (empty($this->config->hideallcourseslink) || is_siteadmin($USER->id)) {
            //$renderer = $PAGE->get_renderer('core', 'course');
            //$search = $renderer->course_search_form('', 'short');
            
            $this->content->footer .= $this->print_search_all();
        }
        
        /***                             AJAX MANAGEMENT                              ***/
        // Verify that course displaying paramete
        // Verify that course displaying pars are updatable by AJAX.
        foreach ($courses as $course) {
            if ($this->config->enablehidecourses) {
                user_preference_allow_ajax_update('block_course_termlist-hidecourse-'.$course->id, PARAM_BOOL);
            }
        }

        // Verify that filter parameters are updatable by AJAX.
        if ($this->config->termcoursefilter == true) {
            user_preference_allow_ajax_update('block_course_termlist-selectedterm', PARAM_ALPHANUMEXT);
        }
        if ($this->config->categorycoursefilter == true) {
            user_preference_allow_ajax_update('block_course_termlist-selectedcategory', PARAM_ALPHANUM);
        }
        if ($this->config->toplevelcategorycoursefilter == true) {
            user_preference_allow_ajax_update('block_course_termlist-selectedtoplevelcategory', PARAM_ALPHANUM);
        }
    
        // Include JS for hiding courses with AJAX.
        if ($this->config->enablehidecourses) {
            $js_hidecoursesoptions = [
                'local_boostcoc' => block_course_termlist_check_local_boostcoc(),
                'courses' => trim($js_courseslist),
                'manage' => $this->param_manage,
                ];
            $PAGE->requires->js_call_amd('block_course_termlist/hidecourse', 'initHideCourse', [$js_hidecoursesoptions]);
        }

        // Include JS for filtering courses with AJAX.
        if ($this->config->termcoursefilter == true || $this->config->categorycoursefilter == true || $this->config->toplevelcategorycoursefilter == true) {
            $js_filteroptions = [
                'local_boostcoc' => block_course_termlist_check_local_boostcoc(),
                'initialsettings' => [
                    'term' => (isset($this->selectedterm)) ? $this->selectedterm : '',
                    'teacher' => (isset($this->selectedteacher)) ? $this->selectedteacher : '',
                    'category' => (isset($this->selectedcategory)) ? $this->selectedcategory : '',
                    'toplevelcategory' => (isset($this->selectedtoplevelcategory)) ? $this->selectedtoplevelcategory : '',
                ],
            ]; // Passing the initialsettings to the JS code is necessary for filtering the course list again when using browser 'back' button.
            $PAGE->requires->js_call_amd('block_course_termlist/filter', 'initFilter', [$js_filteroptions]);
        }
    
        /********************************************************************************/
        /***                             LOCAL_BOOSTCOC                               ***/
        /********************************************************************************/

        // Do only if local_boostcoc is installed.
        if (block_course_termlist_check_local_boostcoc() == true) {
            // Remember the not shown courses for local_boostcoc.
            block_course_termlist_remember_notshowncourses_for_local_boostcoc($courses);

            // Verify that we can also remember the not shown courses for local_boostcoc by AJAX.
            user_preference_allow_ajax_update('local_boostctl-notshowncourses', PARAM_RAW);

            // Remember the active filters for local_boostcoc.
            block_course_termlist_remember_activefilters_for_local_boostcoc($hiddencoursescounter);

            // Verify that we can also remember the active filters for local_boostcoc by AJAX.
            user_preference_allow_ajax_update('local_boostctl-activefilters', PARAM_RAW);
        }
        
        
        return $this->content;
    }
    // end of block content 
    
    /**
     * block contents
     *
     * @return object
     */
    public function check_course_filters($course, $categorylist) {
    
            $course->hidecourse = false; 
            $course->termcoursefiltered = false;
            $course->categorycoursefiltered = false;
            $course->toplevelcategorycoursefiltered = false;
            $course->recentactivity = false;
            $course->shown = true;
            
            if(!$course->term || in_array($course->term, $this->config->timelessterms) || $course->term > 4 ) {
                $course->term = 0;
            }
            
            // Term filter.
            if ($this->config->termcoursefilter == true) {
                // Add course term to filter list.
                if($course->term && !in_array($course->term, $this->config->timelessterms )) {
                    $this->filterterms[$course->term] = $this->config->{"term{$course->term}name"};
                }
            }
            
            // Parent category filter.
            if ($this->config->categorycoursefilter == true) {
                // Add course parent category name to filter list.
                $this->filtercategories[$course->category] =  $categorylist[$course->category]->name;
            }
            
            // Top level category filter.
            if ($this->config->toplevelcategorycoursefilter == true) {
                // Add course top level category name to filter list.
                // Get course top level category name from array of all category names.
                $coursecategory = $categorylist[$course->category];
                $coursecategorypath = explode('/', $coursecategory->path);
                $course->toplevelcategoryid = $coursecategorypath[1];
                $this->filtertoplevelcategories[$course->toplevelcategoryid] = $categorylist[$course->toplevelcategoryid]->name;
            }

            if ($this->config->showrecentactivity && local_ulpgccore_course_recent_activity($course)) {
                $course->recentactivity = true;
            }

            // Check if this course is hidden according to the hide courses feature.
            if ($this->config->enablehidecourses == true) {
                if($course->recentactivity) {
                    $course->hidecourse == false;
                } else {
                    $course->hidecourse = block_course_termlist_course_hidden_by_hidecourses($course);
                    // Increase counter for hidden courses management.
                    if ($course->hidecourse == true) {
                        $this->hiddencoursescounter++;
                    }
                }
            }
            
            // Check if this course is hidden according to the term course filter.
            if ($this->config->termcoursefilter == true) {
                $course->termcoursefiltered = block_course_termlist_course_hidden_by_termcoursefilter($course, $this->selectedterm, $this->config->timelessterms);
            }

            // Check if this course is hidden according to the parent category course filter.
            if ($this->config->categorycoursefilter == true) {
                $course->categorycoursefiltered = block_course_termlist_course_hidden_by_categorycoursefilter($course, $this->selectedcategory);
            }

            // Check if this course is hidden according to the top level category course filter.
            if ($this->config->toplevelcategorycoursefilter == true) {
                $course->toplevelcategorycoursefiltered = block_course_termlist_course_hidden_by_toplevelcategorycoursefilter($course, $this->selectedtoplevelcategory);
            }    
    
            $course->shown = !$course->hidecourse && !$course->termcoursefiltered &&
                                       !$course->categorycoursefiltered  && !$course->toplevelcategorycoursefiltered; 
                                       
        return $course;
    }
    
    
    /**
     * block contents
     *
     * @return object
     */
    public function print_course_row($course) {
        global $USER, $DB, $CFG, $OUTPUT, $PAGE;
        
        $output = '';
        
        // Start course div.
        $output .= '<div id="ctl-course-'.$course->id.'" class="row2 ctl-course">';    
        
        $ishidden = false;
        
        // Start hide course div - later we use this div to filter the course.
        if ($this->config->enablehidecourses == true && $this->param_manage == 0) {
            // Show course if it is visible according to the hide courses feature.
            $ishidden = ($course->hidecourse == false) ? '' : ' ctl-hidden '; 
            $output .= '<div class="hidecoursediv ctl-hidecourse-'.$course->id.$ishidden.'">';
        }
        
        // Start filter by term div - later we use this div to filter the course.
        if ($this->config->termcoursefilter == true && $this->param_manage == 0) {
            // Show course if it is visible according to the term course filter.
            $ishidden = ($course->termcoursefiltered == false) ? '' : ' ctl-hidden ';   
            $ishidden = '';
            $output .= '<div class="termdiv ctl-term-'.$course->term.$ishidden.'">';
        }
        
        // Start filter by parent category div - later we use this div to filter the course.
        if ($this->config->categorycoursefilter == true && $this->param_manage == 0) {
            // Show course if it is visible according to the parent category course filter.
            $ishidden = ($course->categorycoursefiltered == false) ? '' : ' ctl-hidden ';   
            $output .= '<div class="categorydiv ctl-category-'.$course->category.$ishidden.'">';
        }
        
        // Start filter by top level category div - later we use this div to filter the course.
        if ($this->config->toplevelcategorycoursefilter == true && $this->param_manage == 0) {
            // Show course if it is visible according to the top level category course filter.
            $ishidden = ($course->toplevelcategorycoursefiltered == false) ? '' : ' ctl-hidden ';   
            $output .= '<div class="toplevelcategorydiv ctl-toplevelcategory-'.$course->toplevelcategoryid.$ishidden.'">';
        }
        
        // Start standard course overview coursebox.
        $output .= $OUTPUT->container_start('coursebox');
        
        $output .= $this->print_showhide_icons($course->id);

      
        // Get course attributes for use with course link.
        $attributes = array('title' => format_string($course->fullname), 'class' => 'my-course-name'  );
        if (empty($course->visible)) {
            $attributes['class'] .= ' dimmed ';
        }
                
        $coursename = $this->config->showshortname ? $course->shortname.' - '.$course->fullname : $course->fullname;
        if($course->recentactivity) {
            $coursename .= ' '.html_writer::tag('i', '', array('class'=>'fa fa-dot-circle-o'));
        }
        
        $output .= $OUTPUT->heading(html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $coursename, $attributes), 3);
        
        if($this->config->showteachername) {
            $hideonphones =  $this->config->hideonphones ? ' hidden-phone hidden-sm-down ' : '';
            $teachers = $this->print_course_teachers($course);
            $output .= html_writer::div($teachers, 'ctl-metainfo '.$hideonphones);
        }
        
        // End standard course overview coursebox.
        $output .= $OUTPUT->container_end();

        // End filter by term div.
        if ($this->config->termcoursefilter == true && $this->param_manage == 0) {
            $output .= '</div>';
        }

        // End filter by parent category div.
        if ($this->config->categorycoursefilter == true && $this->param_manage == 0) {
            $output .= '</div>';
        }

        // End filter by top level category div.
        if ($this->config->toplevelcategorycoursefilter == true && $this->param_manage == 0) {
            $output .= '</div>';
        }

        // End hide course div.
        if ($this->config->enablehidecourses == true && $this->param_manage == 0) {
            $output .= '</div>';
        }

        // End course div.
        $output .= '</div>';

        
        return $output;
    }

    /**
     * /***                        GENERATE OUTPUT FOR FILTER                     
     *
     * @return object
     */
    public function print_showhide_icons($courseid) {
        global $CFG, $OUTPUT, $PAGE;
        
        $output = '';
        // Output course visibility control icons.
        if ($this->config->enablehidecourses) {
        /*
            $hidestr = get_string('hidecourse', 'block_course_termlist');
            $showstr = get_string('showcourse', 'block_course_termlist');
            $class = array(1=>'', 2 =>'');
            if((block_course_termlist_course_hidden_by_hidecourses($course, 0) == false)) {
                $class[2] = 'ctl-hidden';
            } else {
                $class[1] = 'ctl-hidden';
            }
            
            $hidestr = get_string('hidecourse', 'block_course_termlist');
            $icon = $OUTPUT->pix_icon('hide', $hidestr, 'block_course_termlist');
            $attributes = array('id' => 'ctl-hidecourseicon-'.$course->id,
                                'title' => $hidestr, 
                                'class' => $class[1]);
            $url = new moodle_url($PAGE->url, array('ctl-manage'=>$this->param_manage, 'ctl-hidecourse' =>$course->id, 'ctl-showcourse' => ''));
            $link1 = html_writer:        :link($url, $icon, $attributes);
            $showstr = get_string('showcourse', 'block_course_termlist');
            $icon = $OUTPUT->pix_icon('show', $showstr, 'block_course_termlist');
            $attributes['title'] = $showstr;
            $attributes['class'] = $class[2];
            $url->params(array('ctl-hidecourse' => '', 'ctl-showcourse' => $course->id));
            $link2 = html_writer::link($url, $icon, $attributes);
        
            $output .= html_writer::div($link1.$link2, 'hidecourseicon');
            
            */

            // If course is hidden.
            if (block_course_termlist_course_hidden_by_hidecourses($courseid) == false) { // We can't rely on $course->hidecourse here because otherwise the icon would always be t/show.
                $output .= '<div class="hidecourseicon">
                        <a href="'.$CFG->wwwroot.$PAGE->url->out_as_local_url(true, array('ctl-manage' => $this->param_manage, 'ctl-hidecourse' => $courseid, 'ctl-showcourse' => '')).'" id="ctl-hidecourseicon-'.$courseid.'" title="'.get_string('hidecourse', 'block_course_termlist').'">'.$OUTPUT->pix_icon('hide', get_string('hidecourse', 'block_course_termlist'), 'block_course_termlist').'</a>
                        <a href="'.$CFG->wwwroot.$PAGE->url->out_as_local_url(true, array('ctl-manage' => $this->param_manage, 'ctl-hidecourse' => '', 'ctl-showcourse' => $courseid)).'" id="ctl-showcourseicon-'.$courseid.'" class="ctl-hidden" title="'.get_string('showcourse', 'block_course_termlist').'">'.$OUTPUT->pix_icon('show', get_string('showcourse', 'block_course_termlist'), 'block_course_termlist').'</a>
                    </div>';
            }
            // If course is visible.
            else {
                $output .= '<div class="hidecourseicon">
                        <a href="'.$CFG->wwwroot.$PAGE->url->out_as_local_url(true, array('ctl-manage' => $this->param_manage, 'ctl-hidecourse' => $courseid, 'ctl-showcourse' => '')).'" id="ctl-hidecourseicon-'.$courseid.'" class="ctl-hidden" title="'.get_string('hidecourse', 'block_course_termlist').'">'.$OUTPUT->pix_icon('hide', get_string('hidecourse', 'block_course_termlist'), 'block_course_termlist').'</a>
                        <a href="'.$CFG->wwwroot.$PAGE->url->out_as_local_url(true, array('ctl-manage' => $this->param_manage, 'ctl-hidecourse' => '', 'ctl-showcourse' => $courseid)).'" id="ctl-showcourseicon-'.$courseid.'" title="'.get_string('showcourse', 'block_course_termlist').'">'.$OUTPUT->pix_icon('show', get_string('showcourse', 'block_course_termlist'), 'block_course_termlist').'</a>
                    </div>';
            }

        }
        return $output;
    }
    
    
    /**
     * /***                        GENERATE OUTPUT FOR FILTER                     
     *
     * @return object
     */
    public function print_course_teachers($course) {    

    
        $now = round(time(), -2); // Improves db caching.
        $extrawhere = 'ue.status = '.ENROL_USER_ACTIVE.' AND e.status = '.ENROL_INSTANCE_ENABLED.' AND ue.timestart < '.$now.' AND (ue.timeend = 0 OR ue.timeend > '.$now.')';

        $courseteachers = array();
        // Get course teachers based on global teacher roles.
        if (count($this->teacherroles) > 0) {
            // Get all user name fields for SQL query in a proper way.
            $fields = \core_user\fields::for_name();
            $allnames = $fields->get_sql('u')->selects;            
            $teacherfields = 'ra.id AS raid, u.id, r.sortorder, rn.name'.$allnames; // Moodle would complain about two columns called id. That's why we alias one column to a name different than id.
            $teachersortfields = 'r.sortorder, u.lastname, u.firstname';
            $now = round(time(), -2); // Improves db caching.
            $extrawhere = 'ue.status = '.ENROL_USER_ACTIVE.' AND e.status = '.ENROL_INSTANCE_ENABLED.' AND ue.timestart < '.$now.' AND (ue.timeend = 0 OR ue.timeend > '.$now.')';
            
            $context = context_course::instance($course->id);
            $courseteachers = get_role_users($this->teacherroles, $context, true,
                            $teacherfields, $teachersortfields, false, '', '', '', $extrawhere);
        }

        $trailing = '';
        if($courseteachers) {
            $count = count($courseteachers);
            if($count > 6) {
                $trailing = get_string('teacheretal', 'block_course_termlist', $count - 6 );
                $courseteachers = array_slice($courseteachers, 0, 6);
            }
            $url = new moodle_url('/user/view.php', array('course'=>$course->id));
            foreach($courseteachers as $key => $teacher) {
                $url->param('id', $teacher->id); 
                $courseteachers[$key] = html_writer::link($url, fullname($teacher));
            } 
        
        } else {
            $courseteachers[] = get_string('none');
        }
        $label = (count($courseteachers) > 1) ? get_string('teachers') : get_string('teachers');
    
        return $label.': '.implode(', ', $courseteachers).$trailing;;
    }
    
    
    /**
     * /***                        GENERATE OUTPUT FOR FILTER                     
     *
     * @return object
     */
    public function print_filter_form() {
        global $OUTPUT, $PAGE;

        // Show filter form if any filter is activated and if hidden courses management isn't active.
        if ((!$this->config->enablehidecourses || $this->param_manage == 0) && ($this->config->categorycoursefilter == true || $this->config->toplevelcategorycoursefilter == true || $this->config->termcoursefilter == true)) {
            // Calculate CSS class for filter divs.
            $filtercount = 0;
            if ($this->config->termcoursefilter == true) {
                $filtercount++;
            }
            if ($this->config->categorycoursefilter == true) {
                $filtercount++;
            }
            if ($this->config->toplevelcategorycoursefilter == true) {
                $filtercount++;
            }
            if ($filtercount == 1) {
                $filterwidth = 'span12 col-md-12'; // Class 'span12' is used for Bootstrapbase and will be ignored by Boost.
            } else if ($filtercount == 2) {
                $filterwidth = 'span6 col-md-6'; // Class 'span6' is used for Bootstrapbase and will be ignored by Boost.
            } else if ($filtercount == 3) {
                $filterwidth = 'span4 col-md-4'; // Class 'span4' is used for Bootstrapbase and will be ignored by Boost.
            } else if ($filtercount == 4) {
                $filterwidth = 'span3 col-md-6 col-lg-3'; // Class 'span3' is used for Bootstrapbase and will be ignored by Boost.
            } else {
                $filterwidth = 'span12 col-md-12'; // Class 'span12' is used for Bootstrapbase and will be ignored by Boost.
            }
            
            $form = '';   
            
            // Show term filter.
            if ($this->config->termcoursefilter == true) {
                // Show filter description.
                $label = '';
                if ($this->config->termcoursefilterdisplayname != '') {
                    $label = html_writer::label(format_string($this->config->termcoursefilterdisplayname), 'ctl-filterterm');
                }
                if($label) {
                    $label .= "\n";
                }
                
                // Remember in this variable if selected term was displayed or not.
                $displayed = false;

                // Sort term filter alphabetically in reverse order.
                ksort($this->filterterms);
                
                $options = array('all' => get_string('all', 'block_course_termlist')); 
                foreach($this->filterterms as $key =>$term) {
                    $options[$key] = format_string($term);
                }
                if(array_key_exists($this->selectedcategory, $options)) {
                    $displayed = true;
                }

                $select = html_writer::select($options, 'ctl-term', $this->selectedterm, null,
                                                array('id' => 'ctl-filterterm', 'class' => 'input-block-level form-control'));
                                                
                $form .= html_writer::div($label.$select, 'ctl-filter '.$filterwidth.' mb-3');
                // If selected parent category couldn't be displayed, select all categories and save the new selection.
                // In this case, no option item is marked as selected, but that's ok as the "all" item is at the top.
                if (!$displayed) {
                    $this->selectedterm = 'all';
                    set_user_preference('block_course_termlist-selectedterm', $this->selectedterm);
                }
            
            
            }
            
            // Show top level category filter.
            if ($this->config->toplevelcategorycoursefilter == true) {
                // Show filter description.
                $label = '';
                if ($this->config->toplevelcategorycoursefilterdisplayname != '') {
                    $label = html_writer::label(format_string($this->config->toplevelcategorycoursefilterdisplayname), 'ctl-toplevelcategory');
                }
                if($label) {
                    $label .= "\n";
                }
                
                // Remember in this variable if selected parent category was displayed or not.
                $displayed = false;
                
                // Sort top level category filter by category sort order.
                // Fetch full category information for each category.
                $filtertoplevelcategoriesfullinfo = [];
                foreach ($this->filtertoplevelcategories as $ftl_key => $ftl_value) {
                    $filtertoplevelcategoriesfullinfo[] = $coursecategories[$ftl_key];
                }
                // Sort full category information array by sortorder.
                $success = usort($filtertoplevelcategoriesfullinfo, "block_course_termlist_compare_categories");
                // If sorting was successful, create new array with same data structure like the old one.
                // Otherwise just leave the old array as it is (should not happen).
                if ($success) {
                    $this->filtertoplevelcategories = array();
                    foreach ($filtertoplevelcategoriesfullinfo as $ftl) {
                        $this->filtertoplevelcategories[$ftl->id] = format_string($ftl->name);
                    }
                }

                $options = array('all' => get_string('all', 'block_course_termlist')); 
                foreach($this->filtertoplevelcategories as $key =>$cat) {
                    $options[$key] = $cat;
                }
                if(array_key_exists($this->selectedtoplevelcategory, $options)) {
                    $displayed = true;
                }
                
                $select = html_writer::select($options, 'ctl-category', $this->selectedtoplevelcategory, 
                                                array('id' => 'ctl-toplevelcategory', 'class' => 'input-block-level form-control'));
                                                
                $form .= html_writer::div($label.$select, 'ctl-filter '.$filterwidth.' mb-3');
                // If selected parent category couldn't be displayed, select all categories and save the new selection.
                // In this case, no option item is marked as selected, but that's ok as the "all" item is at the top.
                if (!$displayed) {
                    $this->selectedtoplevelcategory = 'all';
                    set_user_preference('block_course_termlist-selectedtoplevelcategory', $this->selectedtoplevelcategory);
                }
            }
            
            // Show parent category filter.
            if ($this->config->categorycoursefilter == true) {
                // Show filter description.
                $label = '';
                if ($this->config->categorycoursefilterdisplayname != '') {
                    $label = html_writer::label(format_string($this->config->categorycoursefilterdisplayname), 'ctl-filtercategory');
                }
                if($label) {
                    $label .= "\n";
                }
                
                // Remember in this variable if selected parent category was displayed or not.
                $displayed = false;
                // Sort parent category filter alphabetically.
                natcasesort($this->filtercategories);
                $options = array('all' => get_string('all', 'block_course_termlist')); 
                foreach($this->filtercategories as $key =>$cat) {
                    $options[$key] = $cat;
                }
                if(array_key_exists($this->selectedcategory, $options)) {
                    $displayed = true;
                }
                
                $select = html_writer::select($options, 'ctl-category', $this->selectedcategory, 
                                                array('id' => 'ctl-filtercategory', 'class' => 'input-block-level form-control'));
                                                
                $form .= html_writer::div($label.$select, 'ctl-filter '.$filterwidth.' mb-3');
                // If selected parent category couldn't be displayed, select all categories and save the new selection.
                // In this case, no option item is marked as selected, but that's ok as the "all" item is at the top.
                if (!$displayed) {
                    $this->selectedcategory = 'all';
                    set_user_preference('block_course_termlist-selectedcategory', $this->selectedcategory);
                }
            }
            
            $formdiv = $OUTPUT->container(html_writer::div($form, 'row2'), 'container-fluid', 'ctl-filterlist');
            
            $submit = html_writer::empty_tag('input', array('type' => 'submit', 'class' => 'btn btn-primary', 
                                            'value' => get_string('submitfilter', 'block_course_termlist')  )); 
            $formdiv .= $OUTPUT->container(html_writer::div($submit, 'row2'), 'container-fluid mb-3', 'ctl-filtersubmit');
        
            return html_writer::nonempty_tag('form', $formdiv, array('method' => 'post', 'action' => ''));
        }
        
        return;
    }    
    
    /**
     * block contents
     *
     * @return object
     */
    public function print_manage_hidden_courses_top() {
        global $OUTPUT, $PAGE;
        
        if ($this->param_manage == 1) {
            $url =  clone $PAGE->url;
            $url->param('ctl-manage', 0);
            $link = html_writer::link($url, get_string('stopmanaginghiddencourses', 'block_course_termlist'));
            return $OUTPUT->container(html_writer::div($link, 'row2'), 'container-fluid', 'ctl-hiddencoursesmanagement-top');
        }
        
        return '';
    }
    
    
    /**
     * block contents
     *
     * @return object
     */
    public function print_manage_hidden_courses_bottom() {
        global $OUTPUT, $PAGE;
            
        $hiddenclass = '';
        $url = clone $PAGE->url;
        if ($this->param_manage == 1) {
            $url->param('ctl-manage',  0);
            $inner = html_writer::link($url, get_string('stopmanaginghiddencourses', 'block_course_termlist'));
            //echo '<a href="'.$CFG->wwwroot.$PAGE->url->out_as_local_url(true, array('ctl-manage' => 0)).'">'.get_string('stopmanaginghiddencourses', 'block_course_termlist').'</a></div></div>';
        } else {
            // If hidden courses managing is not active, but I have hidden courses, output the box as visible.
            $counter = $this->hiddencoursescounter + $this->filteredcoursescounter;
            
            if ($this->param_manage == 0 && $counter > 0) {
            } else {
                $hiddenclass = 'ctl-hidden';
            }
            $inner = ''; //get_string('youhave', 'block_course_termlist');
            $inner .= get_string('hiddencourses', 'block_course_termlist', html_writer::span($counter, '', array('id'=>'ctl-hiddencoursescount')));
            $url->param('ctl-manage',  1);
            $inner .= ' | '.html_writer::link($url, get_string('managehiddencourses', 'block_course_termlist'));
        }

        return $OUTPUT->container(html_writer::div($inner, "row $hiddenclass"), 
                                                    'container-fluid', 'ctl-hiddencoursesmanagement-bottom');
    }
    
    /**
     * block contents
     *
     * @return object
     */
    public function print_search_all() {
        $footer = '';
        $url = new moodle_url('/course/search.php');
        $footer   .= html_writer::start_tag('form', array('id' => 'coursesearch',
                                                            'action' => $url,
                                                            'method' => 'get'));
        $footer   .= html_writer::start_tag('fieldset', array('class' => 'coursesearchbox'));
        $footer   .= html_writer::empty_tag('input', array('type' => 'text',
                                                            'name' => 'search',
                                                            'class' => 'searchfield'));
        $footer   .= html_writer::link('javascript: coursesearch.submit()',
                                        '<i class="fa fa-search"></i>',
                                        array('id' => 'searchbutton'));
        $footer   .= html_writer::end_tag('fieldset');
        $footer   .= html_writer::end_tag('form');

        
        $url = new moodle_url('/course/index.php');
        $footer .= html_writer::div(html_writer::link($url, get_string('fulllistofcourses') . '...'), 'allcourses');
        return $footer;
    }
    

    /**
     * block contents
     *
     * @return object
     */
    public function set_selected_user_filters_prefs() {
        $param_hidecourse = optional_param('ctl-hidecourse', 0, PARAM_BOOL);
        $param_showcourse = optional_param('ctl-showcourse', 0, PARAM_BOOL);
        $param_term = optional_param('ctl-term', null, PARAM_ALPHANUMEXT);
        $param_category = optional_param('ctl-category', null, PARAM_ALPHANUM);
        $param_toplevelcategory = optional_param('ctl-toplevelcategory', null, PARAM_ALPHANUM);
        
        //$param_teacher = optional_param('ctl-teacher', null, PARAM_ALPHANUM);
        
        $this->param_manage = optional_param('ctl-manage', 0, PARAM_BOOL);
        
    
        // Get teacher roles for later use.
        $this->teacherroles = array();
        if (!empty($this->config->teacherroles)) {
            $this->teacherroles = explode(',', $this->config->teacherroles);
        }

        // Create empty filter for activated filters.
        if ($this->config->termcoursefilter == true) {
            $this->filterterms = array();
        }
        if ($this->config->categorycoursefilter == true) {
            $this->filtercategories = array();
        }
        if ($this->config->toplevelcategorycoursefilter == true) {
            $this->filtertoplevelcategories = array();
        }

        $this->config->showrecentactivity = $this->config->showrecentactivity && get_config('local_ulpgccore', 'enabledrecentactivity');
        
        // Create counter for hidden courses.
        if ($this->config->enablehidecourses) {
            $this->hiddencoursescounter = 0;
        }
    
        // Set displaying preferences when set by GET parameters.
        if ($this->config->enablehidecourses) {
            if ($param_hidecourse != 0) {
                set_user_preference('block_course_termlist-hidecourse-'.$param_hidecourse, 1);
            }
            if ($param_showcourse != 0) {
                set_user_preference('block_course_termlist-hidecourse-'.$param_showcourse, 0);
            }
        }

        // Set and remember term filter if GET parameter is present.
        if ($this->config->termcoursefilter == true) {
            if ($param_term != null) {
                $this->selectedterm = $param_term;
                set_user_preference('block_course_termlist-selectedterm', $param_term);
            } else {
                // Or set term filter based on user preference with 'all' terms fallback.
                $this->selectedterm = get_user_preferences('block_course_termlist-selectedterm', 'all');
            }
        }

        // Set and remember parent category filter if GET parameter is present.
        if ($this->config->categorycoursefilter == true) {
            if ($param_category != null) {
                $this->selectedcategory = $param_category;
                set_user_preference('block_course_termlist-selectedcategory', $param_category);
            }
            // Or set parent category filter based on user preference with 'all' categories fallback.
            else {
                $this->selectedcategory = get_user_preferences('block_course_termlist-selectedcategory', 'all');
            }
        }

        // Set and remember top level category filter if GET parameter is present.
        if ($this->config->toplevelcategorycoursefilter == true) {
            if ($param_toplevelcategory != null) {
                $this->selectedtoplevelcategory = $param_toplevelcategory;
                set_user_preference('block_course_termlist-selectedtoplevelcategory', $param_toplevelcategory);
            }
            // Or set top level category filter based on user preference with 'all' categories fallback.
            else {
                $this->selectedtoplevelcategory = get_user_preferences('block_course_termlist-selectedtoplevelcategory', 'all');
            }
        }
    }
    
    
}

