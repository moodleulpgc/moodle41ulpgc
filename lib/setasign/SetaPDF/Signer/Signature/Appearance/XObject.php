<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: XObject.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * Class representing a signature appearance based on an existing XObject
 *
 * An XObject could be an {@link SetaPDF_Core_Image::toXObject() image}, an extracted
 * {@link SetaPDF_Core_Document_Page::toXObject() page} or a fresh unique instance of a
 * {@link SetaPDF_Core_XObject_Form Form XObject}.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Signature_Appearance_XObject
    extends SetaPDF_Signer_Signature_Appearance_AbstractAppearance
{
    /**
     * The XObject that should be placed into the appearance
     *
     * @var SetaPDF_Core_XObject
     */
    protected $_xObject;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_XObject $xObject
     */
    public function __construct(SetaPDF_Core_XObject $xObject)
    {
        $this->_xObject = $xObject;
    }

    /**
     * Release memory/cycled references.
     */
    public function cleanUp()
    {
        $this->_xObject = null;
    }

    /**
     * Get the n2-layer XObject.
     *
     * @param SetaPDF_Signer_SignatureField $field
     * @param SetaPDF_Core_Document $document
     * @param SetaPDF_Signer $signer
     *
     * @return SetaPDF_Core_XObject_Form
     */
    protected function _getN2XObject(
        SetaPDF_Signer_SignatureField $field,
        SetaPDF_Core_Document $document,
        SetaPDF_Signer $signer
    )
    {
        $rect = [0, 0, $field->getWidth(), $field->getHeight()];
        $xObject = SetaPDF_Core_XObject_Form::create($document, $rect);

        $maxWidth = $this->_xObject->getWidth($field->getHeight());
        $maxHeight = $this->_xObject->getHeight($field->getWidth());

        $x = $y = 0;
        if ($maxHeight > $field->getHeight()) {
            $x = $field->getWidth() / 2 - $maxWidth / 2;
            $this->_xObject->draw($xObject->getCanvas(), $x, $y, null, $field->getHeight());
        } else {
            $y = $field->getHeight() / 2 - $maxHeight / 2;
            $this->_xObject->draw($xObject->getCanvas(), $x, $y, $field->getWidth(), null);
        }

        return $xObject;
    }
}