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
 * Print an overview of groupings & group membership
 *
 * @copyright  Matt Clarkson mattc@catalyst.net.nz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package    core_group
 */

require_once('../config.php');
require_once($CFG->libdir . '/filelib.php');

define('OVERVIEW_NO_GROUP', -1); // The fake group for users not in a group.
define('OVERVIEW_GROUPING_GROUP_NO_GROUPING', -1); // The fake grouping for groups that have no grouping.
define('OVERVIEW_GROUPING_NO_GROUP', -2); // The fake grouping for users with no group.

$courseid   = required_param('id', PARAM_INT);
$groupid    = optional_param('group', 0, PARAM_INT);
$groupingid = optional_param('grouping', 0, PARAM_INT);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);


$returnurl = $CFG->wwwroot.'/group/index.php?id='.$courseid;
$rooturl   = $CFG->wwwroot.'/group/overview.php?id='.$courseid;

if($ulpgcgroups = get_config('local_ulpgcgroups', 'enabledadvancedgroups')) {  // ecastro ULPGC customize groups page
    $userorder = optional_param('order', 1, PARAM_INT); 
    $exclusive = optional_param('exclusive', 0, PARAM_INT);  
    $ulpgcparams = array('order'=>$userorder, 'exclusive'=>$exclusive);
    $rooturl = new moodle_url($rooturl, $ulpgcparams);
    $rooturl = $rooturl->out(); 
    $nameformat = '';
    if(!$userorder) {
        $nameformat = 'firstname';
    } else {
        $nameformat = 'lastname';
    }
    $SESSION->nameformat = $nameformat;
    
}
$onlyactive = $ulpgcgroups && get_config('local_ulpgcgroups', 'onlyactiveenrolments');

if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
    throw new \moodle_exception('invalidcourse');
}

$url = new moodle_url('/group/overview.php', array('id'=>$courseid));
if ($groupid !== 0) {
    $url->param('group', $groupid);
}
if ($groupingid !== 0) {
    $url->param('grouping', $groupingid);
}
$PAGE->set_url($url);

// Make sure that the user has permissions to manage groups.
require_login($course);

$context = context_course::instance($courseid);
require_capability('moodle/course:managegroups', $context);

$strgroups           = get_string('groups');
$strparticipants     = get_string('participants');
$stroverview         = get_string('overview', 'group');
$strgrouping         = get_string('grouping', 'group');
$strgroup            = get_string('group', 'group');
$strnotingrouping    = get_string('notingrouping', 'group');
$strfiltergroups     = get_string('filtergroups', 'group');
$strnogroups         = get_string('nogroups', 'group');
$strdescription      = get_string('description');
$strnotingroup       = get_string('notingrouplist', 'group');
$strnogroup          = get_string('nogroup', 'group');
$strnogrouping       = get_string('nogrouping', 'group');

// This can show all users and all groups in a course.
// This is lots of data so allow this script more resources.
raise_memory_limit(MEMORY_EXTRA);

// Get all groupings and sort them by formatted name.
$groupings = $DB->get_records('groupings', array('courseid'=>$courseid), 'name');
foreach ($groupings as $gid => $grouping) {
    $groupings[$gid]->formattedname = format_string($grouping->name, true, array('context' => $context));
}
core_collator::asort_objects_by_property($groupings, 'formattedname');
$members = array();
$grouped_users = array(); // ecastro ULPGC
foreach ($groupings as $grouping) {
    $members[$grouping->id] = array();
}
// Groups not in a grouping.
$members[OVERVIEW_GROUPING_GROUP_NO_GROUPING] = array();

$userfieldsapi = \core_user\fields::for_name();
$allnames = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
$course_users = get_enrolled_users($context, '', 0, 'u.id, u.username, u.idnumber,  '.$allnames, 'lastname ASC', '', '', $onlyactive); // ecastro ULPGC
$courseusersids = array_keys($course_users); // ecastro ULPGC

// Get all groups and sort them by formatted name.
$groups = $DB->get_records('groups', array('courseid'=>$courseid), 'name');
foreach ($groups as $id => $group) {
    $groups[$id]->formattedname = format_string($group->name, true, ['context' => $context]);
}
core_collator::asort_objects_by_property($groups, 'formattedname');

$params = array('courseid'=>$courseid);
if ($groupid) {
    $groupwhere = "AND g.id = :groupid";
    $params['groupid']   = $groupid;
} else {
    $groupwhere = "";
}

if ($groupingid) {
    if ($groupingid < 0) { // No grouping filter.
        $groupingwhere = "AND gg.groupingid IS NULL";
    } else {
        $groupingwhere = "AND gg.groupingid = :groupingid";
        $params['groupingid'] = $groupingid;
    }
} else {
    $groupingwhere = "";
}

list($sort, $sortparams) = users_order_by_sql('u');

$userfieldsapi = \core_user\fields::for_identity($context)->with_userpic();
[
    'selects' => $userfieldsselects,
    'joins' => $userfieldsjoin,
    'params' => $userfieldsparams
] = (array)$userfieldsapi->get_sql('u', true);
$extrafields = $userfieldsapi->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]);
$allnames = 'u.id ' . $userfieldsselects;

$sql = "SELECT g.id AS groupid, gg.groupingid, u.id AS userid, $allnames, u.idnumber, u.username
          FROM {groups} g
               LEFT JOIN {groupings_groups} gg ON g.id = gg.groupid
               LEFT JOIN {groups_members} gm ON g.id = gm.groupid
               LEFT JOIN {user} u ON gm.userid = u.id
               $userfieldsjoin
         WHERE g.courseid = :courseid $groupwhere $groupingwhere
      ORDER BY g.name, $sort";

$rs = $DB->get_recordset_sql($sql, array_merge($params, $sortparams, $userfieldsparams));
foreach ($rs as $row) {
    $user = username_load_fields_from_object((object) [], $row, null,
        array_merge(['id' => 'userid', 'username', 'idnumber'], $extrafields));

    if (!$row->groupingid) {
        $row->groupingid = OVERVIEW_GROUPING_GROUP_NO_GROUPING;
    }
    if (!array_key_exists($row->groupid, $members[$row->groupingid])) {
        $members[$row->groupingid][$row->groupid] = array();
    }
    if (!empty($user->id) && (!$onlyactive  || in_array($user->id, $courseusersids))) { // ecastro ULPGC
        $members[$row->groupingid][$row->groupid][] = $user;
        $grouped_users[$user->id] = $user->id; // ecastro ULPGC
    }
}
$rs->close();

// Add 'no groupings' / 'no groups' selectors.
$groupings[OVERVIEW_GROUPING_GROUP_NO_GROUPING] = (object)array(
    'id' => OVERVIEW_GROUPING_GROUP_NO_GROUPING,
    'formattedname' => $strnogrouping,
);
$groups[OVERVIEW_NO_GROUP] = (object)array(
    'id' => OVERVIEW_NO_GROUP,
    'courseid' => $courseid,
    'idnumber' => '',
    'name' => $strnogroup,
    'formattedname' => $strnogroup,
    'description' => '',
    'descriptionformat' => FORMAT_HTML,
    'enrolmentkey' => '',
    'picture' => 0,
    'timecreated' => 0,
    'timemodified' => 0,
);

// Add users who are not in a group.
if ($groupid <= 0 && $groupingid <= 0) {
    list($esql, $params) = get_enrolled_sql($context, null, 0, true);
    $sql = "SELECT u.id, $allnames, u.idnumber, u.username
              FROM {user} u
              JOIN ($esql) e ON e.id = u.id
         LEFT JOIN (
                  SELECT gm.userid
                    FROM {groups_members} gm
                    JOIN {groups} g ON g.id = gm.groupid
                   WHERE g.courseid = :courseid
                   ) grouped ON grouped.userid = u.id
                  $userfieldsjoin
             WHERE grouped.userid IS NULL
             ORDER BY $sort";
    $params['courseid'] = $courseid;

    $nogroupusers = $DB->get_records_sql($sql, array_merge($params, $userfieldsparams));

    if ($nogroupusers) {
        $members[OVERVIEW_GROUPING_NO_GROUP][OVERVIEW_NO_GROUP] = $nogroupusers;
    }
}

// Export groups if requested.
if ($dataformat !== '') {
    $columnnames = array(
        'grouping' => $strgrouping,
        'group' => $strgroup,
        'firstname' => get_string('firstname'),
        'lastname' => get_string('lastname'),
    );
    $extrafields = \core_user\fields::get_identity_fields($context, false);
    foreach ($extrafields as $field) {
        $columnnames[$field] = \core_user\fields::get_display_name($field);
    }
    $alldata = array();
    // Generate file name.
    $shortname = format_string($course->shortname, true, array('context' => $context))."_groups";
    $i = 0;
    foreach ($members as $gpgid => $groupdata) {
        if ($groupingid and $groupingid != $gpgid) {
            if ($groupingid > 0 || $gpgid > 0) {
                // Still show 'not in group' when 'no grouping' selected.
                continue; // Do not export.
            }
        }
        if ($gpgid < 0) {
            // Display 'not in group' for grouping id == OVERVIEW_GROUPING_NO_GROUP.
            if ($gpgid == OVERVIEW_GROUPING_NO_GROUP) {
                $groupingname = $strnotingroup;
            } else {
                $groupingname = $strnotingrouping;
            }
        } else {
            $groupingname = $groupings[$gpgid]->formattedname;
        }
        if (empty($groupdata)) {
            $alldata[$i] = array_fill_keys(array_keys($columnnames), '');
            $alldata[$i]['grouping'] = $groupingname;
            $i++;
        }
        foreach ($groupdata as $gpid => $users) {
            if ($groupid and $groupid != $gpid) {
                continue;
            }
            if (empty($users)) {
                $alldata[$i] = array_fill_keys(array_keys($columnnames), '');
                $alldata[$i]['grouping'] = $groupingname;
                $alldata[$i]['group'] = $groups[$gpid]->formattedname;
                $i++;
            }
            foreach ($users as $option => $user) {
                $alldata[$i]['grouping'] = $groupingname;
                $alldata[$i]['group'] = $groups[$gpid]->formattedname;
                $alldata[$i]['firstname'] = $user->firstname;
                $alldata[$i]['lastname'] = $user->lastname;
                foreach ($extrafields as $field) {
                    $alldata[$i][$field] = $user->$field;
                }
                $i++;
            }
        }
    }

    \core\dataformat::download_data(
        $shortname,
        $dataformat,
        $columnnames,
        $alldata,
        function($record, $supportshtml) use ($extrafields) {
            if ($supportshtml) {
                foreach ($extrafields as $extrafield) {
                    $record[$extrafield] = s($record[$extrafield]);
                }
            }
            return $record;
        });
    die;
}

// Main page content.
navigation_node::override_active_url(new moodle_url('/group/index.php', array('id'=>$courseid)));
$PAGE->navbar->add(get_string('overview', 'group'));

/// Print header
$PAGE->set_title($strgroups);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();

echo $OUTPUT->render_participants_tertiary_nav($course);
/// Print overview
echo $OUTPUT->heading(format_string($course->shortname, true, array('context' => $context)) .' '.$stroverview, 3);

echo $strfiltergroups;

$options = array();
$options[0] = get_string('all');
foreach ($groupings as $grouping) {
    $options[$grouping->id] = strip_tags($grouping->formattedname);
}
$popupurl = new moodle_url($rooturl.'&group='.$groupid);
$select = new single_select($popupurl, 'grouping', $options, $groupingid, array());
$select->label = $strgrouping;
$select->formid = 'selectgrouping';
echo $OUTPUT->render($select);

$options = array();
$options[0] = get_string('all');
foreach ($groups as $group) {
    $options[$group->id] = $group->formattedname;
}
$popupurl = new moodle_url($rooturl.'&grouping='.$groupingid);
$select = new single_select($popupurl, 'group', $options, $groupid, array());
$select->label = $strgroup;
$select->formid = 'selectgroup';
echo $OUTPUT->render($select);

if($ulpgcgroups) {
    $popupurl = new moodle_url($rooturl);
    echo local_ulpgcgroups_nameformat_menu($popupurl);
    echo local_ulpgcgroups_exclusive_menu($popupurl);
}

/// Print table
$printed = false;
foreach ($members as $gpgid=>$groupdata) {
    if ($groupingid and $groupingid != $gpgid) {
        if ($groupingid > 0 || $gpgid > 0) { // Still show 'not in group' when 'no grouping' selected.
            continue; // Do not show.
        }
    }
    $table = new html_table();
    $table->head  = array(get_string('groupscount', 'group', count($groupdata)), get_string('groupmembers', 'group'), get_string('usercount', 'group'));
    $table->size  = array('20%', '70%', '10%');
    $table->align = array('left', 'left', 'center');
    $table->width = '90%';
    $table->data  = array();
    $grouping_members = array(); // ecastro ULPGC
    foreach ($groupdata as $gpid=>$users) {
        if ($groupid and $groupid != $gpid) {
            continue;
        }
        $line = array();
        $name = print_group_picture($groups[$gpid], $course->id, false, true, false) . $groups[$gpid]->formattedname;
        $description = file_rewrite_pluginfile_urls($groups[$gpid]->description, 'pluginfile.php', $context->id, 'group', 'description', $gpid);
        $options = new stdClass;
        $options->noclean = true;
        $options->overflowdiv = true;
        $line[] = $name;
        $viewfullnames = has_capability('moodle/site:viewfullnames', $context);
        $fullnames = array();
        /*
        foreach ($users as $user) {
            $displayname = fullname($user, $viewfullnames);
            if ($extrafields) {
                $extrafieldsdisplay = [];
                foreach ($extrafields as $field) {
                    $extrafieldsdisplay[] = s($user->{$field});
                }
                $displayname .= ' (' . implode(', ', $extrafieldsdisplay) . ')';
            }

            $fullnames[] = html_writer::link(new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]),
                $displayname);
        }
        $line[] = implode(', ', $fullnames);
        $line[] = count($users);
        */
        $doubled = false;
        $count = 0;
        foreach ($users as $user) {
            if(!$user->id) {
                continue;
            }
            $doubled = false;
            if($exclusive) {
                $sql = "SELECT COUNT( * )
                            FROM {groups_members} gm
                            JOIN {groupings_groups} gg ON gm.groupid = gg.groupid
                        WHERE  gg.groupingid = :groupingid AND userid = :userid ";
                $params = array('groupingid'=>$gpgid, 'userid'=>$user->id);
                $memberships = $DB->count_records_sql($sql, $params);
                if($memberships > 1) {
                    $doubled = true;
                }
            }
            $fullname = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id.'">'.fullname($user, true).'</a>';
            if($doubled) {
                $fullname = '<span style="background-color:pink;" >'.$fullname.'</span>';
            }
            $fullnames[] = $fullname; 
            $grouping_members[$user->id] = $user;
            $count += 1;
        }
        $line[] = implode('; ', $fullnames); // ecastro to avoid confusion with names in lastname, firstname format
        $line[] = $count;



        $table->data[] = $line;
    }

    // add users that are NOT members of any grouo in grouping // ecastro ULPGC
    $notingrouping = false;
    if($gpgid > 0 && $groupingid) {
        //$gouping_members = groups_get_grouping_members($gpgid, 'u.id,u.username', $sort='lastname ASC')
        if($grouping_members) {
            $notingrouping = array_diff_key($course_users, $grouping_members);
        }
    } else {
        $notingrouping = '';
    }
    if($notingrouping) {
        $fullnames = array();
        foreach($notingrouping as $user) {
            $fullnames[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id.'">'.fullname($user, true).'</a>';
        }
        $line = array(get_string('nogroupsassigned', 'group'), implode('; ', $fullnames), count($fullnames));
        $table->data[] = $line;
    }

    if ($groupid and empty($table->data)) {
        continue;
    }
    if ($gpgid < 0) {
        // Display 'not in group' for grouping id == OVERVIEW_GROUPING_NO_GROUP.
        if ($gpgid == OVERVIEW_GROUPING_NO_GROUP) {
            echo $OUTPUT->heading($strnotingroup, 3);
        } else {
            echo $OUTPUT->heading($strnotingrouping, 3);
        }
    } else {
        echo $OUTPUT->heading($groupings[$gpgid]->formattedname, 3);
        $description = file_rewrite_pluginfile_urls($groupings[$gpgid]->description, 'pluginfile.php', $context->id, 'grouping', 'description', $gpgid);
        $options = new stdClass;
        $options->overflowdiv = true;
        echo $OUTPUT->box(format_text($description, $groupings[$gpgid]->descriptionformat, $options), 'generalbox boxwidthnarrow boxaligncenter');
    }
    echo html_writer::table($table);
    $printed = true;

    if ($gpgid>0 &&  $exclusive){ // ecastro ULPGC

        $conflictgroups = local_ulpgcgroups_multiplegroups_grouping($courseid, $gpgid);
        if($conflictgroups) {
            $list = '';
            foreach($conflictgroups as $g) {
                $list .= $g->name.', ';
            }
            echo '<p>';
            echo '&nbsp;&nbsp;'.get_string('exclusivegroupingconflict', 'local_ulpgcgroups', $list).'&nbsp';
            //helpbutton('exclusivegroupingconflict', get_string('exclusivegroupingconflict', 'group'), 'group');
            echo '</p>';
        }
    }
}


if(!$groupingid && !$groupid) { // ecastro ULPGC
    $notingroup = array();
    if($notingroup = array_diff_key($course_users, $grouped_users)) {
        $table = new html_table();
        $table->head  = array('', get_string('users'), get_string('usercount', 'group'));
        $table->size  = array('20%', '70%', '10%');
        $table->align = array('left', 'left', 'center');
        $table->width = '90%';
        $table->data  = array();
        foreach($notingroup as $user) {
            $fullnames[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id.'">'.fullname($user, true).'</a>';
        }
        $line = array(get_string('nogroupsassigned', 'group'), implode('; ', $fullnames), count($fullnames));
        $table->data[] = $line;
        echo $OUTPUT->heading(get_string('nogroupusers', 'local_ulpgcgroups'), 3);
        echo html_writer::table($table);
    }
}

// Add buttons for exporting groups/groupings.
echo $OUTPUT->download_dataformat_selector(get_string('exportgroupsgroupings', 'group'), 'overview.php', 'dataformat', [
    'id' => $courseid,
    'group' => $groupid,
    'grouping' => $groupingid,
]);

$SESSION->nameformat = ''; // ecastro ULPGC remove naming format

echo $OUTPUT->footer();
