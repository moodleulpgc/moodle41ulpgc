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
 * Defines the scheduler module settings form.
 *
 * @package    mod_scheduler
 * @copyright  2011 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Scheduler modedit form - overrides moodleform
 *
 * @package    mod_scheduler
 * @copyright  2011 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_scheduler_mod_form extends moodleform_mod {

    /** @var array */
    protected $editoroptions;

    /**
     * Form definition
     */
    public function definition() {

        global $CFG, $COURSE, $OUTPUT;
        $mform    =& $this->_form;

        // General introduction.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('introduction', 'scheduler'));

        // Scheduler options.
        $mform->addElement('header', 'optionhdr', get_string('options', 'scheduler'));
        $mform->setExpanded('optionhdr');

        $mform->addElement('text', 'staffrolename', get_string('staffrolename', 'scheduler'), array('size' => '48'));
        $mform->setType('staffrolename', PARAM_TEXT);
        $mform->addRule('staffrolename', get_string('error'), 'maxlength', 255);
        $mform->addHelpButton('staffrolename', 'staffrolename', 'scheduler');

        $modegroup = array();
        $modegroup[] = $mform->createElement('static', 'modeintro', '', get_string('modeintro', 'scheduler'));

        $maxbookoptions = array();
        $maxbookoptions['0'] = get_string('unlimited', 'scheduler');
        for ($i = 1; $i <= 10; $i++) {
            $maxbookoptions[(string)$i] = $i;
        }
        $modegroup[] = $mform->createElement('select', 'maxbookings', '', $maxbookoptions);
        $mform->setDefault('maxbookings', 1);

        $modegroup[] = $mform->createElement('static', 'modeappointments', '', get_string('modeappointments', 'scheduler'));

        $modeoptions['oneonly'] = get_string('modeoneonly', 'scheduler');
        $modeoptions['onetime'] = get_string('modeoneatatime', 'scheduler');
        $modegroup[] = $mform->createElement('select', 'schedulermode', '', $modeoptions);
        $mform->setDefault('schedulermode', 'oneonly');

        $mform->addGroup($modegroup, 'modegrp', get_string('mode', 'scheduler'), ' ', false);
        $mform->addHelpButton('modegrp', 'appointmentmode', 'scheduler');

        if (get_config('mod_scheduler', 'groupscheduling')) {
            // ecastro ULPGC
            /*
            $selopt = array(
                            -1 => get_string('no'),
                             0 => get_string('yesallgroups', 'scheduler')
                           );
            $groupings = groups_get_all_groupings($COURSE->id);
            foreach ($groupings as $grouping) {
                $selopt[$grouping->id] = get_string('yesingrouping', 'scheduler', $grouping->name);
            }
            */
            $selopt = array();
            $selopt['-1'] = get_string('forceonlysingle', 'scheduler');
            $selopt['0'] = get_string('forceno', 'scheduler');
            $selopt['1'] = get_string('forceonlygroups', 'scheduler');
            // ecastro ULPGC end


            $mform->addElement('select', 'bookingrouping', get_string('groupbookings', 'scheduler'), $selopt);
            $mform->addHelpButton('bookingrouping', 'groupbookings', 'scheduler');
	        $mform->setDefault('bookingrouping', -1);
            $mform->disabledIf('bookingrouping', 'groupmode', 'eq', NOGROUPS); // ecastro ULPGC
        }
        $mform->addElement('duration', 'guardtime', get_string('guardtime', 'scheduler'), array('optional' => true));
        $mform->addHelpButton('guardtime', 'guardtime', 'scheduler');

        $mform->addElement('text', 'defaultslotduration', get_string('defaultslotduration', 'scheduler'), array('size' => '2'));
        $mform->setType('defaultslotduration', PARAM_INT);
        $mform->addHelpButton('defaultslotduration', 'defaultslotduration', 'scheduler');
        $mform->setDefault('defaultslotduration', 15);

        //$mform->addElement('selectyesno', 'allownotifications', get_string('notifications', 'scheduler'));
        // ecastro ULPGC cancalonly notifications
        $options = array(-1 => get_string('cancelonly', 'scheduler'),
                            0 => get_string('no'),
                            1 => get_string('yes'));
        $mform->addElement('select', 'allownotifications', get_string('notifications', 'scheduler'), $options); // ecastro ULPGC
        $mform->setDefault('allownotifications', 0);
        // ecastro ULPGC end
        $mform->addHelpButton('allownotifications', 'notifications', 'scheduler');

        $noteoptions['0'] = get_string('usenotesnone', 'scheduler');
        $noteoptions['1'] = get_string('usenotesstudent', 'scheduler');
        $noteoptions['2'] = get_string('usenotesteacher', 'scheduler');
        $noteoptions['3'] = get_string('usenotesboth', 'scheduler');
        $mform->addElement('select', 'usenotes', get_string('usenotes', 'scheduler'), $noteoptions);
        $mform->setDefault('usenotes', '1');

        if(get_config('mod_scheduler', 'sharedslotsenabled')) { // ecastro ULPGC
            $mform->addElement('selectyesno', 'usesharedslots', get_string('usesharedslots', 'scheduler'));
            $mform->addHelpButton('usesharedslots', 'usesharedslots', 'scheduler');
        }

        // Grade settings.
        $this->standard_grading_coursemodule_elements();

        $mform->setDefault('grade', 0);

        $gradingstrategy[SCHEDULER_MEAN_GRADE] = get_string('meangrade', 'scheduler');
        $gradingstrategy[SCHEDULER_MAX_GRADE] = get_string('maxgrade', 'scheduler');
        $mform->addElement('select', 'gradingstrategy', get_string('gradingstrategy', 'scheduler'), $gradingstrategy);
        $mform->addHelpButton('gradingstrategy', 'gradingstrategy', 'scheduler');
        $mform->disabledIf('gradingstrategy', 'grade[modgrade_type]', 'eq', 'none');

        // Booking form and student-supplied data.
        $mform->addElement('header', 'bookinghdr', get_string('bookingformoptions', 'scheduler'));

        $mform->addElement('selectyesno', 'usebookingform', get_string('usebookingform', 'scheduler'));
        $mform->addHelpButton('usebookingform', 'usebookingform', 'scheduler');

        $this->editoroptions = array('trusttext' => true, 'maxfiles' => -1, 'maxbytes' => 0,
                                     'context' => $this->context, 'collapsed' => true);
        $mform->addElement('editor', 'bookinginstructions_editor', get_string('bookinginstructions', 'scheduler'),
                array('rows' => 3, 'columns' => 60), $this->editoroptions);
        $mform->setType('bookinginstructions', PARAM_RAW); // Must be PARAM_RAW for rich text editor content.
        $mform->disabledIf('bookinginstructions_editor', 'usebookingform', 'eq', '0');
        $mform->addHelpButton('bookinginstructions_editor', 'bookinginstructions', 'scheduler');

        $studentnoteoptions['0'] = get_string('no');
        $studentnoteoptions['1'] = get_string('yesoptional', 'scheduler');
        $studentnoteoptions['2'] = get_string('yesrequired', 'scheduler');
        $mform->addElement('select', 'usestudentnotes', get_string('usestudentnotes', 'scheduler'), $studentnoteoptions);
        $mform->setDefault('usestudentnotes', '0');
        $mform->disabledIf('usestudentnotes', 'usebookingform', 'eq', '0');
        $mform->addHelpButton('usestudentnotes', 'usestudentnotes', 'scheduler');

        $uploadgroup = array();

        $filechoices = array();
        for ($i = 0; $i <= get_config('mod_scheduler', 'uploadmaxfiles'); $i++) {
            $filechoices[$i] = $i;
        }
        $uploadgroup[] = $mform->createElement('select', 'uploadmaxfiles', get_string('uploadmaxfiles', 'scheduler'), $filechoices);
        $mform->setDefault('uploadmaxfiles', 0);
        $mform->disabledIf('uploadmaxfiles', 'usebookingform', 'eq', '0');
        $uploadgroup[] = $mform->createElement('advcheckbox', 'requireupload', '', get_string('requireupload', 'scheduler'));
        $mform->disabledIf('requireupload', 'usebookingform', 'eq', '0');

        $mform->addGroup($uploadgroup, 'uploadgrp', get_string('uploadmaxfiles', 'scheduler'), ' ', false);
        $mform->addHelpButton('uploadgrp', 'uploadmaxfiles', 'scheduler');

        $sizechoices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes, 0);
        $mform->addElement('select', 'uploadmaxsize', get_string('uploadmaxsize', 'scheduler'), $sizechoices);
        $mform->setDefault('assignsubmission_file_maxsizebytes', $COURSE->maxbytes);
        $mform->disabledIf('uploadmaxsize', 'usebookingform', 'eq', '0');
        $mform->disabledIf('uploadmaxsize', 'uploadmaxfiles', 'eq', '0');
        $mform->addHelpButton('uploadmaxsize', 'uploadmaxsize', 'scheduler');

        if (!empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey)) {
            $mform->addElement('selectyesno', 'usecaptcha', get_string('usecaptcha', 'scheduler'), $studentnoteoptions);
            $mform->setDefault('usecaptcha', '0');
            $mform->disabledIf('usecaptcha', 'usebookingform', 'eq', '0');
            $mform->addHelpButton('usecaptcha', 'usecaptcha', 'scheduler');
        }

        // Common module settings.
        $this->standard_coursemodule_elements();
        $mform->setDefault('groupmode', NOGROUPS);

        $this->add_action_buttons();
    }

    /**
     * Allows module to modify data returned by get_moduleinfo_data() or prepare_new_moduleinfo_data() before calling set_data()
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param array $defaultvalues passed by reference
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);
        if ($this->current->instance) {
            $newvalues = file_prepare_standard_editor((object)$defaultvalues, 'bookinginstructions',
                             $this->editoroptions, $this->context,
                            'mod_scheduler', 'bookinginstructions', 0);
            $defaultvalues['bookinginstructions_editor'] = $newvalues->bookinginstructions_editor;
        }
        if (array_key_exists('scale', $defaultvalues)) {
            $dgrade = $defaultvalues['scale'];
            $defaultvalues['grade'] = $dgrade;
            $type = 'none';
            if ($dgrade > 0) {
                $type = 'point';
            } else if ($dgrade < 0) {
                $type = 'scale';
            }
            $defaultvalues['grade[modgrade_type]'] = $type;
        }
        
        // completion by ecastro ULPGC
        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completionappointmentsenabled']=
            !empty($default_values['completionappointments']) ? 1 : 0;
        if (empty($default_values['completionappointments'])) {
            $default_values['completionappointments']=1;
        }
        $default_values['completionattendedenabled']=
            !empty($default_values['completionattended']) ? 1 : 0;
        if (empty($default_values['completionattended'])) {
            $default_values['completionattended']=1;
        }
        
    }

    
    /// Completion functions by ecastro ULPGC
    /**
     * Adds completion rules form elements to the moodleform_mod_form 
     *
     * Only available on moodleform_mod.
     *
     * @author Enrique Castro @ ULPGC
     */
      function add_completion_rules() {
        $mform =& $this->_form;

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionappointmentsenabled', '', get_string('completionappointments','scheduler'));
        $group[] =& $mform->createElement('text', 'completionappointments', '', array('size'=>3));
        $mform->setType('completionappointments',PARAM_INT);
        $mform->addGroup($group, 'completionappointmentsgroup', get_string('completionappointmentsgroup','scheduler'), array(' '), false);
        $mform->disabledIf('completionappointments','completionappointmentsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionattendedenabled', '', get_string('completionattended','scheduler'));
        $group[] =& $mform->createElement('text', 'completionattended', '', array('size'=>3));
        $mform->setType('completionattended',PARAM_INT);
        $mform->addGroup($group, 'completionattendedgroup', get_string('completionattendedgroup','scheduler'), array(' '), false);
        $mform->disabledIf('completionattended','completionattendedenabled','notchecked');

        return array('completionappointmentsgroup','completionattendedgroup');
    }

    /**
     * Adds completion rules form elements to the moodleform_mod_form 
     *
     * @param object $data 
     * @author Enrique Castro @ ULPGC
     */    
    function completion_rule_enabled($data) {
        return (!empty($data['completionappointmentsenabled']) && $data['completionappointments']!=0) ||
                (!empty($data['completionattendedenabled']) && $data['completionattended']!=0);
    }

    /**
     * Adds completion rules form elements to the moodleform_mod_form 
     *
     * @return stdClass $data 
     * @author Enrique Castro @ ULPGC
     */
     function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Turn off completion settings if the checkboxes aren't ticked
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionappointmentsenabled) || !$autocompletion) {
                $data->completionappointments = 0;
            }
            if (empty($data->completionattendedenabled) || !$autocompletion) {
                $data->completionattended = 0;
            }
        }
        return $data;
     }    
    
    
    
    /**
     * save_mod_data
     *
     * @param stdClass $data
     * @param context_module $context
     */
    public function save_mod_data(stdClass $data, context_module $context) {
        global $DB;

        $editor = $data->bookinginstructions_editor;
        if ($editor) {
            $data->bookinginstructions = file_save_draft_area_files($editor['itemid'], $context->id,
                                            'mod_scheduler', 'bookinginstructions', 0,
                                            $this->editoroptions, $editor['text']);
            $data->bookinginstructionsformat = $editor['format'];
            $DB->update_record('scheduler', $data);
        }
    }



}
