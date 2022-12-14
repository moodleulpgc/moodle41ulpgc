<?php
/**
 * user bulk action script for batch user enrolment
 */
require_once('../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/advuserbulk/lib.php');

$allcourses     = optional_param_array('allcourses', '', PARAM_INT); // ecastro ULPGC
$selcourses     = optional_param_array('selcourses', '', PARAM_INT);
$accept         = optional_param('accept', 0, PARAM_BOOL);
$confirm        = optional_param('confirm', 0, PARAM_BOOL);
$cancel         = optional_param('cancel', 0, PARAM_BOOL);
$searchtext     = optional_param('searchtext', '', PARAM_RAW);
$groups         = optional_param_array('groups', '', PARAM_INT);
$roleassign     = optional_param('roleassign', '', PARAM_RAW);
$showall        = optional_param('showall', 0, PARAM_BOOL);
$listadd        = optional_param('add', 0, PARAM_BOOL);
$listremove     = optional_param('remove', 0, PARAM_BOOL);
$removeall      = optional_param('removeall', 0, PARAM_BOOL);

admin_externalpage_setup('tooladvuserbulk');
check_action_capabilities('enroltocourses', true);

$return = $CFG->wwwroot . '/' . $CFG->admin . '/tool/advuserbulk/user_bulk.php';

if ($showall) {
    $searchtext = '';
}

$strsearch = get_string('search');
$pluginname = ACTIONS_LANG_PREFIX.'enroltocourses';

if (empty($SESSION->bulk_users) || $cancel) {
    redirect($return);
}

if (!isset($SESSION->bulk_courses) || $removeall) {
    $SESSION->bulk_courses = array();
}

// course selection add/remove actions
if ($listadd && !empty($allcourses)) {
    foreach ($allcourses as $courseid) {
        if (!in_array($courseid, $SESSION->bulk_courses)) {
            $SESSION->bulk_courses[] = $courseid;
        }
    }
}

if ($listremove && !empty($selcourses)) {
    foreach ($selcourses as $courseid) {
        unset($SESSION->bulk_courses[array_search($courseid, $SESSION->bulk_courses)]);
    }
}

// show the confirmation message
if ($accept) {
    global $DB;

    if (empty($SESSION->bulk_courses)) {
        redirect($return);
    }

    // generate user name list
    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $userlist = $DB->get_records_select_menu('user', "id $in", $params, 'fullname', 'id,' . $DB->sql_fullname() . ' AS fullname');
    //$usernames = implode('<br />', $userlist);
    // ecastro ULPGC
    $numusers = count($userlist);
    $numstr = advuserbulk_get_string('numusers', $pluginname, $numusers);
    if($numusers > 10 ) {
        $usernames = $numstr;
    } else {
        $usernames = implode('<br />', $userlist);
    }
    // ecastro ULPGC

    // generate course name list
    $courselist = array();
    $courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname');
    foreach ($courses as $course) {
        if (in_array($course->id, $SESSION->bulk_courses)) {
            $courselist[] = $course->fullname;
        }
    }

    // generate the message
    $confmsg = advuserbulk_get_string('confirmpart1', $pluginname) . '<b>' . $usernames . '</b>';
    $confmsg .= advuserbulk_get_string('confirmpart2', $pluginname);
    $confmsg .= '<b>' . implode('<br />', $courselist) . '</b>';
    if (!empty($groups)) {
        $confmsg .= advuserbulk_get_string('confirmpart3', $pluginname) . '<b>' . s(implode(', ', $groups), false) . '</b>';
    
        unset($SESSION->groups);
        foreach ($groups as $group) {
            $SESSION->groups[] = $group;
        }
    }

    // get system roles info and add the selected role to the message
    if ($roleassign != 0) {
        $role = $DB->get_record('role', array('id' => $roleassign));
        $rolename = $role->name;
    } else {
        $rolename = advuserbulk_get_string('default', $pluginname);
    }
    $confmsg .= advuserbulk_get_string('confirmpart4', $pluginname) . '<b>' . $rolename . '</b>?';

    $optionsyes['confirm'] = true;
    $optionsyes['roleassign'] = $roleassign;

    // print the message
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));

    $buttonyes = new single_button(new moodle_url('index.php', $optionsyes), get_string('yes'));
    $buttonno = new single_button(new moodle_url($return), get_string('no'), 'get');
    echo $OUTPUT->confirm($confmsg, $buttonyes, $buttonno);

    echo $OUTPUT->footer();
    die;
}

// action confirmed, perform it
if ($confirm) {
    require_once($CFG->dirroot . '/group/lib.php');

    if (empty($SESSION->bulk_courses)) {
        redirect($return);
    }

    // for each course, get the default role if needed and check the selected group
    foreach ($SESSION->bulk_courses as $courseid) {
        $groupids = array();
        if(isset($SESSION->groups) && is_array($SESSION->groups)) { // ecastro ULPGC avoid warning if not set
        foreach ($SESSION->groups as $group) {
            $groupid = groups_get_group_by_name($courseid, stripslashes($group));
            if ($groupid) {
                $groupids[] = $groupid;
            }
        }
        }

        $courseswithoutinternalenrol = array();
        if ($roleassign == 0) {
            if ($enrol = enrol_get_plugin('manual'))
                $roleassign = $enrol->get_config('roleid');
            else {
                $courseswithoutinternalenrol[] = $courseid;
                continue;
            }
        }

        foreach ($SESSION->bulk_users as $userid) {
            if (enrol_try_internal_enrol($courseid, $userid, $roleassign)) {
                foreach ($groupids as $groupid) {
                    try {
                        groups_add_member($groupid, $userid);
                    } catch(Exception $e) {
                        
                    }
                }
            }
            else  {
                $courseswithoutinternalenrol[] = $courseid;
            }
        }
    }

    if (count($courseswithoutinternalenrol)) {
        global $OUTPUT;

        echo $OUTPUT->header();
        html_writer::tag('p', advuserbulk_get_string('nointernalenrol', $pluginname, implode(', ', $courseswithoutinternalenrol)));
        echo $OUTPUT->continue_button($return);
        echo $OUTPUT->footer();
        die;
    }

    redirect($return, get_string('changessaved'));
}


// generate full and selected course lists
$coursenames =  advuserbulk_gen_course_list($searchtext, $SESSION->bulk_courses, true);
$selcoursenames =  advuserbulk_gen_course_list('', $SESSION->bulk_courses);

// generate the list of groups names from the selected courses.
// groups with the same name appear only once
$groupnames = array();
foreach ($SESSION->bulk_courses as $courseid) {
    $cgroups = groups_get_all_groups($courseid);
    foreach ($cgroups as $cgroup) {
        if (!in_array($cgroup->name, $groupnames)) {
            $groupnames[] = $cgroup->name;
        }
    }
}

sort($groupnames);

// generate html code for the group select control
foreach ($groupnames as $key => $name) {
    $groupnames[$key] = '<option value="' . s($name, true) . '" >' . s($name, true) . '</option>';
}

$groupnames = implode(' ', $groupnames);

$courseroles = get_roles_for_contextlevels(CONTEXT_COURSE);
$context = context_system::instance();
list($courseviewroles, $ignored) = get_roles_with_cap_in_context($context, 'moodle/course:view');
$enrolableroles = array_diff_key(array_combine($courseroles, $courseroles), $courseviewroles);
$roles = array_intersect_key(get_all_roles(), $enrolableroles);
//$roles[0] = (object) array('name' => advuserbulk_get_string('default', $pluginname));
$roles = role_fix_names($roles, null, ROLENAME_ORIGINAL, true); // ecastro ULPGC
$roles[0] = advuserbulk_get_string('default', $pluginname);

$rolenames = '';
foreach ($roles as $key => $role) {
    $rolenames .= '<option value="' . $key . '"';
    if ($key == $roleassign) {
        $rolenames .= ' selected ';
    }
    $rolenames .= '>' . $role . '</option> ';
}



// print the general page
echo $OUTPUT->header();
?>
<div id="addmembersform">
    <h3 class="main"><?php echo advuserbulk_get_string('title', $pluginname) ?></h3>

    <form id="addform" method="post" action="index.php">
        <table cellpadding="6" class="selectcourses generaltable generalbox boxaligncenter" summary="">
            <tr>
              <td id="existingcell">
                    <p>
                        <label for="allcourses"><?php echo advuserbulk_get_string('allcourses', $pluginname) ?></label>
                    </p>
                    <select name="allcourses[]" size="20" id="allcourses" multiple="multiple"
                            onfocus="document.getElementById('addform').add.disabled=false;
                                document.getElementById('addform').remove.disabled=true;
                                document.getElementById('addform').selcourses.selectedIndex=-1;"
                            onclick="this.focus();">
                                <?php echo $coursenames ?>
                    </select>

                    <br />
                    <label for="searchtext" class="accesshide"><?php p($strsearch) ?></label>
                    <input type="text" name="searchtext" id="searchtext" size="21" value="<?php p($searchtext, true) ?>"
                           onfocus ="getElementById('addform').add.disabled=true;
                               getElementById('addform').remove.disabled=true;
                               getElementById('addform').allcourses.selectedIndex=-1;
                               getElementById('addform').selcourses.selectedIndex=-1;"
                           onkeydown = "var keyCode = event.which ? event.which : event.keyCode;
                               if (keyCode == 13) {
                                   getElementById('addform').previoussearch.value=1;
                                   getElementById('addform').submit();
                               } " />
                    <input name="search" id="search" type="submit" value="<?php p($strsearch) ?>" />
                    <?php
                        if (!empty($searchtext)) {
                            echo '<br /><input name="showall" id="showall" type="submit" value="' . get_string('showall') . '" />' . "\n";
                        }
                    ?>
                </td>
              <td id="buttonscell">
                  <div id="addcontrols">
                        <input name="add" id="add" type="submit" disabled value="<?php echo '&nbsp;' . $OUTPUT->rarrow() . ' &nbsp; &nbsp; ' . get_string('add'); ?>" title="<?php print_string('add'); ?>" />
                  </div>
                  <div id="removecontrols">
                        <input name="remove" id="remove" type="submit" disabled value="<?php echo '&nbsp; ' . $OUTPUT->larrow() . ' &nbsp; &nbsp; ' . get_string('remove'); ?>" title="<?php print_string('remove'); ?>" />
                  </div>
                 </td>
          <td id="potentialcell">
                     <p>
                         <label for="selcourses"><?php echo advuserbulk_get_string('selectedcourses', $pluginname) ?></label>
                     </p>
                     <select name="selcourses[]" size="20" id="selcourses" multiple="multiple"
                             onfocus="document.getElementById('addform').remove.disabled=false;
                                      document.getElementById('addform').add.disabled=true;
                                      document.getElementById('addform').allcourses.selectedIndex=-1;"
                             onclick="this.focus();">
                            <?php echo $selcoursenames; ?>
                    </select>
                    <br />
                    <input name="removeall" id="removeall" type="submit" value="<?php echo get_string('removeall', 'bulkusers') ?>" />
                </td>
                </tr>
                <tr>
                    <td align="center">
                        <label for="roleassign"><?php echo advuserbulk_get_string('roletoset', $pluginname) ?></label>
                        <br />
                        <select name="roleassign" id="roleassign" size="1">
                        <?php echo $rolenames ?>
                                </select>
                    </td>
                    <td align="center">
                        <label for="groups"><?php echo advuserbulk_get_string('autogroup', $pluginname) ?></label>
                        <br />
                        <select name="groups[]" id="groups" size="10" multiple="multiple" >
                            <?php echo $groupnames; ?>
                        </select>
                    </td>
                </tr>
                <tr><td></td><td align="center">
                        <p><input type="submit" name="cancel" value="<?php echo get_string('cancel') ?>" />
                            <input type="submit" name="accept" value="<?php echo advuserbulk_get_string('accept', $pluginname) ?>" /></p>
                    </td>
                </tr>

        </table>
    </form>
</div>
<?php
    echo $OUTPUT->footer();
?>
