<?php

/**
 * Defines the capabilities used by the attendancetools
 *
 * @package    report_attendancetools
 * @subpackage attendancetools
 * @copyright  2023 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'report/attendancetools:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),
    
    /* allows the user to manage attendance tools settings */
    'report/attendancetools:manage' => array(
        'riskbitmask' => RISK_MANAGETRUST|RISK_PERSONAL|RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),
    
);
