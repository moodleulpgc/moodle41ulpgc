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

namespace mod_studentquiz\event;

/**
 * The mod_studentquiz comment created event.
 *
 * @package    mod_studentquiz
 * @copyright  2017 HSR
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comment_created extends \core\event\comment_created {

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/studentquiz/attempt.php', null);
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' added a comment with id '$this->objectid' on the page with id " .
            "'{$this->other['itemid']}' for the studentquiz with course module id '$this->contextinstanceid'.";
    }
}
