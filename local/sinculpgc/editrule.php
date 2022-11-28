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
 * To create, view rule
 * @package local_sinculpgc
 * @author  Enrique Castro @ ULPGC
 * @copyright  2022 onwards ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_sinculpgc\form\rule_form;
use local_sinculpgc\form\import_form;
use local_sinculpgc\helper;
use local_sinculpgc\sinculpgcrule;

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/sinculpgc/lib.php');

admin_externalpage_setup('local_sinculpgc_managerules');
helper::check_manage_capability();

require_sesskey();

$ruleid = optional_param('ruleid', 0, PARAM_INT);
$enrol = required_param('enrol', PARAM_ALPHANUMEXT);
$action = optional_param('action', 'edit', PARAM_ALPHANUMEXT);

$managerulepage = new moodle_url('/local/sinculpgc/managerules.php');
$thispage = new moodle_url('/local/sinculpgc/editrule.php', ['ruleid' => $ruleid]);

$pagelabel = $ruleid ? 'rule:edit' : 'rule:create';
if($action == 'import') {
    $pagelabel = 'rule:import';
}

$PAGE->set_context(context_system::instance());
$PAGE->navbar->add(get_string($pagelabel, 'local_sinculpgc'));
$PAGE->set_url($thispage);
//$PAGE->requires->js_call_amd('local_sinculpgc/rule_form', 'init', array());

if(!$enrol && $action != 'import') {
    \core\notification::add(get_string('error:noenrol', 'local_sinculpgc'), 
                            \core\output\notification::NOTIFY_ERROR);
    redirect($managerulepage);
}

$rule = sinculpgcrule::get_record(['id' => $ruleid]);
$customdata = [
    'persistent' => $rule,
    'enrol' => $enrol,
    'id' => $ruleid
];

if($action == 'import') {
    $mform = new import_form($thispage, $customdata);
} else {

    $mform = new rule_form($thispage, $customdata);
}

if(!empty($rule) && ($action != 'import')){
    $data = $rule->to_record();
    $mform->set_data($data);
    $instance = helper::extract_enrol_instance($rule);
    $mform->set_data($instance);
}

// Proccess form data.
if ($formdata = $mform->get_data()) {

    if($action == 'import') {
        helper::import_rules($formdata);

    } elseif (!$rule) {
        // Create new rule.
        helper::create_new_rule($formdata);
        
    } else {
        // Update rule.
        helper::update_rule($rule, $formdata);
        
    }
    redirect($managerulepage);
    
} else if ($mform->is_cancelled()) {
    redirect($managerulepage);
}

//////////////////////////////////////////////////////////////////////////////
// here starts user interface

// Display form for new/edit rule.
    //$label = $rule ? 'edit' : 'create';
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string($pagelabel, 'local_sinculpgc'));
    //echo $OUTPUT->heading(get_string('rule:'.$label, 'local_sinculpgc'));
    $mform->display();
    echo $OUTPUT->footer();
