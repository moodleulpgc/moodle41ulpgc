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
 * Library code for the synchronize groups report
 *
 * @package   report_autogroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define('ATTENDANCETOOLS_OFFSET_NEAREST', 'nearest' );
define('ATTENDANCETOOLS_OFFSET_NEXT', 'next' );
define('ATTENDANCETOOLS_OFFSET_PREV', 'previous' );

define('ATTENDANCETOOLS_START_WHOLE', 'whole' );
define('ATTENDANCETOOLS_START_HALF', 'half' );
define('ATTENDANCETOOLS_START_QUARTER', 'quarter' );


/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_attendancetools_extend_navigation_course($navigation, $course, $context) {
    // nothing inserted at course level
}


/**
 * Extends the settings navigation with the examboard settings.
 *
 * This function is called when the context for the page is a examboard module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $examboardnode {@link navigation_node}
 */
function report_attendancetools_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
    
}

/**
 * This function extends the module navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $cm
 */
function report_attendancetools_extend_navigation_module(navigation_node $navigation, $cm) {
    global $PAGE;
    /*
    print_object($navigation->get_children_key_list());
    print_object($cm->modname);
    print_object($navigation->get_children_key_list());
    */
    if ($cm->modname == 'attendance') {
        $context = context_module::instance($cm->id);

        // Do not add anything if not allowed to
        if(!has_any_capability(array('mod/attendance:manageattendances'), $context)) {
            return;
        }        
        
        if(!get_config('report_attendancetools', 'enabled')) {
            return;
        }
        
        if ($settingsnode = $navigation->find('roleassign', navigation_node::TYPE_SETTING)) {
                //print_object("Estoy en settingsnode");
                //print_object($navigation->get_children_key_list());
                $action = ['id'=>$cm->id, 'action' => 99];
                if(($group = optional_param('group', 0, PARAM_INT)) && ($group > 0)) {
                    $action['group'] = $group;
                }
                $url = new moodle_url('/report/attendancetools/index.php', $action);
                $node = $settingsnode->create(get_string('autosession', 'report_attendancetools'), $url, navigation_node::TYPE_SETTING, null, 'autosession');
                $navigation->add_node($node, 'roleassign');
               
                if (has_capability('mod/attendance:managetemporaryusers', $context)) {
                    $url = new moodle_url('/mod/attendance/tempusers.php', ['id'=>$cm->id]);
                    $navigation->add(get_string('tempusers', 'attendance'), $url, navigation_node::TYPE_SETTING, null, 'tempusers');
                }
                
                // TODO // TODO  // TODO
                // add assistancia.ulpgc.es
                $url = new moodle_url('https://asistencia.ulpgc.es/moodle.php', array('c'=>'profesor', 'shortname'=>$PAGE->course->shortname));
                $icon = ''; //html_writer::tag('i', '', ['class' => 'icon fa fa-external-link- fa-fw iconsmall']);
                //$navigation->add(get_string('asistencia', 'report_attendancetools').$icon, $url, navigation_node::TYPE_SETTING, null, 'crueasistencia');
                
                $url = new moodle_url('https://www.milista.ulpgc.es/moodle.php', array('c'=>'profesor', 'shortname'=>$PAGE->course->shortname));
                $navigation->add(get_string('milista', 'report_attendancetools').$icon, $url, navigation_node::TYPE_SETTING, null, 'milista');

                $url = new moodle_url('/report/attendancetools/index.php', array('id'=>$cm->id, 'action' => 'config' ));
                $navigation->add(get_string('instantconfig', 'report_attendancetools'), $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsconfig', new pix_icon('i/duration', 'core'));
                $node = $navigation->find('attendancetoolsconfig', navigation_node::TYPE_SETTING);
                $node->set_force_into_more_menu(true);             
                
        }  
    }
}



