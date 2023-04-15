<?php
/**
 * This file contains local_supervision main local library functions
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



    /**
     * Load the plugins from the warning sub folders
     * @param string/array $type - any subfollder name, all if leaved null
     * @param bool $class - if true return classnames
     * @return array - The sorted list of plugins or pluginclasses names
     */
    function supervision_load_plugins($type=null, $class=false) {
        global $CFG;

        $result = array();
        $names = array();
        if($type) {
            if(is_string($type)) {
                $names = array($type => '/local/supervision/warning/'.$type);
            } elseif(is_array($type)) {
                    foreach($type as $t) {
                        $names = array($t => '/local/supervision/warning/'.$t);
                    }
            }
        } else {
            $names = get_plugin_list('supervisionwarning');
        }

        foreach ($names as $name => $path) {
            if (file_exists($path . '/locallib.php')) {
                require_once($path . '/locallib.php');
                $pluginclass = 'supervisionwarning_' . $name;
                if($class)
                    $plugin = $pluginclass;
                else
                    $plugin = new $pluginclass();
                $result[$name] = $plugin;
            }
        }

        ksort($result);
        return $result;
    }


    /**
     * Get from permissions table the Course categories the user can review courses
     *
     * @param int $userid User id
     * @return array categories ids
     */
    function supervision_get_reviewed_itemnames($userid, $scope='category') {
        global $DB;
        $items = array();

        $select = "SELECT sp.id, sp.scope, sp.instance, sp.userid, sp.review ";
        $from = " FROM {supervision_permissions} sp ";
        if($scope == 'category') {
            $select .= ", cc.name AS itemname ";
            $from .= "\n JOIN {course_categories} cc ON sp.instance = cc.id ";
        } elseif($scope == 'department') {
            $select .= ", u.name AS itemname ";
            $from .= "\n JOIN {local_sinculpgc_units} u ON sp.instance = u.id AND u.type = '".SINCULPGC_UNITS_DEPARTMENT."'";
        } else {
            $select .= ", sp.instance AS itemname ";
        }
        $where = " WHERE sp.scope = :scope AND sp.userid = :userid AND review = :review ";
        $params = array('scope'=>$scope, 'userid'=>$userid, 'review'=>1);

        if($reviews = $DB->get_records_sql("$select $from $where ORDER by sp.scope ASC, itemname ASC ", $params)) {
            foreach($reviews as $review) {
                $items[$review->instance] = $review->itemname;
            }
        }
        return $items;
    }

    /**
     * Get from permissions table the category/departments the user can review courses in
     *
     * @param int $userid User id
     * @return array item instance ids
     */
    function supervision_get_reviewed_items($userid, $scope='category') {
        global $DB;
        $items = array();
        if($reviews = $DB->get_records('supervision_permissions', array('scope'=>$scope, 'userid'=>$userid, 'review'=>1), '', 'id, scope, instance')) {
            foreach($reviews as $review) {
                $items[$review->instance] = $review->instance;
            }
        }
        return $items;
    }




    /**
     * Get from permissions table the Course categories/departments the user can supervise warnings
     *
     * @param int $userid User id
     * @return array categories ids
     */
    function supervision_get_supervised_items($userid, $scope='category') {
        global $DB;
        $items = array();
        $select = " scope = :scope AND userid = :userid AND ".$DB->sql_isnotempty('supervision_permissions', 'warnings', true, false);
        if($reviews = $DB->get_records_select('supervision_permissions', $select, array('scope'=>$scope, 'userid'=>$userid), '', 'id, scope, instance')) {
            foreach($reviews as $review) {
                $items[$review->instance] = $review->instance;
            }
        }
        return $items;
    }

    /**
     * Get from permissions table the warning types a supervisor can see
     *
     * @param int $userid User id
     * @return array warnintypes
     */
    function supervision_supervisor_warningtypes($userid) {
        global $DB;
        $items = array();
        $select = " userid = :userid AND ".$DB->sql_isnotempty('supervision_permissions', 'warnings', true, false);
        if($reviews = $DB->get_records_select('supervision_permissions', $select, array('userid'=>$userid), '', 'id, scope, instance, warnings')) {
            foreach($reviews as $review) {
                $warnings = array();
                if($warningtypes = explode(',', $review->warnings)) {
                    foreach($warningtypes as $warning) {
                        if(get_config('supervisionwarning_'.$warning, 'enabled')) {
                            $warnings[] = $warning;
                        }
                    }
                }

                if($warnings && is_array($warnings)) {
                    $items = $items + $warnings;
                }
            }
            if($items = array_unique($items)) {
                natcasesort($items);
            }
        }
        return $items;
    }

    /**
     * Get from warnings table the warning types that have instances for a regular user
     *
     * @param int $userid User id
     * @return array warningtypes
     */
    function supervision_user_haswarnings($userid, $courseid=0, $fixed=false) {
        global $DB;
        $items = array();

        $params = array('user'=>$userid);

        $coursewhere = '';
        if($courseid) {
            $coursewhere = ' AND courseid = :course';
            $params['course'] = $courseid;
        }

        $fixedwhere = '';
        if(!$fixed) {
            $fixedwhere = ' AND timefixed = 0 ';
        } elseif($fixed === true) {
            $fixedwhere = ' AND timefixed != 0 ';
        }

        $sql = "SELECT warningtype
                    FROM {supervision_warnings}
                    WHERE userid = :user $coursewhere $fixedwhere  GROUP BY warningtype ORDER BY warningtype ASC";
        $items = $DB->get_records_sql($sql, $params);
        return array_keys($items);
    }

    /**
     * Checks is user is a en editing supervisor and return instances supervised
     *
     * @param int $userid User id
     * @return int userid of assigner
     */
    function supervision_can_add_supervisors($userid) {
        global $DB;
        $assigner = false;
        if(!$userid) {
            return false;
        }

        if($permissions = $DB->get_records('supervision_permissions', array('userid' => $userid, 'adduser' => 1), '', 'id, scope, instance')) {
            $permissions['user'] = $userid;
        }

        return $permissions;
    }


    /**
     * Takes a supervision permission object and perform role assignment in category/department
     *
     * @param object $data supervision_permissions data object
     * @param bool $delete if role assignment shoudl be deleted (unassigned)
     * @return bool success
     */
    function supervision_assign_supervisor($data, $delete = false, $role = false) {
        global $DB;

        $success = false;
        $items = array();
        $supervisor_role = $role ? $role :  get_config('local_supervision', 'checkerrole');

        if($DB->record_exists('role', array('id'=>$supervisor_role))) {
            if($data->scope == 'category') {
                $context = context_coursecat::instance($data->instance);
                $items[$data->instance] = $context->id;
            } elseif($data->scope == 'department') {
                // get all courses for that department and do again
                $sql = "SELECT c.id, c.shortname 
                              FROM {course} c   
                                JOIN  {local_ulpgccore_course} uc ON c.id = uc.courseid  
                            WHERE  uc.department = :unit  ";
                $params = ['unit' => $data->instance];
                if($courses = $DB->get_records_sql($sql, $params) ) {
                    foreach($courses as $course) {
                        $context = context_course::instance($course->id);
                        $items[$course->id] = $context->id;
                    }
                }
            }
            
            foreach($items as $item) {
                if($data->review AND !$delete) {
                    role_assign($supervisor_role, $data->userid, $item, 'local_supervision' );
                } elseif(!$data->review OR $delete)  {
                    role_unassign($supervisor_role, $data->userid, $item, 'local_supervision' );
                }
                $success = true;
            }
        }
        
        return $success;
    }


    /**
     * Look for name of some permission category/department instance
     *
     * @param object $permission object as in supervision_permissions table
     * @return string formatted name
     */
    function supervision_format_instancename($permission=null) {
        global $DB;
        if(!$permission) {
            return '';
        }
        if($permission->scope == 'category') {
            return $DB->get_field('course_categories', 'name', array('id'=>$permission->instance));
        }

        if($permission->scope == 'department') {
            return $DB->get_field('local_sinculpgc_units', 'name', array('id'=>$permission->instance));
        }

        return '';
    }

    
    /**
     * Called by subpages to set PAGE, context and navigation
     *
     * @param string $subpage name of page
     * @param moodle_url $url the base usrle for the page
     * @return system context class
     */
    function supervision_page_setup($subpage, $url, $title) {    
        global $PAGE;
        
        $context = context_system::instance();
        $PAGE->set_context($context);
        $PAGE->set_url($url);
        $PAGE->set_pagelayout('admin');
        $PAGE->set_title($title);
        $PAGE->set_heading($title);
        
        $PAGE->navbar->add(get_string('administrationsite'), '/admin/search.php');
        $PAGE->navbar->add(get_string('plugins', 'admin'));
        $PAGE->navbar->add(get_string('localplugins'));
        $PAGE->navbar->add(get_string('managewarningsettings', 'local_supervision'));
        $PAGE->navbar->add(get_string($subpage, 'local_supervision'), $url);
        
        return $context;
    }
    
    
    /**
     * Called by cron to load 'live' unfixed warnings and send supervision  emails to supervised users
     * Depends on each userid is a teacher fairly assigned (same group as student, enrolled etc.)
     *
     * @param int $timetocheck starting time for collection or warnings
     * @param int $lastexecution last time this routine was launched by cron
     * @return a moodle icon object
     */
    function supervision_warnings_mailing($config) {
        global $CFG, $DB, $USER;

        $timetocheck = time();

        /// last step: mail warnings if needed
        /// TODO count of failures for each course, user;  JOIN user and course???
        if(empty($config->enablemail)) {
                    return true;
        }
        $maillimit = $timetocheck;
        if(isset($config->maildelay)) { // introduces a delay before sending e-mails
            $maillimit = strtotime('-'.$config->maildelay.' day', $timetocheck);
        }

        if(empty($config->enablemail)) {
            mtrace('no pending stats mailing');
            return true;
        }
        
        $warnings = supervision_load_plugins(null, true);

        $userfieldsapi = \core_user\fields::for_name();
        $names1 = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $names2 = $userfieldsapi->get_sql('st', false, 'st', '', false)->selects;
        $sql = "SELECT sw.*, c.fullname, c.shortname, c.category,
                        u.idnumber, u.email, u.emailstop, u.mailformat, u.maildisplay, $names1,
                        st.idnumber, st.email, st.emailstop, st.mailformat, st.maildisplay, $names2
                    FROM {supervision_warnings} sw
                    JOIN {course} c ON sw.courseid = c.id
                    JOIN {user} u ON u.id = sw.userid
                    JOIN {user} st ON st.id = sw.studentid
                    WHERE (timefixed = 0)   AND (timemailed < :maillimit) ";
        $params = array('maillimit'=>$maillimit);
        if ($rs = $DB->get_recordset_sql($sql, $params)) {
            $supportuser = '';
            if(isset($config->email) && $config->email) {
                $supportuser = core_user::get_support_user();
                $supportuser->email = $config->email;
                $supportuser->mailformat = 1;
                $supportuser->id = 1;
            }
            
            // Prepare the message class.
            $msgdata = new \core\message\message();
            $msgdata->component         = 'local_supervision';
            $msgdata->name              = 'supervision_warning';
            $msgdata->notification      = 1;
            $msgdata->userfrom = core_user::get_noreply_user(); 
            $user = core_user::get_noreply_user();
            $student = core_user::get_noreply_user();
            
            $error = array();
            $sent = array();
            $success = array();
            $failure = array();

            $userfieldsapi = \core_user\fields::for_name();
            $names = $userfieldsapi->get_sql('', false, '', '', false)->selects;
            foreach ($rs as $stat) {
                if(!$stat->userid) {
                    // TODO debug measure, eliminate
                    // no user ID means we cannot send notification, this stat is a failure
                    if($supportuser) {
                        $error[] = $stat->id;
                    }
                    continue;
                }
            
                $info = new StdClass;
                $info->coursename = $stat->shortname.' - '.$stat->fullname;
                $info->reporturl = $CFG->wwwroot.'/report/supervision/index.php?id='.$stat->courseid.'&amp;warning='.$stat->warningtype;
                $info->courseurl = $CFG->wwwroot.'/course/view.php?id='.$stat->courseid;
                $info->activity = $stat->info;
                $info->itemlink = $CFG->wwwroot.$stat->url;
                $info->student = '';
                if($stat->studentid) {
                    //$st = $DB->get_record('user', array('id'=>$stat->studentid), 'id, username, idnumber, email, '.$names);
                    $student = username_load_fields_from_object($student, $stat, 'st', array('idnumber', 'email', 'mailformat', 'maildisplay'));
                    $student->fullname = fullname($student);
                    $info->student = get_string('emailstudent',  'local_supervision', $student);
                }

                $msgdata->courseid = $stat->courseid;
                $msgdata->subject = get_string('warningmailsubject', 'local_supervision', $stat->shortname);
                $msgdata->fullmessage = get_string('warningemailtxt', 'local_supervision', $info );
                $msgdata->fullmessagehtml = get_string('warningemailhtml', 'local_supervision', $info);
                $msgdata->smallmessage = get_string('warningsmalltxt', 'local_supervision', $stat->shortname);
                $msgdata->contexturl = $stat->url;
                $msgdata->contexturlname = $stat->info;
                $msgdata->fullmessageformat = FORMAT_HTML;
                
                $user = username_load_fields_from_object($user, $stat, null, array('idnumber', 'email', 'mailformat', 'maildisplay'));
                $user->id = $stat->userid;
                $user->emailstop = 0;
                
                $msgdata->userto = $user;
                
                $warning = \local_supervision\warning::toclass($stat);
                
                if(message_send($msgdata)) {
                    if(!isset($sent[$stat->courseid][$stat->warningtype][$stat->userid])) {
                        $name = new StdClass();
                        $name->name = fullname($user, false, 'lastname');
                        $name->coursename = $stat->shortname;
                        $name->num = 1;
                        $sent[$stat->courseid][$stat->warningtype][$stat->userid] = $name;
                    } else {
                        $sent[$stat->courseid][$stat->warningtype][$stat->userid]->num++;
                    }
                    $success[] = $stat->id;
                    if($supportuser && $config->maildebug) {
                        $msgdata->userto = $supportuser;
                        message_send($msgdata);
                    }
                } elseif($supportuser) {
                    $failure[] = $stat->id;
                }
            }
            $rs->close();
            
            if($success) {
                // set record  as sent
                list($insql, $params) = $DB->get_in_or_equal($success); 
                $DB->set_field_select('supervision_warnings', 'timemailed', $timetocheck, "id $insql", $params);
                mtrace('   .... sent '.count($success).' notifications');
            }
            unset($success);
            
            if($sent && !empty($config->coordemail)) {
                $contentlist = array();
                $coordslist = array();
                $stat = new stdclass();
                foreach($sent as $courseid => $items) {
                    $stat->courseid = $courseid;
                    foreach($items as $type => $item) {
                        $stat->warningtype = $type;
                        $warning = \local_supervision\warning::toclass($stat);
                        $coords = $warning->get_supervisors();
                        if($coords) {
                            foreach($coords as $coorduser) {
                                if(!isset($coordslist[$coorduser->id])) {
                                    $coordslist[$coorduser->id] = $coorduser;
                                }
                                foreach($item as $userid => $count) {
                                    $contentlist[$coorduser->id][$count->name][] = get_string('countwarnings', 'supervisionwarning_'.$type, $count); 
                                }
                            }
                        }
                    }
                }
                unset($sent);
                if($coordslist) {
                    mtrace('    sending digest to coordinators '); 
                    $msgdata->courseid = 1;
                    $msgdata->subject = get_string('warningdigestsubject',  'local_supervision');
                    $msgdata->smallmessage = '';
                    foreach($coordslist as $coorduser) {
                        $msgdata->userto = $coorduser;
                        foreach($contentlist[$coorduser->id] as $name => $list) {
                            $contentlist[$coorduser->id][$name] =  $name. ': '.implode('; ', $list);
                        }
                        natcasesort($contentlist[$coorduser->id]);
                        if($contentlist[$coorduser->id]) {
                            $text = get_string('warningdigesttxt',  'local_supervision');
                            $msgdata->fullmessage = $text."\n".implode(" \n",$contentlist[$coorduser->id]);
                            $msgdata->fullmessagehtml = "<p>$text</p>".html_writer::alist($contentlist[$coorduser->id]);
                            message_send($msgdata);
                            mtrace('    ... sent digest to coordinator '.fullname($coorduser)); 
                        }
                    }
                    unset($contentlist);
                    unset($coordlist);
                }
            }
            
            if($supportuser) {
                $msgdata->userto = $supportuser;
                $msgdata->fullmessageformat = FORMAT_PLAIN;
                foreach(array('error', 'failure') as $type) {
                    if($$type) {
                        $msgdata->subject = get_string($type.'subject',  'local_supervision');
                        $msgdata->fullmessage = implode(',', $$type);
                        $msgdata->fullmessagehtml = $msgdata->fullmessage;
                        message_send($msgdata);
                        $unset($$type);
                    }
                }
            }
            
        }
    }


    /**
     * Look in table local_sinculpgc_units to 
     *
     * @param string $unittype ulpgc unit type of unit either centro, instituto, departamento, degree
     * @return void
     */
    function supervision_ulpgcunits_update_supervisors($unittype, $role, $withsecretary = false, $usecorecats = false) {
        global $DB;

        $plugins = get_plugin_list('supervisionwarning');
        $warnings = implode(',', array_keys($plugins));

        $ulpgc_supervisors = array();
        $scope = 'category';
        if($unittype == SINCULPGC_UNITS_DEPARTMENT) {
            $scope = 'department';
        } else {
            $scope = 'category';
        }
        $ulpgc_supervisors = supervision_ulpgcunits_get_supervisors($unittype, $withsecretary, $usecorecats);

        //print_object($ulpgc_supervisors);
        //print_object("  ------  ulpgc_supervisors --");
        /*
        if($unittype == SINCULPGC_UNITS_DEGREE) {
        mtrace(" sinculpgc assigning supervisor roles at ".var_dump($ulpgc_supervisors));
        mtrace("   ------  ulpgc_supervisors -- ");
        }
        */
/*        
        /// first delete supervisors found in moodle but NOT in ULPGC
        if($permissions = $DB->get_records('supervision_permissions', array('scope'=>$scope, 'assigner'=>0))) {
            foreach($permissions as $per) {
                if(!(isset($ulpgc_supervisors[$per->userid])) OR
                                        (isset($ulpgc_supervisors[$per->userid]) AND  !in_array($per->userid, $ulpgc_supervisors[$per->userid]))) {
                    // Doesn't exist more on ULPGC, should be deleted
                    $per->review = 0;
                    //supervision_assign_supervisor($per, true, $role);
                    //$DB->delete_records('supervision_permissions', array('id'=>$per->id));
                }
            }
        }
*/
       $now = time();

       $added = 0;
       $updated = 0;
       foreach($ulpgc_supervisors as $userid => $instances) {
       /*
            if($unittype == SINCULPGC_UNITS_DEGREE) {
            mtrace(" processing  supervisor  user $userid  instance ".var_dump($instances));
            mtrace("   ------  ulpgc_supervisors DEGREE -- ");
            }
       */
       
            foreach($instances as $instance) {
                if($permission = $DB->get_record('supervision_permissions', array('userid'=>$userid, 'scope'=>$scope, 'instance'=>$instance, 'assigner'=>0))) {
                    // record exists , ensure we have all permissions
                    $permission->review = 1;
                    $permission->warnings = $warnings;
                    $permission->adduser = 1;
                    $permission->timemodified = $now;
                    $DB->update_record('supervision_permissions', $permission);
                    $updated++;
                } else {
                    // records doesn't exists: add
                    $permission = new \StdClass;
                    $permission->userid = $userid;
                    $permission->scope = $scope;
                    $permission->instance = $instance;
                    $permission->review = 1;
                    $permission->warnings = $warnings;
                    $permission->assigner = 0;
                    $permission->source = 'sinculpgc_'.$unittype;
                    $permission->adduser = 1;
                    $permission->timemodified = $now;
                    if($permission->id = $DB->insert_record('supervision_permissions', $permission)) {
                        $added++;
                    }
                    
/*
                                if($unittype == SINCULPGC_UNITS_DEGREE) {
                                mtrace(" INSERTING  supervisor  user $userid  instance $instance  with ID= ".$permission->id);
                                mtrace("   ------  ulpgc_supervisors DEGREE -- ");
                                }
*/
                    
                }
                supervision_assign_supervisor($permission, false, $role);
            }
        }
        $num = count($ulpgc_supervisors);
        mtrace("        ...  Processed  $num supervisors, $added added and $updated  updated, for sinculpgc unit of type $unittype");
        
        

    }

    function supervision_ulpgcunits_get_supervisors($type, $withsecretary = false, $usecorecats = false) {
        global $DB;
        
        
        $ulpgc_supervisors = [];
        
        $sql = "SELECT su.id, su.type, su.idnumber, ud.id AS directorid, us.id AS secretaryid, uc.id as coordid
                      FROM {local_sinculpgc_units} su
               LEFT JOIN {user} ud ON ud.idnumber = su.director
               LEFT JOIN {user} us ON us.idnumber = su.secretary
               LEFT JOIN {user} uc ON uc.idnumber = su.coord
                   WHERE su.type = :type 
                   GROUP BY su.id ";
        $params = ['type' => $type];
                
        if($units = $DB->get_records_sql($sql, $params) ) {
            foreach($units as $unit) {
                  //mtrace(" sinculpgc assigning supervisor roles at ".var_dump($unit));
                if($type == SINCULPGC_UNITS_DEGREE) {
                    //mtrace(" get_supervisors for type DEGREE ".var_dump($unit));
                    if($unit->coordid) {
                        if(!isset($ulpgc_supervisors[$unit->coordid])) {
                            $ulpgc_supervisors[$unit->coordid] = [$unit->idnumber];
                        } else {
                            $ulpgc_supervisors[$unit->coordid][] = $unit->idnumber;
                        }
                    }
                } else {
                    if($unit->directorid) {
                        if(!isset($ulpgc_supervisors[$unit->directorid])) {
                            $ulpgc_supervisors[$unit->directorid] = [$unit->idnumber];
                        } else {
                            $ulpgc_supervisors[$unit->directorid][] = $unit->idnumber;
                        }
                    }
                    if($withsecretary && $unit->secretaryid) {
                        if(!isset($ulpgc_supervisors[$unit->secretaryid])) {
                            $ulpgc_supervisors[$unit->secretaryid] = [$unit->idnumber];
                        } else {
                            $ulpgc_supervisors[$unit->secretaryid][] = $unit->idnumber;
                        }
                    }
                }
            }
        }
        
/*
        if($type == SINCULPGC_UNITS_DEGREE) {
            //mtrace(" get_supervisors DEGREE ".var_dump($ulpgc_supervisors));
            //mtrace(" unitype  $type");
        }
*/        
        
        if($type == SINCULPGC_UNITS_DEPARTMENT) {
            // we return here, departmets are searched by course 
            return $ulpgc_supervisors;
        }
        
        // we are dealing wiht a category level supervision
        // just load corresponding category  
        // centro/instituto by plain category idnumber
        // degree by ccc_tttt_00_00 pattern
        $results  = [];
        foreach($ulpgc_supervisors as $userid => $units) {
            $results[$userid] = [];
            foreach($units as $unit) {
                $cats = [];
                if($type == SINCULPGC_UNITS_DEGREE) {
                    if($usecorecats) {
                        $cats = $DB->get_records('local_ulpgccore_categories', ['degree'=>$unit], '', 'categoryid, degree');
                    } else {
                        $select = $DB->sql_like('idnumber', ':unit'); 
                        $params = ['unit' => "%\_{$unit}\_%"];
                        
                         //mtrace(" SQL select =  $select ");
                         //mtrace(" SQL params unit =  ".$params['unit']);
                        
                        $cats = $DB->get_records_select('course_categories', $select, $params, '', 'id, idnumber');
                        //mtrace(" SQL found cats for  $unit  = ".var_dump($cats));
                        
                    }
                } elseif($type == SINCULPGC_UNITS_CENTRE  || $type == SINCULPGC_UNITS_INSTITUTE) {
                    if($usecorecats) {
                        $cats = $DB->get_records('local_ulpgccore_categories', ['faculty'=>$unit], '', 'categoryid, faculty');
                    } else {
                        $cats = $DB->get_records('course_categories', ['idnumber'=>$unit], '', 'id, idnumber');
                    }
                }
                if($cats) {
                    $results[$userid] = $results[$userid] + array_keys($cats);
                    //mtrace("Adding to user  $userid  on unit $unit ");
                    //mtrace("A  $unit  results[userid] =   ".var_dump($results[$userid]));
                    //mtrace("Adding to user  $userid  on unit $unit ");
                }
            }
        }

        /*
        if($type == SINCULPGC_UNITS_DEGREE) {
            foreach($results as $userid => $cats) {
        
                //mtrace(" get_supervisors with categories   userid =  $userid   ".var_dump( $cats));
            }
            //mtrace(" unitype  $type");
            
        }
        */

        return $results;
    }


    /**
     * Look in table local_sinculpgc_units to 
     *
     * @param string $unittype ulpgc unit type of unit either centro, instituto, departamento, degree
     * @return void
     */
    function supervision_ulpgcunits_remove_supervisors($unittypes, $role, $withsecretary = false, $usecorecats = false) {
        global $DB;
        
        //remove supervisors in unittypes not synced any more
        list($insql, $params) = $DB->get_in_or_equal($unittypes, SQL_PARAMS_NAMED, 'type', false); 
        // add prefix to param value
        foreach($params as $key => $value) {
            $params[$key] = 'sinculpgc_'.$value;
        }
        
        $select = "assigner = 0 AND source $insql";
        if($permissions = $DB->get_records_select('supervision_permissions', $select, $params)) {        
            // remove role assignments in categories/courses
            foreach($permissions as $per) {
                    $per->review = 0;
                    supervision_assign_supervisor($per, true, $role);
            }
            // now remove supervisor rows in supervision table
            $DB->delete_records_select('supervision_permissions', $select, $params);
            
            $num = count($permissions);
            mtrace("    ...  removed  $num permisions of ulpgcunit types no longer synced");
        }
        
        // remove users that are no longer associated to a sinculpgc unit
        foreach($unittypes as $unittype) {
            mtrace("    ...  Checking removal permisions for users no longer in ulpgcunits of type $unittype");
            $wheresecretary = '';
            if($withsecretary) {
                $wheresecretary = 'AND (u.idnumber != su.secretary)';
            }
            
            $whereuser = " AND ( (u.idnumber != su.director)  $wheresecretary ) " ;
            
            $params = ['type' => $unittype, 
                               'sourcetype' => 'sinculpgc_'.$unittype];
            
            if($unittype == SINCULPGC_UNITS_DEPARTMENT) {
                $params['scope'] = 'department';
                $instancejoin = '';
                $unitwherejoin = " su.idnumber = p.instance ";                 
                /*
                $sql = "SELECT 
                              FROM {supervision_permissions} p 
                               JOIN  {user} u ON uíd = p.userid
                               JOIN {local_sinculpgc_units} su ON su.type = :type  AND su.idnumber = p.instance
                           WHERE p.assigner = 0 AND p.source = :sourcetype AND p.scope = 'department' 
                                        AND ( (u.idnumber != su.director)  $wheresecretary )  ";  */
            }
            
            if($unittype == SINCULPGC_UNITS_CENTRE || $unittype == SINCULPGC_UNITS_INSTITUTE) {
                $params['scope'] = 'category';
                if($usecorecats) {
                    $instancejoin = "JOIN {local_ulpgccore_categories} ucc ON ucc.categoryid = p.instance ";
                    $unitwherejoin =  ' su.idnumber = ucc.faculty';
                } else {
                    $instancejoin = "JOIN {course_categories} cc ON cc.id = p.instance ";
                    $unitwherejoin =  " su.idnumber = cc.idnumber ";
                }                 /*
                                JOIN {local_ulpgccore_categories} ucc ON ucc.categoryid = p.instance
                                JOIN {local_sinculpgc_units} su ON su.type = :type  AND su.idnumber = ucc.faculty
                $sql = "SELECT 
                              FROM {supervision_permissions} p 
                               JOIN {user} u ON uíd = p.userid
                               JOIN {course_categories} cc ON cc.id = p.instance 
                               JOIN {local_sinculpgc_units} su ON su.type = :type  AND su.idnumber = cc.idnumber
                            WHERE p.assigner = 0 AND p.source = :sourcetype AND p.scope = 'category' 
                                        AND ( (u.idnumber != su.director)  $wheresecretary ) "; */
            }
            
            if($unittype == SINCULPGC_UNITS_DEGREE) {
                $params['scope'] = 'category';
                $whereuser = ' AND (u.idnumber != su.coord) ';
                if($usecorecats) {
                    $instancejoin = "JOIN {local_ulpgccore_categories} ucc ON ucc.categoryid = p.instance ";
                    $unitwherejoin =  ' su.idnumber = ucc.degree';
                } else {
                    $instancejoin = "JOIN {course_categories} cc ON cc.id = p.instance ";
                    $unitwherejoin =  "cc.idnumber  LIKE CONCAT('%\_', su.idnumber, '\_%') ";
                }              /*
                                JOIN {local_ulpgccore_categories} ucc ON ucc.categoryid = p.instance
                                JOIN {local_sinculpgc_units} su ON su.type = :type  AND su.idnumber = ucc.degree
                $sql = "SELECT 
                              FROM {supervision_permissions} p 
                               JOIN {user} u ON uíd = p.userid
                               JOIN {course_categories} cc ON cc.id = p.instance 
                               JOIN {local_sinculpgc_units} su ON su.type = :type  AND cc.idnumber  LIKE su.idnumber          
                            WHERE p.assigner = 0 AND p.source = :sourcetype AND p.scope = 'category' 
                                        AND ( (u.idnumber != su.director)  $wheresecretary )";  */
            }
        
        
            $sql = "SELECT p.* 
                            FROM {supervision_permissions} p 
                            JOIN {user} u ON u.id = p.userid
                            $instancejoin
                            JOIN {local_sinculpgc_units} su ON su.type = :type  AND $unitwherejoin
                        WHERE p.assigner = 0 AND p.source = :sourcetype AND p.scope = :scope 
                                    $whereuser ";
/*        
            if($unittype == SINCULPGC_UNITS_DEGREE) {
                mtrace("$sql"); 
                mtrace("params".var_dump($params)); 
            }
   */     
        
            if($permissions = $DB->get_records_sql($sql, $params)) {        
                // remove role assignments in categories/courses
                foreach($permissions as $per) {
                        $per->review = 0;
                        supervision_assign_supervisor($per, true, $role);
                }
                $DB->delete_records_list('supervision_permissions', 'id', array_keys($permissions));
                
                $num = count($permissions);
                mtrace("        ...  removed  $num permisions for users no longer in ulpgcunits of type $unittype");
            }
        }
    }
