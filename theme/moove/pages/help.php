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
 * Theme Boost Union - Help page.
 *
 * @package    theme_moove copied from boost_union
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
// @codingStandardsIgnoreStart
// Let codechecker ignore the next line because otherwise it would complain about a missing login check
// after requiring config.php which is really not needed.
require(__DIR__ . '/../../../config.php');
// @codingStandardsIgnoreEnd

// Require the necessary libraries.
require_once($CFG->dirroot.'/theme/moove/lib.php');

// Set page URL.
$PAGE->set_url('/theme/moove/pages/help.php');

// Set page layout.
$PAGE->set_pagelayout('standard');

// Set page context.
$PAGE->set_context(context_system::instance());

// Add page name as body class.
$PAGE->add_body_class('theme_moove-help');

// Get theme config.
$config = get_config('theme_moove');

// If the help page is disabled, we just show a short friendly warning page and are done.
if ($config->enablehelp != THEME_MOOVE_SETTING_SELECT_YES) {
    echo $OUTPUT->header();
    $notification = new \core\output\notification(get_string('helpdisabled', 'theme_moove'),
            \core\output\notification::NOTIFY_INFO);
    $notification->set_show_closebutton(false);
    echo $OUTPUT->render($notification);
    echo $OUTPUT->footer();
    die;
}

// Set page title.
$PAGE->set_title(theme_moove_get_staticpage_pagetitle('help'));

// Start page output.
echo $OUTPUT->header();

// Show page heading.
echo $OUTPUT->heading(theme_moove_get_staticpage_pagetitle('help'));

// Output help page content.
echo format_text($config->helpcontent);

// Finish page.
echo $OUTPUT->footer();
