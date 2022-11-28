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
 * Definition of Forum scheduled tasks.
 *
 * @package   mod_examboard
 * @category  task
 * @copyright 2018 Enrique castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'mod_examboard\task\reminder_task',
        'blocking' => 0,
        'minute' => '05',
        'hour' => '8',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ),
    
    array(
        'classname' => 'mod_examboard\task\synch_groups_gradeables_task',
        'blocking' => 0,
        'minute' => '05',
        'hour' => '3',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ),
);
