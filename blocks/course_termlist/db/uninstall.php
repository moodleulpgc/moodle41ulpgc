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
 * Block "course overview (campus)" - Uninstall file
 *
 * @package    block_course_termlist
 * @copyright  2019 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Plugin uninstall steps.
 */
function xmldb_block_course_termlist_uninstall() {
    global $DB;

    // The plugin uninstall process in Moodle core will take care of removing the plugin configuration, but not of removing the
    // user preferences which we have set for the users. We have to remove them ourselves.
    // We remove them directly from the DB table and don't use unset_user_preference() as the cache is cleared anyway directly
    // after the plugin has been uninstalled.

    $like = $DB->sql_like('name', '?', true, true, false, '|');
    $params = array($DB->sql_like_escape('block_course_termlist-', '|') . '%');
    $DB->delete_records_select('user_preferences', $like, $params);

    $like = $DB->sql_like('name', '?', true, true, false, '|');
    $params = array($DB->sql_like_escape('local_boostctl-', '|') . '%');
    $DB->delete_records_select('user_preferences', $like, $params);

    return true;
}

