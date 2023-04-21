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
 * Allows the admin to manage videolibment plugins
 *
 * @package    mod_videolib
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$plugin = required_param('plugin', PARAM_PLUGIN);
$action = optional_param('action', null, PARAM_PLUGIN);

$syscontext = context_system::instance();
$PAGE->set_url('/mod/examregistrar/managesubplugins.php');
$PAGE->set_context($syscontext);

require_admin();
require_sesskey();

$return = new moodle_url('/admin/settings.php', array('section' => 'manageexamdeliveryplugins'));

$plugins = core_plugin_manager::instance()->get_plugins_of_type('examdelivery');

$available = mod_examregistrar\plugininfo\examdelivery::get_sorted_plugins();
$seq = array_keys($available);
$sortorder = array_flip($seq);

if (!isset($plugins[$plugin])) {
    throw new \moodle_exception('subpluginnotfound', 'examregistrar', $return, $plugin);
}

$reorder =  false;
switch ($action) {
    case 'disable':
        if ($plugins[$plugin]->is_enabled()) {
            $class = \core_plugin_manager::resolve_plugininfo_class('examdelivery');
            $class::enable_plugin($plugin, 0);
        }
        break;
    case 'enable':
        if (!$plugins[$plugin]->is_enabled()) {
            $class = \core_plugin_manager::resolve_plugininfo_class('examdelivery');
            $class::enable_plugin($plugin, 1);
        }

        break;
    case 'up':
        if ($sortorder[$plugin]) {
            $currentindex = $sortorder[$plugin];
            $seq[$currentindex] = $seq[$currentindex-1];
            $seq[$currentindex-1] = $plugin;
            $reorder = true;
        }
        break;
    case 'down':
        if ($sortorder[$plugin] < count($sortorder)-1) {
            $currentindex = $sortorder[$plugin];
            $seq[$currentindex] = $seq[$currentindex+1];
            $seq[$currentindex+1] = $plugin;
            $reorder = true;
        }
        break;
}

if($reorder) {
    foreach($seq as $key => $plugin) {
        set_config('sortorder', $key, 'examdelivery_'.$plugin);
    }
}

redirect($return);
