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
 * Checking XXX
 *
 * text
 * text explain
 *
 * @package    tool_ulpgccore
 * @category   check
 * @copyright  2023 Enrique castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_ulpgcqc\check\config;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;

/**
 * Checking XXX
 *
 * text
 * text explain
 *
 * @package     tool_ulpgcqc
 * @copyright   2023 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debugging extends check {
    /**
    * Get the short check name
    *
    * @return string
    */
    public function get_name(): string {
        return get_string('check_optionalsubsystems', 'tool_ulpgcqc');
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     */
    public function get_action_link(): ?\action_link {
        $url = new \moodle_url('/admin/settings.php', ['section'=>'debugging']);
        return new \action_link($url, get_string('debugging', 'admin'));
    }
    
    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        global $CFG;
        
        $target = get_config('tool_ulpgcqc');
        
        $status = result::OK;
        $errors = [];
        foreach($checks as $check) {
            if($CFG->{$check}  !=  $target->{$check}) {
                $errors[] = $check;
            }
        }
        
        $details = '';
        if(empty($errors)) {
            $status = result::OK;
            $summary= $this->get_name();
        } else {
            
        }
        
        /*
         posible esquema 
         if(array_keys  contiene X )  
             error gordo, crítico 
        } else {
            si no: solo warning
        */

        return new result($status, $summary, $details);
    }
}
