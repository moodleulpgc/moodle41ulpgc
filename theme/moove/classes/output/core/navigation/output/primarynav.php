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

namespace theme_moove\output\core\navigation\output;


use renderable;
use renderer_base;
use templatable;
use custom_menu;
use html_writer;

/**
 * Primary navigation renderable
 *
 * This file combines primary nav, custom menu, lang menu and
 * usermenu into a standardized format for the frontend
 *
 * @package     core
 * @category    navigation
 * @copyright   2021 onwards Peter Dias
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class primarynav extends \core\navigation\output\primary implements renderable, templatable {


//class primary implements renderable, templatable {
    /** @var moodle_page $page the moodle page that the navigation belongs to */
    //private $page = null;

    /**
     * primary constructor.
     * @param \moodle_page $page
     */
    public function __construct($page) {
        global $PAGE;
        //$this->page = clone $PAGE;
        $this->page = $page;
    }

    /**
     * Combine the various menus into a standardized output.
     *
     * @param renderer_base|null $output
     * @return array
     */
    public function export_for_template(?renderer_base $output = null): array {
        if (!$output) {
            $output = $this->page->get_renderer('core');
        }

        $menudata = (object) array_merge($this->get_custom_menu($output), $this->get_primary_nav(), $this->get_courses_menu($output));

        $moremenu = new \core\navigation\output\more_menu($menudata, 'navbar-nav', false);
        $mobileprimarynav = array_merge($this->get_primary_nav(), $this->get_custom_menu($output));

        $languagemenu = new \core\output\language_menu($this->page);

        return [
            'mobileprimarynav' => $mobileprimarynav,
            'moremenu' => $moremenu->export_for_template($output),
            'lang' => !isloggedin() || isguestuser() ? $languagemenu->export_for_template($output) : [],
            'user' => $this->get_user_menu($output),
        ];
    }

    /**
     * Get the primary nav object and standardize the output
     *
     * @return array
     */
    protected function get_primary_nav(): array {
        $nodes = [];
        foreach ($this->page->primarynav->children as $node) {
            $text = $node->text;
            if($node->key == 'mycourses') {
                continue;
            }
            if($icon = get_config('theme_moove', 'icon'.$node->key)) {
                $attributes['class'] = "ircon fa $icon fa-lg";
                $attributes['title'] = $text; //$node->get_title();
                $attributes['aria-label'] = $node->text;
                $attributes['role'] = 'img';
                $text = html_writer::tag('i', '', $attributes);
            }


            $nodes[] = [
                'title' => $node->get_title(),
                'url' => $node->action(),
                'text' => $text,
                'icon' => $node->icon,
                'isactive' => $node->isactive,
                'key' => $node->key,
            ];
            /*
            $nodes[] = [
                'title' => $node->get_title(),
                'url' => $node->action(),
                'text' => $node->text,
                'icon' => $node->icon,
                'isactive' => $node->isactive,
                'key' => $node->key,
            ];
            */
        }

        return $nodes;
    }


    /**
     * Custom menu items reside on the same level as the original nodes.
     * Fetch and convert the nodes to a standardised array.
     *
     * @param renderer_base $output
     * @return array
     */
    protected function get_courses_menu(renderer_base $output): array {
        global $CFG, $OUTPUT, $PAGE;

        $courses = enrol_get_my_courses();
        
        $block = $this->get_remote_block();
        $remotecourseurl = '';
        $remotes = [];
        if(!empty($block)) {
            $remotecourseurl = $block->config->remotesite.'/course/view.php?id=';
            $remotes = $block->get_remote_courses_list();
        }
        
        // Early return if a courses list does not exists.
        if (empty($courses) && empty($remotes)) {
            return [];
        }

        $currentcourseid = $PAGE->course->id;
        if($currentcourseid == SITEID) {
            $currentcourseid = 0;
        }
        
        $faicon = get_config('theme_moove', 'iconmycourses');
        $attributes['class'] = "ircon fa $faicon fa-lg";
        $attributes['title'] = get_string('mycourses');
        $attributes['aria-label'] = $attributes['title'];
        $attributes['role'] = 'img';
        //$menuitems[] = html_writer::tag('i', '', $attributes);

        $item = new \stdclass();
        $item->moremenuid = uniqid();
        $item->url = '';
        $item->title = '';
        $item->sort = 1;
        $item->children  = [];
        $item->haschildren  = false ;   !(empty($courses) && empty($remotes));
        
        $menuitems = [];
        $sort = 2;
        foreach ($courses as $course) {
            $item->moremenuid = 'localown-'.uniqid(); 
            $item->text = format_string(get_course_display_name_for_list($course));
            $linkcss = $course->visible ? '' : 'dimmed';
            if($course->id == $currentcourseid) {
                $linkcss .= ' currentcourse'; 
                $item->isactive = 1;
            }
            if($linkcss) {
                $item->text = html_writer::span($item->text, $linkcss);
            }
            $item->url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
            $item->title = '';
            $item->sort = $sort;
            $item->children  = [];
            $item->haschildren  = false ;  
            $menuitems[] = clone $item;
            $item->isactive = 0;
            $sort++;
        }
        
        if(!empty($menuitems)) {
            $item->moremenuid = uniqid(); 
            $item->text = '###';
            $item->url = '';
            $item->divider = 1;
            $item->sort = $sort;
            $menuitems[] = clone $item;
            $sort++;
            $item->divider = null ;
            $item->moremenuid = uniqid(); 
            $item->text =get_string('mycourses');
            $item->url = $CFG->wwwroot.'/my/courses.php';
            $item->sort = $sort;
            $menuitems[] = clone $item;
            $sort++;
        }
        
        // if there are regular courses & remote courses, add separator  and title
        if(!empty($menuitems) && !empty($remotes)) {
            $item->moremenuid = uniqid(); 
            $item->text = '###';
            $item->url = '';
            $item->divider = 1;
            $item->sort = $sort;
            $menuitems[] = clone $item;
            $sort++;
            $item->divider = null ;
            $item->moremenuid = uniqid(); 
            $item->sort = $sort;
            $item->text = html_writer::span($block->blockname, 'myremotecourses title');  
            $menuitems[] = clone $item;
            $sort++;
        }

        foreach ($remotes as $course) {
            $item->moremenuid = 'remoteown-'.uniqid(); 
            $item->text = format_string(get_course_display_name_for_list($course));
            $item->url = $remotecourseurl.$course->id;
            $item->title = '';
            $item->sort = $sort;
            $item->children  = [];
            $item->haschildren  = false ;  
            $menuitems[] = clone $item;
            $sort++;
        }
        
        $item->moremenuid = uniqid();
        $item->text = html_writer::tag('i', '', $attributes);
        $item->url = '';
        $item->title = '';
        $item->classes = ' mycourseslist ';
        $item->sort = 1;
        $item->divider = null ;
        $item->children  = $menuitems;
        $item->haschildren  =!(empty($menuitems));        
        
        $nodes = [];
        $nodes[] = $item;
        return $nodes;
    }


    /**
     * Get/Generate the user menu.
     *
     * This is leveraging the data from user_get_user_navigation_info and the logic in $OUTPUT->user_menu()
     *
     * @param renderer_base $output
     * @return array
     */
    public function get_user_menu(renderer_base $output): array {
        // this hack is needed to ensure language_menu is created with the current page url
        $primary = new \core\navigation\output\primary($this->page);
        return $primary->get_user_menu($output);
    }
    
    /**
     * Returns ans instantiated remote courses block
     *
     * @return block class object
     */
    protected function get_remote_block() {
        global $CFG;
        
        $block = false;
        $myremote = get_config('theme_moove', 'remotemycourses'); 
        if($myremote) {
            $block = block_instance_by_id($myremote);
            if(!empty($block)) {
                return $block;
            }
        }
        
        return false;
    }
    
}
