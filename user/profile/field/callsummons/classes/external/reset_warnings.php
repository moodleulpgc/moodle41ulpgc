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

namespace profilefield_callsummons\external;

/**
 * External method for resetting course warnings.
 *
 * @package    profilefield_callsummons
 * @category   external
 * @copyright  ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reset_warnings extends \external_api {
    /**
     * Returns description of parameters.
     *
     * @return \external_function_parameters
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            'profilefieldid' => new \external_value(PARAM_INT, 'Profile file id to modify'),
        ]);
    }

    /**
     * Resets course warnings for a profile field.
     *
     * @param int $profilefieldid Id of the profile field to reset.
     * @throws \invalid_parameter_exception
     */
    public static function execute(int $profilefieldid) {
        \external_api::validate_parameters(self::execute_parameters(), [
            'profilefieldid' => $profilefieldid,
        ]);

        $helper = new \profilefield_callsummons\local\helper();
        $helper->unset_time_dimiss($profilefieldid);
    }

    /**
     * Returns description of method result value
     *
     * @return null
     */
    public static function execute_returns() {
        return null;
    }
}
