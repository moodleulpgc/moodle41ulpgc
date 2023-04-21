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
 * examdelivery management setting.
 *
 * @package    mod_examregistrar
 * @copyright  2016 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_examregistrar;

use core_plugin_manager;
use core_text;
use moodle_url;
use html_table;
use html_writer;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/adminlib.php");

class setting_examdeliveries extends \admin_setting {
    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('examregistrar_delivery_manageui', get_string('manageexamdeliveryplugins', 'examregistrar'), '', '');
    }

    /**
     * Always returns true, does nothing.
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true, does nothing.
     *
     * @return true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Always returns '', does not write anything.
     *
     * @param mixed $data ignored
     * @return string Always returns ''
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Checks if $query is one of the available batchmanage plugins.
     *
     * @param string $query The string to search for
     * @return bool Returns true if found, false if not
     */
    public function is_related($query) {
        if (parent::is_related($query)) {
            return true;
        }

        $query = core_text::strtolower($query);
        $plugins = core_plugin_manager::instance()->get_installed_plugins('examdelivery');; //\core_component::get_plugin_list_with_class('examdelivery', 'batchmanage\examdelivery');
        foreach ($plugins as $plugin => $fulldir) {
            if (strpos(core_text::strtolower($plugin), $query) !== false) {
                return true;
            }
            $localised = get_string('pluginname', 'examdelivery_'.$plugin);
            if (strpos(core_text::strtolower($localised), $query) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Builds the XHTML to display the control.
     *
     * @param string $data Unused
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE;

        // Display strings.
        $strup = get_string('up');
        $strdown = get_string('down');
        $strsettings = get_string('settings');
        $strenable = get_string('enable');
        $strdisable = get_string('disable');
        $struninstall = get_string('uninstallplugin', 'core_admin');
        $strversion = get_string('version');

        $pluginmanager = core_plugin_manager::instance();
        $available = plugininfo\examdelivery::get_sorted_plugins();
        $sortorder = array_flip(array_keys($available));
        $enabled = plugininfo\examdelivery::get_enabled_plugins();

        $return = $OUTPUT->box_start('generalbox examdeliveriesgui');

        $table = new html_table();
        $table->head = array(get_string('name'), $strversion, $strenable,
                $strup . '/' . $strdown, $strsettings, $struninstall);
        $table->colclasses = array('leftalign', 'centeralign', 'centeralign', 'centeralign', 'centeralign',
                'centeralign');
        $table->id = 'examdeliveryplugins';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data = array();

        // Iterate through examdelivery plugins and add to the display table.
        $updowncount = 0;
        $deliverycount = count($available) - 1;
        $last = end($sortorder);
        $first = reset($sortorder);

        $url = new moodle_url('/mod/examregistrar/managesubplugins.php', array('sesskey' => sesskey()));
        $printed = array();
        foreach ($available as $delivery => $version) {
            $plugininfo = $pluginmanager->get_plugin_info('examdelivery_'.$delivery);
            //$version = get_config('examdelivery_'.$delivery, 'version');
            if ($version === false) {
                $version = '';
            }

            if (get_string_manager()->string_exists('pluginname', $delivery)) {
                $name = get_string('pluginname', $delivery);
            } else {
                $name = $delivery;
            }

            // Hide/show links.
            if (isset($enabled[$delivery])) {
                $aurl = new moodle_url($url, array('action' => 'disable', 'plugin' => $delivery));
                $hideshow = "<a href=\"$aurl\">";
                $hideshow .= $OUTPUT->pix_icon('t/hide', $strdisable, 'moodle', array('class'=>'iconsmall'));
                $isenabled = true;
                $displayname = "<span>$name</span>";
            } else {
                if (isset($available[$delivery])) {
                    $aurl = new moodle_url($url, array('action' => 'enable', 'plugin' => $delivery));
                    $hideshow = html_writer::link($aurl, $OUTPUT->pix_icon('t/show', $strenable, 'moodle', array('class'=>'iconsmall')));
                    $isenabled = false;
                    $displayname = "<span class=\"dimmed_text\">$name</span>";
                } else {
                    $hideshow = '';
                    $isenabled = false;
                    $displayname = '<span class="notifyproblem">' . $name . '</span>';
                }
            }
            if ($PAGE->theme->resolve_image_location('monologo', $delivery, false)) {
                $icon = $OUTPUT->pix_icon('icon', '', $delivery, array('class' => 'icon pluginicon'));
            } else {
                $icon = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'icon pluginicon noicon'));
            }

            // Up/down link (only if examdelivery is enabled).
            $updown = '';
            if ($sortorder[$delivery] > $first) {
                $aurl = new moodle_url($url, array('action' => 'up', 'plugin' => $delivery));
                $updown .= html_writer::link($aurl, $OUTPUT->pix_icon('t/up', $strup, 'moodle', array('class'=>'iconsmall')).' &nbsp; ');
            } else {
                $updown .= $OUTPUT->pix_icon('spacer', '', 'moodle', array('class'=>'iconsmall'));
            }
            if ($sortorder[$delivery] < $last) {
                $aurl = new moodle_url($url, array('action' => 'down', 'plugin' => $delivery));
                $updown .= html_writer::link($aurl, $OUTPUT->pix_icon('t/down', $strup, 'moodle', array('class'=>'iconsmall')).' &nbsp; ');
            } else {
                $updown .= $OUTPUT->pix_icon('spacer', '', 'moodle', array('class'=>'iconsmall'));
            }

            // Add settings link.
            if (!$version) {
                $settings = '';
            } else {
                if ($surl = $plugininfo->get_settings_url()) {
                    $settings = html_writer::link($surl, $strsettings);
                } else {
                    $settings = '';
                }
            }

            // Add uninstall info.
            $uninstall = '';
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('examdelivery_'.$delivery, 'manage')) {
                $uninstall = html_writer::link($uninstallurl, $struninstall);
            }

            // Add a row to the table.
            $table->data[] = array($icon . $displayname, $version, $hideshow, $updown, $settings, $uninstall);

            $printed[$delivery] = true;
        }

        $return .= html_writer::table($table);
        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}
