<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * External Web Service Template
 *
 * @package ulpgccore
 * @copyright 2013 Víctor Déniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->libdir . "/externallib.php"); 
require_once("$CFG->dirroot/enrol/externallib.php"); // for remote course
require_once ($CFG->dirroot . "/local/ulpgccore/lib.php"); 

class local_ulpgccore_external extends external_api {

	/**
	 * Actualizar cuatrimestre de un curso
	 *
	 * @return external_function_parameters
	 * @since Moodle 2.5
	 */
	public static function update_course_term_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'ID del curso' ),
				'term' => new external_value ( PARAM_INT, 'Cuatrimestre actual' )
		) );
	}

	/**
	 * Actualiza el cuatrimestre de un curso
	 *
	 * @param int $courseid
	 * @param int $term
	 * @since Moodle 2.5
	 */
	public static function update_course_term($courseid, $term) {
		global $CFG, $DB;
		require_once ($CFG->dirroot . "/course/lib.php");

		$params = self::validate_parameters ( self::update_course_term_parameters (), array (
				'courseid' => $courseid,
				'term' => $term
		) );

		// Catch any exception while updating course and return as warning to
		// user.
		try {
			// Ensure the current user is allowed to run this function.
			$context = context_course::instance ( $courseid, MUST_EXIST );
			// Comentado porque el usuario no tiene permiso
			self::validate_context ( $context );

			// Capability checking
			// OPTIONAL but in most web service it should present
			if (! has_capability ( 'moodle/course:update', $context )) {
				throw new moodle_exception ( 'requireloginerror' );
			}

			// Actualizar cuatrimestre del curso, en la nueva tabla
			$DB->set_field('local_ulpgccore_course', 'term', $term, array('courseid'=>$courseid));
			
			
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return 1;
	}

	/**
	 * Devuelve 1 si se modificó correctamente
	 *
	 * @return external_description
	 * @since Moodle 2.5
	 */
	public static function update_course_term_returns() {
		return new external_value ( PARAM_TEXT, 'Devuelve 1 si se modifica correctamente el cuatrimestre' );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_id_curso_parameters() {
		return new external_function_parameters ( array (
				'idnumber' => new external_value ( PARAM_TEXT, 'Idnumber del curso del que se quiere obtener el id' )
		) );
	}

	/**
	 * Returns el id del curso $idnumber
	 *
	 * @return string welcome message
	 */
	public static function get_id_curso($idnumber) {
		global $USER, $DB;

		// Parameter validation
		// REQUIRED
		$params = self::validate_parameters ( self::get_id_curso_parameters (), array (
				'idnumber' => $idnumber
		) );

		// Context validation
		// OPTIONAL but in most web service it should present
		$context = context_user::instance ( $USER->id );
		self::validate_context ( $context );

		// Capability checking
		// OPTIONAL but in most web service it should present
		if (! has_capability ( 'moodle/course:view', $context )) {
			throw new moodle_exception ( 'cannotviewprofile' );
		}

		$id_curso = $DB->get_field ( 'course', 'id', array (
				'idnumber' => $idnumber
		), $strictness = IGNORE_MISSING );

		return $id_curso;
	}

	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 */
	public static function get_id_curso_returns() {
		return new external_value ( PARAM_TEXT, 'Id del curso con el idnumber especificado' );
	}


    ////////////////////////////////////////////////////////////////////////////
	
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_courses_by_username_parameters() {
        return new external_function_parameters(
                array(
                    'username' => new external_value(PARAM_USERNAME, 'username'),
                    'catidnumber' => new external_value(PARAM_ALPHANUMEXT, 'catidnumber', VALUE_DEFAULT, '')
                )
        );
    }

    
    /**
     * Get a user's enrolled courses.
     *
     * This is a wrapper of core_enrol_get_users_courses(). It accepts
     * the username instead of the id and does some optional filtering
     * logic on the idnumber.
     *
     * @param string $username
     * @param string $catidnumber category idnumber to search courses in 
     * @return array
     */
    public static function get_courses_by_username($username, $catidnumber = '') {
        global $DB;

        // Validate parameters passed from webservice.
        $params = self::validate_parameters(self::get_courses_by_username_parameters(), 
                                            array('username' => $username, 'catidnumber' => $catidnumber) );

        $username = $params['username'];
        $catidnumber = $params['catidnumber'];
        // Extract the userid from the username.
        $userid = $DB->get_field('user', 'id', array('username' => $username));
        
        $categoryid = 0;
        if($catidnumber) {
            $categoryid = $DB->get_field('course_categories', 'id', array('idnumber' => $catidnumber));
        }

        // Get the courses.
        $courses = core_enrol_external::get_users_courses($userid);

        // Process results: apply term logic and drop enrollment counts.
        $result = array();
        foreach ($courses as $course) {
            // only those courses in category
            if($categoryid && ($course->category != $categoryid)) {
                continue;
            }
        
            $new = 0;
            if($username) {
                $new = local_ulpgccore_course_recent_activity((object)$course, $username);
                $new = empty($new) ? 0 : 1;
            }        
        
            $result[] = array(
                'id' => $course['id'],
                'shortname' => $course['shortname'],
                'fullname' => $course['fullname'],
                'visible' => $course['visible'],
                'recentactivity' => $new,
            );
        }

        /*
        // Sort courses by recent access.
        $courselist = self::get_recent_courses($userid);
        $unsorted = $result;
        $sorted = array();
        foreach ($result as $cid => $course) {
            $sort = array_search($course['id'], $courselist);
            if ($sort !== false) {
                $sorted[$sort] = $course;
                unset($unsorted[$cid]);
            }
        }

        ksort($sorted);
        $result = array_merge($sorted, $unsorted);
        */
        return $result;
    }

    /**
     * Retrieves the courses viewed by the user.
     *
     * This function queries the active logstore for access information.
     *
     * @param int $userid
     * @return array
     */
    protected static function get_recent_courses($userid) {
        $manager = get_log_manager();
        $selectreaders = $manager->get_readers();
        if ($selectreaders) {
            $courses = array();
            $reader = reset($selectreaders);

            // Selection criteria.
            $joins = array(
                "userid = :userid",
                "courseid != 1",
                "eventname = :eventname"
            );
            $selector = implode(' AND ', $joins);
            $events = $reader->get_events_select($selector, array('userid' => $userid, 'eventname' => '\core\event\course_viewed'),
                    'timecreated DESC', 0, 0);
            foreach ($events as $event) {
                $courses[] = $event->get_data()['courseid'];
            }
            return $courses;

        } else {
            // No available log reader found.
            return array();
        }
    }

    /**
     * Returns description of get_courses_by_username_returns() result value.
     *
     * @return \external_description
     */
    public static function get_courses_by_username_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'        => new external_value(PARAM_INT, 'id of course'),
                    'shortname' => new external_value(PARAM_RAW, 'short name of course'),
                    'fullname'  => new external_value(PARAM_RAW, 'long name of course'),
                    'visible'   => new external_value(PARAM_INT, '1 means visible, 0 means hidden course'),
                    'recentactivity' => new external_value(PARAM_INT, '1 means active, 0 means quiet course'),
                )
            )
        );
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_courses_by_field_parameters() {
        return new external_function_parameters(
                array(
                    'searchlist' => new external_value(PARAM_TAGLIST, 'searchlist, a comma separated list of values to search courses with matching values'),
                    'field' => new external_value(PARAM_ALPHA, 'Field, the course field to match', VALUE_DEFAULT, 'shortname'),
                    'username' => new external_value(PARAM_ALPHANUMEXT, 'username', VALUE_DEFAULT, '0')
                )
        );
    }

    /**
     * Gets courses identified by a list of values
     *
     * It accepts a list of tersm and a course field identifier. 
     * Looks for courses that have those values (from searchlist) in the indicated course field
     *
     * @param string $searchlist a comma-separated list of values
     * @param string $field a filed in courses table
     * @param string $username a user idnumber or username to check for enrolment
     * @return array
     */
    public static function get_courses_by_field($searchlist, $field = 'shortname', $username = '') {
        global $DB;

        // Validate parameters passed from webservice.
        $params = self::validate_parameters(self::get_courses_by_field_parameters(), 
                                            array('searchlist' => $searchlist, 'field' => $field, 'username' => $username) );

        $username = $params['username'];      
        $field = $params['field'];
        $search = explode(',', $params['searchlist']);
        $search = array_map('trim', $search);

        list($insql, $inparams) = $DB->get_in_or_equal($search);
        $select = " $field $insql ";
        $courses = $DB->get_records_select('course', " $field $insql ", $inparams, '', 'id, shortname, fullname, idnumber, visible');
        
        $result = [];
        foreach($courses as $course) {
            $new = 0;
            if($username) {
                $new = local_ulpgccore_course_recent_activity($course, $username);
                $new = empty($new) ? 0 : 1;
            }
            $result[] = array(
                'id' => $course->id,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
                'visible' => $course->visible,
                'recentactivity' => $new,
            );            
        }

        return $result;
    }

    /**
     * Returns description of get_courses_by_username_returns() result value.
     *
     * @return \external_description
     */
    public static function get_courses_by_field_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'                    => new external_value(PARAM_INT, 'id of course'),
                    'shortname'      => new external_value(PARAM_RAW, 'short name of course'),
                    'fullname'         => new external_value(PARAM_RAW, 'long name of course'),
                    'visible'             => new external_value(PARAM_INT, '1 means visible, 0 means hidden course'),
                    'recentactivity' => new external_value(PARAM_INT, '1 means active, 0 means quiet course'),
                )
            )
        );
    }


//////////////////////////////////////////////////////////////////////////////////////////////////////
// testing trackers

    public static function get_tracker_issues_by_username_parameters() {
        return new external_function_parameters (
            array(
                'username' => new external_value(
                        PARAM_USERNAME,
                        'primary identifier'),
                'trackerid' => new external_value(
                        PARAM_INT,
                        'remote tracker id where to search'),
            )
        );
    }

    public static function get_tracker_issues_by_username_returns() {
       return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'        => new external_value(PARAM_INT, 'id of issue'),
                    'summary' => new external_value(PARAM_RAW, 'short name of issue', VALUE_DEFAULT, 'xxx'),
                    'ticketprefix'  => new external_value(PARAM_RAW, ' prefix for issues  ', VALUE_DEFAULT, ''),
                    'hasresolution' => new external_value(PARAM_INT, '0 no 1 yes, resolution field is filled', VALUE_DEFAULT, 0),                    
                    'status'  => new external_value(PARAM_INT, 'status of the issue', VALUE_DEFAULT, 0),
                    'userlastseen' => new external_value(PARAM_INT, 'date last seen by user', VALUE_DEFAULT, 0)
                )
            )
        );
    }    

    /**
     * Get a user's recent unseen issues on a tracker.
     *
     * @param string $username
     * @param int $trackerid
     * @return array
     */
    public static function get_tracker_issues_by_username($username, $trackerid) {
        global $DB;

        // Validate parameters passed from webservice.
        $params = self::validate_parameters(self::get_tracker_issues_by_username_parameters(), 
                            array('username' => $username, 'trackerid' => $trackerid));
        $result = [];

        // Extract the userid from the username.
        $userid = $DB->get_field('user', 'id', array('username' => $username));    
        
        $ticketprefix = $DB->get_field('tracker', 'ticketprefix', array('id' => $trackerid));
        $openstatus = get_config('tracker', 'openstatus');

        $levels = explode(',', $openstatus);
        list($insql, $inparams) = $DB->get_in_or_equal($levels, SQL_PARAMS_NAMED, 'st_');
        $select = " reportedby = :userid AND trackerid = :trackerid AND status $insql AND usermodified < resolvermodified AND userlastseen < resolvermodified";
        $inparams['userid'] = $userid;
        $inparams['trackerid'] = $trackerid;
        $fields = 'id, summary, status, resolution, userlastseen';

        $issues = $DB->get_records_select('tracker_issue', $select, $inparams, 'usermodified DESC', $fields);
        
        foreach ($issues as $issue) {
            $resolution = empty($issue->resolution) ? 0 : 1;
            $result[] = array(
                'id' => $issue->id,
                'summary' => $issue->summary,
                'ticketprefix' => $ticketprefix,
                'hasresolution' => $resolution,
                'status' => (int)$issue->status,
                'userlastseen' => (int)$issue->userlastseen,
            );    
        }
        
        return $result;

    }

}
