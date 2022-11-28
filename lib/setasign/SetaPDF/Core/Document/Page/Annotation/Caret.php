<?php
/**
* Class representing a caret annotation
*
* See PDF 32000-1:2008 - 12.5.6.11
*
* @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
* @category   SetaPDF
* @package    SetaPDF_Core
* @subpackage Document
* @license    https://www.setasign.com/ Commercial
*/
class SetaPDF_Core_Document_Page_Annotation_Caret
extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    /**
     * Symbol constant
     *
     * @var string
     */
    const SYMBOL_PARAGRAPH = 'P';

    /**
     * Symbol constant
     *
     * @var string
     */
    const SYMBOL_NONE = 'None';

    /**
     * Creates a caret annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    static public function createAnnotationDictionary($rect)
    {
        return parent::_createAnnotationDictionary(
            $rect,
            SetaPDF_Core_Document_Page_Annotation_Link::TYPE_CARET
        );
    }

    /**
     * The constructor.
     *
     * @param array|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface $objectOrDictionary
     * @throws InvalidArgumentException
     */
    public function __construct($objectOrDictionary)
    {
        $dictionary = $objectOrDictionary instanceof SetaPDF_Core_Type_AbstractType
            ? $objectOrDictionary->ensure(true)
            : $objectOrDictionary;

        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            $args = func_get_args();
            $objectOrDictionary = $dictionary = call_user_func_array(
                ['self', 'createAnnotationDictionary'],
                $args
            );
            unset($args);
        }

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Caret')) {
            throw new InvalidArgumentException('The Subtype entry in an caret annotation shall be "Caret".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Get the name specifying a symbol that shall be associated with the caret.
     *
     * @return string
     */
    public function getSymbol()
    {
        $symbol = $this->_annotationDictionary->getValue('Sy');
        if ($symbol === null) {
            return self::SYMBOL_NONE;
        }

        return $symbol->ensure()->getValue();
    }

    /**
     * Set the name specifying a symbol that shall be associated with the caret.
     *
     * @param string $symbol
     */
    public function setSymbol($symbol)
    {
        if (!$symbol || $symbol === self::SYMBOL_NONE) {
            $this->_annotationDictionary->offsetUnset('Sy');
            return;
        }

        if ($symbol !== self::SYMBOL_PARAGRAPH) {
            throw new InvalidArgumentException('Invalid symbol parameter "' . $symbol . '".');
        }

        $this->_annotationDictionary->offsetSet('Sy', new SetaPDF_Core_Type_Name($symbol));
    }

    /**
     * Get the rectangle describing the difference between the annotation Rect and the actual boundaries of the underlying caret.
     *
     * @return null|SetaPDF_Core_DataStructure_Rectangle
     */
    public function getDifferencesRect()
    {
        $differencesRect = $this->_annotationDictionary->getValue('RD');
        if ($differencesRect === null) {
            return null;
        }

        $differencesRect = $differencesRect->ensure();

        return new SetaPDF_Core_DataStructure_Rectangle($differencesRect);
    }

    /**
     * Set the rectangle describing the difference between the annotation Rect and the actual boundaries of the underlying caret.
     *
     * PDF 32000-1:2008 - Table 180
     * <cite>
     * The four numbers correspond to the differences in default user space between the left, top, right, and bottom
     * coordinates of Rect and those of the inner rectangle, respectively. Each value shall be greater than or equal to
     * 0. The sum of the top and bottom differences shall be less than the height of Rect, and the sum of the left and
     * right differences shall be less than the width of Rect.
     * </cite>
     *
     * @param SetaPDF_Core_DataStructure_Rectangle $differencesRect
     */
    public function setDifferencesRect(SetaPDF_Core_DataStructure_Rectangle $differencesRect)
    {
        if (array_sum($differencesRect->toPhp()) == 0) {
            $this->_annotationDictionary->offsetUnset('RD');
            return;
        }

        $this->_annotationDictionary->offsetSet('RD', $differencesRect->getValue());
    }

}