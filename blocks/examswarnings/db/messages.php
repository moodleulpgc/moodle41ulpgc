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
 * Defines message providers (types of message sent) for the block examswarnings plugin.
 *
 * @package   block_examswarnings
 * @copyright 2018 Enrieuqe Castro ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$messageproviders = array(
    // Notify teacher that a warning has been created.
    'exam_staff_reminders' => array(
        'capability' => 'block/examswarnings:view'
    ),
    'exam_student_reminders' => array(
        'capability' => 'block/examswarnings:view'
    ),
    'exam_student_warnings' => array(
        'capability' => 'block/examswarnings:view'
    ),
    'exam_teacher_reminders' => array(
        'capability' => 'block/examswarnings:view'
    ),

);
