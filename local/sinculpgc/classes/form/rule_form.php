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
 * Form to create new rule
 * @package local_sinculpgc
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sinculpgc\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use local_sinculpgc\helper;

//class rule_form extends \core\form\persistent {
class rule_form extends \moodleform {

    /** @var string Persistent class name. */
    //protected static $persistentclass = 'local_sinculpgc\sinculpgcrule';

    public function definition () {
        $mform =& $this->_form;

        $id = $this->_customdata['id'];
        $enrol = $this->_customdata['enrol'];
        
        $mform->addElement('header', 'headerenrol', get_string('pluginname', 'enrol_' . $enrol));
        
        $this->add_clean_enrol_instance_form($enrol, $mform);

        $mform->addElement('header', 'headerrule', get_string('rulesettings', 'local_sinculpgc'));        
        
        $searchfields = ['' => get_string('choose'),
                        'fullname'      => get_string('fullnamecourse'),
                        'shortname'     => get_string('shortnamecourse'),
                        'idnumber'      => get_string('idnumbercourse'),
                        'category'      => get_string('coursecategory'),
                        'catname'       => get_string('categoryname'),
                        'catidnumber'   => get_string('idnumbercoursecategory'),
                        ];
        
        $mform->addElement('select', 'searchfield', get_string('searchfield', 'local_sinculpgc'), $searchfields);
        $mform->addHelpButton('searchfield', 'searchfield', 'local_sinculpgc');
        $mform->addRule('searchfield', get_string('required'), 'required', null, 'client');        
        
        $mform->addElement('textarea', 'searchpattern', get_string('searchpattern', 'local_sinculpgc'), 'rows="3" cols="50"');
        $mform->addHelpButton('searchpattern', 'searchpattern', 'local_sinculpgc');
        $mform->addRule('searchpattern', get_string('required'), 'required', null, 'client');        

        $mform->addElement('text', 'groupto', get_string('rulegroup', 'local_sinculpgc'), 'size="50"');
        $mform->addHelpButton('groupto', 'rulegroup', 'local_sinculpgc');
        $mform->setType('groupto', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'useidnumber', get_string('useidnumber', 'local_sinculpgc'), 
                                                         get_string('useidnumber_help', 'local_sinculpgc'));
        $mform->setDefault('useidnumber', 0);
        $mform->setType('useidnumber', PARAM_INT);
        
        $mform->addElement('selectyesno', 'enabled', get_string('enabled', 'local_sinculpgc'));
        $mform->setDefault('enabled', 0);
        
        $mform->addElement('hidden', 'enrol', $enrol);
        $mform->setType('enrol', PARAM_ALPHANUMEXT);
    
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        
        $label = $id ? 'ruleupdate' : 'ruleadd';
        $this->add_action_buttons(true, (get_string($label, 'local_sinculpgc')));
    }
    
    
    protected function add_clean_enrol_instance_form($enrol, $mform) {
        global $CFG, $DB;; 
        
        $course = '';
        if($idn = get_config('local_sinculpgc', 'referencecourse')) {
            $course = $DB->get_record('course', ['idnumber' => $idn], 'id, idnumber');
        } 
        if(empty($course)) {
            \core\notification::add(get_string('referencecoursenotexists', 'local_sinculpgc'), 
                                    \core\output\notification::NOTIFY_WARNING);                
            $course = get_site(); 
        }        
        $context = \context_course::instance($course->id);
        
        $plugin = enrol_get_plugin($enrol);
        $instance = (object)$plugin->get_instance_defaults();
        $instance->id       = null;
        $instance->courseid   = $course->id;
        
        $location = "{$CFG->dirroot}/enrol/$enrol";
        if(!$plugin->use_standard_editing_ui() && file_exists("$location/edit_form.php")) {   
            //this is an enrol with form in separate script
            include_once("$location/edit_form.php");
            $class = "\\enrol_{$enrol}_edit_form";
            $ef = new $class(null, array($instance, $enrol, $course));
            $form = $ef->_form;
            foreach($form->_elementIndex as $key => $order) {
                $element = $form->getElement($key);
                if($key == 'name') {
                    $mform->addElement($element);
                    $mform->setType($key, PARAM_TEXT);
                } elseif(substr($key, 0, 9) == 'customint') {
                    $mform->addElement($element);
                    $mform->setType($key, PARAM_INT);
                } elseif(substr($key, 0, 10) == 'customchar') {
                    $mform->addElement($element);
                    $mform->setType($key, PARAM_ALPHANUMEXT);
                } elseif(substr($key, 0, 10) == 'customtext') {
                    $mform->addElement($element);
                    $mform->setType($key, PARAM_TEXT);
                } elseif(substr($key, 0, 9) == 'customdec') {
                    $mform->addElement($element);
                    $mform->setType($key, PARAM_CLEANHTML);
                } 
            }
        } else {
            // this is a plugin with edit_instance_form function
            $plugin->edit_instance_form($instance, $mform, $context);
        }
        
        helper::clean_group_element($enrol, $mform);
        
    }
    
}
