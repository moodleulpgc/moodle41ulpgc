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
 * @package format_topicgroup
 * @author Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2023, Andrew Hancox
 */

namespace format_topicgroup\output\courseformat\content;

use format_topicgroup;
use core_courseformat\output\local\content\section as section_base;
use renderer_base;
use moodle_url;

class section extends section_base {

    public function export_for_template(renderer_base $output): \stdClass {
        $model = parent::export_for_template($output);

        $model->groupinginfo = '';
        $model->groupingedit = '';
        $section = $this->format->get_section_grouping($this->section);

        if(!empty($section->groupingid)) {
            $coursecontext = $this->format->get_context();
            // can view
            $caps = ['moodle/course:manageactivities', 'moodle/course:viewhiddenactivities', 'format/topicgroup:viewhidden'];
            if(has_any_capability($caps, $coursecontext)) {
                $model->groupinginfo = get_string('restrictedsectionlbl', 'format_topicgroup', $section->groupingname);
            }

            // manageall or manage & (allgroups or is own)
            if(has_capability('format/topicgroup:manageall', $coursecontext) ||
                        (has_capability('format/topicgroup:manage', $coursecontext) &&
                            ($this->format->is_grouping_member($section->groupingid))) ) {
                $url = new moodle_url('/course/format/topicgroup/setgrouping.php', ['id'=>$this->section->id]);
                $label = get_string('changerestrictsection', 'format_topicgroup', $section->groupingname);
                $icon = new \pix_icon('locked', $label,'format_topicgroup', ['class' => 'icon-large']);
                $model->groupingedit = $output->action_icon($url, $icon);
            }
        }
        return $model;
    }


    public function get_template_name(renderer_base $renderer): string {
        return "format_topicgroup/local/content/section";
    }
}
