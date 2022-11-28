<?php

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
 * Web services con utilidades necesarias para la ULPGC
 *
 * @package    ulpgccore
 * @copyright  2013 Víctor Déniz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'local_ulpgccore_get_id_curso' => array(
                'classname'   => 'local_ulpgccore_external',
                'methodname'  => 'get_id_curso',
                'classpath'   => 'local/ulpgccore/externallib.php',
                'description' => 'Devuelve el id de un curso a partir del idnumber',
                'capabilities' => 'moodle/course:view',
                'type'        => 'read',
        ),
        'local_ulpgccore_update_course_term' => array(
        		'classname'   => 'local_ulpgccore_external',
        		'methodname'  => 'update_course_term',
        		'classpath'   => 'local/ulpgccore/externallib.php',
        		'description' => 'Modifica el cuatrimestre de un curso',
        		'capabilities' => array('moodle/course:update','moodle/course:viewhiddencourses'),        		
        		'type'        => 'write',
        ),
        
        'local_ulpgccore_get_remote_courses_by_username' => array(
            'classname'    => 'local_ulpgccore_external',
            'methodname'   => 'get_courses_by_username',
            'classpath'    => 'local/ulpgccore/externallib.php',
            'description'  => 'Get user\'s courses by username.',
            'type'         => 'read',
            'capabilities' => array('moodle/course:view, moodle/course:viewparticipants'),
        ),
        
        'local_ulpgccore_get_remote_courses_by_field' => array(
            'classname'    => 'local_ulpgccore_external',
            'methodname'   => 'get_courses_by_field',
            'classpath'    => 'local/ulpgccore/externallib.php',
            'description'  => 'Get courses that match a field to a list of values.',
            'type'         => 'read',
            'capabilities' => array('moodle/course:view, moodle/course:viewparticipants'),
        ),
        
        'local_ulpgccore_get_tracker_issues_by_username' => array(
            'classname'    => 'local_ulpgccore_external',
            'methodname'   => 'get_tracker_issues_by_username',
            'classpath'    => 'local/ulpgccore/externallib.php',
            'description'  => 'Get tracker issues.',
            'type'         => 'read',
            'capabilities' => array('moodle/course:view, moodle/course:viewparticipants'),
        ),


);
// Define web service.
$services = array (
        'ULPGC remote courses web service' => array (
                'functions' => array (
                        'local_ulpgccore_get_id_curso',
                        'local_ulpgccore_update_course_term',
                        'local_ulpgccore_get_remote_courses_by_username',
                        'local_ulpgccore_get_remote_courses_by_field', 
                        'local_ulpgccore_get_tracker_issues_by_username',
                ),
                'shortname' => 'local_ulpgccore_remote_courses',
                'restrictedusers' => 1,
                'enabled' => 0
        )
);
