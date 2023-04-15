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
 * ulpgccore lib
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


/**
 * Loads preset file form filesystem and return info array.
 *
 * @param string $presetfile the full path filename for the preset (with full path & extension)
 * @return array preset as associative array
 */
function local_ulpgccore_import_xml_preset(string $presetfile): array {
    $xml = file_get_contents($presetfile);
    $info = (array)simplexml_load_string($xml);
    foreach($info as $key => $value) {
        if(is_a($value, 'SimpleXMLElement')) {
            $info[$key] = (string)$value;
        }
    }
    //print_object($info);
    return $info;
}

/**
 * Convert data object into XML and saves files to filesystem
 *
 * @param string $presetname the filename for the preset (without .xml extension or path)
 * @param string $type either block, profilefield etc.
 * @param stdClass $data the object to serialize into XML
 * @return array preset as associative array
 */
function local_ulpgccore_save_xml_preset(string $presetname, string $type, stdclass $data) {
    global $CFG;

    //This function create a xml object with element PRESET as root.
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $root = $dom->createElement('PRESET');
    $root = $dom->appendChild($root);
    // just in case, ensure no id (to better import afterwards)
    unset($data->id);
    foreach($data as $key => $value) {
        $node = $dom->createElement($key);
        $node = $root->appendChild($node);
        $text = $dom->createTextNode($value);
        $text = $node->appendChild($text);
    }

    $filename = $CFG->dirroot."/local/ulpgccore/presets/$type/$presetname.xml";
    if(file_put_contents($filename, $dom->saveXML(null, LIBXML_NOEMPTYTAG))) {
        \core\notification::success(get_string('presetsaved', 'local_ulpgccore', $presetname));
    } else {
        \core\notification::error(get_string('presetsaveerror', 'local_ulpgccore', $presetname));
    }
}

/**
 * Prints an export icon with action to save an xml preset file
 *
 * @param string $preset the filename for the preset (without .xml extension or path)
 * @param moodle_url $actionurl the url to execute action
 * @param array $params url params for that action
 * @return HTML snippet including action icon
 */
function local_ulpgccore_export_preset_icon(string $preset, \moodle_url $actionurl, array $params): string {
    global $OUTPUT;

    $url = new  \moodle_url($actionurl, $params);
    $confirmaction = new \confirm_action(get_string('confirmpresetexport', 'local_ulpgccore', $preset));
    $icon = new pix_icon('i/export', get_string('preset'.$params['action'], 'local_ulpgccore'));
    return $OUTPUT->action_icon($url, $icon, $confirmaction);
}


function local_ulpgccore_get_reference_course($create = true) {
    global $DB;
    $reference = null;

    $idnumber = get_config('local_ulpgccore', 'referencecourse');
    if(!empty($idnumber)) {
        $reference = $DB->get_field('course', 'id', array('shortname'=>$idnumber));
        if(!$reference && $create) {
            // template/reference course doesn't exist, create one
            $course = \stdClass();
            $course->shortname = $idnumber;
            if(!$categoryid = $DB->get_field('course_categories', 'id', ['name' => 'Plantillas'])) {
                $categoryid = \core_course_category::get_default()->id;
            }
            $course->category = $categoryid;
            $course = create_course($course);
            $reference = $course->id;
        }
    }

    return $reference;
}

function local_ulpgccore_install_reference_course($create = true) {
    global $DB;
    if($bkfile = get_config('local_ulpgccore', 'maintemplate')) {

    }

}

function local_ulpgccore_block_url($block) {
    global $USER;

    $pagetype = trim($block->pagetypepattern, ' -*');
    $params = ['bui_editid' => $block->id, 'edit' => 1];
    if(strpos($block->pagetypepattern, 'course-view') !== false) {
        $params['id'] = local_ulpgccore_get_reference_course(false);
        $params['sesskey'] = sesskey();
        unset($params['edit']);
        $params['notifyeditingon'] = 1;
        $USER->editing = 1;
    } elseif(strpos($block->pagetypepattern, 'mod-quiz-review') !== false) {
        $params['cmid'] = 1;
    }
    $url = new moodle_url('/'.str_replace('-', '/', $pagetype).'.php', $params);

    return html_writer::link($url, $block->blockname);
}


function local_ulpgccore_extract_backup_file($filename) {
    GLOBAL $CFG;
    $this->directory = $directory = $CFG->tempdir . '/backup/';
    $this->filepath = $filepath = $filename . '_folder';
    $archive = $directory . $filename;
    $folder = $directory . $filepath;
    mkdir($folder);
    $fb = get_file_packer('application/vnd.moodle.backup');
    $fb->extract_to_pathname($archive, $folder);
    unlink($archive);
    return true;
}

function local_ulpgccore_update_course() {
    $course_data = new \stdClass();
    $course_data->id = $this->courseid;
    $course_data->fullname = $this->fullname;
    $course_data->shortname = $this->shortname;
    $course_data->visible = 1;
    update_course($course_data);
}

function local_ulpgccore_execute() {
    GLOBAL $CFG, $USER, $DB, $OUTPUT, $PAGE;

    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    // Transaction.
    $transaction = $DB->start_delegated_transaction();
    // Prepare a progress bar which can display optionally during long-running
    // Create new course.
    $folder = $this->filepath; // as found in: $CFG->dataroot . '/temp/backup/'
    $categoryid = $this->category; // e.g. 1 == Miscellaneous
    $userdoingrestore = $USER->id; // e.g. 2 == admin
    $courseid = \restore_dbops::create_new_course('Restored Course', 'RES', $categoryid);
    //$this->courseid = $courseid;

    // Restore backup into course.
    $controller = new \restore_controller($folder, $courseid,
        \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, $userdoingrestore,
        \backup::TARGET_NEW_COURSE);
    $controller->execute_precheck();
    echo $OUTPUT->header();
    $renderer = $PAGE->get_renderer('local_course_creator');
    echo $renderer->display_breadcrumb(2);
    echo get_string('course_restoring', 'local_course_creator');

    // Commit.
    $transaction->allow_commit();

    // Div used to hide the 'progress' step once the page gets onto 'finished'.
    echo \html_writer::start_div('', array('id' => 'executionprogress'));
    // Start displaying the actual progress bar percentage.
    $controller->set_progress(new \core\progress\display());
    $controller->execute_plan();
    echo \html_writer::end_div();
    //echo html_writer::script('document.getElementById("executionprogress").style.display = "none";');
    $controller->destroy();
    //self::delete($this->directory . $this->filepath);
    //self::zupdate_course();
}
