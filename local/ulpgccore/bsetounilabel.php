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
    require_once($CFG->dirroot.'/course/modlib.php');
    
    $action =  optional_param('action', '', PARAM_ALPHA); 
    $baseparams = [];
    $baseurl = new moodle_url('/local/ulpgccore/bsetounilabel.php', $baseparams);

    admin_externalpage_setup('local_ulpgccore_bsetounilabel', '', null, $baseurl);
       
    $context = context_system::instance(); 
    $PAGE->set_context($context);
    require_capability('local/ulpgccore:manage', $context);

    // Get some basic data we are going to need.
    $bsemod = $DB->get_field('modules', 'id', ['name'=>'bootstrapelements']);
    $unilabelmod = $DB->get_field('modules', 'id', ['name'=>'unilabel']);

    $subqsql = "SELECT 1
                FROM {unilabel} ul
                JOIN {course_modules} cmu ON cmu.instance = ul.id AND cmu.course = ul.course AND cmu.module = :unl
               WHERE ul.name = be.name AND cm.section = cmu.section AND ul.course = be.course ";

    $sql = "SELECT be.*, cm.id AS cmid, cs.section AS sectionnum
              FROM {bootstrapelements} be
              JOIN {course_modules} cm ON cm.instance = be.id AND cm.course = be.course AND cm.module = :bse
              JOIN {course_sections} cs ON cs.course = cm.course and cm.section = cs.id
             WHERE be.intro IS NOT NULL AND NOT EXISTS($subqsql)  ";

    $params = ['bse' => $bsemod, 'unl' => $unilabelmod];


    ////// actions 
    if($action == 'update') {

        $elements = $DB->get_records_sql($sql, $params);

        foreach($elements as $element) {
            $updatesuccess = false;
            list($course, $cm) = get_course_and_cm_from_instance($element->id, 'bootstrapelements', $element->course);

            //print_object($element);
            //print_object($cm);
            $moduleinfo = clone $element;
            unset($moduleinfo->id);

            $moduleinfo->coursemodule = 0;
            $moduleinfo->module = $unilabelmod;
            $moduleinfo->modulename = 'unilabel';
            $moduleinfo->instance = 0;
            $moduleinfo->section = $moduleinfo->sectionnum;
            $moduleinfo->beforemod = $moduleinfo->cmid;

            $moduleinfo->add = 'unilabel';
            $moduleinfo->update = 0;
            $moduleinfo->return = 0;

            $moduleinfo->showdescription = $cm->showdescription;
            $moduleinfo->visible = $cm->visible;
            $moduleinfo->visibleoncoursepage = $cm->visibleoncoursepage;
            $moduleinfo->lang = $cm->lang;
            $moduleinfo->score = $cm->score;
            $moduleinfo->availabilityconditionsjson = $cm->availability;
            $moduleinfo->availability = $cm->availability;
            $moduleinfo->completion = $cm->completion;
            $moduleinfo->completionexpected = $cm->completion;
            $moduleinfo->tags = [];

            if(($moduleinfo->bootstraptype == 0) || ($moduleinfo->bootstraptype == 1)) {
                $moduleinfo->unilabeltype = 'collapsedtext';
            } else {
                $moduleinfo->unilabeltype = 'simpletext';
                $icon = '';
                if($element->bootstrapicon) {
                    $icon = html_writer::tag('i', '', ['class' => "icon fa {$element->bootstrapicon} "]);
                }

                $heading = html_writer::tag('h3', $icon.$element->title, ['class' => 'bootssss']);
                $moduleinfo->intro = $heading.$moduleinfo->intro;
                if($moduleinfo->bootstraptype == 3) {
                    $moduleinfo->intro = html_writer::tag('blockquote', $moduleinfo->intro);
                }
                
            }

            $moduleinfo->introeditor = ['text'  => $moduleinfo->intro,
                                        'format'=> $moduleinfo->introformat,
                                        'itemid'=> 0,
                                       ];

            //print_object($moduleinfo);
            // This actuaally creates course_module  & unilabel record
            $moduleinfo = add_moduleinfo($moduleinfo, $course);
            
            // if instance is set, we have successfully added a new coursemodule
            if($moduleinfo->instance) {
                // Now save the unilabel content, in other table, per subplugin type
                $unilabel = new \stdClass();
                $unilabel->id = $moduleinfo->instance;
                $unilabel->course = $moduleinfo->course;
                $unilabel->name = $moduleinfo->name;
                $unilabel->intro = $element->intro;
                $unilabel->introformat = $element->introformat;
                $unilabel->unilabeltype = $moduleinfo->unilabeltype;
                $unilabel->timemodified = $element->timemodified;

                $unilabeltype = \mod_unilabel\factory::get_plugin($moduleinfo->unilabeltype);
                $data = $unilabeltype->get_form_default([], $unilabel);
                if(!empty($data)) {
                    $prefix = "unilabeltype_{$moduleinfo->unilabeltype}_";
                    $icon = '';
                    if($element->bootstrapicon) {
                        $icon = html_writer::tag('i', '', ['class' => "icon fa {$element->bootstrapicon} "]) . ' ';
                    }
                    $data[$prefix.'title'] = $icon.$element->title;

                    if($moduleinfo->bootstraptype == 0) {
                        $data[$prefix.'presentation'] = 'dialog';
                    }
                }
                $updatesuccess = \mod_unilabel\factory::save_plugin_content((object)$data, $unilabel);
            }

            if($moduleinfo->instance && $updatesuccess) {
                core\notification::success(get_string('unilabeladded', 'local_ulpgccore', $element));
            } else {
                core\notification::error(get_string('unilabeladdederror', 'local_ulpgccore', $element));
            }
        }
    }

    
    //////// end actions
    ////////////////////////////////////////////////////////////////////////////////////////////////////



    $elements = $DB->get_records_sql($sql, $params);

    $actionurl = new moodle_url('/local/ulpgccore/bsetounilabel.php', []);

    echo $OUTPUT->header();    
    echo $OUTPUT->heading(get_string('bsetounilabel', 'local_ulpgccore'));
    

        $table = new html_table();
        $table->width = "90%";
        $table->head = [get_string('name'),
                                    get_string('course'),
                                    get_string('section'),
                                ];
    if(!empty($elements)) {
        foreach($elements as $element) {
            $row = [];
            $row[] = $element->name;
            $row[] = $element->course;
            $row[] = $element->sectionnum;

            $table->data[] = $row;
        }
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->box(get_string('nothingtodisplay'), 'generalbox nothingtodisplay');
    }

    if(!empty($elements)) {
        $actionurl->param('action', 'update');
        $button = $OUTPUT->single_button($actionurl, get_string('bsetounilabel', 'local_ulpgccore'));
        echo $OUTPUT->box($button, 'bsetounilabel');
    }

    $returnurl = new moodle_url('/admin/search.php#linkmodules');
    echo $OUTPUT->continue_button($returnurl);
    
    echo $OUTPUT->footer();
