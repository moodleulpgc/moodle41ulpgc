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
 * Prints a list of courses from another Moodle instance.
 *
 * @package   block_remote_courses
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/blocks/remote_courses/locallib.php");

/**
 * Loads the block editing form.
 *
 * @package   block_remote_courses
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_remote_courses_edit_form extends block_edit_form {

    /**
     * Defines the block editing form.
     *
     * @param stdClass $mform
     */
    protected function specific_definition($mform) {
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Configure the block title.
        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_remote_courses'));
        $mform->setDefault('config_title', get_string('remote_courses', 'block_remote_courses'));
        $mform->setType('config_title', PARAM_MULTILANG);

        // Remote site.
        $mform->addElement('text', 'config_remotesite', get_string('blockremotesite', 'block_remote_courses'));
        $mform->setType('config_remotesite', PARAM_URL);

        // Webservice token.
        $mform->addElement('text', 'config_wstoken', get_string('blockwstoken', 'block_remote_courses'));
        $mform->setType('config_wstoken', PARAM_NOTAGS);

        // ecastro ULPGC
        $mform->addElement('header', 'coursesheader', get_string('headercourses', 'block_remote_courses'));
        
        $mform->addElement('text', 'config_listheader', get_string('blocklistheader', 'block_remote_courses'));
        $mform->setType('config_listheader', PARAM_TEXT);
        $mform->disabledIf('config_listheader', 'config_courselist', 'eq', '');
        
        // courseslist 
        $mform->addElement('text', 'config_courselist', get_string('blockcourselist', 'block_remote_courses'));
        $mform->setType('config_courselist', PARAM_NOTAGS);
        $mform->addHelpButton('config_courselist', 'blockcourselist', 'block_remote_courses');

        $options = ['id' => get_string('id', 'tag'), 
                           'shortname' => get_string('shortnamecourse'), 
                           'idnumber' => get_string('idnumbercourse'),  ];
        $mform->addElement('select', 'config_coursefield', get_string('blockcoursefield', 'block_remote_courses'), $options);
        $mform->addHelpButton('config_coursefield', 'blockcoursefield', 'block_remote_courses');
        
        $mform->addElement('advcheckbox', 'config_usercourses', get_string('blockusercourses', 'block_remote_courses'));
        $mform->setType('config_usercourses', PARAM_INT);
        $mform->setDefault('config_usercourses', 0);

        $mform->addElement('text', 'config_coursesheader', get_string('blockcoursesheader', 'block_remote_courses'));
        $mform->setType('config_coursesheader', PARAM_TEXT);
        $mform->disabledIf('config_coursesheader', 'config_usercourses', 'unchecked');
        
        // category idnumber
        $mform->addElement('text', 'config_catidnumber', get_string('blockcatidnumber', 'block_remote_courses'));
        $mform->setType('config_catidnumber', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('config_catidnumber', 'blockcatidnumber', 'block_remote_courses');
        $mform->disabledIf('config_catidnumber', 'config_usercourses', 'unchecked');

        $mform->addElement('advcheckbox', 'config_recentactivity', get_string('blockrecentactivity', 'block_remote_courses'));
        $mform->setType('config_recentactivity', PARAM_INT);
        $mform->setDefault('config_recentactivity', 0);
        
        $mform->addElement('header', 'textheader', get_string('headertext', 'block_remote_courses'));
        // show course shortname
        $mform->addElement('advcheckbox', 'config_showshortname', get_string('blockshowshortname', 'block_remote_courses'), ' ', array(0,1));
        $mform->setType('config_showshortname', PARAM_INT);
        //ecastro ULPGC

        // Intro text.
        $mform->addElement('editor', 'config_introtext', get_string('blockintrotext', 'block_remote_courses'));
        $mform->setType('config_introtext', PARAM_RAW);

        // Courses to show.
        $mform->addElement('text', 'config_numcourses',
            get_string('blocknumcourses', 'block_remote_courses'), array('size' => '2'));
        $mform->setDefault('config_numcourses', REMOTE_COURSES_DEFAULT_DISPLAY);
        $mform->setType('config_numcourses', PARAM_INT);
        $mform->setDefault('config_numcourses', 0); // ecastro ULPGC
        
    }
}
