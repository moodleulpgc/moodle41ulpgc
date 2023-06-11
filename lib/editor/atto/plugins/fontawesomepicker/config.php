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
 * Config file plugin.
 *
 * Config string to associate all the css classes used by FontAwesome with their svg file path
 * The string form is : {css_class_1}:{path_1}||{css_class_2}:{path_2}||{css_class_3}:{path_3}
 * The key index represents the css class used by FontAwesome
 * Be careful: FontAwesome v4 uses fa
 *             FontAwesome v5 uses fab, fas, far, ...
 *
 * @package    atto_fontawesomepicker
 * @copyright  2020 DNE - Ministere de l'Education Nationale 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$pathfontsawesome = get_config(atto_fontawesomepicker\plugin::PLUGIN_NAME, 'pathfontsawesome');


if (!$pathfontsawesome) {
    $pathfontsawesome = atto_fontawesomepicker\plugin::get_default_path_fontawesome();
}

