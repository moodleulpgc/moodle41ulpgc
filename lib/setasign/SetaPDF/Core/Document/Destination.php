<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Destination.php 1444 2020-03-17 20:17:45Z jan.slabon $
 */

/**
 * Class for handling Destinations in a PDF document
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Destination
{
    /**
     * Fit mode constant.
     *
     * <quote>
     * Display the page designated by page, with the coordinates (left, top) positioned at the upper-left corner
     * of the window and the contents of the page magnified by the factor zoom. A null value for any of the parameters
     * left, top, or zoom specifies that the current value of that parameter shall be retained unchanged. A zoom value
     * of 0 has the same meaning as a null value.
     * </quote>
     *
     * @see PDF 32000-1:2008 - Table 151
     */
    const FIT_MODE_XYZ = 'XYZ';

    /**
     * Fit mode constant.
     *
     * <quote>
     * Display the page designated by page, with its contents magnified just enough to fit the entire page within
     * the window both horizontally and vertically. If the required horizontal and vertical magnification factors are
     * different, use the smaller of the two, centering the page within the window in the other dimension.
     * </quote>
     *
     * @see PDF 32000-1:2008 - Table 151
     */
    const FIT_MODE_FIT = 'Fit';

    /**
     * Fit mode constant.
     *
     * <quote>
     * Display the page designated by page, with the vertical coordinate toppositioned at the top edge of the
     * window and the contents of the page magnified just enough to fit the entire width of the page within the window.
     * A null value for top specifies that the current value of that parameter shall be retained unchanged.
     * </quote>
     *
     * @see PDF 32000-1:2008 - Table 151
     */
    const FIT_MODE_FIT_H = 'FitH';

    /**
     * Fit mode constant.
     *
     * <quote>
     * Display the page designated by page, with the horizontal coordinate left positioned at the left edge of
     * the window and the contents of the page magnified just enough to fit the entire height of the page within the
     * window. A null value for left specifies that the current value of that parameter shall be retained unchanged.
     * </quote>
     *
     * @see PDF 32000-1:2008 - Table 151
     */
    const FIT_MODE_FIT_V = 'FitV';

    /**
     * Fit mode constant.
     *
     * <quote>
     * Display the page designated by page, with its contents magnified just enough to fit the rectangle
     * specified by the coordinates left, bottom, right, and top entirely within the window both horizontally and
     * vertically. If the required horizontal and vertical magnification factors are different, use the smaller of the
     * two, centering the rectangle within the window in the other dimension.
     * </quote>
     *
     * @see PDF 32000-1:2008 - Table 151
     */
    const FIT_MODE_FIT_R = 'FitR';

    /**
     * Fit mode constant.
     *
     * <quote>
     * Display the page designated by page, with its contents magnified just enough to fit its bounding box
     * entirely within the window both horizontally and vertically. If the required horizontal and vertical
     * magnification factors are different, use the smaller of the two, centering the bounding box within the window in
     * the other dimension.
     * </quote>
     *
     * @see PDF 32000-1:2008 - Table 151
     */
    const FIT_MODE_FIT_B = 'FitB';

    /**
     * Fit mode constant.
     *
     * <quote>
     * Display the page designated by page, with the vertical coordinate top positioned at the top edge of the
     * window and the contents of the page magnified just enough to fit the entire width of its bounding box within the
     * window. A null value for top specifies that the current value of that parameter shall be retained unchanged.
     * </quote>
     *
     * @see PDF 32000-1:2008 - Table 151
     */
    const FIT_MODE_FIT_BH = 'FitBH';

    /**
     * Fit mode constant.
     *
     * <quote>
     * Display the page designated by page, with the horizontal coordinate left positioned at the left edge of
     * the window and the contents of the page magnified just enough to fit the entire height of its bounding box within
     * the window. A null value for left specifies that the current value of that parameter shall be retained unchanged.
     * </quote>
     *
     * @see PDF 32000-1:2008 - Table 151
     */
    const FIT_MODE_FIT_BV = 'FitBV';

    /**
     * The destination array
     *
     * @var SetaPDF_Core_Type_Array
     */
    protected $_destination;

    /**
     * Find a destination by a name.
     *
     * @param SetaPDF_Core_Document $document
     * @param string $name
     * @return bool|SetaPDF_Core_Document_Destination The destination object or false if it was not found.
     */
    static public function findByName(SetaPDF_Core_Document $document, $name)
    {
        $tree = $document->getCatalog()->getNames()->getTree(SetaPDF_Core_Document_Catalog_Names::DESTS);
        if ($tree === null) {
            $catalogDictionary = $document->getCatalog()->getDictionary();
            if ($catalogDictionary === false || !$catalogDictionary->offsetExists('Dests')) {
                return false;
            }

            /**
             * @var $dests SetaPDF_Core_Type_Dictionary
             */
            $dests = $catalogDictionary->getValue('Dests')->ensure();
            if (!$dests->offsetExists($name)) {
                return false;
            }

            return new self($dests->getValue($name));
        }

        $dest = $tree->get($name);
        if ($dest === false) {
            return false;
        }

        return new self($dest);
    }

    /**
     * Creates an explicit Destination array.
     *
     * This method allows you to pass a flexible argument count after the <code>$fitMode</code> parameter, depending on its value.
     * Following fit modes expect following arguments:
     *
     * {@link SetaPDF_Core_Document_Destination::FIT_MODE_XYZ}
     * <pre>
     * float|null $left, float|null $top, float|null $zoom
     * </pre>
     *
     * {@link SetaPDF_Core_Document_Destination::FIT_MODE_FIT}
     * <pre>
     * - no parameter -
     * </pre>
     *
     * {@link SetaPDF_Core_Document_Destination::FIT_MODE_FIT_H}
     * <pre>
     * float|null $top
     * </pre>
     *
     * {@link SetaPDF_Core_Document_Destination::FIT_MODE_FIT_V}
     * <pre>
     * float|null $left
     * </pre>
     *
     * {@link SetaPDF_Core_Document_Destination::FIT_MODE_FIT_R}
     * <pre>
     * float $left, float $bottom, float $right, float $top
     * </pre>
     *
     * {@link SetaPDF_Core_Document_Destination::FIT_MODE_FIT_B}
     * <pre>
     * - no parameter -
     * </pre>
     *
     * {@link SetaPDF_Core_Document_Destination::FIT_MODE_FIT_BH}
     * <pre>
     * float|null $top
     * </pre>
     *
     * {@link SetaPDF_Core_Document_Destination::FIT_MODE_FIT_BV}
     * <pre>
     * float|null $left
     * </pre>
     *
     * Example:
     * <code>
     * $destinationArray = SetaPDF_Core_Document_Destination::createDestinationArray(
     *     $indirectObject, SetaPDF_Core_Document_Destination::FIT_MODE_XYZ, 30, 50, 200
     * );
     * </code>
     *
     * It is also possible to pass a single array to the <code>$fitMode</code> parameter with all data:
     * <code>
     * $destinationArray = SetaPDF_Core_Document_Destination::createDestinationArray(
     *     $indirectObject, [SetaPDF_Core_Document_Destination::FIT_MODE_XYZ, 30, 50, 200]
     * );
     * </code>
     *
     * @param SetaPDF_Core_Type_IndirectObject|SetaPDF_Core_Type_Numeric $pageObject The indirect object of a page of or
     *                                                                               the page number for the usage in
     *                                                                               remote go-to actions.
     * @param string|array $fitMode The fit mode or an array with the fit mode and all additional arguments
     * @return SetaPDF_Core_Type_Array
     * @throws InvalidArgumentException
     */
    static public function createDestinationArray($pageObject, $fitMode = self::FIT_MODE_FIT)
    {
        $array = new SetaPDF_Core_Type_Array([$pageObject]);
        if (is_array($fitMode)) {
            $parameters = [$array];
            $parameters = array_merge($parameters, $fitMode);
        } else {
            $parameters = [$array, $fitMode];
            $parameters = array_merge($parameters, array_slice(func_get_args(), 2));
        }

        $array = call_user_func_array(['SetaPDF_Core_Document_Destination', '_handleFitModeParameter'], $parameters);

        return $array;
    }

    /**
     * Handle the fitMode parameter and set the correct values in the resulting array.
     *
     * @param SetaPDF_Core_Type_Array $array
     * @param $fitMode
     * @return SetaPDF_Core_Type_Array
     */
    static protected function _handleFitModeParameter(SetaPDF_Core_Type_Array $array, $fitMode)
    {
        // Available modes and parameter count
        $availableFitModes = [
            self::FIT_MODE_XYZ => 3, self::FIT_MODE_FIT => 0, self::FIT_MODE_FIT_H => 1, self::FIT_MODE_FIT_V => 1,
            self::FIT_MODE_FIT_R => 4, self::FIT_MODE_FIT_B => 0, self::FIT_MODE_FIT_BH => 1, self::FIT_MODE_FIT_BV => 1
        ];

        if (!isset($availableFitModes[$fitMode])) {
            throw new InvalidArgumentException(sprintf('Unknown fit mode: %s', $fitMode));
        }

        $array->offsetSet(null, new SetaPDF_Core_Type_Name($fitMode, true));

        $numArgs = func_num_args() - 1;
        for ($i = 2; $i <= $availableFitModes[$fitMode] + 1; $i++) {
            $arg = $numArgs >= $i ? func_get_arg($i) : false;
            if ($arg === false) {
                throw new InvalidArgumentException(
                    sprintf('Wrong parameter count for destination. %s needed', $availableFitModes[$fitMode])
                );
            }

            if ($arg === null) {
                $array->offsetSet(null, SetaPDF_Core_Type_Null::getInstance());
            } else {
                $array->offsetSet(null, new SetaPDF_Core_Type_Numeric($arg));
            }
        }

        return $array;
    }

    /**
     * Creates a destination by page number.
     *
     * All additional arguments are passed to the createDestinationArray() method.
     *
     * Example:
     * <code>
     * $destinationArray = SetaPDF_Core_Document_Destination::createByPageNo(
     *     $document, 123, SetaPDF_Core_Document_Destination::FIT_MODE_XYZ, 30, 50, 200
     * );
     * </code>
     *
     * @see createDestinationArray()
     * @param SetaPDF_Core_Document $document
     * @param int $pageNumber
     * @return SetaPDF_Core_Document_Destination
     */
    static public function createByPageNo(SetaPDF_Core_Document $document, $pageNumber)
    {
        $pages = $document->getCatalog()->getPages();

        $args = func_get_args();
        array_shift($args);
        $args[0] = $pages->getPage($pageNumber)->getPageObject();

        return new self(call_user_func_array(['self', 'createDestinationArray'], $args));
    }

    /**
     * Creates a destination by a page object.
     *
     * All additional arguments are passed to the createDestinationArray() method.
     *
     * @param SetaPDF_Core_Document_Page $page
     * @see createDestinationArray()
     * @return SetaPDF_Core_Document_Destination
     */
    static public function createByPage(SetaPDF_Core_Document_Page $page)
    {
        $args = func_get_args();
        $args[0] = $page->getPageObject();

        return new self(call_user_func_array(['self', 'createDestinationArray'], $args));
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_AbstractType $destination
     * @throws InvalidArgumentException
     */
    public function __construct(SetaPDF_Core_Type_AbstractType $destination)
    {
        $destination = $destination->ensure();

        if ($destination instanceof SetaPDF_Core_Type_Dictionary) {
            if ($destination->offsetExists('D')) {
                $this->_destination = $destination->offsetGet('D')->getValue();
            }
            return;
        }

        if ($destination instanceof SetaPDF_Core_Type_Array) {
            $this->_destination = $destination;
            return;
        }

        throw new InvalidArgumentException('Invalid $destination argument.');
    }

    /**
     * Get the target page number.
     *
     * @param SetaPDF_Core_Document $document
     * @return integer|false
     */
    public function getPageNo(SetaPDF_Core_Document $document)
    {
        $pages = $document->getCatalog()->getPages();

        $indirectReference = $this->_destination->offsetGet(0);
        if ($indirectReference instanceof SetaPDF_Core_Type_IndirectReference) {
            return $pages->getPageNumberByIndirectObject($indirectReference);
        }

        return false;
    }

    /**
     * Get the target page object.
     *
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_Document_Page|false
     */
    public function getPage(SetaPDF_Core_Document $document)
    {
        $pages = $document->getCatalog()->getPages();
        return $pages->getPageByIndirectObject($this->_destination->offsetGet(0));
    }

    /**
     * Get the fit mode and its parameters.
     *
     * @return array Index 0 is the fit mode, all other values are the individual parameters of the fit mode.
     */
    public function getFitMode()
    {
        $result = [];
        $array = $this->getDestinationArray();
        for ($i = 1, $length = count($array); $i < $length; $i++) {
            $result[] = $array->offsetGet($i)->ensure()->getValue();
        }

        return $result;
    }

    /**
     * Set the fit mode.
     *
     * @see createDestinationArray()
     * @param string|array $fitMode
     */
    public function setFitMode($fitMode)
    {
        $array = $this->getDestinationArray();
        while (count($array) > 1) {
            $array->offsetUnset(count($array) - 1);
        }

        if (is_array($fitMode)) {
            $parameters = [$array];
            $parameters = array_merge($parameters, $fitMode);
        } else {
            $parameters = [$array, $fitMode];
            $parameters = array_merge($parameters, array_slice(func_get_args(), 1));
        }

        call_user_func_array(['SetaPDF_Core_Document_Destination', '_handleFitModeParameter'], $parameters);
    }

    /**
     * Get the destination array.
     *
     * @return SetaPDF_Core_Type_Array
     */
    public function getDestinationArray()
    {
        return $this->_destination;
    }

    /**
     * Get the PDF value of this destination.
     *
     * @return SetaPDF_Core_Type_Array
     */
    public function getPdfValue()
    {
        return $this->getDestinationArray();
    }
}