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
 * A table of check results
 *
 * @package    tool_ulpgccore
 * @category   check
 * @copyright  2023 Enrique castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_ulpgcqc\check;

defined('MOODLE_INTERNAL') || die();

/**
 * A table of check results
 *
 * @copyright  2023 Enrique castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /**
     * The list of valid check types
     */
    public const TYPES = ['general', 'config', 'courses', 'users'];

    /**
     * Return all status checks
     *
     * @param string $type of checks to fetch
     * @return array of check objects
     */
    public static function get_checks(string $type): array {
        global $CFG;

        if (!in_array($type, self::TYPES)) {
            throw new \moodle_exception("Invalid check type '$type'");
        }

        $checks = [];
        $checkfiles = get_directory_list($CFG->dirroot.'/admin/tool/ulpgcqc/classes/check/'.$type, '', false); 
        foreach($checkfiles as $file) {
            $pathinfo = pathinfo($file);
            if(($pathinfo['extension'] == 'php') && (strpos($pathinfo['filename'], '_result') === false)) {
                $checkclass = $type.'\\'.$pathinfo['filename'];
                print_object("type: $type   |     ");
                print_object($pathinfo);
                print_object("class: $checkclass"); 
                
                //$checks[] = new $checkclass();
            }
        }

        // Any plugin can add status checks to this report by implementing a callback
        // <component>_status_checks() which returns a check object.
        $morechecks = get_plugins_with_function($type.'_checks', 'lib.php');
        foreach ($morechecks as $plugintype => $plugins) {
            foreach ($plugins as $plugin => $pluginfunction) {
                $result = $pluginfunction();
                foreach ($result as $check) {
                    $check->set_component($plugintype . '_' . $plugin);
                    $checks[] = $check;
                }
            }
        }

        return $checks;
    }

    /**
     * Return all status checks
     *
     * @return array of check objects
     */
    /*
    public static function get_status_checks(): array {
        $checks = [
            new environment\environment(),
            new environment\upgradecheck(),
            new environment\antivirus(),
        ];

        // Any plugin can add status checks to this report by implementing a callback
        // <component>_status_checks() which returns a check object.
        $morechecks = get_plugins_with_function('status_checks', 'lib.php');
        foreach ($morechecks as $plugintype => $plugins) {
            foreach ($plugins as $plugin => $pluginfunction) {
                $result = $pluginfunction();
                foreach ($result as $check) {
                    $check->set_component($plugintype . '_' . $plugin);
                    $checks[] = $check;
                }
            }
        }
        return $checks;
    }
    */
}

