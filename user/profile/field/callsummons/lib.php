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
 * Plugin callbacks.
 *
 * @package    profilefield_callsummons
 * @copyright  ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\notification;
use core_user\output\myprofile\tree;
use profilefield_callsummons\local\helper;
/**
 * Callback to add blocks to the my page.
 *
 * @param tree $tree
 * @param [type] $user
 * @param [type] $iscurrentuser
 * @param [type] $course
 * @return void
 */
function profilefield_callsummons_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {

    $helper = new helper();
    $profilefields = $helper->get_enabled_fields();

    $context = empty($course) ? context_system::instance() : context_course::instance($course->id);
    //$helper = new helper();
    
   
    // TODO display all the blocks in the system context, but only those with information about the course.
    foreach ($profilefields as $profilefield) {
        $categoryname = $profilefield->shortname;
        // Get the category.
        if (!array_key_exists($categoryname, $tree->__get('categories'))) {
            // Create the category.
            $categorytitle = $profilefield->name;
            $category = new core_user\output\myprofile\category($categoryname, $categorytitle, 'private', ' callsummons '.$categoryname);
            $tree->add_category($category);
        } else {
            // Get the existing category.
            $category = $tree->__get('categories')[$categoryname];
        }
        
        // Add "Call summons" node only for current user or users that can see user hidden fields.
        $content = '';
      
        //print_object("iscurrentuser: $iscurrentuser  --");
        if ($iscurrentuser || has_capability('moodle/course:viewhiddenuserfields', $context)) {
            $content = $helper->get_profile_content($user->id, (bool) $iscurrentuser, $profilefield);
        }
        
        if (!empty($content)) {
            $node = new core_user\output\myprofile\node($categoryname, 'profilefield_' . $categoryname,
                get_string('courses'), null, null, $content);
            $category->add_node($node);
        }
    }
    
    return true;
}

/**
 * Callback to inject some Javascript and display warnings in the course pages.
 *
 * profilefield_callsummons/coursewarnings display icons in block_course_termlist.
 * profilefield_callsummons/cancelnotifications handles when a user dimiss a course notification.
 *
 * @return void
 */
function profilefield_callsummons_before_footer() {
    global $PAGE, $USER, $COURSE;

    $helper = new helper();
    $profilefields = $helper->get_enabled_fields();

    if (!empty($profilefields)) {
        foreach ($profilefields as $profilefield) {
            if (!empty($USER->profile[$profilefield->shortname])) {
                $userfields[$profilefield->shortname] = unserialize($USER->profile[$profilefield->shortname]);
                $userfields[$profilefield->shortname]['title'] = $profilefield->name;
                $userfields[$profilefield->shortname]['id'] = $profilefield->id;
                $userfields[$profilefield->shortname]['icon'] = !(empty($profilefield->param4)) ? $profilefield->param4 : 'fa-star';
                $userfields[$profilefield->shortname]['extraclass'] = 'callsummons-' . $profilefield->shortname . '-icon';
                $userfields[$profilefield->shortname]['iconalways'] = $profilefield->param5;
                $userfields[$profilefield->shortname]['description'] = format_text($profilefield->description, $profilefield->descriptionformat);
                $userfields[$profilefield->shortname]['allowdismiss'] = $profilefield->param3;
            }
        }

        // In the Dashboard, display warning icons along the courses.
        if (($PAGE->pagetype == "my-index") && (!empty($userfields))) {
            $PAGE->requires->js_call_amd('profilefield_callsummons/coursewarnings', 'init', [$userfields]);
        }
        if (isset($COURSE) && (!empty($userfields))) {
            foreach ($userfields as $userfield) {
                if (in_array($COURSE->id, array_keys($userfield)) && (($userfield[$COURSE->id] == null))) {
                    $icon = [
                        'pix' => 'i/bullhorn',
                        'component' => 'core'
                    ];
                    //if ($profilefield->param3 == 0) {
                    if ($userfield['allowdismiss'] == 0) {
                        $actions = [];
                    } else {
                        $actions = [
                            [
                                'title' => get_string('dismisswarning', 'profilefield_callsummons'),
                                'url' => '#',
                                'data' => [
                                    'action' => 'dismiss',
                                    'profilefieldid' => $userfield['id'],
                                    'hide' => 1,
                                    'record' => 1,
                                    'userid' => $USER->id,
                                ],
                            ],
                        ];
                    }
                    notification::add_call_to_action($icon, $userfield['title'].$userfield['description'], $actions, 'profilefield_callsummons/warning');
                }
            }
            // Calling the following more than once will register event listeners twice.
            $PAGE->requires->js_call_amd('profilefield_callsummons/cancelnotifications', 'registerEventListeners');
        }
    }
}
