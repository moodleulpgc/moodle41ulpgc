<?php
/**
 * This file is part of the SetaPDF-Core Component
 * 
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Null.php 1475 2020-06-03 10:40:29Z jan.slabon $
 */

/**
 * Class representing a null object
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Type_Null extends SetaPDF_Core_Type_AbstractType
    implements SetaPDF_Core_Type_ScalarValue
{
    /**
     * @var SetaPDF_Core_Type_Null
     */
    static protected $_instance;

    /**
     * Get a singleton instance of this class.
     *
     * @return SetaPDF_Core_Type_Null
     */
    static public function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Parses a php null value to a pdf null string and writes it into a writer.
     *
     * @see SetaPDF_Core_Type_AbstractType
     * @param SetaPDF_Core_WriteInterface $writer
     * @param null $value
     * @return void
     */
    static public function writePdfString(SetaPDF_Core_WriteInterface $writer, $value)
    {
        $writer->write(' null');
    }

    /**
     * Implementation of __clone().
     */
    public function __clone()
    {
        unset($this->_observed);
    }

    /**
     * Add an observer to the object (will never be called by a NULL object).
     *
     * Implementation of the Observer Pattern.
     *
     * @param SplObserver $observer
     */
    public function attach(SplObserver $observer)
    {
        // this type of object will never change a value
    }

    /**
     * Implementation of the abstract setValue() method which is useless for this object type.
     * 
     * @see SetaPDF_Core_Type_AbstractType::setValue()
     * @param null $value
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setValue($value)
    {
        throw new SetaPDF_Core_Type_Exception('PDF Type of NULL cannot have a value.');
    }
    
    /**
     * Get the null value.
     *
     * @see SetaPDF_Core_Type_AbstractType::getValue()
     * @return null
     */
    public function getValue()
    {
        return null;
    }
    
    /**
     * Returns the type as a formatted PDF string.
     *
     * @param SetaPDF_Core_Document|null $pdfDocument
     * @return string
     */
    public function toPdfString(SetaPDF_Core_Document $pdfDocument = null)
    {
        return ' null';
    }

    /**
     * Writes the type as a formatted PDF string to the document.
     *
     * @param SetaPDF_Core_Document $pdfDocument
     */
    public function writeTo(SetaPDF_Core_Document $pdfDocument)
    {
        $pdfDocument->write(' null');
    }

    /**
     * Converts the PDF data type to a PHP data type and returns it.
     *
     * @return null
     */
    public function toPhp()
    {
        return null;
    }    
}