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
 * @package    atto_fontawesomepicker
 * @copyright  2020 DNE - Ministere de l'Education Nationale 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Create list of available Font Awesome icons.
 *
 * @return array
 * @throws dml_exception
 */
function atto_fontawesomepicker_params_for_js() {
    $availableions = get_config(atto_fontawesomepicker\plugin::PLUGIN_NAME, 'availableicons');
    $icons = explode(";", $availableions);

    $embeddingmode = get_config(atto_fontawesomepicker\plugin::PLUGIN_NAME, 'faembeddingmode');

    return array(
        'icons' => $icons,
        'embeddingmode' => $embeddingmode
    );
}

