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
 * Manage rules
 * @package local_sinculpgc
 * @author  Enrique Castro @ ULPGC
 * @copyright  2022 onwards ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/sinculpgc/lib.php');

use local_sinculpgc\helper;
//use local_sinculpgc\output\rulestable;
use local_sinculpgc\output\rulestable;

admin_externalpage_setup('local_sinculpgc_managerules');
helper::check_manage_capability();

$page = optional_param('page', 0, PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);
$perpage  = optional_param('perpage', 30, PARAM_INT);
$ruleid = optional_param('ruleid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);

$thispage = '/local/sinculpgc/managerules.php';
$editrule = '/local/sinculpgc/editrule.php';

$PAGE->set_url(new moodle_url($thispage));
//$PAGE->requires->js_call_amd('local_sinculpgc/preview', 'init');

$output = $PAGE->get_renderer('local_sinculpgc');

///////////////////////////////////////////////////////////////////////////////
// Process actions

if($action == 'enable') {
    helper::enable_rule($ruleid);
    
} elseif($action == 'disable') {
    helper::disable_rule($ruleid);

} elseif($action == 'run') {
    helper::run_rule($ruleid);
    
} elseif($action == 'reset') {    
    helper::reset_rule($ruleid);
    
} elseif($action == 'remove') {   
    helper::remove_rule($ruleid);    
    
} elseif($action == 'delete') {       
    helper::delete_rule($ruleid);
    
} elseif($action == 'statusoff') {       
    helper::update_enrol_status($ruleid, ENROL_INSTANCE_DISABLED);
    
} elseif($action == 'statuson') {       
    helper::update_enrol_status($ruleid, ENROL_INSTANCE_ENABLED);
}


///////////////////////////////////////////////////////////////////////////////
// Main interface

$table = new rulestable('rulestable_table', new moodle_url($thispage), $page);

$site = get_site();
$filename = clean_filename('sinculpgc_rules_' . $site->shortname . '_'.userdate(time(), 
                                                                                                            '%Y%m%d-%H%M'));
$table->is_downloading($download, $filename, $site->fullname);

if (!$table->is_downloading()) {
    echo $output->header();
    echo $output->heading(get_string('managerules', 'local_sinculpgc'));
    
    $importparams = ['ruleid' => 0, 'enrol' => '', 'action' => 'import',  'sesskey' => sesskey()];
    $url = new moodle_url($editrule, $importparams);   
    echo $output->single_button($url, get_string('importrules', 'local_sinculpgc'), 
                                                                        'post', ['class' => 'importrules fa-pull-right']);
                                                               
    echo $output->print_new_rule_button();
    echo $output->render($table);
    echo $output->footer();
} else {
    // Used when exporting & downloading the table
    // make sure all colums are like DB record Undo combination of group & idnumber
    $cols = $table->columns;
    unset($cols['numused']);
    unset($cols['group']);
    unset($cols['actions']);
    $cols['groupto'] = '';
    $cols['useidnumber'] = '';
    $table->define_columns(array_keys($cols));
    $table->define_headers(array_keys($cols));
    echo $output->render($table);
}
