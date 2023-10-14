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
 * Strings for component 'report_attendancetools', language 'en'
 *
 * @package   report_attendancetools
 * @copyright 2023 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


$string['attendancetools'] = 'Attendance tools';
$string['attendancetools:view'] = 'View Attendance tools';
$string['attendancetools:manage'] = 'Manage Instant session params';
$string['pluginname'] = 'Attendance tools';
$string['autosession'] = 'Instant session';
$string['milista'] = 'Milista';
$string['asistenciacrue'] = 'External CRUE';
$string['instantconfig'] = 'Default session params';
$string['enabled'] = 'Enabled';
$string['enabled_desc'] = 'If enabled, Instant sessions could be added and per session settings adjusted.';
$string['sessionstart'] = 'Session start';
$string['sessionstart_desc'] = 'The nominal moment in clock\'s circle each instant session will be recorded as starting: 
either whole hours, half hour or quarters.';
$string['sessionoffset'] = 'Session offset';
$string['sessionoffset_desc'] = 'How to determine the nominal session start from the actual hour. Works in combination with the previous setting. 

 * Nearest: the start time will be the nearest target. For instance, if set to quarters, both 11.10 and 11.20 will be recorded as starting at 11:15. 
 * Next: rounded to the next target time. If set to quarters, 11.10 will be recorded as 11.15 but 11.20 as starting at 11:30.
 * Previous: use the previous target ftrom the current time. 11.00 hour if now is 11.10. 

For instance, if now in 11.10 and set "nearest whole hour" then the start will be 11.00. If the cobination is "next quarter", 
session start will be 11.15 ';
$string['off_nearest'] = 'Nearest';
$string['off_next'] = 'Next';
$string['off_prev'] = 'Previous';
$string['start_next'] = 'Whole and half hours';
$string['start_quarter'] = 'Quarters';
$string['start_whole'] = 'Whole hours only';
$string['start_half'] = 'Whole and half hours';
$string['start_quarter'] = 'Quarters';
$string['synccruestatusestask'] = 'Add CRUE attendance status';
