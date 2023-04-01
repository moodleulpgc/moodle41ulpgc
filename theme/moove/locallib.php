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
 * Theme functions.  FROM Theme Boost Campus - Locallib file
 *
 * @package    theme_moove  modified from  * Theme Boost Campus - Locallib file
 * @copyright 2023 Enrique castro  @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



/**
 * Provides the node for the in-course course or activity settings.
 *
 * @return navigation_node.
 */
function theme_moove_get_incourse_settings() {
    global $COURSE, $PAGE;
    // Initialize the node with false to prevent problems on pages that do not have a courseadmin node.
    $node = false;

    // If setting showsettingsincourse is enabled.
    if (1 || get_config('theme_moove', 'showsettingsincourse') == 'yes') { // TODO CHANGE when setting available
        // Only search for the courseadmin node if we are within a course or a module context.
        if ($PAGE->context->contextlevel == CONTEXT_COURSE || $PAGE->context->contextlevel == CONTEXT_MODULE) {
            // Get the courseadmin node for the current page.
            $node = $PAGE->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            // Check if $node is not empty for other pages like for example the langauge customization page.
            
            //print_object($node->get_children_key_list());
			//echo "<script>alert('ok0');</script>";
			//print_object($node);
            return $node;
            if (!empty($node)) {
                // If the setting 'incoursesettingsswitchtoroleposition' is set either set to the option 'yes'
                // or to the option 'both', then add these to the $node.
                if (((get_config('theme_moove', 'incoursesettingsswitchtoroleposition') == 'yes') ||
                    (get_config('theme_moove', 'incoursesettingsswitchtoroleposition') == 'both'))
                    && !is_role_switched($COURSE->id)) {
                    // Build switch role link
                    // We could only access the existing menu item by creating the user menu and traversing it.
                    // So we decided to create this node from scratch with the values copied from Moodle core.
                    $roles = get_switchable_roles($PAGE->context);
                    if (is_array($roles) && (count($roles) > 0)) {
                        // Define the properties for a new tab.
                        $properties = array('text' => get_string('switchroleto', 'theme_moove'),
                                            'type' => navigation_node::TYPE_CONTAINER,
                                            'key'  => 'switchroletotab');
                        // Create the node.
                        $switchroletabnode = new navigation_node($properties);
                        // Add the tab to the course administration node.
                        $node->add_node($switchroletabnode);
                        // Add the available roles as children nodes to the tab content.
                        foreach ($roles as $key => $role) {
                            $properties = array('action' => new moodle_url('/course/switchrole.php',
                                array('id'         => $COURSE->id,
                                      'switchrole' => $key,
                                      'returnurl'  => $PAGE->url->out_as_local_url(false),
                                      'sesskey'    => sesskey())),
                                                'type'   => navigation_node::TYPE_CUSTOM,
                                                'text'   => $role);
                            $switchroletabnode->add_node(new navigation_node($properties));
                        }
                    }
                }
            }
        }
        return $node;
    }
}

/**
 * Provides the node for the in-course settings for other contexts.
 *
 * @return navigation_node.
 */
function theme_moove_get_incourse_activity_settings() {
    global $PAGE;
    $context = $PAGE->context;
    $node = false;
	
    // If setting showsettingsincourse is enabled
    if (get_config('theme_moove', 'showsettingsincourse') == 'yes') {
		//echo "<script>alert('ok');</script>"; 
        // Settings belonging to activity or resources.
        if ($context->contextlevel == CONTEXT_MODULE) {
            $node = $PAGE->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
			
			print_object($node->get_children_key_list());
        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($PAGE->pagetype === 'course-index-category') {
                $node = $PAGE->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
            }
        } else {
            $node = false;
        }
    }
	
	
    return $node;
}