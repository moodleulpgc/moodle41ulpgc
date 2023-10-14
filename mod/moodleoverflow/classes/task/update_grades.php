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
 * A scheduled task for moodleoverflow cron.
 *
 * @package   mod_moodleoverflow
 * @copyright 2022 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_moodleoverflow\task;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../locallib.php');

/**
 * Class for sending mails to users who have subscribed a moodleoverflow.
 *
 * @package   mod_moodleoverflow
 * @copyright 2022 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_grades extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskupdategrades', 'mod_moodleoverflow');
    }

    /**
     * Runs moodleoverflow cron.
     */
    public function execute() {
        global $DB; 
        
        $lastrun = $this->get_last_run_time(); 

        $sql = "SELECT m.*
                  FROM {moodleoverflow} m 
                 WHERE m.timemodified > ? AND (
                       EXISTS (SELECT 1
                                 FROM {moodleoverflow_posts} p 
                                 JOIN {moodleoverflow_discussions} d ON d.id = p.discussion AND d.moodleoverflow = m.id AND m.course = d.course
                                WHERE d.moodleoverflow = m.id AND p.modified > ?  ) 
                       OR 
                       EXISTS (SELECT 1 
                                 FROM {moodleoverflow_ratings} r              
                                WHERE r.moodleoverflowid = m.id AND r.lastchanged > ?  ) ) ";
        
        $params = [$lastrun, $lastrun, $lastrun];
        $mods = $DB->get_recordset_sql($sql, $params);
        if($mods->valid()) {
            foreach($mods as $mod) {        
                moodleoverflow_update_all_grades_for_cm($mod);
            }
            $mods->close();
        }
        
        return true;
    }

}

