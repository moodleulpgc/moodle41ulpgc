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
 * Plugin settings
 *
 * @package    atto_pumukitmedia
 * @copyright  Teltek Video Research
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || exit();

$ADMIN->add('editoratto', new admin_category('atto_pumukitmedia', new lang_string('pluginname', 'atto_pumukitmedia')));

$settings = new admin_settingpage('atto_pumukitmedia_settings', new lang_string('settings', 'atto_pumukitmedia'));
if ($ADMIN->fulltree) {
    // An option setting
    $settings->add(
        new admin_setting_configtext(
            'atto_pumukitmedia/pumukitmediaurl',
            get_string('pumukitmediaurl', 'atto_pumukitmedia'),
            get_string('pumukitmediaurldesc', 'atto_pumukitmedia'),
            'https://naked-pr-up2u.teltek.es',
            PARAM_URL
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'atto_pumukitmedia/dialogtitle',
            get_string('dialogtitle', 'atto_pumukitmedia'),
            get_string('dialogtitledesc', 'atto_pumukitmedia'),
            get_string('dialogtitledefval', 'atto_pumukitmedia'),
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'atto_pumukitmedia/password',
            get_string('password', 'atto_pumukitmedia'),
            get_string('passworddesc', 'atto_pumukitmedia'),
            get_string('passworddefval', 'atto_pumukitmedia'),
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'atto_pumukitmedia/showpr',
            get_string('showprtext', 'atto_pumukitmedia'),
            get_string('showprdesc', 'atto_pumukitmedia'),
            1
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'atto_pumukitmedia/showplaylist',
            get_string('showplaylisttext', 'atto_pumukitmedia'),
            get_string('showplaylistdesc', 'atto_pumukitmedia'),
            0
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'atto_pumukitmedia/showsharedvideos',
            get_string('showsharedvideostext', 'atto_pumukitmedia'),
            get_string('showsharedvideosdesc', 'atto_pumukitmedia'),
            1
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'atto_pumukitmedia/enabledebugmode',
            get_string('enabledebugmode', 'atto_pumukitmedia'),
            get_string('enabledebugmode', 'atto_pumukitmedia'),
            0
        )
    );
}
