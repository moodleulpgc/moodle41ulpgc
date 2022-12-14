<?php
// $plugin_settings is a mandatory name for including these plugin settings in admin tree
$options = array();
$options[0] = get_string('none');
$options[1] = get_string('all');
$options[2] = get_string('admininstances', 'local_supervision');

$plugin = 'supervisionwarning_unreplied_dialogue';

$settings->add(new admin_setting_configselect("$plugin/enabled", get_string('pluginname', $plugin),
                get_string('config_pluginname', 'supervisionwarning_ungraded_assign'), 0, $options));
$settings->add(new admin_setting_configtext("$plugin/threshold", get_string('threshold', $plugin), 
                get_string('config_threshold', $plugin), 2, PARAM_INT));
$settings->add(new admin_setting_configcheckbox("$plugin/weekends", get_string('weekends', $plugin),
                get_string('config_weekends', $plugin), 1, PARAM_INT));                
                


