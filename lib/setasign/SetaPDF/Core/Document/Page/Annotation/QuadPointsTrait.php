<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id$
 */

/**
 * Trait for handling the QuadPoints property in Annotations
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
trait SetaPDF_Core_Document_Page_Annotation_QuadPointsTrait
{
    /**
     * Set the quad points.
     *
     * @param int[]|float[] $quadPoints An array of quad points (multiple of 8 values: TopLeft x/y, TopRight x/y,
     *                                  BottomLeft x/y, BottomRight x/y - which is a contradiction to the PDF
     *                                  specification but used by all common PDF viewer applications).
     */
    public function setQuadPoints($quadPoints)
    {
        if (!is_array($quadPoints)) {
            $quadPoints = func_get_args();
        }

        if ((count($quadPoints) % 8) !== 0) {
            throw new InvalidArgumentException('Quad points needs to be an array of a multiple of 8 numeric values!');
        }

        $points = new SetaPDF_Core_Type_Array();

        foreach ($quadPoints as $quadPoint) {
            if (!is_numeric($quadPoint)) {
                throw new InvalidArgumentException('Quad point needs to be a numeric value.');
            }

            $points[] = new SetaPDF_Core_Type_Numeric($quadPoint);
        }

        $this->_annotationDictionary->offsetSet('QuadPoints', $points);
    }

    /**
     * Get the quad points.
     *
     * @return array
     */
    public function getQuadPoints()
    {
        if ($this->_annotationDictionary->offsetExists('QuadPoints')) {
            return $this->_annotationDictionary->getValue('QuadPoints')->ensure()->toPhp();
        }

        $rect = $this->getRect();

        // TopLeft, TopRight, BottomLeft, BottomRight
        return [
            $rect->getLlx(), $rect->getUry(), // x4
            $rect->getUrx(), $rect->getUry(), // x3
            $rect->getLlx(), $rect->getLly(), // x1
            $rect->getUrx(), $rect->getLly(), // x2
        ];
    }
}