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
 * File containing the form definition to post in the examboard.
 *
 * @package   mod_examboard
 * @copyright Enrique Castro <@ULPGC>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Class to post in a examboard.
 *
 * @package   mod_examboard
 * @copyright Enrique Castro <@ULPGC>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_submission_form extends moodleform {


    /**
     * Form definition
     *
     * @return void
     */
    function definition() {
        global $CFG, $COURSE, $PAGE;

        $mform =& $this->_form;

        $cmid = $this->_customdata['cmid'];
        $action = $this->_customdata['action'];
        $itemid = $this->_customdata['item'];
        $userid = $this->_customdata['userid'];


        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        
        $options =  array(
                'subdirs'=>0,
                'maxbytes'=>0 ,
                'maxfiles'=>0,
                'changeformat'=>0,
                'context'=>null,
                'noclean'=>0,
                'trusttext'=>0,
                'enable_filemanagement' => false);
        $mform->addElement('editor', 'online', get_string('submissiontext', 'examboard'), array('rows' => 5), $options);
        $mform->addHelpButton('online', 'submissiontext', 'examboard');
        
        
        $maxfiles = get_config('examboard', 'uploadmaxfiles');
        $options =  array(
                    'subdirs' => 0,
                    'maxbytes' => get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $COURSE->maxbytes),
                    'maxfiles' => $maxfiles,
                    'accepted_types' => '*',
                    'enable_filemanagement' => true,
                    'return_types' => FILE_INTERNAL | FILE_CONTROLLED_LINK
                    );
        $mform->addElement('filemanager', 'attachments', get_string('files', 'examboard'), 
                            null, $options);
        $mform->addHelpButton('attachments', 'files', 'examboard');


        $mform->addElement('hidden', 'item', $itemid);
        $mform->setType('item', PARAM_INT);

        $mform->addElement('hidden', 'user', $userid);
        $mform->setType('user', PARAM_INT);
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        //-------------------------------------------------------------------------------
        // buttons
        $submitstring = get_string('savechanges');
        $this->add_action_buttons(true, $submitstring);

    }

    /**
     * Form validation
     *
     * @param array $data data from the form.
     * @param array $files files uploaded.
     * @return array of errors.
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        /*
        if (($data['timeend']!=0) && ($data['timestart']!=0) && $data['timeend'] <= $data['timestart']) {
            $errors['timeend'] = get_string('timestartenderror', 'examboard');
        }
        if (empty($data['message']['text'])) {
            $errors['message'] = get_string('erroremptymessage', 'examboard');
        }
        if (empty($data['subject'])) {
            $errors['subject'] = get_string('erroremptysubject', 'examboard');
        }
        */
        return $errors;
    }
}
