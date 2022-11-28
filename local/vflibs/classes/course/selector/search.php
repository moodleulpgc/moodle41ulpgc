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
 * Code to search for courses in response to an ajax call from a course selector.
 *
 * @package core_course
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../../../../config.php');
require_once($CFG->dirroot.'/local/vflibs/classes/course/selector/course_selector_base.php');

// Get the search parameter.
$search = required_param('search', PARAM_RAW);
$selectorhash = required_param('selectorid', PARAM_ALPHANUM);

$PAGE->set_context(context_system::instance());
$params = array('search' => $search, 'selectorid' => $selectorhash);
$PAGE->set_url(new moodle_url('/local/vflibs/classes/course/selector/search.php', $params));

// Check access.
require_login();
require_sesskey();

// Get and validate the selectorid parameter.
if (!isset($USER->courseselectors[$selectorhash])) {
    print_error('unknowncourseselector');
}

// Get the options.
$options = $USER->courseselectors[$selectorhash];

// Create the appropriate courseselector.
$classname = $options['class'];
unset($options['class']);
$name = $options['name'];
unset($options['name']);
if (isset($options['file'])) {
    require_once($CFG->dirroot . '/' . $options['file']);
    unset($options['file']);
}
$courseselector = new $classname($name, $options);

// Do the search and output the results.
$results = $courseselector->find_courses($search);

$json = array();
foreach ($results as $groupname => $courses) {
    $groupdata = array('name' => $groupname, 'courses' => array());
    foreach ($courses as $course) {
        $output = new stdClass;
        $output->id = $course->id;
        $output->name = $courseselector->output_course($course);
        if (!empty($course->disabled)) {
            $output->disabled = true;
        }
        if (!empty($course->infobelow)) {
            $output->infobelow = $course->infobelow;
        }
        $groupdata['courses'][] = $output;
    }
    $json[] = $groupdata;
}

echo json_encode(array('results' => $json));
