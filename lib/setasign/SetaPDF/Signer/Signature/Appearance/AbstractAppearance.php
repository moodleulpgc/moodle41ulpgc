<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: AbstractAppearance.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * Abstract class representing a signature appearance
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Signer_Signature_Appearance_AbstractAppearance
{
    /**
     * Creates the XObject structure and "layers" (n0 + n2).
     *
     * @param SetaPDF_Signer_SignatureField $field
     * @param SetaPDF_Core_Document $document
     * @param SetaPDF_Signer $signer
     */
    public function createAppearance(
        SetaPDF_Signer_SignatureField $field,
        SetaPDF_Core_Document $document,
        SetaPDF_Signer $signer
    ) {
        $width = $field->getWidth();
        $height = $field->getHeight();

        if ($width == 0 || $height == 0) {
            return;
        }

        $rect = [0, 0, $width, $height];

        // Create the root XObject
        $rootXObject = SetaPDF_Core_XObject_Form::create($document, $rect);
        $rotation = $field->getAppearanceCharacteristics()
            ? $field->getAppearanceCharacteristics()->getRotation()
            : 0;

        if ($rotation != 0) {
            $rotation %= 360;
            if ($rotation < 0) {
                $rotation += 360;
            }

            $r = deg2rad($rotation);
            $a = $d = cos($r);
            $b = sin($r);
            $c = -$b;
            $e = 0;
            $f = 0;

            if ($a == -1) {
                $e = $width;
                $f = $height;
            }

            if ($b == 1) {
                $e = $height;
            }

            if ($c == 1) {
                $f = $width;
            }

            $rootXObject->getIndirectObject()->ensure()->getValue()->offsetSet('Matrix', new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric($a),
                new SetaPDF_Core_Type_Numeric($b),
                new SetaPDF_Core_Type_Numeric($c),
                new SetaPDF_Core_Type_Numeric($d),
                new SetaPDF_Core_Type_Numeric($e),
                new SetaPDF_Core_Type_Numeric($f)
            ]));
        }

        $field->setAppearance($rootXObject);

        // Create intermediate XObject
        $intermediateXObject = SetaPDF_Core_XObject_Form::create($document, $rect);

        // Add and draw intermediate XObject on root XObject
        $rootCanvas = $rootXObject->getCanvas();
        $rootCanvas->setResource(SetaPDF_Core_Resource::TYPE_X_OBJECT, 'FRM', $intermediateXObject);
        $rootCanvas->drawXObject('FRM');

        // check for existing n0 template
        $xObjects = $document->getCatalog()->getAcroForm()->getDefaultResources(true, 'XObject');
        if ($xObjects->offsetExists('DSz')) {
            $n0XObject = new SetaPDF_Core_XObject_Form($xObjects->getValue('DSz'));
        } else {
            // create n0
            $n0XObject = SetaPDF_Core_XObject_Form::create($document, [0, 0, 100, 100]);
            $n0XObject->getCanvas()->write('% DSBlank');
            $xObjects->offsetSet('DSz', $n0XObject->getIndirectObject());
        }

        // draw layer "n0"
        $intermediateCanvas = $intermediateXObject->getCanvas();
        $intermediateCanvas->setResource(SetaPDF_Core_Resource::TYPE_X_OBJECT, 'n0', $n0XObject);
        $n0XObject->draw($intermediateCanvas, 0, 0);

        // draw layer "n2"
        $n2XObject = $this->_getN2XObject($field, $document, $signer);
        $intermediateCanvas->setResource(SetaPDF_Core_Resource::TYPE_X_OBJECT, 'n2', $n2XObject);
        $n2XObject->draw($intermediateCanvas, 0, 0);
    }

    /**
     * Abstract method that has to return the XObject holding the signature appearance for layer "n2".
     *
     * @param SetaPDF_Signer_SignatureField $field
     * @param SetaPDF_Core_Document $document
     * @param SetaPDF_Signer $signer
     * @return SetaPDF_Core_XObject_Form
     */
    abstract protected function _getN2XObject(
        SetaPDF_Signer_SignatureField $field,
        SetaPDF_Core_Document $document,
        SetaPDF_Signer $signer
    );
}