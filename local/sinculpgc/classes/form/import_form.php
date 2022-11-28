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
require_once($CFG->libdir.'/csvlib.class.php');

use local_sinculpgc\helper;
use core_text;
use csv_import_reader;

//class rule_form extends \core\form\persistent {
class import_form extends \moodleform {

    /** @var string Persistent class name. */
    //protected static $persistentclass = 'local_sinculpgc\sinculpgcrule';

    public function definition () {
        $mform =& $this->_form;

        //$enrol = $this->_customdata['enrol'];
        
        $mform->addElement('header', 'importrules', get_string('importrules', 'local_sinculpgc'));

        $fileoptions = array('subdirs'=>0,
                                'maxbytes'=>$course = get_site()->maxbytes,
                                'accepted_types'=>'csv',
                                'maxfiles'=>1,
                                'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'importfile', get_string('uploadafile'), null, $fileoptions);
        $mform->addRule('importfile', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('importfile', 'importfile', 'local_sinculpgc');

        $encodings = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $encodings);
        $mform->addHelpButton('encoding', 'encoding', 'grades');

        $choices = csv_import_reader::get_delimiter_list();
        foreach($choices as $delimiter => $sep) {
            $radio[] = $mform->createElement('radio', 'delimiter', null, get_string('sep'.$delimiter, 'grades'), $delimiter);
        }
        $mform->addGroup($radio, 'delimiter', get_string('separator', 'grades'), ' ', false);
        $mform->addHelpButton('delimiter', 'separator', 'grades');
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter', 'semicolon');
        } else {
            $mform->setDefault('delimiter', 'comma');
        }        

        $mform->addElement('hidden', 'action', 'import');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        
        $mform->addElement('hidden', 'enrol', '');
        $mform->setType('enrol', PARAM_ALPHANUMEXT);
        
         $this->add_action_buttons(true, get_string('importrules', 'local_sinculpgc'));
    }
    
}
