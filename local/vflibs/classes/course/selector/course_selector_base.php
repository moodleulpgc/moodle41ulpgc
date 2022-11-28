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
 * @package local_vflibs
 * @author valery.fremaux@gmail.com
 * @category local
 */
defined('MOODLE_INTERNAL') || die;

/*
 * The default size of a course selector.
 */
define('COURSE_SELECTOR_DEFAULT_ROWS', 20);

/**
 * Base class for course selectors.
 *
 * In your theme, you must give each course-selector a defined width. If the
 * course selector has name="myid", then the div myid_wrapper must have a width
 * specified.
 */
abstract class course_selector_base {

    /**
     * @var string The control name (and id) in the HTML.
     */
    protected $name;

    /**
     * @var array Extra fields to search on and return in addition to shortname and fullname.
     * course fields should be prefixed with c. and category fields with cc.
     */
    protected $extrafields;

    /**
     * @var object Context used for capability checks regarding this selector (does
     * not necessarily restrict user list)
     */
    protected $category;

    /**
     * not necessarily restrict user list)
     */
    protected $recursive = true;

    /**
     * @var boolean Whether the conrol should allow selection of many users, or just one.
     */
    protected $multiselect = true;

    /**
     * @var int The height this control should have, in rows.
     */
    protected $rows = COURSE_SELECTOR_DEFAULT_ROWS;

    /**
     * @var array A list of userids that should not be returned by this control.
     */
    protected $exclude = array();

    /**
     * @var array|null A list of the users who are selected.
     */
    protected $selected = null;

    /**
     * @var boolean When the search changes, do we keep previously selected options that do
     * not match the new search term?
     */
    protected $preserveselected = false;

    /**
     * @var boolean If only one user matches the search, should we select them automatically.
     */
    protected $autoselectunique = false;

    /**
     * @var boolean When searching, do we only match the starts of fields (better performance)
     * or do we match occurrences anywhere?
     */
    protected $searchanywhere = false;

    /**
     * @var mixed This is used by get selected users
     */
    protected $validatingcourseids = null;

    /**
     * @var boolean Used to ensure we only output the search options for one user selector on
     * each page.
     */
    private static $searchoptionsoutput = false;

    /**
     * @var array JavaScript YUI3 Module definition
     */
    protected static $jsmodule = array(
                'name' => 'course_selector',
                'fullpath' => '/local/vflibs/classes/course/selector/module.js',
                'requires'  => array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification'),
                'strings' => array(
                    array('previouslyselectedcourses', 'local_vflibs', '%%SEARCHTERM%%'),
                    array('nomatchingcourses', 'local_vflibs', '%%SEARCHTERM%%'),
                    array('none', 'moodle')
                ));

    /**
     * @var int this is used to define maximum number of users visible in list
     */
    public $maxcoursesperpage = 200;

    public $options;

    // Public API ==============================================================.

    /**
     * Constructor. Each subclass must have a constructor with this signature.
     *
     * @param string $name the control name/id for use in the HTML.
     * @param array $options other options needed to construct this selector.
     * You must be able to clone a courseselector by doing new get_class($us)($us->get_name(), $us->get_options());
     */
    public function __construct($name, $options = array()) {
        global $CFG, $PAGE;

        $this->options = $options;

        // Initialise member variables from constructor arguments.
        $this->name = $name;

        // Use specified context for permission checks, system context if not specified.
        if (isset($options['category'])) {
            $this->category = $options['category'];
        } else {
            $this->category = 0;
        }

        if (isset($options['recursive'])) {
            $this->recursive = $options['recursive'];
        } else {
            $this->recursive = true;
        }

        if (isset($options['extrafields'])) {
            $this->extrafields = $options['extrafields'];
        } else {
            $this->extrafields = array();
        }
        if (isset($options['exclude']) && is_array($options['exclude'])) {
            $this->exclude = $options['exclude'];
        }
        if (isset($options['multiselect'])) {
            $this->multiselect = $options['multiselect'];
        }

        // Read the user prefs / optional_params that we use.
        $this->preserveselected = $this->initialise_option('courdseselector_preserveselected', $this->preserveselected);
        $this->autoselectunique = $this->initialise_option('courseselector_autoselectunique', $this->autoselectunique);
        $this->searchanywhere = $this->initialise_option('courseselector_searchanywhere', $this->searchanywhere);

        if (!empty($CFG->maxcoursesperpage)) {
            $this->maxcoursesperpage = $CFG->maxcoursesperpage;
        }
    }

    /**
     * All to the list of course ids that this control will not select. For example,
     * on the auditquiz category assign page, we do not list the courses who are NOT self enrollable.
     *
     * @param array $arrayofcourseids the user ids to exclude.
     */
    public function exclude($arrayofcourseids) {
        $this->exclude = array_unique(array_merge($this->exclude, $arrayofcourseids));
    }

    /**
     * Clear the list of excluded course ids.
     */
    public function clear_exclusions() {
        $this->exclude = array();
    }

    /**
     * @return array the list of course ids that this control will not select.
     */
    public function get_exclusions() {
        return clone($this->exclude);
    }

    /**
     * @return array of course objects. The courses that were selected. This is a more sophisticated version
     * of optional_param($this->name, array(), PARAM_INT) that validates the
     * returned list of ids against the rules for this ocurse selector.
     */
    public function get_selected_courses() {
        // Do a lazy load.
        if (is_null($this->selected)) {
            $this->selected = $this->load_selected_courses();
        }
        return $this->selected;
    }

    /**
     * Convenience method for when multiselect is false (throws an exception if not).
     * @return object the selected course object, or null if none.
     */
    public function get_selected_course() {
        if ($this->multiselect) {
            throw new moodle_exception('cannotcallusgetselectedcourse');
        }
        $courses = $this->get_selected_courses();
        if (count($courses) == 1) {
            return reset($courses);
        } else if (count($courses) == 0) {
            return null;
        } else {
            throw new moodle_exception('courseselectortoomany');
        }
    }

    /**
     * If you update the database in such a way that it is likely to change the
     * list of courses that this component is allowed to select from, then you
     * must call this method.
     */
    public function invalidate_selected_courses() {
        $this->selected = null;
    }

    /**
     * Output this course_selector as HTML.
     * @param boolean $return if true, return the HTML as a string instead of outputting it.
     * @return mixed if $return is true, returns the HTML as a string, otherwise returns nothing.
     */
    public function display($return = false) {
        global $PAGE;

        // Get the list of requested courses.
        $search = optional_param($this->name . '_searchtext', '', PARAM_RAW);
        if (optional_param($this->name . '_clearbutton', false, PARAM_BOOL)) {
            $search = '';
        }
        $groupedcourses = $this->find_courses($search);

        // Output the select.
        $name = $this->name;
        $multiselect = '';
        if ($this->multiselect) {
            $name .= '[]';
            $multiselect = 'multiple="multiple" ';
        }
        $output = '<div class="courseselector" id="' . $this->name . '_wrapper">' . "\n" .
                '<select name="' . $name . '" id="' . $this->name . '" ' .
                $multiselect . 'size="' . $this->rows . '">' . "\n";

        // Populate the select.
        $output .= $this->output_options($groupedcourses, $search);

        // Output the search controls.
        $output .= "</select>\n<div>\n";
        $output .= '<input type="text" name="' . $this->name . '_searchtext" id="';
        $output .= $this->name . '_searchtext" size="15" value="' . s($search) . '" />';
        $output .= '<input type="submit" name="' . $this->name . '_searchbutton" id="';
        $output .= $this->name . '_searchbutton" value="' . $this->search_button_caption() . '" />';
        $output .= '<input type="submit" name="' . $this->name . '_clearbutton" id="';
        $output .= $this->name . '_clearbutton" value="' . get_string('clear') . '" />';

        // And the search options.
        $optionsoutput = false;
        if (!self::$searchoptionsoutput) {
            $class = 'courseselector_optionscollapsed';
            $label = get_string('searchoptions');
            $output .= print_collapsible_region_start('', 'courseselector_options', $label, $class, true, true);
            $label = get_string('courseselectorpreserveselected', 'local_vflibs');
            $output .= $this->option_checkbox('preserveselected', $this->preserveselected, $label);
            $label = get_string('courseselectorautoselectunique', 'local_vflibs');
            $output .= $this->option_checkbox('autoselectunique', $this->autoselectunique, $label);
            $label = get_string('courseselectorsearchanywhere', 'local_vflibs');
            $output .= $this->option_checkbox('searchanywhere', $this->searchanywhere, $label);
            $output .= print_collapsible_region_end(true);

            $PAGE->requires->js_init_call('M.core_course.init_course_selector_options_tracker', array(), false, self::$jsmodule);
            self::$searchoptionsoutput = true;
        }
        $output .= "</div>\n</div>\n\n";

        // Initialise the ajax functionality.
        $output .= $this->initialise_javascript($search);

        // Return or output it.
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * The height this control will be displayed, in rows.
     *
     * @param integer $numrows the desired height.
     */
    public function set_rows($numrows) {
        $this->rows = $numrows;
    }

    /**
     * @return integer the height this control will be displayed, in rows.
     */
    public function get_rows() {
        return $this->rows;
    }

    /**
     * Whether this control will allow selection of many, or just one course.
     *
     * @param boolean $multiselect true = allow multiple selection.
     */
    public function set_multiselect($multiselect) {
        $this->multiselect = $multiselect;
    }

    /**
     * @return boolean whether this control will allow selection of more than one course.
     */
    public function is_multiselect() {
        return $this->multiselect;
    }

    /**
     * @return string the id/name that this control will have in the HTML.
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Set the course fields that are displayed in the selector in addition to the
     * course's name.
     *
     * @param array $fields a list of field names that exist in the course table.
     */
    public function set_extra_fields($fields) {
        $this->extrafields = $fields;
    }

    // API for sublasses =======================================================.

    /**
     * Search the database for courses matching the $search string, and any other
     * conditions that apply. The SQL for testing whether a course matches the
     * search string should be obtained by calling the search_sql method.
     *
     * This method is used both when getting the list of choices to display to
     * the course, and also when validating a list of courses that was selected.
     *
     * When preparing a list of courses to choose from ($this->is_validating()
     * return false) you should probably have an maximum number of users you will
     * return, and if more users than this match your search, you should instead
     * return a message generated by the too_many_results() method. However, you
     * should not do this when validating.
     *
     * If you are writing a new user_selector subclass, I strongly recommend you
     * look at some of the subclasses later in this file and in admin/roles/lib.php.
     * They should help you see exactly what you have to do.
     *
     * @param string $search the search string.
     * @return array An array of arrays of courses. The array keys of the outer
     *      array should be the string names of optgroups. The keys of the inner
     *      arrays should be courseids, and the values should be course objects
     *      containing at least the list of fields returned by the method
     *      required_fields_sql(). If a course object has a ->disabled property
     *      that is true, then that option will be displayed greyed out, and
     *      will not be returned by get_selected_courses.
     */
    public abstract function find_courses($search);

    /**
     *
     * Note: this function must be implemented if you use the search ajax field
     *       (e.g. set $options['file'] = '/admin/filecontainingyourclass.php';)
     * @return array the options needed to recreate this course_selector.
     */
    protected function get_options() {
        return array(
            'class' => get_class($this),
            'name' => $this->name,
            'exclude' => $this->exclude,
            'extrafields' => $this->extrafields,
            'multiselect' => $this->multiselect,
        );
    }

    // Inner workings ==========================================================.

    /**
     * @return boolean if true, we are validating a list of selected courses,
     *      rather than preparing a list of courses to choose from.
     */
    protected function is_validating() {
        return !is_null($this->validatingcourseids);
    }

    /**
     * Get the list of courses that were selected by doing optional_param then
     * validating the result.
     *
     * @return array of course objects.
     */
    protected function load_selected_courses() {
        // See if we got anything.
        if ($this->multiselect) {
            $courseids = optional_param_array($this->name, array(), PARAM_INT);
        } else if ($courseid = optional_param($this->name, 0, PARAM_INT)) {
            $courseids = array($courseid);
        }
        // If there are no courses there is nobody to load.
        if (empty($courseids)) {
            return array();
        }

        // If we did, use the find_courses method to validate the ids.
        $this->validatingcourseids = $courseids;
        $groupedcourses = $this->find_courses('');
        $this->validatingcourseids = null;

        // Aggregate the resulting list back into a single one.
        $courses = array();
        foreach ($groupedcourses as $group) {
            foreach ($group as $course) {
                if (!isset($courses[$course->id]) && in_array($course->id, $courseids)) {
                    $courses[$course->id] = $course;
                }
            }
        }

        // If we are only supposed to be selecting a single course, make sure we do.
        if (!$this->multiselect && count($courses) > 1) {
            $courses = array_slice($courses, 0, 1);
        }

        return $courses;
    }

    /**
     * @param string $u the table alias for the course table in the query being
     *      built. May be ''.
     * @return string fragment of SQL to go in the select list of the query.
     */
    protected function required_fields_sql($c) {
        // Raw list of fields.
        $fields = array('id', 'shortname', 'fullname');
        // Add additional name fields.
        $fields = array_merge($fields, $this->extrafields);

        // Prepend the table alias.
        if ($c) {
            foreach ($fields as &$field) {
                $field = $c . '.' . $field;
            }
        }
        return implode(',', $fields);
    }

    /**
     * @param string $search the text to search for.
     * @param string $u the table alias for the course table in the query being
     *      built. May be ''.
     * @return array an array with two elements, a fragment of SQL to go in the
     *      where clause the query, and an array containing any required parameters.
     *      this uses ? style placeholders.
     */
    protected function search_sql($search, $c) {
        return courses_search_sql($search, $c, $this->searchanywhere, $this->extrafields,
                $this->exclude, $this->validatingcourseids);
    }

    /**
     * Used to generate a nice message when there are too many courses to show.
     * The message includes the number of courses that currently match, and the
     * text of the message depends on whether the search term is non-blank.
     *
     * @param string $search the search term, as passed in to the find courses method.
     * @param int $count the number of courses that currently match.
     * @return array in the right format to return from the find_courses method.
     */
    protected function too_many_results($search, $count) {
        if ($search) {
            $a = new stdClass;
            $a->count = $count;
            $a->search = $search;
            return array(get_string('toomanycoursesmatchsearch', 'local_vflibs', $a) => array(),
                    get_string('pleasesearchmore') => array());
        } else {
            return array(get_string('toomanycoursestoshow', 'local_vflibs', $count) => array(),
                    get_string('pleaseusesearch') => array());
        }
    }

    /**
     * Output the list of <optgroup>s and <options>s that go inside the select.
     * This method should do the same as the JavaScript method
     * course_selector.prototype.handle_response.
     *
     * @param array $groupedcourses an array, as returned by find_courses.
     * @return string HTML code.
     */
    protected function output_options($groupedcourses, $search) {
        $output = '';

        // Ensure that the list of previously selected courses is up to date.
        if (!$this->multiselect) {
            $this->get_selected_course();
        } else {
            $this->get_selected_courses();
        }

        /*
         * If $groupedcourses is empty, make a 'no matching courses' group. If there is
         * only one selected course, set a flag to select them if that option is turned on.
         */
        $select = false;
        if (empty($groupedcourses)) {
            if (!empty($search)) {
                $groupedcourses = array(get_string('nomatchingcourses', 'local_vflibs', $search) => array());
            } else {
                $groupedcourses = array(get_string('none') => array());
            }
        } else if ($this->autoselectunique && count($groupedcourses) == 1 &&
                count(reset($groupedcourses)) == 1) {
            $select = true;
            if (!$this->multiselect) {
                $this->selected = array();
            }
        }

        // Output each optgroup.
        foreach ($groupedcourses as $groupname => $courses) {
            $output .= $this->output_optgroup($groupname, $courses, $select);
        }

        // If there were previously selected courses who do not match the search, show them too.
        if ($this->preserveselected && !empty($this->selected)) {
            $output .= $this->output_optgroup(get_string('previouslyselectedcourses', '', $search), $this->selected, true);
        }

        /*
         * This method trashes $this->selected, so clear the cache so it is
         * rebuilt before anyone tried to use it again.
         */
        $this->selected = null;

        return $output;
    }

    /**
     * Output one particular optgroup. Used by the preceding function output_options.
     *
     * @param string $groupname the label for this optgroup.
     * @param array $courses the courses to put in this optgroup.
     * @param boolean $select if true, select the courses in this group.
     * @return string HTML code.
     */
    protected function output_optgroup($groupname, $courses, $select) {
        if (!empty($courses)) {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . ' (' . count($courses) . ')">' . "\n";
            foreach ($courses as $course) {
                $attributes = '';
                $attributes .= ' selected="selected"';
                unset($this->selected[$course->id]);
                $output .= '    <option' . $attributes . ' value="' . $course->id . '">' .
                        $this->output_course($course) . "</option>\n";
                if (!empty($course->infobelow)) {
                    /*
                     * 'Poor man's indent' here is because CSS styles do not work
                     * in select options, except in Firefox.
                     */
                    $output .= '    <option disabled="disabled" class="courseselector-infobelow">' .
                            '&nbsp;&nbsp;&nbsp;&nbsp;' . s($course->infobelow) . '</option>';
                }
            }
        } else {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . '">' . "\n";
            $output .= '    <option disabled="disabled">&nbsp;</option>' . "\n";
        }
        $output .= "  </optgroup>\n";
        return $output;
    }

    /**
     * Convert a course object to a string suitable for displaying as an option in the list box.
     *
     * @param object $course the course to display.
     * @return string a string representation of the course.
     */
    public function output_course($course) {
        $out = $course->fullname;
        if ($this->extrafields) {
            $displayfields = array();
            foreach ($this->extrafields as $field) {
                $displayfields[] = $course->{$field};
            }
            $out .= ' (' . implode(', ', $displayfields) . ')';
        }
        return $out;
    }

    /**
     * @return string the caption for the search button.
     */
    protected function search_button_caption() {
        return get_string('search');
    }

    /**
     * Initialise one of the option checkboxes, either from
     * the request, or failing that from the course_preferences table, or
     * finally from the given default.
     */
    private function initialise_option($name, $default) {
        $param = optional_param($name, null, PARAM_BOOL);
        if (is_null($param)) {
            return get_user_preferences($name, $default);
        } else {
            set_user_preference($name, $param);
            return $param;
        }
    }

    /**
     * Output one of the options checkboxes.
     */
    private function option_checkbox($name, $on, $label) {
        if ($on) {
            $checked = ' checked="checked"';
        } else {
            $checked = '';
        }
        $name = 'courseselector_' . $name;
        $output = '<p><input type="hidden" name="' . $name . '" value="0" />' .
                // For the benefit of brain-dead IE, the id must be different from the name of the hidden form field above.
                // It seems that document.getElementById('frog') in IE will return and element with name="frog".
                '<input type="checkbox" id="' . $name . 'id" name="' . $name . '" value="1"' . $checked . ' /> ' .
                '<label for="' . $name . 'id">' . $label . "</label></p>\n";
        return $output;
    }

    /**
     * @param boolean $optiontracker if true, initialise JavaScript for updating the course prefs.
     * @return any HTML needed here.
     */
    protected function initialise_javascript($search) {
        global $USER, $PAGE, $OUTPUT;
        $output = '';

        // Put the options into the session, to allow search.php to respond to the ajax requests.
        $options = $this->get_options();
        $hash = md5(serialize($options));
        $USER->courseselectors[$hash] = $options;

        // Initialise the selector.
        $params = array($this->name, $hash, $this->extrafields, $search);
        $PAGE->requires->js_init_call('M.core_course.init_course_selector', $params, false, self::$jsmodule);
        return $output;
    }
}

/**
 * Returns SQL used to search through user table to find users (in a query
 * which may also join and apply other conditions).
 *
 * You can combine this SQL with an existing query by adding 'AND $sql' to the
 * WHERE clause of your query (where $sql is the first element in the array
 * returned by this function), and merging in the $params array to the parameters
 * of your query (where $params is the second element). Your query should use
 * named parameters such as :param, rather than the question mark style.
 *
 * There are examples of basic usage in the unit test for this function.
 *
 * @param string $search the text to search for (empty string = find all)
 * @param string $u the table alias for the user table in the query being
 *     built. May be ''.
 * @param bool $searchanywhere If true (default), searches in the middle of
 *     names, otherwise only searches at start
 * @param array $extrafields Array of extra user fields to include in search
 * @param array $exclude Array of user ids to exclude (empty = don't exclude)
 * @param array $includeonly If specified, only returns users that have ids
 *     incldued in this array (empty = don't restrict)
 * @return array an array with two elements, a fragment of SQL to go in the
 *     where clause the query, and an associative array containing any required
 *     parameters (using named placeholders).
 */
function courses_search_sql($search, $c = 'c', $searchanywhere = true, array $extrafields = array(),
                            array $exclude = null, array $includeonly = null) {
    global $DB;

    $params = array();
    $tests = array();

    if ($c) {
        $c .= '.';
    }

    // If we have a $search string, put a field LIKE '$search%' condition on each field.
    if ($search) {
        $conditions = array(
            $conditions[] = $c . 'fullname'
        );
        foreach ($extrafields as $field) {
            $conditions[] = $c . $field;
        }
        if ($searchanywhere) {
            $searchparam = '%'.$search.'%';
        } else {
            $searchparam = $search.'%';
        }
        $i = 0;
        foreach ($conditions as $key => $condition) {
            $conditions[$key] = $DB->sql_like($condition, ":con{$i}00", false, false);
            $params["con{$i}00"] = $searchparam;
            $i++;
        }
        $tests[] = '('.implode(' OR ', $conditions).')';
    }

    // If we are being asked to exclude any users, do that.
    if (!empty($exclude)) {
        list($coursetest, $courseparams) = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED, 'ex', false);
        $tests[] = $c.'id '.$coursetest;
        $params = array_merge($params, $courseparams);
    }

    // If we are validating a set list of courseids, add an id IN (...) test.
    if (!empty($includeonly)) {
        list($coursesql, $courseparams) = $DB->get_in_or_equal($includeonly, SQL_PARAMS_NAMED, 'val');
        $tests[] = $c.'id '.$coursesql;
        $params = array_merge($params, $courseparams);
    }

    // In case there are no tests, add one result (this makes it easier to combine
    // this with an existing query as you can always add AND $sql).
    if (empty($tests)) {
        $tests[] = '1 = 1';
    }

    // Combing the conditions and return.
    return array(implode(' AND ', $tests), $params);
}
