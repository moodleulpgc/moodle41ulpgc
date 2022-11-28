<?php
// This file is part of the examregistrar module for Moodle - http://moodle.org/
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
 * The base class for the examregistrar delivery plugins.
 *
 * @package    mod_examregistrar
 * @copyright  2021 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examregistrar;

defined('MOODLE_INTERNAL') || die();

/**
 * Class delivery
 *
 * All examregistrar delivery plugins are based on this class.
 *
 * @package    mod_examregistrar
 * @copyright  2021 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class delivery {

    /** @var int The id in examregistrar_examdelivery table. */
    protected $id;

    /** @var string The helper module name. */
    protected $helpermod;

    /** @var string The delivery type name. */
    protected $name;
    
    /** @var int The exam id. */
    public $examid;

    /** @var int The course module id of the helper module. */
    public $helpercmid;

    /** @var int The module instance id in the helpermodule table. */
    public $instanceid;    

    /** @var int The exam start time timestamp. */
    public $timeopen;

    /** @var int The exam close time timestamp. */
    public $timeclose;
    
    /** @var int The exam duration in seconds. */
    public $timelimit;

    /** @var int The exam delivery status flag for error. */
    public $status;

    /** @var int The id of user modifiying this delivery. */
    public $timelimit;

    /** @var string The name of the component that sets the helpercmid. */
    protected $component;
    
    
    /**
     * Constructor.
     *
     * @param \stdClass $delivery the delivery data
     */
    public function __construct($delivery) {
    
        if(isset($delivery->id)) {
            $this->id = $delivery->id;
        }
        
            $this->examid = $delivery->examid;
            $this->helpermod = $delivery->helpermod;
            $this->helpercmid = $delivery->helpercmid;
            $this->timeopen = $delivery->timeopen;
            $this->timeclose = $delivery->timeclose;
            $this->timelimit = $delivery->timelimit;
            $this->status = $delivery->status;
            $this->component = $delivery->component;
            $this->modifierid = $delivery->modifierid;
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Sets the  id.
     *
     * @param int $id the 
     */
    public function set_id($id) {
        $this->id = $id;
    }

    /**
     * Returns the name.
     *
     * @return int
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Construct tha class object from the id in table examregistrar_examdelivery.
     *
     * @param int $id the      
     */
    public static function get_from_id($deliveryid) {
        global $DB; 
        
        if($rec = $DB->get_record('examregistrar_examdelivery', ['id' => $deliveryid], '*', MUST_EXIST)) {
            return new delivery($rec);
        }
        
        return false;        
    }    
    
    /**
     * Handles saving the form deliverys created by this delivery.
     * Can be overridden if more functionality is needed.
     *
     * @param \context $context the module context the saving is triggered ftrom
     * @return bool true of success, false otherwise.
     */
    public function save_or_update(\context $context) {
        global $DB;    

        
        $delivery = $DB->
        
        
        
        
        $eventdata = array();
        $eventdata['objectid'] = $extraexamid;
        $eventdata['context'] = $context;
        $eventdata['other'] = array();
        $eventdata['other']['edit'] = 'exams';        
        
        
        if($helperinstance = $this->get_helpermod_instance()) {
            $helperinstance->coursemodule = $this->helpercmid;
            $this->update_helper_instance($helperinstance);
        }
        
        return $updated; 
    }

    /**
     * Returns the helper module instance referenced by helpercmid
     *
     * Must be overridden.
     */
    public abstract function get_helper_instance();     
    
    /**
     * Handles updating of the helper module instance.
     *
     * Must be overridden.
     */
    public abstract function update_helper_instance(); 
    
    
    
    /**
     * Handles saving the form deliverys created by this delivery.
     * Can be overridden if more functionality is needed.
     *
     * @param \stdClass $data the form data
     * @return bool true of success, false otherwise.
     */
    public function get_helpermod_instance() {
        global $DB;
        
        if(!empty($this->helpermod) && isset($this->helpercmid) && $this->helpercmid) {
            $sql = 'SELECT h.* 
                    FROM {course_modules} cm ON 
                    JOIN {modules} m ON cm.module = m.id AND m.name = :helpermod
                    JOIN {'.$table.'} h ON ON cm.course = h.course AND cm.instanceid = h.id
                    WHERE cm.id = :cmid '
            
            $this->instance = $DB->get_record_sql(, array('id' => $instanceid), '*', MUST_EXIST);
            $this->instanceid = $this->instance->id;
        } else {
            $this->instance = null;
            $this->instanceid = null;
        }
        
        return $this->instance;
    }
    
    

    /**
     * This function renders the form deliverys when adding a examregistrar delivery.
     * Can be overridden if more functionality is needed.
     *
     * @param \MoodleQuickForm $mform the edit_form instance.
     */
    public function render_form_deliverys($mform) {
        // Render the common deliverys.
        delivery_helper::render_form_delivery_font($mform);
        delivery_helper::render_form_delivery_colour($mform);
        if ($this->showposxy) {
            delivery_helper::render_form_delivery_position($mform);
        }
        delivery_helper::render_form_delivery_width($mform);
        delivery_helper::render_form_delivery_refpoint($mform);
    }

    /**
     * Sets the data on the form when editing an delivery.
     * Can be overridden if more functionality is needed.
     *
     * @param edit_delivery_form $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        // Loop through the properties of the delivery and set the values
        // of the corresponding form delivery, if it exists.
        $properties = [
            'name' => $this->name,
            'font' => $this->font,
            'fontsize' => $this->fontsize,
            'colour' => $this->colour,
            'posx' => $this->posx,
            'posy' => $this->posy,
            'width' => $this->width,
            'refpoint' => $this->refpoint
        ];
        foreach ($properties as $property => $value) {
            if (!is_null($value) && $mform->deliveryExists($property)) {
                $delivery = $mform->getElement($property);
                $delivery->setValue($value);
            }
        }
    }

    /**
     * Performs validation on the delivery values.
     * Can be overridden if more functionality is needed.
     *
     * @param array $data the submitted data
     * @param array $files the submitted files
     * @return array the validation errors
     */
    public function validate_form_deliverys($data, $files) {
        // Array to return the errors.
        $errors = array();

        // Common validation methods.
        $errors += delivery_helper::validate_form_delivery_colour($data);
        if ($this->showposxy) {
            $errors += delivery_helper::validate_form_delivery_position($data);
        }
        $errors += delivery_helper::validate_form_delivery_width($data);

        return $errors;
    }

    /**
     * Handles saving the form deliverys created by this delivery.
     * Can be overridden if more functionality is needed.
     *
     * @param \stdClass $data the form data
     * @return bool true of success, false otherwise.
     */
    public function save_form_deliverys($data) {
        global $DB;

        // Get the data from the form.
        $delivery = new \stdClass();
        $delivery->name = $data->name;
        $delivery->data = $this->save_unique_data($data);
        $delivery->font = (isset($data->font)) ? $data->font : null;
        $delivery->fontsize = (isset($data->fontsize)) ? $data->fontsize : null;
        $delivery->colour = (isset($data->colour)) ? $data->colour : null;
        if ($this->showposxy) {
            $delivery->posx = (isset($data->posx)) ? $data->posx : null;
            $delivery->posy = (isset($data->posy)) ? $data->posy : null;
        }
        $delivery->width = (isset($data->width)) ? $data->width : null;
        $delivery->refpoint = (isset($data->refpoint)) ? $data->refpoint : null;
        $delivery->timemodified = time();

        // Check if we are updating, or inserting a new delivery.
        if (!empty($this->id)) { // Must be updating a record in the database.
            $delivery->id = $this->id;
            return $DB->update_record('examregistrar_deliverys', $delivery);
        } else { // Must be adding a new one.
            $delivery->delivery = $data->delivery;
            $delivery->pageid = $data->pageid;
            $delivery->sequence = \mod_examregistrar\delivery_helper::get_delivery_sequence($delivery->pageid);
            $delivery->timecreated = time();
            return $DB->insert_record('examregistrar_deliverys', $delivery, false);
        }
    }

    /**
     * This will handle how form data will be saved into the data column in the
     * examregistrar_deliverys table.
     * Can be overridden if more functionality is needed.
     *
     * @param \stdClass $data the form data
     * @return string the unique data to save
     */
    public function save_unique_data($data) {
        return '';
    }

    /**
     * This handles copying data from another delivery of the same type.
     * Can be overridden if more functionality is needed.
     *
     * @param \stdClass $data the form data
     * @return bool returns true if the data was copied successfully, false otherwise
     */
    public function copy_delivery($data) {
        return true;
    }

    /**
     * This defines if an delivery plugin can be added to a certificate.
     * Can be overridden if an delivery plugin wants to take over the control.
     *
     * @return bool returns true if the delivery can be added, false otherwise
     */
    public static function can_add() {
        return true;
    }

    /**
     * Handles rendering the delivery on the pdf.
     *
     * Must be overridden.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public abstract function render($pdf, $preview, $user);

    /**
     * Render the delivery in html.
     *
     * Must be overridden.
     *
     * This function is used to render the delivery when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public abstract function render_html();

    /**
     * Handles deleting any data this delivery may have introduced.
     * Can be overridden if more functionality is needed.
     *
     * @return bool success return true if deletion success, false otherwise
     */
    public function delete() {
        global $DB;

        return $DB->delete_records('examregistrar_deliverys', array('id' => $this->id));
    }

    /**
     * This function is responsible for handling the restoration process of the delivery.
     *
     * For example, the function may save data that is related to another course module, this
     * data will need to be updated if we are restoring the course as the course module id will
     * be different in the new course.
     *
     * @param \restore_examregistrar_activity_task $restore
     */
    public function after_restore($restore) {

    }

    /**
     * Magic getter for read only access.
     *
     * @param string $name
     */
    public function __get($name) {
        debugging('Please call the appropriate get_* function instead of relying on magic getters', DEBUG_DEVELOPER);
        if (property_exists($this->delivery, $name)) {
            return $this->delivery->$name;
        }
    }

    /**
     * Set edit form instance for the custom cert delivery.
     *
     * @param \mod_examregistrar\edit_delivery_form $editdeliveryform
     */
    public function set_edit_delivery_form(edit_delivery_form $editdeliveryform) {
        $this->editdeliveryform = $editdeliveryform;
    }

    /**
     * Get edit form instance for the custom cert delivery.
     *
     * @return \mod_examregistrar\edit_delivery_form
     */
    public function get_edit_delivery_form() {
        if (empty($this->editdeliveryform)) {
            throw new \coding_exception('Edit delivery form instance is not set.');
        }

        return $this->editdeliveryform;
    }

}
