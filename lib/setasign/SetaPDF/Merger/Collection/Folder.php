<?php
/**
 * This file is part of the SetaPDF-Merger Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Folder.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * Class representing a folder in a PDF Collection/Portfolio/Package.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Merger_Collection_Folder
{
    /**
     * The collection instance.
     *
     * @var SetaPDF_Merger_Collection
     */
    protected $_collection;

    /**
     * The folder dictionary.
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * The indirect object for this folder.
     *
     * @var null|SetaPDF_Core_Type_IndirectObject
     */
    protected $_indirectObject;

    /**
     * The constructor.
     *
     * @param SetaPDF_Merger_Collection $collection
     * @param string|SetaPDF_Core_Type_IndirectObjectInterface $indirectObjectOrName A folder name or an indirect
     *                                                                               object/reference to a dictionary
     *                                                                               representing a folder.
     */
    public function __construct(
        SetaPDF_Merger_Collection $collection,
        $indirectObjectOrName
    )
    {
        $this->_collection = $collection;

        if ($indirectObjectOrName instanceof SetaPDF_Core_Type_IndirectReference) {
            $indirectObjectOrName = $indirectObjectOrName->getValue();
        }

        if ($indirectObjectOrName instanceof SetaPDF_Core_Type_IndirectObject) {
            $this->_indirectObject = $indirectObjectOrName;
            $this->_indirectObject->observe();
            $this->_dictionary = $indirectObjectOrName->ensure(true);
        } else {
            $this->setName($indirectObjectOrName);
        }
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
     * Get the indirect reference for this folder.
     *
     * @return SetaPDF_Core_Type_IndirectObject
     */
    public function getIndirectObject()
    {
        if (null === $this->_indirectObject) {
            $this->_indirectObject = $this->getCollection()->getDocument()->createNewObject($this->getDictionary());
        }

        return $this->_indirectObject;
    }

    /**
     * Get the dictionary for this folder.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        if (null === $this->_dictionary) {
            $date = new SetaPDF_Core_DataStructure_Date();
            $this->_dictionary = new SetaPDF_Core_Type_Dictionary([
                'Type' => new SetaPDF_Core_Type_Name('Folder'),
                'CreationDate' => $date->getValue(),
                'ModDate' => $date->getValue()
            ]);
        }

        return $this->_dictionary;
    }

    /**
     * Set the folder name.
     *
     * @param string $name The folder name in UTF-8 encoding.
     * @return SetaPDF_Merger_Collection_Folder
     */
    public function setName($name)
    {
        // create conforming file name
        $name = str_replace(["\x00", "\x2F", "\x5C", "\x3A", "\x2A", "\x22", "\x3C", "\x3E", "\x7C"], '', $name);
        $name = rtrim($name, '.');

        $this->getDictionary()->offsetSet(
            'Name',
            new SetaPDF_Core_Type_String(SetaPDF_Core_Encoding::toPdfString($name))
        );

        return $this;
    }

    /**
     * Get the folder name.
     *
     * @return string The folder name in UTF-8 encoding.
     */
    public function getName()
    {
        $name = $this->getDictionary()->getValue('Name')->ensure()->getValue();
        return SetaPDF_Core_Encoding::convertPdfString($name);
    }

    /**
     * Get an folder instance of the parent folder.
     *
     * @return SetaPDF_Merger_Collection_Folder|false
     */
    public function getParent()
    {
        /** @var SetaPDF_Core_Type_Dictionary $dict */
        $dict = $this->getIndirectObject()->ensure();
        $parent = $dict->getValue('Parent');
        if (!$parent) {
            return false;
        }

        return new self($this->_collection, $parent);
    }

    /**
     * Set a parent folder.
     *
     * @param SetaPDF_Merger_Collection_Folder $parent
     * @return SetaPDF_Merger_Collection_Folder
     */
    public function setParent(SetaPDF_Merger_Collection_Folder $parent)
    {
        if ($this->getCollection() !== $parent->getCollection()) {
            throw new InvalidArgumentException('The folders needs to be created with the same collection instance.');
        }

        $indirectObject = $this->getIndirectObject();

        if ($indirectObject->getObjectIdent() === $this->getCollection()->getRootFolder(true)->getIndirectObject()->getObjectIdent()) {
            throw new BadMethodCallException('This folder instance is a root folder. A parent cannot be set.');
        }

        $parentIndirectObject = $parent->getIndirectObject();

        if ($indirectObject->getObjectIdent() === $parentIndirectObject->getObjectIdent()) {
            throw new InvalidArgumentException('Passing the same instance as a parent is impossible.');
        }

        // check if $this is a parent or grandparent of $parent
        $_parent = $parentIndirectObject;
        while ($_parent->ensure()->offsetExists('Parent')) {
            /** @var SetaPDF_Core_Type_Dictionary $_parent */
            $_parent = $_parent->ensure();
            $_parent = $_parent->getValue('Parent');
            if ($_parent->getObjectIdent() === $indirectObject->getObjectIdent()) {
                throw new InvalidArgumentException('This folder is a parent or grand parent of new "parent".');
            }
        }

        // check if a sibling has the same name:
        $parentSubfolders = $parent->getSubfolders();
        foreach ($parentSubfolders as $parentSubfolder) {
            if ($parentSubfolder->getName() === $this->getName()) {
                throw new InvalidArgumentException('A folder with the same name already exists in the parent folder.');
            }
        }

        // force an ID creation
        $this->getId();
        $dict = $this->getDictionary();

        $currentParent = $dict->getValue('Parent');
        if ($currentParent) {
            $currentParent = $currentParent->getValue();
            /* if this node is the first child:
             * - find next and make this to the direct child
             * - if no child can be found, remove the Child entry
             */
            $firstSibling = $currentParent->ensure()->getValue('Child');
            if ($firstSibling->getObjectIdent() == $indirectObject->getObjectIdent()) {
                if ($dict->offsetExists('Next')) {
                    $currentParent->ensure()->offsetSet('Child', $dict->getValue('Next'));
                } else {
                    $currentParent->ensure()->offsetUnset('Child');
                }

                $dict->offsetUnset('Next');

            /* - search the previous node
             * - if current node has a next value set it in the previous node
             * - otherwise remove next from the previous node
             */
            } else {
                $previousNode = $firstSibling;
                while ($previousNode->ensure()->offsetExists('Next')) {
                    $next = $previousNode->ensure()->getValue('Next');
                    if ($next->getValue()->getObjectIdent() == $indirectObject->getObjectIdent()) {
                        break;
                    }
                    $previousNode = $next;
                }

                if ($dict->offsetExists('Next')) {
                    $previousNode->ensure()->offsetSet('Next', $dict->getValue('Next'));
                    $dict->offsetUnset('Next');
                } else {
                    $previousNode->ensure()->offsetUnset('Next');
                }
            }
        }

        $dict->offsetSet('Parent', $parentIndirectObject);

        /** @var SetaPDF_Core_Type_Dictionary $parentDict */
        $parentDict = $parentIndirectObject->ensure();
        if (!$parentDict->offsetExists('Child')) {
            $parentDict->offsetSet('Child', $indirectObject);
        } else {
            $childOfParent = $parentDict->getValue('Child');
            while ($childOfParent->ensure()->offsetExists('Next')) {
                $childOfParent = $childOfParent->getValue('Next');
            }

            $childOfParent->ensure(true)->offsetSet('Next', $indirectObject);
        }

        return $this;
    }

    /**
     * Get and/or create the folder id.
     *
     * @return integer
     * @throws SetaPDF_Merger_Exception
     */
    public function getId()
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('ID')) {
            $root = $this->getCollection()->getRootFolder(true);
            $rootDict = $root->getDictionary();
            /**
             * @var SetaPDF_Core_Type_Array $free
             */
            $free = $rootDict->getValue('Free')->ensure();
            for ($i = 0, $n = $free->count(); $i < $n; $i += 2) {
                $start = $free->offsetGet($i)->ensure();
                $end = $free->offsetGet($i + 1)->ensure();

                if ($start->getValue() < $end->getValue()) {
                    $id = (int)$start->getValue();
                    $start->setValue($id + 1);
                    break;
                }
            }

            if (!isset($id)) {
                throw new SetaPDF_Merger_Exception('There are no free folder ids in this collection.');
            }

            $dict->offsetSet('ID', new SetaPDF_Core_Type_Numeric($id));
        }

        return (int)$dict->getValue('ID')->ensure()->getValue();
    }

    /**
     * Add a file to this folder.
     *
     * @param SetaPDF_Core_Reader_ReaderInterface|string $pathOrReader A reader instance or a path to a file.
     * @param string $filename The filename in UTF-8 encoding.
     * @param null|string $description The description of the file.
     * @param array $fileStreamParams See {@link SetaPDF_Core_EmbeddedFileStream::setParams() SetaPDF_Core_EmbeddedFileStream::setParams()} method.
     * @param null|string $mimeType The subtype of the embedded file. Shall conform to the MIME media type names defined
     *                              in Internet RFC 2046
     * @param null|array|SetaPDF_Merger_Collection_Item $collectionItem The data described by the collection schema.
     * @return string The name that was used to register the file specification in the embedded files name tree.
     */
    public function addFile(
        $pathOrReader,
        $filename,
        $description = null,
        array $fileStreamParams = [],
        $mimeType = null,
        $collectionItem = null
    )
    {
        $fileSpecification = SetaPDF_Core_FileSpecification::createEmbedded(
            $this->getCollection()->getDocument(), $pathOrReader, $filename, $fileStreamParams, $mimeType
        );

        if ($description !== null) {
            $fileSpecification->setDescription($description);
        }

        if ($collectionItem !== null) {
            if (!$collectionItem instanceof SetaPDF_Merger_Collection_Item) {
                $_collectionItem = $collectionItem;
                $collectionItem = new SetaPDF_Merger_Collection_Item();
                $collectionItem->setData($_collectionItem, $this->getCollection()->getSchema());
            }

            $fileSpecification->setCollectionItem($collectionItem->getDictionary());
        }

        $embeddedFiles = $this->getCollection()->getDocument()->getCatalog()->getNames()->getEmbeddedFiles();
        $name = "\xFE\xFF" . SetaPDF_Core_Encoding::convert('<' . $this->getId() . '>' . $filename, 'UTF-8', 'UTF-16BE');
        $embeddedFiles->add($name, $fileSpecification);

        return $name;
    }

    /**
     * Set the collection item data.
     *
     * The data described by the collection schema.
     *
     * @param SetaPDF_Merger_Collection_Item|null $item
     * @return SetaPDF_Merger_Collection_Folder
     */
    public function setCollectionItem(SetaPDF_Merger_Collection_Item $item = null)
    {
        $dict = $this->getDictionary();
        if (null === $item) {
            $dict->offsetUnset('CI');
            return $this;
        }

        $dict->offsetSet('CI', $item->getDictionary());

        return $this;
    }

    /**
     * Get the collection item data.
     *
     * The data described by the collection schema.
     *
     * @return null|SetaPDF_Merger_Collection_Item
     */
    public function getCollectionItem()
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('CI')) {
            return null;
        }

        /**
         * @var SetaPDF_Core_Type_Dictionary $dictionary
         */
        $dictionary = $dict->getValue('CI')->ensure(true);

        return new SetaPDF_Merger_Collection_Item($dictionary);
    }

    /**
     * Set the descriptive text associated with the file specification.
     *
     * @param string|null $desc
     * @param string $encoding
     * @return SetaPDF_Merger_Collection_Folder
     */
    public function setDescription($desc, $encoding = 'UTF-8')
    {
        $dict = $this->getDictionary();
        if (null === $desc) {
            $dict->offsetUnset('Desc');
            return $this;
        }

        $dict->offsetSet(
            'Desc',
            new SetaPDF_Core_Type_String(SetaPDF_Core_Encoding::toPdfString($desc, $encoding))
        );

        return $this;
    }

    /**
     * Get the descriptive text associated with the file specification.
     *
     * @param string $encoding
     * @return null|string
     */
    public function getDescription($encoding = 'UTF-8')
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('Desc')) {
            return null;
        }

        return SetaPDF_Core_Encoding::convertPdfString(
            $dict->getValue('Desc')->ensure()->getValue(),
            $encoding
        );
    }

    /**
     * Set the date the folder was first created.
     *
     * @param DateTime|null $creationDate
     * @return SetaPDF_Merger_Collection_Folder
     */
    public function setCreationDate(DateTime $creationDate = null)
    {
        $dict = $this->getDictionary();
        if (null === $creationDate) {
            $dict->offsetUnset('CreationDate');
            return $this;
        }

        $date = new SetaPDF_Core_DataStructure_Date($creationDate);
        $dict->offsetSet('CreationDate', $date->getValue());

        return $this;
    }

    /**
     * Get the date the folder was first created.
     *
     * @return null|DateTime
     */
    public function getCreationDate()
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('CreationDate')) {
            return null;
        }

        return SetaPDF_Core_DataStructure_Date::stringToDateTime(
            $dict->getValue('CreationDate')->ensure()->getValue()
        );
    }

    /**
     * Set the date of the most recent change to immediate child files or folders of this folder.
     *
     * @param DateTime|null $creationDate
     * @return SetaPDF_Merger_Collection_Folder
     */
    public function setModificationDate(DateTime $creationDate = null)
    {
        $dict = $this->getDictionary();
        if (null === $creationDate) {
            $dict->offsetUnset('ModDate');
            return $this;
        }

        $date = new SetaPDF_Core_DataStructure_Date($creationDate);
        $dict->offsetSet('ModDate', $date->getValue());

        return $this;
    }

    /**
     * Get the date of the most recent change to immediate child files or folders of this folder.
     *
     * @return null|DateTime
     */
    public function getModificationDate()
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('ModDate')) {
            return null;
        }

        return SetaPDF_Core_DataStructure_Date::stringToDateTime(
            $dict->getValue('ModDate')->ensure()->getValue()
        );
    }

    /**
     * Add a subfolder to this folder.
     *
     * @param string $name The folder name.
     * @param null|string $description The description of the folder.
     * @param DateTime|null $creationDate If null "now" will be used.
     * @param DateTime|null $modificationDate If null "now" will be used.
     * @param null|array|SetaPDF_Merger_Collection_Item $collectionItem The data described by the collection schema.
     * @return SetaPDF_Merger_Collection_Folder
     */
    public function addFolder(
        $name,
        $description = null,
        DateTime $creationDate = null,
        DateTime $modificationDate = null,
        $collectionItem = null
    )
    {
        $folder = new SetaPDF_Merger_Collection_Folder($this->getCollection(), $name);
        $folder->setParent($this);

        if ($description !== null) {
            $folder->setDescription($description);
        }

        if ($collectionItem !== null) {
            if (!$collectionItem instanceof SetaPDF_Merger_Collection_Item) {
                $_collectionItem = $collectionItem;
                $collectionItem = new SetaPDF_Merger_Collection_Item();
                $collectionItem->setData($_collectionItem, $this->getCollection()->getSchema());
            }

            $folder->setCollectionItem($collectionItem);
        }

        if (null !== $creationDate) {
            $folder->setCreationDate($creationDate);
        } else {
            $folder->setCreationDate(new DateTime());
        }

        if (null !== $modificationDate) {
            $folder->setModificationDate($modificationDate);
        } else {
            $folder->setModificationDate(new DateTime());
        }

        return $folder;
    }

    /**
     * Checks whether this folder has subfolders or not.
     *
     * @return boolean
     */
    public function hasSubfolders()
    {
        return $this->getDictionary()->offsetExists('Child');
    }

    /**
     * Get a folder by its name in UTF-8 encoding.
     *
     * @param string $name
     * @return false|SetaPDF_Merger_Collection_Folder
     */
    public function getSubfolder($name)
    {
        $dict = $this->getDictionary();
        if (!$dict->offsetExists('Child')) {
            return false;
        }

        $child = new self($this->getCollection(), $dict->getValue('Child'));
        if ($child->getName() === $name) {
            return $child;
        }

        while ($child->getIndirectObject()->ensure()->offsetExists('Next')) {
            $child = new self($this->getCollection(), $child->getIndirectObject()->ensure()->getValue('Next'));
            if ($child->getName() === $name) {
                return $child;
            }
        }

        return false;
    }

    /**
     * Get all subfolders of this folder.
     *
     * @return SetaPDF_Merger_Collection_Folder[]
     */
    public function getSubfolders()
    {
        $dict = $this->getDictionary();
        $result = [];
        if (!$dict->offsetExists('Child')) {
            return $result;
        }

        $child = $dict->getValue('Child');
        $result[] = new self($this->getCollection(), $child);
        while ($child->ensure()->offsetExists('Next')) {
            $child = $child->ensure()->getValue('Next');
            $result[] = new self($this->getCollection(), $child);
        }

        return $result;
    }

    /**
     * Get all file specifications defined for this folder.
     *
     * @return array
     */
    protected function _getFiles()
    {
        $id = $this->getId();
        $prefix = '<' . $id . '>';

        $embeddedFiles = $this->getCollection()->getDocument()->getCatalog()
            ->getNames()->getTree(SetaPDF_Core_Document_Catalog_Names::EMBEDDED_FILES);

        if (null === $embeddedFiles) {
            return [];
        }

        $all = $embeddedFiles->getAll();
        $result = [];
        foreach ($all AS $name => $keyAndValue) {
            $_name = SetaPDF_Core_Encoding::convertPdfString($name);
            if (($id === 0 && strpos($_name, '<') !== 0) ||
                strpos($_name, $prefix) === 0
            ) {
                $result[$name] = $keyAndValue['value'];
            }
        }

        return $result;
    }

    /**
     * Get all files in this folder.
     *
     * @return SetaPDF_Core_FileSpecification[] The keys are the names with which the files are registered in the
     *                                          embedded files name tree.
     */
    public function getFiles()
    {
        $files = $this->_getFiles();
        $result = [];
        foreach ($files AS $name => $value) {
            $result[$name] = new SetaPDF_Core_FileSpecification($value->ensure());
        }

        return $result;
    }

    /**
     * Get a file in this folder by its name in the embedded files name tree.
     *
     * @param string $name
     * @return false|SetaPDF_Core_FileSpecification
     */
    public function getFile($name)
    {
        $id = $this->getId();
        $prefix = '<' . $id . '>';
        $_name = SetaPDF_Core_Encoding::convertPdfString($name);

        if (($id === 0 && strpos($_name, '<') !== 0) ||
            strpos($_name, $prefix) === 0
        ) {
            return $this->getCollection()->getFile($name);
        }

        return false;
    }

    /**
     * Get files and folders in this folder.
     *
     * @return array
     */
    public function getChilds()
    {
        $result = $this->getSubfolders();
        foreach ($this->getFiles() AS $fileSpecification) {
            $result[] = $fileSpecification;
        }

        return $result;
    }

    /**
     * Delete a file within this folder.
     *
     * @param string $fileName The file name (in PDFDoc or UTF-16BE encoding) needs to be prefixed with the folder id.
     * @return bool
     */
    public function deleteFile($fileName)
    {
        $tempFileName = SetaPDF_Core_Encoding::convertPdfString($fileName);
        $id = $this->getId();
        if (strpos($tempFileName, '<' . $id . '>') !== 0) {
            if ($id !== 0 && strpos($tempFileName, '<') !== false) {
                return false;
            }
        }

        return $this->getCollection()->deleteFile($fileName);
    }

    /**
     * Delete this folder, subfolders and files.
     *
     * @param bool $recursive Whether folders should be delete folders recursively or not.
     * @param bool $removeEmbeddedFiles Whether file specifications in this folder should be deleted or not.
     */
    public function delete($recursive = true, $removeEmbeddedFiles = true)
    {
        $subFolders = $this->getSubfolders();
        if (false == $recursive && count($subFolders) > 0) {
            throw new InvalidArgumentException('This folder has subfolders.');
        }

        foreach ($subFolders as $subFolder) {
            $subFolder->delete($recursive, $removeEmbeddedFiles);
        }

        if ($removeEmbeddedFiles) {
            foreach (array_keys($this->_getFiles()) AS $name) {
                $this->getCollection()->deleteFile($name);
            }
        }

        $next = $this->getDictionary()->getValue('Next');

        $parent = $this->getParent();
        if ($parent) {
            $parentChild = $parent->getDictionary()->getValue('Child');

            if ($parentChild->getObjectIdent() == $this->getIndirectObject()->getObjectIdent()) {
                if ($next) {
                    $parent->getDictionary()->offsetSet('Child', $next);
                } else {
                    $parent->getDictionary()->offsetUnset('Child');
                }

            } else {
                // find previous
                $siblings = $parent->getSubfolders();
                $previousSibling = null;
                foreach ($siblings AS $sibling) {
                    if ($sibling->getIndirectObject()->getObjectIdent() == $this->getIndirectObject()->getObjectIdent()) {
                        break;
                    }
                    $previousSibling = $sibling;
                }

                if ($next) {
                    $previousSibling->getDictionary()->offsetSet('Next', $next);
                } else {
                    $previousSibling->getDictionary()->offsetUnset('Next');
                }
            }
        }

        $this->getDictionary()->offsetUnset('Parent');
    }
}