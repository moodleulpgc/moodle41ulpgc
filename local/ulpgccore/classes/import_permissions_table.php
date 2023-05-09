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
 * Library code used by the roles administration interfaces.
 *
 * @package    local_ulpgccore
 * @copyright  2023 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ulpgccore;

defined('MOODLE_INTERNAL') || die();

/**
 * Subclass of core_role_capability_table_base for use on the check Permissions page.
 */
class import_permissions_table extends \core_role_define_role_table_advanced {

    public function save_changes() {
        global $DB, $USER;    
    
        // Record the fact there is data to save, mark all as changed.
        foreach($this->permissions as $capname => $permission) {
            $this->changed[] = $capname;
        }
    
         // Trigger saving by parente classes
        parent::save_changes();
    
        // now we can issue event
        if($this->roleid) {
            // Trigger role updated event.
            event\role_imported::create([
                'userid' => $USER->id,
                'objectid' => $this->roleid,
                'context' => $this->context,
                'other' => [
                    'name' => $this->role->name,
                    'shortname' => $this->role->shortname,
                    'description' => $this->role->description,
                    'archetype' => $this->role->archetype,
                    'contextlevels' => $this->contextlevels,
                    'preset' => $this->preset,
                ]
            ])->trigger();            
        }
    }
}
