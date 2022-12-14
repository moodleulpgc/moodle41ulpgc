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
 * qtype_kprime grading class for kprime scoringmethod
 *
 * @package     qtype_kprime
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @copyright   2014 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/kprime/grading/qtype_kprime_grading.class.php');

/**
 * Provides grading functionality for kprime scoring metod
 *
 * @package     qtype_kprime
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_kprime_grading_kprime extends qtype_kprime_grading {

    /** @var string TYPE */
    const TYPE = 'kprime';

    /**
     * Returns the scoringmethod name.
     * @return string
     */
    public function get_name() {
        return self::TYPE;
    }

    /**
     * Returns the scoringmethod title.
     * @return string
     */
    public function get_title() {
        return get_string('scoring' . self::TYPE, 'qtype_kprime');
    }

    /**
     * Returns the question's grade for a given response.
     * @param qtype_kprime_question $question
     * @param array $answers
     * @return float
     */
    public function grade_question($question, $answers) {
        $correctrows = 0;
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            $grade = $this->grade_row($question, $key, $row, $answers);
            if ($grade > 0) {
                ++$correctrows;
            }
        }
        // Kprime: If all responses are correct = all points
        // If one response is not correct and all the others are correct = half of the points
        // Otherwise = zero points.
        if ($correctrows == $question->numberofrows) {
            return 1;
        } else if ($correctrows == ($question->numberofrows - 1)) {
            return 0.5;
        } else {
            return 0;
        }
    }
}
