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
 * Block "course overview (campus)" - Local library
 *
 * @package    block_course_termlist
 * @copyright  Enrique Castro <@ULPGC> based on block_course_termlist by Alexander Bias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// @codingStandardsIgnoreFile
// Let codechecker ignore this file. This legacy code is not fully compliant to Moodle coding style but working and well documented.

/**
 * Get my courses from DB
 *
 * @return array
 */
function block_course_termlist_get_my_courses() {
    // Get my courses in alphabetical order.
    $courses = enrol_get_my_courses('id, shortname', 'fullname ASC');

    // Remove frontpage course, if enrolled, from courses list.
    $site = get_site();
    if (array_key_exists($site->id, $courses)) {
        unset($courses[$site->id]);
    }

    return $courses;
}


/**
 * Check if course is hidden according to the hide courses feature
 *
 * @param course $course
 * @return boolean
 */
function block_course_termlist_course_hidden_by_hidecourses($courseorid) {

    $course = new stdClass();
    $course->id = (is_object($courseorid)) ? $courseorid->id : $courseorid;

    // Course is visible if it isn't hidden.
    if (get_user_preferences('block_course_termlist-hidecourse-'.$course->id, 0) == 0) {
        return false;

        // Otherwise it is hidden.
    } else {
        return true;
    }
}


/**
 * Check if course is hidden according to the term course filter
 *
 * @param course $course
 * @param string $selectedterm
 * @return boolean
 */
function block_course_termlist_course_hidden_by_termcoursefilter($course, $selectedterm, $timelessterms) {
    // Course is visible if it is within selected term or all terms are selected.
    if (!$course->term ||  $course->term == $selectedterm || $selectedterm == 'all' || in_array($course->term, $timelessterms)) {
        return false;

        // Otherwise it is hidden.
    } else {
        return true;
    }
}


/**
 * Check if course is hidden according to the parent category course filter
 *
 * @param course $course
 * @param string $selectedcategory
 * @return boolean
 */
function block_course_termlist_course_hidden_by_categorycoursefilter($course, $selectedcategory) {
    // Course is visible if it is within selected parent category or all categories are selected.
    if ($course->category == $selectedcategory || $selectedcategory == 'all') {
        return false;

        // Otherwise it is hidden.
    } else {
        return true;
    }
}


/**
 * Check if course is hidden according to the top level category course filter
 *
 * @param course $course
 * @param string $selectedtoplevelcategory
 * @return boolean
 */
function block_course_termlist_course_hidden_by_toplevelcategorycoursefilter($course, $selectedtoplevelcategory) {
    // Course is visible if it is within selected top level category or all categories are selected.
    if ($course->toplevelcategoryid == $selectedtoplevelcategory || $selectedtoplevelcategory == 'all') {
        return false;

        // Otherwise it is hidden.
    } else {
        return true;
    }
}


/**
 * Check if course is visible according to the teacher course filter
 *
 * @param course $course
 * @param string $selectedteacher
 * @return boolean
 */
function block_course_termlist_course_hidden_by_teachercoursefilter($course, $selectedteacher) {
    // Course is visible if it has the selected teacher or all teachers are selected.
    if (isset($course->teachers[$selectedteacher]) || $selectedteacher == 'all') {
        return false;

        // Otherwise it is hidden.
    } else {
        return true;
    }
}


/**
 * Check if course is hidden according to any (preprocessed!) filter
 *
 * @param course $course
 * @return boolean
 */
function block_course_termlist_course_hidden_by_anyfilter($course) {
    // Check if there is any reason to hide the course.
    $hidecourse = (isset($course->termcoursefiltered) && $course->termcoursefiltered) ||
                    (isset($course->categorycoursefiltered) && $course->categorycoursefiltered == true) ||
                    (isset($course->toplevelcategorycoursefiltered) && $course->toplevelcategorycoursefiltered == true) ||
                    (isset($course->teachercoursefiltered) && $course->teachercoursefiltered == true);

    return $hidecourse;
}


/**
 * Check if the configured term dates make sense
 *
 * @return bool
 */
function block_course_termlist_check_term_config() {
    $ctl_config = get_config('block_course_termlist');

    if ($ctl_config->termmode == 1) {
        return true;
    } else if ($ctl_config->termmode == 2 &&
        intval(date('z', strtotime('2003-'.$ctl_config->term1startday))) <
                intval(date('z', strtotime('2003-'.$ctl_config->term2startday)))) {
            return true;
    } else if ($ctl_config->termmode == 3 &&
        intval(date('z', strtotime('2003-'.$ctl_config->term1startday))) <
                intval(date('z', strtotime('2003-'.$ctl_config->term2startday))) &&
        intval(date('z', strtotime('2003-'.$ctl_config->term2startday))) <
                intval(date('z', strtotime('2003-'.$ctl_config->term3startday)))) {
            return true;
    } else if ($ctl_config->termmode == 4 &&
        intval(date('z', strtotime('2003-'.$ctl_config->term1startday))) <
                intval(date('z', strtotime('2003-'.$ctl_config->term2startday))) &&
        intval(date('z', strtotime('2003-'.$ctl_config->term2startday))) <
                intval(date('z', strtotime('2003-'.$ctl_config->term3startday))) &&
        intval(date('z', strtotime('2003-'.$ctl_config->term3startday))) <
                intval(date('z', strtotime('2003-'.$ctl_config->term4startday)))) {
            return true;
    } else {
        return false;
    }
}


/**
 * Take array of teacher objects and return a string of names, sorted by relevance and name
 *
 * @param array $teachers array of teachers
 * @return string string with concatenated teacher names
 */
function block_course_termlist_get_teachername_string($teachers) {
    $ctl_config = get_config('block_course_termlist');

    // If given array is empty, return empty string.
    if (empty($teachers)) {
        return '';
    }

    // Sort all teachers by relevance and name, return empty string when sorting fails.
    $success = usort($teachers, "block_course_termlist_compare_teachers");
    if (!$success) {
        return '';
    }

    // Get all teachers' names as an array according the teacher name style setting.
    $teachernames = array_map(function($obj) {
        global $ctl_config;

        // Display fullname.
        if ($ctl_config->secondrowshowteachernamestyle == 1) {
            return $obj->firstname.' '.$obj->lastname;
        }
        // Display lastname.
        else if ($ctl_config->secondrowshowteachernamestyle == 2) {
            return $obj->lastname;
        }
        // Display firstname.
        else if ($ctl_config->secondrowshowteachernamestyle == 3) {
            return $obj->firstname;
        }
        // Display fullnamedisplay.
        else if ($ctl_config->secondrowshowteachernamestyle == 4) {
            return fullname($obj);
        }
        // Fallback: Display lastname.
        else {
            return $obj->lastname;
        }
    }, $teachers);

    // Implode teachers' names to a single string.
    $teachernames = implode(", ", $teachernames);

    return $teachernames;
}


/**
 * Take term name and year(s) and return displayname for term filter based on plugin configuration
 *
 * @param string $termname The term's name
 * @param string $year The term's year
 * @param string $year2 The term's second year (optional)
 * @return string String with the term's displayname
 */
function block_course_termlist_get_term_displayname($termname, $year, $year2='') {
    $ctl_config = get_config('block_course_termlist');

    // Build the first year - second year combination.
    $displayname = $year;
    if ($year2 != '') {
        // Hyphen separation.
        if ($ctl_config->termyearseparation == 1) {
            $displayname = $year.'-'.$year2;
        }
        // Slash separation.
        else if ($ctl_config->termyearseparation == 2) {
            $displayname = $year.'/'.$year2;
        }
        // Underscore separation.
        else if ($ctl_config->termyearseparation == 3) {
            $displayname = $year.'_'.$year2;
        }
        // No second year.
        else if ($ctl_config->termyearseparation == 4) {
            $displayname = $year;
        }
        // This shouldn't happen.
        else {
            $displayname = $year.'/'.$year2;
        }
    }

    // Add the term name.
    // Prefix with space.
    if ($ctl_config->termyearpos == 1) {
        $displayname = $displayname.' '.$termname;
    }
    // Prefix without space.
    else if ($ctl_config->termyearpos == 2) {
        $displayname = $displayname.$termname;
    }
    // Suffix with space.
    else if ($ctl_config->termyearpos == 3) {
        $displayname = $termname.' '.$displayname;
    }
    // Suffix without space.
    else if ($ctl_config->termyearpos == 4) {
        $displayname = $termname.$displayname;
    }
    // This shouldn't happen.
    else {
        $displayname = $termname. ' '.$termname;
    }

    return $displayname;
}


/**
 * Compare teacher by relevance helper function
 *
 * @param object $a Teacher A
 * @param object $b Teacher B
 * @return int
 */
function block_course_termlist_compare_teachers($a, $b) {
    // Compare relevance of teachers' roles.
    if ($a->sortorder < $b->sortorder) {
        return -1;
    } else if ($a->sortorder > $b->sortorder) {
        return 1;
    } else if ($a->sortorder == $b->sortorder) {
        // Teachers' roles are equal, then compare lastnames.
        return strcasecmp($a->lastname, $b->lastname);
    } else {
        // This should never happen.
        return 0;
    }
}


/**
 * Compare category by sortorder helper function
 *
 * @param object $a Category A
 * @param object $b Category B
 * @return int
 */
function block_course_termlist_compare_categories($a, $b) {
    // Compare sortorder of categories.
    if ($a->sortorder < $b->sortorder) {
        return -1;
    } else if ($a->sortorder > $b->sortorder) {
        return 1;
    } else if ($a->sortorder == $b->sortorder) {
        // Category sortorders are equal - this shouldn't happen, but if it does then compare category names alphabetically.
        return strcasecmp(format_string($a->name), format_string($b->name));
    } else {
        // This should never happen.
        return 0;
    }
}


/**
 * Remember the not shown courses for local_boostcoc
 *
 * Basically, this is remembered by the JavaScript filters directly when they are applied in the browser, but we want a fallback
 * when javascript is off
 * Unfortunately, at page load local_boostcoc can only change the nav drawer _before_ this function can store its data, thus the
 * fallback when javascript is off has a lag.
 *
 * @param array $courses
 */
function block_course_termlist_remember_notshowncourses_for_local_boostcoc($courses) {
    // Do only if local_boostcoc is installed.
    if (block_course_termlist_check_local_boostcoc() == true) {
        // Get all courses which are not shown (because they are hidden by any filter or by the hide courses feature)
        // and store their IDs in an array.
        $notshowncourses = array();
        foreach ($courses as $c) {
            if ((block_course_termlist_course_hidden_by_anyfilter($c) == true ||
                    block_course_termlist_course_hidden_by_hidecourses($c)) == true) {
                $notshowncourses[] = $c->id;
            }
        }

        // Convert not shown courses array to JSON.
        $jsonstring = json_encode($notshowncourses);

        // Store the current status of not shown courses.
        set_user_preference('local_boostctl-notshowncourses', $jsonstring);
    }
}


/**
 * Remember the active filters for local_boostcoc
 *
 * Basically, this is remembered by the JavaScript filters directly when they are applied in the browser, but we want a fallback
 * when javascript is off.
 * Unfortunately, at page load local_boostcoc can only change the nav drawer _before_ this function can store its data, thus the
 * fallback when javascript is off has a lag.
 *
 * @param int $hiddencoursescounter
 */
function block_course_termlist_remember_activefilters_for_local_boostcoc($hiddencoursescounter) {
    // Do only if local_boostcoc is installed.
    if (block_course_termlist_check_local_boostcoc() == true) {
        $ctl_config = get_config('block_course_termlist');

        // Check all filters if they are enabled and active filters (value != all) and check the fact that there are hidden courses and store them in an array.
        $activefilters = array();
        if ($ctl_config->termcoursefilter == true && get_user_preferences('block_course_termlist-selectedterm') != 'all') {
            $activefilters[] = 'filterterm';
        }
        if ($ctl_config->categorycoursefilter == true && get_user_preferences('block_course_termlist-selectedcategory') != 'all') {
            $activefilters[] = 'filtercategory';
        }
        if ($ctl_config->toplevelcategorycoursefilter == true && get_user_preferences('block_course_termlist-selectedtoplevelcategory') != 'all') {
            $activefilters[] = 'filtertoplevelcategory';
        }
        if ($ctl_config->teachercoursefilter == true && get_user_preferences('block_course_termlist-selectedteacher') != 'all') {
            $activefilters[] = 'filterteacher';
        }
        if ($hiddencoursescounter > 0) {
            $activefilters[] = 'hidecourses';
        }

        // Convert active filters array to JSON.
        $jsonstring = json_encode($activefilters);

        // Store the current status of active filters.
        set_user_preference('local_boostctl-activefilters', $jsonstring);
    }
}


/**
 * Check if our companion plugin local_boostcoc is installed
 *
 * @return boolean
 */
function block_course_termlist_check_local_boostcoc() {
    global $CFG;

    static $local_boostctl_installed;

    if (!isset($local_boostctl_installed)) {
        if (file_exists($CFG->dirroot.'/local/boostcoc/lib.php')) {
            $local_boostctl_installed = true;
        } else {
            $local_boostctl_installed = false;
        }
    }

    return $local_boostctl_installed;
}
