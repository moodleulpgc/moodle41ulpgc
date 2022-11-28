<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: ExtGState.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * Resource class for handling external graphic states
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Resource_PropertyList implements SetaPDF_Core_Resource
{
    /**
     * The properties dictionary
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * The indirect object for this property list
     *
     * @var SetaPDF_Core_Type_IndirectObjectInterface
     */
    protected $_indirectObject;

    /**
     * The constructor.
     *
     * @throws InvalidArgumentException
     * @param SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary $objectOrDictionary
     */
    public function __construct($objectOrDictionary = null)
    {
        if ($objectOrDictionary instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            $this->_indirectObject = $objectOrDictionary;
            $objectOrDictionary = $objectOrDictionary->ensure();
        }

        if (!$objectOrDictionary instanceof SetaPDF_Core_Type_Dictionary) {
            throw new InvalidArgumentException(
                'Parameter has to be type of SetaPDF_Core_Type_Dictionary'
            );
        }

        $this->_dictionary = $objectOrDictionary;
    }

    /**
     * Get the property list dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        return $this->_dictionary;
    }

    /**
     * Gets an indirect object for this property list.
     *
     * @see SetaPDF_Core_Resource::getIndirectObject()
     * @param SetaPDF_Core_Document|null $document
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws InvalidArgumentException
     */
    public function getIndirectObject(SetaPDF_Core_Document $document = null)
    {
        if ($this->_indirectObject === null) {
            if ($document === null) {
                throw new InvalidArgumentException('To initialize a new object $document parameter is not optional!');
            }

            $this->_indirectObject = $document->createNewObject($this->getDictionary());
        }

        return $this->_indirectObject;
    }

    /**
     * Returns the resource type for the property list.
     *
     * @return string
     * @see SetaPDF_Core_Resource::getResourceType()
     */
    public function getResourceType()
    {
        return SetaPDF_Core_Resource::TYPE_PROPERTIES;
    }
}