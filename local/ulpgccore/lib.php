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
 * ulpgccore lib
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


/**
 * Add editing nodes to flat navigation in Nav Drawer.
 *
 * @param global_navigation $navigation
 */
function local_ulpgccore_extend_navigation(global_navigation $navigation) {
    global $PAGE;

    //local_ulpgccore_boostnav_get_all_childrenkeys    
}

/**
 * Custom course navigation
 *
 * @param navigation_node $navigation
 */
function local_ulpgccore_extend_navigation_course(navigation_node $navigation) {
    global $PAGE;

    // make module administration accesible from module inner pages
    if(strpos($PAGE->pagetype, 'mod-') !== false) {
        $PAGE->force_settings_menu(true);
    }
    
}    
    
function local_ulpgccore_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $CFG, $PAGE;
    
    // modifications in course pages
    if ($PAGE->course && $PAGE->course->id != 1) {
        if($coursenode =  $nav->find('courseadmin', navigation_node::TYPE_COURSE)) {    
            //print_object($coursenode->get_children_key_list());
            //$url = new moodle_url('/report/log/index.php', array('chooselog' => 1,  'id'=>$PAGE->course->id));
            //$coursenode->add(get_string('pluginname', 'report_log'), $url, navigation_node::TYPE_SETTING, null, 'courselog', new pix_icon('i/report', ''));
            //$url = new moodle_url('/report/loglive/index.php', array('chooselog' => 1,  'id'=>$PAGE->course->id));
            //$coursenode->add(get_string('pluginname', 'report_loglive'), $url, navigation_node::TYPE_SETTING, null, 'courseloglive', new pix_icon('i/report', ''));

            $links = ['import', 'backup', 'restore', 'copy', 'reset', 'tool_recyclebin'];
            $name = get_string('archivereuse', 'local_ulpgccore');
            local_ulpgccore_regroup_nav_nodes($coursenode, $links, $name, 'course_archive_reuse');
        }
    }
    
   if ($settingsnode = $nav->find('users', navigation_node::TYPE_CONTAINER)) {
        $url = new moodle_url('/local/ulpgccore/exportusers.php', array('id'=>$PAGE->course->id));
        $node = $settingsnode->create(get_string('exportusers', 'local_ulpgccore'), $url, navigation_node::TYPE_SETTING, null, 'exportusers', new pix_icon('i/export', ''));
        $key = 'manageinstances';
        if(!in_array($key, $settingsnode->get_children_key_list())) {
            $key = null;
        }
        $settingsnode->add_node($node, $key);

        // re-name some items fotr Participants
        $settingsnode->title(get_string('participants'));
        $settingsnode->text = get_string('participants');
        if($n = $nav->find('review', navigation_node::TYPE_SETTING)) {
            $n->text = get_string('participants', 'local_ulpgccore');
        }        
   } 
   
    // modifications in module pages
    if ($PAGE->cm) {
        if($PAGE->cm->score && get_config('local_ulpgccore', 'enabledadminmods')) {
            if ($settingsnode = $nav->find('modedit', navigation_node::TYPE_SETTING)) {
                if(!has_capability('local/ulpgccore:modedit', $context)) {
                    $settingsnode->hide();
                }
            }
            if ($settingsnode = $nav->find('roleassign', navigation_node::TYPE_SETTING)) {
                if(!has_capability('local/ulpgccore:modroles', $context)) {
                    $settingsnode->hide();
                }
            }
            if ($settingsnode = $nav->find('roleoverride', navigation_node::TYPE_SETTING)) {
                if(!has_capability('local/ulpgccore:modpermissions', $context)) {
                    $settingsnode->hide();
                }
            }          
        }
        
        if($modnode =  $nav->find('modulesettings', navigation_node::TYPE_SETTING)) {    
            $url = new moodle_url('/report/log/index.php', array('chooselog' => 1,  'id'=>$PAGE->course->id, 'modid' => $PAGE->cm->id ));
            $modnode->add(get_string('pluginname', 'report_log'), $url, navigation_node::TYPE_SETTING, null, 'modulelogs', new pix_icon('i/report', ''));

            $links = ['roleoverride', 'roleassign', 'rolecheck'];
            $name = get_string('rolepermissions', 'local_ulpgccore');
            local_ulpgccore_regroup_nav_nodes($modnode, $links, $name, 'mod_roles_overrride');

            $links = ['import', 'backup', 'restore', 'copy', 'reset', 'tool_recycle'];
            $name = get_string('archivereuse', 'local_ulpgccore');
            local_ulpgccore_regroup_nav_nodes($modnode, $links, $name, 'mod_archive_reuse');

/*
            $siblibgs = $modnode->get_siblings();
            foreach($siblibgs as $n) {
                print_object($n->get_children_key_list());
                print_object(' SIB node coursesettings ' . $n->key);
            }

            local_ulpgccore_boostnav_get_all_childrenkeys($modnode);

            foreach($modnode->get_children_key_list() as $key) {
                $nod = $modnode->get($key);
                if($nod->has_children  ) {
                    print_object($nod->get_children_key_list());
                    print_object("Children for key: $key");
                }
            }
*/


        }
    }

    // always make this into more, or last positions 
    if ($node = $nav->find('contextlocking', navigation_node::TYPE_SETTING)) {
        $node->set_force_into_more_menu(true);
    }
    
    //print_object("Estoy en localulpgccore");    
    //print_object($PAGE->navigation->get_children_key_list());
    //print_object(local_ulpgccore_boostnav_get_all_childrenkeys($PAGE->navigation));
/*
    print_object("Estoy en localulpgccore");    
    print_object("Estoy en localulpgccore");
    print_object($PAGE->primarynav->get_children_key_list());
    print_object(local_ulpgccore_boostnav_get_all_childrenkeys($PAGE->primarynav));
    if(isset($PAGE->primarynav)) {
        print_object("Primary Navigation");    
        print_object($PAGE->primarynav->get_children_key_list());
        print_object("Primary Navigation");
    } else {print_object("NOT EXISTS  Primary Navigation");}
    
    print_object($PAGE->secondarynav->get_children_key_list());
    print_object(local_ulpgccore_boostnav_get_all_childrenkeys($PAGE->secondarynav));
    if(!empty($PAGE->secondarynav)) {
        print_object("Secondary Navigation");    
        print_object($PAGE->secondarynav->get_children_key_list());
        print_object("Secondary Navigation");        
    //local_ulpgccore_boostnav_get_all_childrenkeys        
    } else {print_object("NOT EXISTS  Secondary Navigation");}        
*/
  

}



/**
 * This function takes the plugin's custom nodes setting, builds the custom nodes and adds them to the given navigation_node.
 * Based on local_boostnavigation
 *
 * @param navigation_node $node the parent node containing the leafs to regroup
 * @param array $subnodes a list of node keys to search and place within new branch
 * @param string $name
 * @param string $key
 * @param string $beforekey
 * @return void
 */
function local_ulpgccore_regroup_nav_nodes(navigation_node $node, array $subnodes,
                                            $name, $key, $beforekey = null) {
    if($subnodes) {
        $newnode = navigation_node::create($name, null, navigation_node::TYPE_CONTAINER,
                                            null, $key);
        $newnode->action = null;
        $branch = $node->add_node($newnode, $beforekey);
        $branch->action = null;
        foreach($subnodes as $item) {
            if($n = $node->get($item)) {
                //$n->set_parent($branch);
                $branch->add_node(clone $n);
                $n->remove();
            }
        }
    }
}


/**
 * Moodle core does not have a built-in functionality to get all keys of all children of a navigation node,
 * so we need to get these ourselves.
 *
 * @param navigation_node $navigationnode
 * @return array
 */
function local_ulpgccore_boostnav_get_all_childrenkeys(navigation_node $navigationnode) {
    // Empty array to hold all children.
    $allchildren = array();

    // No, this node does not have children anymore.
    if (count($navigationnode->children) == 0) {
        return array();

        // Yes, this node has children.
    } else {
        // Get own own children keys.
        $childrennodeskeys = $navigationnode->get_children_key_list();
        // Get all children keys of our children recursively.
        foreach ($childrennodeskeys as $ck) {
            print_object("start key: $ck");
            $n = $navigationnode->get($ck);
            $ch = local_ulpgccore_boostnav_get_all_childrenkeys($navigationnode->get($ck));
            print_object($ch);
            print_object("key end: $ck  -- name {$n->text}  type: {$n->type} ---------------------------");
            $allchildren = array_merge($allchildren, $ch);
        }
        // And add our own children keys to the result.
        $allchildren = array_merge($allchildren, $childrennodeskeys);

        // Return everything.
        return $allchildren;
    }
}


/**
 * Gets an obtional global message to display in header
 *
 * @return string formatted message
 */
function local_ulpgccore_get_alert_message() { 
        global $COURSE, $OUTPUT, $PAGE, $USER;
        
        $startdate = get_config('local_ulpgccore', 'alertstart');
        $enddate = get_config('local_ulpgccore', 'alertend');
        $now = time();
        $startdate = $startdate ? strtotime($startdate) : 0;
        $enddate = $enddate ? strtotime($enddate) : 0;
        
        if(($startdate && $now <= $startdate) || ($enddate && $now > $enddate)) {
            return '';
        }

        // check applicable role
        if($targetroles = get_config('local_ulpgccore', 'alertroles')) {
            $targetroles = explode(',', $targetroles);
            $userroles = get_user_roles($PAGE->context, 0, false);
            $checkrole = false;
            foreach($targetroles as $roleid) {
                foreach($userroles as $role) {
                    if($role->roleid == $roleid) {
                        $checkrole = true;
                        break 2;
                    }
                }
            }
        } else {
            $checkrole = true;
        }
        
        if(!$checkrole) {
            return '';
        }
        $type = get_config('local_ulpgccore', 'alerttype');
        $icons = array('info'=>'info-circle',
                       'warning'=>'warning',
                       'success'=>'thumbs-up',
                       'danger'=>'exclamation-circle');
        $confirmbutton = get_config('local_ulpgccore', 'alertdismiss');
        
        if($confirmbutton && $confirmed = optional_param('dismissalert', 0, PARAM_INT)) {
            set_user_preference('user_read_globalmessage', 1); 
            return '';
        }
    
        $html = html_writer::start_tag('div', array('class' => 'globalmessage alert alert-'.$type));
        $dismiss = html_writer::tag('span', '&times;', array('aria-hidden'=>'true'));
        $html .= html_writer::tag('button', $dismiss, array('class' => 'close', 'type' => 'button',
                                                            'data-dismiss' => 'alert', 'aria-label' => 'Close'));
        $html .= html_writer::tag('i', null, array('class' => "fa fa-{$icons[$type]} fa-3x fa-pull-left"));
        $html .= get_config('local_ulpgccore', 'alertmessage');
        
        if($confirmbutton) {
            $url = clone $PAGE->url;
            $url->param('dismissalert', 1);
            $html .= html_writer::div($OUTPUT->single_button($url, get_string('dismissalert', 'local_ulpgccore')));
        }
        $html .= html_writer::end_tag('div');

        return $html;


}

/**
 * Gets an obtional global message to display in header
 *
 * @return string formatted message
 */
function local_ulpgccore_block_alert_message() { 
        global $COURSE, $OUTPUT, $PAGE, $USER;

        
        $type = get_config('local_ulpgccore', 'alerttype');
        $type = 'warning';
        $icons = array('info'=>'info-circle',
                       'warning'=>'warning',
                       'success'=>'thumbs-up',
                       'danger'=>'exclamation-circle');
        $confirmbutton = get_config('local_ulpgccore', 'alertdismiss');
        
        if($confirmbutton && $confirmed = optional_param('dismissalert', 0, PARAM_INT)) {
            set_user_preference('user_read_blockmessage', 1); 
            return '';
        }
    
        $html = html_writer::start_tag('div', array('class' => 'globalmessage alert alert-'.$type));
        $dismiss = html_writer::tag('span', '&times;', array('aria-hidden'=>'true'));
        $html .= html_writer::tag('button', $dismiss, array('class' => 'close', 'type' => 'button',
                                                            'data-dismiss' => 'alert', 'aria-label' => 'Close'));
        $html .= html_writer::tag('i', null, array('class' => "fa fa-{$icons[$type]} fa-3x fa-pull-left"));
        $html .= get_config('local_ulpgccore', 'blockmessage');

        if($confirmbutton) {
            $url = clone $PAGE->url;
            $url->param('dismissalert', 1);
            $html .= html_writer::div($OUTPUT->single_button($url, get_string('dismissalert', 'local_ulpgccore')));
        }

        $html .= html_writer::end_tag('div');

        return $html;        
}        


/**
 * Returns an array of user fields
 * @return stdclass course object with fields from ULPGC extra table
 */
function local_ulpgccore_get_userfields() {
    return array(
        'email'       => get_string('email'),
        'phone1'      => get_string('phone'),
        'phone2'      => get_string('phone2'),
        'url'      => get_string('webpage'),
        'icq'      => get_string('icqnumber'),
        'skype'      => get_string('skypeid'),
        'yahoo'      => get_string('yahooid'),
        'msn'      => get_string('msnid'),
        'department'  => get_string('department'),
        'institution' => get_string('institution'),
        'city' => get_string('city'),
        'address'      => get_string('address'),
        'aim' => get_string('aimid'),
        'country' => get_string('country'),
        'lang' => get_string('language'),
        'timezone'      => get_string('timezone'),
    );
}
    

/**
 * Checks if extra course details exist for given courses and add them to course object
 * @param int/object $course object or ID 
 * @return stdclass course object with fields from ULPGC extra table
 */
function local_ulpgccore_get_course_details($courseorid) {
    global $DB;
    
    if(is_int($courseorid) || ctype_digit($courseorid)) {
        $course = $DB->get_record('course', array('id'=>$courseorid), '*', MUST_EXIST);
    } else {
        $course = $courseorid;
    }
    if($extra = $DB->get_record('local_ulpgccore_course', array('courseid'=>$course->id))) {
        $extra = get_object_vars($extra);
    } else {
        $extra = $DB->get_columns('local_ulpgccore_course');
        $extra = array_fill_keys(array_keys($extra), null);
    }
    unset($extra['id']);

    foreach($extra as $field => $value) {
        $course->{$field} = $value; 
    }
    
    return $course;
}


/**
 * Checks if extra course details exist for given courses and add them to course object
 * @param int/object $course object or ID 
 * @return stdclass course object with fields from ULPGC extra table
 */
function local_ulpgccore_get_category_details($categoryorid) {
    global $DB;
    
    if(is_int($categoryorid) || ctype_digit($categoryorid)) {
        $category = $DB->get_record('course_categories', array('id'=>$categoryorid), '*', MUST_EXIST);
    } else {
        $category = $categoryorid;
    }
    if($extra = $DB->get_record('local_ulpgccore_categories', array('categoryid'=>$category->id))) {
        $extra = get_object_vars($extra);
    } else {
        $extra = $DB->get_columns('local_ulpgccore_categories');
        $extra = array_fill_keys(array_keys($extra), null);
    }
    unset($extra['id']);

    foreach($extra as $field => $value) {
        $category->{$field} = $value; 
    }
    
    return $category;
}

/**
 * Checks if extra course details exist for given courses and add them to course object
 * @param array $courseids an array of course IDs to select full objects
 * @param string $fields names of the fields we want to include in each returned object
 * @param string $sort valid ORDER statement for query
 * @return array Array of course objects with fields from ULPGC extra table
 */
function local_ulpgccore_load_courses_details($courseids, $fields, $sort= '') {
    global $DB;

    if(!$courseids) {
        return array();
    }
    
    if(!$sort) {
        $sort = 'sortorder ASC';
    }
    
    list($insql, $params) = $DB->get_in_or_equal($courseids);
    $sql = "SELECT $fields 
            FROM {course} c 
            LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid 
            WHERE c.id $insql 
            ORDER BY $sort ";
    return $DB->get_records_sql($sql, $params); 
}


/**
 * Checks if extra course details exist for given courses and add them to course object
 * @param array $catids an array of course category IDs to select full objects
 * @param string $fields names of the fields we want to include in each returned object
 * @param string $sort valid ORDER statement for query
 * @return array Array of course objects with fields from ULPGC extra table
 */
function local_ulpgccore_load_categories_details($catids, $fields, $sort = '') {
    global $DB;

    if(!$catids) {
        return array();
    }
    
    if(!$sort) {
        $sort = 'sortorder ASC';
    }
    
    list($insql, $params) = $DB->get_in_or_equal($catids);
    $sql = "SELECT $fields 
            FROM {course_categories} c 
            LEFT JOIN {local_ulpgccore_categories} uc ON c.id = uc.categoryid 
            WHERE c.id $insql 
            ORDER BY $sort ";
    return $DB->get_records_sql($sql, $params); 
}


function local_ulpgccore_restricted_icons(cm_info $mod, $renderer) {
    $modicons = '';
    $imgalt = '';
    if (!empty($mod->availableinfo)) {
        $formattedinfo = \core_availability\info::format_info(
            $mod->availableinfo, $mod->get_course());
        $imgalt = format_string($formattedinfo);
    }

    if(!$imgalt && has_capability('moodle/course:viewhiddenactivities', $mod->context)) {
        $imgalt = get_string('accessrestrictions', 'availability');
    }
    if($imgalt) {
        $lockedicon = new pix_icon('t/locked', $imgalt, '',
                array('title' => $imgalt));
        $modicons .= html_writer::tag('span', $renderer->render($lockedicon),
                array('class' => ' restriction'));
    }
    return $modicons;
}

function local_ulpgccore_custom_profile_form($mform) {

        $element = $mform->createElement('static', 'info1', '', get_string('userformwarning', 'local_ulpgccore'));
        $mform->insertElementBefore($element, 'moodle'); 
        $mform->setType('info1', PARAM_NOTAGS);
        unset($element);
    
        if ($mform->elementExists('url')) {
            $element =  $mform->createElement('static', 'userformpublic', '', get_string('userformpublic', 'local_ulpgccore'));
            $mform->insertElementBefore($element, 'url'); 
            $mform->setType('userformpublic', PARAM_NOTAGS);
            unset($element);
        }

        if ($mform->elementExists('idnumber')) {
            if ($mform->elementExists('phone1')) {
                $element = $mform->getElement('idnumber');
                $mform->removeElement('idnumber');
                $mform->insertElementBefore($element, 'phone1'); 
                unset($element);
            }

            $element =  $mform->createElement('static', 'userformhidden', '', get_string('userformhidden', 'local_ulpgccore'));
            $mform->insertElementBefore($element, 'idnumber'); 
            $mform->setType('userformhidden', PARAM_NOTAGS);
            unset($element);
        }

        if ($mform->elementExists('aim')) {
            if ($mform->elementExists('address')) {
                $element = $mform->getElement('aim');
                $mform->removeElement('aim');
                $mform->insertElementBefore($element, 'address'); 
                unset($element);
            }
        }

        $mform->addElement('static', 'info2', '', get_string('userformwarning', 'local_ulpgccore'));
        $mform->setType('info2', PARAM_NOTAGS);
}



/**
 * Get sql statement & params to find all relevant course users & data for exportation
 *
 * @uses $DB
 * @param int $courseid Course ID
 * @param object $context course context object
 * @param object $fromform data from user export form
 * @return array ($sql, $params, $columns) for use out
 */
function local_ulpgccore_exportuser_getsql($courseid, $context, $fromform) {
    global $DB;

    $columns = array('firstname' => get_string('firstname'), 
                        'lastname' => get_string('lastname'), 
                        'idnumber' => get_string('idnumber'));
    $userdetails = local_ulpgccore_get_userfields();
    foreach($userdetails as $field => $name) {
        if(isset($fromform->{$field}) && $fromform->{$field}) {
        $columns[$field] = $name;
        }
    }
    $userfields = implode(', u.', array_keys($columns));
    
    $usergroups = '';
    $params = array('courseid1' => $courseid, 'courseid2' => $courseid, 'ctxid'=>$context->id,
                'now1'=>time(), 'now2'=>time());

    $grouping = ($fromform->exportgroupsgrouping > 0) ? $fromform->exportgroupsgrouping : 0;           
    $coursegids = array_keys(groups_get_all_groups($courseid, 0, $grouping, 'g.id'));             
    if($fromform->exportgroupid || $fromform->exportgroupsgrouping) {
        $groupingwhere = '';
        if($fromform->exportgroupsgrouping) {
            if($fromform->exportgroupsgrouping < 0) {
                // non-grouping: select groups that are NOT members of any grouping
                $sql = "SELECT DISTINCT(g.id), g.id AS gid
                            FROM {groups} g
                            LEFT JOIN {groupings_groups} gg ON gg.groupid = g.id
                            WHERE g.courseid = ? AND gg.id IS NULL ";
                $coursegids = $DB->get_records_sql_menu($sql, array($courseid));
            }
            list($ingroupingsql, $inparams) = $DB->get_in_or_equal($coursegids, SQL_PARAMS_NAMED, 'g');
            $groupingwhere = " AND gm.groupid $ingroupingsql ";
            $params = array_merge($params, $inparams);
        }
       
        if($fromform->exportgroupid == -2) {
            $usergroups = " AND NOT EXISTS(SELECT 1
                                FROM {groups_members} gm 
                                JOIN {groups} g ON g.id = gm.groupid
                                WHERE g.courseid = er.courseid AND gm.userid = uer.userid )";
        } elseif($fromform->exportgroupid == -1) {
            $usergroups = " AND EXISTS(SELECT 1
                                FROM {groups_members} gm 
                                JOIN {groups} g ON g.id = gm.groupid
                                WHERE g.courseid = er.courseid AND gm.userid = uer.userid
                                $groupingwhere)";
        } elseif($fromform->exportgroupid > 0) {
            $usergroups = " AND EXISTS(SELECT 1
                                FROM {groups_members} gm 
                                WHERE gm.userid = uer.userid 
                                AND gm.groupid = :groupid 
                                $groupingwhere)";
            $params['groupid'] = $fromform->exportgroupid;
        } else {
            $usergroups = " AND EXISTS(SELECT 1
                                FROM {groups_members} gm 
                                WHERE gm.userid = uer.userid 
                                $groupingwhere)";
        }
    }

    $rolesfields = '';
    $groupsfields = '';
    $maxroles = 0;
    if(isset($fromform->exportincludeuserroles) && $fromform->exportincludeuserroles) {
        $rolesfields = ", (SELECT GROUP_CONCAT(DISTINCT rra.roleid  ORDER BY rra.roleid ASC SEPARATOR ',')
                        FROM {role_assignments} rra
                        WHERE rra.userid = uer.userid  AND rra.contextid = :rcontextid
                        GROUP BY rra.userid 
                        ORDER BY rra.roleid ) AS userroles";
        $params['rcontextid'] = $context->id;
        $maxroles = 0;

        $sql = "SELECT  uer.userid, COUNT(DISTINCT(ra.roleid)) AS rolecount
                FROM {user_enrolments} uer 
                JOIN {enrol} er ON uer.enrolid = er.id  AND er.status = 0 AND er.courseid = :courseid1 
                JOIN {role_assignments} ra ON ra.userid = uer.userid AND ra.contextid = :ctxid
                WHERE er.courseid = :courseid2 AND uer.status = 0 AND uer.timestart < :now1 AND (uer.timeend = 0 OR uer.timeend > :now2)
                    $usergroups 
                GROUP BY uer.userid
                ORDER BY rolecount DESC";
        if($maxroles = $DB->get_records_sql($sql, $params, 0, 1)) {
            $maxroles = reset($maxroles);
            $maxroles = $maxroles->rolecount;
        }
    }
    for($i = 1; $i <= $maxroles; $i++) {
        $columns['role'.$i] = get_string('role').$i;
    }

    $maxgroups = 0;
    if(isset($fromform->exportincludeusergroups) && $fromform->exportincludeusergroups) {
        $listgroupingjoin = '';
        if(isset($fromform->exportonlygrouping)) {
            if($fromform->exportonlygrouping == -1) {
                if($fromform->exportgroupsgrouping > 0) {
                    $fromform->exportonlygrouping = $fromform->exportgroupsgrouping;
                } else {
                    $fromform->exportonlygrouping = 0;
                }
            }
            if($fromform->exportonlygrouping) {
                $listgroupingjoin = "JOIN {groupings_groups} ugg ON ugg.groupid = ugm.groupid AND ugg.groupingid = :listgroupingid";
                $params['listgroupingid'] = $fromform->exportonlygrouping;
            }
        }
    
        $groupsfields = ", (SELECT GROUP_CONCAT(DISTINCT ug.name  ORDER BY ug.name ASC SEPARATOR ',')
                        FROM {groups_members} ugm 
                        JOIN {groups} ug ON ug.id = ugm.groupid
                        $listgroupingjoin
                        WHERE ugm.userid = uer.userid AND ug.courseid = er.courseid
                        GROUP BY ugm.userid 
                        ORDER BY ug.name) AS usergroups";
        $maxgroups = 0;
        
        
        if($fromform->exportonlygrouping) {
            $coursegids = array_keys(groups_get_all_groups($courseid, 0, $fromform->exportonlygrouping, 'g.id, g.idnumber')); 
        }
        list($ingroupsql, $inparams) = $DB->get_in_or_equal($coursegids, SQL_PARAMS_NAMED, 'gmg', true, 0);
        $sql = "SELECT uer.userid, COUNT(DISTINCT(ugm.groupid)) AS groupcount
                FROM {user_enrolments} uer 
                JOIN {enrol} er ON uer.enrolid = er.id  AND er.status = 0 AND er.courseid = :courseid1 
                JOIN {role_assignments} ra ON ra.userid = uer.userid AND ra.contextid = :ctxid
                LEFT JOIN {groups_members} ugm ON ugm.userid = uer.userid AND ugm.groupid $ingroupsql
                WHERE uer.status = 0 AND uer.timestart < :now1 AND (uer.timeend = 0 OR uer.timeend > :now2)
                    $usergroups 
                GROUP BY uer.userid, ra.roleid
                ORDER BY groupcount DESC";
        if($maxgroups = $DB->get_records_sql($sql, array_merge($params, $inparams), 0, 1)) {
            $maxgroups = reset($maxgroups);
            $maxgroups = $maxgroups->groupcount;
        }
    }
    for($i = 1; $i <= $maxgroups; $i++) {
        $columns['group'.$i] = get_string('group').$i;
    }

    list($inrolesql, $inparams) = $DB->get_in_or_equal($fromform->exportuserroles, SQL_PARAMS_NAMED, 'r');
    $params = array_merge($params, $inparams);
   

    $sortorder = array('lastname', 'firstname', 'idnumber');
    $key = array_search($fromform->exportsort, $sortorder);
    if($key!==false){
        unset($sortorder[$key]);
    }
    $sortorder = array($fromform->exportsort) + $sortorder;
    $sortorder = implode(' ASC, u.', $sortorder);
    
    $sql = "SELECT  DISTINCT(uer.userid), u.$userfields $rolesfields $groupsfields
            FROM {user_enrolments} uer 
            JOIN {enrol} er ON uer.enrolid = er.id  AND er.status = 0 AND er.courseid = :courseid1 
            JOIN {user} u ON u.id = uer.userid 
            JOIN {role_assignments} ra ON ra.userid = uer.userid AND ra.contextid = :ctxid
            WHERE er.courseid = :courseid2 AND uer.status = 0 AND uer.timestart < :now1 AND (uer.timeend = 0 OR uer.timeend > :now2) 
                    AND ra.roleid $inrolesql
                $usergroups
            ORDER BY u.$sortorder ASC "; 
    return array($sql, $params, $columns);
}

/**
 * Processes a user row from raw SQL to dataformat export format
 *
 * @uses $SESSION
 * @param object $row user data to export
 * @return object data for exportation
 */
function local_ulpgccore_exportuser_row($row) {
    global $SESSION;

    $columns = $SESSION->local_ulpgccore_export_columns;
    $rolenames = $SESSION->local_ulpgccore_role_names;

    if(isset($row->userroles) && $rolenames) {
        if($roles = explode(',', $row->userroles)) {
            $i = 1;
            $name = get_string('role');
            foreach($roles as $rid) {
                $col = 'role'.$i;    
                $row->{$col}  = $rolenames[$rid];
                $i++;
            }
        
        }
    }
    
    if(isset($row->usergroups)) {
        if($groups = explode(',', $row->usergroups)) {
            $i = 1;
            $name = get_string('group');
            foreach($groups as $gname) {
                $col = 'group'.$i;    
                $row->{$col}  = $gname;
                $i++;
            }
        }
    }

    $newrow = array();
    foreach($columns as $col) {
        if(isset($row->{$col})) {
            $newrow["$col"] = $row->{$col};
        } else {
            $newrow["$col"] = '';
        }
    }

    return $newrow;
}


/**
 * Allows to know if there is something changed, or un-attended activity (posts, assignments)
 *
 * @uses $CFG, $USER
 * @param object $course Course object we want to query
 * @param string $username the user to check activity for. '' defaults to $USER
 * @return bool true if activity exists
 */
function local_ulpgccore_course_recent_activity($course, $username = '' ) {
    global $CFG, $DB, $USER;

    if(!$username) {
        $user = $DB->get_record('user', ['username' => $username], 'id, idnumber, lastlogin, lastaccess');
        if(empty($user)) {
            return false;
        }
        // Load course enrolment related stuff.
        $user->lastcourseaccess    = array(); // During last session.
        if ($lastaccesses = $DB->get_records('user_lastaccess', array('userid' => $user->id))) {
            foreach ($lastaccesses as $lastaccess) {
                $user->lastcourseaccess[$lastaccess->courseid] = $lastaccess->timeaccess;
            }
        }
    } else {
        $user = $USER;
    }
    
    if(!isset($user->ulpgclastactivity)) {
        $user->ulpgclastactivity = array();
        $user->ulpgclastactivity[$course->id] = 0;
    }
    if(!isset($user->ulpgclastactivity[$course->id])) {
        $user->ulpgclastactivity[$course->id] = 0;
    }
    if(!isset($user->ulpgcrecentactivity)) {
        $user->ulpgcrecentactivity = array();
        $user->ulpgcrecentactivity[$course->id] = false;
    }
    if(!isset($user->ulpgcrecentactivity[$course->id])) {
        $user->ulpgcrecentactivity[$course->id] = false;
    }

    $now = time();
    $delay = 60;
    if(isset($user->ulpgcrecentactivity[$course->id]) && ($user->ulpgclastactivity[$course->id] > (time() - $delay))) {
        return $user->ulpgcrecentactivity[$course->id];
    }

    $context = context_course::instance($course->id);

    if(!is_enrolled($context, $user)) {
        $user->ulpgclastactivity[$course->id] = $now;
        $user->ulpgcrecentactivity[$course->id] = false;
        return false;
    }

    if(isset($user->lastlogin)) {
        $timestart = $user->lastlogin;
    } else {
        $timestart = time() - 172800;  // define('COURSE_MAX_RECENT_PERIOD', 172800);
    }
    if (!empty($user->lastcourseaccess[$course->id])) {
        if ($user->lastcourseaccess[$course->id] > $timestart) {
            $timestart = $user->lastcourseaccess[$course->id];
        }
    }

    $user->lastcourseaccess[$course->id] = time();

    /// TODO students test for changes in GRADES

    /// TODO teacher test for changes in GRADES

    $checkedmodules = array('assign', 'dialogue', 'forum', 'data', 'glossary', 'scheduler', 'quiz', 'tracker');

    $courseinfo = get_fast_modinfo($course);
    $mods = $courseinfo->get_cms();
    foreach($mods as $mod) {
        if (empty($mod->modname) || !in_array($mod->modname, $checkedmodules) || !$mod->uservisible) {
            continue;
        }
        $news = local_ulpgccore_mod_recent_activity($mod, $timestart, $user);
        if (!empty($news)) {
            $user->ulpgclastactivity[$course->id] = $now;
            $user->ulpgcrecentactivity[$course->id] = true;
            return $news;
        }
    }

    return false;
}


/**
 * Allows to know if there is something changed, or un-attended activity (posts, assignments)
 * much code is borrowed from print_recent_activity
 *
 * @uses $CFG, $USER, $QTYPES
 * @param coursemodule_info $cm course module
 * @param int $timestart timestamp to check activity after this time
 * @param object $user a $USER like object for a user to check, if empty, use $USER;
 *
  * @return midex bool false if NO activity detected or either true or string with url to see
 */

function local_ulpgccore_mod_recent_activity($cm, $timestart=0, $user = null) {
    global $CFG, $DB, $USER, $QTYPES;
  
    if(empty($user)) {
        $user = $USER;
    }
    
    
    $news = false;
    
    $course = $cm->get_course();
    $courseid = $course->id;
    $module = $cm->modname;

    if(!isset($user->ulpgcrecentmodactivity)) {
        $user->ulpgcrecentmodactivity = array();
        $user->ulpgcrecentmodactivity[$cm->id] = false;
    }
    if(!isset($user->ulpgcrecentmodactivity[$cm->id])) {
        $user->ulpgcrecentmodactivity[$cm->id] = false;
    }

    if(!isset($user->ulpgclastmodactivity)) {
        $user->ulpgclastmodactivity = array();
        $user->ulpgclastmodactivity[$cm->id] = 0;
    }
    if(!isset($user->ulpgclastmodactivity[$cm->id])) {
        $user->ulpgclastmodactivity[$cm->id] = false;
    }
    
    $delay = 60;
    $now = time();
    if(isset($user->ulpgcrecentmodactivity[$cm->id]) && ($user->ulpgclastmodactivity[$cm->id] > (time() - $delay))  ) {
        return $user->ulpgcrecentmodactivity[$cm->id];
    }

    if(!$timestart) {
        if(isset($user->lastlogin)) {
            $timestart = $user->lastlogin;
        } else {
            $timestart = time() - 172800;  // define('COURSE_MAX_RECENT_PERIOD', 172800);;
        }
        if (!empty($user->lastcourseaccess[$courseid])) {
            if ($user->lastcourseaccess[$courseid] > $timestart) {
                $timestart = $user->lastcourseaccess[$courseid];
            }
        }
    }

    if (!$cm->uservisible) {
        $user->ulpgclastmodactivity[$cm->id] = $now;
        $user->ulpgcrecentmodactivity[$cm->id] = false;
        return false;
    }

    $modcontext = $cm->context;

    // some shortcuts
    $userid = $user->id;

    $news = false;
    $url = '';
    $linktext = '';
    $actions = array();

    /// TODO
    /// TODO  ONLY SEARCH FOR ACTIVITY RELEVANT FOR $user->id
    /// TODO

    switch ($module) {
    case 'assign' :
        if($cangrade = has_capability('mod/assign:grade', $modcontext, $userid, false)) {
            $params = array();
            if($users = ulpgc_get_activity_users($cm, $modcontext, $userid, 'mod/assign:submit')) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            } else {
                break;
            }
            include_once($CFG->dirroot . '/mod/assign/locallib.php');
            $params['assignid'] = $cm->instance;
            $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
            $sql = "SELECT COUNT(s.userid)
                    FROM {assign_submission} s
                    JOIN {assign} a ON s.assignment = a.id AND a.grade != 0 
                    LEFT JOIN {assign_grades} g ON
                            s.assignment = g.assignment AND
                            s.userid = g.userid AND
                            g.attemptnumber = s.attemptnumber
                    WHERE   s.latest = 1 AND
                            s.assignment = :assignid AND
                            s.timemodified IS NOT NULL AND
                            s.status = :submitted AND
                            s.userid $insql AND
                            (s.timemodified > g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL OR g.grade < 0)";
            $news = $DB->count_records_sql($sql, $params);
            if($news) {
                $linktext = get_string('ungradedactivity', 'local_ulpgccore', $news);
                $actions = array('action'=>'grading');
            }
        }
        break;

    case 'dialogue' :
        include_once($CFG->dirroot . '/mod/dialogue/locallib.php');
        $news = dialogue_cm_unreplied_total($cm);
        break;

    case 'forum' :
        include_once($CFG->dirroot . '/mod/forum/lib.php');
        if(!$course) {
            $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
        }
        $news = forum_tp_count_forum_unread_posts($cm, $course);
        break;

    case 'scheduler' :
        $now = time();
        $params = array('scheduler'=>$cm->instance, 'now'=>$now);
        $sql = "SELECT COUNT(ss.id)
                FROM {scheduler_slots} ss
                JOIN {scheduler_appointment} sa ON ss.id = sa.slotid AND sa.slotid IS NOT NULL
                WHERE schedulerid = :scheduler  AND sa.attended = 0 AND ss.starttime >= :now " ;

        $teacher = '';
        $student = '';
        if($canattend = has_capability('mod/scheduler:attend', $modcontext, $userid, false)) {
            $teacher = " AND ss.teacherid = :teacher ";
            $params['teacher'] = $userid;
        } elseif($canappoint = has_capability('mod/scheduler:appoint', $modcontext, $userid, false)) {
            $student = " AND sa.studentid = :student ";
            $params['student'] = $userid;
        }

        $sql = "SELECT COUNT(ss.id)
                FROM {scheduler_slots} ss
                WHERE schedulerid = :scheduler  AND ss.starttime >= :now
                AND EXISTS (SELECT sa.slotid FROM {scheduler_appointment} sa
                            WHERE sa.slotid = ss.id AND sa.attended = 0 $student ) $teacher";

        $news = $DB->count_records_sql($sql, $params);
        break;

    case 'choice' :
        break;

    case 'data' :
        $canapprove = has_capability('mod/data:approve', $modcontext, $userid, false);
        if($canapprove) {
            $params = array();
            if($users = ulpgc_get_activity_users($cm, $modcontext, $userid, 'mod/data:writeentry')) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            } else {
                break;
            }
            $select = " dataid = :data AND approved = 0 AND userid $insql
                        AND timemodified > :start  AND userid <> :user ";
            $params['data'] = $cm->instance;
            $params['start'] = $timestart;
            $params['user'] = $userid;
            $news = $DB->count_records_select('data_records', $select, $params);
        }
        break;

    case 'glossary' :
        $canapprove = has_capability('mod/glossary:approve', $modcontext, $userid, false);
        if($canapprove) {
            $params = array();

            if($users = ulpgc_get_activity_users($cm, $modcontext, $userid, 'mod/glossary:write')) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            } else {
                break;
            }
            $select = " glossaryid = :glossary AND approved = 0 AND teacherentry = 0 AND userid $insql
                        AND timemodified > :start  AND userid <> :user ";
            $params['glossary'] = $cm->instance;
            $params['start'] = $timestart;
            $params['user'] = $userid;
            $news = $DB->count_records_select('glossary_entries', $select, $params);
        }
        break;

    case 'quiz' :
        if($cangrade = has_capability('mod/quiz:grade', $modcontext, $userid, false)) {
            if($users = ulpgc_get_activity_users($cm, $modcontext, $userid, 'mod/quiz:attempt')) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            } else {
                break;
            }
            $params['quiz'] = $cm->instance;
            $sql = "SELECT COUNT(qa.userid)
                    FROM {quiz_attempts} qa
                    WHERE qa.preview = 0 AND qa.state = 'finished' AND qa.sumgrades IS NULL
                        AND qa.quiz = :quiz
                        AND userid $insql ";
            $news = $DB->count_records_sql($sql, $params);
            if($news) {
                $linktext = get_string('ungradedactivity', 'local_ulpgccore', $news);
                $url = new moodle_url('/mod/'.$module.'/report.php', array('id'=>$cm->id, 'mode'=>'grading'));
            }
        }
        break;

    case 'wiki' :
        break;

    case 'tracker' :
        break;
        include_once($CFG->dirroot . '/mod/tracker/locallib.php');
        $now = time();
        $params = array('tracker'=>$cm->instance, 'status'=>RESOLVED, 'answered'=>TESTING,
                            'user1'=>$userid, 'user2'=>$userid, 'user3'=>$userid);

        $viewallclause = '';
        $cananswer = has_capability('mod/tracker:develop', $modcontext, $userid, false);

        if($cananswer) {
            $canviewall = has_capability('mod/tracker:viewallissues', $modcontext, $userid, false);
            if($canviewall) {
                $viewallclause = " OR ( i.assignedto = 0  ) ";
            }
            $select = "( ((i.reportedby = :user1)  OR (i.assignedto = :user3 )  $viewallclause )
                        AND ((i.usermodified >= i.resolvermodified) AND (i.userlastseen <= i.usermodified)) )";

        } else {
            $select = "(((i.reportedby = :user1)  OR (i.assignedto = :user3))
                         AND ((i.resolvermodified > i.userlastseen) OR (i.status = :answered)) )";
        }

        $sql = "SELECT COUNT(i.id)
                FROM {tracker_issue} i
                WHERE
                    $select
                    AND i.status < :status AND i.trackerid = :tracker  ";

        $news = $DB->count_records_sql($sql, $params);
        break;
    }

    $user->ulpgcrecentmodactivity[$cm->id] = false;

    if($news) {
        if(!$url) {
            $url = new moodle_url('/mod/'.$module.'/view.php', array('id'=>$cm->id)+$actions);
        }
        if(!$linktext) {
            $linktext = get_string('newactivity', 'local_ulpgccore', $news);
        }
        $link = html_writer::link($url, $linktext);
        $news = '&nbsp;' . html_writer::tag('span', $link, array('class' => 'unread'));
        $user->ulpgcrecentmodactivity[$cm->id] = $news;
        $user->ulpgclastmodactivity[$cm->id] = $now;
    }
    return $news;
}




/**
 * 
 *
 * @uses $CFG, $USER, $QTYPES
 * @param object $user user object
 * @param string $message additional string to include in LDAP update comments
 */

function ulpgc_get_activity_users($cm, $modcontext, $userid, $capability) {

    $canviewall = has_capability('moodle/site:accessallgroups', $modcontext, $userid, false);
    $groups = array();
    if(!$canviewall) {
        if($groups = groups_get_activity_allowed_groups($cm,$userid)) {
            $groups = array_keys($groups);
        }
    } else {
        $groups = array(0);
    }
    $users = array();
    foreach($groups as $group) {
        $users = $users + get_enrolled_users($modcontext, $capability, $group, 'u.id, u.idnumber');
    }
    if($users) {
        $users = array_unique(array_keys($users));
    }

    return $users;
}



/**
 * Allows to update ULPGC's LDAP with data forwarded from moodle into LDAP
 *
 * @uses $CFG, $USER, $QTYPES
 * @param object $user user object
 * @param string $message additional string to include in LDAP update comments
 */

function local_ulpgccore_update_ldap($user, $message = '') {
    global $CFG, $DB, $USER, $QTYPES;

    $config = get_config('local_ulpgccore');
    if(!$message) {
        $message = 'Alta desde Campus virtual';
    }
    
    if(isset($config->enableupdateldap) && $config->enableupdateldap) {
        try {
            $cliente = new SoapClient ( null, array (
                    'location' => 'https://webservices.ulpgc.es/',
                    'uri' => 'urn:LDAPwsdl'
            ) );
            $parametros = array (
                    "usuario" => $user->username,
                    "rama" => "Todos",
                    "inicio" => 0
            );
            $resultado = $cliente->__soapCall ( 'BusquedaUsuariosActivos', $parametros );
        } catch ( SoapFault $fault ) {
            trigger_error ( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR );
        }
        if ($resultado -> TotalResult == '0') {
            $usuario['dni'] = $user->username;
            $usuario['credencial'] = $user->username;
            $usuario['nombre'] =$user->firstname;
            $usuario['apellidos'] = $user->lastname;
            $usuario['mail'] = $user->email;
            $usuario['rama'] = "Externos";
            $usuario['comentario'] = "$message, plataforma $CFG->plataforma";
            try {
                $parametros = array (
                        "usuario" => $usuario,
                        'binddn' => 'cn=PermisosAdmin,dc=ulpgc,dc=es',
                        'bindpw' => 'ucppmb,aeylf'
                );
                $crea_usuario = $cliente->__soapCall ( "AltaUsuario", $parametros );
            } catch ( SoapFault $fault ) {
                trigger_error ( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR );
            }
        }
    }
}


/**
 * Makes shorter titles & descriptions of courses & categories
 *
 * Uses langs strings with several lines, each containing search & replace items separated by commas
 *  e.g.  Master,M.
 *
 *
 * @param string $text text to be shorten
 * @param array lang strings containing de 
 * @return string text shorten
 */

function local_ulpgccore_shorten_titles($text, $replaces = array('shortenitems')) {

    $lines = array();
    foreach($replaces as $tokens) {
        $tokens = get_string($tokens, 'local_ulpgccore');
        $tokens = preg_split("/((\r?\n)|(\r\n?))/", $tokens);
        $lines = array_merge($lines, $tokens);
    }
    $search = array();    
    $replace = array();    
    foreach($lines as $line) {
        $searchreplace = explode(',', $line);
        if(count($searchreplace) == 2) {
            $search[] = $searchreplace[0];    
            $replace[] = $searchreplace[1];    
        }
    } 
    
    $text = str_replace($search, $replace, $text);

    return shorten_text($text, 50);
}

function local_ulpgccore_render_navbar_output(\renderer_base $renderer) {
    global $USER; 
    
    $output = '';
    
    // Add the notifications popover.
    $enabled = \core_message\api::is_processor_enabled("popup");
    if ($enabled) {
        $unreadcount = \message_popup\api::count_unread_popup_notifications($USER->id);
        $items = \message_popup\api::get_popup_notifications($USER->id);
        $caneditownmessageprofile = has_capability('moodle/user:editownmessageprofile', context_system::instance());
        $preferencesurl = $caneditownmessageprofile ? new moodle_url('/message/notificationpreferences.php') : null;

        $context = [
            'userid' => $USER->id,
            'unreadcount' => $unreadcount,
            'urls' => [
                'seeall' => (new moodle_url('/message/output/popup/notifications.php'))->out(),
                'preferences' => $preferencesurl ? $preferencesurl->out() : null,
            ],
            'items' => $items,
        ];
        $output .= $renderer->render_from_template('local_ulpgccore/notification_popover', $context);
    }    
    
    return $output; 
}
