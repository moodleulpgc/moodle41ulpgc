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
 * Settings for the attendancetools report 
 *
 * @package   report_attendancetools
 * @copyright 2023 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/report/attendancetools/lib.php');

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'report_attendancetools/enabled',
        get_string('enabled', 'report_attendancetools'),
        get_string('enabled_desc', 'report_attendancetools'),
        1
    ));
    
    $options = [ATTENDANCETOOLS_START_WHOLE => get_string('start_whole', 'report_attendancetools'),
                        ATTENDANCETOOLS_START_HALF => get_string('start_half', 'report_attendancetools'),
                        ATTENDANCETOOLS_START_QUARTER =>get_string('start_quarter', 'report_attendancetools'),
                    ];
    $settings->add(new  admin_setting_configselect(
        'report_attendancetools/sessionstart',
        get_string('sessionstart', 'report_attendancetools'),
        get_string('sessionstart_desc', 'report_attendancetools'),
        ATTENDANCETOOLS_START_WHOLE, $options,
    ));

    $options = [ATTENDANCETOOLS_OFFSET_NEAREST => get_string('off_nearest', 'report_attendancetools'),
                        ATTENDANCETOOLS_OFFSET_NEXT => get_string('off_next', 'report_attendancetools'),
                        ATTENDANCETOOLS_OFFSET_PREV =>get_string('off_prev', 'report_attendancetools'),
                    ];
    $settings->add(new  admin_setting_configselect(
        'report_attendancetools/sessionoffset',
        get_string('sessionoffset', 'report_attendancetools'),
        get_string('sessionoffset_desc', 'report_attendancetools'),
        ATTENDANCETOOLS_OFFSET_NEAREST, $options,
    ));
    
    
}
