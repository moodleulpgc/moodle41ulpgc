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
 * Meta category enrolment plugin.
 *
 * @package    enrol
 * @subpackage ulpgcunits
 * @copyright  2022 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * ULPGCUNITS__CREATE_GROUP constant for automatically creating a group for containing users from this meta enrol.
 */
define('ULPGCUNITS_CREATE_GROUP', -1);

/**
 * ULPGCUNITS_FROM_xxxx constants for automatically refresh Unit specification mode.
 */
define('ULPGCUNITS_FROM_FACULTY', -1);      //local_ulpgccore_categories faculty
define('ULPGCUNITS_FROM_DEGREE', -2);       //local_ulpgccore_categories faculty
define('ULPGCUNITS_FROM_CATEGORY', -3);     //category idnumber plain
define('ULPGCUNITS_FROM_CATFACULTY', -4);   //category idnumber CCC_tttt_00
define('ULPGCUNITS_FROM_CATDEGREE', -5);    //category idnumber ccc_TTTT_00
define('ULPGCUNITS_FROM_IDNFACULTY', -6);   //course idnumber centro
define('ULPGCUNITS_FROM_IDNDEGREE', -7);    //course idnumber titulación
define('ULPGCUNITS_FROM_DEPARTAMENT', -8);    //local_ulpgccore_course dept

/*****************************************************************************
customchar1 : unit selector
customchar2 : actual ulpgcunit in use (hidden)

customint3  : auto refresh unit when changed course
customint2  : group
customint8  : local_sinculpgc rule in use. not edited 
customchar3 : unit usertypes enrolled
roleid      : enrolledas assignrole
******************************************************************************/

/**
 * ULPGC units course enrolment plugin.
 * @author Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_ulpgcunits_plugin extends enrol_plugin {


    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/ulpgcunits:config', $context);
    }

    /**
     * Returns localised name of enrol instance
     *
     * @param stdClass $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol);
        } else if (empty($instance->name)) {
            $enrol = $this->get_name();
            
            if($DB->record_exists('local_sinculpgc_units', ['idnumber'=>$instance->customchar2])) {
                $name = $instance->customchar2;
            } else {
                $name = get_string('deletedunit', 'enrol_ulpgcunits', $instance->customchar2);
            }
            if($instance->customint3) {
                $name .= get_string('autocategory', 'enrol_ulpgcunits');
            }
            return get_string('pluginname', 'enrol_'.$enrol) . ' (' . format_string($name) . ')';
        } else {
            return format_string($instance->name);
        }
    }

    /**
     * Returns true if we can add a new instance to this course.
     *
     * @param int $courseid
     * @return boolean
     */
    public function can_add_instance($courseid) {
        global $DB;
        $context = context_course::instance($courseid, MUST_EXIST);
        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/ulpgcunits:config', $context)) {
            return false;
        }
        
        if(!$DB->record_exists_select('local_sinculpgc_units', 'id > 0 ')) {
            false;
        }
        
        return true;
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
     /*
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/ulpgcunits:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }
*/

    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array $fields instance fields
     * @return int id of last instance, null if can not be created
     */
    public function add_instance($course, array $fields = null) {
        global $CFG;

        if(is_array($fields['customchar1'])) {
            $fields['customchar1'] = reset($fields['customchar1']);
        }
        if((int)$fields['customchar1'] > 0) {
            $fields['customchar2'] = $fields['customchar1'];
        } else {
            $fields['customchar2'] =  $this->get_unit_from_course((int)$fields['customchar1'], $course);
        }        
        
        if($fields['customchar3'] && is_array($fields['customchar3'])) {
            $fields['customchar3'] = implode(',', $fields['customchar3']);
        }
        
        if (!empty($fields['customint2']) && $fields['customint2'] == ULPGCUNITS_CREATE_GROUP) {
            $groupid = enrol_ulpgcunits_create_new_group($course->id, $fields['customchar2']);
            $fields['customint2'] = $groupid;
        }
        
        $result = parent::add_instance($course, $fields);

        require_once($CFG->dirroot . '/enrol/ulpgcunits/locallib.php');
        $trace = new null_progress_trace();
        enrol_ulpgcunits_sync($trace, $course->id, $fields['customchar2']);
        $trace->finished();

        return $result;
    }

    /**
     * Update instance of enrol plugin.
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     * @return boolean
     */
    public function update_instance($instance, $data) {
        global $CFG, $DB;

        // NOTE: no ulpgcunit changes here!!!
        if(is_array($data->customchar1)) {
            $data->customchar1 = reset($data->customchar1);
        }

        if($data->customchar3 && is_array($data->customchar3)) {
            $data->customchar3 = implode(',', $data->customchar3);
        }

        $context = context_course::instance($instance->courseid);
        if ($data->roleid != $instance->roleid) {
            // The sync script can only add roles, for perf reasons it does not modify them.
            $params = array(
                'contextid' => $context->id,
                'roleid' => $instance->roleid,
                'component' => 'enrol_ulpgcunits',
                'itemid' => $instance->id
            );
            role_unassign_all($params);
        }
        // Create a new group for the cohort if requested.
        if ($data->customint2 == ULPGCUNITS_CREATE_GROUP) {
            $groupid = enrol_cohort_create_new_group($instance->courseid, $data->customchar2);
            $data->customint2 = $groupid;
        }
        
        $result = parent::update_instance($instance, $data);

        require_once($CFG->dirroot . '/enrol/ulpgcunits/locallib.php');
        $trace = new null_progress_trace();
        enrol_ulpgcunits_sync($trace, $instance->courseid, $instance->customchar2);
        $trace->finished();
        
        return $result;
    }

    /**
     * Called after updating/inserting course.
     *
     * @param bool $inserted true if course just inserted
     * @param stdClass $course
     * @param stdClass $data form data
     * @return void
     */
    public function course_updated($inserted, $course, $data) {
        global $CFG, $DB;
       // It turns out there is no need for ulpgcunits to deal with this hook, see observer class.
    }
    
    /**
     * Update instance status
     *
     * @param stdClass $instance
     * @param int $newstatus ENROL_INSTANCE_ENABLED, ENROL_INSTANCE_DISABLED
     * @return void
     */
    public function update_status($instance, $newstatus) {
        global $CFG;

        parent::update_status($instance, $newstatus);

        require_once($CFG->dirroot . '/enrol/ulpgcunits/locallib.php');
        $trace = new null_progress_trace();
        enrol_ulpgcunits_sync($trace, $instance->courseid, $instance->customchar2);
        $trace->finished();
    }

    /**
     * Does this plugin allow manual unenrolment of a specific user?
     * Yes, but only if user suspended...
     *
     * @param stdClass $instance course enrol instance
     * @param stdClass $ue record from user_enrolments table
     *
     * @return bool - true means user with 'enrol/xxx:unenrol' may unenrol this user, false means nobody may touch this user enrolment
     */
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        if ($ue->status == ENROL_USER_SUSPENDED) {
            return true;
        }

        return false;
    }    
    

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB, $CFG;

        if (!$step->get_task()->is_samesite()) {
            // No meta restore from other sites.
            $step->set_mapping('enrol', $oldid, 0);
            //return;
        }

        if ($data->customint2 > 0 ) {
            $data->customint2 = $step->get_mappingid('group', $data->customint2);
        }

        if($data->customchar1  > 0) {
            $data->customchar2 = $data->customchar1;
        } else {
            $data->customchar2 = $this->get_unit_from_course((int)$data['customchar1'][0], $course);
        }
        /*
        if($DB->record_exists('local_sinculpgc_units', ['idnumber' => $data->customchar2]) {
            $instance = $DB->get_record('enrol', ['roleid' => $data->roleid, 
                                                  'customchar1' => $data->customchar1,
                                                  'customchar2' => $data->customchar2,
                                                  'courseid' => $course->id, 
                                                  'enrol' => $this->get_name(),], '*',  IGNORE_MISSING   )  ; 
            if ($instance) {
                $instanceid = $instance->id;
            } else {
                $instanceid = $this->add_instance($course, (array)$data);
            }
            $step->set_mapping('enrol', $oldid, $instanceid);

            require_once($CFG->dirroot . '/enrol/ulpgcunits/locallib.php');
            $trace = new null_progress_trace();
            enrol_ulpgcunits_sync($trace, $course->id, $data->customchar2);
            $trace->finished();

        } else {
            $step->set_mapping('enrol', $oldid, 0);
        }
        */
    }
    
    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $oldinstancestatus
     * @param int $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        global $DB;

        if ($this->get_config('unenrolaction') != ENROL_EXT_REMOVED_SUSPENDNOROLES) {
            // Enrolments were already synchronised in restore_instance(), we do not want any suspended leftovers.
            return;
        }

        // ENROL_EXT_REMOVED_SUSPENDNOROLES means all previous enrolments are restored
        // but without roles and suspended.

        if (!$DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
            $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, ENROL_USER_SUSPENDED);
        }
    }
    
    /**
     * Restore user group membership.
     * @param stdClass $instance
     * @param int $groupid
     * @param int $userid
     */
    public function restore_group_member($instance, $groupid, $userid) {
        // Nothing to do here, the group members are added in $this->restore_group_restored()
        return;
    }    
    
    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/ulpgcunits:config', $context);
    }
    
    /**
     * Return an array of valid options for the available units.
     *
     * @return array
     */
    protected function get_units_options() {
        global $DB;
        
        $units = [ULPGCUNITS_FROM_FACULTY   => get_string('fromfaculty', 'enrol_ulpgcunits'),
                  ULPGCUNITS_FROM_DEGREE    => get_string('fromdegree', 'enrol_ulpgcunits'),
                  ULPGCUNITS_FROM_CATEGORY  => get_string('fromcategory', 'enrol_ulpgcunits'),
                  ULPGCUNITS_FROM_CATFACULTY=> get_string('fromcatfaculty', 'enrol_ulpgcunits'),
                  ULPGCUNITS_FROM_CATDEGREE => get_string('fromcatdegree', 'enrol_ulpgcunits'),
                  ULPGCUNITS_FROM_IDNFACULTY    => get_string('fromidnfaculty', 'enrol_ulpgcunits'),
                  ULPGCUNITS_FROM_IDNDEGREE    => get_string('fromidndegree', 'enrol_ulpgcunits'),
        ];
        
        $ulpgcunits = $DB->get_records('local_sinculpgc_units', [], 'type ASC, name ASC', 'id, type, name, idnumber' );
        foreach($ulpgcunits as $uid => $unit) {
            $unit->type = get_string('unit_'.$unit->type, 'enrol_ulpgcunits'); 
            $unit->name = format_string($unit->name);
            $units[$unit->idnumber] = get_string('unitnameformat', 'enrol_ulpgcunits', $unit);
        }

        return $units;
    }    
    
    /**
     * Return an array of valid options for the roles.
     *
     * @param stdClass $instance
     * @param context $coursecontext
     * @return array
     */
    protected function get_role_options($instance, $coursecontext) {
        global $DB;

        $roles = get_assignable_roles($coursecontext, ROLENAME_BOTH);
        $roles[0] = get_string('none');
        $roles = array_reverse($roles, true); // Descending default sortorder.

        // If the instance is already configured, but the configured role is no longer assignable in the course then add it back.
        if ($instance->id and !isset($roles[$instance->roleid])) {
            if ($role = $DB->get_record('role', array('id' => $instance->roleid))) {
                $roles[$instance->roleid] = role_get_name($role, $coursecontext, ROLENAME_BOTH);
            } else {
                $roles[$instance->roleid] = get_string('error');
            }
        }

        return $roles;
    }    
    
    /**
     * Return an array of valid options for the groups.
     *
     * @param context $coursecontext
     * @return array
     */
    protected function get_group_options($coursecontext) {
        $groups = array(0 => get_string('none'));
        if (has_capability('moodle/course:managegroups', $coursecontext)) {
            $groups[ULPGCUNITS_CREATE_GROUP] = get_string('creategroup', 'enrol_ulpgcunits');
        }

        foreach (groups_get_all_groups($coursecontext->instanceid) as $group) {
            $groups[$group->id] = format_string($group->name, true, array('context' => $coursecontext));
        }

        return $groups;
    }

    /**
     * We are a good plugin and don't invent our own UI/validation code path.
     *
     * @return boolean
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Add elements to the edit instance form.
     *
     * @param stdClass $instance
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return bool
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $DB;

/*****************************************************************************
customchar1 : unit selector
customchar2 : actual ulpgcunit in use (hidden)

customint3  : auto refresh unit when changed course
customint2  : group
customint8  : local_sinculpgc rule in use. not edited 
customchar3 : unit usertypes enrolled
roleid      : enrolledas assignrole
******************************************************************************/
        
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);        
        
        
        // customchar1 : store category meta linked 
        $options = array(
            'requiredcapabilities' => 'enrol/ulpgcunits:selectaslinked',
            'multiple' => true,
        );
        $units = $this->get_units_options();
        
        $mform->addElement('autocomplete', 'customchar1', get_string('linkedunits', 'enrol_ulpgcunits'), $units, $options);
        $mform->addRule('customchar1', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('customchar1', 'linkedunits', 'enrol_ulpgcunits');
        $mform->setDefault('customchar1', '');
        $current = 0;
        $rule = 0;
        if(!empty($instance->id)) {
            $mform->freeze('customchar1');
            $current = $instance->customchar1;
            $mform->addElement('static', 'currentunit',  get_string('currentunit', 'enrol_ulpgcunits'), $units[$current]);
            $rule = $instance->customint8;            
        }
        
        $mform->addElement('hidden', 'customchar2', $current);
        $mform->setType('customchar2', PARAM_INT);    
        
        $mform->addElement('hidden', 'customint8', $rule);
        $mform->setType('customint8', PARAM_INT);    
        
        // customint3 : autocategory, if refresh when moved
        $mform->addElement('selectyesno', 'customint3', get_string('refreshautocategory', 'enrol_ulpgcunits'), 0);
        $mform->addHelpButton('customint3', 'refreshautocategory', 'enrol_ulpgcunits');
        $mform->disabledIf('customint3', 'linkedcategories', 'neq', 0);

        $typesmenu = ['director' => get_string('director', 'enrol_ulpgcunits'),
                        'secretary' => get_string('secretary', 'enrol_ulpgcunits'),
                        'coord' => get_string('coordinator', 'enrol_ulpgcunits')];        
        $usertypes = &$mform->addElement('select', 'customchar3', get_string('usertypes', 'enrol_ulpgcunits'), $typesmenu, 'size="3"');
        $usertypes->setMultiple(true);
        $mform->addHelpButton('customchar3', 'usertypes', 'enrol_ulpgcunits');
        $mform->addRule('customchar3', null, 'required');

        $roles = $this->get_role_options($instance, $context);
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_ulpgcunits'), $roles);
        $mform->setDefault('roleid', $this->get_config('roleid'));
        
        $groups = $this->get_group_options($context);
        $mform->addElement('select', 'customint2', get_string('syncgroup', 'enrol_ulpgcunits'), $groups);
        $mform->addHelpButton('customint2', 'syncgroup', 'enrol_ulpgcunits');
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        global $DB;
        $errors = array();

        $idnumber = '';
        if(empty($data['customchar1'])) {
            $errors['customchar1'] = get_string('required');        
        } elseif(count($data['customchar1']) > 1 ) {
            $errors['customchar1'] = get_string('errormultipleunits', 'enrol_ulpgcunits');
        } elseif((int)$data['customchar1'][0] < 0) {            
            $idnumber =  $this->get_unit_from_course((int)$data['customchar1'][0], $context->instanceid);
        } else {
            $idnumber = $data['customchar1'][0];
        }
        
        if(empty($idnumber) || !$DB->record_exists('local_sinculpgc_units', ['idnumber' => $idnumber])) {
            $errors['customchar1'] = get_string('errorunitnotexists', 'enrol_ulpgcunits');   
        }
        
        $params = ['roleid'     => $data['roleid'],
                   'courseid'   => $data['courseid'],
                   'id'         => $data['id'],
                   'idnumber'   => $idnumber,];
        $select = "roleid = :roleid AND customchar2 = :idnumber AND courseid = :courseid AND enrol = 'ulpgcunits' AND id <> :id";
        if ($DB->record_exists_select('enrol', $select, $params)) {
            $errors['customchar1'] = get_string('instanceexists', 'enrol_ulpgcunits');
        }        
        
        $validunits = array_keys($this->get_units_options());
        $validroles = array_keys($this->get_role_options($instance, $context));
        $validgroups = array_keys($this->get_group_options($context));        

        $tovalidate = ['customchar2' => $validunits,
                        'roleid' => $validroles,            
                        'customint2' => $validgroups];
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }

    

    /**
     * Calculates the unit associated to the course by several criteria 
     *
     * @param string $input the instance unit selector customchar1
     * @param mixed $courseorid a course object or ID for loading the course object
     * @return string $unit idnumber string
     */
    protected function get_unit_from_course($input, $courseorid) {
        global $DB;

        $unit = '';
        $course = false;

        if(is_object($courseorid) && isset($courseorid->category) && 
                                isset($courseorid->id) &&  isset($courseorid->idnumber)) {
            $course = $courseorid;
        } elseif((int)$courseorid) {
            $course = $DB->get_record('course', ['id' => $courseorid]);
        }

        if(empty($course)) {
            return $unit;
        }

        switch($input) {
            case ULPGCUNITS_FROM_FACULTY :
                    $unit = $DB->get_field('local_ulpgccore_categories', 'faculty', ['categoryid' => $course->category]);
                    break;
        
            case ULPGCUNITS_FROM_DEGREE :
                    $unit = $DB->get_field('local_ulpgccore_categories', 'degree', ['categoryid' => $course->category]);
                    break;

            case ULPGCUNITS_FROM_CATEGORY :
                    $unit = $DB->get_field('course_categories', 'idnumber', ['id' => $course->category]);
                    break;

            case ULPGCUNITS_FROM_CATFACULTY :
                    $units = $DB->get_field('course_categories', 'idnumber', ['id' => $course->category]);
                    $units = explode('_', $units);
                    $unit = reset($units);
                    break;

            case ULPGCUNITS_FROM_CATDEGREE :  // 111_4036_00_00
                    $units = $DB->get_field('course_categories', 'idnumber', ['id' => $course->category]);
                    $units = explode('_', $units);
                    $unit = $units[1];
                    break;
                    
            case ULPGCUNITS_FROM_IDNFACULTY : // course idnumber 4029_40_00_1_1_42901_165
                    $parts = explode('_', $course->idnumber);
                    $unit = end($parts);
                    break;
                    
            case ULPGCUNITS_FROM_IDNDEGREE :
                    $parts = explode('_', $course->idnumber);
                    $unit = reset($parts);
                    
                    break;

            case ULPGCUNITS_FROM_DEPARTMENT :
                    $unit = $DB->get_field('local_ulpgccore_course', 'department', ['courseid' => $course->id]);
                    break;
                    
            default: $unit = $input;
        }

        return $unit;
    }
    
}

/**
* Prevent removal of enrol roles.
* @param int $itemid
* @param int $groupid
* @param int $userid
* @return bool
*/
function enrol_ulpgcunits_allow_group_member_remove($itemid, $groupid, $userid) {
    return false;
}    

    
/**
 * Create a new group with the ulpgcunit name.
 *
 * @param int $courseid
 * @param string $ulpgcunit
 * @return int $groupid Group ID for this ulpgcunits.
 */
function enrol_ulpgcunits_create_new_group($courseid, $ulpgcunit) {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/group/lib.php');

    $groupidnumber = 'ulpgcunit_'.$ulpgcunit;
    if($group = groups_get_group_by_idnumber($courseid, $groupidnumber)) {
        return $group->id;
    }
    
    $groupname = 'ULPGC-'.$ulpgcunit;
    $a = new stdClass();
    $a->name = $groupname;
    $a->increment = '';
        $inc = 1;
    // Check to see if the ulpgcunits group name already exists. Add an incremented number if it does.
    while ($DB->record_exists('groups', array('name' => $groupname, 'courseid' => $courseid))) {
        $a->increment = '(' . (++$inc) . ')';
        $newshortname = trim(get_string('defaultgroupnametext', 'enrol_ulpgcunits', $a));
        $groupname = $newshortname;
    }
    // Create a new group for the ulpgcunit.
    $groupdata = new stdClass();
    $groupdata->courseid = $courseid;
    $groupdata->name = $groupname;
    $groupdata->idnumber = $groupidnumber;
    $groupid = groups_create_group($groupdata);

    return $groupid;
}    
