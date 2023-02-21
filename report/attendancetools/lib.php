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
        
        //print_object($navigation->get_children_key_list());
        $sn = $PAGE->secondarynav;
        $sn = $PAGE->navigation;
        
        //print_object($sn->get_children_key_list());
        
        $sn = $PAGE->settingsnav;
        //print_object(get_class($sn));
        
        if($PAGE->has_secondary_navigation()) {
            $PAGE->set_secondary_navigation(true, false);
            $sn = $PAGE->secondarynav;
            
            $nodes = $navigation->get_siblings();
            //print_object($nodes);
        
            //print_object(get_class($sn));
            //print_object($sn->get_children_key_list());
            
            //print_object('has_secondary_navigation');
            
            
        }
        
        if($PAGE->has_tablist_secondary_navigation()) {
            //print_object('has_tablist secondary_navigation');
        }

        
        $sn = $navigation->get('roleassign'); 
        //print_object($sn->type);
        
        // Do not add anything if not allowed to
        if(!has_any_capability(array('mod/attendance:manageattendances'), $context)) {
            return;
        }        
        
        $url = new moodle_url('/report/attendancetools/index.php', array('id'=>$cm->id));

        //print_object($navigation->get_children_key_list());
        //https://www.milista.ulpgc.es/moodle.php?c=profesor&shortname=42901
        
        
    //return;
    if ($settingsnode = $navigation->find('roleassign', navigation_node::TYPE_SETTING)) {
            //print_object("Estoy en settingsnode");
        
            $action = ['id'=>$cm->id, 'action' => 99];
            if(($group = optional_param('group', 0, PARAM_INT)) && ($group > 0)) {
                $action['group'] = $group;
            }
            $url = new moodle_url('/report/attendancetools/index.php', $action);
            $node = $settingsnode->create(get_string('autosession', 'report_attendancetools'), $url, navigation_node::TYPE_SETTING, null, 'autosession');
            $navigation->add_node($node, 'roleassign');
            
            $url = new moodle_url('https://www.milista.ulpgc.es/moodle.php', array(c=>'profesor', 'shortname'=>$PAGE->course->shortname));
            $navigation->add(get_string('milista', 'report_attendancetools'), $url, navigation_node::TYPE_SETTING, null, 'milista');
            
            $navigation->add(get_string('config', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsconfig', new pix_icon('i/config', ''));
            $node = $navigation->find('attendancetoolsconfig', navigation_node::TYPE_SETTING);
            $node->set_force_into_more_menu(true);             
            
    }  
      //print_object($navigation->get_children_key_list());

/*        
        $node = $navigation->add(get_string('contenttools', 'report_attendancetools'), null, navigation_node::TYPE_CONTAINER, null, 'attendancetoolsinout');
        
        if(has_capability('report/attendancetools:import', $context)) {
            $url->param('a', 'create');
            $node->add(get_string('create', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolssend', new pix_icon('i/cohort', ''));
            
            $url->param('a', 'import');
            $node->add(get_string('import', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsimport', new pix_icon('i/import', ''));
        }
        
        if(has_capability('report/attendancetools:export', $context)) {
            $url->param('a', 'export');
            $node->add(get_string('export', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsexport', new pix_icon('i/export', ''));
        }
        
        if(has_capability('report/attendancetools:download', $context)) {
            $url->param('a', 'download');
            $node->add(get_string('download', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsdownload', new pix_icon('t/download', ''));
        }
        
        $url->param('a', 'setfield');
        $node->add(get_string('setfield', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolssetfield', new pix_icon('t/editstring', ''));
        
        if(has_capability('report/attendancetools:bulkdelete', $context)) {
            $url->param('a', 'delissues');
            $node->add(get_string('delissues', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsdelissues', new pix_icon('t/delete', ''));
        }
        
        $rurl = new moodle_url(me());
        if(($rurl->get_param('view') == 'admin') && ($eid = $rurl->get_param('elementid'))  && ($rurl->get_param('what') == 'viewelementoptions')) {
            $rurl = new moodle_url('/report/attendancetools/loadfield.php', array('id'=>$cm->id, 'eid'=>$eid));
            $node->add(get_string('loadoptions', 'report_attendancetools'), $rurl, navigation_node::TYPE_SETTING, null, 'attendancetoolsloadfield', new pix_icon('i/withsubcat', ''));
        }

        $node = $navigation->add(get_string('checktools', 'report_attendancetools'), null, navigation_node::TYPE_CONTAINER, null, 'attendancetoolscheck');

        if(has_capability('report/attendancetools:report', $context)) {
            $url->param('a','comply');
            $node->add(get_string('comply', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsreport', new pix_icon('i/completion-manual-enabled', ''));
            
            $url->param('a','fieldcomply');
            $node->add(get_string('fieldcomply', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsfieldcomply', new pix_icon('i/completion-manual-enabled', ''));
            
            $url->param('a','usercomply');
            $node->add(get_string('usercomply', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsusercomply', new pix_icon('i/completion-manual-enabled', ''));
        }
        
        if(has_capability('report/attendancetools:warning', $context)) {
            $url->param('a', 'warning');
            $node->add(get_string('warning', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolswarning', new pix_icon('i/info', ''));
        }
//*        
        $url->param('a', 'assigntasktable');
        $node->add(get_string('assigntasktable', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsassigntasktable', new pix_icon('t/assignroles', ''));

        $url->param('a', 'deletetask');
        $node->add(get_string('deletetask', 'report_attendancetools'), clone $url, navigation_node::TYPE_SETTING, null, 'attendancetoolsdeletetask', new pix_icon('t/delete', ''));
       
*/

    }
}



