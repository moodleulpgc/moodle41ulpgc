<?php

/**
 * Global configuration settings for the tracker module.
 *
 * @package    mod
 * @subpackage tracker
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



if ($ADMIN->fulltree) {

    require_once($CFG->dirroot.'/mod/tracker/lib.php');

    $settings->add(new admin_setting_configtext('tracker/resolvingdays', get_string('resolvingdays', 'tracker'),
                       get_string('configresolvingdays', 'tracker'), 5, PARAM_INT));

    $settings->add(new admin_setting_configtext('tracker/closingdays', get_string('closingdays', 'tracker'),
                       get_string('configclosingdays', 'tracker'), 3, PARAM_INT));

    $settings->add(new admin_setting_configtext('tracker/reportmaxfiles', get_string('reportmaxfiles', 'tracker'),
                       get_string('configreportmaxfiles', 'tracker'), 3, PARAM_INT));

    $settings->add(new admin_setting_configtext('tracker/developmaxfiles', get_string('developmaxfiles', 'tracker'),
                       get_string('configdevelopmaxfiles', 'tracker'), 5, PARAM_INT));


    $STATUSKEYS = array(POSTED => get_string('posted', 'tracker'),
                        OPEN => get_string('open', 'tracker'),
                        RESOLVING => get_string('resolving', 'tracker'),
                        WAITING => get_string('waiting', 'tracker'),
                        TESTING => get_string('testing', 'tracker'),
                        RESOLVED => get_string('resolved', 'tracker'),
                        ABANDONNED => get_string('abandonned', 'tracker'),
                        TRANSFERED => get_string('transfered', 'tracker'),
                        PUBLISHED => get_string('published', 'tracker'),
                        VALIDATED => get_string('validated', 'tracker'),
                        );

    $settings->add(new admin_setting_configmultiselect('tracker/openstatus', get_string('openstatus', 'tracker'),
                                                    get_string('openstatus_desc', 'tracker'), array(POSTED, OPEN, RESOLVING, WAITING, TESTING), $STATUSKEYS));
                       
}
