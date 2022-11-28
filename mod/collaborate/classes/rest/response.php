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
 * REST response class
 * @author    Guy Thomas
 * @copyright Copyright (c) 2017 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborate\rest;

class response {
    /**
     * @var string
     */
    public $jsonstr = '';

    /**
     * @var null | stdClass
     */
    public $object = null;

    /**
     * @var int
     */
    public $httpcode;

    /**
     * response constructor.
     * @param string $jsonstr
     * @param int $httpcode
     */
    public function __construct($jsonstr, $httpcode) {
        $this->jsonstr = $jsonstr;
        if (!empty($jsonstr)) {
            $this->object = (object) json_decode($jsonstr);
        }
        $this->httpcode = $httpcode;
    }
}
