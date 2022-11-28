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
 * Block "course overview (campus)" - Privacy provider
 *
 * @package    block_course_termlist
 * @copyright  Enrique Castro <@ULPGC> based on block_course_termlist by Alexander Bias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_termlist\privacy;

use \core_privacy\local\request\writer;
use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementing provider.
 *
 * @package    block_course_termlist
 * @copyright  Enrique Castro <@ULPGC> based on block_course_termlist by Alexander Bias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider,
        \core_privacy\local\request\user_preference_provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised item collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_user_preference('block_course_termlist-selectedterm',
                'privacy:metadata:preference:selectedterm');
        $collection->add_user_preference('block_course_termlist-selectedteacher',
                'privacy:metadata:preference:selectedteacher');
        $collection->add_user_preference('block_course_termlist-selectedcategory',
                'privacy:metadata:preference:selectedcategory');
        $collection->add_user_preference('block_course_termlist-selectedtoplevelcategory',
                'privacy:metadata:preference:selectedtoplevelcategory');
        $collection->add_user_preference('block_course_termlist-hidecourse-',
                'privacy:metadata:preference:hidecourse');
        $collection->add_user_preference('local_boostctl-notshowncourses',
                'privacy:metadata:preference:local_boostctl-notshowncourses');
        $collection->add_user_preference('local_boostctl-activefilters',
                'privacy:metadata:preference:local_boostctl-activefilters');

        return $collection;
    }

    /**
     * Store all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preferences = get_user_preferences();
        foreach ($preferences as $name => $value) {
            $descriptionidentifier = null;

            // User preferences for filters.
            if (strpos($name, 'block_course_termlist-selected') === 0) {
                if ($name == 'block_course_termlist-selectedterm') {
                    $descriptionidentifier = 'privacy:request:preference:selectedterm';
                } else if ($name == 'block_course_termlist-selectedteacher') {
                    $descriptionidentifier = 'privacy:request:preference:selectedteacher';
                } else if ($name == 'block_course_termlist-selectedcategory') {
                    $descriptionidentifier = 'privacy:request:preference:selectedcategory';
                } else if ($name == 'block_course_termlist-selectedtoplevelcategory') {
                    $descriptionidentifier = 'privacy:request:preference:selectedtoplevelcategory';
                }

                if ($descriptionidentifier !== null) {
                    writer::export_user_preference(
                            'block_course_termlist',
                            $name,
                            $value,
                            get_string($descriptionidentifier, 'block_course_termlist', (object) [
                                    'value' => $value,
                            ])
                    );
                }

                // User preferences for hiding stuff.
            } else if (strpos($name, 'block_course_termlist-hide') === 0) {
                if (strpos($name, 'block_course_termlist-hidecourse-') === 0) {
                    $descriptionidentifier = 'privacy:request:preference:hidecourse';
                    $item = substr($name, strlen('block_course_termlist-hidecourse-'));
                }

                if ($descriptionidentifier !== null) {
                    writer::export_user_preference(
                            'block_course_termlist',
                            $name,
                            $value,
                            get_string($descriptionidentifier, 'block_course_termlist', (object) [
                                    'item' => $item,
                                    'value' => $value,
                            ])
                    );
                }

                // User preferences for local_boostcoc.
            } else if (strpos($name, 'local_boostctl-') === 0) {
                if ($name == 'local_boostctl-notshowncourses') {
                    $descriptionidentifier = 'privacy:request:preference:local_boostctl-notshowncourses';
                } else if ($name == 'local_boostctl-activefilters') {
                    $descriptionidentifier = 'privacy:request:preference:local_boostctl-activefilters';
                }

                if ($descriptionidentifier !== null) {
                    writer::export_user_preference(
                            'block_course_termlist',
                            $name,
                            $value,
                            get_string($descriptionidentifier, 'block_course_termlist', (object) [
                                    'value' => $value,
                            ])
                    );
                }
            }
        }
    }
}
