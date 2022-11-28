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
 * Group observers.
 *
 * @package    mod_examregistrar
 * @copyright  2021 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examregistrar;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/examregistrar/locallib.php');

/**
 * Group observers class.
 *
 * @package    mod_examregistrar
 * @copyright  2021 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_observer {

    /**
     * A user has been booked to an exam 
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function exam_deliver_helper_overrides($event) {
        // check if exam is extracall & get delivery helpers 
        if($deliveryhelpers = examregistrar_exam_has_extracall_delivery($event->other['examid'],
                                                                    $event->other['bookedsite'])) {
            foreach($deliveryhelpers as $helper) {
                examregistrar_process_exam_delivery_user_override($helper, $event->relateduserid);
            }
        } 
    }
}
