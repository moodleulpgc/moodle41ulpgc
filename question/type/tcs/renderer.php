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
 * tcs question renderer class.
 *
 * @package qtype_tcs
 * @copyright  2020 Université de Montréal
 * @author     Marie-Eve Lévesque <marie-eve.levesque.8@umontreal.ca>
 * @copyright  based on work by 2014 Julien Girardot <julien.girardot@actimage.com>

 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for tcs questions.
 *
 * @copyright  2020 Université de Montréal
 * @author     Marie-Eve Lévesque <marie-eve.levesque.8@umontreal.ca>
 * @copyright  based on work by 2014 Julien Girardot <julien.girardot@actimage.com>

 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_tcs_renderer extends qtype_with_combined_feedback_renderer {

    /**
     * @var string The qtype name.
     */
    protected static $qtypename = 'tcs';

    /**
     * Whether a choice should be considered right, wrong or partially right.
     * @param question_answer $ans representing one of the choices.
     * @return fload 1.0, 0.0 or something in between, respectively.
     */
    protected function is_right(question_answer $ans) {
        return $ans->fraction;
    }

    /**
     * Generate the display of the formulation part of the question. This is the
     * area that contains the quetsion text, and the controls for students to
     * input their answers. Some question types also embed bits of feedback, for
     * example ticks and crosses, in this area.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $question   = $qa->get_question();
        $responseoutput = $question->get_format_renderer($this->page);
        $questiontext = $question->format_questiontext($qa);
        $showeffect = !empty($question->labeleffecttext) && !empty($question->effecttext);

        $result = '';
        $result .= html_writer::start_div('tcs-container');
        if (!empty($question->showquestiontext)) {
            $labelsituation = ($question->labelsituation === null) ? get_string('situation', 'qtype_tcs') :
                $question->labelsituation;
            $result .= html_writer::tag('p', html_writer::tag('strong', $labelsituation));
            $result .= html_writer::tag('div', $questiontext, array('class' => 'qtext'));
        }

        // Show "This question is outside my field of competence" field.
        $isoutsidefieldcompetencechecked = false;
        if (intval($question->showoutsidefieldcompetence) === 1) {
            $inputname = $qa->get_qt_field_name('outsidefieldcompetence');
            $inputattributeshidden = [];
            $inputattributeshidden['type']    = 'hidden';
            $inputattributeshidden['name']    = $inputname;
            $inputattributeshidden['id']      = $inputname;
            $inputattributeshidden['class']   = 'm-r-1 outsidefieldcompetence';

            $inputattributes = [];
            if ($options->readonly) {
                $inputattributes['disabled'] = 'disabled';
                $inputattributeshidden['disabled'] = 'disabled';
            }
            $labeltext = get_string('labeloutsidefieldcompetence', 'qtype_tcs');
            $step = $qa->get_last_step_with_qt_var('outsidefieldcompetence');
            if (!$step->has_qt_var('outsidefieldcompetence')) {
                $step = new question_attempt_step(array('outsidefieldcompetence' => 0));
            }
            $isoutsidefieldcompetencechecked = $step->get_qt_var('outsidefieldcompetence');
            if ($isoutsidefieldcompetencechecked) {
                $inputattributes['checked'] = 'checked';
                $inputattributeshidden['value']   = 1;
            } else {
                unset($inputattributes['checked']);
                $inputattributeshidden['value']   = 0;
            }
            $inputattributes['type']    = 'checkbox';
            $inputattributes['value']   = 1;
            $inputattributes['id']      = $inputname . '-control';
            $inputattributes['class']   = 'm-r-1 outsidefieldcompetence';
            $label = html_writer::tag('label', $labeltext, ['for' => $inputattributes['id']]);
            $result .= html_writer::empty_tag('input', $inputattributes) . $label;
            $result .= html_writer::empty_tag('input', $inputattributeshidden);
            $this->page->requires->js_call_amd('qtype_tcs/tcs', 'init',
                ['outsidefieldcompetenceid' => $inputattributes['id'], $inputattributeshidden['id']]);
        }

        $newinformationeffect = ($question->labelnewinformationeffect === null) ? get_string('newinformationeffect', 'qtype_tcs') :
            $question->labelnewinformationeffect;
        // Show hypothesis.
        $hypothisistext = $question->format_text($question->hypothisistext, $question->hypothisistext, $qa,
            'qtype_' . static::$qtypename, 'hypothisistext', $question->id);

        $result .= html_writer::start_div('Rtable Rtable--2cols Rtable--collapse');
        $result .= html_writer::div($question->labelhypothisistext, 'Rtable-cell Rtable-cell--head', ['style' => 'order:0;']);
        $result .= html_writer::div($hypothisistext, 'Rtable-cell', ['style' => 'order:1;']);
        // Show effect on hypothesis.
        if ($showeffect) {
            $effecttext = $question->format_text($question->effecttext, $question->effecttext, $qa, 'qtype_tcs', 'effecttext',
                $question->id);
            $result .= html_writer::div($question->labeleffecttext, 'Rtable-cell Rtable-cell--head', ['style' => 'order:0;']);
            $result .= html_writer::div($effecttext, 'Rtable-cell', ['style' => 'order:1;']);
            $result .= html_writer::end_div();
            $result .= html_writer::start_div('Rtable Rtable--collapse');
        }
        // Show answers.
        $result .= html_writer::div($newinformationeffect, 'Rtable-cell Rtable-cell--head', ['style' => 'order:0;']);
        $formanswer = $this->get_answers_result($qa, $options, $isoutsidefieldcompetencechecked);
        $result .= html_writer::div($formanswer, 'Rtable-cell', ['style' => 'order:1;']);
        $result .= html_writer::end_div();

        // Show answer feedback.
        if (intval($question->showfeedback) === 1) {
            $inputname = $qa->get_qt_field_name('answerfeedback');
            $labelfeedback = ($question->labelfeedback === null) ? get_string('feedback', 'qtype_tcs') :
                $question->labelfeedback;
            $result .= html_writer::label($labelfeedback, $inputname);
            $step = $qa->get_last_step_with_qt_var('answerfeedback');
            if (!$step->has_qt_var('answerfeedback') && empty($options->readonly)) {
                $step = new question_attempt_step(array('answerfeedback' => ''));
            }
            if (empty($options->readonly)) {
                $attributes = $isoutsidefieldcompetencechecked ? ['disabled' => true] : [];
                $answer = $responseoutput->response_area_input('answerfeedback', $qa, $step, $attributes);
            } else {
                $answer = html_writer::tag('p', $step->get_qt_var('answerfeedback'),
                        ['id' => $inputname, 'class' => 'p-2 whitebackground']);
            }

            $result .= html_writer::tag('div', $answer, array('class' => 'answerfeedback'));
        }

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($qa->get_last_qt_data()),
                    array('class' => 'validationerror'));
        }
        $result .= html_writer::end_div();
        return $result;
    }

    /**
     * Get answers result.
     * @param question_attempt $qa
     * @param question_display_options $options
     * @param int $disabled
     * @return string HTML answers
     */
    public function get_answers_result(question_attempt $qa, question_display_options $options, $disabled = 0) {
        $radiobuttons = array();
        $feedback = array();
        $classes = array();
        $displayfeedback = false;

        $question = $qa->get_question();
        $response = $question->get_response($qa);

        $inputname = $qa->get_qt_field_name('answer');
        $inputattributes = array(
            'type' => 'radio',
            'name' => $inputname,
        );

        if ($options->readonly || $disabled) {
            $inputattributes['disabled'] = 'disabled';
        }

        $maxfraction = $question->get_max_fraction($question->answers);

        foreach ($question->get_order($qa) as $value => $ansid) {
            $ans = $question->answers[$ansid];
            $inputattributes['name']    = $qa->get_qt_field_name('answer');
            $inputattributes['value']   = $value;
            $inputattributes['id']      = $qa->get_qt_field_name('answer' . $value);

            $isselected = $question->is_choice_selected($response, $value);
            if ($isselected) {
                $inputattributes['checked'] = 'checked';
            } else {
                unset($inputattributes['checked']);
            }

            $label = html_writer::tag('label',
                    $question->make_html_inline(
                        $question->format_text($ans->answer, $ans->answerformat, $qa, 'question', 'answer', $ansid)
                    ),
                    array('for' => $inputattributes['id'])
                );
            $radiobuttons[] = html_writer::empty_tag('input', $inputattributes) . $label;

            // Param $options->suppresschoicefeedback is a hack specific to the
            // oumultiresponse question type. It would be good to refactor to
            // avoid refering to it here.
            $classes[] = 'r' . ($value % 2);
            if ($options->correctness) {
                if ($maxfraction == 0) {
                    $percent = 0;
                } else {
                    $percent = round(($ans->fraction / $maxfraction) * 100);
                }
                $feedbackstruct  = html_writer::start_div('progress-container');
                $feedbackstruct .= html_writer::start_div('progress', array('style' => 'height: 20px;'));
                $feedbackstruct .= html_writer::div('', 'progress-bar', array('style' => 'width:'.$percent.'%',
                            'role' => 'progressbar', 'aria-valuenow' => $ans->fraction, 'aria-valuemax' => $maxfraction));
                $feedbackstruct .= html_writer::end_div();
                $feedbackstruct .= html_writer::tag('span', (int)$ans->fraction);
                $feedbackstruct .= html_writer::end_div();
                $feedback[] = $feedbackstruct;
                if ($displayfeedback === false && $percent > 0) {
                    $displayfeedback = true;
                }
            } else {
                $feedback[] = '';
            }
        }
        if (!$displayfeedback) {
            $feedback = array_fill_keys(array_keys($feedback), '');
        }
        $result = '';
        $result .= html_writer::start_tag('div', array('class' => 'answer'));

        foreach ($radiobuttons as $key => $radio) {
            $radioanswer = html_writer::div($radio, 'answer-item');
            $feedbackanswer = ($feedback[$key]) ? html_writer::div($feedback[$key], 'feedback-item') : '';
            $result .= html_writer::tag('div', $radioanswer . ' ' . $feedbackanswer, ['class' => 'answer-line']) . "\n";
        }

        $result .= html_writer::end_tag('div'); // Answer.

        return $result;
    }

    /**
     * Returns the specific feedbacks of all answers.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    public function specific_feedback(question_attempt $qa) {
        $strfeedback = html_writer::nonempty_tag('p', $this->combined_feedback($qa));

        $question = $qa->get_question();

        foreach ($question->get_order($qa) as $ansid) {
            $answer = $question->answers[$ansid];
            $trimmedfeedback = $question->make_html_inline($answer->feedback);
            $trimmedanswer = $question->make_html_inline($answer->answer);
            if (!empty($trimmedfeedback)) {
                $str = $question->format_text($trimmedanswer, $answer->answerformat, $qa, 'question', 'answer', $answer->id);
                $strfeedback .= html_writer::tag('p', html_writer::tag('strong', $str));
                // Do not use $trimmedfeedback here, just a regular trim.
                $strfeedback .= html_writer::start_tag('div', array('class' => 'tcs-answerspecificfeedback'));
                $strfeedback .= $question->format_text(trim($answer->feedback), $answer->feedbackformat,
                    $qa, 'question', 'answerfeedback', $answer->id);
                $strfeedback .= html_writer::end_tag('div');
            }
        }
        return $strfeedback;
    }

    /**
     * Redefines the parent function to change the order of the feedbacks.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function feedback(question_attempt $qa, question_display_options $options) {
        $output = '';
        $hint = null;

        if ($options->numpartscorrect) {
            $output .= html_writer::nonempty_tag('div', $this->num_parts_correct($qa),
                    array('class' => 'numpartscorrect'));
        }

        if ($hint) {
            $output .= $this->hint($qa, $hint);
        }

        if ($options->generalfeedback) {
            $output .= html_writer::nonempty_tag('div', $this->general_feedback($qa),
                    array('class' => 'generalfeedback'));
        }

        if ($options->rightanswer) {
            $output .= html_writer::nonempty_tag('div', $this->correct_response($qa),
                    array('class' => 'rightanswer'));
        }

        // The specific feedback goes at the end.
        if ($options->feedback) {
            $output .= html_writer::nonempty_tag('div', $this->specific_feedback($qa),
                    array('class' => 'specificfeedback'));
            $hint = $qa->get_applicable_hint();
        }

        return $output;
    }

    /**
     * Gereate an automatic description of the correct response to this question.
     * Not all question types can do this. If it is not possible, this method
     * should just return an empty string.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();

        $right = array();
        $maxfraction = $this->get_max_fraction($question->answers);

        foreach ($question->answers as $ansid => $ans) {
            if ((string) $ans->fraction === (string) $maxfraction) {
                $right[] = $question->make_html_inline($question->format_text($ans->answer, $ans->answerformat,
                            $qa, 'question', 'answer', $ansid));
            }
        }

        if (!empty($right)) {
                return get_string('correctansweris', 'qtype_tcs',
                        implode(', ', $right));
        }

        return '';
    }

    /**
     * Get max fraction.
     * @param array $arranswers
     * @return int
     */
    public function get_max_fraction($arranswers) {
        $max = 0;

        foreach ($arranswers as $answer) {
            if ($answer->fraction > $max) {
                $max = $answer->fraction;
            }
        }

        return $max;
    }
}

/**
 * An tcs format renderer for tcs where the student should use a plain input box.
 *
 * @package qtype_tcs
 * @copyright  2020 Université  de Montréal.
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_tcs_format_plain_renderer extends plugin_renderer_base {

    /**
     * Return the HTML for the textarea.
     *
     * @param string $response content of the textarea
     * @param array $attributes textarea attributes
     * @return string the HTML for the textarea.
     */
    protected function textarea($response, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_tcs_response';
        $attributes['rows'] = 7;
        return html_writer::tag('textarea', s($response), $attributes);
    }

    /**
     * Return class name.
     *
     * @return string class name
     */
    protected function class_name() {
        return 'qtype_tcs_plain';
    }

    /**
     * Return the HTML for the textarea.
     *
     * @param string $name the name of the textarea
     * @param question_attempt $qa
     * @param question_attempt_step $step
     * @param array $attributes textarea attributes
     * @return string the HTML for the textarea.
     */
    public function response_area_input($name, $qa, $step, $attributes = []) {
        $inputname = $qa->get_qt_field_name($name);
        $attributes += ['name' => $inputname, 'id' => $inputname];
        return $this->textarea($step->get_qt_var($name), $attributes) .
                html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));
    }
}
