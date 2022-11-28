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
 * Local stuff for meta category enrolment plugin.
 *
 * @package    enrol
 * @subpackage ulpgcunits
 * @copyright  2022 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/group/lib.php');

/**
 * Event handler for meta enrolment plugin.
 *
 * We try to keep everything in sync via listening to events,
 * it may fail sometimes, so we always do a full sync in cron too.
 */
class enrol_ulpgcunits_handler {

    /**
     * Event processor - unit member changed
     * @param \core\event\ulpgcunits_member_added $event
     * @return bool
     */
    public static function unit_updated(\core\event\unit_updated $event) {
        global $DB, $CFG;
        require_once("$CFG->dirroot/group/lib.php");

        // Does any enabled ulpgcunits instance want to sync with this ulpgcunits?
        $sql = "SELECT e.*, r.id as roleexists
                  FROM {enrol} e
             LEFT JOIN {role} r ON (r.id = e.roleid)
                 WHERE e.customchar2 = :unit AND e.enrol = 'ulpgcunits' AND e.status = :enrolstatus
              ORDER BY e.id ASC";
        $idn = $DB->get_field('local_sinculpgc_units', 'idnumber', ['id' => $event->objectid]);
        $params['unit'] = $idn;
        $params['enrolstatus'] = ENROL_INSTANCE_ENABLED;
        if (!$instances = $DB->get_records_sql($sql, $params)) {
            return true;
        }

        $trace = new null_progress_trace();
        enrol_ulpgcunits_sync($trace, null, $idn);
        $trace->finished();
        
        return true;        
    }

    /**
     * Event processor - unit member changed
     * @param \core\event\ulpgcunits_member_added $event
     * @return bool
     */
    public static function unit_deleted(\core\event\unit_deleted $event) {
        global $DB, $CFG;
        require_once("$CFG->dirroot/group/lib.php");

        // Does anything want to sync with this ulpgcunits?
        $idn = $DB->get_field('local_sinculpgc_units', 'idnumber', ['id' => $event->objectid]);
        $instances = $DB->get_recordset('enrol', array('customchar2'=>$idn, 'enrol'=>'ulpgcunits'), 'id ASC');
        if(!$instances->valid()) {
            return true;
        }
        
        $plugin = enrol_get_plugin('ulpgcunits');
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);        
        
        foreach ($instances as $instance) {
            if ($unenrolaction != ENROL_EXT_REMOVED_UNENROL) {
                $context = context_course::instance($instance->courseid);
                if ($unenrolaction != ENROL_EXT_REMOVED_SUSPEND) {
                    role_unassign_all(array('contextid' => $context->id, 'component' => 'enrol_ulpgcunits',
                        'itemid' => $instance->id));
                }
                $plugin->update_status($instance, ENROL_INSTANCE_DISABLED);
            } else {
                $plugin->delete_instance($instance);
            }
        }
        $instances->close();

        return true;        
    }
    
}


/**
 * Sync all meta category links.
 *
 * @param progress_trace $trace
 * @param int $courseid one course having an instance on category meta link, empty mean all
 * @param string $unitidnumber one course category or array of caterory IDs, pointed by ulpgcunits instance, empty mean any
 * @return int 0 means ok, 1 means error, 2 means plugin disabled
 */
function enrol_ulpgcunits_sync(progress_trace $trace, $courseid = NULL, $unitidnumber = NULL, $verbose = false) {
    global $CFG, $DB;
    
    // purge all roles if meta sync disabled, those can be recreated later here in cron
    if (!enrol_is_enabled('ulpgcunits')) {
        $trace->output('Meta sync plugin is disabled, unassigning all plugin roles and stopping.');
        role_unassign_all(array('component'=>'enrol_ulpgcunits'));
        return 2;
    }

    // unfortunately this may take a long time, execution can be interrupted safely
    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_HUGE);

    $trace->output('Starting ulpgcunits user enrolment synchronisation...');

    $allroles = get_all_roles();
    $plugin = enrol_get_plugin('ulpgcunits');
    $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);

    // Does anything want to sync with this ulpgcunits?
    $params = ['enrol' => 'ulpgcunits'];
    $where = '';
    if($courseid) {
        $where .= ' AND e.courseid = :courseid '; 
        $params['courseid'] = $courseid;
    }
    if($unitidnumber) {
        $where .= ' AND e.customchar2 = :customchar2 '; 
        $params['customchar2'] = $unitidnumber;
    }
   
   $sql = "SELECT e.*, su.name AS unitname, su.type, u1.id AS directorid, u2.id AS secretaryid, u3.id AS coordid
             FROM {enrol} e
             JOIN {local_sinculpgc_units} su ON su.idnumber = e.customchar2 AND su.idnumber != 0
        LEFT JOIN {user} u1 ON u1.idnumber = su.director
        LEFT JOIN {user} u2 ON u2.idnumber = su.secretary
        LEFT JOIN {user} u3 ON u3.idnumber = su.coord
            WHERE e.enrol = :enrol $where 
            ORDER BY e.id ";
   
    $instances = $DB->get_recordset_sql($sql, $params);
    if(!$instances->valid()) {
        return true;
    }
    
    foreach($instances as $instance) {
        // Iterate through all not enrolled yet users.
        $usertypes = explode(',', $instance->customchar3);
        $users = [];
        foreach($usertypes as $type) {
            if(!empty($instance->{$type.'id'})) { 
                $users[] =  $instance->{$type.'id'} ;
            }
        }
        
        $enrolled = $DB->get_records_menu('user_enrolments', ['enrolid' => $instance->id], '', 'userid, status');
        foreach($users as $uid) {
            if(!isset($enrolled[$uid]) || $enrolled[$uid] == ENROL_USER_SUSPENDED ) {
                 //If we are here, there are some  user to process
                if (isset($enrolled[$uid]) && $enrolled[$uid] == ENROL_USER_SUSPENDED) {
                    $plugin->update_user_enrol($instance, $uid->userid, ENROL_USER_ACTIVE);
                    $trace->output("unsuspending: $uid ==> $instance->courseid via ulpgcunit $instance->customchar2", 1);
                } else {
                    $plugin->enrol_user($instance, $uid);
                    $trace->output("enrolling: $uid ==> $instance->courseid via ulpgcunit $instance->customchar2", 1);
                }                
            }
        }
    
        // Unenrol as necessary 
        if($users) {
            list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u', false);
            $select = " enrolid = :enrolid AND userid $insql ";
            $params['enrolid'] = $instance->id;
            $toremove = $DB->get_records_select('user_enrolments', $select, $params);
            foreach($toremove as $ue) {
                if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                    // Remove enrolment together with group membership, grades, preferences, etc.
                    $plugin->unenrol_user($instance, $ue->userid);
                    $trace->output("unenrolling: $ue->userid ==> $instance->courseid via ulpgcunit $instance->customchar2", 1);

                } else { // ENROL_EXT_REMOVED_SUSPENDNOROLES
                    // Just disable and ignore any changes.
                    if ($ue->status != ENROL_USER_SUSPENDED) {
                        $plugin->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                        $context = context_course::instance($instance->courseid);
                        if ($unenrolaction != ENROL_EXT_REMOVED_SUSPEND) {
                            role_unassign_all(array('userid' => $ue->userid, 'contextid' => $context->id,
                                'component' => 'enrol_ulpgcunits', 'itemid' => $instance->id));
                            $trace->output("unsassigning all roles: $ue->userid ==> $instance->courseid", 1);
                        }
                        $trace->output("suspending: $ue->userid ==> $instance->courseid", 1);
                    }
                }            
            }
        }
    
    }
    $instances->close();
    unset($instances);

    // Now assign all necessary roles to enrolled users - skip suspended instances and users.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT e.roleid, ue.userid, c.id AS contextid, e.id AS itemid, e.courseid
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'ulpgcunits' AND e.status = :statusenabled $onecourse)
              JOIN {role} r ON (r.id = e.roleid)
              JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :coursecontext)
              JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0)
         LEFT JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = ue.userid AND ra.itemid = e.id AND ra.component = 'enrol_ulpgcunits' AND e.roleid = ra.roleid)
             WHERE ue.status = :useractive AND ra.id IS NULL";
    $params = array();
    $params['statusenabled'] = ENROL_INSTANCE_ENABLED;
    $params['useractive'] = ENROL_USER_ACTIVE;
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ra) {
        role_assign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_ulpgcunits', $ra->itemid);
        $trace->output("assigning role: $ra->userid ==> $ra->courseid as ".$allroles[$ra->roleid]->shortname, 1);
    }
    $rs->close();

    if ($unenrolaction != ENROL_EXT_REMOVED_SUSPEND) {
        // Remove unwanted roles - sync role can not be changed, we only remove role when unenrolled.
        $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
        $sql = "SELECT ra.roleid, ra.userid, ra.contextid, ra.itemid, e.courseid
                  FROM {role_assignments} ra
                  JOIN {context} c ON (c.id = ra.contextid AND c.contextlevel = :coursecontext)
                  JOIN {enrol} e ON (e.id = ra.itemid AND e.enrol = 'ulpgcunits' $onecourse)
             LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ra.userid AND ue.status = :useractive)
                 WHERE ra.component = 'enrol_ulpgcunits' AND (ue.id IS NULL OR e.status <> :statusenabled)";
        $params = array();
        $params['statusenabled'] = ENROL_INSTANCE_ENABLED;
        $params['useractive'] = ENROL_USER_ACTIVE;
        $params['coursecontext'] = CONTEXT_COURSE;
        $params['courseid'] = $courseid;

        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $ra) {
            role_unassign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_ulpgcunits', $ra->itemid);
            $trace->output("unassigning role: $ra->userid ==> $ra->courseid as ".$allroles[$ra->roleid]->shortname, 1);
        }
        $rs->close();
    }

    // Finally sync groups.
    $affectedusers = groups_sync_with_enrolment('ulpgcunits', $courseid, 'customint2');
    foreach ($affectedusers['removed'] as $gm) {
        $trace->output("removing user from group: $gm->userid ==> $gm->courseid - $gm->groupname", 1);
    }
    foreach ($affectedusers['added'] as $ue) {
        $trace->output("adding user to group: $ue->userid ==> $ue->courseid - $ue->groupname", 1);
    }    
    
    $trace->output('...ulpgcunits user enrolment synchronisation finished.');

    die;
    
    return 0;
}
