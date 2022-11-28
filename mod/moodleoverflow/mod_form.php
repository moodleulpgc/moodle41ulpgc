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
// You should have received a copy of the GNU
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The main moodleoverflow configuration form.
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   mod_moodleoverflow
 * @copyright 2017 Kennet Winter <k_wint10@uni-muenster.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_moodleoverflow\anonymous;
use mod_moodleoverflow\review;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_moodleoverflow
 * @copyright  2017 Kennet Winter <k_wint10@uni-muenster.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_moodleoverflow_mod_form extends moodleform_mod {

    /**
     * Defines forms elements.
     */
    public function definition() {
        global $CFG, $COURSE;

        // Define the modform.
        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('moodleoverflowname', 'moodleoverflow'), array('size' => '64'));
        if (!empty(get_config('moodleoverflow', 'formatstringstriptags'))) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $currentsetting = $this->current && property_exists($this->current, 'anonymous') ? $this->current->anonymous : 0;
        $possiblesettings = [
                anonymous::EVERYTHING_ANONYMOUS => get_string('anonymous:everything', 'moodleoverflow')
        ];

        if ($currentsetting <= anonymous::QUESTION_ANONYMOUS) {
            $possiblesettings[anonymous::QUESTION_ANONYMOUS] = get_string('anonymous:only_questions', 'moodleoverflow');
        }

        if ($currentsetting == anonymous::NOT_ANONYMOUS) {
            $possiblesettings[anonymous::NOT_ANONYMOUS] = get_string('no');
        }

        if (get_config('moodleoverflow', 'allowanonymous') == '1') {
            $mform->addElement('select', 'anonymous', get_string('anonymous', 'moodleoverflow'), $possiblesettings);
            $mform->addHelpButton('anonymous', 'anonymous', 'moodleoverflow');
            $mform->setDefault('anonymous', anonymous::NOT_ANONYMOUS);
        }

        if (get_config('moodleoverflow', 'allowreview') == 1) {
            $possiblesettings = [
                    review::NOTHING => get_string('nothing', 'moodleoverflow'),
                    review::QUESTIONS => get_string('questions', 'moodleoverflow'),
                    review::EVERYTHING => get_string('questions_and_posts', 'moodleoverflow')
            ];

            $mform->addElement('select', 'needsreview', get_string('review', 'moodleoverflow'), $possiblesettings);
            $mform->addHelpButton('needsreview', 'review', 'moodleoverflow');
            $mform->setDefault('needsreview', review::NOTHING);
        }

        // Attachments.
        $mform->addElement('header', 'attachmentshdr', get_string('attachments', 'moodleoverflow'));

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes, 0, get_config('moodleoverflow', 'maxbytes'));
        $choices[1] = get_string('uploadnotallowed');
        $mform->addElement('select', 'maxbytes', get_string('maxattachmentsize', 'moodleoverflow'), $choices);
        $mform->addHelpButton('maxbytes', 'maxattachmentsize', 'moodleoverflow');
        $mform->setDefault('maxbytes', get_config('moodleoverflow', 'maxbytes'));

        $choices = array(
            0   => 0,
            1   => 1,
            2   => 2,
            3   => 3,
            4   => 4,
            5   => 5,
            6   => 6,
            7   => 7,
            8   => 8,
            9   => 9,
            10  => 10,
            20  => 20,
            50  => 50,
            100 => 100
        );
        $mform->addElement('select', 'maxattachments', get_string('maxattachments', 'moodleoverflow'), $choices);
        $mform->addHelpButton('maxattachments', 'maxattachments', 'moodleoverflow');
        $mform->setDefault('maxattachments', get_config('moodleoverflow', 'maxattachments'));

        // Subscription Handling.
        $mform->addElement('header', 'subscriptiontrackingheader', get_string('subscriptiontrackingheader', 'moodleoverflow'));

        // Prepare the array with options for the subscription state.
        $options = array();
        $options[MOODLEOVERFLOW_CHOOSESUBSCRIBE] = get_string('subscriptionoptional', 'moodleoverflow');
        $options[MOODLEOVERFLOW_FORCESUBSCRIBE] = get_string('subscriptionforced', 'moodleoverflow');
        $options[MOODLEOVERFLOW_INITIALSUBSCRIBE] = get_string('subscriptionauto', 'moodleoverflow');
        $options[MOODLEOVERFLOW_DISALLOWSUBSCRIBE] = get_string('subscriptiondisabled', 'moodleoverflow');

        // Create the option to set the subscription state.
        $mform->addElement('select', 'forcesubscribe', get_string('subscriptionmode', 'moodleoverflow'), $options);
        $mform->addHelpButton('forcesubscribe', 'subscriptionmode', 'moodleoverflow');

        // Set the options for the default readtracking.
        $options = array();
        $options[MOODLEOVERFLOW_TRACKING_OPTIONAL] = get_string('trackingoptional', 'moodleoverflow');
        $options[MOODLEOVERFLOW_TRACKING_OFF] = get_string('trackingoff', 'moodleoverflow');
        if (get_config('moodleoverflow', 'allowforcedreadtracking')) {
            $options[MOODLEOVERFLOW_TRACKING_FORCED] = get_string('trackingon', 'moodleoverflow');
        }

        // Create the option to set the readtracking state.
        $mform->addElement('select', 'trackingtype', get_string('trackingtype', 'moodleoverflow'), $options);
        $mform->addHelpButton('trackingtype', 'trackingtype', 'moodleoverflow');

        // Choose the default tracking type.
        $default = get_config('moodleoverflow', 'trackingtype');
        if ((!get_config('moodleoverflow', 'allowforcedreadtracking')) AND ($default == MOODLEOVERFLOW_TRACKING_FORCED)) {
            $default = MOODLEOVERFLOW_TRACKING_OPTIONAL;
        }
        $mform->setDefault('trackingtype', $default);

        // Grade options.
        $mform->addElement('header', 'gradeheading',
            $CFG->branch >= 311 ? get_string('gradenoun') : get_string('grade'));

        $mform->addElement('text', 'grademaxgrade', get_string('modgrademaxgrade', 'grades'));
        $mform->setType('grademaxgrade', PARAM_INT);
        $mform->addRule('grademaxgrade', get_string('grademaxgradeerror', 'moodleoverflow'), 'regex', '/^[0-9]+$/', 'client');

        $mform->addElement('text', 'gradescalefactor', get_string('scalefactor', 'moodleoverflow'));
        $mform->addHelpButton('gradescalefactor', 'scalefactor', 'moodleoverflow');
        $mform->setType('gradescalefactor', PARAM_INT);
        $mform->addRule('gradescalefactor', get_string('scalefactorerror', 'moodleoverflow'), 'regex', '/^[0-9]+$/', 'client');

        if ($this->_features->gradecat) {
            $mform->addElement(
                'select', 'gradecat',
                get_string('gradecategoryonmodform', 'grades'),
                grade_get_categories_menu($COURSE->id, $this->_outcomesused)
            );
            $mform->addHelpButton('gradecat', 'gradecategoryonmodform', 'grades');
        }

        // Rating options.
        $mform->addElement('header', 'ratingheading', get_string('ratingheading', 'moodleoverflow'));

        // Which rating is more important?
        $options = array();
        $options[MOODLEOVERFLOW_PREFERENCE_STARTER] = get_string('starterrating', 'moodleoverflow');
        $options[MOODLEOVERFLOW_PREFERENCE_TEACHER] = get_string('teacherrating', 'moodleoverflow');
        $mform->addElement('select', 'ratingpreference', get_string('ratingpreference', 'moodleoverflow'), $options);
        $mform->addHelpButton('ratingpreference', 'ratingpreference', 'moodleoverflow');
        $mform->setDefault('ratingpreference', MOODLEOVERFLOW_PREFERENCE_STARTER);

        if (get_config('moodleoverflow', 'allowdisablerating') == 1) {
            // Allow Rating.
            $mform->addElement('selectyesno', 'allowrating', get_string('allowrating', 'moodleoverflow'));
            $mform->addHelpButton('allowrating', 'allowrating', 'moodleoverflow');
            $mform->setDefault('allowrating', MOODLEOVERFLOW_RATING_ALLOW);

            // Allow Reputation.
            $mform->addElement('selectyesno', 'allowreputation', get_string('allowreputation', 'moodleoverflow'));
            $mform->addHelpButton('allowreputation', 'allowreputation', 'moodleoverflow');
            $mform->setDefault('allowreputation', MOODLEOVERFLOW_REPUTATION_ALLOW);
        }

        // Course wide reputation?
        $mform->addElement('selectyesno', 'coursewidereputation', get_string('coursewidereputation', 'moodleoverflow'));
        $mform->addHelpButton('coursewidereputation', 'coursewidereputation', 'moodleoverflow');
        $mform->setDefault('coursewidereputation', MOODLEOVERFLOW_REPUTATION_COURSE);
        $mform->hideIf('coursewidereputation', 'anonymous', 'gt', 0);

        // Allow negative reputations?
        $mform->addElement('selectyesno', 'allownegativereputation', get_string('allownegativereputation', 'moodleoverflow'));
        $mform->addHelpButton('allownegativereputation', 'allownegativereputation', 'moodleoverflow');
        $mform->setDefault('allownegativereputation', MOODLEOVERFLOW_REPUTATION_NEGATIVE);
        
        // ecastro ULPGC
        $votes = new stdClass();
        $votes->votescalevote = get_config('moodleoverflow', 'votescalevote');
        $votes->votescaleupvote = get_config('moodleoverflow', 'votescaleupvote');
        $votes->votescaledownvote = get_config('moodleoverflow', 'votescaledownvote');
        $votes->votescalesolved = get_config('moodleoverflow', 'votescalesolved');
        $votes->votescalehelpful = get_config('moodleoverflow', 'votescalehelpful');
        
        $mform->addElement('static', 'staticreputation', get_string('stdreputation', 'moodleoverflow'), get_string('stdreputationcalc', 'moodleoverflow', $votes));
        
        $mform->addElement('advcheckbox', 'repcountvotes', get_string('repcountvotes', 'moodleoverflow'));
        $mform->addHelpButton('repcountvotes', 'repcountvotes', 'moodleoverflow');
        $mform->setDefault('repcountvotes', MOODLEOVERFLOW_RATING_ALLOW);

        $mform->addElement('advcheckbox', 'repcountposts', get_string('repcountposts', 'moodleoverflow'));
        $mform->addHelpButton('repcountposts', 'repcountposts', 'moodleoverflow');
        $mform->setDefault('repcountposts', MOODLEOVERFLOW_RATING_ALLOW);
        
        $mform->addElement('text', 'votefactorpost', get_string('votefactorpost', 'moodleoverflow'), ['size'=>'3']);
        $mform->addHelpButton('votefactorpost', 'votefactorpost', 'moodleoverflow');
        $mform->setType('votefactorpost', PARAM_INT);
        $mform->setDefault('votefactorpost', 100);
        $mform->addRule('votefactorpost', get_string('votefactorerror', 'moodleoverflow'), 'regex', '/^[0-9]+$/', 'client');
        $mform->disabledIf('votefactorpost', 'repcountposts', 'unckecked');
        
        $mform->addElement('text', 'votefactorcomment', get_string('votefactorcomment', 'moodleoverflow'), ['size'=>'3']);
        $mform->addHelpButton('votefactorcomment', 'votefactorcomment', 'moodleoverflow');
        $mform->setType('votefactorcomment', PARAM_INT);        
        $mform->setDefault('votefactorcomment', 100);
        $mform->addRule('votefactorcomment', get_string('votefactorerror', 'moodleoverflow'), 'regex', '/^[0-9]+$/', 'client');
        $mform->disabledIf('votefactorcomment', 'repcountposts', 'unckecked');
        
        $mform->addElement('advcheckbox', 'repcountownvotes', get_string('repcountownvotes', 'moodleoverflow'));
        $mform->addHelpButton('repcountownvotes', 'repcountownvotes', 'moodleoverflow');
        $mform->setDefault('repcountownvotes', 0);

        $mform->addElement('text', 'votefactorvote', get_string('votefactorvote', 'moodleoverflow'), ['size'=>'3']);
        $mform->addHelpButton('votefactorvote', 'votefactorvote', 'moodleoverflow');
        $mform->setType('votefactorvote', PARAM_INT);
        $mform->setDefault('votefactorvote', 25);
        $mform->addRule('votefactorvote', get_string('votefactorerror', 'moodleoverflow'), 'regex', '/^[0-9]+$/', 'client');
        $mform->disabledIf('votefactorvote', 'repcountownvotes', 'unckecked');
        
        
        if(isset($this->current->coursemodule) &&  isset($this->current->votefactorpost)) {
            $votes->votescalepost = $votes->votescalevote * $this->current->votefactorpost/100;
            $votes->votescalecomment = $votes->votescalevote * $this->current->votefactorcomment/100;
            $votes->votescalevote = $votes->votescalevote * $this->current->votefactorvote/100;
            $mform->addElement('static', 'postreputation', get_string('postreputation', 'moodleoverflow'), get_string('postreputationcalc', 'moodleoverflow', $votes));
        }
        
        // locking options.
        $mform->addElement('header', 'lockingheading', get_string('lockingheading', 'moodleoverflow'));

        
        $options = ['' => get_string('locknever', 'moodleoverflow'), 
                    RATING_SOLVED => get_string('locksolved', 'moodleoverflow'),
                    RATING_HELPFUL => get_string('lockhelpful', 'moodleoverflow'),
                    (RATING_HELPFUL+100) => get_string('lockboth', 'moodleoverflow'), 
                    ];
        $mform->addElement('select', 'lockdiscussions', get_string('lockdiscussions', 'moodleoverflow'), $options);
        $mform->addHelpButton('lockdiscussions', 'lockdiscussions', 'moodleoverflow');

        $name = get_string('lockuntildate', 'moodleoverflow');
        $mform->addElement('date_time_selector', 'lockuntildate', $name, array('optional'=>true));
        $mform->addHelpButton('lockuntildate', 'lockuntildate', 'moodleoverflow');
                $mform->disabledIf('lockuntildate', 'lockdiscussions', 'eq', '');
        // ecastro ULPGC        
       
        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        $mform->disabledIf('completionusegrade', 'grademaxgrade', 'in', [0, '']);
        $mform->disabledIf('completionusegrade', 'gradescalefactor', 'in', [0, '']);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
    
    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completiondiscussionsenabled']=
            !empty($default_values['completiondiscussions']) ? 1 : 0;
        if (empty($default_values['completiondiscussions'])) {
            $default_values['completiondiscussions']=1;
        }
        $default_values['completionanswersenabled']=
            !empty($default_values['completionanswers']) ? 1 : 0;
        if (empty($default_values['completionanswers'])) {
            $default_values['completionanswers']=1;
        }
        // Tick by default if Add mode or if completion posts settings is set to 1 or more.
        if (empty($this->_instance) || !empty($default_values['completioncomments'])) {
            $default_values['completioncommentsenabled'] = 1;
        } else {
            $default_values['completioncommentsenabled'] = 0;
        }
        if (empty($default_values['completioncomments'])) {
            $default_values['completioncomments']=1;
        }
        if (empty($default_values['completionsuccess'])) {
            $default_values['completionsuccess']=1;
        }
    }

    /**
     * Add custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionanswersenabled', '', get_string('completionanswers','moodleoverflow'));
        $group[] =& $mform->createElement('text', 'completionanswers', '', array('size'=>3));
        $mform->setType('completionanswers',PARAM_INT);
        $mform->addGroup($group, 'completionanswersgroup', get_string('completionanswersgroup','moodleoverflow'), array(' '), false);
        $mform->disabledIf('completionanswers','completionanswersenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completiondiscussionsenabled', '', get_string('completiondiscussions','moodleoverflow'));
        $group[] =& $mform->createElement('text', 'completiondiscussions', '', array('size'=>3));
        $mform->setType('completiondiscussions',PARAM_INT);
        $mform->addGroup($group, 'completiondiscussionsgroup', get_string('completiondiscussionsgroup','moodleoverflow'), array(' '), false);
        $mform->disabledIf('completiondiscussions','completiondiscussionsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completioncommentsenabled', '', get_string('completioncomments','moodleoverflow'));
        $group[] =& $mform->createElement('text', 'completioncomments', '', array('size'=>3));
        $mform->setType('completioncomments',PARAM_INT);
        $mform->addGroup($group, 'completioncommentsgroup', get_string('completioncommentsgroup','moodleoverflow'), array(' '), false);
        $mform->disabledIf('completioncomments','completioncommentsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completiosuccessenabled', '', get_string('completiosuccess','moodleoverflow'));
        $group[] =& $mform->createElement('text', 'completiosuccess', '', array('size'=>3));
        $mform->setType('completiosuccess',PARAM_INT);
        $mform->addGroup($group, 'completiosuccessgroup', get_string('completiosuccessgroup','moodleoverflow'), array(' '), false);
        $mform->disabledIf('completiosuccess','completiosuccessenabled','notchecked');
        
        return array('completiondiscussionsgroup','completionanswersgroup','completioncommentsgroup', 'completiosuccessgroup');
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completiondiscussionsenabled']) && $data['completiondiscussions']!=0) ||
            (!empty($data['completionanswersenabled']) && $data['completionanswers']!=0) ||
            (!empty($data['completioncommentsenabled']) && $data['completioncomments']!=0) || 
            (!empty($data['completionsuccessenabled']) && $data['completionsuccess']!=0);
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        //parent::data_postprocessing($data);
        if (isset($data->anonymous) && $data->anonymous != anonymous::NOT_ANONYMOUS) {
            $data->coursewidereputation = false;
        }

        // Turn off completion settings if the checkboxes aren't ticked
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiondiscussionsenabled) || !$autocompletion) {
                $data->completiondiscussions = 0;
            }
            if (empty($data->completionanswersenabled) || !$autocompletion) {
                $data->completionanswers = 0;
            }
            if (empty($data->completioncommentsenabled) || !$autocompletion) {
                $data->completioncomments = 0;
            }
            if (empty($data->completioncommentsenabled) || !$autocompletion) {
                $data->completionsuccess = 0;
            }
        }
    }
    
}
