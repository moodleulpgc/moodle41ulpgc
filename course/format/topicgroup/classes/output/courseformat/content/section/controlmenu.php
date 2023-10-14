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
 * Contains the default section controls output class.
 *
 * @package   format_topicgroup
 * @author    Enrique Castro @ULPGC
 * @copyright 2023 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_topicgroup\output\courseformat\content\section;

use format_topics\output\courseformat\content\section\controlmenu as topics_controlmenu;
use moodle_url;
use pix_icon;


/**
 * Base class to render section controls.
 *
 * @package   format_topicgroup
 * @author    Enrique Castro @ULPGC
 * @copyright 2023 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controlmenu extends topics_controlmenu {

    /**
     * Generate the edit control items of a section.
     *
     * This method must remain public until the final deprecation of section_edit_control_items.
     *
     * @return array of edit control items
     */
    public function section_control_items() {

        $format = $this->format;
        $section = $this->section;
        $coursecontext = $format->get_context();

        $controls = [];

        $tgsection = $format->get_section_grouping($this->section);
        $groupingid = (isset($tgsection->groupingid) && $tgsection->groupingid) ? $tgsection->groupingid : 0;

        $url = new moodle_url('/course/format/topicgroup/setgrouping.php', ['id'=>$section->id]);

        $manageall = has_capability('format/topicgroup:manageall', $coursecontext);
        if (!$groupingid && $manageall) {
            $controls['restrictsection'] = [
                    'url' => $url,
                    'icon' => 't/unlock',
                    'name' => get_string('restrictsection', 'format_topicgroup'),
                    'pixattr' => ['class' => ''],
                    'attr' => ['class' => 'icon editing_unsetgrouping',
                               'data-id' => $section->id]
            ];
        }

        if ($groupingid && $manageall ||
                (has_capability('format/topicgroup:manage', $coursecontext) &&
                ($format->is_grouping_member($groupingid))) ) {
            $controls['restrictsection'] = [
                    'url' => $url,
                    'icon' => 'i/edit',
                    'name' => get_string('editrestrictsection', 'format_topicgroup'),
                    'pixattr' => ['class' => ''],
                    'attr' => ['class' => 'icon editing_unsetgrouping',
                               'data-id' => $section->id]
            ];

            $url->param('unset', 1);
            $controls['unrestrictsection'] = [
                    'url' => $url,
                    'icon' => 't/unlocked',
                    'name' => get_string('unrestrictsection', 'format_topicgroup'),
                    'pixattr' => ['class' => ''],
                    'attr' => ['class' => 'icon editing_unsetgrouping',
                               'data-id' => $section->id]
            ];
        }

        $parentcontrols = parent::section_control_items();

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = [];
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }
}
