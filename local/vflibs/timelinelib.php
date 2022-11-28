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
 * an integration wrapper for timeline of SIMILE project
 *
 * @package local_vflibs
 * @author valery.fremaux@gmail.com
 * @category local
 */
defined('MOODLE_INTERNAL') || die();

function timeline_require_js($libroot) {
    global $CFG, $PAGE;

    static $timelineloaded = false;

    if ($timelineloaded) {
        return;
    }

    $PAGE->requires->js($libroot.'/timeline_api_2.3.0/setup.php', true);
    $PAGE->requires->js($libroot.'/timeline_api_2.3.0/timeline_js/timeline-api.js', true);
    $timelineloaded = true;
}

function timeline_initialize($return = false) {
    global $timelinegraphs;

    $str = '';
    $strtmp = '';

    if (!empty($timelinegraphs)) {
        foreach ($timelinegraphs as $tgraph) {
            $strtmp .= "timeline_initialize_$tgraph();\n";
        }
    }

    $str .= "<script type=\"text/javascript\">
        function timeline_initialize_all(){
            {$strtmp}
        }

        // document.body.onload = timeline_initialize_all;
        timeline_initialize_all();
    </script>\n";

    if ($return) {
        return $str;
    }
    echo $str;
}

function timeline_print_graph(&$theblock, $htmlid, $width, $height, &$data, $return = false) {
    global $timelinegraphs, $CFG, $COURSE, $USER;

    if (!isset($timelinegraphs)) {
        $timelinegraphs = array();
    }

    // Generate data on a tmp file.
    timeline_generate_xml($theblock, $htmlid, $data);

    $str = "<div id=\"timeline_{$htmlid}\" style=\"height:{$height}px; width:{$width}pxborder: 1px solid #aaa\"></div>\n";

    $genid = rand(1000, 100000);

    $str .= "<script type=\"text/javascript\">
        var tl;
        function timeline_initialize_{$htmlid}() {

           // create bands
              var eventSource = new Timeline.DefaultEventSource();
           var bandInfos = [
             Timeline.createBandInfo({
                 eventSource:    eventSource,
                 width:          \"70%\",
                 intervalUnit:   Timeline.DateTime.{$theblock->config->upperbandunit},
                 intervalPixels: 100
             })
    ";
    if ($theblock->config->showlowerband) {
        $str .= ",
             Timeline.createBandInfo({
                 eventSource:    eventSource,
                 width:          \"30%\",
                 intervalUnit:   Timeline.DateTime.{$theblock->config->lowerbandunit},
                 intervalPixels: 200
             })
         ";
    }

    $str .= "
           ];
    ";

    if ($theblock->config->showlowerband) {
        $str .= "
                bandInfos[1].syncWith = 0;
                   bandInfos[1].highlight = true;
           ";
    }

    $filename = $htmlid.'_'.$USER->id.'.xml';
    $blockcontext = context_block::instance($thelbock->instance->id);
    $xmlurl = moodle_url::make_pluginfile_url($blockcontext->id, 'block_'.$theblock->blockname, 'timelineevents', $theblock->instance->id, '/', $filename, true);
    $xmlurl .= '&unique='.uniqid(); // Avoid caching.

    $str .= "
            tl = Timeline.create(document.getElementById(\"timeline_{$htmlid}\"), bandInfos);
            Timeline.loadXML(\"$xmlurl\", function(xml, url) { eventSource.loadXML(xml, url); });
         }

         var resizeTimerID = null;
         function onResize() {
             if (resizeTimerID == null) {
                 resizeTimerID = window.setTimeout(function() {
                     resizeTimerID = null;
                     tl.layout();
                 }, 500);
             }
         }
        </script>
    ";

    $timelinegraphs[] = $htmlid;

    if ($return) {
        return $str;
    }
    echo $str;
}

/**
 * Generates a temp file that can be loaded into a timeline
 * date formats : Standard UTF timestamps. Ex: May 28 2006 09:00:00 GMT
 * Postgre to_char pattern : 'Mon DD YYYY HH24:MI:SS GMT'
 * Mysql formatting using date_format :  '%b %d %Y %H:%i:%s GMT'
 */
function timeline_generate_xml(&$theblock, $htmlid, &$data) {
    global $CFG, $COURSE, $USER;

    $fs = get_file_storage();

    $tmp = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n\n";
    $tmp .= "<data>\n";
    $colors = preg_split("/\r?\n/", $theblock->config->timelinecolors);
    $colorkeys = preg_split("/\r?\n/", $theblock->config->timelinecolorkeys);
    $theblock->normalize($colorkeys, $colors);
    $colouring = array_combine($colorkeys, $colors);
    if (!function_exists('mytrim')) {
        function mytrim(&$data) {
            $data = trim($data);
        }
    }
    array_walk($colorkeys, 'mytrim');

    foreach ($data as $d) {
        $eventattrs = array();
        if (empty($d)) {
            continue;
        }
        if (!empty($theblock->config->timelineeventstart) &&
                !empty($d->{$theblock->config->timelineeventstart})) {
            $eventattrs[] = "start=\"".timeline_date_convert($d->{$theblock->config->timelineeventstart}, $theblock)."\"";
        }
        if (!empty($theblock->config->timelineeventend) &&
                !empty($d->{$theblock->config->timelineeventend}) &&
                        $d->{$theblock->config->timelineeventend} != "1 Jan 1970 01:00:00 GMT") {
            $eventattrs[] = "end=\"".timeline_date_convert($d->{$theblock->config->timelineeventend}, $theblock)."\"";
        }
        if (!empty($theblock->config->timelineeventend) &&
                !empty($d->{$theblock->config->timelineeventend}) &&
                        $d->{$theblock->config->timelineeventend} != "1 Jan 1970 01:00:00 GMT") {
            $eventattrs[] = "isDuration=\"true\"";
        }
        if (!empty($theblock->config->timelineeventtitle) &&
                !empty($d->{$theblock->config->timelineeventtitle})) {
            $eventattrs[] = "title=\"".str_replace('&', '&amp;', $d->{$theblock->config->timelineeventtitle})."\"";
        }
        if (!empty($theblock->config->timelineeventlink) &&
                !empty($d->{$theblock->config->timelineeventlink})) {
            $eventattrs[] = "link=\"".$d->{$theblock->config->timelineeventlink}."\"";
        }
        if (!empty($theblock->config->timelinecolorfield) && !empty($d->{$theblock->config->timelinecolorfield})) {
            if (array_key_exists($d->{$theblock->config->timelinecolorfield}, $colouring)) {
                $eventattrs[] = "color=\"".$colouring[$d->{$theblock->config->timelinecolorfield}]."\"";

                /*
                if (file_exists($basefilepath.$d->{$theblock->config->timelinecolorfield}.".png")) {
                    $eventattrs[] = "icon=\"".$basefileurl.$d->{$theblock->config->timelinecolorfield}.".png\"";
                }
                */
            }
            $eventattrs[] = "textColor=\"#505050\"";
        }

        if (!empty($theblock->config->timelineeventdesc)) {
            $tmp .= '<event '.implode(' ', $eventattrs)." >";
            $tmp .= str_replace('&', '&amp;', $d->{$theblock->config->timelineeventtitle})."</event>\n";
        }
    }
    $tmp .= "</data>\n";

    $filerec = new StdClass;
    $blockcontext = context_block::instance($theblock->instance->id);
    $filerec->contextid = $blockcontext->id;
    $filerec->component = 'block_'.$theblock->instance->blockname;
    $filerec->filearea = 'timelineevents';
    $filerec->itemid = $theblock->instance->id;
    $filerec->filepath = '/';
    $filerec->filename = $htmlid.'_'.$USER->id.'.xml';

    $fs->create_file_from_string($filerec, $tmp);
}

function timeline_date_convert($date, $theblock) {

    // This might be for further needs.
    if ($theblock->config->target != 'moodle') {
        // We have an extra PostGre db, usually date are given in Postgre format.
        assert(true);
    }

    return $date;
}