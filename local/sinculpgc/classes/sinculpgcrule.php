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

namespace local_sinculpgc;

use core\persistent;

/**
 * sinculpgc Enrol rule class.
 *
 * @package    local_sinculpgc
 * @author     Enrique castro @ ULPGC
 * @copyright  2022 onwards ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sinculpgcrule extends persistent {

    /** Table name for the persistent. */
    const TABLE = 'local_sinculpgc_rules';

    /**
     * @inheritdoc
     */
    protected static function define_properties() {
        return [
            'enrol' => [
                'type' => PARAM_ALPHANUMEXT,
                'null' => NULL_NOT_ALLOWED,
                'default' => '',
            ],
            'roleid' => array(
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
                'default' => 0
            ),
            'searchfield' => [
                'type' => PARAM_ALPHANUMEXT,
                'null' => NULL_NOT_ALLOWED,
                'default' => '',
            ],
            'searchpattern' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
                'default' => '',
            ],
            'enrolparams' => [
                'type' => PARAM_RAW,
                'null' => NULL_NOT_ALLOWED,
            ],
            'groupto' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
                'default' => '',                
            ],
            'useidnumber' => [
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,                
            ],
            'enabled' => [
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
                'default' => 1,
            ],
        ];
    }

    /**
     * Run after update.
     *
     * @param bool $result Result of update.
     */
    protected function after_update($result) {
        if ($result) {
            //self::purge_caches();
            // TODO actions after updating rule
        }
    }

    /**
     * Run after created.
     */
    protected function after_create() {
        //self::purge_caches();
        //TODO actions after creating rule e.g. run sync task , if enebled in config
    }

    /**
     * Run after deleted.
     *
     * @param bool $result Result of delete.
     */
    protected function after_delete($result) {
        //self::purge_caches();
        //TODO actions after deleting rule e.g. run sync task , if enebled in config
      
      
    }

    /**
     * Enable a rule.
     * @param $ruleid rule id
     * @return sinculpgcrule
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public static function enable($ruleid) {
        $persistent = new sinculpgcrule($ruleid);
        $persistent->set('enabled', 1);
        $persistent->update();
        return $persistent;
    }

    /**
     * Disable a rule.
     * @param $ruleid rule id
     * @return sinculpgcrule
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public static function disable($ruleid) {
        $persistent = new sinculpgcrule($ruleid);
        $persistent->set('enabled', 0);
        $persistent->update();
        return $persistent;
    }

    /**
     * Get enabled rules.
     *
     * @return \stdClass[]
     */
    public static function get_enabled_rules(): array {
        $persistents = self::get_records(['enabled' => 1], 'id');
            $result = [];
            foreach ($persistents as $persistent) {
                $record = $persistent->to_record();
                $result[$record->id] = $record;
            }

        return $result;
    }

    /**
     * Get all rules
     *
     * @return \stdClass[]
     */
    public static function get_all_rule_records(): array {
        $persistents = self::get_records([], 'timemodified', 'DESC');
        $result = [];
        foreach ($persistents as $persistent) {
            $record = $persistent->to_record();
            $result[$record->id] = $record;
        }
        return $result;
    }

    /**
     * Create new rule
     * @param \stdClass $data
     * @return persistent
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public static function create_new_rule($data) {
        if(isset($data->enrolparams) && is_object($data->enrolparams)) {
            //$data->enrolparams = json_encode($data->enrolparams);
        }    
    
        $persistent = new self(0, $data);
        return $persistent->create();
    }

    /**
     * Update data of the rule
     * @param sinculpgcrule $persistent site rule persistent object
     * @param \stdClass $data new data
     * @return bool
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public static function update_rule_data(sinculpgcrule $persistent, $data) {
        if(isset($data->enrolparams) && is_object($data->enrolparams)) {
            //$data->enrolparams = json_encode($data->enrolparams);
        }
        $persistent->from_record($data);
        
        return $persistent->update();
    }
    
}
