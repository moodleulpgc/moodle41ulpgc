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
 * Library code used by the service control interfaces.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Getting a list of all services.
 *
 * @param int $limitfrom
 * @param int $limitnum
 *
 * @return array
 * @throws \dml_exception
 */
function local_webhooks_get_list_records($limitfrom = 0, $limitnum = 0) {
    global $DB;

    $listservices = $DB->get_records('local_webhooks_service', null, 'id', '*', $limitfrom, $limitnum);

    foreach ($listservices as $servicerecord) {
        if (!empty($servicerecord->events)) {
            $servicerecord->events = local_webhooks_unarchive_data($servicerecord->events);
        }
    }

    return $listservices;
}

/**
 * Getting information about the service.
 *
 * @param int $serviceid
 *
 * @return object
 * @throws \dml_exception
 */
function local_webhooks_get_record($serviceid = 0) {
    global $DB;

    $servicerecord = $DB->get_record('local_webhooks_service', array('id' => $serviceid), '*', MUST_EXIST);

    if (!empty($servicerecord->events)) {
        $servicerecord->events = local_webhooks_unarchive_data($servicerecord->events);
    }

    return $servicerecord;
}

/**
 * Clear the database table.
 *
 * @throws \dml_exception
 */
function local_webhooks_remove_list_records() {
    global $DB;

    $DB->delete_records('local_webhooks_service');
}

/**
 * Delete the record.
 *
 * @param int $serviceid
 *
 * @throws \dml_exception
 * @throws \coding_exception
 */
function local_webhooks_remove_record($serviceid = 0) {
    global $DB;

    $DB->delete_records('local_webhooks_service', array('id' => $serviceid));
    local_webhooks_events::service_deleted($serviceid);
}

/**
 * Update the record in the database.
 *
 * @param  object  $data
 * @param  boolean $insert
 *
 * @return boolean
 * @throws \dml_exception
 * @throws \coding_exception
 */
function local_webhooks_update_record($data, $insert = true) {
    global $DB;

    if (empty($data->events)) {
        $data->events = array();
    }

    $data->events = local_webhooks_archiving_data($data->events);

    if ((bool) $insert) {
        $result = $DB->insert_record('local_webhooks_service', $data);
        local_webhooks_events::service_added($result);
    } else {
        $result = $DB->update_record('local_webhooks_service', $data);
        local_webhooks_events::service_updated($data->id);
    }

    return (bool) $result;
}

/**
 * Make a backup copy of all the services.
 *
 * @return string
 * @throws \dml_exception
 * @throws \coding_exception
 */
function local_webhooks_create_backup() {
    $listservices = local_webhooks_get_list_records();
    $listservices = local_webhooks_archiving_data($listservices);
    local_webhooks_events::backup_performed();

    return $listservices;
}

/**
 * Restore the data from the backup.
 *
 * @param string $listservices
 *
 * @throws \dml_exception
 * @throws \coding_exception
 */
function local_webhooks_restore_backup($listservices = '') {
    $listservices = local_webhooks_unarchive_data($listservices);

    local_webhooks_remove_list_records();

    foreach ($listservices as $servicerecord) {
        local_webhooks_update_record($servicerecord);
    }

    local_webhooks_events::backup_restored();
}

/**
 * Compress an array into a string.
 *
 * @param  array $data
 *
 * @return string
 */
function local_webhooks_archiving_data(array $data = array()) {
    return base64_encode(gzcompress(serialize($data), 3));
}

/**
 * Gets an array from a compressed string.
 *
 * @param  string $data
 *
 * @return array
 */
function local_webhooks_unarchive_data($data = '') {
    return unserialize(gzuncompress(base64_decode($data)));
}