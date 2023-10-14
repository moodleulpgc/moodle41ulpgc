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
namespace atto_fontawesomepicker;

/**
 * Class atto_fontawesomepicker/plugin.
 *
 * This class hold spectific constants and functions for the atto_fontawesomepicker plugin
 *
 * @package    atto_fontawesomepicker
 * @copyright  2023 DNE - Ministere de l'Education Nationale 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class plugin {

    const PLUGIN_NAME = 'atto_fontawesomepicker';

    const EMBEDDING_MOD_FACODE =  0; // only with fontawesome code
    const EMBEDDING_MOD_FILTER = 1; // with fontawesomefilter

    
    public static function get_embedding_mode_options() {
        return array(
            self::EMBEDDING_MOD_FACODE => get_string('embedding_mode_facode', self::PLUGIN_NAME),
            self::EMBEDDING_MOD_FILTER => get_string('embedding_mode_filter', self::PLUGIN_NAME), 
        );
    }

    public static function get_default_path_fontawesome() {
        global $CFG;
        return 'fa:' . $CFG->dirroot . '/lib/fonts/fontawesome-webfont.svg';
    }
}
