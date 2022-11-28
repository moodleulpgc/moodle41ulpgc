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
 * @package local_sinculpgc
 * @author Enrique Castro  @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2022 onwards ULPGC
 */

namespace local_sinculpgc\task;

use local_sinculpgc\sinculpgcrule;
use local_sinculpgc\helper;

/**
 * Scheduled task to sync users with Azure AD.
 */
class rulesenrolsync extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_rulesenrolsync', 'local_sinculpgc');
    }

    /**
     * Do the job.
     */
    public function execute() {
        global $CFG, $DB;
        
        $config = get_config('local_sinculpgc');
        
        if (!empty($config->enablesynchrules)) {
        
            mtrace('Starting local_sinculpgc rules syncing');
            // Add instances to new rules or courses
            // Find courses by rule criteria NOT having enrol.customint8 set or rule.timemodified > enrol.timemodified
            //force : all updated, even those enrol timemodified after rule = sobreescribir cambios manuales Config???            
            helper::rule_add_enrol_instances(0, $config->forcereset);
        
            // process deletion of enrol instances
            //Borrados & disabled not keep
            // SELECT enrol customint8 > 0 LEF JOIN rule On customint8 = ruleid NULL 
            // SELECT enrol customint8 > 0  and rule customint8 = ruleid disabled & notkeep
            //local_sinculpgc_remove_enrol_instances($ruleid = null disabledtoo = null); if roleid: solo afecta esa regla, esos cursos if force, remove when rule disabled
            helper::rule_remove_enrol_instances(0, $config->removeondisabling);
            
            mtrace('Finished local_sinculpgc rules syncing');
        }
        return true;
    }
    
    /**
     * Override this function if you want this scheduled task to run, even if the component is disabled.
     *
     * @return bool
     */
     public function get_run_if_component_disabled() {
        return false;
     }
   
}
