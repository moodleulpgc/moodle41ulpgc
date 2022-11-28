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
defined('MOODLE_INTERNAL') || die;

$plugins = array(
        'sparklines' => array('files' => array('sparklines/sparklines.min.js')),
        'jqplotjquery' => array('files' => array('jqplot/jquery.min.js')),
        'jqplot'      => array(
            'files' => array(
                'jqplot/jquery.jqplot.min.js',
                'jqplot/excanvas.min.js',
                'jqplot/plugins/jqplot.dateAxisRenderer.min.js',
                'jqplot/plugins/jqplot.barRenderer.min.js',
                'jqplot/plugins/jqplot.highlighter.min.js',
                'jqplot/plugins/jqplot.canvasOverlay.min.js',
                'jqplot/plugins/jqplot.cursor.min.js',
                'jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
                'jqplot/plugins/jqplot.pointLabels.min.js',
                'jqplot/plugins/jqplot.logAxisRenderer.min.js',
                'jqplot/plugins/jqplot.canvasTextRenderer.min.js',
                'jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
                'jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js',
                'jqplot/plugins/jqplot.enhancedLegendRenderer.min.js',
                'jqplot/plugins/jqplot.pieRenderer.min.js',
                'jqplot/plugins/jqplot.donutRenderer.min.js'
            )
        ),
        'jqwidgets-core' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxcore.js',
                'jqwidgets/jqwidgets/jqxdraw.js',
                'jqwidgets/jqwidgets/styles/jqx.base.css'
            )
        ),
        'jqwidgets-bargauge' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxbargauge.js'
            )
        ),
        'jqwidgets-progressbar' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxprogressbar.js'
            )
        ),
        'jqwidgets-core' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxcore.js',
                'jqwidgets/jqwidgets/jqxdraw.js',
                'jqwidgets/jqwidgets/styles/jqx.base.css'
            )
        ),
        'jqwidgets-bargauge' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxbargauge.js'
            )
        ),
        'jqwidgets-progressbar' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxprogressbar.js'
            )
        ),
        'jqwidgets-bulletchart' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxcore.js',
                'jqwidgets/jqwidgets/jqxdata.js',
                'jqwidgets/jqwidgets/jqxtooltip.js',
                'jqwidgets/jqwidgets/jqxbulletchart.js',
                'jqwidgets/jqwidgets/styles/jqx.base.css'
            )
        ),
        'jqwidgets-barchart' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxcore.js',
                'jqwidgets/jqwidgets/jqxdraw.js',
                'jqwidgets/jqwidgets/jqxchart.core.js',
                'jqwidgets/jqwidgets/jqxdata.js',
                'jqwidgets/jqwidgets/styles/jqx.base.css'
            )
        ),
        'jqwidgets-switchbutton' => array(
            'files' => array(
                'jqwidgets/jqwidgets/jqxcore.js',
                'jqwidgets/jqwidgets/jqxswitchbutton.js',
                'jqwidgets/jqwidgets/jqxcheckbox.js',
                'jqwidgets/jqwidgets/styles/jqx.base.css'
            )
        )
);
