<?php
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

defined('MOODLE_INTERNAL') || die();

$config = get_config('local_vflibs');

if (!class_exists('pdf')) {

if (!class_exists('VFTCPDF')) {
    if (!empty($config->enablelocalpdf)) {
        require_once($CFG->dirroot.'/local/vflibs/vftcpdf/tcpdf.php');
        require_once($CFG->dirroot.'/local/vflibs/vftcpdf/vfclassrouter.php');
    } else {
        require_once($CFG->dirroot.'/lib/pdflib.php');
        require_once($CFG->dirroot.'/local/vflibs/vftcpdf/standardclassrouter.php');
    }
}

function tcpdf_decode_html_color($htmlcolor, $reverse = false) {
    if (preg_match('/#([0-9A-Fa-f][0-9A-Fa-f])([0-9A-Fa-f][0-9A-Fa-f])([0-9A-Fa-f][0-9A-Fa-f])/', $htmlcolor, $matches)) {
        $r = hexdec($matches[1]);
        $g = hexdec($matches[2]);
        $b = hexdec($matches[3]);
        return array($r, $g, $b);
    }
    if ($reverse) {
        return array(255 - $r,255 - $g,255 - $b);
    }
    return array(0,0,0);
}

/**
 * Makes physical path accessible for document integration 
 */
function tcpdf_get_path_from_hash($contenthash) {
    global $CFG;

    // Detect is local file or not.
    $l1 = $contenthash[0].$contenthash[1];
    $l2 = $contenthash[2].$contenthash[3];
    return "$l1/$l2";
}


function tcpdf_add_standard_plugin_settings(&$settings, $plugin, $defaultdocument = '') {
    $key = $plugin.'/pdfgeneration';
    $settings->add(new admin_setting_heading($key, get_string('configpdfgeneration', 'local_vflibs'), ''));

    /*
     * If the calling plugin has an alternate rendering strategy, such as other document formats or an
     * online web rendering, it may use this setting to know if PDF docgen is available.
     */
    $key = $plugin.'/pdfenabled';
    $label = get_string('configpdfenabled', 'local_vflibs');
    $desc = get_string('configpdfenabled_desc', 'local_vflibs');
    $default = 0;
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, $default));

    /*
     * A Generic JSON formatted has array of properties. If JSON is not correctly parsed and
     * the content is not empty, an alternative INI like key=value list will be parsed.
     */
    $key = $plugin.'/printconfig';
    $label = get_string('configprintconfig', 'local_vflibs');
    $desc = get_string('configprintconfig_desc', 'local_vflibs');
    $default = '';
    $settings->add(new admin_setting_configtextarea($key, $label, $desc, $default));

    /*
     * Stores the default HTML template for rendering the document content
     * using writeHTML() primitive. the calling plugin may have it's own documenttype
     * specific templates and at least will have the default available in its own plugin scope.
     */
    $key = $plugin.'/defaulttemplate';
    $label = get_string('configdefaulttemplate', 'local_vflibs');
    $desc = get_string('configdefaulttemplate_desc', 'local_vflibs');
    $default = $defaultdocument;
    $settings->add(new admin_setting_configtextarea($key, $label, $desc, $default));

    /*
     * A watermark image as a JPEG image that will be printed in transparency in the background.
     * Note that watermark printing alters some HTML rendering functions such as TABLE cell backgrounds.
     */
    $key = $plugin.'/docwatermarkimage';
    $label = get_string('configdocwatermarkimage', 'local_vflibs');
    $desc = get_string('configdocwatermarkimage_desc', 'local_vflibs');
    $settings->add(new admin_setting_configstoredfile($key, $label, $desc, 'docwatermark'));

    /*
     * A simple logo image to print somewhere in the page. Will be usually an alternative
     * to a full width header image.
     */
    $key = $plugin.'/doclogoimage';
    $label = get_string('configdoclogoimage', 'local_vflibs');
    $desc = get_string('configdoclogoimage_desc', 'local_vflibs');
    $settings->add(new admin_setting_configstoredfile($key, $label, $desc, 'doclogo'));

    /*
     * A full width header image to print as header. Will print on all pages unless
     * the innerheader image is set. In that case will print only on the first page.
     */
    $key = $plugin.'/docheaderimage';
    $label = get_string('configdocheaderimage', 'local_vflibs');
    $desc = get_string('configdocheaderimage_desc', 'local_vflibs');
    $settings->add(new admin_setting_configstoredfile($key, $label, $desc, 'docheader'));

    /*
     * A full width header image to print as header in the inner pages. Will print on all pages unless
     * the first page.
     */
    $key = $plugin.'/docinnerheaderimage';
    $label = get_string('configdocinnerheaderimage', 'local_vflibs');
    $desc = get_string('configdocinnerheaderimage_desc', 'local_vflibs');
    $settings->add(new admin_setting_configstoredfile($key, $label, $desc, 'docinnerheader'));

    /*
     * A full width header image to print as header. Will print on all pages unless
     * the innerheader image is set. In that case will print only on the first page.
     */
    $key = $plugin.'/docfooterimage';
    $label = get_string('configdocfooterimage', 'local_vflibs');
    $desc = get_string('configdocfooterimage_desc', 'local_vflibs');
    $settings->add(new admin_setting_configstoredfile($key, $label, $desc, 'docfooter'));

    /*
     * A full width header image to print as header. Will print on all pages unless
     * the innerheader image is set. In that case will print only on the first page.
     */
    $key = $plugin.'/docinnerfooterimage';
    $label = get_string('configdocinnerfooterimage', 'local_vflibs');
    $desc = get_string('configdocinnerfooterimage_desc', 'local_vflibs');
    $settings->add(new admin_setting_configstoredfile($key, $label, $desc, 'docinnerfooter'));
}

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 */
function tcpdf_print_text($pdf, $x, $y, $align, $font = 'freeserif', $style, $size = 10, $text) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 0, 0, true, $align);
}

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 */
function tcpdf_print_textbox($pdf, $w, $x, $y, $align, $font = 'freeserif', $style, $size = 10, $text) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell($w, 0, '', '', $text, 0, 2, 0, true, $align);
}

/**
 * Creates rectangles for line border for A4 size paper.
 *
 * @param stdClass $pdf
 */
function tcpdf_draw_frame($pdf) {

    $config = get_config('local_shop');
    $printconfig = json_decode($config->printconfig);

    if (@$printconfig->bordercolor > 0) {
        if ($printconfig->bordercolor == 1) {
            $color = array(0, 0, 0); // Black.
        }
        if ($printconfig->bordercolor == 2) {
            $color = array(153, 102, 51); // Brown.
        }
        if ($printconfig->bordercolor == 3) {
            $color = array(0, 51, 204); // Blue.
        }
        if ($printconfig->bordercolor == 4) {
            $color = array(0, 180, 0); // Green.
        }

        // create outer line border in selected color
        $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
        $pdf->Rect(10, 10, 190, 277);
        // create middle line border in selected color
        $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
        $pdf->Rect(13, 13, 184, 271);
        // create inner line border in selected color
        $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
        $pdf->Rect(16, 16, 178, 265);
    }
}

/**
 * Creates rectangles for line border for letter size paper.
 *
 * @param stdClass $pdf
 */
function tcpdf_draw_frame_letter($pdf) {

    $config = get_config('local_shop');
    $printconfig = json_decode($config->printconfig);

    if (@$printconfig->bordercolor > 0) {
        if ($printconfig->bordercolor == 1) {
            $color = array(0, 0, 0); // Black.
        }
        if ($printconfig->bordercolor == 2) {
            $color = array(153, 102, 51); // Brown.
        }
        if ($printconfig->bordercolor == 3) {
            $color = array(0, 51, 204); // Blue.
        }
        if ($printconfig->bordercolor == 4) {
            $color = array(0, 180, 0); // Green.
        }
        // Create outer line border in selected color.
        $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
        $pdf->Rect(25, 20, 561, 751);
        // Create middle line border in selected color.
        $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
        $pdf->Rect(40, 35, 531, 721);
        // Create inner line border in selected color.
        $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
        $pdf->Rect(51, 46, 509, 699);
    }
}

/**
 * Prints border images from the borders folder in PNG or JPG formats.
 *
 * @param stdClass $pdf the pdf document
 * @param stdClass $type the image type to print
 * @param int $x x position
 * @param int $y y position
 * @param int $w the width
 * @param int $h the height
 */
function tcpdf_print_image($pdf, $context, $component, $filearea, $itemid = 0, $x, $y, $w, $h) {
    global $CFG;

    $fs = get_file_storage();

    $files = $fs->get_area_files($context->id, 'local_shop', 'docheader', 0, 'itemid, filepath, filename', false);
    $f = array_pop($files);
    if ($f) {
        $filepathname = $f->get_contenthash();
    } else {
        return;
    }

    $uploadpath = tcpdf_path_from_hash($filepathname).'/'.$filepathname;

    // Uploaded path will superseed.
    if (file_exists($uploadpath)) {
        $pdf->Image($uploadpath, $x, $y, $w, $h);
    } else if (file_exists($defaultpath)) {
        $pdf->Image($path, $x, $y, $w, $h);
    }
}

function tcpdf_print_qrcode($pdf, $code, $targeturl, $x, $y) {
    global $CFG;

    $style = array(
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => array(255, 255, 255), // False.
            'module_width' => 1, // Width of a single module in points.
            'module_height' => 1 // Height of a single module in points.
    );

    $pdf->write2DBarcode(''.$targeturl, 'QRCODE,H', $x, $y, 35, 35, $style, 'N');
}

/**
 * Retrieve path from local shop file hash
 *
 * @param array $contenthash
 * @return string the path
 */
function tcpdf_path_from_hash($contenthash, $fullpath = false) {
    global $CFG;

    $l1 = $contenthash[0].$contenthash[1];
    $l2 = $contenthash[2].$contenthash[3];
    if ($fullpath) {
        return "$l1/$l2";
    } else {
        return $CFG->dataroot."/filedir/$l1/$l2";
    }
}

}