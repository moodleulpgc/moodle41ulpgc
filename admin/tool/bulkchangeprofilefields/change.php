<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Update user's profile field.
 *
 * @package     tool_bulkchangeprofilefields
 * @copyright   2022 Daniel Neis Araujo <daniel@adapta.online>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/admin/user/lib.php');
require_once($CFG->libdir.'/adminlib.php');

$submitbutton = optional_param('submitbutton', '', PARAM_TEXT);
$fieldids = optional_param_array('fieldids', [], PARAM_INT);

admin_externalpage_setup('userbulk');
require_capability('moodle/user:create', context_system::instance());

$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

if ($submitbutton == '') {
    $form = new \tool_bulkchangeprofilefields\form\fieldselect();
} else if ($submitbutton == get_string('next', 'tool_bulkchangeprofilefields')) {
    $pastform = new \tool_bulkchangeprofilefields\form\fieldselect();
    if ($data = $pastform->get_data()) {
        $form = new \tool_bulkchangeprofilefields\form\fieldchange(null, ['fieldids' => $data->field]);
    }
} else if  ($submitbutton == get_string('change', 'tool_bulkchangeprofilefields')) {
    $form = new \tool_bulkchangeprofilefields\form\fieldchange(null, ['fieldids' => $fieldids]);
    if ($data = $form->get_data()) {
        unset($data->fieldids);
        unset($data->submitbutton);
        $dataarray = (array)$data;
        foreach ($SESSION->bulk_users as $u) {
            $user = new stdclass();
            $user->id = $u;
            foreach ($dataarray as $fieldshortname => $value) {
                $user->{$fieldshortname} = $value;
            }
            // Save custom profile fields data.
            profile_save_data($user);
        }
        redirect($return, get_string('fieldupdated', 'tool_bulkchangeprofilefields'));
    }
} else {
    $form = new \tool_bulkchangeprofilefields\form\fieldselect();
}

if ($form->is_cancelled()) {
    redirect($return);
}

echo $OUTPUT->header(),
     $form->render(),
     $OUTPUT->footer();
