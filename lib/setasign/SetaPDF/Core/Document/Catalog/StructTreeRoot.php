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
 * Class representing the access to the StructTreeRoot dictionary of a document
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_StructTreeRoot
{
    /**
     * The catalog instance
     *
     * @var SetaPDF_Core_Document_Catalog
     */
    protected $_catalog;

    /**
     * @var SetaPDF_Core_DataStructure_NumberTree
     */
    protected $_parentTree;

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
     * Gets and creates the indirect object of the StructTreeRoot entry.
     *
     * @param bool $create
     * @return SetaPDF_Core_Type_IndirectObject|null
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getObject($create = false)
    {
        $catalogDict = $this->_catalog->getDictionary($create);
        if ($catalogDict === null ||
            (!$catalogDict->offsetExists('StructTreeRoot') && $create === false)
        ) {
            return null;
        }

        $object = $catalogDict->getValue('StructTreeRoot');

        if (!$object instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            if ($create === false) {
                return null;
            }

            $dictionary = new SetaPDF_Core_Type_Dictionary([
                'Type' => new SetaPDF_Core_Type_Name('StructTreeRoot', true)
            ]);
            $object = $this->getDocument()->createNewObject($dictionary);

            $catalogDict->offsetSet('StructTreeRoot', $object);
        }

        return $object;
    }

    /**
     * Get and creates the dictionary of the StructTreeRoot entry.
     *
     * @param bool $create
     * @return SetaPDF_Core_Type_AbstractType|null
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getDictionary($create = false)
    {
        $object = $this->getObject($create);
        if ($object === null) {
            return null;
        }

        $dictionary = $object->ensure();

        if (!$dictionary instanceof SetaPDF_Core_Type_Dictionary) {
            throw new SetaPDF_Core_Type_Exception('Invalid data type. Expected dictionary.');
        }

        if (SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Type', null, true) !== 'StructTreeRoot') {
            throw new SetaPDF_Core_Type_Exception('Invalid /Type value. Expected "StructTreeRoot".');
        }

        return $dictionary;
    }

    /**
     * Checks whether the StructTreeRoot dictionary exists or not.
     *
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function exists()
    {
        return $this->getDictionary() !== null;
    }

    /**
     * Get the immediate children of the structure tree root.
     *
     * @return array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getChilds()
    {
        /** @var SetaPDF_Core_Type_Dictionary $dictionary */
        $dictionary = $this->getDictionary();
        if ($dictionary === null) {
            return [];
        }

        $k = $dictionary->getValue('K');
        if ($k === null) {
            return [];
        }

        try {
            $k = $k->ensure();
        } catch (SetaPDF_Core_Type_IndirectReference_Exception $e) {
            $k = null;
            // reference could not be resolved.
        }

        if ($k === null) {
            return [];
        }

        if ($k instanceof SetaPDF_Core_Type_Dictionary) {
            return [$k];
        }

        if ($k instanceof SetaPDF_Core_Type_Array) {
            $childs = [];
            foreach ($k->getValue() as $value) {
                $childs[] = $value->ensure();
            }

            return $childs;
        }

        throw new SetaPDF_Core_Type_Exception('Invalid data type for children of the structure tree root.');
    }

    /**
     * Gets and create the parent tree.
     *
     * @param bool $create
     * @return SetaPDF_Core_DataStructure_NumberTree|null
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getParentTree($create = false)
    {
        if ($this->_parentTree === null) {
            $dictionary = $this->getDictionary($create);
            if ($dictionary === null ||
                (!$dictionary->offsetExists('ParentTree') && $create === false)
            ) {
                return null;
            }

            $object = $dictionary->getValue('ParentTree');

            if (!$object instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
                if ($create === false) {
                    return null;
                }

                $object = $this->getDocument()->createNewObject(new SetaPDF_Core_Type_Dictionary([
                    'Nums' => new SetaPDF_Core_Type_Array([])
                ]));

                $dictionary->offsetSet('ParentTree', $object);
            }

            $this->_parentTree = new SetaPDF_Core_DataStructure_NumberTree($object->ensure(), $this->getDocument());
        }

        return $this->_parentTree;
    }

    /**
     * Get and sets the next key in the parent tree (if it not already exists).
     *
     * @return int
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getParentTreeNextKey()
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null) {
            return 0;
        }

        $parentTreeNextKey = $dictionary->getValue('ParentTreeNextKey');

        if ($parentTreeNextKey instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            try {
                $parentTreeNextKey = $parentTreeNextKey->ensure();
            } catch (SetaPDF_Core_Type_IndirectReference_Exception $e) {
                $parentTreeNextKey = null;
                // reference could not be resolved.
            }
        }

        if (!$parentTreeNextKey instanceof SetaPDF_Core_Type_Numeric) {
            $allKeys = $this->getParentTree(true)->getAll(true);
            $lastKey = count($allKeys) > 0 ? max($this->getParentTree(true)->getAll(true)) : -1;
            $parentTreeNextKey = new SetaPDF_Core_Type_Numeric($lastKey + 1);
            $dictionary->offsetSet('ParentTreeNextKey', $parentTreeNextKey);
        }

        return $parentTreeNextKey->getValue();
    }

    /**
     * Get the current next key for the parent tree and increase it.
     *
     * @return int
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAndIncrementParentTreeNextKey()
    {
        /** @var SetaPDF_Core_Type_Dictionary $dictionary */
        $dictionary = $this->getDictionary(true);
        $current = $this->getParentTreeNextKey();
        $dictionary->getValue('ParentTreeNextKey')->ensure()->setValue($current + 1);

        return $current;
    }

    /**
     * Add a child element.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface $object
     * @param null $beforeIndex
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function addChild(SetaPDF_Core_Type_IndirectObjectInterface $object, $beforeIndex = null)
    {
        /** @var SetaPDF_Core_Type_Dictionary $dictionary */
        $dictionary = $this->getDictionary(true);
        $k = $dictionary->getValue('K');
        if ($k === null) {
            $dictionary->offsetSet('K', $this->getDocument()->createNewObject(new SetaPDF_Core_Type_Array()));
        }

        $k = $dictionary->getValue('K');
        if (!$k->ensure() instanceof SetaPDF_Core_Type_Array) {
            $k = new SetaPDF_Core_Type_Array([$k]);
            $dictionary->offsetSet('K', $this->getDocument()->createNewObject($k));
        }

        /** @var SetaPDF_Core_Type_Array $k */
        $k = $k->ensure();
        if ($beforeIndex !== null) {
            $k->insertBefore($object, $beforeIndex);
        } else {
            $k[] = $object;
        }
    }
}