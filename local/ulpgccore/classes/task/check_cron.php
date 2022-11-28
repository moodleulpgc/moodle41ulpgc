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
 * Meta sync enrolments task.
 *
 * @package   local_ulpgccore
 * @author    Enrique Castro <@ULPGC>
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ulpgccore\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Meta sync enrolments task.
 *
 * @package   local_ulpgccore
 * @author    Enrique Castro <@ULPGC>
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_cron extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('checkcrontrask', 'local_ulpgccore');
    }

    /**
     * Run task for syncing metacat enrolments.
     */
    public function execute() {
        $now = time();
        $config = get_config('local_ulpgccore');

        if($config->croncheckemail) {
            if($config->croncheck && (($now - $config->lastcron)/3600 > $config->croncheck)) {        
                global $SITE;
                $noreply = \core_user::get_noreply_user();
                $user = \core_user::get_support_user();
                $user->mailformat = 1;
                foreach(explode(',', $config->croncheckemail) as $email) {
                    $user->email = trim($email);           
                    $text = $html = $subject = $SITE->shortname. '  cron delayed > '. $config->croncheck;
                    email_to_user($user, $noreply, $subject, $text, $html);
                }
            }
            set_config('lastcron', $now, 'local_ulpgccore');
        }
    }
}
