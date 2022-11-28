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
 * Prints a particular instance of moodleoverflow
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_moodleoverflow
 * @copyright 2017 Kennet Winter <k_wint10@uni-muenster.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config and locallib.
require_once(__DIR__.'/../../config.php');
global $CFG, $PAGE, $DB, $OUTPUT, $SESSION;
require_once($CFG->dirroot.'/mod/moodleoverflow/locallib.php');

// Declare optional parameters.
$id      = optional_param('id', 0, PARAM_INT);       // Course Module ID.
$m       = optional_param('m', 0, PARAM_INT);        // MoodleOverflow ID.
$page    = optional_param('page', 0, PARAM_INT);     // Which page to show.

// Set the parameters.
$params = array();
if ($id) {
    $params['id'] = $id;
} else {
    $params['m'] = $m;
}
if ($page) {
    $params['page'] = $page;
}
$PAGE->set_url('/mod/moodleoverflow/view.php', $params);

// Check for the course and module.
if ($id) {
    $cm              = get_coursemodule_from_id('moodleoverflow', $id, 0, false, MUST_EXIST);
    $course          = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moodleoverflow  = $DB->get_record('moodleoverflow', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($m) {
    $moodleoverflow  = $DB->get_record('moodleoverflow', array('id' => $m), '*', MUST_EXIST);
    $course          = $DB->get_record('course', array('id' => $moodleoverflow->course), '*', MUST_EXIST);
    $cm              = get_coursemodule_from_instance('moodleoverflow', $moodleoverflow->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('missingparameter');
}



// Require a login.
require_login($course, true, $cm);

// Set the context.
$context = context_module::instance($cm->id);
$PAGE->set_context($context);

// Check some capabilities.
if (!has_capability('mod/moodleoverflow:viewdiscussion', $context)) {
    notice(get_string('noviewdiscussionspermission', 'moodleoverflow'));
}

// Mark the activity completed (if required) and trigger the course_module_viewed event.
$event = \mod_moodleoverflow\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->trigger();

// ecastro ULPGC completion
// Completion.
require_once($CFG->libdir.'/completionlib.php');
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.
$PAGE->set_url('/mod/moodleoverflow/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moodleoverflow->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here.
echo $OUTPUT->header();

if ($moodleoverflow->anonymous > 0) {
    $strkeys = [
            \mod_moodleoverflow\anonymous::QUESTION_ANONYMOUS => 'desc:only_questions',
            \mod_moodleoverflow\anonymous::EVERYTHING_ANONYMOUS => 'desc:anonymous'
    ];
    echo html_writer::tag('p', get_string($strkeys[$moodleoverflow->anonymous], 'moodleoverflow'));
}

$reviewlevel = \mod_moodleoverflow\review::get_review_level($moodleoverflow);
if ($reviewlevel > 0) {
    $strkeys = [
        \mod_moodleoverflow\review::QUESTIONS => 'desc:review_questions',
        \mod_moodleoverflow\review::EVERYTHING => 'desc:review_everything'
    ];
    echo html_writer::tag('p', get_string($strkeys[$reviewlevel], 'moodleoverflow'));
}

if (has_capability('mod/moodleoverflow:reviewpost', $context)) {
    $reviewpost = \mod_moodleoverflow\review::get_first_review_post($moodleoverflow->id);

    if ($reviewpost) {
        echo html_writer::link($reviewpost, get_string('review_needed', 'mod_moodleoverflow'), ['class' => 'btn btn-danger']);
    }
}

// Return here after posting, etc.
$SESSION->fromdiscussion = qualified_me();

// Print the discussions.
moodleoverflow_print_latest_discussions($moodleoverflow, $cm, $page, get_config('moodleoverflow', 'manydiscussions'));

// Finish the page.
echo $OUTPUT->footer();
