<?php

/**
 * Version details
 *
 * @package    block_examswarnings
 * @copyright  2012 Enrique Castro at ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2019080100;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2012061700;        // Requires this Moodle version
$plugin->component = 'block_examswarnings'; // Full name of the plugin (used for diagnostics)
$plugin->dependencies = array('mod_examregistrar' => ANY_VERSION);
