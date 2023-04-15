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

$action = optional_param('action', '', PARAM_ALPHA);
$archetype = optional_param('arch', 'user', PARAM_ALPHA);

// Get the base URL for this and related pages into a convenient variable.
$baseurl = new moodle_url('/local/ulpgccore/rolecapscheck.php', array('action'=>$action, 'arch'=>$archetype));

// setup page
admin_externalpage_setup('local_ulpgccore_rolecapscheck', '', null, $baseurl);

// Check access permissions.
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('local/ulpgccore:manage', $systemcontext);

$archetypes = get_role_archetypes();


// Handle confirmations and actions.


$PAGE->set_navigation_overflow_state(false);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('rolecapscheck', 'local_ulpgccore'));

$select = new single_select($baseurl, 'arch', $archetypes, $archetype);
$select->label = get_string('archetype', 'core_role');
$select->formid = 'selectarchetype';
echo $OUTPUT->render($select);


//$adminurl = new moodle_url('/admin/');
$arguments = array('contextid' => $systemcontext->id,
                'contextname' => $systemcontext->get_context_name(),
                'adminurl' => $baseurl->out());
$PAGE->requires->strings_for_js(
                                array('roleprohibitinfo', 'roleprohibitheader', 'roleallowinfo', 'roleallowheader',
                                    'confirmunassigntitle', 'confirmroleunprohibit', 'confirmroleprevent', 'confirmunassignyes',
                                    'confirmunassignno', 'deletexrole'), 'core_role');
$PAGE->requires->js_call_amd('core/permissionmanager', 'initialize', array($arguments));

$table = new check_role_permissions_table($systemcontext, $archetype);

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
