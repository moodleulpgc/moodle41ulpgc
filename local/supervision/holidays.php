<?php

/**
 * This file contains a local_supervision page
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once($CFG->dirroot."/local/supervision/locallib.php");
    require_once($CFG->libdir.'/adminlib.php');

    $cid = optional_param('cid', SITEID, PARAM_INT);
    $baseparams = array('cid' => $cid);

    $baseurl = new moodle_url('/local/supervision/holidays.php', $baseparams);

    require_login();
   
    $title = get_string('editholidays', 'local_supervision');
    $context = supervision_page_setup('editholidays', $baseurl, $title);
    require_capability('local/supervision:manage', $context);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('holidaystable', 'local_supervision'));

    $config = get_config('local_supervision');
    $time = strtotime($config->startdisplay);
    $holidays = $DB->get_records_select('supervision_holidays', ' datestart >= :time', array('time'=>$time), ' datestart ASC' );

    echo '<div class="singlebutton forumaddnew">';
    $url = new moodle_url('/local/supervision/editholidays.php', array());
    echo $OUTPUT->single_button($url, get_string('insertholiday', 'local_supervision'), 'post');
    echo '</div>';
    print '<br />';
    if($holidays) {
        $table = new html_table();
        $table->width = "80%";
        $table->head = array(get_string('name'), get_string('date'), get_string('duration', 'local_supervision'), get_string('type', 'local_supervision'), get_string('action')  );
        $table->align = array('left', 'left', 'left', 'center', 'center');
        //$table->size = array ("15%", "*", "35%", "*", "*", "*", "*");

        $stredit = get_string('edit');
        $strdelete = get_string('delete');

        foreach($holidays as $vacation) {
            $row = array();
            $row[] = $vacation->name;
            $row[] = userdate($vacation->datestart);
            $row[] = format_time($vacation->timeduration);
            $row[] = $vacation->scope;
            $rurl = new moodle_url($url);
            $rurl->param('hid', $vacation->id);
            $icons = html_writer::link($rurl, $OUTPUT->pix_icon('t/edit', $stredit, 'moodle', array('class'=>'iconsmall', 'title'=>$stredit)));
            $rurl->param('del', 1);
            $row[] = $icons.html_writer::link($rurl, $OUTPUT->pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete)));
            $table->data[] = $row;
        }
       echo html_writer::table($table);
    } else {
    }
    
    $returnurl = get_local_referer(false);
    $me = qualified_me();
    if($returnurl == $me) {
        $returnurl = is_siteadmin() ? '/admin/settings.php?section=supervisionwarnings' : '/my';
        $returnurl = $CFG->wwwroot.$returnurl;
    }
    
    echo $OUTPUT->continue_button($returnurl);
    
    echo $OUTPUT->footer();
