<?php
//============================================================+
// File name   : vftcpdf.php
// Version     : 6.2.26
// Begin       : 2002-08-03
// Last Update : 2018-09-14
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2002-2018 Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the License
// along with TCPDF. If not, see
// <http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT>.
//
// See LICENSE.TXT file for more information.
// -------------------------------------------------------------------
//
// Description :
//   This is a PHP class for generating PDF documents without requiring external extensions.
//
// NOTE:
//   This class was originally derived in 2002 from the Public
//   Domain FPDF class by Olivier Plathey (http://www.fpdf.org),
//   but now is almost entirely rewritten and contains thousands of
//   new lines of code and hundreds new features.
//
// Main features:
//  * no external libraries are required for the basic functions;
//  * all standard page formats, custom page formats, custom margins and units of measure;
//  * UTF-8 Unicode and Right-To-Left languages;
//  * TrueTypeUnicode, TrueType, Type1 and CID-0 fonts;
//  * font subsetting;
//  * methods to publish some XHTML + CSS code, Javascript and Forms;
//  * images, graphic (geometric figures) and transformation methods;
//  * supports JPEG, PNG and SVG images natively, all images supported by GD (GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM) and all images supported via ImagMagick (http://www.imagemagick.org/www/formats.html)
//  * 1D and 2D barcodes: CODE 39, ANSI MH10.8M-1983, USD-3, 3 of 9, CODE 93, USS-93, Standard 2 of 5, Interleaved 2 of 5, CODE 128 A/B/C, 2 and 5 Digits UPC-Based Extension, EAN 8, EAN 13, UPC-A, UPC-E, MSI, POSTNET, PLANET, RMS4CC (Royal Mail 4-state Customer Code), CBC (Customer Bar Code), KIX (Klant index - Customer index), Intelligent Mail Barcode, Onecode, USPS-B-3200, CODABAR, CODE 11, PHARMACODE, PHARMACODE TWO-TRACKS, Datamatrix, QR-Code, PDF417;
//  * JPEG and PNG ICC profiles, Grayscale, RGB, CMYK, Spot Colors and Transparencies;
//  * automatic page header and footer management;
//  * document encryption up to 256 bit and digital signature certifications;
//  * transactions to UNDO commands;
//  * PDF annotations, including links, text and file attachments;
//  * text rendering modes (fill, stroke and clipping);
//  * multiple columns mode;
//  * no-write page regions;
//  * bookmarks, named destinations and table of content;
//  * text hyphenation;
//  * text stretching and spacing (tracking);
//  * automatic page break, line break and text alignments including justification;
//  * automatic page numbering and page groups;
//  * move and delete pages;
//  * page compression (requires php-zlib extension);
//  * XOBject Templates;
//  * Layers and object visibility.
//  * PDF/A-1b support
//============================================================+

/**
 * @file
 * This is a PHP class for generating PDF documents without requiring external extensions.<br>
 * TCPDF project (http://www.tcpdf.org) was originally derived in 2002 from the Public Domain FPDF class by Olivier Plathey (http://www.fpdf.org), but now is almost entirely rewritten.<br>
 * <h3>TCPDF main features are:</h3>
 * <ul>
 * <li>no external libraries are required for the basic functions;</li>
 * <li>all standard page formats, custom page formats, custom margins and units of measure;</li>
 * <li>UTF-8 Unicode and Right-To-Left languages;</li>
 * <li>TrueTypeUnicode, TrueType, Type1 and CID-0 fonts;</li>
 * <li>font subsetting;</li>
 * <li>methods to publish some XHTML + CSS code, Javascript and Forms;</li>
 * <li>images, graphic (geometric figures) and transformation methods;
 * <li>supports JPEG, PNG and SVG images natively, all images supported by GD (GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM) and all images supported via ImagMagick (http://www.imagemagick.org/www/formats.html)</li>
 * <li>1D and 2D barcodes: CODE 39, ANSI MH10.8M-1983, USD-3, 3 of 9, CODE 93, USS-93, Standard 2 of 5, Interleaved 2 of 5, CODE 128 A/B/C, 2 and 5 Digits UPC-Based Extension, EAN 8, EAN 13, UPC-A, UPC-E, MSI, POSTNET, PLANET, RMS4CC (Royal Mail 4-state Customer Code), CBC (Customer Bar Code), KIX (Klant index - Customer index), Intelligent Mail Barcode, Onecode, USPS-B-3200, CODABAR, CODE 11, PHARMACODE, PHARMACODE TWO-TRACKS, Datamatrix, QR-Code, PDF417;</li>
 * <li>JPEG and PNG ICC profiles, Grayscale, RGB, CMYK, Spot Colors and Transparencies;</li>
 * <li>automatic page header and footer management;</li>
 * <li>document encryption up to 256 bit and digital signature certifications;</li>
 * <li>transactions to UNDO commands;</li>
 * <li>PDF annotations, including links, text and file attachments;</li>
 * <li>text rendering modes (fill, stroke and clipping);</li>
 * <li>multiple columns mode;</li>
 * <li>no-write page regions;</li>
 * <li>bookmarks, named destinations and table of content;</li>
 * <li>text hyphenation;</li>
 * <li>text stretching and spacing (tracking);</li>
 * <li>automatic page break, line break and text alignments including justification;</li>
 * <li>automatic page numbering and page groups;</li>
 * <li>move and delete pages;</li>
 * <li>page compression (requires php-zlib extension);</li>
 * <li>XOBject Templates;</li>
 * <li>Layers and object visibility;</li>
 * <li>PDF/A-1b support.</li>
 * </ul>
 * Tools to encode your unicode fonts are on fonts/utils directory.</p>
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 * @version 6.2.26
 */

// TCPDF configuration
require_once($CFG->dirroot.'/local/vflibs//tcpdf/tcpdf.php');

/**
 * @class TCPDF
 * PHP class for generating PDF documents without requiring external extensions.
 * TCPDF project (http://www.tcpdf.org) has been originally derived in 2002 from the Public Domain FPDF class by Olivier Plathey (http://www.fpdf.org), but now is almost entirely rewritten.<br>
 * @package com.tecnick.tcpdf
 * @brief PHP class for generating PDF documents without requiring external extensions.
 * @version 6.2.26
 * @author Nicola Asuni - info@tecnick.com
 * @IgnoreAnnotation("protected")
 * @IgnoreAnnotation("public")
 * @IgnoreAnnotation("pre")
 */
class VFTCPDF extends TCPDF {

    // ADDITIONS
    protected $doc_format = '';
    protected $doc_orientation = 'P';

    /**
     * Drawing start base.
     */
    protected $basex = 10;
    protected $basey = 10;

    /**
     * An associative array of printable objects parameters.
     */
    protected $objects;

    // ADDITIONS
    public function getDocOrientation() {
        return $this->doc_orientation;
    }

    public function getDocFormat() {
        return $this->doc_format;
    }

    /**
     * Add extra inits such as non standard fonts, customobjects
     * @param object $printconfig local confuguration comming from calling plugin.
     */
    public function init($printconfig) {
        global $CFG;

        $config = get_config('local_vflibs');
        $fs = get_file_storage();
        $context = context_system::instance();

        if (!empty($config->enablelocalpdf)) {

            $fonts = glob($CFG->dirroot.'/local/vflibs/vftcpdf/fonts/*.php');
            foreach ($fonts as $f) {

                $style = '';

                if (preg_match('/bi.php$/', $f)) {
                    $style = 'BI';
                } else if (preg_match('/b.php$/', $f)) {
                    $style = 'B';
                } else if (preg_match('/i.php$/', $f)) {
                    $style = 'I';
                } else if (preg_match('/bd.php$/', $f)) {
                    $style = 'B';
                }

                $fontname = basename($f, '.php');
                $fontname = preg_replace('/(bd|bi|b|i)$/', '', $fontname);

                if (preg_match('/^uni2cid/', $fontname)) {
                    continue;
                }

                if (!empty($f)) {
                    $this->AddFont($fontname, $style, $f, 'default');
                }
            }

            $x = 10;
            $y = 10;

            if (!empty($printconfig->marginx)) {
                $x = $printconfig->marginx;
            }
            if (!empty($printconfig->marginy)) {
                $y = $printconfig->marginy;
            }

            $this->setBaseX($x);
            $this->setBaseY($y);

            // Some standard graphic objects.
            $wmark = new StdClass;
            $wmark->x = $this->getBaseX();
            $wmark->y = $this->getBaseY();
            $wmark->w = $this->getPageWidth() - ($wmark->x * 2);
            $wmark->h = $this->getPageHeight() - ($wmark->y * 2);

            if (!empty($printconfig->watermarkx)) {
                $wmark->x = $printconfig->watermarkx;
            }
            if (!empty($printconfig->watermarky)) {
                $wmark->y = $printconfig->watermarky;
            }
            if (!empty($printconfig->watermarkw)) {
                $wmark->w = $printconfig->watermarkw;
            }
            if (!empty($printconfig->watermarkh)) {
                $wmark->h = $printconfig->watermarkh;
            }

            if (!$fs->is_area_empty($context->id, $printconfig->plugin, 'docwatermark', 0)) {
                $files = $fs->get_area_files($context->id, $printconfig->plugin, 'docwatermark', 0, 'filename', true);
                $file = array_pop($files);
                $wmark->image = $file;
            }

            $this->addCustomObject('wmark', $wmark);

            $header = new StdClass;
            $header->x = $this->basex;
            $header->y = $this->basey;
            $header->w = $this->getPageWidth() - (2 * $this->basex);
            $header->h = '';

            if (!empty($printconfig->headerx)) {
                $header->x = $printconfig->headerx;
            }
            if (!empty($printconfig->headery)) {
                $header->y = $printconfig->headery;
            }
            if (!empty($printconfig->headerw)) {
                $header->w = $printconfig->headerw;
            }
            if (!empty($printconfig->headerh)) {
                $header->h = $printconfig->headerh;
            }

            if (!$fs->is_area_empty($context->id, $printconfig->plugin, 'docheader', 0)) {
                $files = $fs->get_area_files($context->id, $printconfig->plugin, 'docheader', 0, 'filename', true);
                $file = array_pop($files);
                $header->image = $file;
            }
            $this->addCustomObject('header', $header);

            $innerheader = clone($header);
            if (!$fs->is_area_empty($context->id, $printconfig->plugin, 'docinnerheader', 0)) {
                $files = $fs->get_area_files($context->id, 'local_shop', 'docinnerheader', 0, 'filename', true);
                $file = array_pop($files);
                $innerheader->image = $file;
                $this->addCustomObject('innerheader', $innerheader);
            }

            $footer = new StdClass;
            $footer->x = $this->basex;
            $footer->y = $this->getPageHeight() - (2 * $this->basey);
            $footer->w = $this->getPageWidth() - (2 * $this->basey);;
            $footer->h = 0;

            if (!empty($printconfig->footerx)) {
                $footer->x = $printconfig->footerx;
            }
            if (!empty($printconfig->footery)) {
                $footer->y = $printconfig->footery;
            }
            if (!empty($printconfig->footerw)) {
                $footer->w = $printconfig->footerw;
            }
            if (!empty($printconfig->footerh)) {
                $footer->h = $printconfig->footerh;
            }

            if (!$fs->is_area_empty($context->id, $printconfig->plugin, 'docfooter', 0)) {
                $files = $fs->get_area_files($context->id, $printconfig->plugin, 'docfooter', 0, 'filename', true);
                $file = array_pop($files);
                $footer->image = $file;
            }

            $this->addCustomObject('footer', $footer);

            $innerfooter = clone($footer);
            if (!$fs->is_area_empty($context->id, $printconfig->plugin, 'docinnerfooter', 0)) {
                $files = $fs->get_area_files($context->id, $printconfig->plugin, 'docinnerfooter', 0, 'filename', true);
                $file = array_pop($files);
                $innerfooter->image = $file;
                $this->addCustomObject('innerfooter', $innerfooter);
            }

            $logo = new StdClass;
            $logo->x = $this->getBaseX();
            $logo->y = $this->getBaseY();
            $logo->w = '';
            $logo->h = '';

            if (!empty($printconfig->logox)) {
                $logo->x = $printconfig->logox;
            }
            if (!empty($printconfig->logoy)) {
                $logo->y = $printconfig->logoy;
            }
            if (!empty($printconfig->logow)) {
                $logo->w = $printconfig->logow;
            }
            if (!empty($printconfig->logoh)) {
                $logo->h = $printconfig->logoh;
            }
            if (!$fs->is_area_empty($context->id, $printconfig->plugin, 'doclogo', 0)) {
                $files = $fs->get_area_files($context->id, $printconfig->plugin, 'doclogo', 0, 'filename', true);
                $file = array_pop($files);
                $logo->image = $file;
                $this->addCustomObject('logo', $logo);
            }
        } else {
            debug_trace("Using standard TCPDF");
        }
    }

    /**
     * Custom override for our header.
     */
    public function addCustomObject($objectname, $data) {
        $this->objects[$objectname] = $data;
        if (($objectname == 'header') || ($objectname == 'innerheader')) {
            if (!empty($data->h)) {
                $this->setTopMargin($data->h + $this->basex + 10);
                $this->setPrintHeader(true);
            }
        }

        if (($objectname == 'footer') || ($objectname == 'innerfooter')) {
            $breakpoint = $this->basey;
            if (!empty($data->h)) {
                $breakpoint = $this->basey + $data->h;
                $this->setPrintFooter(true);
                $this->SetAutoPageBreak(true, $breakpoint);
            }
        }
    }

    public function setBaseX($x) {
        $this->basex = $x;
    }

    public function setBaseY($y) {
        $this->basey = $y;
    }

    public function getCustomObject($objectname) {
        return $this->objects[$objectname];
    }

    public function getBaseX() {
        return $this->basex;
    }

    public function getBaseY() {
        return $this->basey;
    }

     //Page header
    public function Header() {

        $headerimagekey = 'header';
        if ($this->getAliasNumPage() > 1) {
            $headerimagekey = 'innerheader';
            if (!isset($this->objects[$headerimagekey]->image) && isset($this->objects['header']->image)) {
                $headerimagekey = 'header';
            }
        }

        if (isset($this->objects[$headerimagekey]->image)) {

            $filepathname = $this->objects[$headerimagekey]->image->get_contenthash();

            $uploadpath = tcpdf_path_from_hash($filepathname).'/'.$filepathname;

            $obj = $this->objects[$headerimagekey];

            if (!is_numeric($obj->w)) {
                $obj->w = $this->getPageWidth() - ($this->basex * 2);
            }

            // Uploaded path will superseed.
            if (file_exists($uploadpath)) {
                $this->Image($uploadpath, $this->basex, $this->basey, $obj->w, $obj->h);
            }

        }

        if (is_numeric($obj->h)) {
            $this->setY($this->basey + $obj->h) + 10;
        } else {
            $this->setY($this->basey) + 10;
        }

    }

    // Page footer
    public function Footer() {
        $footerimagekey = 'footer';
        if ($this->getAliasNumPage() > 1) {
            $footerimagekey = 'innerfooter';
            if (!isset($this->objects[$footerimagekey]->image) && isset($this->objects['footer']->image)) {
                $footerimagekey = 'footer';
            }
        }

        if (isset($this->objects[$footerimagekey]->image)) {

            $filepathname = $this->objects[$footerimagekey]->image->get_contenthash();

            $uploadpath = tcpdf_path_from_hash($filepathname).'/'.$filepathname;

            $obj = $this->objects[$footerimagekey];

            if (!is_numeric($obj->w)) {
                $obj->w = $this->getPageWidth() - ($this->basex * 2);
            }

            if (!is_numeric($obj->h)) {
                $obj->h = 0;
            }

            // Uploaded path will superseed.
            if (file_exists($uploadpath)) {
                // $this->setY( - $this->basex - $obj->h);
                $this->setY($obj->y);
                $this->Image($uploadpath, $this->basex, '', $obj->w, $obj->h);
                $this->setY($obj->y + 10);
            }
            $this->SetFont('helvetica', 'I', 8);
            $this->setX($this->basex + 10);
            $this->Cell($obj->w, $obj->h - 2, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'B');
        }

        $this->renderWatermark();
    }

    public function renderWatermark() {

        if (empty($this->objects['wmark']->image)) {
            return;
        }

        // Disable page break
        $pb = $this->getAutoPageBreak();
        $bm = $this->getBreakMargin();
        $this->setAutoPageBreak(false);

        // Set alpha to semi-transparency.
        $this->SetAlpha(0.2);
        $x = $this->objects['wmark']->x;
        $y = $this->objects['wmark']->y;
        $w = $this->objects['wmark']->w;
        $h = $this->objects['wmark']->h;

        $filepathname = $this->objects['wmark']->image->get_contenthash();
        $uploadpath = tcpdf_path_from_hash($filepathname).'/'.$filepathname;

        $this->Image($uploadpath, $x, $y, $w, $h);
        // Restore.
        $this->setAutoPageBreak($pb, $bm);
    }

    public function renderLogo() {
        $this->renderCustomObject('logo');
    }

    public function renderCustomObject($objname) {

        if (empty($this->objects[$objname]->image)) {
            return;
        }

        // Set alpha to semi-transparency.
        $this->SetAlpha(1);
        $x = $this->objects[$objname]->x;
        $y = $this->objects[$objname]->y;
        $w = @$this->objects[$objname]->w;
        $h = @$this->objects[$objname]->h;

        $filepathname = $this->objects[$objname]->image->get_contenthash();
        $uploadpath = tcpdf_path_from_hash($filepathname).'/'.$filepathname;

        $this->Image($uploadpath, $x, $y, $w, $h);
    }

    public function renderQRCode($codeurl) {
        if (empty($this->objects['qrcode'])) {
            return;
        }

        $style = array(
                'border' => 2,
                'vpadding' => 'auto',
                'hpadding' => 'auto',
                'fgcolor' => array(0, 0, 0),
                'bgcolor' => array(255, 255, 255), // False.
                'module_width' => 1, // Width of a single module in points.
                'module_height' => 1 // Height of a single module in points.
        );

        $this->SetAlpha(1);
        $x = $this->objects['qrcode']->x;
        $y = $this->objects['qrcode']->y;
        if (isset($this->objects['qrcode']->w) && is_numeric($this->objects['qrcode']->w)) {
            $w = $this->objects['qrcode']->w;
        } else {
            $w = 50;
        }
        if (isset($this->objects['qrcode']->h) && is_numeric($this->objects['qrcode']->h)) {
            $h = $this->objects['qrcode']->h;
        } else {
            $h = 50;
        }
        $this->write2DBarcode(''.$codeurl, 'QRCODE,H', $x, $y, $w, $h, $style, 'N');
    }
}