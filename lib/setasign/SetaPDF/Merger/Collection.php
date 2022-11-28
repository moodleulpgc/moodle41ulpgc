<?php
/**
 * This file is part of the SetaPDF-Merger Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Collection.php 1444 2020-03-17 20:17:45Z jan.slabon $
 */

/**
 * Class for creating and managing PDF Collections (aka Portfolios, or Packages).
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Merger_Collection
{
    /**
     * The collection view shall be presented in details mode.
     *
     * @var string
     */
    const VIEW_DETAILS = 'D';

    /**
     * The collection view shall be presented in tile mode.
     *
     * @var string
     */
    const VIEW_TILE = 'T';

    /**
     * The collection view shall be initially hidden.
     *
     * @var string
     */
    const VIEW_HIDDEN = 'H';

    /**
     * The collection view shall be presented by the navigator specified by the Navigator entry.
     *
     * @var string
     */
    const VIEW_NAVIGATOR = 'C';

    /**
     * Indicates that the window is split horizontally.
     *
     * @var string
     */
    const SPLIT_HORIZONTALLY = 'H';

    /**
     * Indicates that the window is split vertically.
     *
     * @var string
     */
    const SPLIT_VERTICALLY = 'V';

    /**
     * Indicates that the window is not split. The entire window region shall be dedicated to the file navigation view.
     *
     * @var string
     */
    const SPLIT_NO = 'N';

    /**
     * Sort in ascending order.
     *
     * @var boolean
     */
    const SORT_ASC = true;

    /**
     * Sort in descending order.
     *
     * @var string
     */
    const SORT_DESC = false;

    /**
     * The document instance of the cover sheet.
     *
     * @var SetaPDF_Core_Document
     */
    protected $_document;

    /**
     * The schema instance.
     *
     * @var SetaPDF_Merger_Collection_Schema
     */
    protected $_schema;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document $document
     */
    public function __construct(SetaPDF_Core_Document $document)
    {
        $this->_document = $document;

        // Set PDF version and extension level
        $document->setPdfVersion('1.7');
        $document->getCatalog()->getExtensions()->setExtension('ADBE', '1.7', 3);
    }

    /**
     * Release cylced referenced.
     */
    public function cleanUp()
    {
        $this->_document = null;

        if (null !== $this->_schema) {
            $this->_schema->cleanUp();
            $this->_schema = null;
        }
    }

    /**
     * Get the document instance.
     *
     * @return SetaPDF_Core_Document
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * Checks whether the document instance has the Collection dictionary defined or not.
     *
     * @return boolean
     */
    public function isCollection()
    {
        $root = $this->getDocument()->getCatalog()->getDictionary();
        if ($root === null) {
            return false;
        }

        return $root->offsetExists('Collection');
    }

    /**
     * Get the collection dictionary.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary($create = false)
    {
        $root = $this->getDocument()->getCatalog()->getDictionary($create);
        if ($root === null) {
            return null;
        }

        if (!$root->offsetExists('Collection')) {
            if (false === $create) {
                return null;
            }

            $root->offsetSet(
                'Collection',
                $this->getDocument()->createNewObject(new SetaPDF_Core_Type_Dictionary())
            );
        }

        /**
         * @var SetaPDF_Core_Type_Dictionary $dict
         */
        $dict = $root->getValue('Collection')->ensure(true);

        return $dict;
    }

    /**
     * Get the schema instance.
     *
     * @return SetaPDF_Merger_Collection_Schema
     */
    public function getSchema()
    {
        if (null === $this->_schema) {
            $this->_schema = new SetaPDF_Merger_Collection_Schema($this);
        }

        return $this->_schema;
    }

    /**
     * Set the initial view.
     *
     * @param string $view A view constant.
     * @see SetaPDF_Merger_Collection::VIEW_DETAILS
     * @see SetaPDF_Merger_Collection::VIEW_TILE
     * @see SetaPDF_Merger_Collection::VIEW_HIDDEN
     */
    public function setView($view)
    {
        if (null === $view) {
            $dict = $this->getDictionary();
            if ($dict !== null) {
                $dict->offsetUnset('View');
            }
            return;
        }

        if (!in_array($view, [self::VIEW_DETAILS, self::VIEW_TILE, self::VIEW_HIDDEN, self::VIEW_NAVIGATOR])) {
            throw new InvalidArgumentException('Invalid initial view value: "' . $view . '"');
        }

        $dict = $this->getDictionary(true);

        $dict->offsetSet('View', new SetaPDF_Core_Type_Name($view));
    }

    /**
     * Get the initial view.
     *
     * @return string
     */
    public function getView()
    {
        $dict = $this->getDictionary();
        if (null === $dict || !$dict->offsetExists('View')) {
            return self::VIEW_DETAILS;
        }

        return $dict->getValue('View')->ensure()->getValue();
    }

    /**
     * Set the data that specifies the order in which the collection shall be sorted in the user interface.
     *
     * @param array $sort The key is the field name, while the value defines the direction. Valid key names are field
     *                    names defined in the schema or {@link SetaPDF_Merger_Collection_Schema::DATA_*} constants.
     * @see SetaPDF_Merger_Collection::SORT_ASC
     * @see SetaPDF_Merger_Collection::SORT_DESC
     */
    public function setSort(array $sort)
    {
        $s = new SetaPDF_Core_Type_Array();
        $a = new SetaPDF_Core_Type_Array();

        $schema = $this->getSchema();

        foreach ($sort AS $field => $direction) {
            // Acrobat uses these field name as the default column name (Desc -> Description, F -> FileName)
            if ($field === SetaPDF_Merger_Collection_Schema::DATA_DESCRIPTION && !$schema->hasField($field)) {
                $field = 'Description';
            } elseif ($field === SetaPDF_Merger_Collection_Schema::DATA_FILE_NAME && !$schema->hasField($field)) {
                $field = 'FileName';
            }

            if (!in_array($field, [
                    'FileName',
                    'Description',
                    SetaPDF_Merger_Collection_Schema::DATA_SIZE,
                    SetaPDF_Merger_Collection_Schema::DATA_COMPRESSED_SIZE,
                    SetaPDF_Merger_Collection_Schema::DATA_CREATION_DATE,
                    SetaPDF_Merger_Collection_Schema::DATA_MODIFICATION_DATE
                ]) && !$schema->hasField($field)) {
                throw new InvalidArgumentException('The field "' . $field . '" is not specified in the Schema.');
            }

            $s[] = new SetaPDF_Core_Type_Name($field);
            $a[] = new SetaPDF_Core_Type_Boolean($direction);
        }

        $dict = $this->getDictionary(true);
        if (count($s) === 0) {
            $dict->offsetUnset('Sort');
        } else {
            $dict->offsetSet('Sort', new SetaPDF_Core_Type_Dictionary([
                'S' => $s,
                'A' => $a
            ]));
        }
    }

    /**
     * Get the data which specifies the order in which in the collection shall be sorted in the user interface.
     *
     * @return array The key is the field name, while the value describing the direction.
     * @see SetaPDF_Merger_Collection::SORT_ASC
     * @see SetaPDF_Merger_Collection::SORT_DESC
     */
    public function getSort()
    {
        $dict = $this->getDictionary();
        if (null === $dict || !$dict->offsetExists('Sort')) {
            return [];
        }

        /**
         * @var SetaPDF_Core_Type_Dictionary $sort
         */
        $sort = $dict->getValue('Sort')->ensure();

        $fields = [];
        $directions = [];

        $s = $sort->getValue('S');
        if (null === $s) {
            return [];
        }

        $s = $s->ensure();
        if ($s instanceof SetaPDF_Core_Type_Name) {
            $fields[] = $s->getValue();
        } elseif ($s instanceof SetaPDF_Core_Type_Array) {
            foreach ($s AS $name) {
                $fields[] = $name->ensure()->getValue();
            }
        }

        $a = $sort->getValue('A');
        if (null !== $a) {
            $a = $a->ensure();
            if ($a instanceof SetaPDF_Core_Type_Boolean) {
                $directions[] = $a->getValue();
            } elseif ($a instanceof SetaPDF_Core_Type_Array) {
                foreach ($a AS $direction) {
                    $directions[] = $direction->ensure()->getValue();
                }
            }
        }

        $result = [];
        foreach ($fields AS $key => $field) {
            $result[$field] = isset($directions[$key]) ? $directions[$key] : self::SORT_ASC;
        }

        return $result;
    }

    /**
     * Get and/or creates the split dictionary.
     *
     * @param bool $create
     * @return SetaPDF_Core_Type_Dictionary|null
     */
    private function _getSplitDictionary($create = false)
    {
        $dict = $this->getDictionary($create);
        if (null === $dict) {
            return null;
        }

        if (!$dict->offsetExists('Split')) {
            if (false === $create) {
                return null;
            }

            $dict->offsetSet('Split', new SetaPDF_Core_Type_Dictionary());
        }

        /**
         * @var SetaPDF_Core_Type_Dictionary $splitDict
         */
        $splitDict = $dict->getValue('Split')->ensure();

        return $splitDict;
    }

    /**
     * Set the orientation of the splitter bar.
     *
     * @param string $direction
     */
    public function setSplitterDirection($direction)
    {
        if (null === $direction) {
            $dict = $this->_getSplitDictionary();
            if ($dict !== null) {
                $dict->offsetUnset('Direction');
            }
            return;
        }

        if (!in_array($direction, [self::SPLIT_HORIZONTALLY, self::SPLIT_VERTICALLY, self::SPLIT_NO])) {
            throw new InvalidArgumentException('Invalid splitter bar direction value: "' . $direction . '"');
        }

        $dict = $this->_getSplitDictionary(true);
        $dict->offsetSet('Direction', new SetaPDF_Core_Type_Name($direction));
    }

    /**
     * Get the orientation of the splitter bar.
     *
     * @return string|null
     */
    public function getSplitterDirection()
    {
        $dict = $this->_getSplitDictionary();
        if (null === $dict || !$dict->offsetExists('Direction')) {
            return null;
        }

        return $dict->getValue('Direction')->ensure()->getValue();
    }

    /**
     * Set the initial position of the splitter bar.
     *
     * @param number $position
     */
    public function setSplitterPosition($position)
    {
        if (null === $position) {
            $dict = $this->_getSplitDictionary();
            if ($dict !== null) {
                $dict->offsetUnset('Position');
            }
            return;
        }

        $dict = $this->_getSplitDictionary(true);
        $dict->offsetSet('Position', new SetaPDF_Core_Type_Numeric($position));
    }

    /**
     * Get the initial position of the splitter bar.
     *
     * @return number|null
     */
    public function getSplitterPosition()
    {
        $dict = $this->_getSplitDictionary();
        if (null === $dict || !$dict->offsetExists('Position')) {
            return null;
        }

        return $dict->getValue('Position')->ensure()->getValue();
    }

    /**
     * Add a file to the collection.
     *
     * @param SetaPDF_Core_Reader_ReaderInterface|string $pathOrReader A reader instance or a path to a file.
     * @param string $filename The filename in UTF-8 encoding.
     * @param null|string $description The description of the file in UTF-8 encoding.
     * @param array $fileStreamParams See {@link SetaPDF_Core_EmbeddedFileStream::setParams()} method.
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
        // force creation of Collection dictionary
        $this->getDictionary(true);

        $fileSpecification = SetaPDF_Core_FileSpecification::createEmbedded(
            $this->_document,
            $pathOrReader,
            $filename,
            $fileStreamParams,
            $mimeType
        );

        if ($description !== null) {
            $fileSpecification->setDescription($description);
        }

        if ($collectionItem !== null) {
            if (!$collectionItem instanceof SetaPDF_Merger_Collection_Item) {
                $_collectionItem = $collectionItem;
                $collectionItem = new SetaPDF_Merger_Collection_Item();
                $collectionItem->setData($_collectionItem, $this->getSchema());
            }

            $fileSpecification->setCollectionItem($collectionItem->getDictionary());
        }

        $embeddedFiles = $this->_document->getCatalog()->getNames()->getEmbeddedFiles();
        $name = "\xFE\xFF" . SetaPDF_Core_Encoding::convert($filename, 'UTF-8', 'UTF-16BE');
        $embeddedFiles->add(
            $name,
            $fileSpecification
        );

        return $name;
    }

    /**
     * Removes a file from the collection.
     *
     * If the file doesn't exists false will be returned.
     *
     * @param string $name The name with which the file is registered in the documents embedded files name tree.
     * @return bool
     */
    public function deleteFile($name)
    {
        return $this->getDocument()
            ->getCatalog()
            ->getNames()
            ->getEmbeddedFiles()
            ->remove($name);
    }

    /**
     * Set the name of the document, that should be initially presented.
     *
     * If you want to open a document, that is located in a subfolder, you will need to pass the id of the subfolder
     * as a prefix to the name:
     *
     * <code>
     * $collection->setInitialDocument('<' . $folder->getId() . '>' . $name);
     * </code>
     *
     * @param string $name
     */
    public function setInitialDocument($name)
    {
        if (null === $name) {
            $dict = $this->getDictionary();
            if ($dict !== null) {
                $dict->offsetUnset('D');
            }
            return;
        }

        // check if the name exists
        $embeddedFiles = $this->_document->getCatalog()->getNames()->getEmbeddedFiles();
        $fileSpecification = $embeddedFiles->get($name);
        if (false === $fileSpecification) {
            throw new InvalidArgumentException('No embedded file "' . $name . '" found.');
        }

        $dict = $this->getDictionary(true);

        $dict->offsetSet('D', new SetaPDF_Core_Type_String($name));
    }

    /**
     * Get the name of the document, that should be initially presented.
     *
     * @return string|null Null if it is not defined.
     */
    public function getInitialDocument()
    {
        $dict = $this->getDictionary();
        if (null === $dict || !$dict->offsetExists('D')) {
            return null;
        }

        return $dict->getValue('D')->ensure()->getValue();
    }

    /**
     * Checks whether this collection has folders or not.
     *
     * @return bool
     */
    public function hasFolders()
    {
        if (!$this->isCollection()) {
            return false;
        }

        $rootFolder = $this->getRootFolder();
        if (null === $rootFolder) {
            return false;
        }

        return $rootFolder->hasSubfolders();
    }

    /**
     * Get and/or created the root folder instance.
     *
     * To ensure that a root folder is created pass true as the $create parameter.
     *
     * @param boolean $create Defines whether to create the folder if it does not exists or not.
     * @return SetaPDF_Merger_Collection_Folder|null
     */
    public function getRootFolder($create = false)
    {
        $dict = $this->getDictionary($create);
        if (null === $dict) {
            return null;
        }

        if (!$dict->offsetExists('Folders')) {
            if (false === $create) {
                return null;
            }

            $date = new SetaPDF_Core_DataStructure_Date();
            $folders = new SetaPDF_Core_Type_Dictionary([
                'Type' => new SetaPDF_Core_Type_Name('Folder'),
                'ID' => new SetaPDF_Core_Type_Numeric(0),
                'Name' => new SetaPDF_Core_Type_String(''),
                'Free' => new SetaPDF_Core_Type_Array([
                    new SetaPDF_Core_Type_Numeric(1),
                    new SetaPDF_Core_Type_Numeric(2147483647),
                ]),
                'ModDate' => $date->getValue()
            ]);

            $dict->offsetSet('Folders', $this->getDocument()->createNewObject($folders));
        }

        return new SetaPDF_Merger_Collection_Folder($this, $dict->getValue('Folders'));
    }

    /**
     * Add a folder to the collection.
     *
     * @param string $name The folder name.
     * @param null|string $description The description of the folder.
     * @param DateTime|null $creationDate If null "now" will be used.
     * @param DateTime|null $modificationDate If null "now" will be used.
     * @param null|array|SetaPDF_Merger_Collection_Item $collectionItem The data described by the collection schema.
     * @return SetaPDF_Merger_Collection_Folder
     **/
    public function addFolder(
        $name,
        $description = null,
        DateTime $creationDate = null,
        DateTime $modificationDate = null,
        $collectionItem = null
    )
    {
        return $this->getRootFolder(true)->addFolder(
            $name, $description, $creationDate, $modificationDate, $collectionItem
        );
    }

    /**
     * Get all embedded files from this collection/document.
     *
     * @return SetaPDF_Core_FileSpecification[]
     */
    public function getFiles()
    {
        $embeddedFiles = $this->_document->getCatalog()->getNames()->getEmbeddedFiles();
        return $embeddedFiles->getAll();
    }

    /**
     * Get a file by its name in the embedded files name tree.
     *
     * @param string $name
     * @return false|SetaPDF_Core_FileSpecification
     */
    public function getFile($name)
    {
        $embeddedFiles = $this->_document->getCatalog()->getNames()->getEmbeddedFiles();
        return $embeddedFiles->get($name);
    }
}