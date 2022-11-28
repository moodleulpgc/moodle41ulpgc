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
 * Question behaviour for deferred feedback with no negative credit per question
 *
 * @package    qbehaviour
 * @subpackage deferrednonegatives
 * @copyright  2021 Enrique Castro @ULPGC 
 * @copyright based on deferredallnothing by Daniel Thies 2015 dthies@ccal.edu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../deferredfeedback/behaviour.php');

/**
 * Question behaviour for deferred feedback (all-or-nothing).
 *
 * The student enters their response during the attempt, and it is saved. Later,
 * when the whole attempt is finished, their answer is graded, but no partial
 * credit is given for any questions.
 *
 * @copyright  2021 Enrique Castro @ULPGC 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_deferrednonegatives extends qbehaviour_deferredfeedback {

    public function process_finish(question_attempt_pending_step $pendingstep) {
        $keep = parent::process_finish($pendingstep);
        $fraction = $pendingstep->get_fraction();
        if (($keep == question_attempt::KEEP) &&
                ($fraction != null) && ($fraction < 0)) {
            $pendingstep->set_fraction(0);
            $pendingstep->set_state(question_state::$gradedwrong);
        }
        return $keep;
    }

    public function get_state_string($showcorrectness) {
        if ($this->qa->get_state()->is_partially_correct()) {
            return question_state::$gradedwrong->default_string($showcorrectness);
        }
        return $this->qa->get_state()->default_string($showcorrectness);
    }

}
