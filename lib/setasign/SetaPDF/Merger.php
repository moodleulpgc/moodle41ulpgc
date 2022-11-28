<?php
/**
 * This file is part of the SetaPDF-Merger Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Merger.php 1595 2020-12-03 11:17:06Z jan.slabon $
 */

/**
 * The main class of the SetaPDF-Merger Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Merger
{
    /**
     * Version
     *
     * @var string
     */
    const VERSION = SetaPDF_Core::VERSION;

    /**
     * Constant defines that existing outline items should be copied as child items to the newly created outline item
     *
     * @var string
     */
    const COPY_OUTLINES_AS_CHILDS = 'copyOutlinesAsChilds';

    /**
     * Constant defines that existing outlines items should be copied to the outlines root
     *
     * @var string
     */
    const COPY_OUTLINES_TO_ROOT = 'copyOutlinesToRoot';

    /**
     * Key for the title property of an outline item
     *
     * @var string
     */
    const OUTLINES_TITLE = SetaPDF_Core_Document_OutlinesItem::TITLE;

    /**
     * Key for the color property of an outline item
     *
     * @var string
     */
    const OUTLINES_COLOR = SetaPDF_Core_Document_OutlinesItem::COLOR;

    /**
     * Key for the bold style property of an outline item
     *
     * @var string
     */
    const OUTLINES_BOLD = SetaPDF_Core_Document_OutlinesItem::BOLD;

    /**
     * Key for the italic style property of an outline item
     *
     * @var string
     */
    const OUTLINES_ITALIC = SetaPDF_Core_Document_OutlinesItem::ITALIC;

    /**
     * Key for the parent property of an outline item
     *
     * @var string
     */
    const OUTLINES_PARENT = 'parent';

    /**
     * Key for the fit-mode property of an outline item destination
     */
    const OUTLINES_FIT_MODE = self::DESTINATION_FIT_MODE;

    /**
     * Key for the copy behavior of an outline item
     *
     * @var string
     */
    const OUTLINES_COPY = 'copy';

    /**
     * Keyword for all pages
     *
     * @var string
     */
    const PAGES_ALL = 'all';

    /**
     * Keyword for the first page
     *
     * @var string
     */
    const PAGES_FIRST = 'first';

    /**
     * Keyword for the last page
     *
     * @var string
     */
    const PAGES_LAST = 'last';

    /**
     * Keyword for the destination name.
     *
     * @var string
     */
    const DESTINATION_NAME = 'name';

    /**
     * Keyword for the destination fit mode.
     *
     * @var string
     */
    const DESTINATION_FIT_MODE = 'fitMode';

    /**
     * The initial document
     *
     * The initial document is the document to which the
     * new documents/pages will be added.
     *
     * It will be created automatically if none was provided
     * in the constructor.
     *
     * @var SetaPDF_Core_Document
     */
    protected $_initialDocument;

    /**
     * The currently processed document instance.
     *
     * @var SetaPDF_Core_Document
     */
    protected $_currentDocument;

    /**
     * The documents/pages which should be added
     *
     * @var array
     */
    protected $_documents = [];

    /**
     * Cache for document objects by filename
     *
     * @var array
     */
    protected $_documentCache = [];

    /**
     * Should names be copied/handled
     *
     * @var boolean
     */
    protected $_handleNames = true;

    /**
     * Callback method used for renaming names
     *
     * @see SetaPDF_Core_DataStructure_NameTree::adjustNameCallback()
     * @var callback
     */
    protected $_adjustNameCallback;

    /**
     * Renamed names
     *
     * @internal
     * @var array
     */
    protected $_renamed = [];

    /**
     * Flag saying if same named form fields should be renamed.
     *
     * @var bool
     */
    protected $_renameSameNamedFormFields = true;

    /**
     * A callback which is called just before a page is added to the new document
     *
     * @var null|callback
     * @see SetaPDF_Merger::_beforePageAdded()
     */
    public $beforePageAddedCallback;

    /**
     * A max file handler.
     *
     * @var SetaPDF_Core_Reader_MaxFileHandler
     */
    protected $_maxFileHanlder;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document $initialDocument The initial document to start with
     */
    public function __construct(SetaPDF_Core_Document $initialDocument = null)
    {
        $this->_initialDocument = $initialDocument;
        $this->_adjustNameCallback = ['SetaPDF_Core_DataStructure_NameTree', 'adjustNameCallback'];
    }

    /**
     * Returns the initial document.
     *
     * @see SetaPDF_Merger::$_initialDocument
     * @return SetaPDF_Core_Document
     */
    public function getInitialDocument()
    {
        if ($this->_initialDocument === null) {
            $this->_initialDocument = new SetaPDF_Core_Document();
        }

        return $this->_initialDocument;
    }

    /**
     * Alias for getInitialDocument.
     *
     * @return SetaPDF_Core_Document
     */
    public function getDocument()
    {
        return $this->getInitialDocument();
    }

    /**
     * Set the writer for the initial document.
     *
     * @param SetaPDF_Core_Writer_WriterInterface $writer The writer instance
     */
    public function setWriter(SetaPDF_Core_Writer_WriterInterface $writer)
    {
        $this->getInitialDocument()->setWriter($writer);
    }

    /**
     * Set the maximum file handler.
     *
     * @param SetaPDF_Core_Reader_MaxFileHandler|null $handler
     */
    public function setMaxFileHandler(SetaPDF_Core_Reader_MaxFileHandler $handler = null)
    {
        $this->_maxFileHanlder = $handler;
    }

    /**
     * Get the maximum file handler.
     *
     * @return SetaPDF_Core_Reader_MaxFileHandler|null
     */
    public function getMaxFileHandler()
    {
        return $this->_maxFileHanlder;
    }

    /**
     * Helper method to get the page count of a document or file.
     *
     * @param string|SetaPDF_Core_Document $filename The filename or the document instance
     * @param boolean $cacheDocumentInstance Cache the document instance or not
     * @return integer
     */
    public function getPageCount($filename, $cacheDocumentInstance = true)
    {
        $document = $this->_getDocument($filename, $cacheDocumentInstance);
        $pages = $document->getCatalog()->getPages();

        return $pages->count();
    }

    /**
     * Add a document by filename.
     *
     * The document could include dynamic content like form fields, links or any other page annotation.
     *
     * Form fields are handled especially:
     * If a document was added with form fields which names were already used by a previously added
     * document the field name will be suffixed with a slash and a number.
     *
     * This behavior may lead to corrupted java scripts which may calculate field sums by field names!
     *
     * @param string|array $filenameOrConfig The filename or config array. If an array is passed the keys has to be
     *                                       named as the method parameters. All other parameters are optional then.
     * @param mixed $pages                   The pages to add from the file. See
     *                                       {@link SetaPDF_Merger::_checkPageNumber() _checkPageNumber()} for a full
     *                                       description.
     * @param string|array $nameConfig The configuration for a named destination for this file.
     * @param null|string|array $outlinesConfig The outlines config,
     * @param boolean $copyLayers Whether to copy layer information of the document.
     *
     * @throws InvalidArgumentException
     * @return int|null
     */
    public function addFile($filenameOrConfig, $pages = null, $nameConfig = null, $outlinesConfig = null, $copyLayers = true)
    {
        if (is_array($filenameOrConfig)) {
            if (!isset($filenameOrConfig['filename'])) {
                throw new InvalidArgumentException('Missing filename-key in config array.');
            }

            extract($filenameOrConfig, EXTR_OVERWRITE);

            // keep BC
            if (isset($name)) {
                $nameConfig = $name;
                unset($name);
            }

            /**
             * @var string $filename
             */
        } else {
            $filename = $filenameOrConfig;
        }

        $this->_documents[] = [
            $filename,
            $pages,
            $this->_ensureNameConfig($nameConfig),
            $this->_ensureOutlinesConfig($outlinesConfig),
            $copyLayers
        ];

        return $this->_checkOutlinesConfig($outlinesConfig);
    }

    /**
     * Add a document.
     *
     * Same as {@link SetaPDF_Merger::addFile() addFile()} but the document has to be passed as
     * \SetaPDF_Core_Document instance.
     *
     * @see addFile()
     *
     * @param SetaPDF_Core_Document|array $documentOrConfig The document or config array. If an array is passed the keys
     *                                                      has to be named as the method parameters. All other
     *                                                      parameters are optional then.
     * @param mixed $pages                                  The pages to add from the file. See
     *                                                      {@link SetaPDF_Merger::_checkPageNumber() _checkPageNumber()}
     *                                                      for a full description.
     * @param string|array $nameConfig The configuration for a named destination for this file.
     * @param null|string|array $outlinesConfig The outlines config
     * @param boolean $copyLayers Whether to copy layer information of the document
     *
     * @throws InvalidArgumentException
     * @return int|null
     */
    public function addDocument(
        $documentOrConfig, $pages = null, $nameConfig = null, $outlinesConfig = null, $copyLayers = true
    )
    {
        if (is_array($documentOrConfig)) {
            if (!isset($documentOrConfig['document'])) {
                throw new InvalidArgumentException('Missing document-key in config array.');
            }

            extract($documentOrConfig, EXTR_OVERWRITE);

            // keep BC
            if (isset($name)) {
                $nameConfig = $name;
                unset($name);
            }

            /**
             * @var $document
             */
        } else {
            $document = $documentOrConfig;
        }

        if (!($document instanceof SetaPDF_Core_Document)) {
            throw new InvalidArgumentException('Invalid $document parameter. Has to be instance of SetaPDF_Core_Document');
        }

        $this->_documents[] = [
            $document,
            $pages,
            $this->_ensureNameConfig($nameConfig),
            $this->_ensureOutlinesConfig($outlinesConfig),
            $copyLayers
        ];

        return $this->_checkOutlinesConfig($outlinesConfig);
    }

    /**
     * Ensures the name config array.
     *
     * @param array|string|null $nameConfig
     * @return array|null
     */
    protected function _ensureNameConfig($nameConfig)
    {
        if ($nameConfig === null) {
            return null;
        }

        if (!is_array($nameConfig) && $nameConfig !== '') {
            return [
                self::DESTINATION_NAME => (string)$nameConfig,
                self::DESTINATION_FIT_MODE => [SetaPDF_Core_Document_Destination::FIT_MODE_FIT]
            ];
        }

        if (!isset($nameConfig[self::DESTINATION_NAME]) || $nameConfig[self::DESTINATION_NAME] === '') {
            throw new InvalidArgumentException('Missing name configuration for named destination.');
        }

        if (!isset($nameConfig[self::DESTINATION_FIT_MODE])) {
            $nameConfig[self::DESTINATION_FIT_MODE] = [SetaPDF_Core_Document_Destination::FIT_MODE_FIT];
        }

        if (!is_array($nameConfig[self::DESTINATION_FIT_MODE])) {
            throw new InvalidArgumentException('Fit mode needs to be an array.');
        }

        return $nameConfig;
    }

    /**
     * Ensures the outlines configuration array.
     *
     * @param array|string|null $outlinesConfig
     * @return array|null
     */
    protected function _ensureOutlinesConfig($outlinesConfig)
    {
        if ($outlinesConfig === null) {
            return null;
        }

        if (!is_array($outlinesConfig)) {
            return [
                self::OUTLINES_TITLE => $outlinesConfig,
                self::OUTLINES_FIT_MODE => [SetaPDF_Core_Document_Destination::FIT_MODE_FIT]
            ];
        }

        if (isset($outlinesConfig[self::OUTLINES_TITLE]) && !isset($outlinesConfig[self::OUTLINES_FIT_MODE])) {
            $outlinesConfig[self::OUTLINES_FIT_MODE] = [SetaPDF_Core_Document_Destination::FIT_MODE_FIT];
        }

        if (isset($outlinesConfig[self::OUTLINES_TITLE]) && !is_array($outlinesConfig[self::OUTLINES_FIT_MODE])) {
            throw new InvalidArgumentException('Fit mode needs to be an array.');
        }

        return $outlinesConfig;
    }

    /**
     * Checks the $outlinesConfig parameter if it is possible to add childs to the resulting outline item.
     *
     * @param string|array $outlinesConfig The outlines config
     * @return int|null
     */
    protected function _checkOutlinesConfig($outlinesConfig)
    {
        // only return an id if outline is added and is usable as a parent item
        return
            $outlinesConfig !== null && (
                is_string($outlinesConfig) || (is_array($outlinesConfig) && isset($outlinesConfig[self::OUTLINES_TITLE]))
            )
                ? count($this->_documents) - 1
                : null;
    }

    /**
     * Will be called just before a page is added to the pages tree.
     *
     * An own callback can be defined through the $beforePageAddedCallback property.
     * Or this method can be overwritten to implement own logic in the scope of the class.
     *
     * @param SetaPDF_Core_Document_Page $page The page that will be added
     * @param int $pageNumber The number of the page
     */
    protected function _beforePageAdded(SetaPDF_Core_Document_Page $page, $pageNumber)
    {
        if ($this->beforePageAddedCallback !== null && is_callable($this->beforePageAddedCallback)) {
            call_user_func($this->beforePageAddedCallback, $page, $pageNumber);
        }
    }

    /**
     * Defines that the document's name dictionaries are merged into the resulting document.
     *
     * This behavior is enabled by default. It sadly needs much memory and script runtime,
     * because name trees could be very huge.
     *
     * @param boolean $handleNames The flag status
     * @param null|callback $adjustNameCallback See {@link SetaPDF_Core_DataStructure_Tree::merge()} for a detailed description of the callback
     */
    public function setHandleNames($handleNames = true, $adjustNameCallback = null)
    {
        $this->_handleNames = (boolean)$handleNames;
        if (null !== $adjustNameCallback) {
            $this->_adjustNameCallback = $adjustNameCallback;
        }
    }

    /**
     * Set the flag defining if same named form fields should be renamed (default behavior).
     *
     * If this flag is set to false the fields will be merged so that all same named fields
     * will have the same value. Notice that this could occur in an incorrect appearance if the
     * initial values are different.
     *
     * @param bool $renameSameNamedFormFields The flag status
     */
    public function setRenameSameNamedFormFields($renameSameNamedFormFields = true)
    {
        $this->_renameSameNamedFormFields = $renameSameNamedFormFields;
    }

    /**
     * Merges the documents/pages in memory.
     *
     * This method merges the documents and/or pages to the initial
     * document object without calling the save()-method.
     * The document is hold in memory until it is "manually" saved through the
     * initial document instance.
     *
     * @return SetaPDF_Core_Document
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Merger_Exception
     */
    public function merge()
    {
        $resDocument = $this->getInitialDocument();
        $resPages = $resDocument->getCatalog()->getPages();

        $touchedPdfs = [];
        $addedPages = [];
        $hasFormFields = [];
        $importFormFieldsFrom = [];

        $namedDestinations = [];
        $outlineTargets = [];

        foreach ($this->_documents AS $currentDocumentId => $documentData) {
            $this->_currentDocument = null;
            $this->_currentDocument = $this->_getDocument($documentData[0]);

            if ($this->_currentDocument->hasSecHandler()) {
                $secHandler = $this->_currentDocument->getSecHandlerIn();
                if (!$secHandler->getPermission(SetaPDF_Core_SecHandler::PERM_ASSEMBLE)) {
                    throw new SetaPDF_Core_SecHandler_Exception(
                        sprintf('Extraction of pages is not allowed with this credentials (%s).', $secHandler->getAuthMode()),
                        SetaPDF_Core_SecHandler_Exception::NOT_ALLOWED
                    );
                }
            }

            $ident = $this->_currentDocument->getInstanceIdent();
            if (!isset($addedPages[$ident])) {
                $addedPages[$ident] = [];
            }

            $pages = $this->_currentDocument->getCatalog()->getPages();
            if (($documentData[1] === null || $documentData[1] === self::PAGES_ALL) && !isset($touchedPdfs[$ident])) {
                try {
                    $pages->ensureAllPageObjects();
                } catch (BadMethodCallException $e) {
                }
            }

            /**
             * @var SetaPDF_Core_Document_Page[] $pagesToAdd
             */
            $pagesToAdd = [];

            $pageCount = $pages->count();

            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                if ($this->_checkPageNumber($pageNumber, $documentData[1])) {

                    $page = $pages->extract($pageNumber, $resDocument);
                    $page->flattenInheritedAttributes();

                    $this->_beforePageAdded($page, count($pagesToAdd) + $resPages->count() + 1);

                    /* Info: It is NOT faster to resolve the page object directly
                     * without the wrapper class SetaPDF_Core_Document_Page
                     */
                    $pagesToAdd[] = $page;

                    // rem page object for named destinations
                    if (isset($documentData[2]) && !isset($namedDestinations[$documentData[2][self::DESTINATION_NAME]])) {
                        $namedDestinations[$documentData[2][self::DESTINATION_NAME]] = [
                            'pageNo' => ($resPages->count() + count($pagesToAdd)),
                            'fitMode' => $documentData[2][self::DESTINATION_FIT_MODE]
                        ];
                    }

                    $resDocument->unBlockReferencedObject($pages->getPagesIndirectObject($pageNumber));
                    $addedPages[$ident][$pageNumber] = true;

                    // Handle outline
                    if (isset($documentData[3]) && !isset($outlineTargets[$currentDocumentId]) &&
                        (is_string($documentData[3]) || (is_array($documentData[3]) && isset($documentData[3][self::OUTLINES_TITLE])))
                    ) {
                        $outlineTargets[$currentDocumentId] = ($resPages->count() + count($pagesToAdd));
                    }

                } elseif (!isset($touchedPdfs[$ident]) && $this->_currentDocument->getInstanceIdent() !== $resDocument->getInstanceIdent()) {
                    // block resolving of not imported pages through references
                    $resDocument->blockReferencedObject($pages->getPagesIndirectObject($pageNumber));
                }
            }

            if (count($pagesToAdd) > 0) {
                $resPages->append($pagesToAdd);
                $touchedPdfs[$ident] = true;

                $resDocument->setMinPdfVersion($this->_currentDocument->getPdfVersion());

                // already checked that form fields should be copied
                if (isset($importFormFieldsFrom[$ident])) {
                    continue;
                }

                // check if the document has form fields attached
                if (!isset($hasFormFields[$ident])) {
                    $fields = $this->_currentDocument->getCatalog()->getAcroForm()->getFieldsArray();
                    if ($fields instanceof SetaPDF_Core_Type_Array && $fields->count() > 0) {
                        $hasFormFields[$ident] = true;
                    } else {
                        $hasFormFields[$ident] = false;
                    }
                }

                // if so, check if the used pages have widget annotations
                if ($hasFormFields[$ident]) {
                    foreach ($pagesToAdd AS $page) {
                        $annotations = $page->getAnnotations();
                        $widgetAnnotations = $annotations->getAll(SetaPDF_Core_Document_Page_Annotation::TYPE_WIDGET);
                        if (count($widgetAnnotations) > 0) {
                            $importFormFieldsFrom[$ident] = true;
                            break;
                        }
                    }
                }
            }
        }

        $this->_currentDocument = null;

        if (0 === $resPages->count()) {
            throw new SetaPDF_Merger_Exception(
                'Resulting document has zero pages.'
            );
        }

        $this->_handleNames($touchedPdfs, $namedDestinations);
        $this->_handleAcroForms($importFormFieldsFrom);
        $this->_handleOutlines($touchedPdfs, $outlineTargets);
        $this->_handleOptionalContent($touchedPdfs);

        // TODO: cleanUp documents ?!

        $info = $resDocument->getInfo();
        $info->setSyncMetadata(true);
        $date = new SetaPDF_Core_DataStructure_Date();
        $info->setModDate($date);
        if ($info->getCreationDate() === null) {
            $info->setCreationDate($date);
        }
        $info->setProducer(
            'SetaPDF-Merger Component v' . self::VERSION .
            ' ©Setasign 2005-' . date('Y') . ' (www.setasign.com)'
        );
        $info->syncMetadata();

        return $resDocument;
    }

    /**
     * Handle creation and import of outlines.
     *
     * @param array $touchedPdfs
     * @param array $outlineTargets
     */
    protected function _handleOutlines($touchedPdfs, $outlineTargets)
    {
        $resDocument = $this->getInitialDocument();
        $resPages = $resDocument->getCatalog()->getPages();
        $outlines = $resDocument->getCatalog()->getOutlines();
        $items = [];

        foreach ($this->_documents AS $currentDocumentId => $documentData) {
            if (!isset($documentData[3])) {
                continue;
            }

            $config = $documentData[3];

            // import outlines to root outlines entry
            if (isset($config[self::OUTLINES_COPY]) && $config[self::OUTLINES_COPY] == self::COPY_OUTLINES_TO_ROOT) {
                $currentDocument = $this->_getDocument($documentData[0]);
                $outlines->appendChildCopy($currentDocument->getCatalog()->getOutlines(), $resDocument);
                continue;
            }

            // create outline item
            if (isset($config[self::OUTLINES_PARENT]) &&
                $config[self::OUTLINES_PARENT] instanceof SetaPDF_Core_Document_OutlinesItem
            ) {
                $target = $config[self::OUTLINES_PARENT];

            } else {
                $target = isset($config[self::OUTLINES_PARENT]) && isset($items[$config[self::OUTLINES_PARENT]])
                    ? $items[$config[self::OUTLINES_PARENT]]
                    : $outlines;
            }

            if (isset($config[self::OUTLINES_TITLE])) {
                $items[$currentDocumentId] = SetaPDF_Core_Document_OutlinesItem::create($resDocument, $config);

                $parameter = [$resPages->getPagesIndirectObject($outlineTargets[$currentDocumentId])];
                $parameter = array_merge($parameter, $config[self::OUTLINES_FIT_MODE]);

                $destArray = call_user_func_array(
                    ['SetaPDF_Core_Document_Destination', 'createDestinationArray'],
                    $parameter
                );

                $items[$currentDocumentId]->setDestination($destArray);

                $target->appendChild($items[$currentDocumentId]);
            }

            // import outlines as childs to the newly created outline
            if (isset($config[self::OUTLINES_COPY]) && $config[self::OUTLINES_COPY] == self::COPY_OUTLINES_AS_CHILDS) {
                $currentDocument = $this->_getDocument($documentData[0]);
                if (isset($config[self::OUTLINES_TITLE])) {
                    $items[$currentDocumentId]->appendChildCopy($currentDocument->getCatalog()->getOutlines(), $resDocument);
                } else {
                    $target->appendChildCopy($currentDocument->getCatalog()->getOutlines(), $resDocument);
                }
            }
        }
    }

    /**
     * Handle AcroForm data.
     *
     * @param array $touchedPdfs
     */
    protected function _handleAcroForms($touchedPdfs)
    {
        if (count($touchedPdfs) === 0) {
            return;
        }

        if ($this->_renameSameNamedFormFields) {
            $this->_handleAcroFormsByRenamingSameNamedFields($touchedPdfs);
        } else {
            $this->_handleAcroFormsByMergingSameNamedFields($touchedPdfs);
        }
    }

    /**
     * Handles AcroForm data by merging same named form fields.
     *
     * @param array $touchedPdfs
     */
    protected function _handleAcroFormsByMergingSameNamedFields($touchedPdfs)
    {
        /**
         * 1. Resolve all field names of the initial document
         * 2. Walk through all other documents
         * 2a. Copy fields if the names do not exists
         * 2b. If a fieldname exists and the field is not from the same document, append it's terminal fields to the
         *     existing field. By adding a kids entry (if not already existing) prior its terminal field
         */
        $resDocument = $this->getInitialDocument();
        $resAcroForm = $resDocument->getCatalog()->getAcroForm();
        $resPages = $resDocument->getCatalog()->getPages();
        $resFieldsArray = $resAcroForm->getFieldsArray();
        $resAcroFormInitiated = false;

        $names = [];
        $parents = [];
        $deleteObjects = [];

        if ($resFieldsArray && $resFieldsArray->count() > 0) {
            $initialFieldsObjects = $resAcroForm->getTerminalFieldsObjects();
            foreach ($initialFieldsObjects AS $terminalObject) {
                /* We need the object holding the "T" entry, because this may be cloned and attached
                 * to the resulting document already.
                 */
                $object = SetaPDF_Core_Type_Dictionary_Helper::resolveObjectByAttribute($terminalObject, 'T');
                $name = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName(
                    $resDocument->ensureObject($object)->ensure()
                );
                $names[$name] = $terminalObject;

                $parentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($terminalObject->ensure(true), 'Parent');
                if ($parentDict instanceof SetaPDF_Core_Type_Dictionary) {
                    $parent = $terminalObject->ensure(true)->getValue('Parent')->getValue();
                    $parentName = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName(
                        $resDocument->ensureObject($parent)->ensure()
                    );
                    $parents[$parentName] = $parent;
                }
            }
        }

        foreach ($this->_documents AS $documentData) {
            $document = $this->_getDocument($documentData[0]);
            $ident = $document->getInstanceIdent();
            if (!isset($touchedPdfs[$ident])) {
                continue;
            }

            $acroForm = $document->getCatalog()->getAcroForm();
            $fieldsArray = $acroForm->getFieldsArray();
            if (false === $fieldsArray || $fieldsArray->count() === 0) {
                continue;
            }

            // Setup the AcroForm entry in the resulting document
            if (false === $resAcroFormInitiated) {
                $resAcroForm->addDefaultEntriesAndValues();
                $resFieldsArray = $resAcroForm->getFieldsArray();
                $resAcroFormInitiated = true;
            }

            // Copy DR field values
            $resAcroFormDict = $resAcroForm->getDictionary();
            $acroFormDict = $acroForm->getDictionary();
            if ($acroFormDict->offsetExists('DR')) {
                $resDr = $resAcroFormDict->getValue('DR');
                foreach ($acroFormDict->getValue('DR') AS $name => $values) {
                    if (!$resDr->offsetExists($name)) {
                        $resDr[$name] = clone $values;
                    }

                    foreach ($values AS $resName => $value) {
                        $resDict = $resDr[$name]->getValue();
                        if (!$resDict->offsetExists($resName)) {
                            $resDict->offsetSet($resName, clone $value);
                        }
                    }
                }
            }

            $fieldsObjects = $acroForm->getTerminalFieldsObjects();

            foreach ($fieldsObjects AS $object) {
                $page = $resPages->getPageByAnnotation($object);
                if ($page === false) {
                    $deleteObjects[] = $object;
                    $document->blockReferencedObject($object);
                    continue;
                }

                $dict = $object->ensure(true);
                $name = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($dict);
                // resolve parent name
                $parent = $parentName = null;
                if ($dict->offsetExists('Parent')) {
                    $parentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Parent');
                    if ($parentDict instanceof SetaPDF_Core_Type_Dictionary) {
                        $parent = $dict->getValue('Parent')->getValue();
                        $parentName = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($parentDict);
                    }
                }

                // If a same named field already exists
                if (isset($names[$name])) {
                    // cloning this value will not work in all cases
                    $existingDict = $names[$name]->ensure(true);

                    if (SetaPDF_Core_Type_Dictionary_Helper::getValue($existingDict, 'Parent') instanceof SetaPDF_Core_Type_Dictionary) {
                        $parentObject = $existingDict->getValue('Parent');
                    } else {
                        $parentObject = null;
                    }

                    if (null === $parentObject || (
                            $existingDict->getValue('T') && $dict->getValue('T') &&
                            SetaPDF_Core_Encoding::convertPdfString($existingDict->getValue('T')->getValue()) ==
                            SetaPDF_Core_Encoding::convertPdfString($dict->getValue('T')->getValue())
                        )) {
                        $names[$name] = $resDocument->createNewObject($names[$name]);

                        // 1. Remove the existing field from the Fields array or the Kids array of the direct parent
                        // 2. Create a new intermediate field, with data from the original field + Kids array
                        // 3. Remove the V, DV and T value from the terminal field and use this field dictionary in the next step
                        // 4. Add the removed field to the new Kids array
                        //
                        /** @var SetaPDF_Core_Type_Array $parentFieldsArray */
                        $parentFieldsArray = $parentObject ? $parentObject->ensure(true)->getValue('Kids')->ensure(true) : $resFieldsArray;
                        $idx = $parentFieldsArray->indexOf($names[$name]);
                        $parentFieldsArray->offsetUnset($idx);

                        $ft = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($existingDict, 'FT');
                        $intermediate = new SetaPDF_Core_Type_Dictionary([
                            clone $existingDict->offsetGet('T'),
                            'V' => $existingDict->offsetExists('V') ? clone $existingDict->getValue('V')->ensure() : new SetaPDF_Core_Type_String(),
                            'FT' => clone $ft,
                            'Kids' => new SetaPDF_Core_Type_Array([
                                new SetaPDF_Core_Type_IndirectReference($names[$name])
                            ])
                        ]);

                        if ($parentObject) {
                            $intermediate['Parent'] = $parentObject;
                        }

                        if ($existingDict->offsetExists('DV')) {
                            $intermediate['DV'] = clone $existingDict->offsetGet('DV');
                        }

                        $ff = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($existingDict, 'Ff', null);
                        if ($ff !== null) {
                            $intermediate['Ff'] = clone $ff;
                        }

                        unset($existingDict['V'], $existingDict['DV'], $existingDict['T'], $existingDict['FT'], $existingDict['Ff']);

                        $parentObject = $resDocument->createNewObject($intermediate);
                        $parentFieldsArray[] = $parentObject;

                        $existingDict['Parent'] = $parentObject;
                    }

                    /** @var SetaPDF_Core_Type_Dictionary $newDict */
                    $newDict = $object->ensure(true);
                    $newDict['Parent'] = $parentObject;
                    unset($newDict['V'], $newDict['DV'], $newDict['T'], $newDict['FT'], $newDict['Ff']);

                    /** @var SetaPDF_Core_Type_Dictionary $parentDict */
                    $parentDict = $parentObject->ensure();

                    // make sure that P is removed if Kids array is available
                    // because it will result in invalid page references
                    //
                    unset($parentDict['P']);

                    $kids = $parentDict['Kids']->ensure();
                    // Add the field only if not already done
                    if ($kids->indexOf($object) === -1) {
                        $kids[] = $object;
                    }

                    //$names[$name] = $object;

                    // A field where a parent one exists
                } elseif (isset($parents[$parentName])) {
                    $parentDict = $parents[$parentName]->ensure(true);
                    $kids = $parentDict['Kids']->ensure();

                    if ($kids->indexOf($object) === -1) {
                        $object->ensure(true)->offsetSet('Parent', $parents[$parentName]);
                        $kids[] = $object;
                    }

                    $names[$name] = $object;

                    // A new field
                } else {
                    $terminalObject = $object;
                    // Add the root field to the Fields array
                    while (
                        SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Parent') instanceof SetaPDF_Core_Type_Dictionary
                    ) {
                        $object = $dict->getValue('Parent');
                        $dict = $object->ensure(true);
                    }

                    if ($resFieldsArray->indexOf($object) === -1) {
                        $resFieldsArray[] = $object;
                    }

                    $names[$name] = $terminalObject;

                    if ($parentName !== null && !isset($parents[$parentName])) {
                        $parents[$parentName] = $parent;
                    }
                }
            }
        }

        foreach ($deleteObjects as $object) {
            $this->_removeFormFieldFromFieldTree($object);
        }

        $co = $acroForm->getCalculationOrderArray();
        if ($co) {
            $resAcroForm->getCalculationOrderArray(true)->merge($co);
        }
    }

    /**
     * Handles AcroForm data by renaming same named form fields.
     *
     * @param array $touchedPdfs
     */
    protected function _handleAcroFormsByRenamingSameNamedFields($touchedPdfs)
    {
        /**
         * 1. Resolve all field names of the initial document
         * 2. Walk through all other documents
         * 2a. Copy fields if the names do not exists
         * 2b. Copy fields if the names exists and rename it
         */
        $resDocument = $this->getInitialDocument();
        $resAcroForm = $resDocument->getCatalog()->getAcroForm();
        $resPages = $resDocument->getCatalog()->getPages();
        $resFieldsArray = $resAcroForm->getFieldsArray();
        $resAcroFormInitiated = false;

        $names = [];
        $parents = [];
        $deleteObjects = [];

        if ($resFieldsArray && $resFieldsArray->count() > 0) {
            $initialFieldsObjects = $resAcroForm->getTerminalFieldsObjects();
            foreach ($initialFieldsObjects AS $terminalObject) {
                /* We need the object holding the "T" entry, because this may be cloned and attached
                 * to the resulting document already.
                 */
                $object = SetaPDF_Core_Type_Dictionary_Helper::resolveObjectByAttribute($terminalObject, 'T');
                $name = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName(
                    $resDocument->ensureObject($object)->ensure()
                );
                $names[$name] = $terminalObject;

                $parentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($terminalObject->ensure(true), 'Parent');
                if ($parentDict instanceof SetaPDF_Core_Type_Dictionary) {
                    $parent = $terminalObject->ensure(true)->getValue('Parent')->getValue();
                    $parentName = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName(
                        $resDocument->ensureObject($parent)->ensure()
                    );
                    $parents[$parentName] = $parent;
                }
            }
        }

        foreach ($this->_documents AS $documentData) {
            $document = $this->_getDocument($documentData[0]);
            $ident = $document->getInstanceIdent();
            if (!isset($touchedPdfs[$ident])) {
                continue;
            }

            $acroForm = $document->getCatalog()->getAcroForm();
            $fieldsArray = $acroForm->getFieldsArray();
            if ($fieldsArray === false || $fieldsArray->count() === 0) {
                continue;
            }

            // Setup the AcroForm entry in the resulting document
            if ($resAcroFormInitiated === false) {
                $resAcroForm->addDefaultEntriesAndValues();
                $resFieldsArray = $resAcroForm->getFieldsArray();
                $resAcroFormInitiated = true;
            }

            // Copy DR field values
            $resAcroFormDict = $resAcroForm->getDictionary();
            $acroFormDict = $acroForm->getDictionary();
            if ($acroFormDict->offsetExists('DR')) {
                $resDr = $resAcroFormDict->getValue('DR');
                foreach ($acroFormDict->getValue('DR') AS $name => $values) {
                    if (!$resDr->offsetExists($name)) {
                        $resDr[$name] = clone $values;
                    }

                    foreach ($values AS $resName => $value) {
                        $resDict = $resDr[$name]->getValue();
                        if (!$resDict->offsetExists($resName)) {
                            $resDict->offsetSet($resName, clone $value);
                        }
                    }
                }
            }

            $fieldsObjects = $acroForm->getTerminalFieldsObjects();

            foreach ($fieldsObjects AS $object) {
                $page = $resPages->getPageByAnnotation($object);
                if ($page === false) {
                    $deleteObjects[] = $object;
                    $document->blockReferencedObject($object);
                    continue;
                }

                $dict = $object->ensure(true);
                $name = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($dict);
                // resolve parent name
                $parent = $parentName = null;
                if ($dict->offsetExists('Parent')) {
                    $parentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Parent');
                    if ($parentDict instanceof SetaPDF_Core_Type_Dictionary) {
                        $parent = $dict->getValue('Parent')->getValue();
                        $parentName = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($parentDict);
                    }
                }

                // If a same named field already exists
                if (isset($names[$name]) && $names[$name] !== $ident) {
                    $parentIsSame = $parentName === $name;
                    $suffix = 0;
                    while (isset($names[$name . '-' . $suffix]) && $names[$name . '-' . $suffix] !== $ident) {
                        $suffix++;
                    }

                    $originalObject = $object;
                    $originalTObject = SetaPDF_Core_Type_Dictionary_Helper::resolveObjectByAttribute($originalObject, 'T');
                    $ot = $originalTObject->ensure(true)->getValue('T');

                    $object = $object->deepClone($resDocument);
                    $tObject = SetaPDF_Core_Type_Dictionary_Helper::resolveObjectByAttribute($object, 'T');
                    $t = $tObject->ensure(true)->getValue('T');

                    $_name = $ot->getValue();
                    if (strpos($_name, "\xFE\xFF") === 0) {
                        $_name = SetaPDF_Core_Encoding::convert($_name . '-' . $suffix, 'UTF-8', 'UTF-16BE');
                    } else {
                        $_name .= '-' . $suffix;
                    }

                    $t->setValue($_name);

                    $name .= '-' . $suffix;
                    if ($parentIsSame) {
                        $parentName = $name;
                    }
                }

                if (isset($parents[$parentName])) {
                    // TODO: At this point we could merge same named intermediate nodes

                    $parentDict = $parents[$parentName]->ensure(true);
                    $kids = $parentDict['Kids']->ensure();

                    if ($kids->indexOf($object) === -1) {
                        $object->ensure(true)->offsetSet('Parent', $parents[$parentName]);
                        $kids[] = $object;
                    }

                    $names[$name] = $ident;

                    // A new field
                } else {
                    // Add the root field to the Fields array
                    while (
                        SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Parent') instanceof SetaPDF_Core_Type_Dictionary
                    ) {
                        $object = $dict->getValue('Parent');
                        $dict = $object->ensure(true);
                    }

                    if ($resFieldsArray->indexOf($object) === -1) {
                        $resFieldsArray[] = $object;
                    }

                    $names[$name] = $ident;

                    if ($parentName !== null && !isset($parents[$parentName])) {
                        $parents[$parentName] = $parent;
                    }
                }
            }
        }

        foreach ($deleteObjects as $object) {
            $this->_removeFormFieldFromFieldTree($object);
        }

        $co = $acroForm->getCalculationOrderArray();
        if ($co) {
            $resAcroForm->getCalculationOrderArray(true)->merge($co);
        }
    }

    /**
     * Removes a form field in its parent fields array
     *
     * @param $fieldObject
     */
    protected function _removeFormFieldFromFieldTree($fieldObject)
    {
        $document = $this->getInitialDocument();
        list($objectId, $generation) = $document->getIdForObject($fieldObject);
        try {
            $fieldObject = $document->resolveIndirectObject($objectId, $generation);
        } catch (SetaPDF_Core_Document_ObjectNotDefinedException $e) {
            // use the original object instance
        }

        // remove field object(s)
        $currentObject = $fieldObject;

        while (
            $currentObject->ensure(true) instanceof SetaPDF_Core_Type_Dictionary &&
            $currentObject->ensure(true)->offsetExists('Parent')
        ) {
            $lastObject = $currentObject->observe();
            $currentObject = $currentObject->ensure(true)->getValue('Parent');
            if (!$currentObject->ensure(true) instanceof SetaPDF_Core_Type_Dictionary) {
                break;
            }

            if ($currentObject->ensure(true)->offsetExists('Kids')) {
                $kids = $currentObject->ensure(true)->offsetGet('Kids')->ensure(true);
                foreach ($kids as $key => $value) {
                    if (
                        $value instanceof SetaPDF_Core_Type_IndirectReference &&
                        $lastObject->getObjectIdent() === $value->getObjectIdent()
                    ) {
                        $kids->offsetUnset($key);
                    }
                }

                // If there are still fields in the kids array, stop here
                if ($kids->count() > 0) {
                    break;
                }
            }
        }
    }

    /**
     * Imports names of all used documents and defined named destinations.
     *
     * @param array $touchedPdfs
     * @param array $namedDestinations
     */
    protected function _handleNames($touchedPdfs, $namedDestinations)
    {
        if ($this->_handleNames !== true && count($namedDestinations) > 0) {
            return;
        }

        $resDocument = $this->getInitialDocument();
        $resPages = $resDocument->getCatalog()->getPages();

        $resNames = $resDocument->getCatalog()->getNames();
        if (count($namedDestinations) > 0) {
            $dests = $resNames->getTree(SetaPDF_Core_Document_Catalog_Names::DESTS, true);
            foreach ($namedDestinations AS $name => $namedDestinationData) {
                $pageNumber = $namedDestinationData['pageNo'];

                $parameter = [$resPages->getPagesIndirectObject($pageNumber)];
                $parameter = array_merge($parameter, $namedDestinationData['fitMode']);

                $destArray = call_user_func_array(
                    ['SetaPDF_Core_Document_Destination', 'createDestinationArray'],
                    $parameter
                );

                $dests->add($name, $destArray);
            }
        }

        if ($this->_handleNames === true) {
            $this->_renamed = [];
            $resultIdent = $resDocument->getInstanceIdent();
            $namesCopied = [];
            foreach ($this->_documents AS $documentData) {
                $document = $this->_getDocument($documentData[0]);
                $ident = $document->getInstanceIdent();
                if (!isset($touchedPdfs[$ident]) || $resultIdent === $ident || isset($namesCopied[$ident])) {
                    continue;
                }

                $this->_renamed[$ident] = [];

                $names = $document->getCatalog()->getNames();
                $trees = $names->getTrees();
                foreach ($trees AS $name => $tree) {
                    $resTree = $resNames->getTree($name, true);
                    $_renamed = $resTree->merge($tree, $this->_adjustNameCallback);
                    $this->_renamed[$ident] = array_merge($this->_renamed[$ident], $_renamed);
                    $namesCopied[$ident] = true;
                }

                if (count($this->_renamed[$ident])) {
                    $document->registerWriteCallback(
                        [$this, 'rewriteNamesCallback'],
                        'SetaPDF_Core_Type_String',
                        'rewrite strings'
                    );

                    $document->registerWriteCallback(
                        [$this, 'rewriteNamesCallback'],
                        'SetaPDF_Core_Type_HexString',
                        'rewrite hex strings'
                    );
                }
            }
        }
    }

    /**
     * Handles optional content data (Layers).
     *
     * @param array $touchedPdfs
     */
    protected function _handleOptionalContent(array $touchedPdfs)
    {
        $processedPdfs = [];

        $optionalContent = $this->getDocument()->getCatalog()->getOptionalContent();

        foreach ($this->_documents AS $documentData) {
            $document = $this->_getDocument($documentData[0]);
            $ident = $document->getInstanceIdent();
            if ($documentData[4] !== true || !isset($touchedPdfs[$ident]) || isset($processedPdfs[$ident])) {
                continue;
            }

            $_optionalContent = $document->getCatalog()->getOptionalContent();

            $orderArray = $_optionalContent->getOrderArray();
            if ($orderArray) {
                $optionalContent->getOrderArray(true)->merge($orderArray);
            }

            $onArray = $_optionalContent->getOnArray();
            if ($onArray) {
                $optionalContent->getOnArray(true)->merge($onArray);
            }

            $offArray = $_optionalContent->getOffArray();
            if ($offArray) {
                $optionalContent->getOffArray(true)->merge($offArray);
            }

            $asArray = $_optionalContent->getAsArray();
            if ($asArray) {
                $optionalContent->getAsArray(true)->merge($asArray);
            }

            foreach ($_optionalContent->getGroups() AS $group) {
                if ($group instanceof SetaPDF_Core_Document_OptionalContent_Group) {
                    $optionalContent->addGroup($group);
                }
            }

            $processedPdfs[$ident] = true;
        }
    }

    /**
     * Callback method for renaming string values of renamed names.
     *
     * @see SetaPDF_Merger::_handleNames()
     * @param SetaPDF_Core_Document $document The document instance
     * @param SetaPDF_Core_Type_StringValue $value The string value
     */
    public function rewriteNamesCallback(SetaPDF_Core_Document $document, SetaPDF_Core_Type_StringValue $value)
    {
        $ident = $document->getInstanceIdent();
        if (count($this->_renamed[$ident]) === 0) {
            return;
        }

        $currentValue = $value->getValue();
        if (isset($this->_renamed[$ident][$currentValue])) {
            $value->setValue($this->_renamed[$ident][$currentValue]);
        }
    }

    /**
     * Checks a page number against a condition.
     *
     * @param integer $pageNumber The page number
     * @param null|integer|string|array|callback $condition Valid conditions are:
     *          <ul>
     *          <li><b>PAGES_XXX</b> constant or <b>null</b> (equal to {@link SetaPDF_Merger::PAGES_ALL})</li>
     *          <li><b>Integer</b> with the valid page number</li>
     *          <li><b>String</b> with the valid page number or the valid range (e.g. '10-12')</li>
     *          <li><b>Array</b> with all valid page numbers</li>
     *          <li><b>Callback</b> with the arguments (int $pageNumber, \SetaPDF_Core_Document $document)</li>
     *          </ul>
     * @return boolean
     */
    protected function _checkPageNumber($pageNumber, $condition = null)
    {
        if (
            null === $condition ||
            $condition === self::PAGES_ALL ||
            ($pageNumber === 1 && $condition === self::PAGES_FIRST) ||
            (
                $condition === self::PAGES_LAST &&
                $this->_currentDocument->getCatalog()->getPages()->count() === $pageNumber
            )
        ) {
            return true;
        }

        if (is_string($condition) && preg_match('~^(\d+)-(\d*)$~', $condition, $matches)) {
            $start = (int)$matches[1];
            $end   = $matches[2] ? (int)$matches[2] : $this->_currentDocument->getCatalog()->getPages()->count();

            return $pageNumber >= $start && $pageNumber <= $end;
        }

        if (is_callable($condition)) {
            return $condition($pageNumber, $this->_currentDocument);
        }

        if (is_array($condition)) {
            return in_array($pageNumber, $condition, true);
        }

        return $pageNumber == $condition;
    }

    /**
     * Get a document instance by filename.
     *
     * @param string|SetaPDF_Core_Document $filename The filename
     * @param boolean $cache Cache the document by filename
     * @return SetaPDF_Core_Document
     * @throws SetaPDF_Merger_Exception
     */
    protected function _getDocument($filename, $cache = true)
    {
        if ($filename instanceof SetaPDF_Core_Document) {
            return $filename;
        }

        if (!isset($this->_documentCache[$filename])) {
            $maxFileHandler = $this->getMaxFileHandler();
            try {
                if ($maxFileHandler !== null) {
                    $reader = $maxFileHandler->createReader($filename);
                    $document = SetaPDF_Core_Document::load($reader);
                } else {
                    $document = SetaPDF_Core_Document::loadByFilename($filename);
                }
            } catch (Exception $e) {
                throw new SetaPDF_Merger_Exception(
                    'An error occurs while creating the document instance.', 0, $e, $filename
                );
            }
            if ($cache) {
                $this->_documentCache[$filename] = $document;
            } else {
                return $document;
            }
        }

        return $this->_documentCache[$filename];
    }

    /**
     * Get a document instance by a filename.
     *
     * @param string $filename The filename
     * @param boolean $cache Cache the document by filename
     * @return SetaPDF_Core_Document
     */
    public function getDocumentByFilename($filename, $cache = true)
    {
        return $this->_getDocument($filename, $cache);
    }

    /**
     * Get the currently processed document instance.
     *
     * This method can be used to get the document instance that is actually processed if an Exception is thrown.
     *
     * @return SetaPDF_Core_Document
     */
    public function getCurrentDocument()
    {
        return $this->_currentDocument;
    }

    /**
     * Release objects to free memory and cycled references.
     *
     * After calling this method the instance of this object is unusable!
     *
     * @return void
     */
    public function cleanUp()
    {
        foreach (array_keys($this->_documents) AS $key) {
            $document = $this->_getDocument($this->_documents[$key][0]);
            $document->cleanUp();
            unset($this->_documents[$key]);
        }

        $this->_documents = [];

        foreach (array_keys($this->_documentCache) AS $key) {
            $this->_documentCache[$key]->cleanUp();
            unset($this->_documentCache[$key]);
        }

        if ($this->_initialDocument !== null) {
            $this->_initialDocument->cleanUp();
            $this->_initialDocument = null;
        }
    }
}