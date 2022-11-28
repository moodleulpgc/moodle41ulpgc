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
 * Event fired whenever a user subscribes to a calendar.
 *
 * @package local_sinculpgc
 * @author Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2022 onwards ULPGC
 */

namespace local_sinculpgc\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event fired whenever a user subscribes to a calendar.
 */
class unit_deleted extends \core\event\base {
    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventunitdeleted', 'local_sinculpgc');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        $idnumber = $this->data['other']['idnumber'];
        return "The user with id '$this->userid' has deleted unit {$this->objectid} with idnumber $idnumber. ";
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_sinculpgc_units';
    }
}
