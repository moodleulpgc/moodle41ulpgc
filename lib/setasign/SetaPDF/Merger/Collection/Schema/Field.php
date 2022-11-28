<?php
/**
 * This file is part of the SetaPDF-Merger Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Field.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Class representing a field in a schema.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Merger_Collection_Schema_Field
{
    /**
     * The fields dictionary.
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * Create a schema field by a name and data type.
     *
     * @param string $fieldName
     * @param string $dataType
     * @param string $encoding
     * @return SetaPDF_Merger_Collection_Schema_Field
     */
    static public function create($fieldName, $dataType, $encoding = 'UTF-8')
    {
        $instance = new self(new SetaPDF_Core_Type_Dictionary());
        $instance->setName($fieldName, $encoding);
        $instance->setDataType($dataType);

        return $instance;
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_Dictionary $dictionary
     */
    public function __construct(SetaPDF_Core_Type_Dictionary $dictionary)
    {
        $this->_dictionary = $dictionary;
    }

    /**
     * Get the fields dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        return $this->_dictionary;
    }

    /**
     * Set the data type.
     *
     * @param string $dataType
     * @return SetaPDF_Merger_Collection_Schema_Field
     */
    public function setDataType($dataType)
    {
        if (!in_array($dataType, [
            SetaPDF_Merger_Collection_Schema::TYPE_STRING,
            SetaPDF_Merger_Collection_Schema::TYPE_DATE,
            SetaPDF_Merger_Collection_Schema::TYPE_NUMBER,
            SetaPDF_Merger_Collection_Schema::DATA_FILE_NAME,
            SetaPDF_Merger_Collection_Schema::DATA_DESCRIPTION,
            SetaPDF_Merger_Collection_Schema::DATA_MODIFICATION_DATE,
            SetaPDF_Merger_Collection_Schema::DATA_CREATION_DATE,
            SetaPDF_Merger_Collection_Schema::DATA_SIZE,
            SetaPDF_Merger_Collection_Schema::DATA_COMPRESSED_SIZE
        ])) {
            throw new InvalidArgumentException('Invalid data type value: "' . $dataType . '"');
        }

        $this->getDictionary()->offsetSet('Subtype', new SetaPDF_Core_Type_Name($dataType));

        return $this;
    }

    /**
     * Get the data type.
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->getDictionary()->getValue('Subtype')->ensure()->getValue();
    }

    /**
     * Set the textual field name that shall be presented to the user by the interactive PDF processor.
     *
     * @param string $name
     * @param string $encoding
     * @return SetaPDF_Merger_Collection_Schema_Field
     */
    public function setName($name, $encoding = 'UTF-8')
    {
        $name = SetaPDF_Core_Encoding::toPdfString($name, $encoding);
        $this->getDictionary()->offsetSet('N', new SetaPDF_Core_Type_String($name));

        return $this;
    }

    /**
     * Get the textual field name that shall be presented to the user by the interactive PDF processor.
     *
     * @param string $encoding
     * @return string
     */
    public function getName($encoding = 'UTF-8')
    {
        $name = $this->getDictionary()->getValue('N')->ensure()->getValue();
        return SetaPDF_Core_Encoding::convertPdfString($name, $encoding);
    }

    /**
     * Set the relative order of the field name in the user interface.
     *
     * If you set it, you should set this in all fields. Otherwise you will get an unexpected result in different
     * PDF viewers.
     *
     * @param integer $order
     * @return SetaPDF_Merger_Collection_Schema_Field
     */
    public function setOrder($order)
    {
        $dict = $this->getDictionary();
        if (null === $order) {
            $dict->offsetUnset('O');
            return $this;
        }

        $dict->offsetSet('O', new SetaPDF_Core_Type_Numeric((int)$order));

        return $this;
    }

    /**
     * Get the relative order of the field name in the user interface.
     *
     * @return integer|null
     */
    public function getOrder()
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('O')) {
            return null;
        }

        return (int)$dict->getValue('O')->ensure()->getValue();
    }

    /**
     * Set the initial visibility of the field in the user interface.
     *
     * @param boolean $visibility
     * @return SetaPDF_Merger_Collection_Schema_Field
     */
    public function setVisibility($visibility)
    {
        $dict = $this->getDictionary();
        if (null === $visibility) {
            $dict->offsetUnset('V');
            return $this;
        }

        $dict->offsetSet('V', new SetaPDF_Core_Type_Boolean($visibility));

        return $this;
    }

    /**
     * Get the initial visibility of the field in the user interface.
     *
     * @return boolean
     */
    public function getVisibility()
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('V')) {
            return true;
        }

        return $dict->getValue('V')->ensure()->getValue();
    }

    /**
     * Set a flag indicating whether the interactive PDF processor should provide support for editing the field value.
     *
     * @param boolean $allowEdit
     * @return SetaPDF_Merger_Collection_Schema_Field
     */
    public function setAllowEdit($allowEdit)
    {
        $dict = $this->getDictionary();
        if (null === $allowEdit) {
            $dict->offsetUnset('E');
            return $this;
        }

        $dict->offsetSet('E', new SetaPDF_Core_Type_Boolean($allowEdit));

        return $this;
    }

    /**
     * Get a flag indicating whether the interactive PDF processor should provide support for editing the field value.
     *
     * @return boolean
     */
    public function getAllowEdit()
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('E')) {
            return false;
        }

        return $dict->getValue('E')->ensure()->getValue();
    }
}