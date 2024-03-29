<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class for logging of override created events
 *
 * @package mod_vpl
 * @copyright 2014 onwards Juan Carlos Rodríguez-del-Pino, 2021 Astor Bizard
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_vpl\event;

defined( 'MOODLE_INTERNAL' ) || die();
require_once(dirname( __FILE__ ) . '/../../locallib.php');

/**
 * Event class for when an override is created.
 */
class override_created extends override_base {
    /**
     * Initialize the override creation event.
     */
    protected function init() {
        parent::init();
        $this->data['crud'] = 'c';
        $this->legacyaction = 'created override';
    }

    /**
     * Get the event description.
     *
     * @return string The event description.
     */
    public function get_description() {
        return $this->get_description_mod( 'created' );
    }
}
