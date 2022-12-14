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
 * @package    enrol_apply
 * @copyright  emeneo.com (http://emeneo.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     emeneo.com (http://emeneo.com/)
 * @author     Johannes Burk <johannes.burk@sudile.com>
 */

require('../../config.php');
require_once('edit_form.php');

$courseid   = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT);

$course = get_course($courseid);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/apply:config', $context);

$PAGE->set_url('/enrol/apply/edit.php', array('courseid' => $course->id, 'id' => $instanceid));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
if (!enrol_is_enabled('apply')) {
    redirect($return);
}

$plugin = enrol_get_plugin('apply');

if ($instanceid) {
    $instance = $DB->get_record(
        'enrol',
        array(
            'courseid' => $course->id,
            'enrol' => 'apply',
            'id' => $instanceid),
        '*', MUST_EXIST);
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, we have to add new instance.
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id' => $course->id)));
    $instance = (object)$plugin->get_instance_defaults();
    $instance->id       = null;
    $instance->courseid = $course->id;
}

// Process notify setting for editing...
// Convert to array for use with multi-select element.
//$notify = array('$@NONE@$');
/*
if ($instance->customtext3 != '') {
    $notify = explode(',', $instance->customtext3);
}
*/
//$instance->notify = $notify;

$mform = new enrol_apply_edit_form(null, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {
    // Process notify setting for storing...
    // Note: Mostly copied from admin_setting_users_with_capability::write_setting().
    $notify = $data->notify;
    // If all is selected, remove any explicit options.
    if (in_array('$@ALL@$', $notify)) {
        $notify = array('$@ALL@$');
    }
    // None never needs to be written to the DB.
    if (in_array('$@NONE@$', $notify)) {
        unset($notify[array_search('$@NONE@$', $notify)]);
    }
    // Convert back to string for storing in enrol table.
    //$data->customtext3 = implode(',', $notify);
    $notify = implode(",", $notify);

    if ($instance->id) {
        $instance->status           = $data->status;
        $instance->name             = $data->name;
        $instance->customtext1      = $data->customtext1;
        $instance->customtext2      = $data->customtext2;
        $instance->customtext3      = $notify;
        $instance->customint1       = $data->customint1;
        $instance->customint2       = $data->customint2;
        $instance->customint3       = $data->customint3;
        $instance->customint6       = $data->customint6;
        // Add optionnal commentary zone
        // customint7 -> select for opt_commentaryzone
        // Start modification
        $instance->customint7       = $data->customint7;
        // End mofication
        $instance->roleid           = $data->roleid;
        $instance->enrolperiod      = $data->enrolperiod;
        // Add notifications
        // Start modification
        $selectedvalue = $data->expirynotify;
        if ($selectedvalue > 0) {
            $instance->expirynotify = 1;
        } else {
            $instance->expirynotify = 0;
        }
        if ($selectedvalue == 2) {
            $instance->notifyall = 1;
        } else {
            $instance->notifyall = 0;
        }
        if ($selectedvalue != 0) {
            if ($data->expirythreshold < 86400) {
                $instance->expirythreshold = 86400;
            } else {
                $instance->expirythreshold = $data->expirythreshold;
            }
        }

        // End modification
        $instance->timemodified = time();

        $DB->update_record('enrol', $instance);

        // Adding record in enrol_apply_groups
        // Start modification

        $groups = $data->groupselect;

        $DB->delete_records('enrol_apply_groups', array('enrolid' => $instance->id));

        foreach ($groups as $value) {
            $object = new stdClass();
            $object->groupid = $value;
            $object->enrolid = $instance->id;
            $DB->insert_record('enrol_apply_groups', $object);
        }

        // End modification


    } else {
        // Notification threshold
        // Start modification
        $selectedvalue = $data->expirynotify;
        if ($selectedvalue > 0) {
            $expirynotify = 1;
        } else {
            $expirynotify = 0;
        }
        if ($selectedvalue == 2) {
            $notifyall = 1;
        } else {
            $notifyall = 0;
        }

        if ($data->expirythreshold < 86400) {
            $expirythreshold = 86400;
        } else {
            $expirythreshold = $data->expirythreshold;
        }
        // End modification

        $fields = array(
            'status'            => $data->status,
            'name'              => $data->name,
            'roleid'            => $data->roleid,
            'customint1'        => $data->customint1,
            'customint2'        => $data->customint2,
            'customint3'        => $data->customint3,
            'customint6'        => $data->customint6,
            // customint7 -> field for opt_commentaryzone
            // Start modification
            'customint7'        => $data->customint7,
            // End modification
            'customtext1'       => $data->customtext1,
            'customtext2'       => $data->customtext2,
            'customtext3'       => $notify,
            'enrolperiod'       => $data->enrolperiod,
            // Notification Threshold
            // Start modification
            'expirynotify'      => $expirynotify,
            'notifyall'         => $notifyall,
            'expirythreshold'   => $expirythreshold,
            // End modification

        );
        $id = $plugin->add_instance($course, $fields);

        // Start modification
        $groups = $data->groupselect;
        foreach ($groups as $value) {
            $object = new stdClass();
            $object->groupid = $value;
            $object->enrolid = $id;
            $DB->insert_record('enrol_apply_groups', $object);
        }
        // End modification
    }
    redirect($return);
}

$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(get_string('pluginname', 'enrol_apply'));

$renderer = $PAGE->get_renderer('enrol_apply');
$renderer->edit_page($mform);
