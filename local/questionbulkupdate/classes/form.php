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
 * Tool for questions bulk update.
 *
 * @package    local_questionbulkupdate
 * @copyright  2021 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_questionbulkupdate;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/question/type/multichoice/questiontype.php');

/**
 * Form for selecting category and question options.
 *
 * @package    local_questionbulkupdate
 * @copyright  2021 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form extends \moodleform {

    /**
     * @var string
     */
    protected $strdonotchange;
    /**
     * @var array
     */
    protected $yesnodonotchange;

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;

        $context = $this->_customdata['context'];

        // Select category.
        $this->definition_select_category($mform, $context);

        $this->strdonotchange = get_string('donotupdate', 'local_questionbulkupdate');
        $this->yesnodonotchange = [
            helper::DO_NOT_CHANGE => $this->strdonotchange,
            0 => get_string('no'),
            1 => get_string('yes')
        ];

        // Common question options.
        $this->definition_common($mform);

        // ecastro ULPGC  hidden & ownership options.
        $this->definition_hidden_and_ownership($mform, $context);

        // Multichoice options.
        $this->definition_multichoice($mform);

        // ecastro ULPGC tags options.
        $this->definition_tags($mform, $context);

        // Action buttons.
        $this->add_action_buttons(true, get_string('updatequestions', 'local_questionbulkupdate'));
    }

    /**
     * Definition for select category block.
     *
     * @param \MoodleQuickForm $mform
     * @param \context $context
     * @throws \coding_exception
     */
    protected function definition_select_category(\MoodleQuickForm $mform, $context) {
        $mform->addElement('header', 'header', get_string('selectcategoryheader', 'local_questionbulkupdate'));

        $qcontexts = new \question_edit_contexts($context);
        $contexts = $qcontexts->having_one_cap([
            'moodle/question:editall',
            'moodle/question:editmine'
        ]);

        $options = array();
        $options['contexts'] = $contexts;
        $options['top'] = true;
        $mform->addElement('questioncategory', 'categoryandcontext', get_string('category', 'question'), $options);

        $defaultcategory = $this->_customdata['defaultcategory']; // ecastro ULPGC
        $mform->setDefault('categoryandcontext', $defaultcategory);

        $mform->addElement(
            'advcheckbox',
            'includingsubcategories',
            get_string('includingsubcategories', 'qtype_random'),
            null,
            null,
            [0, 1]
        );
    }

    /**
     * Definition for common options block.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    protected function definition_common($mform) {
        global $CFG;

        $mform->addElement('header', 'header', get_string('commonoptionsheader', 'local_questionbulkupdate'));

        if ($CFG->version >= 2019052000.00) { // Moodle 3.7.
            $floattype = 'float';
        } else {
            $floattype = 'text';
        }

        $elements = [];
        $elements[] = $mform->createElement(
            $floattype,
            'defaultmark',
            get_string('defaultmark', 'question'),
            ['size' => 7]
        );
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->disabledIf('defaultmark', 'donotupdate_defaultmark', 'checked');
        $elements[] = $mform->createElement(
            'checkbox',
            'donotupdate_defaultmark',
            get_string('donotupdate', 'local_questionbulkupdate')
        );
        $mform->setDefault('donotupdate_defaultmark', true);
        $mform->addGroup($elements, null, get_string('defaultmark', 'question'));

        $penaltyoptions = [helper::DO_NOT_CHANGE => $this->strdonotchange];
        foreach ([1.0000000, 0.5000000, 0.3333333, 0.2500000, 0.2000000, 0.1000000, 0.0000000] as $penalty) {
            $penaltyoptions["{$penalty}"] = (100 * $penalty) . '%';
        }
        $mform->addElement(
            'select',
            'penalty',
            get_string('penaltyforeachincorrecttry', 'question'),
            $penaltyoptions);
        $mform->setDefault('penalty', -1);
    }

    /**
     * Definition for multichoice question options.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    protected function definition_multichoice($mform) {
        $mform->addElement('header', 'header', get_string('pluginname', 'qtype_multichoice'));

        $mform->addElement(
            'select',
            'multichoice_shuffleanswers',
            get_string('shuffleanswers', 'qtype_multichoice'),
            $this->yesnodonotchange
        );
        $mform->setDefault('multichoice_shuffleanswers', helper::DO_NOT_CHANGE);  // ecastro ULPGC enforce helper::DO_NOT_CHANGE

        $mform->addElement(
            'select',
            'multichoice_answernumbering',
            get_string('answernumbering', 'qtype_multichoice'),
            [helper::DO_NOT_CHANGE => $this->strdonotchange] + 
                \qtype_multichoice::get_numbering_styles()  // ecastro ULPGC enforce helper::DO_NOT_CHANGE
        );

/*    array_merge implies a renumbering of all array keys    
            array_merge(
                [helper::DO_NOT_CHANGE => $this->strdonotchange],  // ecastro ULPGC enforce helper::DO_NOT_CHANGE
                \qtype_multichoice::get_numbering_styles()
*/        
        
        $mform->setDefault('multichoice_answernumbering', helper::DO_NOT_CHANGE); // ecastro ULPGC enforce helper::DO_NOT_CHANGE

        if (\get_component_version('qtype_multichoice') >= 2020041600) {
            $mform->addElement(
                'select',
                'multichoice_showstandardinstruction',
                get_string('showstandardinstruction', 'qtype_multichoice'),
                $this->yesnodonotchange
            );
            $mform->setDefault('multichoice_showstandardinstruction', helper::DO_NOT_CHANGE);  // ecastro ULPGC enforce helper::DO_NOT_CHANGE
        }
        // ecastro ULPGC
        // weight grading options
        $gradingoptions = [helper::DO_NOT_CHANGE => $this->strdonotchange, 
                            'fixed' => get_string('weightfixed', 'local_questionbulkupdate'),
                            'formula' => get_string('weightformula', 'local_questionbulkupdate'), 
                         ];
        
        $mform->addElement(
            'select',
            'answergrade',
            get_string('answergrade', 'local_questionbulkupdate'),
            $gradingoptions
        );
        $mform->setDefault('answergrade', helper::DO_NOT_CHANGE);   
        $mform->addHelpButton('answergrade', 'answergrade', 'local_questionbulkupdate');        
        

        $wrongselect = $mform->addElement(
                                        'select',
                                        'answerwrong',
                                        get_string('answerwrong', 'local_questionbulkupdate'),
                                        \question_bank::fraction_options_full(),
                                        ['size' => 6]
                                        
                                    );
        $mform->addHelpButton('answerwrong', 'answerwrong', 'local_questionbulkupdate');        
        $mform->disabledIf('answerwrong', 'answergrade', 'eq', helper::DO_NOT_CHANGE);
        $wrongselect->setMultiple(true);
        
        $mform->addElement(
            'select',
            'answerfraction',
            get_string('grade'),
            \question_bank::fraction_options_full()
        );
        $mform->disabledIf('answerfraction', 'answergrade', 'neq', 'fixed');
        // ecastro ULPGC
    }

    
    /**
     * Definition for question hidden && ownership management
     *
     * @param \MoodleQuickForm $mform
     * @param \context $context
     * @author Enrique Castro @ULPGC
     */
    protected function definition_hidden_and_ownership($mform, $context) {
    
        // visibility 
        $hiddenoptions = [helper::DO_NOT_CHANGE => $this->strdonotchange, 
                            1 => get_string('hiddenhidden', 'local_questionbulkupdate'),
                            0 => get_string('hiddenshow', 'local_questionbulkupdate'),
                            'toggle' => get_string('hiddentoggle', 'local_questionbulkupdate'), 
                         ];
        $mform->addElement(
            'select',
            'hidden',
            get_string('hidden', 'local_questionbulkupdate'),
            $hiddenoptions);
        $mform->setDefault('hidden', helper::DO_NOT_CHANGE);   
        $mform->addHelpButton('hidden', 'hidden', 'local_questionbulkupdate');
        
        // ownership  
        if(has_capability('moodle/question:editall', $context)) { 
            $coursecontext = ($this->_customdata['context'])->get_course_context();
            $names = get_all_user_name_fields(true, 'u');
            $ownerslist = get_enrolled_users($coursecontext, 'moodle/question:editmine', 
                                            0, 'u.id, '.$names, 'u.lastname ASC, u.firstname ASC');
            foreach($ownerslist as $uid => $owner) {
                $ownerslist[$uid] = fullname($owner);
            }
            $ownerslist = [helper::DO_NOT_CHANGE => $this->strdonotchange] + $ownerslist;
            $mform->addElement(
                'select',
                'createdby',
                get_string('createdby', 'question'),
                $ownerslist);
            $mform->setDefault('createdby', helper::DO_NOT_CHANGE);
            $mform->addHelpButton('createdby', 'ownership', 'local_questionbulkupdate');
        
            $mform->addElement(
                'advcheckbox',
                'applyenrolled',
                get_string('applyenrolled', 'local_questionbulkupdate'),
                null,
                null,
                [0, 1]
            );
            $mform->addHelpButton('applyenrolled', 'applyenrolled', 'local_questionbulkupdate');    
        }
    }
   
    /**
     * Definition for tags management
     *
     * @param \MoodleQuickForm $mform
     * @param \context $context
     * @author Enrique Castro @ULPGC
     */
    protected function definition_tags($mform, $context) {
        global $DB;
        
        $mform->addElement('header', 'header', get_string('tags'));    
    
        $tagsoptions = [helper::DO_NOT_CHANGE => $this->strdonotchange, 
                            'add' => get_string('tagsadd', 'local_questionbulkupdate'),
                            'del' => get_string('tagsremove', 'local_questionbulkupdate'),
                        ];
        $mform->addElement(
            'select',
            'tagsmanage',
            get_string('tagsmanage', 'local_questionbulkupdate'),
            $tagsoptions);
        $mform->setDefault('tagsmanage', helper::DO_NOT_CHANGE);   
        $mform->addHelpButton('tagsmanage', 'tagsmanage', 'local_questionbulkupdate');    
    
        // taglist  borrowed from  edit_question_form add_tag_fields() 
        $contexts = array_values($context->get_parent_contexts(true));
        $tags = \core_tag_tag::get_tags_by_area_in_contexts('core_question', 'question', $contexts);
        $tagstrings = [];
        foreach ($tags as $tag) {
            $tagstrings[$tag->name] = $tag->name;
        }        
        $showstandard = \core_tag_area::get_showstandard('core_question', 'question');
        if ($showstandard != \core_tag_tag::HIDE_STANDARD) {
            $namefield = empty($CFG->keeptagnamecase) ? 'name' : 'rawname';
            $standardtags = $DB->get_records('tag',
                    array('isstandard' => 1, 'tagcollid' => \core_tag_area::get_collection('core', 'question')),
                    $namefield, 'id,' . $namefield);
            foreach ($standardtags as $standardtag) {
                $tagstrings[$standardtag->$namefield] = $standardtag->$namefield;
            }
        }        
        
        $options = [
            'tags' => true,
            'multiple' => true,
            'noselectionstring' => get_string('anytags', 'quiz'),
        ];
        $mform->addElement('autocomplete', 'tags',  get_string('tags'), $tagstrings, $options);        
        $mform->disabledIf('tags', 'tagmanage', 'eq', helper::DO_NOT_CHANGE);   
    }
}
