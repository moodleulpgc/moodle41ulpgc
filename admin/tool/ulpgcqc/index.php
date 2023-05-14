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
 * Platform status & checkings report
 *
 * @package     tool_ulpgcqc
 * @copyright   2023 Enrique Castro @ ULPGC
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/lib/adminlib.php');

require_login(null, false);
require_capability('tool/ulpgcqc:view', context_system::instance());

$report = optional_param('report', '', PARAM_ALPHANUMEXT); // Show detailed info about one check only.
$detail = optional_param('detail', '', PARAM_TEXT); // Show detailed info about one check only.

$pagename =  'tool_ulpgcqc_report';
if($report) {
    $pagename .= '_' . $report;
}
admin_externalpage_setup($pagename, '', null, '', ['pagelayout' => 'report']);

$url = new moodle_url('/admin/tool/ulpgcqc/index.php', ['report' => $report]);
$table = new tool_ulpgcqc\check\table($report, $url, $detail);

if (!empty($table->detail)) {
    $PAGE->set_docs_path($url . '?detail=' . $detail);
    $PAGE->navbar->add($table->detail->get_name());
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_ulpgcqc'));

// prepare tabs
$tabs = []
foreach(['summary', 'general', 'config', 'courses', 'users'] as $tab) {
    $tabs[] = new tabobject($tab, new moodle_url('/admin/tool/ulpgcqc/index.php', ['report' => $tab]),
                            get_string('tab'.$tab, 'tool_ulpgcqc'))
}
echo $OUTPUT->tabtree($tabs, $report);

echo $table->render($OUTPUT);
echo $OUTPUT->footer();
