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
 * Displays form for uploading a backed up workflow.
 *
 * @package tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\action;
use tool_lifecycle\local\backup\restore_lifecycle_workflow;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\form\form_upload_workflow;
use tool_lifecycle\local\form\form_workflow_instance;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\table\workflow_definition_table;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_login();
global $OUTPUT, $PAGE, $DB;

\tool_lifecycle\permission_and_navigation::setup_draft();

$PAGE->set_url(new \moodle_url(urls::UPLOAD_WORKFLOW));
$title = get_string('upload_workflow', 'tool_lifecycle');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title, $PAGE->url);

$form = new form_upload_workflow();
if ($form->is_cancelled()) {
    // Cancelled, redirect back to workflow drafts.
    redirect(new moodle_url(urls::WORKFLOW_DRAFTS));
}

$renderer = $PAGE->get_renderer('tool_lifecycle');

if ($data = $form->get_data()) {
    $xmldata = $form->get_file_content('backupfile');
    $restore = new restore_lifecycle_workflow($xmldata);
    $errors = $restore->execute();
    if (count($errors) != 0) {
        /** @var \tool_lifecycle_renderer $renderer */
        $renderer = $PAGE->get_renderer('tool_lifecycle');
        $renderer->render_workflow_upload_form($form, $errors);
        die();
    } else {
        // Redirect to workflow page.
        redirect(urls::WORKFLOW_DETAILS, ['wf' => $restore->get_workflow()->id]);
    }
}

$renderer->render_workflow_upload_form($form);
