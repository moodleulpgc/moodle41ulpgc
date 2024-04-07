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

namespace core_question\local\bank;

/**
 * Class bulk_action_base is the base class for bulk actions ui.
 *
 * Every plugin wants to implement a bulk action, should extend this class, add appropriate values to the methods
 * and finally pass this object via plugin_feature class.
 *
 * @package    core_question
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class question_edit_handler_base {

    /**
     * @var \context
     */
    protected $parentcontext;

    /**
     * Returns the context for the data associated with the given instanceid.
     *
     * @param int $instanceid id of the record to get the context for
     * @return \context the context for the given record
     * @throws \coding_exception
     */
    public function get_instance_context(int $instanceid = 0) : \context {
        if ($instanceid > 0) {
            $questiondata = \question_bank::load_question_data($instanceid);
            $contextid = $questiondata->contextid;
            $context = \context::instance_by_id($contextid);
            return $context;
        } else {
            throw new \coding_exception('Instance id must be provided.');
        }
    }

    /**
     * Sets parent context for the question.
     *
     * This may be needed when question is being created, there is no question context but we need to check capabilities
     *
     * @param \context $context
     */
    public function set_parent_context(\context $context): void {
        $this->parentcontext = $context;
    }

    /**
     * Returns the parent context for the question.
     *
     * @return \context
     */
    protected function get_parent_context() : \context {
        if ($this->parentcontext) {
            return $this->parentcontext;
        } else {
            return \context_system::instance();
        }
    }

    /**
     * The current user can edit custom fields for the given question.
     *
     * @param int $instanceid id of the question to test edit permission
     * @return bool true if the current can edit custom fields, false otherwise
     */
    public function can_edit(int $instanceid = 0) : bool {
        if ($instanceid) {
            $context = $this->get_instance_context($instanceid);
        } else {
            $context = $this->get_parent_context();
        }

        $caps = $this->get_edit_capabilities();
        return (has_all_capabilities($caps, $context));
    }

    /**
     * Get the capabilities for the edit action.
     * This method helps to get those caps which will be used to check who can edit.
     * For ex: ['moodle/question:moveall', 'moodle/question:add']
     * All caps need to be true for the user to be able to edit.
     *
     * @return array|null
     */
    public function get_edit_capabilities(): ?array {
        return ['moodle/question:editmine'];
    }

    /**
     * Adds custom fields to instance editing form
     *
     * Example:
     *   public function definition() {
     *     // ... normal instance definition, including hidden 'id' field.
     *     $handler->instance_form_definition($this->_form, $instanceid);
     *     $this->add_action_buttons();
     *   }
     *
     * @param \MoodleQuickForm $mform
     * @param int $instanceid id of the instance, can be null when instance is being created
     * @param string $headerlangidentifier If specified, a lang string will be used for field category headings
     * @param string $headerlangcomponent
     */
    abstract public function instance_form_definition(\MoodleQuickForm $mform, int $instanceid = 0,
                        ?string $headerlangidentifier = null, ?string $headerlangcomponent = null);


    /**
     * Validates the given data for custom fields, used in moodleform validation() function
     *
     * Example:
     *   public function validation($data, $files) {
     *     $errors = [];
     *     // .... check other fields.
     *     $errors = array_merge($errors, $handler->instance_form_validation($data, $files));
     *     return $errors;
     *   }
     *
     * @param array $data
     * @param array $files
     * @return array validation errors
     */
    abstract public function instance_form_validation(array $data, array $files);


    /**
     * Form data definition callback.
     *
     * This method is called from moodleform::definition_after_data and allows to tweak
     * mform with some data coming directly from the field plugin data controller.
     *
     * @param \MoodleQuickForm $mform
     * @param int $instanceid
     */
    public function instance_form_definition_after_data(\MoodleQuickForm $mform, int $instanceid = 0) {

    }

    /**
     * Prepares the custom fields data related to the instance to pass to mform->set_data()
     *
     * Example:
     *   $instance = $DB->get_record(...);
     *   // .... prepare editor, filemanager, add tags, etc.
     *   $handler->instance_form_before_set_data($instance);
     *   $form->set_data($instance);
     *
     * @param stdClass $instance the instance that has custom fields, if 'id' attribute is present the custom
     *    fields for this instance will be added, otherwise the default values will be added.
     */
    public function instance_form_before_set_data(stdClass $instance) {
        $instanceid = !empty($instance->id) ? $instance->id : 0;
    }

    /**
     * Saves the given data for custom fields, must be called after the instance is saved and id is present
     *
     * Example:
     *   if ($data = $form->get_data()) {
     *     // ... save main instance, set $data->id if instance was created.
     *     $handler->instance_form_save($data);
     *     redirect(...);
     *   }
     *
     * @param stdClass $instance data received from a form
     * @param bool $isnewinstance if this is call is made during instance creation
     */
    abstract public function instance_form_save(stdClass $instance, bool $isnewinstance = false);

}
