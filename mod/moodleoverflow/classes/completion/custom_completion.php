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

declare(strict_types = 1);

namespace mod_moodleoverflow\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for the moodleoverflow activity.
 *
 * Class for defining mod_moodleoverflow's custom completion rules and fetching the completion statuses
 * of the custom completion rules for a given moodleoverflow instance and a user.
 *
 * @package mod_moodleoverflow
 * @copyright 2023 Enrique Castro @ ULPGC
 * @author ecastro ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $moodleoverflowid = $this->cm->instance;
        // Get moodleoverflow details
        if (!($moodleoverflow=$DB->get_record('moodleoverflow',array('id'=>$moodleoverflowid)))) {
            throw new Exception("Can't find moodleoverflow {$cm->instance}");
        }

        $postcountparams = array('userid'=>$userid,'moodleoverflowid'=>$moodleoverflowid);
        $postcountsql = "SELECT COUNT(1)
                           FROM {moodleoverflow_posts} fp
                     INNER JOIN {moodleoverflow_discussions} fd ON fp.discussion=fd.id
                                -- ratingjoin
                          WHERE fp.userid = :userid AND fd.moodleoverflow = :moodleoverflowid";

        if ($rule == 'completiondiscussions') {
            $status = $moodleoverflow->completiondiscussions <=
                        $DB->count_records('moodleoverflow_discussions',array('moodleoverflow'=>$moodleoverflow->id,'userid'=>$userid));
        } else if ($rule == 'completionanswers') {
            $status = $moodleoverflow->completionanswers <=
                        $DB->get_field_sql($postcountsql.' AND fp.parent = fd.firstpost',$postcountparams);
        } else if ($rule == 'completioncomments') {
            $status = $moodleoverflow->completioncomments <=
                        $DB->get_field_sql($postcountsql.' AND (fp.parent <> fd.firstpost AND fp.parent <> 0)', $postcountparams);
        } else if ($rule == 'completionsuccess') {
            $ratingjoin = 'INNER JOIN {moodleoverflow_ratings} r ON r.postid = fp.id
                            AND r.moodleoverflowid = fd.moodleoverflow
                            AND (r.rating = '.RATING_SOLVED.' OR r.rating = '.RATING_HELPFUL.') ';
            $postcountsql = str_replace('-- ratingjoin',  $ratingjoin, $postcountsql);
            $status = $moodleoverflow->completionsuccess <=
                        $DB->get_field_sql($postcountsql, $postcountparams);
        }
        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completiondiscussions',
            'completionanswers',
            'completioncomments',
            'completionsuccess'
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $completiondiscussions = $this->cm->customdata['customcompletionrules']['completiondiscussions'] ?? 0;
        $completionanswers = $this->cm->customdata['customcompletionrules']['completionanswers'] ?? 0;
        $completioncomments = $this->cm->customdata['customcompletionrules']['completioncomments'] ?? 0;
        $completionsuccess = $this->cm->customdata['customcompletionrules']['completionsuccess'] ?? 0;

        return [
            'completiondiscussions' => get_string('completiondetail:discussions', 'moodleoverflow', $completiondiscussions),
            'completionanswers' => get_string('completiondetail:answers', 'moodleoverflow', $completionanswers),
            'completioncomments' => get_string('completiondetail:comments', 'moodleoverflow', $completioncomments),
            'completionsuccess' => get_string('completiondetail:success', 'moodleoverflow', $completionsuccess),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionanswers',
            'completionsuccess',
            'completioncomments',
            'completiondiscussions',
        ];
    }
}
