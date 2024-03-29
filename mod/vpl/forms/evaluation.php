<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Atomatic evaluation from link
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../locallib.php');
require_once(dirname(__FILE__).'/../vpl.class.php');
require_once(dirname(__FILE__).'/../vpl_submission_CE.class.php');
require_once(dirname(__FILE__).'/../editor/editor_utility.php');

global $USER, $DB, $OUTPUT;

require_login();

$id = required_param( 'id', PARAM_INT );
$userid = optional_param( 'userid', false, PARAM_INT );
$parms = [ 'id' => $id ];
if ($userid) {
    $parms['userid'] = $userid;
}
$vpl = new mod_vpl( $id );
$vpl->prepare_page( 'forms/evaluation.php',  $parms);
if ((! $userid || $userid == $USER->id) && $vpl->get_instance()->evaluate) { // Evaluate own submission.
    $userid = $USER->id;
    $vpl->require_capability( VPL_SUBMIT_CAPABILITY );
} else { // Evaluate other user submission.
    $vpl->require_capability( VPL_GRADE_CAPABILITY );
}
if ($USER->id == $userid) {
    $vpl->restrictions_check();
}
vpl_editor_util::generate_requires_evaluation();
// Display page.
$vpl->print_header( get_string( 'evaluation', VPL ) );
flush();

echo '<h2>' . s( get_string( 'evaluating', VPL ) ) . '</h2>';
$user = $DB->get_record( 'user', [ 'id' => $userid ] );
$text = ' ' . $vpl->user_picture( $user );
$text .= ' ' . fullname( $user );
echo $OUTPUT->box( $text );
$ajaxurl = "edit.json.php?id={$id}&userid={$userid}&action=";
if (optional_param( 'grading', 0, PARAM_INT )) {
    $inpopup = optional_param( 'inpopup', 0, PARAM_INT );
    $nexturl = "../forms/gradesubmission.php?id={$id}&userid={$userid}&inpopup={$inpopup}";
} else {
    $nexturl = "../forms/submissionview.php?id={$id}&userid={$userid}";
}
vpl_editor_util::print_js_i18n();
vpl_editor_util::generate_evaluate_script( $ajaxurl, $nexturl );
$vpl->print_footer();
