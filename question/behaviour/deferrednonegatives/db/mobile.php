<?php
// This file is part of the deferred all or nothing question behaviour for Moodle
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
 * Deferred feedback (all-or-nothing) question behaviour mobile addon
 *
 * @package    qbehaviour_deferrednonegatives
 * @copyright  2021 Enrique Castro @ULPGC 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$addons = array(
    "qbehaviour_deferrednonegatives" => array(
        "handlers" => array(
            "deferrednonegatives" => array(
                "displaydata" => array(),
                "delegate" => "CoreQuestionBehaviourDelegate",
                "method" => "mobile_qbehaviour_deferrednonegatives",
            ),
        ),
        "lang" => array(
            array("pluginname", "qbehaviour_deferrednonegatives"),
        ),
    ),
);
