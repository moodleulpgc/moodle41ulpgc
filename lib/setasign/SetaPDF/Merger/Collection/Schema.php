<?php
/**
 * This file is part of the SetaPDF-Merger Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Schema.php 1411 2020-01-30 15:20:24Z jan.slabon $
 */

/**
 * Class for handling data schemas in PDF Collections/Portfolios/Packages.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Merger_Collection_Schema
{
    /**
     * Constant defining a string data type (value needs to be in PdfDocEncoding or UTF-16BE)
     *
     * @var string
     */
    const TYPE_STRING = 'S';

    /**
     * Constant defining a date data type
     *
     * @var string
     */
    const TYPE_DATE = 'D';

    /**
     * Constant defining a number type
     *
     * @var string
     */
    const TYPE_NUMBER = 'N';

    /**
     * Constant defining the file name property
     *
     * @var string
     */
    const DATA_FILE_NAME = 'F';

    /**
     * Constant defining the description property
     *
     * @var string
     */
    const DATA_DESCRIPTION = 'Desc';

    /**
     * Constant defining the modification date property
     *
     * @var string
     */
    const DATA_MODIFICATION_DATE = 'ModDate';

    /**
     * Constant defining the creation date property
     *
     * @var string
     */
    const DATA_CREATION_DATE = 'CreationDate';

    /**
     * Constant defining the size property
     *
     * @var string
     */
    const DATA_SIZE = 'Size';

    /**
     * Constant defining the compressed size property
     *
     * @var string
     */
    const DATA_COMPRESSED_SIZE = 'CompressedSize';

    /**
     * The collection instance.
     *
     * @var SetaPDF_Merger_Collection
     */
    protected $_collection;

    /**
     * The constructor.
     *
     * @param SetaPDF_Merger_Collection $collection
     */
    public function __construct(SetaPDF_Merger_Collection $collection)
    {
        $this->_collection = $collection;
    }

    /**
     * Remove cycled references.
     */
    public function cleanUp()
    {
        $this->_collection = null;
    }

    /**
     * Get the collection instance.
     *
     * @return SetaPDF_Merger_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Get and/or create the schema dictionary.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary($create = false)
    {
        $collectionDict = $this->_collection->getDictionary($create);
        if (null === $collectionDict) {
            return null;
        }

        if (!$collectionDict->offsetExists('Schema')) {
            if (false === $create) {
                return null;
            }

            $collectionDict->offsetSet(
                'Schema',
                $this->_collection->getDocument()->createNewObject(new SetaPDF_Core_Type_Dictionary())
            );
        }

        /**
         * @var SetaPDF_Core_Type_Dictionary $dictionary
         */
        $dictionary = $collectionDict->getValue('Schema')->ensure(true);
        return $dictionary;
    }

    /**
     * Get all field instances.
     *
     * @return SetaPDF_Merger_Collection_Schema_Field[]
     */
    public function getFields()
    {
        $fields = [];
        $dict = $this->getDictionary();
        if (null === $dict) {
            return $fields;
        }

        foreach ($dict->getKeys() AS $name) {
            if ($name == 'Type') {
                continue;
            }

            $fields[$name] = $this->getField($name);
        }

        return $fields;
    }

    /**
     * Check if a field exists.
     *
     * @param string $name
     * @return boolean
     */
    public function hasField($name)
    {
        $dict = $this->getDictionary();
        if ($dict === null || !$dict->offsetExists($name)) {
            return false;
        }

        return true;
    }

    /**
     * Get a field instance by its name.
     *
     * @param string $name
     * @return bool|SetaPDF_Merger_Collection_Schema_Field
     */
    public function getField($name)
    {
        if (!$this->hasField($name)) {
            return false;
        }

        $dict = $this->getDictionary();
        return new SetaPDF_Merger_Collection_Schema_Field($dict->getValue($name)->ensure());
    }

    /**
     * Add a field to the schema.
     *
     * @param string $name The internal field key name.
     * @param null|string|SetaPDF_Merger_Collection_Schema_Field $fieldOrFieldName The field name or an instance of a field.
     * @param null|string $dataType The data field or type. See class constants for possible values.
     * @param null|integer $order The relative order of the field name in the user interface. You should set this,
     *                            otherwise you will get an unexpected result in different PDF viewers.
     * @return SetaPDF_Merger_Collection_Schema_Field
     * @see SetaPDF_Merger_Collection_Schema::DATA_FILE_NAME
     * @see SetaPDF_Merger_Collection_Schema::DATA_DESCRIPTION
     * @see SetaPDF_Merger_Collection_Schema::DATA_SIZE
     * @see SetaPDF_Merger_Collection_Schema::DATA_CREATION_DATE
     * @see SetaPDF_Merger_Collection_Schema::DATA_MODIFICATION_DATE
     * @see SetaPDF_Merger_Collection_Schema::DATA_COMPRESSED_SIZE
     * @see SetaPDF_Merger_Collection_Schema::TYPE_NUMBER
     * @see SetaPDF_Merger_Collection_Schema::TYPE_STRING
     * @see SetaPDF_Merger_Collection_Schema::TYPE_DATE
     */
    public function addField($name, $fieldOrFieldName = null, $dataType = null, $order = null)
    {
        if ($fieldOrFieldName instanceof SetaPDF_Merger_Collection_Schema_Field) {
            $field = $fieldOrFieldName;
        } else {
            if (!$dataType) {
                throw new InvalidArgumentException('Missing data type parameter for field "' . $name .'".');
            }

            $field = SetaPDF_Merger_Collection_Schema_Field::create(
                $fieldOrFieldName, $dataType
            );
        }

        if ('Type' === $name) {
            throw new InvalidArgumentException('Field name cannot be "Type".');
        }

        if ($order !== null) {
            $field->setOrder($order);
        }

        $dict = $this->getDictionary(true);
        $dict->offsetSet($name, $field->getDictionary());

        return $field;
    }

    /**
     * Adds several fields to the schema.
     *
     * @param array $fields The keys are the internal field key name while the values are passed as additional parameter
     *                      to the {@link SetaPDF_Merger_Collection_Schema::addField() addField()} method.
     * @see SetaPDF_Merger_Collection_Schema::addField()
     */
    public function addFields(array $fields)
    {
        foreach ($fields as $keyName => $fieldData) {
            $arguments = [$keyName];
            if (!is_array($fieldData)) {
                $fieldData = [$fieldData];
            }

            foreach ($fieldData as $_fieldData) {
                $arguments[] = $_fieldData;
            }

            call_user_func_array([$this, 'addField'], $arguments);
        }
    }

    /**
     * Remove a field from the schema.
     *
     * @param $name
     * @return boolean
     */
    public function removeField($name)
    {
        if (!$this->hasField($name)) {
            return false;
        }

        $dict = $this->getDictionary();
        $dict->offsetUnset($name);

        return true;
    }
}