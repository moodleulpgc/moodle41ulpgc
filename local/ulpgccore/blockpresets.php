<?php
/**
 * This file contains a local_ulpgccore page to manage & store default blocks
 *
 * @package   local_ulpgccore
 * @copyright 2023 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->dirroot.'/local/ulpgccore/presetlib.php');
    require_once($CFG->dirroot.'/my/lib.php');
    
    $action =  optional_param('action', '', PARAM_ALPHA); 
    $preset =  optional_param('preset', '', PARAM_FILE);
    $blockid =  optional_param('block', 0, PARAM_INT);
    
    $baseparams = [];

    $baseurl = new moodle_url('/local/ulpgccore/blockpresets.php', $baseparams);

    admin_externalpage_setup('local_ulpgccore_blockpresets', '', null, $baseurl);
       
    $context = context_system::instance(); 
    $PAGE->set_context($context);
    require_capability('local/ulpgccore:manage', $context);

    ////// actions 
    $presets = [];
    if(($action == 'del') && !empty($preset)) {
        $filename = $CFG->dirroot.'/local/ulpgccore/presets/blocks/'.$preset.'.xml';
        if(unlink($filename)) {
            core\notification::success(get_string('presetremoved', 'local_ulpgccore', $preset));  
        } else {
            core\notification::error(get_string('presetremoveerror', 'local_ulpgccore', $preset));  
        }
        
    }
    if(($action == 'import') && !empty($preset)) {
        if(!empty($preset)) {
            $presets = [$preset];
        } elseif($presets = optional_param('presets', '', PARAM_TAGLIST)) {
            $presets = explode(',', $presets);
        }

        foreach($presets as $preset) {
            $error = false;
            $info = local_ulpgccore_import_xml_preset($CFG->dirroot.'/local/ulpgccore/presets/blocks/'.$preset.'.xml');
            if(empty($info['blockname']) || empty($info['pagetypepattern']) || empty($info['defaultregion'])) {
                \core\notification::error(get_string('preseterrorxml', 'local_ulpgccore'));
                $error = true;
            }

            // get a custom block manager for the desired page, containde in $info preset
            $bm = local_ulpgccore_get_custom_block_manager($info);
            // we ensure block region exists or added to a known region
            $region = $info['defaultregion'];
            if(!$bm->is_known_region($info['defaultregion'])) {
                $region = $bm->get_default_region(); 
                core\notification::warning(get_string('blockregionchanged', 'local_ulpgccore', $region));  
            }

            $params = ['blockname' => $info['blockname'],
                        'pagetypepattern' => $info['pagetypepattern'],
                        'parentcontextid'  => 1,
                        'requiredbytheme' => 0,
                        ];
            if(!empty($info['subpagepattern'])) {
                $params['subpagepattern'] = $info['subpagepattern'];
            } 
                        
            if($block = $DB->get_record('block_instances', $params)) {
                // update block
                $bm->reposition_block($block->id, $region, $info['defaultweight']);
            } else {
                // add block
                $subpage = empty($info['subpagepattern']) ? null : $info['subpagepattern'];
                $bm->add_block($info['blockname'], $region,
                            $info['defaultweight'], $info['showinsubcontexts'],
                            $info['pagetypepattern'], $subpage);
            }    
            
            // now ensure block instance config data is restored
            if(!empty($info['configdata'])) {
                if($block = $DB->get_record('block_instances', $params)) {
                    if($block->configdata != $info['configdata']) {
                        $block->configdata = $info['configdata'];
                        $DB->update_record('block_instances', $block);
                    }
                }
            }
        }
    }
    
    if(($action == 'export') && !empty($blockid)) {
        $block = $DB->get_record('block_instances', array('id'=>$blockid), '*', MUST_EXIST);
        if(!$preset) {
            $preset = trim($block->blockname).'-'.trim($block->pagetypepattern, ' *').'-'.trim($block->defaultregion);
        }

        local_ulpgccore_save_xml_preset($preset, 'blocks', $block);
    }
    
    if(($action == 'resetall') && confirm_sesskey()) {
        \core\session\manager::write_close();
        my_reset_page_for_all_users(MY_PAGE_PRIVATE, 'my-index');
        core\notification::success(get_string('alldashboardswerereset', 'my'));        
    }
    
    //////// end actions
    ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Get some basic data we are going to need.
    $blocks = $DB->get_records('block_instances', ['parentcontextid'    => 1, // only in system
                                                   //'showinsubcontexts'  => 1, // only those in multiple pages
                                                   'requiredbytheme' => 0]);
    
    $blockshortnames = [];
    foreach($blocks as $block) {
        $blockshortnames[$block->id] = $block->blockname . '|' . $block->pagetypepattern;
    }
    $blockscount = count($blocks);
    $loadpresets = [];
    
    $presetfiles = glob($CFG->dirroot.'/local/ulpgccore/presets/blocks/*.xml');
    $presetblocks = explode(',', get_config('local_ulpgccore', 'blockpresets'));
    if(is_array($presetblocks)) {
        $presetblocks = array_map('trim', $presetblocks);
    }
    
    $actionurl = new moodle_url('/local/ulpgccore/blockpresets.php', []);

    echo $OUTPUT->header();    
    echo $OUTPUT->heading(get_string('presetblockstable', 'local_ulpgccore'));
    
    // Button to reset users my-pages, cleaning blocks
    
    $select = 'parentcontextid > 1 AND  pagetypepattern = :pagetype AND requiredbytheme = 0 ';
    if( $DB->record_exists_select('block_instances', $select, ['pagetype' => 'my-index'])) {
        $actionurl->params(['action'=> 'resetall', 'sesskey' => sesskey()]);
        $button =  $OUTPUT->single_button($actionurl, get_string('reseteveryonesdashboard', 'my'));
    } else {
        $button = get_string('myindexcleaned', 'local_ulpgccore');
    }
    echo $OUTPUT->box($button, 'resetmyindex');
    
    if($presetfiles) {
        $table = new html_table();
        $table->width = "90%";
        $table->head = [get_string('presetname', 'local_ulpgccore'),
                                    get_string('block'),
                                    get_string('pagetypes', 'core_block'),
                                    get_string('defaultregion', 'core_block'),
                                    get_string('presetdateload', 'local_ulpgccore'),
                                    get_string('presetdatechanged', 'local_ulpgccore'),
                                    get_string('actions'),
                                ];
        $table->align = array('left', 'left', 'left', 'left', 'left', 'center', 'center', 'center');

        
        foreach($presetfiles as $presetlong) {
            $row = [];
            $info = local_ulpgccore_import_xml_preset($presetlong);
            $preset =  basename($presetlong, ".xml"); 
            $row[] = $preset;
            $blockname = $info['blockname'];  
            if($blockid = array_search($info['blockname'] . '|' . $info['pagetypepattern'] , $blockshortnames)) {
                $block = $blocks[$blockid];
                unset($blocks[$blockid]);
                $blockname = local_ulpgccore_block_url($block);
            }
            if(in_array($preset, $presetblocks)) {
                $blockname .= '<br />' . get_string('required');
                if(!$blockid) {
                    $loadpresets[] = $preset;
                }
            }

            $row[] = $blockname;
            $suffix = $info['subpagepattern'] ?  '<br />' .  get_string('blocksubpage', 'local_ulpgccore',  $info['subpagepattern'])  : '';
            $row[] = $info['pagetypepattern'] . $suffix;
            $suffix = '';
            
            $row[] = $info['defaultregion'];
           
            if($blockid) {
                $suffix = '';    
                if($info['defaultregion'] != $block->defaultregion) {
                    $suffix ='<br />' .  get_string('blocknewregion', 'local_ulpgccore',  $block->defaultregion);
                }
                
                $row[] = userdate($block->timecreated) . $suffix;
                $row[] = userdate($block->timemodified);
            } else {
                $row[] = '';
                $row[] = '';
            }

            $actions = [];    
            $params = ['preset' => $preset];            
            // reset / import
            $params['action'] = $blockid ? 'reset' : 'import';
            if($blockid) {
                $params['block'] = $blockid;
            }
            $url = new moodle_url($actionurl, $params);
            $confirmaction = new \confirm_action(get_string('confirmpresetimport', 'local_ulpgccore', $preset));
            $icon = new pix_icon('i/import', get_string('preset'.$params['action'], 'local_ulpgccore'));
            $actions[] = $OUTPUT->action_icon($url, $icon, $confirmaction);    
            
            // export
            if($blockid) {
                $params['action'] = 'export';
                $actions[] = local_ulpgccore_export_preset_icon($preset, $actionurl, $params);
            }

            $params['action'] = 'del';
            $url = new moodle_url($actionurl, $params);
            $confirmaction = new \confirm_action(get_string('confirmpresetdelete', 'local_ulpgccore', $preset));
            $icon = new pix_icon('i/delete', get_string('preset'.$params['action'], 'local_ulpgccore'));
            $actions[] = $OUTPUT->action_icon($url, $icon, $confirmaction);    
            
            $row[] =  implode(' ', $actions);            
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->box(get_string('nothingtodisplay'), 'generalbox nothingtodisplay');
    }

    if($loadpresets) {
        $actionurl->param('action', 'import');
        $actionurl->param('presets', implode(',',$loadpresets));
        $button = $OUTPUT->single_button($actionurl, get_string('presetsinstall', 'local_ulpgccore'));
        echo $OUTPUT->box($button, 'installpresets');
    }

    if($blocks) {
        echo $OUTPUT->heading(get_string('otherblockstable', 'local_ulpgccore'));
        
        $table = new html_table();
        $table->width = "75%";
        $table->head = [get_string('block'),
                        get_string('pagetypes', 'core_block'),
                        get_string('defaultregion', 'core_block'),
                        get_string('actions'),
                        ];
        $table->align = array('left', 'left', 'left',  'center');
        //$table->size = array ("15%", "*", "35%", "*", "*", "*", "*");

        $actionurl = new moodle_url('/local/ulpgccore/blockpresets.php', []);
        foreach($blocks as $block) {
            $row = [];
            $row[] = local_ulpgccore_block_url($block);
            $row[] = $block->pagetypepattern;
            $row[] = $block->defaultregion;
            
            $actions = [];
            $preset = trim($block->blockname).'-'.trim($block->pagetypepattern, ' *').'-'.trim($block->defaultregion);
            $params = ['action' => 'export', 'block' =>$block->id, 'preset' => $preset];
            $actions[] = local_ulpgccore_export_preset_icon($preset, $actionurl, $params);

            $row[] =  implode(' &nbsp; ', $actions);            

            $table->data[] = $row;
        }
        echo html_writer::table($table);
        
    }
    
    $returnurl = new moodle_url('/admin/search.php#linkmodules');
    echo $OUTPUT->continue_button($returnurl);
    
    echo $OUTPUT->footer();
