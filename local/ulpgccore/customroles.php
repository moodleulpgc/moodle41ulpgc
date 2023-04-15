<?php
/**
 * This file contains a local_ulpgccore page to manage & store custom roles
 *
 * @package   local_ulpgccore
 * @copyright 2023 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once($CFG->libdir.'/adminlib.php');
    
    $action =  optional_param('action', '', PARAM_ALPHA); 
    $preset =  optional_param('preset', '', PARAM_FILE); 
    $roleid =  optional_param('role', 0, PARAM_INT); 
    
    $baseparams = [];

    $baseurl = new moodle_url('/local/ulpgccore/customroles.php', $baseparams);

    admin_externalpage_setup('local_ulpgccore_customroles', '', null, $baseurl);
       
    $context = context_system::instance(); 
    $PAGE->set_context($context);
    require_capability('local/ulpgccore:manage', $context);

    $archetypes = get_role_archetypes();
    
    ////// actions 
    $presets = [];
    if(($action == 'import')) {
        if(!empty($preset)) {
            $presets = [$preset];
        } elseif($presets = optional_param('presets', '', PARAM_TAGLIST)) {
            $presets = explode(',', $presets);
        }

        foreach($presets as $preset) {
            $error = false;
            $xml = file_get_contents($CFG->dirroot.'/local/ulpgccore/presets/roles/'.$preset.'.xml');
            $info = core_role_preset::parse_preset($xml);
            if(empty($info['archetype'])  || empty($info['shortname']) || empty($info['name'])) {
                \core\notification::error(get_string('preseterrorxml', 'local_ulpgccore'));
                $error = true;
            }
            if(!in_array($info['archetype'], $archetypes)) {
                \core\notification::error(get_string('rolepreseterrorarchetype', 'local_ulpgccore'));
                $error = true;
            }

            if(!$error) {
                $options = array(
                        'shortname'     => 1,
                        'name'          => 1,
                        'description'   => 1,
                        'permissions'   => 1,
                        'archetype'     => 1,
                        'contextlevels' => 1,
                        'allowassign'   => 1,
                        'allowoverride' => 1,
                        'allowswitch'   => 1,
                        'allowview'   => 1);

                $definitiontable = new local_ulpgccore\import_permissions_table($context, 0);
                $definitiontable->force_archetype($info['archetype'], $options);
                $definitiontable->force_preset($xml, $options);

                //sesskey() confirm_sesskey()
                // Process submission in necessary.
                if ($definitiontable->is_submission_valid()) {
                    $definitiontable->preset = $preset;
                    $definitiontable->save_changes();
                    \core\notification::success(get_string('presetloaded', 'local_ulpgccore',$preset));
                } else {
                    \core\notification::success(get_string('presetloaderror', 'local_ulpgccore',$preset));
                }
            }
        }

    }
    
    if(($action == 'export') && !empty($roleid)) {
        if(!$preset) {
            $role = $DB->get_record('role', array('id'=>$roleid), '*', MUST_EXIST);
            if ($role->shortname) {
                $preset = $role->shortname;
            } else {
                $preset = 'role_'.$roleid;      
            }
        }
        $xml = core_role_preset::get_export_xml($roleid);
        $filename = $CFG->dirroot.'/local/ulpgccore/presets/roles/'.$preset.'.xml';
        if(file_put_contents($filename, $xml)) {
            \core\notification::success(get_string('presetsaved', 'local_ulpgccore',$preset));
        } else {
            \core\notification::success(get_string('presetsaveerror', 'local_ulpgccore',$preset));
        }
    }
    
    //////// end actions
    
    // Get some basic data we are going to need.
    $roles = role_fix_names(get_all_roles(), $context, ROLENAME_ORIGINAL);
    $roleshortnames = [];
    foreach($roles as $role) {
        $roleshortnames[$role->id] = $role->shortname;
    }
    $rolescount = count($roles);
    $loadroles = [];

    $presetfiles = glob($CFG->dirroot.'/local/ulpgccore/presets/roles/*.xml');
    $presetroles = explode(',', get_config('local_ulpgccore', 'rolepresets'));
    if(is_array($presetroles)) {
        $presetroles = array_map('trim', $presetroles);
    }

    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string('presetrolestable', 'local_ulpgccore'));            
    if($presetfiles) {
        
        $table = new html_table();
        $table->width = "90%";
        $table->head = [get_string('presetname', 'local_ulpgccore'),
                                    get_string('roleshortname', 'core_role'),
                                    get_string('rolefullname', 'core_role'),
                                    get_string('archetype', 'core_role'),
                                    get_string('presetdateload', 'local_ulpgccore'),
                                    get_string('presetdatechanged', 'local_ulpgccore'),
                                    get_string('actions'),
                                ];
        $table->align = array('left', 'left', 'left', 'left', 'left', 'center', 'center', 'center');
        //$table->size = array ("15%", "*", "35%", "*", "*", "*", "*");
        
        $actionurl = new moodle_url('/local/ulpgccore/customroles.php', []);
        $roleurl  = new moodle_url('/admin/roles/define.php', ['action' => 'view']);
        foreach($presetfiles as $presetlong) {
            $row = [];
            $xml = file_get_contents($presetlong);
            $info = core_role_preset::parse_preset($xml);
            
            $preset =  basename($presetlong, ".xml"); 
            $row[] = $preset;

            $roleid = array_search($info['shortname'], $roleshortnames);
            $shortname = $info['shortname'];
            $name = $info['name'];
            $archetype = $info['archetype'];
            $aid = array_search($archetype, $roleshortnames);
            $roleurl->param('roleid', $aid);
            $archetype = html_writer::link($roleurl, $archetype);

            if($roleid) {
                $role = $roles[$roleid];
                unset($roles[$roleid]);
                $roleurl->param('roleid', $roleid);
                $shortname = html_writer::link($roleurl, $shortname);

                if($name != $role->name) {
                    $name .= '<br />' . $role->name;
                }

                $params = ['eventname' => '\\local_ulpgccore\\event\\role_imported',  'objecttable' => 'role', 'objectid' => $roleid];
                if($created = $DB->get_records('logstore_standard_log', $params, 'timecreated DESC', '*', 0, 1)  ) {
                    $created = reset($created);
                }

                unset($params['eventname']);
                if($mod = $DB->get_records('logstore_standard_log', $params, 'timecreated DESC', '*', 0, 1)) {
                    $mod = reset($mod);
                }

                if($modifiedcaps = $DB->get_records('role_capabilities', ['roleid' => $roleid], 'timemodified DESC', '*', 0, 1)) {
                    $modifiedcaps = reset($modifiedcaps);
                }

                $dateloaded = userdate($created->timecreated);
                $datechanged = userdate(max($modifiedcaps->timemodified, $mod->timecreated));
            } else {
                $dateloaded = '';
                $datechanged = '';
            }

            if(in_array($info['shortname'], $presetroles)) {
                $shortname .= '<br />' . get_string('required');
                if(!$roleid) {
                    $loadroles[] = $preset;
                }
            }

            $actions = [];    
            $params = ['preset' => $preset];            
            // reset / import
            $params['action'] = $roleid ? 'reset' : 'import';
            if($roleid) {
                $params['role'] = $roleid;
            }
            $url = new moodle_url($actionurl, $params);
            $confirmaction = new \confirm_action(get_string('confirmpresetimport', 'local_ulpgccore', $preset));
            $icon = new pix_icon('i/import', get_string('preset'.$params['action'], 'local_ulpgccore'));
            $actions[] = $OUTPUT->action_icon($url, $icon, $confirmaction);            
            
            // export
            if($roleid) {
                $params['action'] = 'export';    
                $url = new moodle_url($actionurl, $params);
                $confirmaction = new \confirm_action(get_string('confirmpresetexport', 'local_ulpgccore', $preset));
                $icon = new pix_icon('i/export', get_string('preset'.$params['action'], 'local_ulpgccore'));
                $actions[] = $OUTPUT->action_icon($url, $icon, $confirmaction);            
            }

            $actions =  implode(' &nbsp; ', $actions);
            
            $table->data[] = [$preset, $shortname, $name, $archetype, $dateloaded, $datechanged, $actions];
        }
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->box(get_string('nothingtodisplay'), 'generalbox nothingtodisplay');
    }
    

    if($loadroles) {
        $actionurl->param('action', 'import');
        $actionurl->param('presets', implode(',',$loadroles));
        $button = $OUTPUT->single_button($actionurl, get_string('presetsinstall', 'local_ulpgccore'));
        echo $OUTPUT->box($button, 'installpresets');
    }


    // other roles, those not archetypes of listed above
    $otherroles = [];
    foreach($roles as $rid => $role) {
        if(!in_array($role->shortname,   $archetypes)  && ($role->shortname != $role->archetype)) {
            $otherroles[$rid] = $role;
        }
    }

    if($otherroles) {
        echo $OUTPUT->heading(get_string('otherrolestable', 'local_ulpgccore'));    
        
        $table = new html_table();
        $table->width = "70%";
        $table->head = [get_string('roleshortname', 'core_role'),
                                    get_string('rolefullname', 'core_role'),
                                    get_string('archetype', 'core_role'),
                                    get_string('actions'),
                                ];
        $table->align = array('left', 'left', 'left',  'center');
        //$table->size = array ("15%", "*", "35%", "*", "*", "*", "*");
        
        $actionurl = new moodle_url('/local/ulpgccore/customroles.php', []);
        foreach($otherroles as $role) {        
            $row = [];

            $roleurl->param('roleid', $role->id);
            $row[] = html_writer::link($roleurl, $role->shortname);

            $row[] = $role->name;

            $aid = array_search($role->archetype, $roleshortnames);
            $roleurl->param('roleid', $aid);
            $row[] = html_writer::link($roleurl, $role->archetype);
            
            $actions = [];
            $preset = $role->shortname;
            $params = ['action' => 'export', 'role' =>$role->id, 'preset' => $preset];    
            $url = new moodle_url($actionurl, $params);
            $confirmaction = new \confirm_action(get_string('confirmpresetexport', 'local_ulpgccore', $preset));
            $icon = new pix_icon('i/export', get_string('preset'.$params['action'], 'local_ulpgccore'));
            $actions[] = $OUTPUT->action_icon($url, $icon, $confirmaction);            
            
            
            
            $row[] =  implode(' &nbsp; ', $actions);            
            
            $table->data[] = $row;
        }
        echo html_writer::table($table);
        
    }
    
    $returnurl = new moodle_url('/admin/search.php#linkmodules');
    echo $OUTPUT->continue_button($returnurl);
    
    echo $OUTPUT->footer();
