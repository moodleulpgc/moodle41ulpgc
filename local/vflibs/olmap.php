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
 * GoogleMap iframe wrapper
 * 
 * @package local_vflibs
 * @category local
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 1.9
 */
require('../../config.php');

$systemcontext = context_system::instance();

$config = get_config('local_vflibs');

$lat = optional_param('lat', 0, PARAM_TEXT);
$lng = optional_param('lng', 0, PARAM_TEXT);
$id = required_param('mapid', PARAM_TEXT);
$zoom = optional_param('zoom', 5, PARAM_INT);
$mapid = 'map_'.$id;
$markers = optional_param_array('markers', [], PARAM_TEXT); // Array of markers

$params = ['lat' => $lat, 'lng' => $lng, 'mapid' => $id];
$url = new moodle_url('/local/vflibs/googlemap.php', $params);
$PAGE->set_context($systemcontext);

$template = new StdClass;
$template->lat = $lat;
$template->lng = $lng;
$template->zoom = $zoom;

$fs = get_file_storage();

$markerimages = $fs->get_area_files($systemcontext->id, 'local_vflibs', 'googlemarks', 0, true);
$hasshadow = [];
$imageurls = [];
$shadowurls = [];

if (!empty($markerimages)) {
    foreach ($markerimages as $imfile) {

        $sizeinfo = $imfile->get_imageinfo();

        $imname = $imfile->get_filename();
        if (strpos('mk_', $imname) === 0) {
            $classname = str_replace('sh_', '', $imname);
            $imageurls[$classname] = moodle_url::make_plugin_file(
                $systemcontext->id,
                'local_vflibs',
                'googlemarks',
                0,
                $imfile->get_filepath(),
                $imfile->get_filename());
            if (!empty($sizeinfo)) {
                $markerimagetpl->sizeinfo0 = $sizeinfo['width'];
                $markerimagetpl->sizeinfo1 = $sizeinfo['height'];
            }
            $template->markerimages[] = $markerimagetpl;
        } else {
            $classname = str_replace('sh_', '', $imname);
            $shadowurls[$classname] = moodle_url::make_plugin_file(
                $systemcontext->id,
                'local_vflibs',
                'googlemarks',
                0,
                $imfile->get_filepath(),
                $imfile->get_filename());
            if (!empty($sizeinfo)) {
                $shadowimagetpl->sizeinfo0 = $sizeinfo['width'];
                $shadowimagetpl->sizeinfo1 = $sizeinfo['height'];
            }
            $hasshadow[$classname] = true;
        }
    }
}

if (!empty($markers)) {
    foreach ($markers as $amarker) {
        $amarkertpl = json_decode(stripslashes(urldecode($amarker)));
        if (!empty($amarkertpl->markerclass)) {
            $amarkertpl->iconurl = $imageurls[$amarkertpl->markerclass];
            if (array_key_exists($amarkertpl->markerclass, $hasshadow)) {
                $amarkertpl->hasshadow = true;
            }
        }
        $template->markers[] = $amarkertpl;
    }
}

echo $OUTPUT->render_from_template('local_vflibs/openlayersframe', $template);

