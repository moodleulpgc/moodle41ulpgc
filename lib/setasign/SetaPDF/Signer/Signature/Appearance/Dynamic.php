<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Dynamic.php 1591 2020-11-19 15:07:17Z jan.slabon $
 */

/**
 * Class representing a dynamic visible signature appearance
 *
 * This signature appearance allows you to define a background image or xobject and a logo.
 * The appearance will also display certificate details extracted from the used certificate and
 * some signature properties like {@link SetaPDF_Signer::setLocation() location}.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Signature_Appearance_Dynamic
    extends SetaPDF_Signer_Signature_Appearance_AbstractAppearance
{
    const CONFIG_BACKGROUND_LOGO = 'backgroundLogo';
    const CONFIG_BACKGROUND_LOGO_OPACITY = 'backgroundLogoOpacity';
    const CONFIG_GRAPHIC = 'graphic';
    const CONFIG_GRAPHIC_COLOR = 'graphicColor';
    const CONFIG_GRAPHIC_ALIGN = 'graphicAlign';
    const CONFIG_GRAPHIC_ALIGN_LEFT = 'left';
    const CONFIG_GRAPHIC_ALIGN_RIGHT = 'right';
    const CONFIG_TEXT_ALIGN = 'textAlign';
    const CONFIG_TEXT_COLOR = 'textColor';
    const CONFIG_LABELS = 'labels';
    const CONFIG_SHOW_LABELS = 'showLabels';
    const CONFIG_NAME = 'name';
    const CONFIG_SHOW_NAME = 'showName';
    const CONFIG_SHOW_NAME_TPL = 'showNameTpl';
    const CONFIG_DISTINGUISHED_NAME = 'distinguishedName';
    const CONFIG_SHOW_DISTINGUISHED_NAME = 'showDistinguishedName';
    const CONFIG_SHOW_DISTINGUISHED_NAME_TPL = 'showDistinguishedNameTpl';
    const CONFIG_REASON = 'reason';
    const CONFIG_SHOW_REASON = 'showReason';
    const CONFIG_SHOW_REASON_TPL = 'showReasonTpl';
    const CONFIG_LOCATION = 'location';
    const CONFIG_SHOW_LOCATION = 'showLocation';
    const CONFIG_SHOW_LOCATION_TPL = 'showLocationTpl';
    const CONFIG_DATE = 'date';
    const CONFIG_SHOW_DATE = 'showDate';
    const CONFIG_SHOW_DATE_TPL = 'showDateTpl';
    const CONFIG_SHOW_DATE_FORMAT = 'showDateFormat';

    /**
     * The main signature module
     *
     * @var SetaPDF_Signer_Signature_Module_ModuleInterface
     */
    protected $_module;

    /**
     * The configuration array
     *
     * @var array
     */
    protected $_config = [
        self::CONFIG_BACKGROUND_LOGO => null,
        self::CONFIG_BACKGROUND_LOGO_OPACITY => 1.0,
        self::CONFIG_GRAPHIC => true,
        self::CONFIG_GRAPHIC_COLOR => [0, 0, 0],
        self::CONFIG_GRAPHIC_ALIGN => self::CONFIG_GRAPHIC_ALIGN_LEFT,
        self::CONFIG_TEXT_ALIGN => SetaPDF_Core_Text::ALIGN_LEFT,
        self::CONFIG_TEXT_COLOR => [0, 0, 0],
        self::CONFIG_SHOW_LABELS => true,

        self::CONFIG_SHOW_NAME => true,
        self::CONFIG_SHOW_NAME_TPL => 'Digitally signed by %s',

        self::CONFIG_SHOW_DISTINGUISHED_NAME => true,
        self::CONFIG_SHOW_DISTINGUISHED_NAME_TPL => 'DN: %s',

        self::CONFIG_SHOW_REASON => true,
        self::CONFIG_SHOW_REASON_TPL => 'Reason: %s',

        self::CONFIG_SHOW_LOCATION => true,
        self::CONFIG_SHOW_LOCATION_TPL => 'Location: %s',

        self::CONFIG_SHOW_DATE => true,
        self::CONFIG_SHOW_DATE_TPL => 'Date: %s',
        self::CONFIG_SHOW_DATE_FORMAT => 'd.m.Y H:i:s O', // see date()
    ];

    /**
     * The certificate info returned from openssl_x509_parse()
     *
     * @var null|array
     */
    protected $_certificateInfo = null;

    /**
     * A font that is used for rendering text.
     *
     * @var SetaPDF_Core_Font_FontInterface
     */
    protected $_font = null;

    /**
     * The constructor.
     *
     * Just pass a signature module that offers a getCertificate() method to the constructor.
     * The data for the appearance will be extracted automatically.
     *
     * @param SetaPDF_Signer_Signature_Module_ModuleInterface $module
     * @throws InvalidArgumentException
     */
    public function __construct(SetaPDF_Signer_Signature_Module_ModuleInterface $module)
    {
        if (!method_exists($module, 'getCertificate')) {
            throw new InvalidArgumentException('The signature module should implement a getCertificate() method.');
        }

        $this->_module = $module;
    }

    /**
     * Release memory/cycled references.
     */
    public function cleanUp()
    {
        $this->_module = null;
    }

    /**
     * Set a specific configuration value.
     *
     * @param string $key The key of the config. See and use {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_XXX} for a valid key.
     * @param mixed $value
     * @return boolean|SetaPDF_Signer_Signature_Appearance_Dynamic Returns false if the key is invalid.
     */
    public function setConfig($key, $value)
    {
        if (!array_key_exists($key, $this->_config)) {
            return false;
        }

        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method], $value);
        }

        $this->_config[$key] = $value;
        return $this;
    }

    /**
     * Get a specific configuration value.
     *
     * @param string $key The key of the config. See and use {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_XXX} for a valid key.
     * @return bool|mixed Returns false if the key is invalid.
     */
    public function getConfig($key)
    {
        if (!array_key_exists($key, $this->_config)) {
            return false;
        }

        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        }

        return $this->_config[$key];
    }

    /**
     * Set how and if a graphic is displayed on the appearance.
     *
     * If a XObject is passed it will be used as the graphic, if true is passed the common name of the certificate
     * will be used. If false is passed no graphic will be shown.
     *
     * @param SetaPDF_Core_XObject|boolean $graphic A XObject or a boolean value
     * @return $this
     */
    public function setGraphic($graphic)
    {
        $this->_config[self::CONFIG_GRAPHIC] = $graphic;

        return $this;
    }

    /**
     * Get if and how the graphic on is displayed.
     *
     * @return mixed
     */
    public function getGraphic()
    {
        return $this->_config[self::CONFIG_GRAPHIC];
    }

    /**
     * Set whether the graphic should be displayed on the left or right.
     *
     * @param string $align A self::CONFIG_GRAPHIC_ALIGN_* constant.
     * @see self::CONFIG_GRAPHIC_ALIGN_RIGHT
     * @see self::CONFIG_GRAPHIC_ALIGN_LEFT
     */
    public function setGraphicAlign($align)
    {
        if ($align !== self::CONFIG_GRAPHIC_ALIGN_RIGHT) {
            $align = self::CONFIG_GRAPHIC_ALIGN_LEFT;
        }

        $this->_config[self::CONFIG_GRAPHIC_ALIGN] = $align;
    }

    /**
     * Get whether the graphic should be displayed on the left or right.
     *
     * @return string
     */
    public function getGraphicAlign()
    {
        return $this->_config[self::CONFIG_GRAPHIC_ALIGN];
    }

    /**
     * Set the graphic text color.
     *
     * @param string $graphicColor
     * @return $this
     */
    public function setGraphicColor($graphicColor)
    {
        $this->_config[self::CONFIG_GRAPHIC_COLOR] = $graphicColor;

        return $this;
    }

    /**
     * Get the graphic text color.
     *
     * @return string
     */
    public function getGraphicColor()
    {
        return $this->_config[self::CONFIG_GRAPHIC_COLOR];
    }

    /**
     * Set a background logo and its opacity.
     *
     * The background logo is displayed in the center of the appearance.
     *
     * @param SetaPDF_Core_XObject $xObject
     * @param float $opacity
     * @return $this|bool
     */
    public function setBackgroundLogo(SetaPDF_Core_XObject $xObject = null, $opacity = 1.0)
    {
        $this->_config[self::CONFIG_BACKGROUND_LOGO] = $xObject;
        $this->setBackgroundLogoOpacity($opacity);

        return $this;
    }

    /**
     * Get the current background logo.
     *
     * @return mixed
     */
    public function getBackgroundLogo()
    {
        return $this->_config[self::CONFIG_BACKGROUND_LOGO];
    }

    /**
     * Set the background logo opacity.
     *
     * @param float $opacity A value between 0 (not visible) an 1 (full opacity)
     * @return $this
     */
    public function setBackgroundLogoOpacity($opacity)
    {
        $this->_config[self::CONFIG_BACKGROUND_LOGO_OPACITY] = (float)$opacity;

        return $this;
    }

    /**
     * Get the background logo opacity.
     *
     * @return float
     */
    public function getBackgroundLogoOpacity()
    {
        return $this->_config[self::CONFIG_BACKGROUND_LOGO_OPACITY];
    }

    /**
     * Set the text align.
     *
     * @see SetaPDF_Core_Text
     * @param string $textAlign
     * @return $this
     */
    public function setTextAlign($textAlign)
    {
        $this->_config[self::CONFIG_TEXT_ALIGN] = $textAlign;

        return $this;
    }

    /**
     * Get the text align.
     *
     * @return string
     */
    public function getTextAlign()
    {
        return $this->_config[self::CONFIG_TEXT_ALIGN];
    }

    /**
     * Set the text color.
     *
     * @param string $textColor
     * @return $this
     */
    public function setTextColor($textColor)
    {
        $this->_config[self::CONFIG_TEXT_COLOR] = $textColor;

        return $this;
    }

    /**
     * Get the text color.
     *
     * @return string
     */
    public function getTextColor()
    {
        return $this->_config[self::CONFIG_TEXT_COLOR];
    }

    /**
     * Defines whether a text part will be shown or not.
     *
     * Available names are:
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_NAME}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_REASON}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_DATE}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_LABELS}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_DISTINGUISHED_NAME}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_LOCATION}
     *
     * @param string $name
     * @param boolean $show
     * @return $this|bool
     */
    public function setShow($name, $show = true)
    {
        return $this->setConfig('show' . ucfirst($name), (boolean)$show);
    }

    /**
     * Get whether a text part will be shown or not.
     *
     * @see setShow()
     * @param string $name
     * @return boolean
     */
    public function getShow($name)
    {
        return $this->getConfig('show' . ucfirst($name));
    }

    /**
     * Defines a template for a specific text.
     *
     * The template will be used as a label of a specific value. The value is passed via sprintf() to this template.
     *
     * Following names are possible:
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_NAME}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_REASON}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_DATE}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_DISTINGUISHED_NAME}
     * - {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_LOCATION}
     *
     * @param string $name
     * @param string $tpl
     * @return $this|bool
     */
    public function setShowTpl($name, $tpl)
    {
        return $this->setConfig('show' . ucfirst($name) . 'Tpl', $tpl);
    }

    /**
     * Get a defined template for a specific text part.
     *
     * @see setShowTpl()
     * @param string $name
     * @return string
     */
    public function getShowTpl($name)
    {
        return $this->getConfig('show' . ucfirst($name) . 'Tpl');
    }

    /**
     * Sets a format for a specific value.
     *
     * Currently only the $name {@link SetaPDF_Signer_Signature_Appearance_Dynamic::CONFIG_DATE} is supported.
     *
     * @param string $name
     * @param string $format
     * @return $this|bool
     */
    public function setShowFormat($name, $format)
    {
        return $this->setConfig('show' . ucfirst($name) . 'Format', $format);
    }

    /**
     * Get the format for a specific value.
     *
     * @see setShowFormat()
     * @param string $name
     * @return bool|mixed
     */
    public function getShowFormat($name)
    {
        return $this->getConfig('show' . ucfirst($name) . 'Format');
    }

    /**
     * Returns information about the supplied certificate.
     *
     * @return array|null
     * @throws SetaPDF_Signer_Exception
     */
    public function getCertificateInfo()
    {
        if ($this->_certificateInfo === null) {
            $certificate = $this->_module->getCertificate();
            if (!$certificate instanceof SetaPDF_Signer_X509_Certificate) {
                $certificate = SetaPDF_Signer_X509_Certificate::fromFileOrString($certificate);
            }

            // keep this for BC
            $this->_certificateInfo = openssl_x509_parse($certificate->get());

            if ($this->_certificateInfo === false) {
                throw new SetaPDF_Signer_Exception('Certificate cannot be parsed.');
            }
        }

        return $this->_certificateInfo;
    }

    /**
     * Set the font that should be used for displaying text.
     *
     * @param SetaPDF_Core_Font_FontInterface|null $font
     */
    public function setFont(SetaPDF_Core_Font_FontInterface $font = null)
    {
        $this->_font = $font;
    }

    /**
     * Get the font that should be used for displaying text.
     *
     * @return SetaPDF_Core_Font_FontInterface|null
     */
    public function getFont()
    {
        return $this->_font;
    }

    /**
     * Creates the "n2" layer appearance.
     *
     * @param SetaPDF_Signer_SignatureField $field
     * @param SetaPDF_Core_Document $document
     * @param SetaPDF_Signer $signer
     * @return SetaPDF_Core_XObject_Form
     */
    protected function _getN2XObject(
        SetaPDF_Signer_SignatureField $field,
        SetaPDF_Core_Document $document,
        SetaPDF_Signer $signer
    )
    {
        $width = $field->getWidth();
        $height = $field->getHeight();
        $rect = [0, 0, $width, $height];

        // Create the root XObject
        $xObject = SetaPDF_Core_XObject_Form::create($document, $rect);

        $this->_drawBackground($xObject, $document);
        $this->_drawGraphic($xObject, $document, $signer);
        $text = $this->_prepareText($signer);
        $this->_drawText($text, $xObject, $document);

        return $xObject;
    }

    /**
     * Draws the text on the appearance.
     *
     * If no graphic is set the text will be placed on the full width of the appearance.
     *
     * @param string $text
     * @param SetaPDF_Core_XObject_Form $xObject
     * @param SetaPDF_Core_Document $document
     */
    protected function _drawText($text, SetaPDF_Core_XObject_Form $xObject, SetaPDF_Core_Document $document)
    {
        $graphic = $this->getGraphic();
        if ($graphic === false || $graphic === null) {
            $width = $xObject->getWidth();
            $x = 0;
        } else {
            $width = $xObject->getWidth() / 2;
            $x = $this->getGraphicAlign() === self::CONFIG_GRAPHIC_ALIGN_LEFT ? $width : 0;
        }

        $height = $xObject->getHeight();

        $font = $this->getFont();
        if ($font === null) {
            $font = SetaPDF_Core_Font_Standard_Helvetica::create($document);
        }

        $textBlock = new SetaPDF_Core_Text_Block($font, $height / 2);
        $textBlock->setWidth($width);
        $textBlock->setText($text);
        $textBlock->setAlign($this->getTextAlign());
        $textBlock->setTextColor($this->getTextColor());

        while (($currentHeight = $textBlock->getTextHeight()) > $height) {
            $fontSize = $textBlock->getFontSize();
            $rel = $currentHeight / $height;
            $f = ($rel < 1.8) ? .1 : $rel / 2;
            $textBlock->setFontSize($fontSize - $f);
        }

        $textBlock->draw($xObject->getCanvas(), $x, $height / 2 - $textBlock->getTextHeight() / 2);
    }

    /**
     * Draws the graphic on the left side of the signature appearance.
     *
     * @param SetaPDF_Core_XObject_Form $xObject
     * @param SetaPDF_Core_Document $document
     */
    protected function _drawGraphic(
        SetaPDF_Core_XObject_Form $xObject,
        SetaPDF_Core_Document $document,
        SetaPDF_Signer $signer
    )
    {
        $graphic = $this->getGraphic();
        if ($graphic === false || $graphic === null) {
            return;
        }

        $width = $xObject->getWidth() / 2;
        $height = $xObject->getHeight();

        $canvas = $xObject->getCanvas();
        $x = ($this->getGraphicAlign() === self::CONFIG_GRAPHIC_ALIGN_RIGHT) ? $width : 0;

        if ($graphic instanceof SetaPDF_Core_XObject) {
            $maxWidth = $graphic->getWidth($xObject->getHeight());
            $maxHeight = $graphic->getHeight($width);

            $y = 0;
            if ($maxHeight > $xObject->getHeight()) {
                $x += ($width / 2 - $maxWidth / 2);
                $graphic->draw($canvas, $x, $y, null, $xObject->getHeight());
            } else {
                $y = $xObject->getHeight() / 2 - $maxHeight / 2;
                $graphic->draw($canvas, $x, $y, $width, null);
            }

        } else {
            $certificateInfo = $this->getCertificateInfo();

            $font = $this->getFont();
            if ($font === null) {
                $font = SetaPDF_Core_Font_Standard_Helvetica::create($document);
            }

            $textBlock = new SetaPDF_Core_Text_Block($font, $height / 2);
            $textBlock->setWidth($width);
            $textBlock->setTextColor($this->getGraphicColor());
            if (isset($certificateInfo['subject']['CN'])) {
                $textBlock->setText($certificateInfo['subject']['CN']);
            } else {
                $textBlock->setText($signer->getName());
            }

            while ($textBlock->getTextHeight() > $height) {
                $textBlock->setFontSize($textBlock->getFontSize() - 0.1);
            }

            $textBlock->draw($canvas, $x, $height / 2 - $textBlock->getTextHeight() / 2);
        }
    }

    /**
     * Draw the background logo of the signature appearance.
     *
     * @param SetaPDF_Core_XObject_Form $xObject
     * @param SetaPDF_Core_Document $document
     */
    protected function _drawBackground(SetaPDF_Core_XObject_Form $xObject, SetaPDF_Core_Document $document)
    {
        $backgroundLogo = $this->getBackgroundLogo();
        if ($backgroundLogo === null) {
            return;
        }

        $maxWidth = $backgroundLogo->getWidth($xObject->getHeight());
        $maxHeight = $backgroundLogo->getHeight($xObject->getWidth());

        $canvas = $xObject->getCanvas();

        $opacity = $this->getBackgroundLogoOpacity();
        $opacitySet = abs($opacity - 1.0) > SetaPDF_Core::FLOAT_COMPARISON_PRECISION;
        if ($opacitySet) {
            $gs = new SetaPDF_Core_Resource_ExtGState();
            $gs->setConstantOpacity($opacity);
            $gs->setConstantOpacityNonStroking($opacity);
            $gs->setBlendMode('Normal');
            $gs->getIndirectObject($document);

            if ($backgroundLogo instanceof SetaPDF_Core_XObject_Form) {
                $backgroundLogo->setGroup(new SetaPDF_Core_TransparencyGroup());
            }
            $canvas->saveGraphicState();
            $canvas->setGraphicState($gs);
        }

        $x = $y = 0;
        if ($maxHeight > $xObject->getHeight()) {
            $x = $xObject->getWidth() / 2 - $maxWidth / 2;
            $backgroundLogo->draw($canvas, $x, $y, null, $xObject->getHeight());
        } else {
            $y = $xObject->getHeight() / 2 - $maxHeight / 2;
            $backgroundLogo->draw($canvas, $x, $y, $xObject->getWidth(), null);
        }

        if ($opacitySet) {
            $canvas->restoreGraphicState();
        }
    }

    /**
     * Prepares the text string.
     *
     * @param SetaPDF_Signer $signer
     * @return string
     */
    protected function _prepareText(SetaPDF_Signer $signer)
    {
        $showLabels = $this->_config[self::CONFIG_SHOW_LABELS];
        $text = [];
        $certificateInfo = $this->getCertificateInfo();

        if ($this->getShow(self::CONFIG_DISTINGUISHED_NAME)) {
            $subjectPieces = [];
            foreach ($certificateInfo['subject'] AS $k => $v) {
                if (!$v) {
                    continue;
                }

                $k = strtolower($k);
                if ($k === 'emailaddress') {
                    $k = 'email';
                }

                if (is_array($v)) {
                    $v = implode('; ', $v);
                }

                $subjectPieces[] = $k . '=' . $v;
            }

            $distinguishedName = implode(', ', $subjectPieces);
            if ($distinguishedName) {
                $text[] = $showLabels
                        ? sprintf($this->getShowTpl(self::CONFIG_DISTINGUISHED_NAME), $distinguishedName)
                        : $distinguishedName;
            }
        }

        // Reason
        if ($this->getShow(self::CONFIG_REASON)) {
            $reason = $signer->getReason();
            if ($reason) {
                $text[] = $showLabels
                        ? sprintf($this->getShowTpl(self::CONFIG_REASON), $reason)
                        : $reason;
            }
        }

        // Location
        if ($this->getShow(self::CONFIG_LOCATION)) {
            $location = $signer->getLocation();
            if ($location) {
                $text[] = $showLabels
                        ? sprintf($this->getShowTpl(self::CONFIG_LOCATION), $location)
                        : $location;
            }
        }

        // Date
        if ($this->getShow(self::CONFIG_DATE)) {
            $date = $signer->getTimeOfSigning();

            if ($date) {
                $date = $date->getAsDateTime()->format($this->getShowFormat(self::CONFIG_DATE));
                $text[] = $showLabels
                        ? sprintf($this->getShowTpl(self::CONFIG_DATE), $date)
                        : $date;
            }
        }

        // Name
        if ($this->_config[self::CONFIG_SHOW_NAME] || (count($text) === 0 && $this->_config[self::CONFIG_GRAPHIC] === false)) {
            $name = isset($certificateInfo['subject']['CN']) ? $certificateInfo['subject']['CN'] : $signer->getName();
            if ($name) {
                array_unshift($text, $showLabels
                    ? sprintf($this->_config[self::CONFIG_SHOW_NAME_TPL], $name)
                    : $name);
            }
        }

        return implode("\n", $text);
    }
}