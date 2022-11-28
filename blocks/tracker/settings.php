<?php
require_once($CFG->dirroot.'/mod/tracker/lib.php');

$options = array(0=>get_string('none'));

// required during installation
$dbman = $DB->get_manager();
$table = new xmldb_table('tracker');
if($dbman->table_exists($table)) {
    if($trackers = $DB->get_records_menu('tracker', null, '', 'id, name')) {
        $options = $options + $trackers;
    }
}

$settings->add(new admin_setting_configselect('block_tracker/tracker', get_string('tracker', 'block_tracker'),
                   get_string('tracker_desc', 'block_tracker'), 0, $options));

                   
$settings->add(new admin_setting_configcheckbox('block_tracker/enabledremote', get_string('enabledremote', 'block_tracker'),
            get_string('enabledremote_desc', 'block_tracker'), 0, PARAM_INT));
                   

$settings->add(new admin_setting_configtext('block_tracker/remoteserver', get_string('remoteserver','block_tracker'), 
                get_string('remoteserver_desc','block_tracker'), ''));
                   
$settings->add(new admin_setting_configtext('block_tracker/remoteinstance', get_string('remoteinstance','block_tracker'), 
                get_string('remoteinstance_desc','block_tracker'), ''));
                   
$settings->add(new admin_setting_configtext('block_tracker/wstoken', get_string('wstoken','block_tracker'), 
                get_string('wstoken_desc','block_tracker'), ''));
