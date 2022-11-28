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
 * ulpgctf format question importer.
 *
 * @package    qformat_ulpgctf
 * @copyright  2014 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * ulpgctf format - a simple format for creating multiple choice questions (with
 * only one correct choice, and no feedback). Based un AIKEN format
 *
 * The format looks like this:
 *
 * Question text
 * A) Choice #1
 * B) Choice #2
 * C) Choice #3
 * D) Choice #4
 * NAME: name of the question
 * PG: 25
 * ANSWER: B
 *
 * That is,
 *  + question text all one one line.
 *  + then a number of choices, one to a line. Each line must comprise a letter,
 *    then ')' or '.', then a space, then the choice text.
 *  + Then a line of the form 'ANSWER: X' to indicate the correct answer.
 *  + Then a line of the form 'PG: xxx'to indicate the manual page to look for the answer
 *
 * Be sure to word "All of the above" type choices like "All of these" in
 * case choices are being shuffled.
 *
 * @copyright  2014 Enrique Castro, ULOGC based on 2003 Tom Robb <tom@robb.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_ulpgctf extends qformat_default {

    public function provide_import() {
        return true;
    }

    public function provide_export() {
        return false;
    }

    public function export_file_extension() {
        return '.txt';
    }

    public function readquestions($lines) {
        $questions = array();
        $question = $this->defaultquestion();
        $endchar = chr(13);
        foreach ($lines as $line) {
            $stp = strpos($line, $endchar, 0);
            $newlines = explode($endchar, $line);
            $linescount = count($newlines);
            for ($i=0; $i < $linescount; $i++) {
                $nowline = trim($newlines[$i]);
                // Go through the array and build an object called $question
                // When done, add $question to $questions.
                if (strlen($nowline) < 2) {
                    continue;
                }
                if (preg_match('/^[\/][\/]/', $nowline)) {
                    // this is a comment
                    continue;
                }
                if (preg_match('/^[A-Z][).][ \t]/', $nowline)) {
                    // A choice. Trim off the label and space, then save.
                    $question->answer[] = $this->text_field(
                            htmlspecialchars(trim(substr($nowline, 2)), ENT_NOQUOTES));
                    $question->fraction[] = 0;
                    $question->feedback[] = $this->text_field('');
                } elseif(preg_match('/^NAME:/', $nowline)) {
                    $question->name = htmlspecialchars(trim(substr($nowline, strpos($nowline, ':') + 1)), ENT_NOQUOTES);
                } elseif(preg_match('/^PG:/', $nowline)) {
                    $question->generalfeedback = htmlspecialchars(trim(substr($nowline, strpos($nowline, ':') + 1)), ENT_NOQUOTES);
                } elseif(preg_match('/^NEGATIVOS/', $nowline)) {
                    $question->negatives = true;
                    $weight = round(-1/(count($question->fraction) - 1), 5);
                    foreach($question->answer as $i => $val) {
                        $question->fraction[$i] = $weight;
                    }
                } elseif(preg_match('/^ANSWER:/', $nowline)) {
                    // The line that indicates the correct answer. This question is finised.
                    $ans = trim(substr($nowline, strpos($nowline, ':') + 1));
                    $ans = substr($ans, 0, 1);
                    // We want to map A to 0, B to 1, etc.
                    $rightans = ord($ans) - ord('A');
                    $question->fraction[$rightans] = 1;
                    if(!isset($question->name) || !$question->name) {
                        $question->name = $this->create_default_question_name($question->questiontext, get_string('questionname', 'question'));
                    }
                    $questions[] = $question;

                    // Clear array for next question set.
                    $question = $this->defaultquestion();
                    continue;
                } else {
                    // Must be the first line of a new question, since no recognised prefix.
                    $question->qtype = 'multichoice';
                    $question->name = '';
                    $question->generalfeedback = '';
                    $question->questiontext = htmlspecialchars(trim($nowline), ENT_NOQUOTES);
                    $question->questiontextformat = FORMAT_HTML;
                    $question->generalfeedback = '';
                    $question->generalfeedbackformat = FORMAT_HTML;
                    $question->single = 1;
                    $question->answer = array();
                    $question->fraction = array();
                    $question->feedback = array();
                    $question->correctfeedback = $this->text_field('');
                    $question->partiallycorrectfeedback = $this->text_field('');
                    $question->incorrectfeedback = $this->text_field('');
                    $question->negatives = false;
                }
            }
        }
        return $questions;
    }

    protected function text_field($text) {
        return array(
            'text' => htmlspecialchars(trim($text), ENT_NOQUOTES),
            'format' => FORMAT_HTML,
            'files' => array(),
        );
    }

    public function readquestion($lines) {
        // This is no longer needed but might still be called by default.php.
        return;
    }


    public function writequestion($question) {
        global $OUTPUT;

        $unknownformat = get_string('unknown', 'format_ulpgctf');

        // Start with a comment.
        $expout = "// question: {$question->id}  ";
        if($question->qtype != 'multichoice') {
            $expout .= " name: {$question->name}  $unknownformat \n";
            return $expout;
        }
        $expout .= "\n";

        $expout .= $this->write_questiontext($question->questiontext, $question->questiontextformat);
        $expout .= "\n";

        $letter = 'A';
        $rightanswer = '';
        foreach ($question->options->answers as $answer) {
            if ($answer->fraction == 1) {
                $rightanswer = $letter;
            }
            $expout .= "{$letter}) {$answer->answer} \n";
            $letter = ++$letter;
        }

        $expout .= "NAME: {$question->name} \n";
        $expout .= "NAME: {$question->generalfeedback} \n";
        $expout .= "ANSWER: {$rightanswer} \n";
        $expout .= "\n";

        return $expout;
    }

}


