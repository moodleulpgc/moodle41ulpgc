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
 * Provides the information to restore Concordance questions.
 *
 * @package    qtype_tcs
 * @subpackage backup-moodle2
 * @category   backup
 * @copyright  2020 Université de Montréal
 * @author     Marie-Eve Lévesque <marie-eve.levesque.8@umontreal.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to restore Concordance questions.
 *
 * @package    qtype_tcs
 * @subpackage backup-moodle2
 * @category   backup
 * @copyright  2020 Université de Montréal
 * @author     Marie-Eve Lévesque <marie-eve.levesque.8@umontreal.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_tcs_plugin extends restore_qtype_plugin {

    /**
     * @var string The qtype name.
     */
    protected static $qtypename = 'tcs';

    /**
     * @var string The tcs table name.
     */
    protected static $tablename = 'qtype_tcs';

    /**
     * @var array The name of columns for decoding content.
     */
    protected static $optionsdecodecontent = ['hypothisistext', 'effecttext'];

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        $paths = array();

        // This qtype uses question_answers, add them.
        $this->add_question_question_answers($paths);

        // Add own qtype stuff.
        $elename = static::$qtypename;
        // We used get_recommended_name() so this works.
        $elepath = $this->get_pathfor('/' . $elename);
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the qtype/tcs element.
     *
     * @param array|object $data tcs object to work with.
     */
    public function process_tcs($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = (bool) $this->get_mappingid('question_created', $oldquestionid);

        // If the question has been created by restore, we need to create its
        // qtype_tcs_options too.
        if ($questioncreated) {
            $data->questionid = $newquestionid;

            // It is possible for old backup files to contain unique key violations.
            // We need to check to avoid that.
            if (!$DB->record_exists(static::$tablename . '_options', array('questionid' => $data->questionid))) {
                $newitemid = $DB->insert_record(static::$tablename . '_options', $data);
                $this->set_mapping(static::$tablename . '_options', $oldid, $newitemid);
            }
        }
    }

    /**
     * Do any re-coding necessary in the student response.
     *
     * @param int $questionid the new id of the question
     * @param int $sequencenumber of the step within the qusetion attempt.
     * @param array $response the response data from the backup.
     * @return array the recoded response.
     */
    public function recode_response($questionid, $sequencenumber, array $response) {
        if (array_key_exists('_order', $response)) {
            $response['_order'] = $this->recode_choice_order($response['_order']);
        }
        return $response;
    }

    /**
     * Recode the choice order as stored in the response.
     * @param string $order the original order.
     * @return string the recoded order.
     */
    protected function recode_choice_order($order) {
        $neworder = array();
        foreach (explode(',', $order) as $id) {
            if ($newid = $this->get_mappingid('question_answer', $id)) {
                $neworder[] = $newid;
            }
        }
        return implode(',', $neworder);
    }

    /**
     * Given one question_states record, return the answer
     * recoded pointing to all the restored stuff for tcs questions.
     *
     * Answer are two (hypen speparated) lists of comma separated question_answers
     * the first to specify the order of the answers and the second to specify the
     * responses. Note the order list (the first one) can be optional.
     *
     * @param object $state the state to recode the answer of.
     */
    public function recode_legacy_state_answer($state) {
        $answer = $state->answer;
        $orderarr = array();
        $responsesarr = array();
        $lists = explode(':', $answer);
        // If only 1 list, answer is missing the order list, adjust.
        if (count($lists) == 1) {
            $lists[1] = $lists[0]; // Here we have the responses.
            $lists[0] = '';        // Here we have the order.
        }
        // Map order.
        if (!empty($lists[0])) {
            foreach (explode(',', $lists[0]) as $id) {
                if ($newid = $this->get_mappingid('question_answer', $id)) {
                    $orderarr[] = $newid;
                }
            }
        }
        // Map responses.
        if (!empty($lists[1])) {
            foreach (explode(',', $lists[1]) as $id) {
                if ($newid = $this->get_mappingid('question_answer', $id)) {
                    $responsesarr[] = $newid;
                }
            }
        }
        // Build the final answer, if not order, only responses.
        $result = '';
        if (empty($orderarr)) {
            $result = implode(',', $responsesarr);
        } else {
            $result = implode(',', $orderarr) . ':' . implode(',', $responsesarr);
        }
        return $result;
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder.
     */
    public static function define_decode_contents() {
        $contents = array();

        $fields = array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback');
        $contents[] = new restore_decode_content(static::$tablename . '_options',
                $fields, 'qtype_tcs_options');
        $contents[] = new restore_decode_content(static::$tablename . '_options', static::$optionsdecodecontent, 'qtype_tcs');
        return $contents;
    }
}
