<?php
/**
 * Extensión de sincronización de la ULPGC
 *
 * @package    local
 * @subpackage ulpgcquiz
 * @copyright  2016 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/////////////////////////////////////////////////////////////////////////////////
///  Called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$plugin->version  = 2023080101;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2022112800;  // Requires this Moodle version (Moodle 2.7)
$plugin->cron     = 0;           // Period for cron to check this module (secs)

$plugin->component = 'local_ulpgcquiz';
$plugin->maturity  = MATURITY_STABLE;

$plugin->release = '1.2';             // User-friendly version number
