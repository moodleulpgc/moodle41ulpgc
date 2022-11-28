<?php
/**
 * This file is part of the SetaPDF-Merger Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Item.php 1444 2020-03-17 20:17:45Z jan.slabon $
 */

/**
 * Class representing a collection item.
 *
 * A collection item shall contain the data described in the collection schema for a particular file or folder.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Merger_Collection_Item
{
    /**
     * The collection item dictionary.
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_Dictionary|null $dictionary
     */
    public function __construct(SetaPDF_Core_Type_Dictionary $dictionary = null)
    {
        $this->_dictionary = $dictionary;
    }

    /**
     * Get the dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        if (null === $this->_dictionary) {
            $this->_dictionary = new SetaPDF_Core_Type_Dictionary();
        }

        return $this->_dictionary;
    }

    /**
     * Set the data of an entry in this collection item.
     *
     * @param string $name
     * @param mixed $value To create a subitem, pass an array of 2 values where the first value is the data and the
     *                     second value is the prefix.
     * @param null|string|SetaPDF_Merger_Collection_Schema $type If $type is null the type will be resolved by the PHP
     *                                                           type. Othersie a constant value of
     *                                                           {@link SetaPDF_Merger_Collection_Schema::DATA_TYPE_*}
     *                                                           have to be passed or an instance of
     *                                                           {@link SetaPDF_Merger_Collection_Schema} from which the
     *                                                           type will be resolved automatically and ensured that
     *                                                           the field exists in the schema.
     */
    public function setEntry($name, $value, $type = null)
    {
        if ($type instanceof SetaPDF_Merger_Collection_Schema) {
            $field = $type->getField($name);
            if (false === $field) {
                throw new InvalidArgumentException('Field "' . $name .'" is not defined in schema."');
            }

            $type = $field->getDataType();
        }

        $evaluateType = static function($value) use (&$type, $name) {
            if (null === $type) {
                if (is_string($value)) {
                    $type = SetaPDF_Merger_Collection_Schema::TYPE_STRING;
                } elseif (is_numeric($value)) {
                    $type = SetaPDF_Merger_Collection_Schema::TYPE_NUMBER;
                } elseif ($value instanceof DateTime) {
                    $type = SetaPDF_Merger_Collection_Schema::TYPE_DATE;
                } else {
                    throw new InvalidArgumentException('Data type could not be resolved from entry "' . $name . '"');
                }
            }
        };

        if (is_array($value)) {
            if (!isset($value[0]) || !isset($value[1])) {
                throw new InvalidArgumentException('An array value needs to be passed with 2 values: data and prefix.');
            }
            $evaluateType($value[0]);
            $data = $this->_createPdfValue($value[0], $type);
            $prefix =  new SetaPDF_Core_Type_String($value[1]);
            $value = new SetaPDF_Core_Type_Dictionary(['D' => $data, 'P' => $prefix]);
        } else {
            $evaluateType($value);
            $value = $this->_createPdfValue($value, $type);
        }

        $this->getDictionary()->offsetSet($name, $value);
    }

    /**
     * Set several entries in this item.
     *
     * @param array $data The keys are the entry names.
     * @param SetaPDF_Merger_Collection_Schema $schema
     */
    public function setData(array $data, SetaPDF_Merger_Collection_Schema $schema = null)
    {
        foreach ($data AS $name => $value) {
            $this->setEntry($name, $value, $schema);
        }
    }

    /**
     * Get the data as PHP values.
     *
     * @return array If the value is a collection subitem the value will be an array of 2 values where the first key
     *               is the value and the second the prefix.
     */
    public function getData()
    {
        $result = [];
        foreach ($this->getDictionary() AS $key => $value) {
            if ($key === 'Type') {
                continue;
            }

            if ($value instanceof SetaPDF_Core_Type_Dictionary) {
                $result[$key] = [];
                $data = $value->getValue('D');
                if ($data) {
                    $result[$key][0] = $data->ensure()->getValue();
                }
                $prefix = $value->getValue('P');
                if ($prefix) {
                    $result[$key][1] = $prefix->ensure()->getValue();
                }
            } else {
                $result[$key] = $value->ensure()->getValue();
            }
        }

        return $result;
    }

    /**
     * Prepares a value depending on its type.
     *
     * @param string|number $value
     * @param string $type
     * @return SetaPDF_Core_DataStructure_Date|SetaPDF_Core_Type_Numeric|SetaPDF_Core_Type_String
     * @throws SetaPDF_Exception_NotImplemented
     */
    private function _createPdfValue($value, $type)
    {
        switch ($type) {
            case SetaPDF_Merger_Collection_Schema::TYPE_DATE:
                $value = new SetaPDF_Core_DataStructure_Date($value);
                $value = $value->getValue();
                break;
            case SetaPDF_Merger_Collection_Schema::TYPE_STRING:
                $value = new SetaPDF_Core_Type_String($value);
                break;
            case SetaPDF_Merger_Collection_Schema::TYPE_NUMBER:
                $value = new SetaPDF_Core_Type_Numeric($value);
                break;
            default:
                throw new SetaPDF_Exception_NotImplemented('Unsupported data type: ' . $type);
        }

        return $value;
    }
}