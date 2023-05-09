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
 * Displays the page for viewing exams and booking if allowed
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/examregistrar/bookinglib.php');
require_once($CFG->dirroot.'/mod/examregistrar/booking_form.php');

if(!$canbook) {
    throw new required_capability_exception($context, 'mod/examregistrar:book', 'nopermissions');
}

$tab = 'booking';
$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cm->id,'tab'=>$tab));
if($cancel = optional_param('cancel', '', PARAM_ALPHANUM)) {
    $baseurl->param('tab', 'view');
    redirect($baseurl, '', 0);
}

$period   = optional_param('period', '', PARAM_INT);
$now = time();
//$now = strtotime('4 may 2014') + 3605;

$periodobj = '';
if(!$period) {
    $periods = examregistrar_current_periods($examregistrar, $now);
    if($periods) {
        $periodobj = reset($periods);
        $period = $periodobj->id;
    }
}
if(!$periodobj) {
    $periodobj = $DB->get_record('examregistrar_periods', array('id'=>$period), '*', MUST_EXIST);
}

$searchname = optional_param('searchname', '', PARAM_TEXT);
$searchid = optional_param('searchid', '', PARAM_INT);
$sort = optional_param('sorting', 'shortname', PARAM_ALPHANUM);
$order = optional_param('order', 'ASC', PARAM_ALPHANUM);
$baseparams = array('exreg' => $examregistrar, 'id'=>$cm->id, 'tab'=>$tab);
$bookingparams = array('period'=>$period,
                      'searchname'=>$searchname,
                      'searchid'=>$searchid,
                      'programme'=>$programme,
                      'sorting'=>$sort,
                      'order'=>$order,
                      'user'=>$userid);
                      
$bookingurl = new moodle_url($baseurl, $bookingparams);

echo $output->box(get_string('bookinghelp1', 'examregistrar', $examregistrar), 'generalbox mod_introbox', 'examregistrarintro');

/// display user form, if allowed
if($canbookothers = has_capability('mod/examregistrar:bookothers',$context)) {
    print_collapsible_region_start('', 'showhideuserselector', get_string('searchoptions'),
                    'examregistrar_booking_userselector_collapsed', true, false);
    $options = array('context' => $context, 'examregid' => $examregistrar->id);
    $userselector = new examregistrar_user_selector('user', $options);
    $userselector->set_multiselect(false);
    $userselector->set_rows(5);
    $userselector->nameformat = 'lastname fistname';
    $viewuser = $userselector->get_selected_user();
    // Show UI for choosing a user to report on.
    echo $output->box_start('generalbox boxwidthnormal boxaligncenter', 'chooseuser');
    echo '<form method="get" action="' . $CFG->wwwroot . '/mod/examregistrar/view.php" >';

    // Hidden fields.
    echo html_writer::input_hidden_params($bookingurl, array('user'));

    // User selector.
    echo $output->heading('<label for="user">' . get_string('selectuser','examregistrar') . '</label>', 4);
    $userselector->display();

    // Submit button and the end of the form.
    echo '<p id="chooseusersubmit"><input type="submit" value="' . get_string('showuserexams', 'examregistrar') . '" /></p>';
    echo '</form>';
    echo $output->box_end();
    print_collapsible_region_end(false);
}

$canviewall = has_capability('mod/examregistrar:viewall', $context);

$config = examregistrar_get_instance_config($examregistrar->id);
$capabilities = array('bookothers'=>$canbookothers, 'manageexams'=>$canmanageexams);
$lagdays = examregistrar_set_lagdays($examregistrar, $config, $periodobj, $capabilities);

echo $output->exams_item_selection_form($examregistrar, $course, $bookingurl, $bookingparams);
if($canviewall) {
    echo $output->exams_courses_selector_form($examregistrar, $course, $bookingurl, $bookingparams);
}

$bookingurl->param('action', 'checkvoucher');
echo $output->box(html_writer::link($bookingurl, get_string('checkvoucher', 'examregistrar')), 'resettable mdl-right ');
$bookingurl->remove_params('action');

// Get courses with bookable exams in period, annuality
list($examcourses, $noexamcourses) = examregistrar_booking_get_bookable_courses($examregistrar, $course, $bookingparams, 
                                                                                $canviewall, $canbookothers, $canmanageexams);

// get existing user bookings in those course 
$bookings =  examregistrar_bookings_in_courses($examcourses, $examregprimaryid, $period, $userid);
unset($examcourses);
// build sessions array for structure bookings in separate sessions 
$sessions = examregistrar_booking_booked_sessions($bookings, $userid);

/// get period name & code
if($period) {
    list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period, 'periods', 'period');

}
echo $output->heading(get_string('examsforperiod', 'examregistrar', $periodname));
$session = examregistrar_next_sessionid($examregistrar, time(), true);
$info = new stdClass();
$info->lagdays = examregistrar_set_lagdays($examregistrar, $config, $periodobj, array());
$info->weekexamday = userdate($session->examdate, '%A');
$info->weekday = userdate(($session->examdate - DAYSECS*$info->lagdays - 3600*3), '%A');
echo $output->box(get_string('bookinghelp2', 'examregistrar', $info), 'generalbox mod_introbox', 'examregistrarintro');

$params = array('period'=>$period, 'programme'=>$programme, 'user'=>$userid, 'order'=>$order, 'sorting'=>$sort);
$mform = new examregistrar_booking_form(null, array('exreg' => $examregistrar, 'cmid'=>$cm->id, 'period'=>$periodobj,
                                                    'examcourses'=>$bookings, 'noexamcourses'=>$noexamcourses,
                                                    'params'=>$bookingparams, 'capabilities'=>$capabilities),
                                               'post', '', array('class'=>' bookingform ' ));

$message = array();

if($formdata = $mform->get_data()) {

// process bookings by student or bookothers
    $now = time();
    //$now = strtotime('4 may 2014') + 3605;
    $bookings = optional_param_array_array('booking', array(), PARAM_INT);
    
    list($bookings, $errors) = examregistrar_clean_userbookings($bookings, $sessions);
    // now only remain true bookings without errors
  
    $exam = false;
    if($bookings) {
        foreach($bookings as $key => $booking) {
            $newid = 0;
            $record = false;
            // there may be several non-booked records if changed booking many times
            $exam = $DB->get_record('examregistrar_exams', array('id'=>$booking['examid']));
            if($records = $DB->get_records('examregistrar_bookings', array('examid'=>$booking['examid'], 'userid'=>$userid,
                                                                            'booked'=>$booking['booked'], 'bookedsite'=>$booking['bookedsite'],
                                                                            'modifierid'=>$USER->id), 'timemodified DESC')) {
                $record = reset($records);
                $newid = $record->id;
                // recover exam voucher if record_exists
                $voucher = $DB->get_record('examregistrar_vouchers', array('examregid'=>$examregprimaryid, 'bookingid'=> $record->id));
                
            } else {
                if(!$exam) {
                    $booking['error'] = 'noexamid';
                    $errors[$key] = $booking;
                } else {
                    //$examdate = $DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$exam->examsession, 'examregid'=>$exam->examregid, 'period'=>$exam->period));    
                    //$examdate = examregistrar_booking_get_examdate($exam, $canmanageexams);
                    if(($exam->period == $period) &&
                            !examregistrar_check_exam_in_past($now, $lagdays, $exam, $canmanageexams) &&
                            (examregistrar_check_exam_within_period($now, $periodobj, $config->selectdays, $exam) OR $canmanageexams)) {
                            
                        if($record = examregistrar_booking_store_new($booking, $userid, $now)) {
                            if(!empty($record) && $newid = $record->id) {
                            $voucher = examregistrar_set_booking_voucher($examregprimaryid, $newid, $now);
                            }
                        }
                    } else {
                        $booking['error'] = 'offbounds';
                        $errors[$key] = $booking;
                    }
                }
            }
            // all other bookings for examid & userid set booked = 0
            if($newid) {
                $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cm->id, 'down'=>'voucher'));
                $message[$newid] = examregistrar_booking_notify_newbooking($examregistrar->id, $context, 
                                                                            $downloadurl, $record, $voucher);
            }
            // there must be only one booking in one call in case several calls in a period
            examregistrar_booking_clean_newbookings($exam, $booking, $newid, $userid, $context);
        }
    }

    if($errors) {
        foreach($errors as $key => $error) {
            $shortname = $error['shortname'];
            $errors[$key] = html_writer::span(get_string('bookingerror_'.$error['error'], 'examregistrar', $shortname), 'errorbox alert-error');
            unset($formdata->booking[$key]);
        }
        $message[] = '<p>'.implode('<br />', $errors).'</p>';
    }
}

if($message) {
    echo $output->box(get_string('changessaved'), ' generalbox messagebox success ');
    foreach($message as $mes) {
        echo $output->box($mes, ' generalbox messagebox centerbox centeredbox error ');
    }
    $url = new moodle_url($baseurl, $bookingparams);
    echo $output->continue_button($url);

} else {
    $mform->display();
}
