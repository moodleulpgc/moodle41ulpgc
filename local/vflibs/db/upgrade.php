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
 * @package   local_vflibs
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Standard upgrade handler.
 *
 * Each time we upgrade the libs we will collect information about installed plugins of the mylearnignfactory catalog.
 *
 * @param int $oldversion
 */
function xmldb_local_vflibs_upgrade($oldversion = 0) {
    global $CFG, $THEME, $DB;

    $result = true;

    $dbman = $DB->get_manager();

    collect();

    return $result;
}

/**
 * Collects all plugins that have the 
 *
 *
 */
function collect($verbose = false) {
    $plugins = get_plugins_with_function('supports_feature');

    $counter = 0;
    $editorplugins = array();
    if (!empty($plugins)) {
        foreach ($plugins as $plugintype => $typearr) {
            foreach ($typearr as $plugin => $pluginfunction) {
                $editorplugins[] = $plugintype.'_'.$plugin;
                $counter++;
            }
        }
    }

    if ($verbose) {
        echo "Collected... $counter plugins.\n";
    }

    set_config('editorplugins', implode(',', $editorplugins), 'local_vflibs');
}