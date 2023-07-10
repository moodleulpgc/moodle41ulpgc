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
 * Atto text editor integration version file.
 *
 * @package    atto_fontawesomepicker
 * @copyright  2020 DNE - Ministere de l'Education Nationale 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use atto_fontawesomepicker\editor_atto_fontawesomepicker;

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_fontawesomepicker', new lang_string('pluginname', 'atto_fontawesomepicker')));

$settings = new admin_settingpage('atto_fontawesomepicker_settings', new lang_string('pluginname', 'atto_fontawesomepicker'));
$PAGE->requires->js_call_amd("atto_fontawesomepicker/adminjs", "init");

if ($ADMIN->fulltree) {
    $pathfontsawesome = '';
    if (file_exists(__DIR__.'/config.php')) {
        require(__DIR__.'/config.php');
    }

    $settings->add(new editor_atto_fontawesomepicker(
        'atto_fontawesomepicker/availableicons',
        get_string('availableicons', 'atto_fontawesomepicker'), 
        '', 
        '', 
        PARAM_RAW, 
        100, 
        8,
        $pathfontsawesome
    ));

    $settings->add(new admin_setting_configselect(
        'atto_fontawesomepicker/faembeddingmode', 
        get_string('faembeddingmode', atto_fontawesomepicker\plugin::PLUGIN_NAME), 
        get_string('faembeddingmode_desc', atto_fontawesomepicker\plugin::PLUGIN_NAME), 
        atto_fontawesomepicker\plugin::EMBEDDING_MOD_FACODE, 
        atto_fontawesomepicker\plugin::get_embedding_mode_options()
    ));
}
