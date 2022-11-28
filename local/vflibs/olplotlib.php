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
 * @package local_vflibs
 * @author valery.fremaux@gmail.com
 * @category local
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Implements a google maps API V3 wrapper for Moodle
 */

function olmaps_require_js() {
    echo "<script type=\"text/javascript\" src=\"https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/build/ol.js\"></script>\n";
}

function googlemaps_initialize() {
    global $olmaps;

    $str = '';

    if (!empty($olmaps)) {
        foreach ($olmaps as $gmap) {
            $str .= "ol_initialize_{$gmap}();\n";
        }
    }

    echo "<script type=\"text/javascript\">
        function ol_initialize_all(){
            {$str}
        }

        document.body.onload = ol_initialize_all;
        ol_initialize_all();
    </script>\n";
}

/**
 * ex : list($lat,$lng, $zoom) = array(-34.397, 150.644,8)
 * ex options : {
 *             zoom: 8,
 *             center: latlng,
 *             mapTypeId: google.maps.MapTypeId.ROADMAP
 *           };
 */
function ol_print_graph($htmlid, $lat, $lng, $width = 400, $height = 350, $options = array(), $data = null) {
    global $olmaps;

    if (!isset($olmaps)) {
        $olmaps = array();
    }

    if (empty($lat)) {
        $lat = '46.769968';
    }
    if (empty($lng)) {
        $lng = '1.757813';
    }

    if (empty($options['zoom'])) {
        $options['zoom'] = 7;
    }

    $str = "\n
    <script type=\"text/javascript\">
        function ol_initialize_{$htmlid}() {
            var map = new ol.Map({
                target: 'map{$htmlid}',
                layers: [
                    new ol.layer.Tile({
                    source: new ol.source.OSM()
                })],
                view: new ol.View({
                    center: ol.proj.fromLonLat([{{lng}}, {{lat}}]),
                    zoom: {$options['zoom']}
                })
            });
        }
    </script>\n
    ";

    $str .= "\n<div id=\"map{$htmlid}\" style=\"width:{$width}px; height:{$height}px\"></div>";

    $googlemaps[] = $htmlid;

    return $str;
}

function googlemaps_embed_graph($htmlid, $lat, $lng, $width = 400, $height = 350, $options = array(), $data = null) {
    global $COURSE, $OUTPUT;

    if (empty($lat)) {
        $lat = '46.769968';
    }
    if (empty($lng)) {
        $lng = '1.757813';
    }

    $optionsstr = json_encode($options);
    $optionsstr = preg_replace('/\"(google\.maps\.[^\s]*)\"/', "$1", $optionsstr); // Remove quotes provided by php jsonisation.
    $optionsstr = preg_replace('/\"([\d+.]+)\"/', "$1", $optionsstr); // Remove quotes around numbers provided by php jsonisation.
    $optionsstr = str_replace('"latlng"', 'latlng', $optionsstr); // Remove quotes provided by php jsonisation.

    $template = new StdClass;
    $template->htmlid = $htmlid;
    $template->courseid = $COURSE->id;
    $template->lat = $lat;
    $template->lng = $lng;
    $template->width = $width;
    $template->height = $height;
    $template->optionsstr = $optionsstr;

    foreach ($data as $d) {
        $markertpl = new Stdclass;
        $markertpl->data = json_encode($d);
        $template->markers[] = $markertpl;
    }

    return $OUTPUT->render_from_template('local_vflibs/googlemapform', $template);
}

/**
 * get exact static gelocation from a human readable address
 * Important : note that this function is sensible to Google Terms
 * of service definition, that is allowing a 2500 resolutions per day
 * as free unregistered service, but needing a Premier service account
 * to resolve a bigger amount per day.
 * @param string $region
 * @param string $address
 * @param string $postalcode
 * @param string $city
 * @param arrayref &$errors filled with google errors
 */
function olmaps_get_geolocation($region, $address, $postalcode, $city, &$errors) {

    $locationurlstring = 'region='.$region.'&address='.urlencode($address).','.urlencode($postalcode.' '.$city);

    if ($cached = $DB->get_record('dashboard_geo_cache', array('address' => $locationurlstring))) {
        return $cached->latlng;
    }

    $uri = 'http://maps.google.fr/maps/api/geocode/json';
    $querystring = 'sensor=false&'.$locationurlstring;

    // Initialize with the target URL.
    $ch = curl_init($uri.'?'.$querystring);

    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle Dashboards');
    curl_setopt($ch, CURLOPT_POSTFIELDS, '');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $rawresponse = curl_exec($ch);

    if ($rawresponse === false) {
        $errors[] = curl_errno($ch) .':'. curl_error($ch);
        return false;
    }

    if (!$geostruct = json_decode($rawresponse)) {
        $errors[] = "Google bad response format";
        return false;
    }

    if ($geostruct->status != 'OK') {
        $errors[] = "Google denied service. Reason : ".$geostruct->status;
        return false;
    }

    $location = $geostruct->results[0]->geometry->location->lat.','.$geostruct->results[0]->geometry->location->lng;

    // Caches result.
    $cacherec->address = $locationurlstring;
    $cacherec->latlng = $location;
    $cacherec->regioncode = $region;
    $DB->insert_record('dashboard_geo_cache', $cacherec);
    return $location;
}
