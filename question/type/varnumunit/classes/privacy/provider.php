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
 * Privacy Subsystem implementation for qtype_varnumunit.
 *
 * @package    qtype_varnumunit
 * @copyright  2018 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_varnumunit\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;
use stdClass;

/**
 * Privacy Subsystem for qtype_varnumunit implementing user_preference_provider.
 *
 * @copyright  2018 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This component has data.
        // We need to return default options that have been set a user preferences.
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\user_preference_provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference('qtype_varnumunit_defaultmark', 'privacy:preference:defaultmark');
        $collection->add_user_preference('qtype_varnumunit_penalty', 'privacy:preference:penalty');
        $collection->add_user_preference('qtype_varnumunit_unitfraction', 'privacy:preference:unitfraction');
        return $collection;
    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preference = get_user_preferences('qtype_varnumunit_defaultmark', null, $userid);
        if (null !== $preference) {
            $desc = get_string('privacy:preference:defaultmark', 'qtype_varnumunit');
            writer::export_user_preference('qtype_varnumunit', 'defaultmark', $preference, $desc);
        }

        $preference = get_user_preferences('qtype_varnumunit_penalty', null, $userid);
        if (null !== $preference) {
            $desc = get_string('privacy:preference:penalty', 'qtype_varnumunit');
            writer::export_user_preference('qtype_varnumunit', 'penalty', transform::percentage($preference), $desc);
        }

        $preference = get_user_preferences('qtype_varnumunit_unitfraction', null, $userid);
        if (null !== $preference) {
            $a = new stdClass();
            $a->num = (100 * (1 - $preference)) . '%';
            $a->unit = (100 * $preference) . '%';
            $stringvalue = get_string('percentgradefornumandunit', 'qtype_varnumunit', $a);
            $desc = get_string('privacy:preference:unitfraction', 'qtype_varnumunit');
            writer::export_user_preference('qtype_varnumunit', 'unitfraction', $stringvalue, $desc);
        }
    }
}
