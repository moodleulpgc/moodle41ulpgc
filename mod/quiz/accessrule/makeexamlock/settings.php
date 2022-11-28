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
 * Implementaton of the quizaccess_makeexamlock plugin.
 *
 * @package   quizaccess_makeexamlock
 * @author    Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

if ($hassiteconfig) {

    $settings->add(new admin_setting_configcheckbox('quizaccess_makeexamlock/enabled',
        get_string('enabled', 'quizaccess_makeexamlock'),
        '',
        1
    ));

    // Allow disable the rule per instance.
    $settings->add(new admin_setting_configcheckbox('quizaccess_makeexamlock/allowdisable',
        get_string('allowdisable', 'quizaccess_makeexamlock'),
        '',
        1
    ));
    // Default enabled state in new instances.
    $settings->add(new admin_setting_configcheckbox('quizaccess_makeexamlock/enabledbydefault',
        get_string('enabledbydefault', 'quizaccess_makeexamlock'),
        '',
        1
    ));

    $options = array('' => get_string('none'),
                    'examreg' => get_string('modeexamreg', 'quizaccess_makeexamlock'),  
                    'idnumber' => get_string('modeidnumber', 'quizaccess_makeexamlock'), );
    $settings->add(new admin_setting_configselect('quizaccess_makeexamlock/examregmode',
        get_string('examregmode', 'quizaccess_makeexamlock'),
        get_string('examregmode_desc', 'quizaccess_makeexamlock'),
        'course',
        $options
    ));    
    
    $settings->add(new admin_setting_configtext('quizaccess_makeexamlock/examreg',
            get_string('examreginstance', 'quizaccess_makeexamlock'),
            get_string('examreginstance_desc', 'quizaccess_makeexamlock'),
            '', PARAM_ALPHANUMEXT
    ));    

    // Allow disable the rule per instance.
    $settings->add(new admin_setting_configcheckbox('quizaccess_makeexamlock/requirebooking',
        get_string('requirebooking', 'quizaccess_makeexamlock'),
        get_string('requirebooking_desc', 'quizaccess_makeexamlock'),
        0
    ));

}
