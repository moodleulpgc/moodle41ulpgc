<?php
/**
 * This file contains a local_ulpgccore page to manage & store default profilefields
 *
 * @package   local_ulpgccore
 * @copyright 2023 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once($CFG->libdir.'/adminlib.php');
    
    $action =  optional_param('action', '', PARAM_ALPHA); 
    $preset =  optional_param('preset', '', PARAM_FILE); 
    
    $baseparams = [];

    $baseurl = new moodle_url('/local/ulpgccore/profilefieldpresets.php', $baseparams);

    admin_externalpage_setup('local_ulpgccore_profilefieldpresets', '', null, $baseurl);
       
    $context = context_system::instance(); 
    $PAGE->set_context($context);
    require_capability('local/ulpgccore:manage', $context);


    ////// actions 
    if(($action == 'import') && !empty($preset)) {
        $error = false;
        $xml = file_get_contents($CFG->dirroot.'/local/ulpgccore/presets/profilefields/'.$preset.'.xml');
        $info = core_role_preset::parse_preset($xml);
        if(empty($info['archetype'])  || empty($info['shortname']) || empty($info['name'])) {
            \core\notification::error(get_string('rolepreseterrorxml', 'local_ulpgccore'));
            $error = true;
        }
        if(!in_array($info['archetype'], $archetypes)) {
            \core\notification::error(get_string('rolepreseterrorarchetype', 'local_ulpgccore'));
            $error = true;
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
        $filename = $CFG->dirroot.'/local/ulpgccore/presets/profilefields/'.$preset.'.xml';
        if(file_put_contents($filename, $xml)) {
            \core\notification::success(get_string('rolepresetsaved', 'local_ulpgccore',$preset));
        } else {
            \core\notification::success(get_string('profilefieldsaveerror', 'local_ulpgccore',$preset));
        }
    }
    
    //////// end actions
    
    // Get some basic data we are going to need.
    $profilefields = $DB->get_records('profilefield_instances', ['parentcontextid' => 1, 'requiredbytheme' => 0]);
    $profilefieldshortnames = [];
    foreach($profilefields as $profilefield) {
        $profilefieldshortnames[$role->id] = $role->shortname;
    }
    $profilefieldscount = count($profilefields);
    
    echo $OUTPUT->header();

    $presetfiles = glob($CFG->dirroot.'/local/ulpgccore/presets/profilefields/*.xml');

    echo $OUTPUT->heading(get_string('presetprofilefieldstable', 'local_ulpgccore'));
    if($presetfiles) {
        
        $table = new html_table();
        $table->width = "90%";
        $table->head = [get_string('presetname', 'local_ulpgccore'),
                                    get_string('profilefieldshortname', 'core_role'),
                                    get_string('pagetypepattern', 'core_role'),
                                    get_string('defaultregion', 'core_role'),
                                    get_string('roledateload', 'local_ulpgccore'),
                                    get_string('roledatechanged', 'local_ulpgccore'),
                                    get_string('actions'),
                                ];
        $table->align = array('left', 'left', 'left', 'left', 'left', 'center', 'center', 'center');
        //$table->size = array ("15%", "*", "35%", "*", "*", "*", "*");
        
        $actionurl = new moodle_url('/local/ulpgccore/profilefieldpresets.php', []);
        foreach($presetfiles as $presetlong) {
            $row = [];
            $xml = file_get_contents($presetlong);
            $info = core_role_preset::parse_preset($xml);
            
            $preset =  basename($presetlong, ".xml"); 
            $row[] = $preset;
            $row[] = $info['shortname'];
            $row[] = $info['name'];
            $row[] = $info['archetype'];
            if($roleid = array_search($info['shortname'], $profilefieldshortnames)) {
                unset($profilefields[$roleid]);
                $params = ['eventname' => '\local_ulpgccore\event\role_imported',  'objecttable' => 'role', 'objectid' => $roleid];
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
                
                $row[] = userdate($created->timecreated);
                $row[] = userdate(max($modifiedcaps->timemodified, $mod->timecreated));
            } else {
                $row[] = '';
                $row[] = '';
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
            $icon = new pix_icon('i/import', get_string('rolepreset'.$params['action'], 'local_ulpgccore'));
            $actions[] = $OUTPUT->action_icon($url, $icon, $confirmaction);            
            
            // export
            if($roleid) {
                $params['action'] = 'export';    
                $url = new moodle_url($actionurl, $params);
                $confirmaction = new \confirm_action(get_string('confirmpresetexport', 'local_ulpgccore', $preset));
                $icon = new pix_icon('i/export', get_string('rolepreset'.$params['action'], 'local_ulpgccore'));
                $actions[] = $OUTPUT->action_icon($url, $icon, $confirmaction);            
            }

            $row[] =  implode(' &nbsp; ', $actions);            
            
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->box(get_string('nothingtodisplay'), 'generalbox nothingtodisplay');
    }
    

    // other profilefields, those not archetypes of listed above
    $otherprofilefields = [];
    foreach($profilefields as $bid => $profilefield) {
        if(!in_array($role->shortname,   $archetypes)  && ($role->shortname != $role->archetype)) {
            $otherprofilefields[$rid] = $role;
        }
    }

    if($otherprofilefields) {
        echo $OUTPUT->heading(get_string('otherprofilefieldstable', 'local_ulpgccore'));
        
        $table = new html_table();
        $table->width = "70%";
        $table->head = [get_string('profilefieldshortname', 'core_role'),
                                    get_string('rolefullname', 'core_role'),
                                    get_string('archetype', 'core_role'),
                                    get_string('actions'),
                                ];
        $table->align = array('left', 'left', 'left',  'center');
        //$table->size = array ("15%", "*", "35%", "*", "*", "*", "*");
        
        $actionurl = new moodle_url('/local/ulpgccore/customprofilefields.php', []);
        foreach($otherprofilefields as $role) {
            $row = [];

            $row[] = $role->shortname;
            $row[] = $role->name;
            $row[] = $role->archetype;            
            
            $actions = [];
            $prset = $role->shortname;
            $params = ['action' => 'export', 'role' =>$role->id, 'preset' => $preset];    
            $url = new moodle_url($actionurl, $params);
            $confirmaction = new \confirm_action(get_string('confirmpresetexport', 'local_ulpgccore', $preset));
            $icon = new pix_icon('i/export', get_string('rolepreset'.$params['action'], 'local_ulpgccore'));
            $actions[] = $OUTPUT->action_icon($url, $icon, $confirmaction);            
            
            
            
            $row[] =  implode(' &nbsp; ', $actions);            
            
            $table->data[] = $row;
        }
        echo html_writer::table($table);
        
    }
    
    $returnurl = new moodle_url('/admin/search.php#linkmodules');
    echo $OUTPUT->continue_button($returnurl);
    
    echo $OUTPUT->footer();
