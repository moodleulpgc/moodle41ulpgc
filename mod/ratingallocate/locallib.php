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
 * Internal library of functions for module ratingallocate
 *
 * All the ratingallocate specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod_ratingallocate
 * @copyright 2014 M Schulze
 * @copyright  based on code by Stefan Koegel copyright (C) 2013 Stefan Koegel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use core_availability\info_module;
use ratingallocate\db as this_db;

global $CFG;

require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/form_manual_allocation.php');
require_once(dirname(__FILE__) . '/form_modify_choice.php');
require_once(dirname(__FILE__) . '/form_upload_choices.php');
require_once(dirname(__FILE__) . '/renderable.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once(__DIR__ . '/classes/algorithm_status.php');

// Takes care of loading all the solvers.
require_once(dirname(__FILE__) . '/solver/ford-fulkerson-koegel.php');
require_once(dirname(__FILE__) . '/solver/edmonds-karp.php');

// Now come all the strategies.
require_once(dirname(__FILE__) . '/strategy/strategy01_yes_no.php');
require_once(dirname(__FILE__) . '/strategy/strategy02_yes_maybe_no.php');
require_once(dirname(__FILE__) . '/strategy/strategy03_lickert.php');
require_once(dirname(__FILE__) . '/strategy/strategy04_points.php');
require_once(dirname(__FILE__) . '/strategy/strategy05_order.php');
require_once(dirname(__FILE__) . '/strategy/strategy06_tickyes.php');

/**
 * Simulate a static/singleton class that holds all the strategies that registered with him
 */
class strategymanager {

    /** @var array of string-identifier of all registered strategies */
    private static $strategies = array();

    /**
     * Add a strategy to the strategymanager
     * @param string $strategyname
     */
    public static function add_strategy($strategyname) {
        self::$strategies[] = $strategyname;
    }

    /**
     * Get the current list of strategies
     * @return array
     */
    public static function get_strategies() {
        return self::$strategies;
    }

}

define('ACTION_GIVE_RATING', 'give_rating');
define('ACTION_DELETE_RATING', 'delete_rating');
define('ACTION_SHOW_CHOICES', 'show_choices');
define('ACTION_EDIT_CHOICE', 'edit_choice');
define('ACTION_UPLOAD_CHOICES', 'upload_choices');
define('ACTION_ENABLE_CHOICE', 'enable_choice');
define('ACTION_DISABLE_CHOICE', 'disable_choice');
define('ACTION_DELETE_CHOICE', 'delete_choice');
define('ACTION_START_DISTRIBUTION', 'start_distribution');
define('ACTION_DELETE_ALL_RATINGS', 'delete_all_ratings');
define('ACTION_MANUAL_ALLOCATION', 'manual_allocation');
define('ACTION_DISTRIBUTE_UNALLOCATED_FILL', 'distribute_unallocated_fill');
define('ACTION_DISTRIBUTE_UNALLOCATED_EQUALLY', 'distribute_unallocated_equally');
define('ACTION_PUBLISH_ALLOCATIONS', 'publish_allocations'); // Make them displayable for the users.
define('ACTION_SOLVE_LP_SOLVE', 'solve_lp_solve'); // Instead of only generating the mps-file, let it solve.
define('ACTION_SHOW_RATINGS_AND_ALLOCATION_TABLE', 'show_ratings_and_allocation_table');
define('ACTION_SHOW_ALLOCATION_TABLE', 'show_allocation_table');
define('ACTION_SHOW_STATISTICS', 'show_statistics');
define('ACTION_ALLOCATION_TO_GROUPING', 'allocation_to_gropuping');

/**
 * Wrapper for db-record to have IDE autocomplete feature of fields
 * @property int $id
 * @property int $course
 * @property string $name
 * @property string $intro
 * @property string $strategy
 * @property int $accesstimestart
 * @property int $accesstimestop
 * @property int $publishdate
 * @property int $published
 * @property int $notificationsend
 * @property int $runalgorithmbycron
 * @property int $algorithmstarttime
 * @property int $algorithmstatus
 * -1 failure while running algorithm;
 * 0 algorithm has not been running;
 * 1 algorithm running;
 * 2 algorithm finished;
 * @property string $setting
 */
class ratingallocate_db_wrapper {

    /** @var stdClass */
    public $dbrecord;

    /** Emulates the functionality as if there were explicit records by passing them to the original db record
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return $this->dbrecord->{$name};
    }

    public function __construct($record) {
        $this->dbrecord = $record;
    }

}

/**
 * Kapselt eine Instanz von ratingallocate
 *
 * @author max
 *
 */
class ratingallocate {

    /** @var int */
    private $ratingallocateid;

    /** @var ratingallocate_db_wrapper */
    public $ratingallocate;

    /** @var stdClass original db_record of this instance */
    private $origdbrecord;

    /** @var stdClass */
    private $course;

    /** @var stdClass */
    private $coursemodule;

    /** @var context_module */
    private $context;

    /** @var $db moodle_database */
    public $db; // Public because solvers need it, too.

    /**
     * @var mod_ratingallocate_renderer the custom renderer for this module
     */
    protected $renderer;

    const NOTIFY_SUCCESS = 'notifysuccess';
    const NOTIFY_MESSAGE = 'notifymessage';

    /**
     * Returns all users enrolled in the course the ratingallocate is in
     * @throws moodle_exception
     */
    public function get_raters_in_course(): array {

        $modinfo = get_fast_modinfo($this->course);
        $cm = $modinfo->get_cm($this->coursemodule->id);

        $raters = get_enrolled_users($this->context, 'mod/ratingallocate:give_rating');
        $info = new info_module($cm);
        // Only show raters who had the ability to access this activity. This funktion ignores the visibility setting,
        // so the ratings and allocations are still shown, even when the activity is hidden
        $filteredraters = $info->filter_user_list($raters);

        return $filteredraters;
    }

    /**
     * Get candidate groups for restricting choices.
     *
     * @return array A mapping of group IDs to names.
     */
    public function get_group_candidates() {
        $options = array();
        $groupcandidates = groups_get_all_groups($this->course->id);
        foreach ($groupcandidates as $group) {
            $options[$group->id] = $group->name;
        }
        return $options;
    }

    public function __construct($ratingallocaterecord, $course, $coursem, context_module $context) {
        global $DB;
        $this->db = &$DB;

        $this->origdbrecord = $ratingallocaterecord;
        $this->ratingallocate = new ratingallocate_db_wrapper($ratingallocaterecord);
        $this->ratingallocateid = $this->ratingallocate->id;
        $this->course = $course;
        $this->coursemodule = $coursem;
        $this->context = $context;
    }

    /**
     * @return string
     * @throws coding_exception
     */
    private function process_action_start_distribution() {
        global $DB, $PAGE;
        // Process form: Start distribution and call default page after finishing.
        if (has_capability('mod/ratingallocate:start_distribution', $this->context)) {

            if ($this->get_algorithm_status() === \mod_ratingallocate\algorithm_status::RUNNING) {
                // Don't run, if an instance is already running.
                redirect(new moodle_url('/mod/ratingallocate/view.php',
                        array('id' => $this->coursemodule->id)),
                        get_string('algorithm_already_running', RATINGALLOCATE_MOD_NAME),
                        null,
                        \core\output\notification::NOTIFY_INFO);
            } else if ($this->ratingallocate->runalgorithmbycron === "1" &&
                    $this->get_algorithm_status() === \mod_ratingallocate\algorithm_status::NOTSTARTED
            ) {
                // Don't run, if the cron has not started yet, but is set as priority.
                redirect(new moodle_url('/mod/ratingallocate/view.php',
                        array('id' => $this->coursemodule->id)),
                        get_string('algorithm_scheduled_for_cron', RATINGALLOCATE_MOD_NAME),
                        null,
                        \core\output\notification::NOTIFY_INFO);
            } else {
                $this->origdbrecord->{this_db\ratingallocate::ALGORITHMSTATUS} = \mod_ratingallocate\algorithm_status::RUNNING;
                $DB->update_record(this_db\ratingallocate::TABLE, $this->origdbrecord);
                // Try to get some more memory, 500 users in 10 groups take about 15mb.
                raise_memory_limit(MEMORY_EXTRA);
                core_php_time_limit::raise();
                // Distribute choices.
                $timeneeded = $this->distrubute_choices();

                // Logging.
                $event = \mod_ratingallocate\event\distribution_triggered::create_simple(
                        context_module::instance($this->coursemodule->id), $this->ratingallocateid, $timeneeded);
                $event->trigger();

                redirect(new moodle_url($PAGE->url->out()),
                        get_string('distribution_saved', RATINGALLOCATE_MOD_NAME, $timeneeded),
                        null,
                        \core\output\notification::NOTIFY_SUCCESS);
            }
        }
        redirect(new moodle_url('/mod/ratingallocate/view.php',
                array('id' => $this->coursemodule->id)));
        return;
    }

    private function delete_all_student_ratings() {
        global $USER;
        // Disallow to delete ratings for students and tutors.
        if (!has_capability('mod/ratingallocate:start_distribution', $this->context, null, false)) {
            redirect(new moodle_url('/mod/ratingallocate/view.php', array('id' => $this->coursemodule->id)),
                get_string('error_deleting_all_insufficient_permission', RATINGALLOCATE_MOD_NAME));
            return;
        }
        // Disallow deletion when there can't be new ratings submitted
        $status = $this->get_status();
        if ($status !== self::DISTRIBUTION_STATUS_RATING_IN_PROGRESS and $status !== self::DISTRIBUTION_STATUS_TOO_EARLY) {
            redirect(new moodle_url('/mod/ratingallocate/view.php', array('id' => $this->coursemodule->id)),
                get_string('error_deleting_all_no_rating_possible', RATINGALLOCATE_MOD_NAME));
            return;
        }
        $this->delete_all_ratings();
        redirect(new moodle_url('/mod/ratingallocate/view.php', array('id' => $this->coursemodule->id)),
            get_string('success_deleting_all', RATINGALLOCATE_MOD_NAME));
    }

    private function process_action_give_rating() {
        global $CFG;

        $output = '';
        /* @var mod_ratingallocate_renderer */
        $renderer = $this->get_renderer();
        // Print data and controls for students, but not for admins.
        if (has_capability('mod/ratingallocate:give_rating', $this->context, null, false)) {
            global $DB, $PAGE, $USER;

            $status = $this->get_status();
            // If no choice option exists WARN!
            if (!$DB->record_exists('ratingallocate_choices', array('ratingallocateid' => $this->ratingallocateid))) {
                $renderer->add_notification(get_string('no_choice_to_rate', RATINGALLOCATE_MOD_NAME));
            } else if ($status === self::DISTRIBUTION_STATUS_RATING_IN_PROGRESS) {
                // Rating is possible...

                // Suche das richtige Formular nach Strategie.
                $strategyform = 'ratingallocate\\' . $this->ratingallocate->strategy . '\\mod_ratingallocate_view_form';

                /* @var $mform moodleform */
                $mform = new $strategyform($PAGE->url->out(), $this);
                $mform->add_action_buttons();

                if ($mform->is_cancelled()) {
                    // Return to view.
                    redirect("$CFG->wwwroot/mod/ratingallocate/view.php?id=" . $this->coursemodule->id);
                    return "";
                } else if ($mform->is_submitted() && $mform->is_validated() && $data = $mform->get_data()) {
                    // Save submitted data and call default page.
                    $this->save_ratings_to_db($USER->id, $data->data);

                    // Return to view.
                    redirect(
                            "$CFG->wwwroot/mod/ratingallocate/view.php?id=" . $this->coursemodule->id,
                            get_string('ratings_saved', RATINGALLOCATE_MOD_NAME),
                            null, \core\output\notification::NOTIFY_SUCCESS
                    );
                }

                $mform->definition_after_data();

                $output .= $renderer->render_ratingallocate_strategyform($mform);
                // Logging.
                $event = \mod_ratingallocate\event\rating_viewed::create_simple(
                        context_module::instance($this->coursemodule->id), $this->ratingallocateid);
                $event->trigger();
            }
        }
        return $output;
    }

    /**
     * Processes the action of a user deleting his rating.
     */
    private function process_action_delete_rating() {
        /* @var mod_ratingallocate_renderer */
        $renderer = $this->get_renderer();
        // Print data and controls for students, but not for admins.
        if (has_capability('mod/ratingallocate:give_rating', $this->context, null, false)) {
            global $USER;

            $status = $this->get_status();
            if ($status === self::DISTRIBUTION_STATUS_RATING_IN_PROGRESS) {
                // Rating is possible...

                $this->delete_ratings_of_user($USER->id);
                $renderer->add_notification(get_string('ratings_deleted', RATINGALLOCATE_MOD_NAME), self::NOTIFY_SUCCESS);

                redirect(new moodle_url('/mod/ratingallocate/view.php',
                        array('id' => $this->coursemodule->id)),
                        get_string('ratings_deleted', RATINGALLOCATE_MOD_NAME),
                        null,
                        \core\output\notification::NOTIFY_SUCCESS);
            }
        }
        redirect(new moodle_url('/mod/ratingallocate/view.php', array('id' => $this->coursemodule->id)));
    }

    private function process_action_show_choices() {

        if (has_capability('mod/ratingallocate:modify_choices', $this->context)) {
            global $OUTPUT;
            /* @var mod_ratingallocate_renderer */
            $renderer = $this->get_renderer();

            // Notifications if no choices exist or too few in comparison to strategy settings.
            $availablechoices = $this->get_rateable_choices();
            $strategysettings = $this->get_strategy_class()->get_static_settingfields();
            if (array_key_exists(ratingallocate\strategy_order\strategy::COUNTOPTIONS, $strategysettings)) {
                $necessarychoices =
                        $strategysettings[ratingallocate\strategy_order\strategy::COUNTOPTIONS][2];
            } else {
                $necessarychoices = 0;
            }
            if (count($availablechoices) < $necessarychoices) {
                $renderer->add_notification(get_string('too_few_choices_to_rate', RATINGALLOCATE_MOD_NAME, $necessarychoices));
            }

            echo $renderer->render_header($this->ratingallocate, $this->context, $this->coursemodule->id);
            echo $OUTPUT->heading(get_string('show_choices_header', RATINGALLOCATE_MOD_NAME));

            $renderer->ratingallocate_show_choices_table($this, true);
            echo $OUTPUT->single_button(new moodle_url('/mod/ratingallocate/view.php',
                    array('id' => $this->coursemodule->id)), get_string('back'), 'get');
            echo $renderer->render_footer();
        }

    }

    private function process_action_edit_choice() {
        global $DB, $PAGE;

        $output = '';
        if (has_capability('mod/ratingallocate:modify_choices', $this->context)) {
            global $OUTPUT;
            $choiceid = optional_param('choiceid', 0, PARAM_INT);

            if ($choiceid) {
                $record = $DB->get_record(this_db\ratingallocate_choices::TABLE, array('id' => $choiceid));
                $choice = new ratingallocate_choice($record);
            } else {
                $choice = null;
            }

            $data = new stdClass();
            $options = array('subdirs' => false, 'maxfiles' => -1, 'accepted_types' => '*', 'return_types' => FILE_INTERNAL);
            file_prepare_standard_filemanager($data, 'attachments', $options, $this->context,
                    'mod_ratingallocate', 'choice_attachment', $choiceid);

            $mform = new modify_choice_form(new moodle_url('/mod/ratingallocate/view.php',
                    array('id' => $this->coursemodule->id,
                            'ratingallocateid' => $this->ratingallocateid,
                            'action' => ACTION_EDIT_CHOICE,
                    )),
                    $this, $choice, array('attachment_data' => $data));

            /* @var mod_ratingallocate_renderer */
            $renderer = $this->get_renderer();

            if ($mform->is_submitted() && $data = $mform->get_submitted_data()) {

                if (!$mform->is_cancelled()) {
                    if ($mform->is_validated()) {
                        // Processing for editor element (FORMAT_HTML is assumed).
                        // Note: No file management implemented at this point.
                        if (is_array($data->explanation)) {
                            $data->explanation = $data->explanation['text'];
                        }

                        $this->save_modify_choice_form($data);

                        $data = file_postupdate_standard_filemanager($data, 'attachments', $options, $this->context,
                                'mod_ratingallocate', 'choice_attachment', $data->choiceid);
                        $renderer->add_notification(get_string("choice_added_notification", RATINGALLOCATE_MOD_NAME),
                                self::NOTIFY_SUCCESS);

                        if ($data->usegroups) {
                            $this->update_choice_groups($data->choiceid, $data->groupselector);
                        }

                    } else {
                        $output .= $OUTPUT->heading(get_string('edit_choice', RATINGALLOCATE_MOD_NAME), 2);
                        $output .= $mform->to_html();
                        return $output;
                    }
                }
                if (object_property_exists($data, 'submitbutton2')) {
                    // If form was submitted using submit2, redirect to the empty edit choice form.
                    redirect(new moodle_url('/mod/ratingallocate/view.php',
                            array('id' => $this->coursemodule->id,
                                    'ratingallocateid' => $this->ratingallocateid,
                                    'action' => ACTION_EDIT_CHOICE, 'next' => true)));
                } else {
                    // If form was submitted using save or cancel, redirect to the choices table.
                    redirect(new moodle_url('/mod/ratingallocate/view.php',
                            array('id' => $this->coursemodule->id, 'action' => ACTION_SHOW_CHOICES)));
                }
            } else {
                $isnext = optional_param('next', false, PARAM_BOOL);
                if ($isnext) {
                    $renderer->add_notification(get_string("choice_added_notification", RATINGALLOCATE_MOD_NAME),
                            self::NOTIFY_SUCCESS);
                }
                $output .= $OUTPUT->heading(get_string('edit_choice', RATINGALLOCATE_MOD_NAME), 2);
                $output .= $mform->to_html();
            }
        }
        return $output;
    }

    /**
     * Upload one or more choices via a CSV file.
     */
    private function process_action_upload_choices() {
        global $DB, $PAGE;

        $output = '';
        if (has_capability('mod/ratingallocate:modify_choices', $this->context)) {
            global $OUTPUT;

            $url = new moodle_url('/mod/ratingallocate/view.php',
                array('id' => $this->coursemodule->id,
                    'ratingallocateid' => $this->ratingallocateid,
                    'action' => ACTION_UPLOAD_CHOICES,
                )
            );
            $mform = new upload_choices_form($url, $this);
            $renderer = $this->get_renderer();

            if ($mform->is_submitted() && $data = $mform->get_submitted_data()) {
                if (!$mform->is_cancelled()) {
                    if ($mform->is_validated()) {
                        $content = $mform->get_file_content('uploadfile');
                        $name = $mform->get_new_filename('uploadfile');
                        $live = !$data->testimport;  // If testing, importer is not live.
                        // Properly process the file content.
                        $choiceimporter = new \mod_ratingallocate\choice_importer($this->ratingallocateid, $this);
                        $importstatus = $choiceimporter->import($content, $live);

                        switch ($importstatus->status) {
                            case \mod_ratingallocate\choice_importer::IMPORT_STATUS_OK:
                                \core\notification::info($importstatus->status_message);
                                break;
                            case \mod_ratingallocate\choice_importer::IMPORT_STATUS_DATA_ERROR:
                                \core\notification::warning($importstatus->status_message);
                                $choiceimporter->issue_notifications($importstatus->errors);
                                break;
                            case \mod_ratingallocate\choice_importer::IMPORT_STATUS_SETUP_ERROR:
                            default:
                                \core\notification::error($importstatus->status_message);
                                $choiceimporter->issue_notifications($importstatus->errors,
                                    \core\output\notification::NOTIFY_ERROR);
                        }

                        redirect(new moodle_url('/mod/ratingallocate/view.php',
                            array(
                                'id' => $this->coursemodule->id,
                                'action' => ACTION_SHOW_CHOICES
                            )));
                    }
                }
            }

            $output .= $OUTPUT->heading(get_string('upload_choices', 'ratingallocate'), 2);
            $output .= $mform->to_html();
        }
        return $output;
    }

    /**
     * Enables or disables a choice and displays the choices list.
     * @param bool $active states if the choice should be set active or inavtive
     */
    private function process_action_enable_choice($active) {
        if (has_capability('mod/ratingallocate:modify_choices', $this->context)) {
            global $DB;
            $choiceid = optional_param('choiceid', 0, PARAM_INT);

            if ($choiceid) {
                $DB->set_field(this_db\ratingallocate_choices::TABLE,
                        this_db\ratingallocate_choices::ACTIVE,
                        $active,
                        array('id' => $choiceid));
            }
            redirect(new moodle_url('/mod/ratingallocate/view.php',
                    array('id' => $this->coursemodule->id, 'action' => ACTION_SHOW_CHOICES)));
        }
    }

    /**
     * Deletes a choice and displays the choices list.
     */
    private function process_action_delete_choice() {
        if (has_capability('mod/ratingallocate:modify_choices', $this->context)) {
            global $DB;
            $choiceid = optional_param('choiceid', 0, PARAM_INT);

            if ($choiceid) {
                $choice = $DB->get_record(this_db\ratingallocate_choices::TABLE, array('id' => $choiceid));
                if ($choice) {
                    // Delete related group associations, if any.
                    $DB->delete_records(this_db\ratingallocate_group_choices::TABLE, ['choiceid' => $choiceid]);
                    $DB->delete_records(this_db\ratingallocate_ch_gengroups::TABLE, ['choiceid' => $choiceid]);
                    $DB->delete_records(this_db\ratingallocate_choices::TABLE, array('id' => $choiceid));

                    redirect(new moodle_url('/mod/ratingallocate/view.php',
                            array('id' => $this->coursemodule->id, 'action' => ACTION_SHOW_CHOICES)),
                            get_string('choice_deleted_notification', RATINGALLOCATE_MOD_NAME,
                                    $choice->{this_db\ratingallocate_choices::TITLE}),
                            null,
                            \core\output\notification::NOTIFY_SUCCESS);
                } else {
                    redirect(new moodle_url('/mod/ratingallocate/view.php',
                            array('id' => $this->coursemodule->id, 'action' => ACTION_SHOW_CHOICES)),
                            get_string('choice_deleted_notification_error', RATINGALLOCATE_MOD_NAME),
                            null,
                            \core\output\notification::NOTIFY_ERROR);
                }
            }
            redirect(new moodle_url('/mod/ratingallocate/view.php',
                    array('id' => $this->coursemodule->id, 'action' => ACTION_SHOW_CHOICES)));
        }
    }

    private function process_action_manual_allocation() {
        // Manual allocation.
        $output = '';
        if (has_capability('mod/ratingallocate:start_distribution', $this->context)) {
            global $OUTPUT, $PAGE;

            $mform = new manual_alloc_form($PAGE->url, $this);
            $notification = '';
            $notificationtype = null;

            if (!$mform->no_submit_button_pressed() && $data = $mform->get_submitted_data()) {
                if (!$mform->is_cancelled()) {
                    /* @var mod_ratingallocate_renderer */
                    $renderer = $this->get_renderer();
                    $status = $this->get_status();
                    if ($status === self::DISTRIBUTION_STATUS_TOO_EARLY ||
                            $status === self::DISTRIBUTION_STATUS_RATING_IN_PROGRESS) {
                        $notification = get_string('modify_allocation_group_desc_' . $status, RATINGALLOCATE_MOD_NAME);
                        $notificationtype = \core\output\notification::NOTIFY_WARNING;
                    } else {
                        $allocationdata = optional_param_array('allocdata', array(), PARAM_INT);
                        if ($userdata = optional_param_array('userdata', null, PARAM_INT)) {
                            $this->save_manual_allocation_form($allocationdata, $userdata);
                            $notification = get_string('manual_allocation_saved', RATINGALLOCATE_MOD_NAME);
                            $notificationtype = \core\output\notification::NOTIFY_SUCCESS;
                        } else {
                            $notification = get_string('manual_allocation_nothing_to_be_saved', RATINGALLOCATE_MOD_NAME);
                            $notificationtype = \core\output\notification::NOTIFY_INFO;
                        }
                    }
                } else {
                    redirect(new moodle_url('/mod/ratingallocate/view.php',
                            array('id' => $this->coursemodule->id)));
                }
                // If form was submitted using save or cancel, retirect to the default page.
                if (property_exists($data, "submitbutton")) {
                    if ($notification) {
                        redirect(new moodle_url('/mod/ratingallocate/view.php',
                                array('id' => $this->coursemodule->id)), $notification, null, $notificationtype);

                    } else {
                        redirect(new moodle_url('/mod/ratingallocate/view.php',
                                array('id' => $this->coursemodule->id)));
                    }
                    // If the save and continue button was pressed,
                    // redirect to the manual allocation form to refresh the checked radiobuttons.
                } else if (property_exists($data, "submitbutton2")) {
                    if ($notification) {
                        redirect(new moodle_url('/mod/ratingallocate/view.php',
                                array('id' => $this->coursemodule->id, 'action' => ACTION_MANUAL_ALLOCATION)), $notification, null,
                                $notificationtype);

                    } else {
                        redirect(new moodle_url('/mod/ratingallocate/view.php',
                                array('id' => $this->coursemodule->id, 'action' => ACTION_MANUAL_ALLOCATION)));
                    }
                }
            }
            $output .= $OUTPUT->heading(get_string('manual_allocation', RATINGALLOCATE_MOD_NAME), 2);

            $output .= $mform->to_html();
            $this->showinfo = false;
        }
        return $output;
    }

    /**
     * Retrieve all used groups in rateable choices.
     *
     * @return array of group ids used in rateable choices
     */
    public function get_all_groups_of_choices(): array {
        $rateablechoiceswithgrouprestrictions = array_filter($this->get_rateable_choices(),
                fn($choice) => !empty($choice->usegroups) && !empty($this->get_choice_groups($choice->id)));
        $rateablechoiceids = array_map(fn($choice) => $choice->id, $rateablechoiceswithgrouprestrictions);
        $groupids = [];
        foreach ($rateablechoiceids as $choiceid) {
            $groupids = array_merge($groupids, array_map(fn($group) => $group->id, $this->get_choice_groups($choiceid)));
        }
        return array_unique($groupids);
    }

    /**
     * Helper method returning an array of groupids belonging to the groups the user is member in.
     *
     * If the user is not a member of any group an empty array is being returned. Only group ids of groups defined in the
     * choices restrictions are being considered here.
     *
     * @param int $userid the id of the user we want to get the group ids he/she belongs to
     * @return array of group ids the user belongs to, not including groups which are not specified in at least one of the choices'
     *  group restrictions
     */
    public function get_user_groupids(int $userid): array {
        $groups = groups_get_user_groups($this->ratingallocate->course, $userid)[0];
        if (empty($groups)) {
            return [];
        } else {
            return array_filter($groups, fn($group) => in_array($group, $this->get_all_groups_of_choices()));
        }
    }

    /**
     * Helper function to retrieve undistributed users.
     *
     * This function returns an associative array [groupcount => [ users ]], groupcount meaning the amount of groups (used in
     *  ratingallocate choices) the users are member of.
     *
     * @return array Associative array [groupcount => [ users ]]
     */
    private function get_undistributed_users_with_groupscount(): array {
        $cachedallocations = $this->get_allocations();
        $raters = $this->get_raters_in_course();
        $undistributedusers = array_map(fn($user) => $user->id, array_values(array_filter($raters,
            fn($user) => !in_array($user->id, array_keys($cachedallocations)))));

        $undistributeduserswithgroups = [];
        foreach ($undistributedusers as $user) {
            $undistributeduserswithgroups[count($this->get_user_groupids($user))][] = $user;
        }
        return $undistributeduserswithgroups;
    }

    /**
     * Returns an array of all userids of users which do not have an allocation (yet).
     *
     * This array will be sorted: Users with fewer memberships in groups used in the choices will come first. Exception:
     * Users without group membership (groups count 0) are at the end of the array.
     *
     * @return array Array of user ids not having an allocation
     */
    public function get_undistributed_users(): array {
        $undistributedusers = [];
        $userswithgroups = $this->get_undistributed_users_with_groupscount();
        if (empty($userswithgroups)) {
            return [];
        }
        for ($i = 1; $i <= max(array_keys($userswithgroups)); $i++) {
            if (empty($userswithgroups[$i])) {
                continue;
            }
            $undistributedusers = array_merge($undistributedusers, $userswithgroups[$i]);
        }
        if (!empty($userswithgroups[0])) {
            $undistributedusers = array_merge($undistributedusers, $userswithgroups[0]);
        }
        return $undistributedusers;
    }

    /**
     * Function to retrieve the next choice which an undistributed user should be assigned to.
     *
     * @param string $distributionalgorithm the algorithm which should be applied to search for the next choice
     * @param int $userid the userid of the user for which the next choice should be retrieved
     * @return int id of the choice the given user should be assigned to, returns -1 if no valid choice
     *  for the user could be found, returns -2 if there are no places left to assign any user
     * @throws dml_exception
     */
    public function get_next_choice_to_assign_user(string $distributionalgorithm, int $userid): int {
        global $DB;

        $placesleft = [];
        // Due to performance reasons we need to save some database query results to avoid multiple inefficient queries.
        $cachedusergroupids = $this->get_user_groupids($userid);
        $cachedundistributedusers = $this->get_undistributed_users();
        $cachedallocations = $this->get_allocations();
        $cachedchoices = [];
        foreach ($this->get_rateable_choices() as $choice) {
            $cachedchoices[$choice->id] = $choice;
            $placesleft[$choice->id] = $choice->maxsize -
                count(array_filter($cachedallocations, fn($allocation) => $allocation->choiceid == $choice->id));
        }

        // We have to remove the choices which are already maxed out.
        $placesleft = array_filter($placesleft, fn($numberoffreeplaces) => $numberoffreeplaces != 0);

        // Early exit if there are no choices with places left. We return -2 to signal the calling function that
        // *independently* from the userid (we have not calculated anything userid specific until here) there are no
        // choices with free places left.
        if (empty($placesleft)) {
            return -2;
        }

        // Filter choices the user cannot be assigned to.
        foreach (array_keys($placesleft) as $choiceid) {
            $choice = $DB->get_record('ratingallocate_choices', ['id' => $choiceid]);
            if (empty($choice->usegroups)) {
                // If we have a group without group restrictions it will always be available.
                continue;
            }
            $choicegroups = $this->get_choice_groups($choiceid);
            if (empty($choicegroups)) {
                // If we have a group with group restrictions enabled, but without groups defined, no user
                // can ever be assigned, so remove it.
                unset($placesleft[$choiceid]);
                continue;
            }
            // So only choices with 'proper' group restrictions are left now.
            $groupidsofcurrentchoice = array_map(fn($group) => $group->id, $choicegroups);
            $intersectinggroupids = array_intersect($cachedusergroupids, $groupidsofcurrentchoice);
            if (empty($intersectinggroupids)) {
                // If the user is not in one of the groups of the current choice, we remove the choice from possibles choices.
                unset($placesleft[$choiceid]);
            }
        }

        // At this point $placesleft only contains choices the user can be assigned to.
        if (empty($placesleft)) {
            // If we have no choice to assign, we return -1 to signal the algorithm that we cannot assign the user.
            return -1;
        }

        // We now have to decide which choice id will be returned as the one the user will be assigned to.
        // In case of "equal distribution" we have to fake the amount of available places first.
        if ($distributionalgorithm == ACTION_DISTRIBUTE_UNALLOCATED_EQUALLY) {
            $userstodistributecount = count($cachedundistributedusers);
            $freeplacescount = array_reduce($placesleft, fn($a, $b) => $a + $b);

            $freeplacesoverhang = $freeplacescount - $userstodistributecount;

            if ($freeplacesoverhang > 0) {
                // Only if there are more free places than users to distribute, we want to distribute "equally".
                // Choices with more places left should be targeted first when reducing places left.
                arsort($placesleft);
                $i = 0;
                $choicesmaxed = [];
                // We now lower each count of available places in each choice for every additional place that we have altogether
                // than users to still distribute.
                while ($freeplacesoverhang > 0 && count(array_unique($choicesmaxed)) < count($placesleft)) {
                    // Second condition means that we will stop if we failed trying to reduce *every* choice.
                    $nextchoiceid = array_keys($placesleft)[$i];
                    if ($placesleft[$nextchoiceid] > 0) {
                        // If we can still lower it, we do it.
                        $placesleft[$nextchoiceid] = $placesleft[$nextchoiceid] - 1;
                        $freeplacesoverhang--;
                    } else {
                        // If we cannot lower the places left anymore for this choice, we track that and will try to lower the
                        // available places for the next one instead.
                        $choicesmaxed[] = $nextchoiceid;
                    }
                    $i++;
                    // We are iterating over all the choices constantly and try to reduce the available places.
                    $i = $i % count($placesleft);
                }
                // We recalculated the left places for each choice, so we have to remove the choices which are now maxed out.
                $placesleft = array_filter($placesleft, fn($numberoffreeplaces) => $numberoffreeplaces != 0);
            }
        }

        // From here on it's just the algorithm 'distribute by filling up'.
        $possiblechoices = $placesleft;

        $choicessortedwithgroupscount = [];
        $choicessorted = [];
        foreach (array_keys($possiblechoices) as $choiceid) {
            $choice = $DB->get_record('ratingallocate_choices', ['id' => $choiceid]);
            // In case group restrictions are disabled for a choice that choice could still could have groups assigned.
            // However, we need to treat them like they do not have any groups.
            $groupscount = empty($choice->usegroups) ? 0 : count($this->get_choice_groups($choiceid));
            $choicessortedwithgroupscount[$groupscount][] = $choiceid;
        }
        foreach ($choicessortedwithgroupscount as &$choiceswithcertaingroupcount) {
            usort($choiceswithcertaingroupcount, function($a, $b) use ($placesleft) {
                // Choices with the same amount of groups are sorted according the count of left places: fewer places first.
                return $placesleft[$a] - $placesleft[$b];
            });
        }
        for ($i = 1; $i <= max(array_keys($choicessortedwithgroupscount)); $i++) {
            if (empty($choicessortedwithgroupscount[$i])) {
                continue;
            }
            $choicessorted = array_merge($choicessorted, $choicessortedwithgroupscount[$i]);
        }
        if (!empty($choicessortedwithgroupscount[0])) {
            $choicessorted = array_merge($choicessorted, $choicessortedwithgroupscount[0]);
        }

        // This is kind of a dilemma. We want the choices to be filled up beginning at the one with the least places left to fill it
        // up as quickly as possible.
        // However, in case of group restrictions this will lead to problems as we are assigning users which have to be assigned to
        // specific choices (because all others cannot be assigned to it). So in the end these choices will not be available when
        // we arrive at the choice with the group restrictions.
        // Therefore, we are first filling up choices with group restrictions first (beginning at choices with fewer groups). Only
        // in case we have the same amount of groups for two choices or we have no group restrictions at all we pick choices with
        // fewer places left first (see foreach loop with usort a few lines above).

        return !empty($choicessorted) ? array_shift($choicessorted) : -1;
    }

    /**
     * Try to distribute all currently unallocated users.
     *
     * @param string $distributionalgorithm the distributionalgorithm which should be used, you can choose between
     *  ACTION_DISTRIBUTE_UNALLOCATED_EQUALLY and ACTION_DISTRIBUTE_UNALLOCATED_FILL
     * @return void
     * @throws dml_exception
     */
    public function distribute_users_without_choice(string $distributionalgorithm): void {
        // This could need some extra memory, especially because we are caching some data structures in memory while
        // running the algorithm.
        raise_memory_limit(MEMORY_EXTRA);
        core_php_time_limit::raise();

        // This will retrieve a list of users we are trying to assign to choices, sorted from the least group membership count to
        // more group memberships. Users without group memberships will be at the end of the array.
        $possibleusers = $this->get_undistributed_users();

        $transaction = $this->db->start_delegated_transaction();

        $usertoassign = array_shift($possibleusers);
        // As long as we have a user to assign, we try to assign him.
        while ($usertoassign != null) {

            // Calculate the choice to assign the user to depending on the given algorithm.
            $choicetoassign = $this->get_next_choice_to_assign_user($distributionalgorithm, $usertoassign);
            if ($choicetoassign === -2) {
                // This means there are no free places left in any choice for any user, so we can stop the algorithm
                // as a whole.
                break;
            } else if ($choicetoassign == -1) {
                // This means that the user could not be assigned (for example due to group restrictions),
                // so we try the next one.
                $usertoassign = array_shift($possibleusers);
                continue;
            }
            $this->add_allocation($choicetoassign, $usertoassign);
            $usertoassign = array_shift($possibleusers);
        }
        // At this point we tried to assign all the users. It is possible that users remain undistributed, though.

        $transaction->allow_commit();
    }

    private function process_action_show_ratings_and_alloc_table() {
        $output = '';
        // Print ratings table.
        if (has_capability('mod/ratingallocate:start_distribution', $this->context)) {
            global $OUTPUT;
            /* @var mod_ratingallocate_renderer */
            $renderer = $this->get_renderer();
            $output .= $renderer->ratings_table_for_ratingallocate($this->get_rateable_choices(),
                    $this->get_ratings_for_rateable_choices(), $this->get_raters_in_course(),
                    $this->get_allocations(), $this);

            $output .= html_writer::empty_tag('br', array());
            $output .= $OUTPUT->single_button(new moodle_url('/mod/ratingallocate/view.php', array(
                    'id' => $this->coursemodule->id)), get_string('back'), 'get');

            // Logging.
            $event = \mod_ratingallocate\event\ratings_and_allocation_table_viewed::create_simple(
                    context_module::instance($this->coursemodule->id), $this->ratingallocateid);
            $event->trigger();
        }
        return $output;
    }

    private function process_action_show_allocation_table() {
        $output = '';
        // Print ratings table.
        if (has_capability('mod/ratingallocate:start_distribution', $this->context)) {
            global $OUTPUT;
            /* @var mod_ratingallocate_renderer */
            $renderer = $this->get_renderer();

            $output .= $renderer->allocation_table_for_ratingallocate($this);

            $output .= html_writer::empty_tag('br', array());
            $output .= $OUTPUT->single_button(new moodle_url('/mod/ratingallocate/view.php',
                    array('id' => $this->coursemodule->id)), get_string('back'), 'get');
            // Logging.
            $event = \mod_ratingallocate\event\allocation_table_viewed::create_simple(
                    context_module::instance($this->coursemodule->id), $this->ratingallocateid);
            $event->trigger();
        }
        return $output;
    }

    private function process_action_show_statistics() {
        $output = '';
        // Print ratings table.
        if (has_capability('mod/ratingallocate:start_distribution', $this->context)) {
            global $OUTPUT;
            /* @var mod_ratingallocate_renderer */
            $renderer = $this->get_renderer();

            $output .= $renderer->statistics_table_for_ratingallocate($this);

            $output .= html_writer::empty_tag('br', array());
            $output .= $OUTPUT->single_button(new moodle_url('/mod/ratingallocate/view.php',
                    array('id' => $this->coursemodule->id)), get_string('back'), 'get');
            // Logging.
            $event = \mod_ratingallocate\event\allocation_statistics_viewed::create_simple(
                    context_module::instance($this->coursemodule->id), $this->ratingallocateid);
            $event->trigger();
        }
        return $output;
    }

    private function process_publish_allocations() {
        $status = $this->get_status();
        if ($status === self::DISTRIBUTION_STATUS_READY_ALLOC_STARTED) {

            $this->publish_allocation();

            redirect(new moodle_url('/mod/ratingallocate/view.php',
                    array('id' => $this->coursemodule->id)),
                    get_string('distribution_published', RATINGALLOCATE_MOD_NAME),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS);
        }

        redirect(new moodle_url('/mod/ratingallocate/view.php',
                array('id' => $this->coursemodule->id)));
    }

    private function process_action_allocation_to_grouping() {
        $this->synchronize_allocation_and_grouping();

        redirect(new moodle_url('/mod/ratingallocate/view.php',
                array('id' => $this->coursemodule->id)),
                get_string('moodlegroups_created', RATINGALLOCATE_MOD_NAME),
                null,
                \core\output\notification::NOTIFY_SUCCESS);
    }

    private function process_default() {
        global $OUTPUT;
        $output = '';
        /* @var mod_ratingallocate_renderer */
        $renderer = $this->get_renderer();
        $status = $this->get_status();
        if (has_capability('mod/ratingallocate:give_rating', $this->context, null, false)) {
            if ($status === self::DISTRIBUTION_STATUS_RATING_IN_PROGRESS) {
                if ($this->is_setup_ok()) {
                    $output .= $OUTPUT->single_button(new moodle_url('/mod/ratingallocate/view.php',
                            array('id' => $this->coursemodule->id,
                                    'action' => ACTION_GIVE_RATING)),
                            get_string('edit_rating', RATINGALLOCATE_MOD_NAME), 'get');

                    $output .= $OUTPUT->single_button(new moodle_url('/mod/ratingallocate/view.php',
                            array('id' => $this->coursemodule->id,
                                    'action' => ACTION_DELETE_RATING)),
                            get_string('delete_rating', RATINGALLOCATE_MOD_NAME), 'get');
                } else {
                    $renderer->add_notification(get_string('no_rating_possible', RATINGALLOCATE_MOD_NAME));
                }
            }
        }
        // Print data and controls to edit the choices.
        if (has_capability('mod/ratingallocate:modify_choices', $this->context)) {
            $output .= $renderer->modify_choices_group($this->ratingallocateid, $this->coursemodule->id, $status);
        }

        // Print data and controls for teachers.
        if (has_capability('mod/ratingallocate:start_distribution', $this->context)) {
            $undistributeduserscount = count($this->get_undistributed_users());
            $output .= $renderer->modify_allocation_group($this->ratingallocateid, $this->coursemodule->id, $status,
                $undistributeduserscount, (int) $this->ratingallocate->algorithmstatus,
                (boolean) $this->ratingallocate->runalgorithmbycron);
            $output .= $renderer->publish_allocation_group($this->ratingallocateid, $this->coursemodule->id, $status);
            $output .= $renderer->reports_group($this->ratingallocateid, $this->coursemodule->id, $status, $this->context);
        }

        // Logging.
        $event = \mod_ratingallocate\event\ratingallocate_viewed::create_simple(
                context_module::instance($this->coursemodule->id), $this->ratingallocateid);
        $event->trigger();

        return $output;
    }

    // States if the ratingallocate info schould be displayed.
    private $showinfo = true;

    /**
     * This is what the view.php calls to make the output
     */
    public function handle_view() {
        global $PAGE, $USER;
        $action = optional_param('action', '', PARAM_TEXT);

        $PAGE->set_cacheable(false); // TODO necessary.

        // Output starts here.
        $output = '';

        /* @var mod_ratingallocate_renderer */
        $renderer = $this->get_renderer();

        switch ($action) {
            case ACTION_START_DISTRIBUTION:
                $output .= $this->process_action_start_distribution();
                break;

            case ACTION_GIVE_RATING:
                $output .= $this->process_action_give_rating();
                $this->showinfo = false;
                break;

            case ACTION_DELETE_RATING:
                $this->process_action_delete_rating();
                break;

            case ACTION_DELETE_ALL_RATINGS:
                $this->delete_all_student_ratings();
                break;

            case ACTION_SHOW_CHOICES:
                $this->process_action_show_choices();
                return "";

            case ACTION_EDIT_CHOICE:
                $result = $this->process_action_edit_choice();
                if (!$result) {
                    return "";
                }
                $output .= $result;
                $this->showinfo = false;
                break;

            case ACTION_UPLOAD_CHOICES:
                $result = $this->process_action_upload_choices();
                if (!$result) {
                    return "";
                }
                $output .= $result;
                $this->showinfo = false;
                break;

            case ACTION_ENABLE_CHOICE:
                $this->process_action_enable_choice(true);
                return "";

            case ACTION_DISABLE_CHOICE:
                $this->process_action_enable_choice(false);
                return "";

            case ACTION_DELETE_CHOICE:
                $this->process_action_delete_choice();
                return "";

            case ACTION_PUBLISH_ALLOCATIONS:
                $this->process_publish_allocations();
                break;

            case ACTION_ALLOCATION_TO_GROUPING:
                $this->process_action_allocation_to_grouping();
                break;

            case ACTION_MANUAL_ALLOCATION:
                $output .= $this->process_action_manual_allocation();
                break;

            case ACTION_DISTRIBUTE_UNALLOCATED_EQUALLY:
            case ACTION_DISTRIBUTE_UNALLOCATED_FILL:
                $this->distribute_users_without_choice($action);
                redirect(new moodle_url('/mod/ratingallocate/view.php', ['id' => $this->coursemodule->id]),
                    get_string('unassigned_users_assigned', RATINGALLOCATE_MOD_NAME),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS);
                break;

            case ACTION_SHOW_RATINGS_AND_ALLOCATION_TABLE:
                $output .= $this->process_action_show_ratings_and_alloc_table();
                $this->showinfo = false;
                break;

            case ACTION_SHOW_ALLOCATION_TABLE:
                $output .= $this->process_action_show_allocation_table();
                $this->showinfo = false;
                break;

            case ACTION_SHOW_STATISTICS:
                $output .= $this->process_action_show_statistics();
                $this->showinfo = false;
                break;

            default:
                $output .= $this->process_default();
        }

        if ($this->showinfo) {
            $choicestatus = new ratingallocate_choice_status();
            $choicestatus->accesstimestart = $this->ratingallocate->accesstimestart;
            $choicestatus->accesstimestop = $this->ratingallocate->accesstimestop;
            $choicestatus->publishdate = $this->ratingallocate->publishdate;
            $choicestatus->is_published = $this->ratingallocate->published;
            $choicestatus->available_choices = $this->get_rateable_choices();
            // Filter choices to display by groups, where 'usegroups' is true.
            $choicestatus->available_choices = $this->filter_choices_by_groups($choicestatus->available_choices, $USER->id);

            $strategysettings = $this->get_strategy_class()->get_static_settingfields();
            if (array_key_exists(ratingallocate\strategy_order\strategy::COUNTOPTIONS, $strategysettings)) {
                $choicestatus->necessary_choices =
                        $strategysettings[ratingallocate\strategy_order\strategy::COUNTOPTIONS][2];
            } else {
                $choicestatus->necessary_choices = 0;
            }
            $choicestatus->own_choices = $this->get_rating_data_for_user($USER->id);
            // Filter choices to display by groups, where 'usegroups' is true.
            $choicestatus->own_choices = $this->filter_choices_by_groups($choicestatus->own_choices, $USER->id);
            $choicestatus->allocations = $this->get_allocations_for_user($USER->id);
            $choicestatus->strategy = $this->get_strategy_class();
            $choicestatus->show_distribution_info = has_capability('mod/ratingallocate:start_distribution', $this->context);
            $choicestatus->show_user_info = has_capability('mod/ratingallocate:give_rating', $this->context, null, false);
            $choicestatus->algorithmstarttime = $this->ratingallocate->algorithmstarttime;
            $choicestatus->algorithmstatus = $this->get_algorithm_status();
            $choicestatusoutput = $renderer->render($choicestatus);
        } else {
            $choicestatusoutput = "";
        }

        $header = $renderer->render_header($this->ratingallocate, $this->context, $this->coursemodule->id);
        $footer = $renderer->render_footer();
        return $header . $choicestatusoutput . $output . $footer;
    }

    /**
     * Returns the number of all users that placed a rating on the current ratingallocate activity.
     * @param int $courseid course id
     * @return int
     */
    public function get_number_of_active_raters() {
        $sql = 'SELECT COUNT(DISTINCT ra_ratings.userid) AS number
                FROM {ratingallocate} ra INNER JOIN {ratingallocate_choices} ra_choices
                ON ra.id = ra_choices.ratingallocateid INNER JOIN {ratingallocate_ratings} ra_ratings
                ON ra_choices.id = ra_ratings.choiceid
                WHERE ra.course = :courseid AND ra.id = :ratingallocateid';
        $numberofratersfromdb = $this->db->get_field_sql($sql, array(
                'courseid' => $this->course->id, 'ratingallocateid' => $this->ratingallocateid));
        return (int) $numberofratersfromdb;
    }

    /**
     * Returns all ratings for active choices
     */
    public function get_ratings_for_rateable_choices() {
        $sql = 'SELECT r.*
                FROM {ratingallocate_choices} c
                JOIN {ratingallocate_ratings} r
                  ON c.id = r.choiceid
                WHERE c.ratingallocateid = :ratingallocateid AND c.active = 1';

        $ratings = $this->db->get_records_sql($sql, array(
                'ratingallocateid' => $this->ratingallocateid
        ));
        $raters = $this->get_raters_in_course();

        // Filter out everyone who can't give ratings.
        $fromraters = array_filter($ratings, function($rating) use ($raters) {
            return array_key_exists($rating->userid, $raters);
        });

        return $fromraters;
    }

    /**
     * distribution of choices for each user
     * take care about max_execution_time and memory_limit
     */
    public function distrubute_choices() {
        require_capability('mod/ratingallocate:start_distribution', $this->context);

        // Set algorithm status to running.
        $this->origdbrecord->algorithmstatus = \mod_ratingallocate\algorithm_status::RUNNING;
        $this->origdbrecord->algorithmstarttime = time();
        $this->db->update_record(this_db\ratingallocate::TABLE, $this->origdbrecord);

        $distributor = new solver_edmonds_karp();
        // $distributor = new solver_ford_fulkerson();
        $timestart = microtime(true);
        $distributor->distribute_users($this);
        $timeneeded = (microtime(true) - $timestart);
        // echo memory_get_peak_usage();

        // Set algorithm status to finished.
        $this->origdbrecord->algorithmstatus = \mod_ratingallocate\algorithm_status::FINISHED;
        $this->db->update_record(this_db\ratingallocate::TABLE, $this->origdbrecord);

        return $timeneeded;
    }

    /**
     * Creates moodle groups from the current ratingallocate allocation or synchronizes the group user assignments
     * based on the current allocation.
     * @throws coding_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */
    public function synchronize_allocation_and_grouping() {
        require_capability('moodle/course:managegroups', $this->context);

        // Search if there is already a grouping from us.
        if (!$groupingids = $this->db->get_record(this_db\ratingallocate_groupings::TABLE,
            array('ratingallocateid' => $this->ratingallocateid),
            'groupingid')) {
            // Create grouping.
            $data = new stdClass();
            $data->name = get_string('groupingname', RATINGALLOCATE_MOD_NAME, $this->ratingallocate->name);
            $data->courseid = $this->course->id;
            $groupingid = groups_create_grouping($data);

            // Insert groupingid and ratingallocateid into the table.
            $data = new stdClass();
            $data->groupingid = $groupingid;
            $data->ratingallocateid = $this->ratingallocateid;
            $this->db->insert_record(this_db\ratingallocate_groupings::TABLE, $data);

        } else {
            // If there is already a grouping for this allocation assign the corresponing id to groupingid.
            $groupingid = $groupingids->groupingid;
        }

        $choices = $this->get_choices_with_allocationcount();

        // Loop through existing choices.
        foreach ($choices as $choice) {
            if ($this->db->record_exists(this_db\ratingallocate_choices::TABLE,
                    ['id' => $choice->id])) {

                // Checks if there is already a group for this choice.

                if ($groupids = $this->db->get_record(this_db\ratingallocate_ch_gengroups::TABLE,
                    array('choiceid' => $choice->id),
                    'groupid')) {

                    $groupid = $groupids->groupid;
                    $group = groups_get_group($groupid);

                    // Delete all the members from the existing group for this choice.
                    if ($group) {
                        groups_delete_group_members_by_group($group->id);
                        groups_assign_grouping($groupingid, $group->id);
                    }

                } else {
                    // If the group for this choice does not exist yet, create it.
                    $data = new stdClass();
                    $data->courseid = $this->course->id;
                    $data->name = $choice->title;
                    $createdid = groups_create_group($data);
                    if ($createdid) {
                        groups_assign_grouping($groupingid, $createdid);

                        // Insert the mapping between group and choice into the Table.
                        $this->db->insert_record(this_db\ratingallocate_ch_gengroups::TABLE,
                            ['choiceid' => $choice->id, 'groupid' => $createdid]);
                    }
                }
            }
        }

        // Add all participants in the correct group.
        $allocations = $this->get_allocations();
        foreach ($allocations as $allocation) {
            $choiceid = $allocation->choiceid;
            $userid = $allocation->userid;

            // Get the group corresponding to the choiceid.
            $groupids = $this->db->get_record(this_db\ratingallocate_ch_gengroups::TABLE,
                array('choiceid' => $choiceid),
                'groupid');
            $groupid = $groupids->groupid;
            $group = groups_get_group($groupid);
            if ($group) {
                groups_add_member($group, $userid);
            }
        }
        // Invalidate the grouping cache for the course.
        cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($this->course->id));
    }

    /**
     * Publish the allocation and schedule to send the notifications to the participants.
     */
    public function publish_allocation() {
        require_capability('mod/ratingallocate:start_distribution', $this->context);

        $this->origdbrecord->{this_db\ratingallocate::PUBLISHED} = true;
        $this->origdbrecord->{this_db\ratingallocate::PUBLISHDATE} = time();
        $this->origdbrecord->{this_db\ratingallocate::NOTIFICATIONSEND} = -1;
        $this->ratingallocate = new ratingallocate_db_wrapper($this->origdbrecord);
        $this->db->update_record(this_db\ratingallocate::TABLE, $this->origdbrecord);

        // Create the instance.
        $task = new mod_ratingallocate\task\send_distribution_notification();

        // Add custom data.
        $task->set_component('mod_ratingallocate');
        $task->set_custom_data(array(
                'ratingallocateid' => $this->ratingallocateid
        ));

        // Queue it.
        \core\task\manager::queue_adhoc_task($task);

        // Logging.
        $event = \mod_ratingallocate\event\allocation_published::create_simple(
                context_module::instance($this->coursemodule->id), $this->ratingallocateid);
        $event->trigger();
    }

    /**
     * Call this function when the algorithm failed and the algorithm status has to be set to failed.
     */
    public function set_algorithm_failed() {
        $this->origdbrecord->algorithmstatus = \mod_ratingallocate\algorithm_status::FAILURE;
        $this->db->update_record(this_db\ratingallocate::TABLE, $this->origdbrecord);
    }

    /**
     * Returns all users, that have not been allocated but have given ratings
     *
     * @return array;
     */
    public function get_ratings_for_rateable_choices_for_raters_without_alloc() {
        $sql = 'SELECT al.*
                FROM {ratingallocate_allocations} al
               WHERE al.ratingallocateid = :ratingallocateid';

        $allocated = $this->db->get_records_sql($sql, array(
                'ratingallocateid' => $this->ratingallocateid
        ));
        $ratings = $this->get_ratings_for_rateable_choices();
        // Macht daraus ein Array mit userid => quatsch.
        $allocated = array_flip(array_map(function($entry) {
            return $entry->userid;
        }, $allocated));

        // Filter out everyone who already has an allocation.
        $unallocraters = array_filter($ratings, function($ratings) use ($allocated) {
            return !array_key_exists($ratings->userid, $allocated);
        });

        return $unallocraters;
    }

    /*
     * Returns all active choices with allocation count
     */
    public function get_choices_with_allocationcount() {
        $sql = 'SELECT c.*, al.usercount
            FROM {ratingallocate_choices} c
            LEFT JOIN (
                SELECT choiceid, count( userid ) AS usercount
                FROM {ratingallocate_allocations}
                WHERE ratingallocateid =:ratingallocateid1
                GROUP BY choiceid
            ) AS al ON c.id = al.choiceid
            WHERE c.ratingallocateid =:ratingallocateid and c.active = :active';

        $choices = $this->db->get_records_sql($sql, array(
                'ratingallocateid' => $this->ratingallocateid,
                'ratingallocateid1' => $this->ratingallocateid,
                'active' => true,
        ));
        return $choices;
    }

    /**
     * Returns the allocation for each user. The keys of the returned array contain the userids.
     * @return array all allocation objects that belong this ratingallocate
     */
    public function get_allocations() {
        $query = 'SELECT al.userid, al.*, r.rating
                FROM {ratingallocate_allocations} al
           LEFT JOIN {ratingallocate_choices} c ON al.choiceid = c.id
           LEFT JOIN {ratingallocate_ratings} r ON al.choiceid = r.choiceid AND al.userid = r.userid
               WHERE al.ratingallocateid = :ratingallocateid AND c.active = 1';
        $records = $this->db->get_records_sql($query, array(
                'ratingallocateid' => $this->ratingallocateid
        ));
        return $records;
    }

    /**
     * Removes all allocations for choices in $ratingallocateid
     */
    public function clear_all_allocations() {
        $this->db->delete_records('ratingallocate_allocations', array('ratingallocateid' => intval($this->ratingallocateid)));
    }

    /**
     * Gets called by the adhoc_taskmanager and its task in send_distribution_notification
     *
     * @param stdClass $userfrom
     */
    public function notify_users_distribution() {
        global $CFG;

        // Make sure we have not sent them yet.
        if ($this->origdbrecord->{this_db\ratingallocate::NOTIFICATIONSEND} > 0) {
            mtrace('seems we have sent them already');
            return;
        }

        $users = $this->get_users_with_ratings();
        $choices = $this->get_choices_with_allocationcount();
        $allocations = $this->get_allocations();
        foreach ($users as $userid => $allocobj) {

            // Prepare the email to be sent to the user.
            $userto = get_complete_user_data('id', $userid);
            if ($CFG->branch >= 402) {
                \core\cron::setup_user($userto);
            } else {
                cron_setup_user($userto);
            }

            $notificationsubject = format_string($this->course->shortname, true) . ': ' .
                    get_string('allocation_notification_message_subject', 'ratingallocate',
                            $this->ratingallocate->name);

            if (array_key_exists($userid, $allocations) && $allocobj = $allocations[$userid]) {
                // Get the assigned choice_id.
                $allocchoiceid = $allocobj->choiceid;

                $notificationtext = get_string('allocation_notification_message', 'ratingallocate', array(
                        'ratingallocate' => $this->ratingallocate->name,
                        'choice' => $choices[$allocchoiceid]->title,
                        'explanation' => format_text($choices[$allocchoiceid]->explanation)));
            } else {
                $notificationtext = get_string('no_allocation_notification_message', 'ratingallocate', array(
                        'ratingallocate' => $this->ratingallocate->name));
            }

            // Prepare the message.
            $eventdata = new \core\message\message();
            $eventdata->courseid = $this->course->id;
            $eventdata->component = 'mod_ratingallocate';
            $eventdata->name = 'allocation';
            $eventdata->notification = 1;

            $eventdata->userfrom = core_user::get_noreply_user();
            $eventdata->userto = $userid;
            $eventdata->subject = $notificationsubject;
            $eventdata->fullmessage = $notificationtext;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = '';

            $eventdata->smallmessage = '';
            $eventdata->contexturl = new moodle_url('/mod/ratingallocate/view.php',
                    array('id' => $this->coursemodule->id));
            $eventdata->contexturlname = $this->ratingallocate->name;

            $mailresult = message_send($eventdata);
            if (!$mailresult) {
                mtrace(
                        "ERROR: mod/ratingallocate/locallib.php: Could not send notification to user $userto->id " .
                        "... not trying again.");
            }
        }

        // Update the 'notified' flag.
        $this->origdbrecord->{this_db\ratingallocate::NOTIFICATIONSEND} = 1;
        $this->ratingallocate = new ratingallocate_db_wrapper($this->origdbrecord);

        $this->db->update_record(this_db\ratingallocate::TABLE, $this->origdbrecord);
    }

    /**
     * Create a moodle grouping with the name of the ratingallocate instance
     * and create groups according to the distribution. Groups are identified
     * by their idnumber. If a group exists, all users are removed.
     * @deprecated
     */
    public function create_moodle_groups() {
        $this->process_action_allocation_to_grouping();
    }

    /**
     * Returns all ratings from the user with id $userid.
     * @param int $userid
     * @return array
     */
    public function get_rating_data_for_user($userid) {
        $sql = "SELECT c.id as choiceid, c.title, c.explanation, c.ratingallocateid, c.maxsize, c.usegroups, r.rating, r.id AS ratingid, r.userid
                FROM {ratingallocate_choices} c
           LEFT JOIN {ratingallocate_ratings} r
                  ON c.id = r.choiceid and r.userid = :userid
               WHERE c.ratingallocateid = :ratingallocateid AND c.active = 1
               ORDER by c.title";
        return $this->db->get_records_sql($sql, array(
                'ratingallocateid' => $this->ratingallocateid,
                'userid' => $userid
        ));
    }

    /**
     * Returns all ids of users who handed in a rating to any choice of the instance.
     * @return array of userids
     */
    public function get_users_with_ratings() {
        $sql = "SELECT DISTINCT r.userid
                FROM {ratingallocate_choices} c
                JOIN {ratingallocate_ratings} r
                  ON c.id = r.choiceid
               WHERE c.ratingallocateid = :ratingallocateid AND c.active = 1";
        return $this->db->get_records_sql($sql, array(
                'ratingallocateid' => $this->ratingallocateid
        ));
    }

    /**
     * Deletes all ratings in this ratingallocate
     */
    public function delete_all_ratings() {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        try {
            $choices = $this->get_choices();

            foreach ($choices as $id => $choice) {
                $data = array(
                    'choiceid' => $id
                );

                // Delete the allocations associated with this rating.
                $DB->delete_records('ratingallocate_allocations', $data);

                // Actually delete the rating.
                $DB->delete_records('ratingallocate_ratings', $data);
            }

            $transaction->allow_commit();

            // Logging.
            $event = \mod_ratingallocate\event\all_ratings_deleted::create_simple(
                context_module::instance($this->coursemodule->id), $this->ratingallocateid);
            $event->trigger();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    /**
     * Delete all ratings of a users
     * @param int $userid
     */
    public function delete_ratings_of_user($userid) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        try {

            $choices = $this->get_choices();

            foreach ($choices as $id => $choice) {
                $data = array(
                        'userid' => $userid,
                        'choiceid' => $id
                );

                // Actually delete the rating.
                $DB->delete_records('ratingallocate_ratings', $data);
            }

            $transaction->allow_commit();

            // Logging.
            $event = \mod_ratingallocate\event\rating_deleted::create_simple(
                    context_module::instance($this->coursemodule->id), $this->ratingallocateid);
            $event->trigger();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    /**
     * Save all the users rating to db
     * @param int $userid
     * @param array $data
     */
    public function save_ratings_to_db($userid, array $data) {
        /* @var moodle_database */
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $loggingdata = array();
        try {
            foreach ($data as $id => $rdata) {
                $rating = new stdClass ();
                $rating->rating = $rdata['rating'];

                $ratingexists = array(
                        'choiceid' => $rdata['choiceid'],
                        'userid' => $userid
                );
                if ($DB->record_exists('ratingallocate_ratings', $ratingexists)) {
                    // The rating exists, we need to update its value
                    // We get the id from the database.

                    $oldrating = $DB->get_record('ratingallocate_ratings', $ratingexists);
                    if ($oldrating->{this_db\ratingallocate_ratings::RATING} != $rating->rating) {
                        $rating->id = $oldrating->id;
                        $DB->update_record('ratingallocate_ratings', $rating);

                        // Logging.
                        array_push($loggingdata,
                                array('choiceid' => $oldrating->choiceid, 'rating' => $rating->rating));
                    }
                } else {
                    // Create a new rating in the table.

                    $rating->userid = $userid;
                    $rating->choiceid = $rdata['choiceid'];
                    $rating->ratingallocateid = $this->ratingallocateid;
                    $DB->insert_record('ratingallocate_ratings', $rating);

                    // Logging.
                    array_push($loggingdata,
                            array('choiceid' => $rating->choiceid, 'rating' => $rating->rating));
                }
            }
            $transaction->allow_commit();

            $completion = new completion_info($this->course);
            $completion->set_module_viewed($this->coursemodule);

            // Logging.
            $event = \mod_ratingallocate\event\rating_saved::create_simple(
                    context_module::instance($this->coursemodule->id), $this->ratingallocateid, $loggingdata);
            $event->trigger();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    /**
     * Returns all active choices in the instance with $ratingallocateid
     */
    public function get_rateable_choices() {
        global $DB;
        return $DB->get_records(this_db\ratingallocate_choices::TABLE,
                array(this_db\ratingallocate_choices::RATINGALLOCATEID => $this->ratingallocateid,
                        this_db\ratingallocate_choices::ACTIVE => true,
                ), this_db\ratingallocate_choices::TITLE);
    }

    /**
     * Filters a list of choice data objects according to a user's group membership.
     *
     * @param array $choices An array of objects, keyed by ID. Objects must have a 'usegroups' field.
     * @param int $userid A user ID.
     *
     * @return array A filtered array of choices, keyed by ID.
     */
    public function filter_choices_by_groups($choices, $userid) {

        // See all the choices, if you have the capability to modify them.
        if (has_capability('mod/ratingallocate:modify_choices', $this->context)
            || has_capability('mod/ratingallocate:export_ratings', $this->context)) {
            return $choices;
        }

        $filteredchoices = array();

        // Index 0 for "all groups" without groupings.
        $usergroupids = groups_get_user_groups($this->course->id, $userid)[0];

        foreach ($choices as $choiceid => $choice) {
            if ($choice->usegroups) {
                // Check for overlap between user group and choice group IDs.
                $choicegroups = $this->get_choice_groups($choiceid);
                $intersection = array_intersect($usergroupids, array_keys($choicegroups));
                // Pass if there is an intersection, block otherwise.
                if (count($intersection)) {
                    $filteredchoices[$choiceid] = $choice;
                }
            } else {
                $filteredchoices[$choiceid] = $choice;
            }
        }

        return $filteredchoices;
    }

    /**
     * Returns all choices in the instance with $ratingallocateid
     */
    public function get_choices() {
        global $DB;
        return $DB->get_records(this_db\ratingallocate_choices::TABLE,
                array(this_db\ratingallocate_choices::RATINGALLOCATEID => $this->ratingallocateid,
                ), this_db\ratingallocate_choices::TITLE);
    }

    /**
     * Returns all memberships of a user for rateable choices in this instance of ratingallocate
     */
    public function get_allocations_for_user($userid) {
        $sql = 'SELECT m.id AS ratingallocateid, c.title, c.explanation, al.choiceid
            FROM {ratingallocate} m
            JOIN {ratingallocate_allocations} al
            ON m.id = al.ratingallocateid
            JOIN {ratingallocate_choices} c
            ON al.choiceid = c.id
            WHERE al.ratingallocateid = :ratingallocateid
            AND al.userid = :userid';

        return $this->db->get_records_sql($sql, array(
                'ratingallocateid' => $this->ratingallocateid,
                'userid' => $userid
        ));
    }

    /**
     * Adds the manual allocation to db. Does not perform checks if there is already an allocation user-choice
     * @param $allocdata array of users to the choice ids they should be allocated to.
     * @global mixed $DB
     */
    public function save_manual_allocation_form($allocdata, $userdata) {
        try {
            $transaction = $this->db->start_delegated_transaction();

            $allusers = $this->get_raters_in_course();
            $allchoices = $this->get_rateable_choices();

            foreach ($userdata as $id => $user) {
                $this->remove_allocations($id);
            }

            foreach ($allocdata as $id => $choiceallocationid) {
                // Is this user in this course?
                if (key_exists($id, $allusers) && key_exists($id, $userdata) && key_exists($choiceallocationid, $allchoices)) {
                    // Create new allocation.
                    $this->add_allocation($choiceallocationid, $id);
                }
            }
            // Logging.
            $event = \mod_ratingallocate\event\manual_allocation_saved::create_simple(
                    context_module::instance($this->coursemodule->id), $this->ratingallocateid);
            $event->trigger();

            $transaction->allow_commit();
        } catch (Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
        }
    }

    public function save_modify_choice_form($data) {
        global $DB;
        try {
            $transaction = $this->db->start_delegated_transaction();
            $loggingdata = array();

            $allusers = $this->get_raters_in_course();
            $allchoices = $this->get_rateable_choices();

            $choice = new ratingallocate_choice($data);
            $choice->{this_db\ratingallocate_choices::RATINGALLOCATEID} = $this->ratingallocateid;

            if (!empty($data->choiceid)) {
                $choice->id = $data->choiceid;
                $DB->update_record(this_db\ratingallocate_choices::TABLE, $choice->dbrecord);
            } else {
                // Update choiceid for pass through to file attachments.
                $data->choiceid = $DB->insert_record(this_db\ratingallocate_choices::TABLE, $choice->dbrecord);
            }

            // Logging.
            // $event = \mod_ratingallocate\event\choice_saved::create_simple(
            // context_course::instance($this->course->id), $this->ratingallocateid, );
            // $event->trigger();

            $transaction->allow_commit();
        } catch (Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
        }

    }

    /**
     * remove an allocation between choiceid and userid
     * @param int $choiceid
     * @param int $userid
     * @return boolean
     */
    public function remove_allocation($choiceid, $userid) {
        $this->db->delete_records('ratingallocate_allocations', array(
                'choiceid' => $choiceid,
                'userid' => $userid
        ));
        return true;
    }

    /**
     * Remove all allocations of a user.
     *
     * @param int $userid id of the user.
     */
    public function remove_allocations($userid) {
        $this->db->delete_records('ratingallocate_allocations', array(
                'userid' => $userid,
                'ratingallocateid' => $this->ratingallocateid
        ));
    }

    /**
     * add an allocation between choiceid and userid
     * @param int $choiceid
     * @param int $userid
     * @return boolean
     */
    public function add_allocation($choiceid, $userid) {
        $this->db->insert_record_raw('ratingallocate_allocations', array(
                'choiceid' => $choiceid,
                'userid' => $userid,
                'ratingallocateid' => $this->ratingallocateid
        ));
        return true;
    }

    /**
     * alter an allocation between old_choiceid and userid
     * @param int $oldchoiceid
     * @param int $newchoiceid
     * @param int $userid
     * @return boolean
     */
    public function alter_allocation($oldchoiceid, $newchoiceid, $userid) {
        $this->db->set_field(this_db\ratingallocate_allocations::TABLE, this_db\ratingallocate_allocations::CHOICEID,
                $newchoiceid, array(
                        'choiceid' => $oldchoiceid,
                        'userid' => $userid
                )
        );
        return true;
    }

    /**
     * internal helper to populate the real db with random data, currently disabled
     */
    public function addtestdata() {
        return; // Delete this if you really want to call this function!
        $transaction = $this->db->start_delegated_transaction();
        for ($i = 2; $i < 502; $i++) { // Set right user id's!
            for ($c = 1; $c <= 21; $c++) {
                $ratingi = rand(1, 5);
                if ($ratingi > 0) {
                    $rating = new stdclass();
                    $rating->userid = $i;
                    $rating->choiceid = $c;
                    $rating->rating = $ratingi;
                    $this->db->insert_record('ratingallocate_ratings', $rating);
                }
            }
        }
        $transaction->allow_commit();
    }

    /**
     * Lazy load the page renderer and expose the renderer to plugin.
     *
     * @return mod_ratingallocate_renderer
     */
    public function get_renderer() {
        global $PAGE;
        if ($this->renderer) {
            return $this->renderer;
        }
        $this->renderer = $PAGE->get_renderer('mod_ratingallocate');
        return $this->renderer;
    }

    /**
     * Adds static elements to the radioarray to make use of css for formatting
     * @param array $radioarray
     * @param moodleform $mform
     * @return array radioarray
     */
    public function prepare_horizontal_radio_choice($radioarray, $mform) {
        $result = array();
        // Add static elements to provide a list with choices annotated with css classes.
        $result[] =& $mform->createElement('static', 'li', null, '<ul class="horizontal choices">');
        foreach ($radioarray as $id => $radio) {
            $result[] =& $mform->createElement('static', 'static' . $id, null, '<li class="option">');
            $result[] = $radio;
            $result[] =& $mform->createElement('static', 'static' . $id, null, '</li>');
        }
        $result[] =& $mform->createElement('static', 'static', null, '</ul>');

        return $result;
    }

    /**
     * Return a set of option titles for the given array of rating values
     * @param array $ratings
     */
    public function get_options_titles(array $ratings) {
        return $this->get_strategy_class()->translate_ratings_to_titles($ratings);
    }

    /**
     * Returns the strategy class for the ratingallocate
     */
    private function get_strategy_class() {
        $strategyclassp = 'ratingallocate\\' . $this->ratingallocate->strategy . '\\strategy';
        $allsettings = json_decode($this->ratingallocate->setting, true);
        if (array_key_exists($this->ratingallocate->strategy, $allsettings)) {
            return new $strategyclassp($allsettings[$this->ratingallocate->strategy]);
        } else {
            return new $strategyclassp();
        }
    }

    /** Rating phase has not started yet. */
    const DISTRIBUTION_STATUS_TOO_EARLY = 'too_early';
    /** Rating phase in progress. */
    const DISTRIBUTION_STATUS_RATING_IN_PROGRESS = 'rating_in_progress';
    /** Rating phase ended, but no allocations exist. */
    const DISTRIBUTION_STATUS_READY = 'ready';
    /** Rating phase ended and there are already some allocations. */
    const DISTRIBUTION_STATUS_READY_ALLOC_STARTED = 'ready_alloc_started';
    /** Rating phase ended and allocations have been published. */
    const DISTRIBUTION_STATUS_PUBLISHED = 'published';

    /**
     * Returns the status of the ratingallocate, which is used for altering the help texts
     * as well as for enabling and disabling functionalities.
     * @return string the current status of the ratingallocte
     */
    public function get_status() {
        $now = time();
        if ($this->ratingallocate->accesstimestart > $now) {
            return self::DISTRIBUTION_STATUS_TOO_EARLY;
        }
        if ($this->ratingallocate->accesstimestop > $now) {
            return self::DISTRIBUTION_STATUS_RATING_IN_PROGRESS;
        }
        if ($this->ratingallocate->published == true) {
            return self::DISTRIBUTION_STATUS_PUBLISHED;
        }
        if (count($this->get_allocations()) == 0) {
            return self::DISTRIBUTION_STATUS_READY;
        } else {
            return self::DISTRIBUTION_STATUS_READY_ALLOC_STARTED;
        }
    }

    /**
     * Returns the current algorithm status.
     * The different values can be found in the class ratingallocate_status.
     * @return int the current algorithm status
     */
    public function get_algorithm_status() {
        return (int) $this->ratingallocate->algorithmstatus;
    }

    /** Returns the context of the ratingallocate instance
     * @return context_module
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * Get candidate group selection options for a groupselector form element.
     *
     * @param array $grouplist (optional) A list of group records to build mappings from.
     *
     * @return array A mapping of group IDs to names.
     */
    public function get_group_selections($grouplist=null) {
        $options = array();

        // Default to all relevant groups for this context.
        if (!$grouplist) {
            $grouplist = groups_get_all_groups($this->course->id);
        }

        foreach ($grouplist as $group) {
            $options[$group->id] = $group->name;
        }

        return $options;
    }

    /**
     * Returns the groups associated with a ratingallocate choice.
     *
     * @param int $choiceid
     *
     * @return array A list of group records.
     */
    public function get_choice_groups($choiceid) {
        global $DB;

        $sql = 'SELECT g.*
        FROM {ratingallocate_group_choices} gc
        JOIN {groups} g ON gc.groupid=g.id
        WHERE choiceid=:choiceid';

        $records = $DB->get_records_sql($sql, array('choiceid' => $choiceid));
        $results = array();

        foreach ($records as $record) {
            $results[$record->id] = $record;
        }

        return $results;
    }

    /**
     * Update group set for a choice item.
     *
     * @param int $choiceid A ratingallocate_choice.
     * @param array $groupids An array of group IDs to be associated with the choice item.
     *
     * @return null
     */
    public function update_choice_groups($choiceid, $groupids) {
        global $DB;

        // Check group IDs against existing choices.
        $oldgroups = $this->get_choice_groups($choiceid);
        $oldids = array_keys($oldgroups);

        // Diff gives us all IDs in the first list, but not in the second.
        $removals = array_values(array_diff($oldids, $groupids));
        $additions = array_values(array_diff($groupids, $oldids));

        // Add records for new choice group entries.
        foreach ($additions as $gid) {
            $record = new stdClass();
            $record->choiceid = $choiceid;
            $record->groupid = $gid;
            $DB->insert_record('ratingallocate_group_choices', $record);
        }

        // Remove records for obsolete choice group entries.
        foreach ($removals as $gid) {
            $DB->delete_records('ratingallocate_group_choices', array(
                'choiceid' => $choiceid,
                'groupid' => $gid,
            ));
        }
    }

    /**
     * @return bool true, if all strategy settings are ok.
     */
    public function is_setup_ok() {
        if ($this->ratingallocate->strategy === 'strategy_order') {
            $choicecount = count($this->get_rateable_choices());
            $strategyclass = $this->get_strategy_class();
            $strategysettings = $strategyclass->get_static_settingfields();
            $necessarychoices = $strategysettings[ratingallocate\strategy_order\strategy::COUNTOPTIONS][2];
            if ($choicecount < $necessarychoices) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param int choiceid
     * @return array of file objects.
     */
    public function get_file_attachments_for_choice($choiceid) {
        $areafiles = get_file_storage()->get_area_files($this->context->id, 'mod_ratingallocate', 'choice_attachment', $choiceid);
        $files = array();
        foreach ($areafiles as $f) {
            if ($f->is_directory()) {
                // Skip directories.
                continue;
            }
            $files[] = $f;
        }
        return $files;
    }

}

/**
 * Kapselt eine Instanz von ratingallocate_choice
 *
 * @property int $id
 * @property int $ratingallocateid
 * @property string $title
 * @property string explanation
 * @property int $maxsize
 * @property bool $active
 * @property bool $usegroups Whether to restrict the visibility of this choice to the members of specified groups.
 */
class ratingallocate_choice {
    /** @var stdClass original db record */
    public $dbrecord;

    /** Emulates the functionality as if there were explicit records by passing them to the original db record
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return $this->dbrecord->{$name};
    }

    /** Emulates the functionality as if there were explicit records by passing them to the original db record
     *
     * @param string $name
     */
    public function __set($name, $value) {
        $this->dbrecord->{$name} = $value;
    }

    public function __construct($record) {
        $this->dbrecord = $record;
    }

}
/**
 * Kapselt eine Instanz von ratingallocate_group_choices.
 * (Encapsulating an instance of ratingallocate_group_choices.)
 *
 * @property int $id
 * @property int $choiceid
 * @property int $groupid
 */
class ratingallocate_group_choices {
    /** @var stdClass original db record */
    public $dbrecord;

    /**
     * Emulates the functionality as if there were explicit records by passing them to the original db record.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return $this->dbrecord->{$name};
    }

    /**
     * Emulates the functionality as if there were explicit records by passing them to the original db record.
     *
     * @param string $name
     */
    public function __set($name, $value) {
        $this->dbrecord->{$name} = $value;
    }

    public function __construct($record) {
        $this->dbrecord = $record;
    }
}
/**
 * Remove all users (or one user) from one group, invented by MxS by copying from group/lib.php
 * because it didn't exist there
 *
 * @param int $courseid
 * @return bool success
 */
function groups_delete_group_members_by_group($groupid) {
    global $DB;

    if (is_bool($groupid)) {
        debugging('Incorrect groupid function parameter');
        return false;
    }

    // Select * so that the function groups_remove_member() gets the whole record.
    $groups = $DB->get_recordset('groups', array('id' => $groupid));

    foreach ($groups as $group) {
        $userids = $DB->get_fieldset_select('groups_members', 'userid', 'groupid = :groupid',
                array('groupid' => $group->id));

        // Very ugly hack because some group-management functions are not provided in lib/grouplib.php
        // but does not add too much overhead since it does not include more files...
        require_once(dirname(dirname(dirname(__FILE__))) . '/group/lib.php');
        foreach ($userids as $id) {
            groups_remove_member($group, $id);
        }
    }
    return true;
}
