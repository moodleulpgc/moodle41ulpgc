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

// jshint undef:false, unused:false, scripturl:true

/**
 * Javascript controller for fixing doclinks.
 *
 * @module     local_vflibs/docfix
 * @package    local_vflibs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log'], function($, log) {

    var docfix = {
        init: function() {
            $('.editing_help').attr('target', '_blank');

            log.debug('ADM VFLibs Docfix initialized');
        }
    };

    return docfix;
});