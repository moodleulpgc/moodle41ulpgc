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
 * @package local_ulpgcgroups
 * @author Enrique Castro
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2019 onwards Enrique Castro @ ULPGC
 */

$tasks = [
    [
        'classname' => 'local_ulpgcgroups\task\cohortgroupsync',
        'blocking' => 0,
        'minute' => '5',
        'hour' => '7',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ],
    [
        'classname' => 'local_ulpgcgroups\task\rolegroupsync',
        'blocking' => 0,
        'minute' => '20',
        'hour' => '7',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ],
    [
        'classname' => 'local_ulpgcgroups\task\enrolgroupsync',
        'blocking' => 0,
        'minute' => '35',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],    
];
