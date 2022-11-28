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
 * Class representing the access to the mark information dictionary.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_MarkInfo
{
    /**
     * The catalog instance
     *
     * @var SetaPDF_Core_Document_Catalog
     */
    protected $_catalog;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document_Catalog $catalog
     */
    public function __construct(SetaPDF_Core_Document_Catalog $catalog)
    {
        $this->_catalog = $catalog;
    }

    /**
     * Get the document instance.
     *
     * @return SetaPDF_Core_Document
     */
    public function getDocument()
    {
        return $this->_catalog->getDocument();
    }

    /**
     * Release memory and cycled references.
     */
    public function cleanUp()
    {
        $this->_catalog = null;
    }

    /**
     * Get the mark info dictionary.
     *
     * @param bool $create
     * @return array|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary|null
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getDictionary($create = false)
    {
        $catalogDict = $this->_catalog->getDictionary($create);
        if ($catalogDict === null ||
            (!$catalogDict->offsetExists('MarkInfo') && $create === false)
        ) {
            return null;
        }

        $dictionary = $catalogDict->getValue('MarkInfo');
        if ($dictionary === null && $create === false) {
            return null;
        }

        if ($dictionary !== null) {
            try {
                $dictionary = $dictionary->ensure();
            } catch (SetaPDF_Core_Type_IndirectReference_Exception $e) {
                $dictionary = null;
                // reference could not be resolved.
            }
        }

        if ($dictionary === null) {
            if ($create === false) {
                return null;
            }

            $dictionary = new SetaPDF_Core_Type_Dictionary();
            $catalogDict->offsetSet('MarkInfo', $dictionary);
        }

        return $dictionary;
    }

    /**
     * Checks if a entry is set or not.
     *
     * @param string $name
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    protected function _is($name)
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null) {
            return false;
        }

        $value = $dictionary->getValue($name);
        if ($value === null) {
            return false;
        }

        try {
            $value = $value->ensure();
        } catch (SetaPDF_Core_Type_IndirectReference_Exception $e) {
            return false;
        }

        return (boolean)$value->getValue();
    }

    /**
     * Set a value in the mark information dictionary.
     *
     * @param string $name
     * @param boolean $value
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    protected function _set($name, $value)
    {
        if ($this->_is($name) === $value) {
            return;
        }

        $dictionary = $this->getDictionary(true);
        if ($value) {
            $dictionary->offsetSet($name, new SetaPDF_Core_Type_Boolean(true));
        } else {
            $dictionary->offsetUnset($name);
        }
    }

    /**
     * Checks the flag indicating whether the document conforms to tagged PDF conventions.
     *
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function isMarked()
    {
        return $this->_is('Marked');
    }

    /**
     * Set the flag indicating whether the document conforms to tagged PDF conventions.
     *
     * @param bool $marked
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function setMarked($marked = true)
    {
        $this->_set('Marked', $marked);
    }

    /**
     * Checks the flag indicating the presence of structure elements that contain user properties attributes.
     *
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function hasUserProperties()
    {
        return $this->_is('UserProperties');
    }

    /**
     * Set the flag indicating the presence of structure elements that contain user properties attributes.
     *
     * @param bool $userProperties
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function setUserProperties($userProperties = true)
    {
        $this->_set('UserProperties', $userProperties);
    }

    /**
     * Checks the flag indicating the presence of tag suspects.
     *
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function hasSuspects()
    {
        return $this->_is('Suspects');
    }

    /**
     * Set the flag indicating the presence of tag suspects.
     *
     * @param bool $suspects
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function setSuspects($suspects = true)
    {
        $this->_set('Suspects', $suspects);
    }
}