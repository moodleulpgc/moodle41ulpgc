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
 * Page to view the course links
 *
 * @package    core
 * @subpackage link
 * @copyright  2021 Sujith Haridasan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// Course id.
$courseid = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/local/ulpgcgroups/index.php', array('id' => $courseid)));

// Basic access checks.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);

// Otherwise, output the page with a notification stating that there are no available course group links.
$managegroupsstr = get_string('managegroups', 'local_ulpgcgroups');
$PAGE->set_title($managegroupsstr);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->add_body_class('limitedwidth');
$PAGE->set_pagelayout('incourse');
$url = new moodle_url('/local/ulpgcgroups/index.php', array('id'=>$courseid));
navigation_node::override_active_url(new moodle_url('/group/index.php', array('id'=>$courseid)));
$PAGE->navbar->add($managegroupsstr);

echo $OUTPUT->header();
echo $OUTPUT->heading($managegroupsstr);

// Check if there is at least one displayable link.
$haslinks = false;
if ($linknode = $PAGE->settingsnav->find('groupscontainer', \navigation_node::TYPE_CONTAINER)) {
    foreach ($linknode->children as $child) {
        if ($child->display) {
            $haslinks = true;
            break;
        }
    }
}

if ($haslinks) {
    echo $OUTPUT->render_from_template('core/report_link_page', ['node' => $linknode]);
} else {
    echo html_writer::div($OUTPUT->notification(get_string('nolinks', 'local_ulpgcgroups'), 'error'), 'mt-3');
}
echo $OUTPUT->footer();
