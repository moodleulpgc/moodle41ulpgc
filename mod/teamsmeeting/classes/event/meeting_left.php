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
 * Plugin event classes are defined here.
 *
 * @package     mod_teamsmeeting
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_teamsmeeting\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The meeting_left event class.
 *
 * @package    mod_teamsmeeting
 * @copyright  2020 Enrique Castro <@ULPGC>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class meeting_left extends \core\event\base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $usertext = "The user with id '$this->userid'";
        if($this->userid != $this->relateduserid) {
            $usertext .= ", acting as user '$this->relateduserid',";
        }
        return "$usertext has left the videoconference '{$this->other['meetingidid']}' in teamsmeeting activity with id '$this->objectid' 
            with course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventmeetingleft', 'mod_teamsmeeting');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/teamsmeeting/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'teamsmeeting';
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['meetingid'])) {
            throw new \coding_exception('The \'meetingid\' value must be set in other.');
        }
    }


}
