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
 * Main library of plugin.
 *
 * @package    quizaccess_makeexamlock
 * @copyright  2020 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Checks if current questions correspond to an approved & final exam version
 *
 * @param int $quizid the quiz ID to check
 * @return bool
 */
function quizaccess_makeexamlock_quiz_is_final($quizid) {
    global $DB;
    
    $final = false;
    $quizobj = quiz::create($quizid);
    $quiz = $quizobj->get_quiz();    

    if(isset($quiz->makeexamlock) && $quiz->makeexamlock > 0) {
        if($makeexamattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quizid, 'currentattempt'=>1))) {
            if($exam = $DB->get_record('examregistrar_exams', array('id'=>$makeexamattempt->examid))) {
                if($quiz->makeexamlock == $exam->id &&
                        $makeexamattempt->status >= EXAM_STATUS_APPROVED) { 
                    $final = true;
                }
            }
        }
    }

    return $final;
}  
