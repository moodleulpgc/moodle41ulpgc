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
 * Subplugin info class.
 *
 * @package   mod_examregistrar
 * @copyright 2021 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_examregistrar\plugininfo;

use core\plugininfo\base;
use moodle_url, part_of_admin_tree, admin_settingpage, core_plugin_manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Subplugin info class.
 *
 * @package   mod_examregistrar
 * @copyright 2021 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examdelivery extends base {

    /**
     * Finds all enabled plugins, the result may include missing plugins.
     * @return array|null of enabled plugins $pluginname=>$version, null means unknown
     */
    public static function get_enabled_plugins() {
        $plugins = core_plugin_manager::instance()->get_installed_plugins('examdelivery');
        if (!$plugins) {
            return [];
        }

        foreach($plugins as $plugin => $version) {
            $enabled = get_config('examdelivery_'.$plugin, 'enabled');
            if(empty($enabled)) {
                unset($plugins[$plugin]);
            }
        }

        return $plugins;
    }



    /**
     * Finds all enabled plugins, the result may include missing plugins.
     * @return array|null of enabled plugins $pluginname=>$pluginname, null means unknown
     */
    public static function get_sorted_plugins() {
        $plugins = core_plugin_manager::instance()->get_installed_plugins('examdelivery');
        if (!$plugins) {
            return [];
        }

        $sorted = [];
        $sortedplugins = [];
        foreach($plugins as $plugin => $version) {
            $key = get_config('examdelivery_'.$plugin, 'sortorder');
            if(isset($key) && ($key != '') && !isset($sorted[$key])) {
                $sorted[$key] = $plugin;
            } else {
                $sorted[] = $plugin;
            }
        }
        ksort($sorted);


        foreach($sorted as $key => $plugin) {
            set_config('sortorder', $key, 'examdelivery_'.$plugin);
            $sortedplugins[$plugin] = $plugins[$plugin];
        }

        return $sortedplugins;
    }


    public static function enable_plugin(string $pluginname, int $enabled): bool {
        $haschanged = false;


        $plugin = 'examdelivery_' . $pluginname;

        print_object("name   $pluginname   param: $plugin  ");
        $oldvalue = get_config($plugin, 'enabled');
        $disabled = !$enabled;

        print_object("old: $oldvalue    en: $enabled    dis: $disabled  "  );
        // Only set value if there is no config setting or if the value is different from the previous one.
        if ($oldvalue == false && $enabled) {
            /*
            if (get_config('moodlecourse', 'format') === $pluginname) {
                // The default course format can't be disabled.
                throw new \moodle_exception('cannotdisableformat', 'error');
            }
            */
            set_config('enabled', $enabled, $plugin);

            print_object(" 'enabled', $enabled, $plugin ");
            $haschanged = true;
        } else if ($oldvalue != false && $disabled) {
            unset_config('enabled', $plugin);
            $haschanged = true;
            print_object(" 'DISABLED', $disabled, $plugin ");
        }

        if ($haschanged) {
            add_to_config_log('enabled', $oldvalue, $disabled, $plugin);
            \core_plugin_manager::reset_caches();
        }

        return $haschanged;
    }


    /**
     * Do not allow users to uninstall if used in future exams.
     *
     * @return bool
     */
    public function is_uninstall_allowed() {
        if(0) {
            return false; /// TODO XXXX XXXX
        }
        return true;
    }

    /**
     * Loads plugin settings to the settings tree.
     *
     * @param \part_of_admin_tree $adminroot
     * @param string $parentnodename
     * @param bool $hassiteconfig whether the current user has moodle/site:config capability
     */
    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.
        $ADMIN = $adminroot; // May be used in settings.php.
        $plugininfo = $this; // Also can be used inside settings.php.

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();
        $settings = new admin_settingpage($section, $this->displayname, 'moodle/site:config', false);

        include($this->full_path('settings.php'));
        $ADMIN->add($parentnodename, $settings);
    }

    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php', array('section' => 'manageexamdeliveryplugins'));
    }

    /**
     * Get the settings section name.
     *
     * @return null|string the settings section name.
     */
    public function get_settings_section_name() {
        if (file_exists($this->full_path('settings.php'))) {
            return 'examdelivery_' . $this->name;
        } else {
            return null;
        }
    }
    
    // TODO
    /* Management interface may be borrowed from assign subplugins */
}
