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
 * Block "course overview (campus)" - Upgrade script
 *
 * @package    block_course_termlist
 * @copyright  2019 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade block_course_termlist.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_block_course_termlist_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2019061200) {

        upgrade_plugin_savepoint(true, 2019061200, 'block', 'course_termlist');
    }

    return true;
}

