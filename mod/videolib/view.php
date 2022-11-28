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
 * Prints an instance of mod_videolib.
 *
 * @package     mod_videolib
 * @copyright   2018 Enrique Castro @ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
//require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/mod/videolib/locallib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);
// ... module instance id.
$v  = optional_param('v', 0, PARAM_INT);
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($id) {
    list($course, $cm) = get_course_and_cm_from_cmid($id, '');
    $videolib = $DB->get_record('videolib', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($v) {
    $videolib = $DB->get_record('videolib', array('id' => $v), '*', MUST_EXIST);
    list($course, $cm) = get_course_and_cm_from_instance($videolib, 'videolib');
} else {
    print_error(get_string('missingidandcmid'));
}


require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videolib:view', $context);

// Completion and trigger events.
videolib_view($videolib, $course, $cm, $context);

$baseurl = new moodle_url('/mod/videolib/view.php', array('id' => $cm->id));
$PAGE->set_url($baseurl);

$options = empty($videolib->displayoptions) ? array() : unserialize($videolib->displayoptions);

if ($inpopup and $videolib->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$videolib->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname.': '.$videolib->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($videolib);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($videolib->name), 2);
groups_print_activity_menu($cm, $baseurl);
$currentgroup = groups_get_activity_group($cm, true);

if (!empty($options['printintro'])) {
    if (trim(strip_tags($videolib->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'pageintro');
        echo format_module_intro('videolib', $videolib, $cm->id);
        echo $OUTPUT->box_end();
    }
}

$parameters = $videolib->searchtype ? videolib_parameter_value_mapping($videolib, $cm, $course) : null;

$source = videolib_get_source_plugin($videolib, $parameters);

$source->instance_video_setup($context);

echo $OUTPUT->box($source->show(), 'generalbox center clearfix');

$strlastmodified = get_string("lastmodified");
echo "<div class=\"modified\">$strlastmodified: ".userdate($videolib->timemodified)."</div>";

echo $OUTPUT->footer();
