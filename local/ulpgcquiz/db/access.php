<?php

/**
 * Defines the capabilities used by the coursetemplating admin tools
 *
 * @package    local
 * @subpackage ulpgcquiz
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    /* allows the user to manage ULPGC groups settings */
    'local/ulpgcquiz:manage' => [
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG | RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ],
    ],
    // View the quiz reports.
    'local/ulpgcquiz:viewhiddenstatus' => [
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ]
    ],
];
