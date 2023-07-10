<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Bulk user action to change user profile fields.
 *
 * @package     tool_bulkchangeprofilefields
 * @copyright   2022 Daniel Neis Araujo <daniel@adapta.online>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function tool_bulkchangeprofilefields_bulk_user_actions() {
    return [
        'tool_bulkchangeprofilefields_change' =>
            new action_link(
                new moodle_url('/admin/tool/bulkchangeprofilefields/change.php'),
                get_string('changefields', 'tool_bulkchangeprofilefields')
            ),
    ];
}
