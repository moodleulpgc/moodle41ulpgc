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
 * Helper class to create, retrieve, manage rules
 * @package local_sinculpgc
 * @author  Enrique Castro @ ULPGC
 * @copyright  2022 onwards ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_sinculpgc;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/lib/completionlib.php');
use local_sinculpgc\sinculpgcrule;
use context_user;
use moodle_url;

class helper {

    /**
     * Create new rule
     * @param \stdClass $data form data
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \core\invalid_persistent_exception
     * @throws \required_capability_exception
     */
    public static function create_new_rule($data) {
        self::check_manage_capability();
        
        // Create new rule.
        self::sanitise_data($data);

        $rule = sinculpgcrule::create_new_rule($data);
        
        // Log created event.
        $params = array(
            'objectid' => $rule->get('id'),
        );
        $event = \local_sinculpgc\event\rule_created::create($params);
        $event->trigger();
    }

    /**
     * Update existing rule.
     * @param sinculpgc $rule site rule persistent
     * @param \stdClass $data form data
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     * @throws \dml_exception
     * @throws \required_capability_exception
     */
    public static function update_rule(sinculpgcrule $rule, $data) {
        self::check_manage_capability();

        self::sanitise_data($data);
        sinculpgcrule::update_rule_data($rule, $data);
        
        // Log updated event.
        $params = array(
            'objectid' => $rule->get('id'),
            'other' => ['action' => 'updated'],
        );
        $event = \local_sinculpgc\event\rule_updated::create($params);
        $event->trigger();
    }

    /**
     * Sanitise submitted data before creating or updating a site notice.
     *
     * @param \stdClass $data
     */
    private static function sanitise_data(\stdClass $data) {
        $instance = new \stdClass();
        
        if(!isset($data->roleid) && 
                                (($data->enrol == 'metacat') || ($data->enrol == 'metapattern' )) ) {
            $data->roleid = $data->customint2;
        }
        
        foreach ((array)$data as $key => $value) {
            if (!key_exists($key, sinculpgcrule::properties_definition())) {
                if($key != 'submitbutton') {
                    $instance->{$key} = $value;
                }
                unset($data->$key);
            }
        }

        
        $data->enrolparams = '';
        if(!empty($instance)) {
            $data->enrolparams = json_encode($instance);
        }
    }    
    
    /**
     * Reset a rule
     * @param $ruleid rule id
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \core\invalid_persistent_exception
     * @throws \required_capability_exception
     */
    public static function reset_rule($ruleid) {
        self::check_manage_capability();
        try {
            // Log reset event.
            $params = array(
                'objectid' => $ruleid,
                'other' => ['action' => 'reset'],
            );
            $event = \local_sinculpgc\event\rule_updated::create($params);
            $event->trigger();
            self::rule_add_enrol_instances($ruleid, true);
            
        } catch (Exception $e) {
            \core\notification::error($e->getMessage());
        }
    }

    /**
     * Reset a rule
     * @param $ruleid rule id
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \core\invalid_persistent_exception
     * @throws \required_capability_exception
     */
    public static function run_rule($ruleid) {
        self::check_manage_capability();
        try {
            // Log run event.
            $params = array(
                'objectid' => $ruleid,
                'other' => ['action' => 'run'],
            );
            $event = \local_sinculpgc\event\rule_updated::create($params);
            $event->trigger();
            
            if($num = self::rule_add_enrol_instances($ruleid, false)) {
                \core\notification::success(get_string('instancesadded', 'local_sinculpgc', $num));
            }
            
        } catch (Exception $e) {
            \core\notification::error($e->getMessage());
        }
    }
    

    /**
     * Enable a rule
     * @param $ruleid rule id
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \core\invalid_persistent_exception
     * @throws \required_capability_exception
     */
    public static function enable_rule($ruleid) {
        self::check_manage_capability();
        try {
            $rule = sinculpgcrule::enable($ruleid);
            // Log enabled event.
            $params = array(
                'objectid' => $rule->get('id'),
                'other' => ['action' => 'enabled'],
            );
            $event = \local_sinculpgc\event\rule_updated::create($params);
            $event->trigger();
        } catch (Exception $e) {
            \core\notification::error($e->getMessage());
        }
    }

    /**
     * Disable a rule
     * @param $ruleid rule id
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \core\invalid_persistent_exception
     * @throws \required_capability_exception
     */
    public static function disable_rule($ruleid) {
        self::check_manage_capability();
        try {
            $rule = sinculpgcrule::disable($ruleid);
            // Log disable event.
            $params = array(
                'objectid' => $rule->get('id'),
                'other' => ['action' => 'disabled'],
            );
            $event = \local_sinculpgc\event\rule_updated::create($params);
            $event->trigger();
        } catch (Exception $e) {
            \core\notification::error($e->getMessage());
        }
    }

    /**
     * Delete a rule
     * @param $ruleid rule id
     * @throws \dml_exception
     * @throws \coding_exception
     * @throws \required_capability_exception
     */
    public static function delete_rule($ruleid) {
        self::check_manage_capability();
        $rule = sinculpgcrule::get_record(['id' => $ruleid]);
        if ($rule) {
            $rule->delete();
            // Log delete event.
            $params = array(
                'objectid' => $rule->get('id'),
                'other' => ['action' => 'deleted'],
            );
            $event = \local_sinculpgc\event\rule_deleted::create($params);
            $event->trigger();
            \core\notification::success(get_string('ruledeleted', 'local_sinculpgc', $ruleid));

            if (!get_config('local_sinculpgc', 'lazydelete')) {
                if($num = self::rule_remove_enrol_instances($ruleid, true)) {
                    \core\notification::success(get_string('instancesremoved', 'local_sinculpgc', $num));                    
                }
            }
        }
    }

    /**
     * Delete a rule
     * @param $ruleid rule id
     * @throws \dml_exception
     * @throws \coding_exception
     * @throws \required_capability_exception
     */
    public static function remove_rule($ruleid) {
        self::check_manage_capability();
        if ($ruleid) {
            // Log remove event.
            $params = array(
                'objectid' => $ruleid,
                'other' => ['action' => 'removed'],
            );
            $event = \local_sinculpgc\event\rule_updated::create($params);
            $event->trigger();

            if($num = self::rule_remove_enrol_instances($ruleid, true)) {
                \core\notification::success(get_string('instancesremoved', 'local_sinculpgc', $num));
            }
        }
    }
    
    /**
     * Get a rule
     * @param $ruleid rule id
     * @return bool|\stdClass
     */
    public static function retrieve_rule($ruleid) {
        $rule = sinculpgcrule::get_record(['id' => $ruleid]);
        if ($rule) {
            return $rule; 
        } else {
            return false;
        }
    }

    /**
     * Retrieve all rules
     * @return array
     * @throws \dml_exception
     */
    public static function retrieve_rulestable() {
        $rules = sinculpgcrule::get_all_rule_records();
        foreach($rules as $ruleid => $record) {
            $rules[$ruleid] = sinculpgcrule::get_record(['id' => $ruleid]); 
        }
        return $rules;
    }

    /**
     * Retrieve active rules
     * @return array
     * @throws \dml_exception
     */
    public static function retrieve_enabled_rules() {
        $rules = sinculpgcrule::get_enabled_rules();
        foreach($rules as $ruleid => $record) {
            $rules[$ruleid] = sinculpgcrule::get_record(['id' => $ruleid]); 
        }
        return $rules;
    }

    /**
     * Format date interval.
     * @param $time
     * @return string
     * @throws \coding_exception
     */
    public static function format_interval_time($time) {
        // Datetime for 01/01/1970.
        $datefrom = new \DateTime("@0");
        // Datetime for 01/01/1970 after the specified time (in seconds).
        $dateto = new \DateTime("@$time");
        // Format the date interval.
        return $datefrom->diff($dateto)->format(get_string('timeformat:resetinterval', 'local_sinculpgc'));
    }

    /**
     * Format boolean value
     * @param $value boolean
     * @return string
     * @throws \coding_exception
     */
    public static function format_boolean($value) {
        if ($value) {
            return get_string('booleanformat:true', 'local_sinculpgc');
        } else {
            return get_string('booleanformat:false', 'local_sinculpgc');
        }
    }


    /**
     * Check capability.
     * @throws \required_capability_exception
     * @throws \dml_exception
     */
    public static function check_manage_capability() {
        $syscontext = \context_system::instance();
        require_capability('local/sinculpgc:manage', $syscontext);
    }

    /**
     * Gets appropiate grouo field name for eah enrol instance type
     *
     * @param string $enrol name of enrol plugin/method
     * @return string name of the field
     */
    public static function get_group_field(string $enrol) {    
        $groupfield = 'customint2';
        if( ($enrol == 'metacat') || ($enrol == 'metapattern') ) {
            $groupfield = 'customint3';
        } elseif( ($enrol == 'self') || ($enrol == 'waitlist') || ($enrol == 'apply')   ) {
            $groupfield = '';
        }    
        return $groupfield;
    }
    

    /**
     * Cleans up group element options in enrol instace form: remove references to groupids of specific courses
     *
     * @param string $enrol name of enrol plugin/method
     * @param object $mform form to mangle
     * @return string name of the field
     */
    public static function clean_group_element(string $enrol, object $mform) {    
    
        $groupelement = self::get_group_field($enrol);
        
        if(!empty($groupelement) && $mform->elementExists($groupelement) && 
                            ($mform->getElementType($groupelement) == 'select') ) {
            $select = $mform->getElement($groupelement);
            $options = $select->_options; 
            foreach($options as $key => $option) {
                if ($option['attr']['value'] > 0){
                    unset($options[$key]);
                }
            }
            $select->_options = array_merge($options);
            $select->addOption(get_string('existinggroup', 'local_sinculpgc'), SINCULPGC_GROUP_EXISTING);
        }
    }
    
    
    
    /**
     * Construct enrol instance object from rule enrolparams  & eventually course data 
     *
     * @param sinculpgcrule $persistent site rule persistent object
     * @param int $courseid the course ID to look for roles or groups 
     * @param bool $create if forced creation of new group if groupto not existing
     * @return stdClass $instance enrol table like record
     */
    public static function extract_enrol_instance(sinculpgcrule $rule, $courseid = false, $create = false) {
        $data = $rule->get('enrolparams');
        $instance = new \stdClass();
        if(!empty($data) && is_string($data) && $data[0] == '{' ) {
            $instance = json_decode($data);
        }
        
        if($courseid) {
            $enrol = $rule->get('enrol');
            $roleid = $rule->get('roleid');
            $groupto = trim($rule->get('groupto'));
            $useidnumber = $rule->get('useidnumber');
            
            $groupfield = self::get_group_field($enrol);

            if($groupfield) {
                $groupid = false;
                if( ($instance->{$groupfield} == SINCULPGC_GROUP_EXISTING) && $groupto) {
                    if(!$useidnumber) {
                        $groupid = groups_get_group_by_name($courseid, $groupto); 
                    } else {
                        if($group =  groups_get_group_by_idnumber($courseid, $groupto)) {
                            $groupid = $group->id;
                        }
                    }
                    if(!$groupid && $create) {
                        // we need to create a new group 
                        $group = new \stdClass();
                        $group->courseid = $courseid;
                        $group->name = $groupto;
                        if($useidnumber) {
                            $group->idnumber = $groupto;
                        }
                        
                        $groupid = groups_create_group($group);
                    }
                }
                
                if($groupid) {
                    $instance->{$groupfield} = $groupid;
                } else {
                    $instance->{$groupfield} = 0;
                }
            }
            $instance->roleid = $roleid;
            $instance->enrol = $enrol;
            $instance->customint8 = $rule->get('id');
            $instance->timemodified = $rule->get('timemodified');
        }
    
        return $instance;
    }    
    
    /**
    * Sets the status property in enrol instances
    *
    * @param int $ruleid The sinculpgc rule to use
    * @param int $status ENROL_INSTANCE_DISABLED or ENROL_INSTANCE_ENABLED constants
    * @return null
    */
    public static function update_enrol_status($ruleid, $status = 0) {
        global $DB;    

        $sql = "SELECT e.*
                FROM {enrol} e 
                JOIN {local_sinculpgc_rules} r ON r.id = e.customint8 AND e.enrol = r.enrol
                WHERE e.customint8 > 0  AND  e.customint8 = :ruleid AND e.status != :status "; 
        $params = ['ruleid' => $ruleid, 'status' => $status];
        $rs = $DB->get_recordset_sql($sql, $params);
        
        $plugins = enrol_get_plugins(false);
        $num = 0;
        foreach($rs as $instance) {
            if($plugin = $plugins[$instance->enrol]) {
                $plugin->update_status($instance, $status);
                $num++;
                //TODO mtrace messages
            }
        }
        $rs->close();   
        if($num) {
            \core\notification::success(get_string('statusupdated', 'local_sinculpgc', $num));
        }
    }
    
    
    
    /**
    * Imports rules from CSV file uploaded
    *
    * @param stdClass $data formdata form import form
    * @return array [$sql, $params]
    */
    public static function import_rules(\stdClass $data) {
        global $DB, $USER;

        $managerulepage = new \moodle_url('/local/sinculpgc/managerules.php');
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->importfile, 'id DESC', false)) {
            \core\notification::error(get_string('noimportfile', 'local_sinculpgc'));
            return;
        }
        $file = reset($files);
        $csvdata = $file->get_content();
        
        $label = 'local_sinculpgc_import_'.time();
        $importid = \csv_import_reader::get_new_iid($label);
        $columns = '';
        if ($csvdata) {
            $csvreader = new \csv_import_reader($importid, $label);
            $csvreader->load_csv_content($csvdata, $data->encoding, $data->delimiter);
            $csvreader->init();
            $columns = $csvreader->get_columns();
        }
        $requiredfields = ['enrol', 'roleid','searchfield','searchpattern','enrolparams', 'groupto', 'useidnumber'];
        if($error = array_diff($requiredfields, $columns)) {
            \core\notification::error(get_string('columnsmissing', 'local_sinculpgc'));
            return;
        }        
        
        $plugins = enrol_get_plugins(false);
        $skip = [];
        $imported = 0;
        while ($record = $csvreader->next()) {
            $skipped = false;
            $rule = (object)array_combine($columns, $record);
            
            if(empty($rule->enrol) || !isset($plugins[$rule->enrol])) {
                    // skip, keep 3 fields for identification
                    $skipped = true;
            }
            
            if(empty($rule->roleid)) {
                $rule->roleid = 0;
            } elseif(is_string($rule->roleid)) {
                if($roleid = $DB->get_field('role', 'id', ['shortname' => $rule->roleid] ) ) {
                    $rule->roleid = $roleid;
                } else {
                    $skipped = true;
                }
            }
            
            // Changes br tags by \n in searchpattern
            $rule->searchpattern = preg_replace("/<br[^>]*>\s*\r*\n*/is", "\n", $rule->searchpattern);
            
            if($skipped) {
                // skip, keep 3 fields for identification
                $skip[] = implode(', ', array_slice( $record, 0, 3));
                continue;
            }
            
            $params = get_object_vars($rule);
            // we do not want to add variants of the same rule. Compare without enrolparams.
            unset($params['searchpattern']);
            unset($params['enrolparams']);
            if(!$skipped && !$DB->record_exists('local_sinculpgc_rules', $params)) {
                $rule->enabled = 0;
                $rule->timemodified = time();
                if($rule->id = $DB->insert_record('local_sinculpgc_rules', $rule)) {
                    $imported++;
                }
            }
        }
        
        if($imported) {
            \core\notification::success(get_string('importedcount', 'local_sinculpgc', $imported));
        }
        
        if($skip) {
            $msg = \html_writer::alist($skip);
            \core\notification::warning(get_string('importfailures', 'local_sinculpgc', $msg));
        }
    }
    
    
    /**
    * Construct and SQL where statement to search fo courses by rule criteria
    *
    * @param sinculpgcrule $rule The sinculpgc rule to use
    * @return array [$sql, $params]
    */
    public static function get_rule_search_sql($rule) {
        global $DB;

        $searchfield = $rule->get('searchfield');
        $searchpattern = $rule->get('searchpattern');
        
        if($searchfield == 'catidnumber') {
            $searchfield = 'cc.idnumber';
        } else {
            $searchfield = "c.$searchfield";
        }
        
        $i = 1;
        $patternsearch = [];
        $params = [];

        if($lines = preg_split("/(\r\n|\n|\r)/", $searchpattern)) {
            foreach($lines as $line) {
                if($patterns = explode('|', $line)) {
                    $search = [];
                    foreach($patterns as $pattern) {
                        $param = "pattern$i";
                        $search[] = $DB->sql_like($searchfield, ':'.$param);
                        $params[$param] = $pattern;
                        $i++;
                    }
                    if($search) {
                        $patternsearch[] = ' ( ' . implode(' OR ', $search) .' ) ';
                    }
                }
            }
        }
        
        if($patternsearch) {
            $patternsearch = ' ( ' . implode(' AND ', $patternsearch) .' ) ';
        } else {
            $patternsearch = false;
            $params = false;
        }

        return [$patternsearch, $params];
    }
    

    protected static function mtrace($msg) {
        mtrace('.... '.$msg);
    }    
    
    /**
    * Search courses by rule criteria and apply enrol instance
    *
    * @param int $ruleid The sinculpgc rule to use, 0 means all rules
    * @param bool $forcereset whether to force reset of manually modified enrol instances
    * @return null
    */
    public static function rule_add_enrol_instances($ruleid = 0, $forcereset = false) {
        global $DB;
        //local_sinculpgc_synch_rule($rule);
        // search by enrol courses with SQL In rule & NOT enrol customint8 = rule or rule modifieed after enrol  
        //force : all updated, even those enrol timemodified after rule = sobreescribir cambios manuales Config???
        //only add /update
                    
        // get the rules to work on
        $rules = [];
        if($ruleid) {
            $rule = self::retrieve_rule($ruleid);
            if($rule->get('enabled')) {
                $rules[$ruleid] = $rule;
            }
        } else {
            $rules = self::retrieve_enabled_rules();
        }
        
        $plugins = enrol_get_plugins(false);
        $creategroup = get_config('local_sinculpgc', 'forcegroup');
        $processed = 0;
        
        foreach($rules as $rule) {
            // now process this rule, search for matching courses NOT having enrol with customint8 = ruleid 
            list($coursewhere, $params) = self::get_rule_search_sql($rule); 
            $ruleid = $rule->get('id');
            $params['ruleid'] = $ruleid;
            
            $resetwhere = '';
            if($forcereset) {
                $resetwhere = ' OR ( e.timemodified > r.timemodified ) ';
            }
        
            $sql = "SELECT c.id, c.idnumber, c.shortname, c.category, cc.idnumber AS catidnumber, e.id AS enrolid, r.id AS ruleid  
                          FROM {course} c 
                            JOIN {course_categories} cc ON cc.id = c.category
                    LEFT JOIN {enrol} e ON e.courseid = c.id AND e.customint8 = :ruleid
                    LEFT JOIN {local_sinculpgc_rules} r ON r.id = e.customint8 AND e.enrol = r.enrol
                        WHERE $coursewhere AND 
                                    ( (e.id IS NULL) OR (  (e.timemodified < r.timemodified)  $resetwhere ) )";
            $rs = $DB->get_recordset_sql($sql, $params);
            
            $enrol = $rule->get('enrol');
            $instances = [];
            self::mtrace("Procesing rule $ruleid for enrol method $enrol \n");
            
            foreach($rs as $course) {
                
                $plugin = $plugins[$enrol];
                
                $instance = self::extract_enrol_instance($rule, $course->id, $creategroup);
                $instance = get_object_vars($instance);
                
                //TODO mtrace messages
                if(isset($course->customint8) && $course->customint8) {
                    // we have an instance, we are updating
                    $plugin->update_instance($course, $instance); 
                    $instances[] = $course->enrolid;
                    self::mtrace("... Updated enrol instance {$course->enrolid} in course {$course->shortname} \n");
                } else {
                    // we are adding a new one
                    $instances[] = $plugin->add_instance($course, $instance);
                    self::mtrace("... Added $enrol to course {$course->shortname} with id {$course->id} \n");
                }
                
                //TODO mtrace messages
            }
            $rs->close();
            
            // make added or updated enrol instances have the same timemodified as rule
            $instances = array_unique(array_filter($instances));
            if($instances) {
                list($insql, $params) = $DB->get_in_or_equal($instances);
                $select = "id $insql ";
                $DB->set_field_select('enrol', 'timemodified', $rule->get('timemodified'), $select, $params);
                // just a safety measure
                $DB->set_field_select('enrol', 'customint8', $ruleid, $select, $params);
            }
            $num = count($instances);
            $processed += $num;
            self::mtrace("Finished procesing rule $ruleid.  $num enrol instances added/updated \n");
        }
        
        return $processed ;
    }

    /**
    * Search courses that hsve enrol instances associated to sinculpgc rules 
    * and remove if rule deleted or disabled, or cours eno longer in rule  search criteria 
    *
    * @param int $ruleid The sinculpgc rule to use,  means all rules
    * @param bool $withdisabled whether to include existing but disabled rules in the removal or not
    * @return void
    */
    public static function rule_remove_enrol_instances($ruleid = 0, $withdisabled = false) {
        global $DB; 
        // process deletion of enrol instances
        //Borrados & disabled not keep
        // SELECT enrol customint8 > 0 LEF JOIN rule On customint8 = ruleid NULL 
        // SELECT enrol customint8 > 0  and rule customint8 = ruleid disabled & notkeep

        $params = [];
        $rulewhere = '';
        if($ruleid) {
            $rulewhere = ' AND e.customint8 = :ruleid '; 
            $params['ruleid'] = $ruleid;    
        }
            
        $disabledwhere = '';
        if($withdisabled) {
            $disabledwhere = ' OR ( (r.id = e.customint8) AND (r.enabled = 0) ) ';
        }
        
        $sql = "SELECT e.* 
                FROM {enrol} e 
                LEFT JOIN {local_sinculpgc_rules} r ON r.id = e.customint8 AND e.enrol = r.enrol
                WHERE e.customint8 > 0  AND ( (r.id IS NULL) $disabledwhere ) $rulewhere "; 
        $rs = $DB->get_recordset_sql($sql, $params);
        
        $plugins = enrol_get_plugins(false);
        $num = 0;
        self::mtrace("Procesing enrol instance removal");
        foreach($rs as $instance) {
            if($plugin = $plugins[$instance->enrol]) {
                $plugin->delete_instance($instance);
                self::mtrace("... deleted enrol instance with id {$instance->id} for course {$instance->courseid}, associated with rule {$instance->customint8} and enrol '{$instance->enrol}'  ");
                $num++;
                //TODO mtrace messages
            }
        }
        $rs->close();
        
        self::mtrace("Finished enrol instance removal");
        return $num;
    }
    

    
}
