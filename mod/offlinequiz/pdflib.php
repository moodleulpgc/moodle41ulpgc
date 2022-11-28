<?php
// This file is part of mod_offlinequiz for Moodle - http://moodle.org/
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
 * Creates the PDF forms for offlinequizzes
 *
 * @package       mod
 * @subpackage    offlinequiz
 * @author        Juergen Zimmer <zimmerj7@univie.ac.at>
 * @copyright     2015 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @since         Moodle 2.2+
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->dirroot . '/lib/pdflib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/filter/tex/filter.php');
require_once($CFG->dirroot . '/mod/offlinequiz/html2text.php');
require_once($CFG->dirroot . '/mod/offlinequiz/documentlib.php');

define('LOGO_MAX_ASPECT_RATIO', 3.714285714);

class offlinequiz_barcodewriter {
    /**
     *
     * @param pdf $pdf
     * @param int $barcode
     * @param int $x
     * @param int $y
     */
    public static function print_barcode($pdf, $barcode, $x, $y) {
        // Print bar code for page.
        $value = substr('000000000000000000000000'.base_convert($barcode,  10,  2), -25);
        $pdf->Rect($x, $y, 0.2, 3.5, 'F');
        $pdf->Rect($x, $y, 0.7, 0.2, 'F');
        $pdf->Rect($x, $y + 3.5, 0.7, 0.2, 'F');
        $x += 0.7;
        for ($i = 0; $i < 25; $i++) {
            if ($value[$i] == '1') {
                $pdf->Rect($x, $y, 0.7, 3.5, 'F');
                $pdf->Rect($x, $y, 1.2, 0.2, 'F');
                $pdf->Rect($x, $y + 3.5, 1.2, 0.2, 'F');
                $x += 1;
            } else {
                $pdf->Rect($x, $y, 0.2, 3.5, 'F');
                $pdf->Rect($x, $y, 0.7, 0.2, 'F');
                $pdf->Rect($x, $y + 3.5, 0.7, 0.2, 'F');
                $x += 0.7;
            }
        }
    }
}

class offlinequiz_pdf extends pdf
{
    /**
     * Containing the current page buffer after checkpoint() was called.
     */
    private $checkpoint;

    public function checkpoint() {
        $this->checkpoint = $this->getPageBuffer($this->page);
    }

    public function backtrack() {
        $this->setPageBuffer($this->page, $this->checkpoint);
    }

    public function is_overflowing() {
        return $this->y > $this->PageBreakTrigger;
    }

    public function set_title($newtitle) {
        $this->title = $newtitle;
    }
    
}
class offlinequiz_question_pdf extends offlinequiz_pdf
{
    private $tempfiles = array();

    /**
     * (non-PHPdoc)
     * @see TCPDF::Header()
     */
    // @codingStandardsIgnoreLine  This function name is not moodle-standard but I need to overwrite TCPDF
    public function Header() {
        $this->SetFont('FreeSans', 'I', 8);
        // Title.
        $this->Ln(10); // ecastro ULPGC
        if (!empty($this->title)) {
            $this->Cell(0, 10, $this->title, 0, 0, 'C');
        }
        $this->Rect(15, 25, 175, 0.3, 'F');
        // Line break.
        $this->Ln(10); // ecastro ULPGC
        $this->diskcache = false;
    }

    /**
     * (non-PHPdoc)
     * @see TCPDF::Footer()
     */
    // @codingStandardsIgnoreLine  This function name is not moodle-standard but I need to overwrite TCPDF
    public function Footer() {
        // Position at 2.5 cm from bottom.
        $this->SetY(-25);
        $this->SetFont('FreeSans', 'I', 8);
        // Page number.
        $this->Cell(0, 10, offlinequiz_str_html_pdf(get_string('page')) . ' ' . $this->getAliasNumPage() .
                '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

class offlinequiz_answer_pdf extends offlinequiz_pdf {
    public $groupid = 0;

    /**
     * (non-PHPdoc)
     * @see TCPDF::Header()
     */
    // @codingStandardsIgnoreLine  This function name is not moodle-standard but I need to overwrite TCPDF
    public function Header() {
        global $CFG;

        $offlinequizconfig = get_config('offlinequiz');

        $letterstr = 'ABCDEF';

        $logourl = trim($offlinequizconfig->logourl);
        if (!empty($logourl)) {
            $aspectratio = $this->get_logo_aspect_ratio($logourl);
            if ($aspectratio < LOGO_MAX_ASPECT_RATIO) {
                $newlength = 54 * $aspectratio / LOGO_MAX_ASPECT_RATIO;
                $this->IMAGE($logourl, 133, 5, $newlength, 0); // ecastro ULPGC
            } else {
                $this->Image($logourl, 133, 5, 54, 0);  // ecastro ULPGC
            }

        }
        // Print the top left fixation cross.
        $this->Line(11, 12, 14, 12);
        $this->Line(12.5, 10.5, 12.5, 13.5);
        $this->Line(193, 12, 196, 12);
        $this->Line(194.5, 10.5, 194.5, 13.5);
        
        $this->SetFont('FreeSans', 'B', 14);
        // ecastro ULPGC
        $this->SetXY(15, 10);
        $this->Cell(30, 2, offlinequiz_str_html_pdf($this->shortname), 0, 2, 'L');
        $this->SetFont('FreeSans', 'B', 9);
        $this->MultiCell(119, 6, offlinequiz_str_html_pdf($this->fullname), 0, 'L', 0);
        
        $this->SetXY(135, 18);
        $this->Cell(60, 3, offlinequiz_str_html_pdf(get_string('answerform',  'offlinequiz')), 0, 2, 'C');
        $this->SetFont('FreeSans', '', 8);

        // ecastro
        $this->Cell(60, 3, offlinequiz_str_html_pdf(get_string('forautoanalysis',  'offlinequiz')), 0, 1, 'C');

        // Rectangle for the teachers to sign.
        $this->SetXY(108, 26);
        $this->Cell(24, 3, ' '.offlinequiz_str_html_pdf(get_string('signature',  'offlinequiz')), 0, 0, 'C');
        $this->Line(108, 26, 132, 26);                                 
        $this->Line(108, 50, 132, 50);
        $this->Line(108, 26, 108, 50);
        $this->Line(132, 26, 132, 50);
        $this->Line(127, 45, 132, 45);
        $this->Line(127, 45, 127, 50);
        $this->SetXY(108, 46);
        $this->Cell(18, 3, offlinequiz_str_html_pdf(get_string('invigilator',  'offlinequiz')), 0, 0, 'R');
        
        $this->SetXY(15, 26);
        $this->Cell(90, 8, ' '.offlinequiz_str_html_pdf(get_string('firstname')).":", 1, 1, 'L');
        $this->Cell(90, 8, ' '.offlinequiz_str_html_pdf(get_string('lastname')).":", 1, 1, 'L');
        $name = ' '.offlinequiz_str_html_pdf(get_string('name',  'offlinequiz')).': ';
        $width = $this->GetStringWidth($name) + 1 ;
        $this->Cell(90, 8, $name, 1, 0, 'L');
        $this->SetX(15 + $width);
        $this->SetFont('FreeSans', 'B', 9);
        $this->Cell(90 - $width, 8, $this->title, 0, 1, 'L');
        $this->SetFont('FreeSans', '', 8);
        $this->Ln(6);
        //44431 Simulación, Optimización, Instrumentación y Control de Procesos Químicos
        //41536 Tratamiento Jurídico de las Relaciones Comerciales: desde el Derecho Romano al Derecho Actual
        $this->Cell(20, 7, offlinequiz_str_html_pdf(get_string('group', 'offlinequiz')).":", 0, 0, 'L'); // ecastro ULPGC
        //ecastro 
        /*
        // original
        $this->Cell(90, 7, ' '.offlinequiz_str_html_pdf(get_string('firstname')).":", 1, 0, 'L');
        $this->Cell(29, 7, ' '.offlinequiz_str_html_pdf(get_string('invigilator',  'offlinequiz')), 0, 1, 'C');
        $this->Cell(90, 7, ' '.offlinequiz_str_html_pdf(get_string('lastname')).":", 1, 1, 'L');
        $this->Cell(90, 7, ' '.offlinequiz_str_html_pdf(get_string('signature',  'offlinequiz')).":", 1, 1, 'L');
        $this->Ln(5);
        $this->Cell(20, 7, offlinequiz_str_html_pdf(get_string('group', 'offlinequiz')).":", 0, 0, 'L');
        $this->SetXY(34.4,  57.4);
        */

        $x= 34.4; 
        $y = 57.4;
        $this->SetXY($x, $y);
        // Print boxes for groups.
        for ($i = 0; $i <= 5; $i++) {
            $this->Cell(6,  3.5,  $letterstr[$i], 0, 0, 'R');
            $this->Cell(0.85,  1, '', 0, 0, 'R');
            $this->Rect($this->GetX(),  $this->GetY(),  3.5,  3.5);
            $this->Cell(2.7,  1, '', 0, 0, 'C');
            if (!empty($this->group) and $letterstr[$i] == $this->group) {
                $this->Image("$CFG->dirroot/mod/offlinequiz/pix/kreuz.gif", $this->GetX() - 2.75,  $this->Gety() + 0.15,  3.15,  0);
            }
        }
        
        $this->SetFont('FreeSans', 'B', 32);
        // position right to block, and baseline with boxes, taking into account font size in points, 72 points/inch
        $this->SetXY($x -16*25.4/72,  $y + 3.5 - 32*25.4/72);
        $this->Cell(6,  6.35,  $this->group, 0, 0, 'L');
        
        // Print instructions labels
        $this->SetFont('FreeSans', '', 8);
        $this->SetXY(15,  $y);
        $this->Ln(8); 
        //instruction1 strings with <br /> AND LF are interpeted as with two LF, two lines.
        $this->MultiCell(90, 2, offlinequiz_str_html_pdf(get_string('instruction1',  'offlinequiz')), 0, 'L');
        $this->Ln(2);
        $this->MultiCell(90, 2, offlinequiz_str_html_pdf(get_string('instruction2',  'offlinequiz')), 0, 'L');
        $this->Ln(2);
        $this->MultiCell(115, 3, offlinequiz_str_html_pdf(get_string('instruction3',  'offlinequiz')), 0, 'L');
        
        // ecatro ULPGC
        // now write the example markers 
        $x= 106;
        $y = 72;
        $this->Image("$CFG->dirroot/mod/offlinequiz/pix/kreuz.gif",  $x,  $y,  3.15,  0);   
        $this->Rect($x,  $y,  3.5,  3.5);
        $this->SetXY($x + 5, $y); 
        $this->Cell(20, 2, offlinequiz_str_html_pdf(get_string('markcorrect', 'offlinequiz')), 0, 0, 'L');

        $y = $y + 12;
        $this->Image("$CFG->dirroot/mod/offlinequiz/pix/ausstreichen.jpg", $x,  $y,  4.1,  0);         
        $this->Rect($x,  $y,  3.5,  3.5);
        $this->SetXY($x + 5, $y); 
        $this->Cell(20, 2, offlinequiz_str_html_pdf(get_string('markinvalid', 'offlinequiz')), 0, 0, 'L');
        $this->Ln(2);
        // ecastro ULPGC


        
        
        $this->SetFont('FreeSans', 'B', 8);

        // ecastro ULPGC allow use two ID types
        $offlinequizconfig->allowtwoidnumbers = 1;
        if($offlinequizconfig->allowtwoidnumbers) {
            $x= 138 + ($offlinequizconfig->ID_digits) * 6.5;
            $y= 33;
            $this->Rect($x, $y, 3.5, 3.5);        
            $this->SetXY($x + 4, $y);
            $this->Cell(10, 3, offlinequiz_str_html_pdf(get_string('useridnumber',  'offlinequiz')), 
                        0, 0, 'L');  // Box for ID number type.
            $y= 38;
            $this->Rect($x, $y, 3.5, 3.5);
            $this->SetXY($x + 4, $y);
            $this->Cell(10, 3, offlinequiz_str_html_pdf(get_string('usermid',  'offlinequiz')), 
                        0, 0, 'L');  // Box for ID number type.
        }
        // ecastro ULPGC allow use two ID types

        $this->SetFont('FreeSans', 'B', 10);
        $this->SetXY(137, 27);
        $this->Cell($offlinequizconfig->ID_digits * 6.5, 7,
                    offlinequiz_str_html_pdf(get_string('idnumber',  'offlinequiz')), 0, 1, 'C');
        $this->SetXY(137, 34);
        $this->Cell($offlinequizconfig->ID_digits * 6.5, 7, '', 1, 1, 'C');  // Box for ID number.

        for ($i = 1; $i < $offlinequizconfig->ID_digits; $i++) {      // Little lines to separate the digits.
            $this->Line(137 + $i * 6.5, 39, 137 + $i * 6.5, 41);
        }

        $this->SetDrawColor(150);
        //$this->Line(137,  47.7,  138 + $offlinequizconfig->ID_digits * 6.5,  47.7);  // Line to sparate 0 from the other.
        $this->Line(137,  48,  138 + $offlinequizconfig->ID_digits * 6.5,  48);  // Line to sparate 0 from the other. // ecastro ULPGC
        $this->SetDrawColor(0);

        // Print boxes for the user ID number.
        $this->SetFont('FreeSans', '', 12);
        for ($i = 0; $i < $offlinequizconfig->ID_digits; $i++) {
            $x = 139 + 6.5 * $i;
            for ($j = 0; $j <= 9; $j++) {
                $y = 44 + $j * 6;
                $this->Rect($x, $y, 3.5, 3.5);
            }
        }

        // Print the digits for the user ID number.
        $this->SetFont('FreeSans', '', 10);
        for ($y = 0; $y <= 9; $y++) {
            $this->SetXY(134, ($y * 6 + 44));
            $this->Cell(3.5, 3.5, "$y", 0, 1, 'C');
            $this->SetXY(138 + $offlinequizconfig->ID_digits * 6.5, ($y * 6 + 44));
            $this->Cell(3.5, 3.5, "$y", 0, 1, 'C');
        }

        $this->Ln();
        $this->Rect(10, $this->GetY() - 3.5, 190, 0.5, 'F');  // ecastro ULPGC                 // Bold line on bottom.        
    }

    private function get_logo_aspect_ratio($logourl) {
        list($originalwidth, $originalheight) = getimagesize($logourl);
        return $originalwidth / $originalheight;
    }


    /**
     * (non-PHPdoc)
     * @see TCPDF::Footer()
     */
    // @codingStandardsIgnoreLine  This function name is not moodle-standard but I need to overwrite TCPDF
    public function Footer() {
        $letterstr = ' ABCDEF';

        $footery = 280 + 0; // ecastro ULPGC
       
        //$this->Line(11, 285, 14, 285);
        $y = $footery + 5;
        $this->Line(11, $y, 14, $y);
        
        //$this->Line(12.5, 283.5, 12.5, 286.5);
        $y = $footery + 3.5;
        $this->Line(12.5, $y, 12.5, $y + 3);
        
        //$this->Line(193, 285, 196, 285);
        $y = $footery + 5;
        $this->Line(193, $y, 196, $y);
        
        //$this->Line(194.5, 283.5, 194.5, 286.5);
        $y = $footery + 3.5;
        $this->Line(194.5, $y, 194.5, $y + 3 );

        //$this->Rect(192, 282.5, 2.5, 2.5, 'F');                // Flip indicator.
        $y = $footery + 2.5;
        $this->Rect(192, $y, 2.5, 2.5, 'F');                // Flip indicator.
        
        //$this->Rect(15, 281, 174, 0.5, 'F');                   // Bold line on bottom.
        $y = $footery ;
        $this->Rect(10, $y, 190, 0.5, 'F');                   // Bold line on bottom.

        
        // Position at x mm from bottom.
        //$this->SetY(-20);
        $this->SetY(-15);
        $yset = $this->GetY();
        $this->SetFont('FreeSans', '', 8);
        $this->Cell(10, 4, $this->formtype, 1, 0, 'C');

        // ID of the offline quiz.
        $this->Cell(15, 4, substr('0000000'.$this->offlinequiz, -7), 1, 0, 'C');

        // Letter for the group.
        $this->Cell(10, 4, $letterstr[$this->groupid], 1, 0, 'C');

        // ID of the user who created the form.
        $this->Cell(15, 4, substr('0000000'.$this->userid, -7), 1, 0, 'C');

        // Name of the offline-quiz.
        $title = $this->title;
        $width = 100;

        while ($this->GetStringWidth($title) > ($width - 1)) {
            $title = mb_substr($title,  0,  mb_strlen($title) - 1);
        }
        $this->Cell($width, 4, $title, 1, 0, 'C');

        $y = $this->GetY();
        $x = $this->GetX();
        
        
        
        // Print bar code for page.
        offlinequiz_barcodewriter::print_barcode($this, $this->PageNo(), $x, $y);

        $this->Rect($x, $y, 0.2, 3.7, 'F');

        // Page number.
        $this->Ln(3);
        $this->SetFont('FreeSans', 'I', 8);
        $this->Cell(0, 10, offlinequiz_str_html_pdf(get_string('page') . ' ' . $this->getAliasNumPage() . '/' .
                $this->getAliasNbPages()), 0, 0, 'C');
    }
}

class offlinequiz_participants_pdf extends offlinequiz_pdf {
    public $listno;

    /**
     * (non-PHPdoc)
     * @see TCPDF::Header()
     */
    // @codingStandardsIgnoreLine  This function name is not moodle-standard but I need to overwrite TCPDF
    public function Header() {
        global $CFG,  $DB;

        $this->Line(11,  12,  14, 12);
        $this->Line(12.5, 10.5, 12.5, 13.5);
        $this->Line(193, 12, 196, 12);
        $this->Line(194.5, 10.5, 194.5, 13.5);

        $this->Line(12.5, 18, 18.5, 12);

        $this->SetFont('FreeSans', 'I', 8);

        // Title.
        $x = $this->GetX();
        $y = $this->GetY();
        $this->SetXY($x + 9, $y + 5.5);
        if (!empty($this->title)) {
            $this->Cell(110, 15, $this->title, 0, 1, 'L');
        }

        $this->SetXY($x, $y);
        $this->Rect(15, 23, 175, 0.3, 'F');
        // Line break.
        $this->Ln(26);

        $this->Cell(10, 3.5, '', 0, 0, 'C');
        $this->Cell(3.5, 3.5, '', 1, 0, 'C');
        $this->Image($CFG->dirroot . '/mod/offlinequiz/pix/kreuz.gif', $this->GetX() - 3.3, $this->Gety() + 0.2, 3.15, 0);
        $this->SetFont('FreeSans', 'B', 10);
        $this->Cell(31, 3.5, "", 0, 0, 'L');
        $this->Cell(55, 3.5, offlinequiz_str_html_pdf(get_string('lastname')), 0, 0, 'L');
        $this->Cell(60, 3.5, offlinequiz_str_html_pdf(get_string('firstname')), 0, 1, 'L');
        $this->Rect(15, ($this->GetY() + 1), 175, 0.3, 'F');
        $this->Ln(4.5);
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Rect(145, 8, 25, 13);     // Square for the teachers to sign.

        $this->SetXY(145.5, 6.5);
        $this->SetFont('FreeSans', '', 8);
        $this->Cell(29, 7, get_string('invigilator', 'offlinequiz'), 0, 0, 'L');

        $this->SetXY($x, $y);
    }

    /**
     * (non-PHPdoc)
     * @see TCPDF::Footer()
     */
    // @codingStandardsIgnoreLine  This function name is not moodle-standard but I need to overwrite TCPDF
    public function Footer() {
        $this->Line(11, 285, 14, 285);
        $this->Line(12.5, 283.5, 12.5, 286.5);
        $this->Line(193, 285, 196, 285);
        $this->Line(194.5, 283.5, 194.5, 286.5);
        $this->Rect(192, 282.5, 2.5, 2.5, 'F');                // Flip indicator.
        $this->Rect(15, 281, 175, 0.5, 'F');

        // Position at 1.7 cm from bottom.
        $this->SetY(-17);
        // FreeSans italic 8.
        $this->SetFont('FreeSans', 'I', 8);
        // Page number.
        $this->Cell(0, 10,
                    offlinequiz_str_html_pdf(get_string('page') . ' ' .
                                             $this->getAliasNumPage().'/' . $this->getAliasNbPages() .
                                             ' ( '.$this->listno.' )'), 0, 0, 'C');
        // Print barcode for list.
        $y = $this->GetY() - 5;
        $x = 170;
        offlinequiz_barcodewriter::print_barcode($this, $this->PageNo(), $x, $y);

        $this->Rect($x, $y, 0.2, 3.7, 'F');
    }
}

/**
 * Returns a rendering of the number depending on the answernumbering format.
 *
 * @param int $num The number, starting at 0.
 * @param string $style The style to render the number in. One of the
 * options returned by {@link qtype_multichoice:;get_numbering_styles()}.
 * @return string the number $num in the requested style.
 */
function number_in_style($num, $style) {
        return chr(ord('a') + $num);
}

/**
 * prints the question to the pdf
 */
function offlinequiz_print_question_html($pdf, $question, $texfilter, $trans, $offlinequiz) {
    $pdf->checkpoint();

    $questiontext = $question->questiontext;

    // Filter only for tex formulas.
    if (!empty($texfilter)) {
        $questiontext = $texfilter->filter($questiontext);
    }

    // Remove all HTML comments (typically from MS Office).
    $questiontext = preg_replace("/<!--.*?--\s*>/ms", "", $questiontext);

    // Remove <font> tags.
    $questiontext = preg_replace("/<font[^>]*>[^<]*<\/font>/ms", "", $questiontext);

    // Remove <script> tags that are created by mathjax preview.
    $questiontext = preg_replace("/<script[^>]*>[^<]*<\/script>/ms", "", $questiontext);

    // Remove all class info from paragraphs because TCPDF won't use CSS.
    $questiontext = preg_replace('/<p[^>]+class="[^"]*"[^>]*>/i', "<p>", $questiontext);

    $questiontext = $trans->fix_image_paths($questiontext, $question->contextid, 'questiontext', $question->id,
        1, 300, $offlinequiz->disableimgnewlines);

    $html = '';

    $html .= $questiontext . '<br/><br/>';
    return $html;
}

function offlinequiz_get_answers_html($offlinequiz, $templateusage,
    $slot, $question, $texfilter, $trans, $correction) {
    $html = '';
    $slotquestion = $templateusage->get_question ( $slot );
    // There is only a slot for multichoice questions.
    $attempt = $templateusage->get_question_attempt($slot);
    $order = $slotquestion->get_order($attempt);  // Order of the answers.

    foreach ($order as $key => $answer) {
        $answertext = $question->options->answers[$answer]->answer;
        // Filter only for tex formulas.
        if (!empty($texfilter)) {
            $answertext = $texfilter->filter($answertext);
        }

        // Remove all HTML comments (typically from MS Office).
        $answertext = preg_replace("/<!--.*?--\s*>/ms", "", $answertext);
        // Remove all paragraph tags because they mess up the layout.
        $answertext = preg_replace("/<p[^>]*>/ms", "", $answertext);
        // Remove <script> tags that are created by mathjax preview.
        $answertext = preg_replace("/<script[^>]*>[^<]*<\/script>/ms", "", $answertext);
        $answertext = preg_replace("/<\/p[^>]*>/ms", "", $answertext);
        $answertext = $trans->fix_image_paths($answertext, $question->contextid, 'answer', $answer,
            1, 300, $offlinequiz->disableimgnewlines);

        if ($correction) {
            if ($question->options->answers[$answer]->fraction > 0) {
                $html .= '<span style="background-color: #ffff00;">';
            }
            // ecastro ULPGC reduce print load for revision. Only correct, right, options graded.
            if($question->options->answers[$answer]->fraction != 0) {
                $answertext .= "  (".round($question->options->answers[$answer]->fraction * 100)."%)©";
            }
            // ecastro ULPGC 
        }

        $html .= number_in_style($key, $question->options->answernumbering) . ') &nbsp; ';
        $html .= $answertext;

        if ($correction) {
            if ($question->options->answers[$answer]->fraction > 0) {
                $html .= '</span>';
            }
        }

        $html .= "<br/>\n";
    }

    $infostring = offlinequiz_get_question_infostring($offlinequiz, $question);
    if ($infostring) {
        $html .= '<br/>' . $infostring . '<br/>';
    }
    return $html;
}

function offlinequiz_write_question_to_pdf($pdf, $fontsize, $questiontype, $html, $number) {

    $pdf->writeHTMLCell(165,  round($fontsize / 2), $pdf->GetX(), $pdf->GetY() + 0.3, $html);
    $pdf->Ln();

    if ($pdf->is_overflowing()) {
        $pdf->backtrack();
        $pdf->AddPage();
        $pdf->Ln(14);

        // Print the question number and the HTML string again on the new page.
        if ($questiontype == 'multichoice' || $questiontype == 'multichoiceset') {
            $pdf->SetFont('FreeSans', 'B', $fontsize);
            $pdf->Cell(4, round($fontsize / 2), "$number)  ", 0, 0, 'R');
            $pdf->SetFont('FreeSans', '', $fontsize);
        }

        $pdf->writeHTMLCell(165,  round($fontsize / 2), $pdf->GetX(), $pdf->GetY() + 0.3, $html);
        $pdf->Ln();
    }
}
/**
 * Generates the PDF question/correction form for an offlinequiz group.
 *
 * @param question_usage_by_activity $templateusage the template question  usage for this offline group
 * @param object $offlinequiz The offlinequiz object
 * @param object $group the offline group object
 * @param int $courseid the ID of the Moodle course
 * @param object $context the context of the offline quiz.
 * @param boolean correction if true the correction form is generated.
 * @return stored_file instance, the generated PDF file.
 */
function offlinequiz_create_pdf_question(question_usage_by_activity $templateusage, $offlinequiz, $group,
                                         $courseid, $context, $correction = false) {
    global $CFG, $DB, $OUTPUT;

    $letterstr = 'abcdefghijklmnopqrstuvwxyz';
    $groupletter = strtoupper($letterstr[$group->groupnumber - 1]);

    $coursecontext = context_course::instance($courseid);
    
    $pdf = new offlinequiz_question_pdf('P', 'mm', 'A4');
    $trans = new offlinequiz_html_translator();
    offlinequiz_set_course_info($courseid, $pdf); // ecastro ULPGC
    $title = offlinequiz_str_html_pdf($offlinequiz->name);
    if (!empty($offlinequiz->time)) {
        $title .= ": ".offlinequiz_str_html_pdf(userdate($offlinequiz->time));
    }
    $title .= ",  ".offlinequiz_str_html_pdf(get_string('group', 'offlinequiz')." $groupletter");
    $pdf->set_title($title);
    $pdf->SetMargins(15, 28, 15);
    $pdf->SetAutoPageBreak(false, 25);
    $pdf->AddPage();

    // Print title page.
    $pdf->SetFont('FreeSans', 'B', 14);
    $pdf->Ln(4);
    
    // ecastro ULPGC print a common title page
    $pdf->group = $groupletter;
    $pdf->groupid = $group->groupnumber;
    $pdf->offlinequiz = $offlinequiz->id;    
    
    $sheet = get_string('questionsheet', 'offlinequiz');
    if ($correction) {
        $sheet = get_string('correctionsheet', 'offlinequiz');
    }
    
    $pdf->Cell(0, 4, offlinequiz_str_html_pdf($sheet), 0, 0, 'C');
    $pdf->Ln(8);
    
    $pdf->SetFont('FreeSans', 'B', 14);
    $pdf->Cell(25, 2, offlinequiz_str_html_pdf($pdf->shortname), 0, 0, 'L');
    $pdf->Ln(1); 
    $pdf->Cell(25, 2, '', 0, 0, 'L');
    $pdf->SetFont('FreeSans', 'B', 10);
    $pdf->MultiCell(150, 6, offlinequiz_str_html_pdf($pdf->fullname), 0, 'L', 0);
    $pdf->Ln(4);
    $name = get_string('name',  'offlinequiz') . ': ' . $title;
    $pdf->Cell(150, 3, offlinequiz_str_html_pdf($name), 0, 2, 'L');
    // ecastro ULPGC print a common title page
    
    if (!$correction) {
        //$pdf->Cell(0, 4, offlinequiz_str_html_pdf(get_string('questionsheet', 'offlinequiz')), 0, 0, 'C');
        $startY = 62;
        $pdf->setY($startY);
        $pdf->Ln(4);
        
        /*
        if ($offlinequiz->printstudycodefield) {
            $pdf->Rect(34, 42, 137, 50, 'D');
        } else {
            $pdf->Rect(34, 42, 137, 40, 'D');
        }*/
        if ($offlinequiz->printstudycodefield) {
            $pdf->Rect(34, $startY, 137, 50, 'D');
        } else {
            $pdf->Rect(34, $startY, 137, 40, 'D');
        }

        
        $pdf->SetFont('FreeSans', '', 10);
        // Line breaks to position name string etc. properly.
        //$pdf->Ln(14);
        $pdf->Cell(58, 10, offlinequiz_str_html_pdf(get_string('name')) . ":", 0, 0, 'R');
        //$pdf->Rect(76, 54, 80, 0.3, 'F');
        $pdf->Rect(76, $startY+12, 80, 0.3, 'F');
        $pdf->Ln(10);
        $pdf->Cell(58, 10, offlinequiz_str_html_pdf(get_string('idnumber', 'offlinequiz')) . ":", 0, 0, 'R');
        //$pdf->Rect(76, 64, 80, 0.3, 'F');
        $pdf->Rect(76, $startY+22, 80, 0.3, 'F');
        $pdf->Ln(10);
        if ($offlinequiz->printstudycodefield) {
            $pdf->Cell(58, 10, offlinequiz_str_html_pdf(get_string('studycode', 'offlinequiz')) . ":", 0, 0, 'R');
            //$pdf->Rect(76, 74, 80, 0.3, 'F');
            $pdf->Rect(76, $startY+32, 80, 0.3, 'F');
            $pdf->Ln(10);
        }
        
        $pdf->Cell(58, 10, offlinequiz_str_html_pdf(get_string('signature', 'offlinequiz')) . ":", 0, 0, 'R');
        if ($offlinequiz->printstudycodefield) {
            //$pdf->Rect(76, 84, 80, 0.3, 'F');
            $pdf->Rect(76, $startY+42, 80, 0.3, 'F');
        } else {
            //$pdf->Rect(76, 74, 80, 0.3, 'F');
            $pdf->Rect(76, $startY+32, 80, 0.3, 'F');
        }
        $pdf->Ln(25);
        $pdf->SetFont('FreeSans', '', $offlinequiz->fontsize);
        $pdf->SetFontSize($offlinequiz->fontsize);

        // The PDF intro text can be arbitrarily long so we have to catch page overflows.
        if (!empty($offlinequiz->pdfintro)) {
            $oldx = $pdf->GetX();
            $oldy = $pdf->GetY();

            $pdf->checkpoint();
            $pdf->writeHTMLCell(165, round($offlinequiz->fontsize / 2), $pdf->GetX(), $pdf->GetY(), $offlinequiz->pdfintro);
            $pdf->Ln();

            if ($pdf->is_overflowing()) {
                $pdf->backtrack();
                $pdf->SetX($oldx);
                $pdf->SetY($oldy);
                $paragraphs = preg_split('/<p>/', $offlinequiz->pdfintro);

                foreach ($paragraphs as $paragraph) {
                    if (!empty($paragraph)) {
                        $sentences = preg_split('/<br\s*\/>/', $paragraph);
                        foreach ($sentences as $sentence) {
                            $pdf->checkpoint();
                            $pdf->writeHTMLCell(165, round($offlinequiz->fontsize / 2), $pdf->GetX(), $pdf->GetY(),
                                                $sentence . '<br/>');
                            $pdf->Ln();
                            if ($pdf->is_overflowing()) {
                                $pdf->backtrack();
                                $pdf->AddPage();
                                $pdf->Ln(14);
                                $pdf->writeHTMLCell(165, round($offlinequiz->fontsize / 2), $pdf->GetX(), $pdf->GetY(), $sentence);
                                $pdf->Ln();
                            }
                        }
                    }
                }
            }
        }
    }

    $pdf->AddPage();
    //$pdf->Ln(2);
    $pdf->SetMargins(15, 15, 15);    
    // ecastro ULPGC new section if two columns
    $offlinequiz->qsheetcols = 1;
    if($offlinequiz->qsheetcols > 1) {
        // disable existing columns
        $pdf->resetColumns();
        // set columns
        $pagedim = $pdf->getPageDimensions($pdf->getPage());  
        $width = ($pagedim['wk'] - $pagedim['lm'] - $pagedim['rm'] - 10 ) / 2;
        $pdf->setEqualColumns($offlinequiz->qsheetcols, $width);
    }


    // Load all the questions needed for this offline quiz group.
    $sql = "SELECT q.*, c.contextid, ogq.page, ogq.slot, ogq.maxmark
              FROM {offlinequiz_group_questions} ogq
              JOIN {question} q ON q.id = ogq.questionid
              JOIN {question_versions} qv ON qv.questionid = q.id
              JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
              JOIN {question_categories} c ON c.id = qbe.questioncategoryid
             WHERE ogq.offlinequizid = :offlinequizid
               AND ogq.offlinegroupid = :offlinegroupid
          ORDER BY ogq.slot ASC ";
    $params = array('offlinequizid' => $offlinequiz->id, 'offlinegroupid' => $group->id);

    // Load the questions.
    $questions = $DB->get_records_sql($sql, $params);
    if (!$questions) {
        echo $OUTPUT->box_start();
        echo $OUTPUT->error_text(get_string('noquestionsfound', 'offlinequiz', $groupletter));
        echo $OUTPUT->box_end();
        return;
    }

    // Load the question type specific information.
    if (!get_question_options($questions)) {
        print_error('Could not load question options');
    }

    // Restore the question sessions to their most recent states.
    // Creating new sessions where required.
    $number = 1;

    // We need a mapping from question IDs to slots, assuming that each question occurs only once.
    $slots = $templateusage->get_slots();

    $texfilter = new filter_tex($context, array());

    // If shufflequestions has been activated we go through the questions in the order determined by
    // the template question usage.
    if ($offlinequiz->shufflequestions) {
        foreach ($slots as $slot) {
            $slotquestion = $templateusage->get_question($slot);
            $currentquestionid = $slotquestion->id;

            // Add page break if necessary because of overflow.
            if ($pdf->GetY() > 230) {
                $pdf->AddPage();
                $pdf->Ln(14);
            }
            set_time_limit(120);
            $question = $questions[$currentquestionid];

            $html = offlinequiz_print_question_html($pdf, $question, $texfilter, $trans, $offlinequiz);

            if ($question->qtype == 'multichoice' || $question->qtype == 'multichoiceset') {

                $html = $html . offlinequiz_get_answers_html($offlinequiz, $templateusage,
                    $slot, $question, $texfilter, $trans, $correction);

            }
            if ($offlinequiz->disableimgnewlines) {
                // This removes span attribute added by TEX filter which created extra line break after every LATEX formula.
                $html = preg_replace("/(<span class=\"MathJax_Preview\">.+?)+(title=\"TeX\" >)/ms", "", $html);
                $html = preg_replace("/<\/a><\/span>/ms", "", $html);
                $html = preg_replace("/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/ms", "", $html);
            }
            // Finally print the question number and the HTML string.
            if ($question->qtype == 'multichoice' || $question->qtype == 'multichoiceset') {
                $pdf->SetFont('FreeSans', 'B', $offlinequiz->fontsize);
                $pdf->Cell(4, round($offlinequiz->fontsize / 2), "$number)  ", 0, 0, 'R');
                $pdf->SetFont('FreeSans', '', $offlinequiz->fontsize);
            }
            offlinequiz_write_question_to_pdf($pdf, $offlinequiz->fontsize, $question->qtype, $html, $number);
            $number += $questions[$currentquestionid]->length;
        }
    } else {
        // No shufflequestions, so go through the questions as they have been added to the offlinequiz group.
        // We also have to show description questions that are not in the template.

        // First, compute mapping  questionid -> slotnumber.
        $questionslots = array();
        foreach ($slots as $slot) {
            $questionslots[$templateusage->get_question($slot)->id] = $slot;
        }
        $currentpage = 1;
        foreach ($questions as $question) {
            $currentquestionid = $question->id;

            // Add page break if set explicitely by teacher.
            if ($question->page > $currentpage) {
                $pdf->AddPage();
                $pdf->Ln(14);
                $currentpage++;
            }

            // Add page break if necessary because of overflow.
            if ($pdf->GetY() > 230) {
                $pdf->AddPage();
                $pdf->Ln( 14 );
            }
            set_time_limit( 120 );

            // Either we print the question HTML.
            $html = offlinequiz_print_question_html($pdf, $question, $texfilter, $trans, $offlinequiz);

            if ($question->qtype == 'multichoice' || $question->qtype == 'multichoiceset') {

                $slot = $questionslots[$currentquestionid];

                $html = $html . offlinequiz_get_answers_html($offlinequiz, $templateusage,
                    $slot, $question, $texfilter, $trans, $correction);
            }

            // Finally print the question number and the HTML string.
            if ($question->qtype == 'multichoice' || $question->qtype == 'multichoiceset') {
                $pdf->SetFont ( 'FreeSans', 'B', $offlinequiz->fontsize );
                $pdf->Cell ( 4, round ( $offlinequiz->fontsize / 2 ), "$number)  ", 0, 0, 'R' );
                $pdf->SetFont ( 'FreeSans', '', $offlinequiz->fontsize );
            }

            // This removes span attribute added by TEX filter which created extra line break after every LATEX formula.
            if ($offlinequiz->disableimgnewlines) {
                $html = preg_replace("/(<span class=\"MathJax_Preview\">.+?)+(title=\"TeX\" >)/ms", "", $html);
                $html = preg_replace("/<\/a><\/span>/ms", "", $html);
                $html = preg_replace("/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/ms", "", $html);
            }

            offlinequiz_write_question_to_pdf($pdf, $offlinequiz->fontsize, $question->qtype, $html, $number);
            $number += $questions[$currentquestionid]->length;
        }

    }

    $fs = get_file_storage();

    $fileprefix = get_string('fileprefixform', 'offlinequiz');
    if ($correction) {
        $fileprefix = get_string('fileprefixcorrection', 'offlinequiz');
    }

    // Prepare file record object.
    $date = usergetdate(time());
    $timestamp = sprintf('%04d%02d%02d_%02d%02d%02d',
            $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes'], $date['seconds']);

    $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'mod_offlinequiz',
            'filearea' => 'pdfs',
            'filepath' => '/',
            'itemid' => 0,
            'filename' => $fileprefix . '_' . $groupletter . '_' . $timestamp . '.pdf');

    if ($oldfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'])) {

        $oldfile->delete();
    }
    $pdfstring = $pdf->Output('', 'S');

    $file = $fs->create_file_from_string($fileinfo, $pdfstring);
    $trans->remove_temp_files();

    return $file;
}


/*
 * Generates the PDF answer form for an offlinequiz group.
*
* @param int $maxanswers the maximum number of answers in all question of the offline group
* @param question_usage_by_activity $templateusage the template question  usage for this offline group
* @param object $offlinequiz The offlinequiz object
* @param object $group the offline group object
* @param int $courseid the ID of the Moodle course
* @param object $context the context of the offline quiz.
* @return stored_file instance, the generated PDF file.
*/
function offlinequiz_create_pdf_answer($maxanswers, $templateusage, $offlinequiz, $group, $courseid, $context) {
    global $CFG, $DB, $OUTPUT, $USER;

    $letterstr = ' abcdefghijklmnopqrstuvwxyz';
    $groupletter = strtoupper($letterstr[$group->groupnumber]);

    $texfilter = new filter_tex($context, array());

    $pdf = new offlinequiz_answer_pdf('P', 'mm', 'A4');
    offlinequiz_set_course_info($courseid, $pdf); // ecastro ULPGC
    $title = offlinequiz_str_html_pdf($offlinequiz->name);
    if (!empty($offlinequiz->time)) {
        $title = $title . ": " . offlinequiz_str_html_pdf(userdate($offlinequiz->time));
    }
    $pdf->set_title($title);
    offlinequiz_set_course_info($courseid, $pdf); // ecastro ULPGC
    $pdf->group = $groupletter;
    $pdf->groupid = $group->groupnumber;
    $pdf->offlinequiz = $offlinequiz->id;
    /*
    $pdf->formtype = 4;
    $pdf->colwidth = 7 * 6.5;
    if ($maxanswers > 5) {
        $pdf->formtype = 3;
        $pdf->colwidth = 9 * 6.5;
    }
    if ($maxanswers > 7) {
        $pdf->formtype = 2;
        $pdf->colwidth = 14 * 6.5;
    }
    if ($maxanswers > 12) {
        $pdf->formtype = 1;
        $pdf->colwidth = 26 * 6.5;
    }
    if ($maxanswers > 26) {
        print_error('Too many answers in one question');
    }
    */
    
    $pdf->userid = $USER->id;
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // Load all the questions and quba slots needed by this script.
    $slots = $templateusage->get_slots();

    $sql = "SELECT q.*, c.contextid, ogq.page, ogq.slot, ogq.maxmark
              FROM {offlinequiz_group_questions} ogq
              JOIN {question} q ON ogq.questionid = q.id
              JOIN {question_versions} qv ON qv.questionid = q.id
              JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
              JOIN {question_categories} c ON qbe.questioncategoryid = c.id
             WHERE ogq.offlinequizid = :offlinequizid
               AND ogq.offlinegroupid = :offlinegroupid
          ORDER BY ogq.slot ASC ";
    $params = array('offlinequizid' => $offlinequiz->id, 'offlinegroupid' => $group->id);

    if (!$questions = $DB->get_records_sql($sql, $params)) {
        echo $OUTPUT->box_start();
        echo $OUTPUT->error_text(get_string('noquestionsfound', 'offlinequiz', $groupletter));
        echo $OUTPUT->box_end();
        return;
    }

    // Load the question type specific information.
    if (!get_question_options($questions)) {
        print_error('Could not load question options');
    }

    // Counting the total number of multichoice questions in the question usage.
    $totalnumber = offlinequiz_count_multichoice_questions($templateusage);
    
    // ecastro ULPGC 
    $pdf->formtype = offlinequiz_get_formtype($offlinequiz->questionspercolumn, $totalnumber, $maxanswers);
    $pdf->colwidth = 7 * 6.5;
    if ($maxanswers > 5) {
        $pdf->colwidth = 9 * 6.5;
    }
    if ($maxanswers > 7) {
        $pdf->colwidth = 14 * 6.5;
    }
    if ($maxanswers > 12) {
        $pdf->colwidth = 26 * 6.5;
    }
    if ($maxanswers > 26) {
        print_error('Too many answers in one question');
    }
    
    $questionspercolumn = max(5, min(25, $offlinequiz->questionspercolumn));
    $numperblock = $questionspercolumn;
    if($offlinequiz->labelblocks) {
        $numperblock = ceil($questionspercolumn/$offlinequiz->labelblocks);
    }    
    // ecastro ULPGC 

    $number = 0;
    $col = 1;
    $offsety = 105.5; 
    $offsetx = 17.3; 
    $offsetx = floor((210 - $pdf->colwidth*$pdf->formtype)/2); // ecastro ULPGC
    
    $page = 1;
    
    $pdf->SetY($offsety);

    $pdf->SetFont('FreeSans', 'B', 10);
    foreach ($slots as $key => $slot) {
        set_time_limit(120);
        $slotquestion = $templateusage->get_question($slot);
        $currentquestionid = $slotquestion->id;
        $attempt = $templateusage->get_question_attempt($slot);
        $order = $slotquestion->get_order($attempt);  // Order of the answers.

        // Get the question data.
        $question = $questions[$currentquestionid];

        // Only look at multichoice questions.
        if ($question->qtype != 'multichoice' && $question->qtype != 'multichoiceset') {
            continue;
        }
        
        // Print the answer letters every 8 questions.
        //if ($number % 8 == 0) {
        if ($number % $numperblock == 0) { // ecastro ULPGC
            $pdf->SetFont('FreeSans', '', 8);
            $pdf->SetX(($col - 1) * ($pdf->colwidth) + $offsetx + 5);
            for ($i = 0; $i < $maxanswers; $i++) {
                $pdf->Cell(3.5, 3.5, number_in_style($i, $question->options->answernumbering), 0, 0, 'C');
                $pdf->Cell(3, 3.5, '', 0, 0, 'C');
            }
            $pdf->Ln(4.5);
            $pdf->SetFont('FreeSans', 'B', 10);
        }

        $pdf->SetX(($col - 1) * ($pdf->colwidth) + $offsetx);

        $pdf->Cell(5, 1, ($number + 1).")  ", 0, 0, 'R');

        // Print one empty box for each answer.
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        for ($i = 1; $i <= count($order); $i++) {
            // Move the boxes slightly down to align with question number.
            $pdf->Rect($x, $y + 0.6, 3.5, 3.5, '', array('all' => array('width' => 0.2)));
            $x += 6.5;
        }

        $pdf->SetX($x);

        $pdf->Ln(6.5);

        // Switch to next column if necessary.
        //if (($number + 1) % 24 == 0) {
        if (($number + 1) % $questionspercolumn == 0) { // ecastro ULPGC
            // ecastro ULPGC
            // This is last on column, print letters again 
                $pdf->SetFont('FreeSans', '', 8);
                $pdf->SetX(($col - 1) * ($pdf->colwidth) + $offsetx + 5);
                for ($i = 0; $i < $maxanswers; $i++) {
                    $pdf->Cell(3.5, 3.5, number_in_style($i, $question->options->answernumbering), 0, 0, 'C');
                    $pdf->Cell(3, 3.5, '', 0, 0, 'C');
                }
                $pdf->Ln(4.5);
                $pdf->SetFont('FreeSans', 'B', 10);
            // ecastro ULPGC                        
        
            $pdf->SetY($offsety);
            $col++;
            // Do a pagebreak if necessary.
            if ($col > $pdf->formtype and ($number + 1) < $totalnumber) {
                $col = 1;
                $pdf->AddPage();
                $page++;
                $pdf->SetY($offsety);
            }
        }
        $number ++;
    }

    $group->numberofpages = $page;
    $DB->update_record('offlinequiz_groups', $group);

    $fs = get_file_storage();

    // Prepare file record object.
    $date = usergetdate(time());
    $timestamp = sprintf('%04d%02d%02d_%02d%02d%02d',
            $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes'], $date['seconds']);

    $fileprefix = get_string('fileprefixanswer', 'offlinequiz');
    $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'mod_offlinequiz',
            'filearea' => 'pdfs',
            'filepath' => '/',
            'itemid' => 0,
            'filename' => $fileprefix . '_' . $groupletter . '_' . $timestamp . '.pdf');

    if ($oldfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'])) {
        $oldfile->delete();
    }
    $pdfstring = $pdf->Output('', 'S');
    $file = $fs->create_file_from_string($fileinfo, $pdfstring);
    return $file;
}

/**
 * Creates a PDF document for a list of participants
 *
 * @param unknown_type $offlinequiz
 * @param unknown_type $courseid
 * @param unknown_type $list
 * @param unknown_type $context
 * @return boolean|stored_file
 */
function offlinequiz_create_pdf_participants($offlinequiz, $courseid, $list, $context) {
    global $CFG, $DB;

    $coursecontext = context_course::instance($courseid); // Course context.
    $systemcontext = context_system::instance();

    $offlinequizconfig = get_config('offlinequiz');
    $listname = $list->name;

    // First get roleids for students.
    if (!$roles = get_roles_with_capability('mod/offlinequiz:attempt', CAP_ALLOW, $systemcontext)) {
        print_error("No roles with capability 'mod/offlinequiz:attempt' defined in system context");
    }

    $roleids = array();
    foreach ($roles as $role) {
        $roleids[] = $role->id;
    }

    list($csql, $cparams) = $DB->get_in_or_equal($coursecontext->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'ctx');
    list($rsql, $rparams) = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'role');
    $params = array_merge($cparams, $rparams);

    $sql = "SELECT DISTINCT u.id, u." . $offlinequizconfig->ID_field . ", u.firstname, u.lastname
              FROM {user} u,
                   {offlinequiz_participants} p,
                   {role_assignments} ra,
                   {offlinequiz_p_lists} pl
             WHERE ra.userid = u.id
               AND p.listid = :listid
               AND p.listid = pl.id
               AND pl.offlinequizid = :offlinequizid
               AND p.userid = u.id
               AND ra.roleid $rsql AND ra.contextid $csql
          ORDER BY u.lastname, u.firstname";

    $params['offlinequizid'] = $offlinequiz->id;
    $params['listid'] = $list->id;

    $participants = $DB->get_records_sql($sql, $params);

    if (empty($participants)) {
        return false;
    }

    $pdf = new offlinequiz_participants_pdf('P', 'mm', 'A4');
    $pdf->listno = $list->listnumber;
    $title = offlinequiz_str_html_pdf($offlinequiz->name);
    // Add the list name to the title.
    $title .= ', '.offlinequiz_str_html_pdf($listname);
    $pdf->set_title($title);
    $pdf->SetMargins(15, 25, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();
    $pdf->Ln(9);

    $position = 1;

    $pdf->SetFont('FreeSans', '', 10);
    foreach ($participants as $participant) {
        $pdf->Cell(9, 3.5, "$position. ", 0, 0, 'R');
        $pdf->Cell(1, 3.5, '', 0, 0, 'C');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Rect($x, $y + 0.6, 3.5, 3.5);
        $pdf->Cell(3, 3.5, '', 0, 0, 'C');

        $pdf->Cell(6, 3.5, '', 0, 0, 'C');
        $userkey = substr($participant->{$offlinequizconfig->ID_field},
                          strlen($offlinequizconfig->ID_prefix), $offlinequizconfig->ID_digits);
        $pdf->Cell(13, 3.5, $userkey, 0, 0, 'R');
        $pdf->Cell(12, 3.5, '', 0, 0, 'L');
        if ($pdf->GetStringWidth($participant->firstname) > 40) {
            $participant->firstname = substr($participant->firstname, 0, 20);
        }
        if ($pdf->GetStringWidth($participant->lastname) > 55) {
            $participant->lastname = substr($participant->lastname, 0, 25);
        }
        $pdf->Cell(55, 3.5, $participant->lastname, 0, 0, 'L');
        $pdf->Cell(40, 3.5, $participant->firstname, 0, 0, 'L');
        $pdf->Cell(10, 3.5, '', 0, 1, 'R');
        // Print barcode.
        $y = $pdf->GetY() - 3.5;
        $x = 170;
        offlinequiz_barcodewriter::print_barcode($pdf, $participant->id, $x, $y);
        $pdf->Rect($x, $y, 0.2, 3.7, 'F');
        $pdf->Rect(15, ($pdf->GetY() + 1), 175, 0.2, 'F');
        if ($position % NUMBERS_PER_PAGE != 0) {
            $pdf->Ln(3.6);
        } else {
            $pdf->AddPage();
            $pdf->Ln(9);
        }
        $position++;
    }

    $fs = get_file_storage();

    // Prepare file record object.
    $date = usergetdate(time());
    $timestamp = sprintf('%04d%02d%02d_%02d%02d%02d',
            $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes'], $date['seconds']);

    $fileprefix = get_string('fileprefixparticipants', 'offlinequiz');
    $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'mod_offlinequiz',
            'filearea' => 'participants',
            'filepath' => '/',
            'itemid' => 0,
            'filename' => $fileprefix . '_' . $list->id . '_' . $timestamp . '.pdf');

    if ($oldfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'])) {
        $oldfile->delete();
    }

    $pdfstring = $pdf->Output('', 'S');
    $file = $fs->create_file_from_string($fileinfo, $pdfstring);
    return $file;
}


/**
 * Function to transform Moodle HTML code of a question into proprietary markup that only supports italic, underline and bold.
 *
 * @param unknown_type $input The input text.
 * @param unknown_type $stripalltags Whether all tags should be stripped.
 * @param unknown_type $questionid The ID of the question the text stems from.
 * @param unknown_type $coursecontextid The course context ID.
 * @return mixed
 */
function offlinequiz_str_html_pdf($input, $stripalltags=true, $questionid=null, $coursecontextid=null) {
    global $CFG;

    $output = $input;
    $fs = get_file_storage();

    // Replace linebreaks.
    $output = preg_replace('!<br>!i', "\n", $output);
    $output = preg_replace('!<br />!i', "\n", $output);
    $output = preg_replace('!</p>!i', "\n", $output);

    if (!$stripalltags) {
        $output = preg_replace('data:image\/[a-z]*;base64,', '@', $output);
        // First replace the plugin image tags.
        $output = str_replace('[', '(', $output);
        $output = str_replace(']', ')', $output);
        $strings = preg_split("/<img/i", $output);
        $output = array_shift($strings);
        foreach ($strings as $string) {
            $output .= '[*p ';
            $imagetag = substr($string, 0, strpos($string, '>'));
            $attributes = explode(' ', $imagetag);
            foreach ($attributes as $attribute) {
                $valuepair = explode('=', $attribute);
                if (strtolower(trim($valuepair[0])) == 'src') {
                    $pluginfilename = str_replace('"', '', str_replace("'", '', $valuepair[1]));
                    $pluginfilename = str_replace('@@PLUGINFILE@@/', '', $pluginfilename);
                    $file = $fs->get_file($coursecontextid, 'question', 'questiontext', $questionid, '/', $pluginfilename);
                    // Copy file to temporary file.
                    $output .= $file->get_id(). ']';
                }
            }
            $output .= substr($string, strpos($string, '>') + 1);
        }
        $strings = preg_split("/<span/i", $output);
        $output = array_shift($strings);
        foreach ($strings as $string) {
            $tags = preg_split("/<\/span>/i", $string);
            $styleinfo = explode('>', $tags[0]);
            $style = array();
            if (stripos($styleinfo[0], 'bold')) {
                $style[] = '[*b]';
            }
            if (stripos($styleinfo[0], 'italic')) {
                $style[] = '[*i]';
            }
            if (stripos($styleinfo[0], 'underline')) {
                $style[] = '[*u]';
            }
            sort($style);
            array_shift($styleinfo);
            $output .= implode($style) . implode($styleinfo, '>');
            rsort($style);
            $output .= implode($style);
            if (!empty($tags[1])) {
                $output .= $tags[1];
            }
        }

        $search  = array('/<i[ ]*>(.*?)<\/i[ ]*>/smi', '/<b[ ]*>(.*?)<\/b[ ]*>/smi', '/<em[ ]*>(.*?)<\/em[ ]*>/smi',
                '/<strong[ ]*>(.*?)<\/strong[ ]*>/smi', '/<u[ ]*>(.*?)<\/u[ ]*>/smi',
                '/<sub[ ]*>(.*?)<\/sub[ ]*>/smi', '/<sup[ ]*>(.*?)<\/sup[ ]*>/smi' );
        $replace = array('[*i]\1[*i]', '[*b]\1[*b]', '[*i]\1[*i]',
                '[*b]\1[*b]', '[*u]\1[*u]',
                '[*l]\1[*l]', '[*h]\1[*h]');
        $output = preg_replace($search, $replace, $output);
    }
    $output = strip_tags($output);
    $search  = array('&quot;', '&amp;', '&gt;', '&lt;');
    $replace = array('"', '&', '>', '<');
    $result = str_ireplace($search, $replace, $output);

    return $result;
}
