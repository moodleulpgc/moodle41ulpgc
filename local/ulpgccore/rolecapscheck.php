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
 * Change permissions.
 *
 * @package    core_role
 * @copyright  2009 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ulpgccore\check_role_permissions_table;

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$archetype = optional_param('arch', 'editingteacher', PARAM_ALPHA);
$skip = optional_param('skip', 1, PARAM_INT);

// Get the base URL for this and related pages into a convenient variable.
$baseurl = new moodle_url('/local/ulpgccore/rolecapscheck.php', array('arch'=>$archetype, 'skip' => $skip));

// setup page
admin_externalpage_setup('local_ulpgccore_rolecapscheck', '', null, $baseurl);

// Check access permissions.
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->add_body_class('rolecapscheck');
require_capability('local/ulpgccore:manage', $systemcontext);

$archetypes = get_role_archetypes();


// Handle confirmations and actions.
if($reset = optional_param('reset', 0, PARAM_INT)) {
    reset_role_capabilities($reset);
}

if($copyarch = optional_param('copy', 0, PARAM_INT)) {
    $capabilities = $systemcontext->get_capabilities();

    $rolecaps = $DB->get_records_menu('role_capabilities',
                                      ['roleid' => $copyarch, 'contextid' => $systemcontext->id],
                                      '', 'capability, permission');

    $sql = "SELECT rc.capability, rc.permission
              FROM {role_capabilities} rc
              JOIN {role} r ON r.id = rc.roleid
             WHERE r.shortname = :arch AND rc.contextid = :ctx ";
    $params = ['arch' => $archetype, 'ctx' => $systemcontext->id];
    $archcaps = $DB->get_records_sql_menu($sql, $params);

    foreach($capabilities as $cap) {
        $arch = isset($archcaps[$cap->name]) ? $archcaps[$cap->name] : '';
        $perm = isset($rolecaps[$cap->name]) ? $rolecaps[$cap->name] : '';

        if($perm != $arch) {
            if($arch !== '') {
                assign_capability($cap->name, (int)$arch, $copyarch, $systemcontext->id);
            } else {
                unassign_capability($cap->name, $copyarch, $systemcontext->id);
            }
        }
    }
}

if($cap = optional_param('cap', 0, PARAM_INT)) {
    $roles = get_archetype_roles($archetype);
    if($archetype == 'editingteacher') {
        $teacher = $DB->get_record('role', ['shortname' => 'teacher']);
        $roles[$teacher->id] = $teacher;
    }
    $roles = role_fix_names($roles, $systemcontext, ROLENAME_ORIGINAL);

    $sql = "SELECT rc.roleid, rc.permission, c.id AS capid, rc.capability
              FROM {role_capabilities} rc
              JOIN {capabilities} c ON rc.capability = c.name
             WHERE c.id = :capid  AND rc.contextid = :ctx ";
    $params = ['capid' => $cap, 'ctx' => $systemcontext->id];
    $caproles = $DB->get_records_sql($sql, $params);

    foreach($roles as $rid => $role) {
        if($role->shortname == $archetype) {
            $archroleid = $role->id;
            // removed archetype, to avoid mangled afterwards
            unset($roles[$rid]);
        }
    }

    foreach($roles as $roleid => $role) {
        if($roleid == $archroleid) {
            // do not change archetype
            continue;
        }
        $arch = isset($caproles[$archroleid]) ? $caproles[$archroleid]->permission: '';
        $perm = isset($caproles[$roleid]) ? $caproles[$roleid]->permission : '';

        if($perm != $arch) {
            if(!(empty($perm) && empty($arch))) {
                $capname = isset($caproles[$archroleid]) ? $caproles[$archroleid]->capability : '';
                if(!$capname) {
                    $capname = $caproles[$roleid]->capability;
                }
                assign_capability($capname, (int)$arch, $roleid, $systemcontext->id);
            }
        }
    }
}
//


$PAGE->set_navigation_overflow_state(false);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('rolecapscheck', 'local_ulpgccore'));

echo $OUTPUT->container_start('controls d-flex');
    $select = new single_select($baseurl, 'arch', $archetypes, $archetype);
    $select->label = get_string('archetype', 'core_role');
    $select->formid = 'selectarchetype';
    echo $OUTPUT->render($select);

    $options = [0 => get_string('skip0', 'local_ulpgccore'),
                1 => get_string('skip1', 'local_ulpgccore'),];
    $select = new single_select($baseurl, 'skip', $options, $skip);
    $select->label = get_string('skipcap', 'local_ulpgccore');
    $select->formid = 'selectskip';
    echo $OUTPUT->render($select);
echo $OUTPUT->container_end();

//$adminurl = new moodle_url('/admin/');
$arguments = array('contextid' => $systemcontext->id,
                'contextname' => $systemcontext->get_context_name(),
                'adminurl' => $baseurl->out());
$PAGE->requires->strings_for_js(
                                array('roleprohibitinfo', 'roleprohibitheader', 'roleallowinfo', 'roleallowheader',
                                    'confirmunassigntitle', 'confirmroleunprohibit', 'confirmroleprevent', 'confirmunassignyes',
                                    'confirmunassignno', 'deletexrole'), 'core_role');
$PAGE->requires->js_call_amd('core/permissionmanager', 'initialize', array($arguments));

$table = new check_role_permissions_table($systemcontext, $archetype, $baseurl, $skip);

if($table->has_derived_roles()) {
    echo $OUTPUT->box_start('generalbox capbox');
        $table->display();
    echo $OUTPUT->box_end();
} else {
     echo $OUTPUT->box(get_string('nothingtodisplay'), 'generalbox nothingtodisplay');
}

$returnurl = new moodle_url('/admin/search.php#linkmodules');
echo $OUTPUT->continue_button($returnurl);

echo $OUTPUT->footer();
