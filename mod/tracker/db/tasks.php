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
 * Definition of Tracker scheduled tasks.
 *
 * @package   mod_tracker
 * @category  task
 * @copyright 2018 Enrique castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'mod_tracker\task\update_priority_stack',
        'blocking' => 0,
        'minute' => '55',
        'hour' => '*/8',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ),

    array(
        'classname' => 'mod_tracker\task\close_answered',
        'blocking' => 0,
        'minute' => '3',
        'hour' => '*/1',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ),

    array(
        'classname' => 'mod_tracker\task\field_autofill',
        'blocking' => 0,
        'minute' => '15',
        'hour' => '6',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ),
    
    array(
        'classname' => 'mod_tracker\task\add_autowatches',
        'blocking' => 0,
        'minute' => '*/15',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )

);
