<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Signer.php 1543 2020-09-15 14:57:30Z jan.slabon $
 */

/**
 * The main class of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer
{
    /**
     * Version
     *
     * @var string
     */
    const VERSION = SetaPDF_Core::VERSION;

    /**
     * Property constant
     *
     * @var string
     */
    const PROP_NAME = 'Name';

    /**
     * Property constant
     *
     * @var string
     */
    const PROP_LOCATION = 'Location';

    /**
     * Property constant
     *
     * @var string
     */
    const PROP_REASON = 'Reason';

    /**
     * Property constant
     *
     * @var string
     */
    const PROP_TIME_OF_SIGNING = 'M';

    /**
     * Property constant
     *
     * @var string
     */
    const PROP_CONTACT_INFO = 'ContactInfo';

    /**
     * Certification level constant
     *
     * @var integer
     */
    const CERTIFICATION_LEVEL_NONE = 0;

    /**
     * Certification level constant
     *
     * @var integer
     */
    const CERTIFICATION_LEVEL_NO_CHANGES_ALLOWED = 1;

    /**
     * Certification level constant
     *
     * @var integer
     */
    const CERTIFICATION_LEVEL_FORM_FILLING = 2;

    /**
     * Certification level constant
     *
     * @var integer
     */
    const CERTIFICATION_LEVEL_FORM_FILLING_AND_ANNOTATIONS = 3;

    /**
     * Document which shall be signed
     *
     * @var SetaPDF_Core_Document
     */
    protected $_document;

    /**
     * A temporary instance of the signed document
     *
     * @var SetaPDF_Core_Document
     */
    protected $_tempDocument;

    /**
     * The callback for authenticating at the security handler of the document
     *
     * @var null|callback
     */
    protected $_secHandlerCallback;

    /**
     * The signature field name
     *
     * @var string
     */
    protected $_fieldName = SetaPDF_Signer_SignatureField::DEFAULT_FIELD_NAME;

    /**
     * The signature properties
     *
     * @var array
     */
    protected $_signatureProperties = [
        self::PROP_NAME => null,
        self::PROP_LOCATION => null,
        self::PROP_REASON => null,
        self::PROP_TIME_OF_SIGNING => null,
        self::PROP_CONTACT_INFO => null
    ];

    /**
     * The certification level
     *
     * @var int
     */
    protected $_certificationLevel = self::CERTIFICATION_LEVEL_NONE;

    /**
     * The temporary writer instance
     *
     * @var SetaPDF_Core_Writer_WriterInterface
     */
    protected $_tempWriter;

    /**
     * The byte range value
     *
     * @var SetaPDF_Core_Type_Array
     */
    protected $_byteRange;

    /**
     * The byte offset position of the place holder/reserved space
     *
     * @var int
     */
    protected $_placeHolderByteOffset;

    /**
     * The byte length of the reserved space for the signature content
     *
     * @var int
     */
    protected $_signatureContentLength = 5000;

    /**
     * Defines if the signatureContentLength property can be changed automatically, if a signature value doesn't fit
     * into the reserved space.
     *
     * @var bool
     */
    protected $_allowSignatureContentLengthChange = true;

    /**
     * A placeholder for the second length value in the ByteRange array
     *
     * @var string
     */
    protected $_offsetEndPlaceholder = '(<---OFFSET--->)';

    /**
     * A timestamp module interface
     *
     * @var SetaPDF_Signer_Timestamp_Module_ModuleInterface
     */
    protected $_timestampModule;

    /**
     * An appearance instance
     *
     * @var SetaPDF_Signer_Signature_Appearance_AbstractAppearance
     */
    protected $_appearance;

    /**
     * The constructor.
     *
     * If the passed document is protected by a security handler it is possible to pass a callback as second parameter
     * that will be called if an authentication is needed.
     *
     * @param SetaPDF_Core_Document $document The document instance
     * @param callback|null $secHandlerCallback A callback which should auth on a security handler (if needed).
     *                                          The callback will be called with two parameters: The first parameter
     *                                          will be the
     *                                          {@link SetaPDF_Core_SecHandler_SecHandlerInterface security handler} and
     *                                          the second parameter is the current
     *                                          {@link SetaPDF_Core_Document document} instance. It should return true
     *                                          or false whether the authentication was successful or not.
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function __construct(SetaPDF_Core_Document $document, $secHandlerCallback = null)
    {
        if ($secHandlerCallback !== null && is_callable($secHandlerCallback)) {
            $this->_secHandlerCallback = $secHandlerCallback;
            $this->_callSecHandlerCallback($document);
        }

        SetaPDF_Core_SecHandler::checkPermission($document, SetaPDF_Core_SecHandler::PERM_FILL_FORM);

        $this->_document = $document;
    }

    /**
     * Release memory and cycled references.
     */
    public function cleanUp()
    {
        $this->_document->cleanUp();
        $this->_document = null;

        if (isset($this->_tempDocument)) {
            $this->_tempDocument->cleanUp();
            $this->_tempDocument = null;
        }

        $this->_tempWriter = null;
    }

    /**
     * Proxy method for {@link SetaPDF_Signer_SignatureField::add()}.
     *
     * @see SetaPDF_Signer_SignatureField::add()
     * @param string $fieldName The field name in UTF-8 encoding
     * @param int $pageNumber The page number on which the signature field shall appear.
     * @param int|string $xOrPosition Integer with the x-position or {@link SetaPDF_Signer_SignatureField::POSITION_XXX}
     * @param int|array $yOrTranslate Integer with the y-position (if $xOrPosition is an integer) or an array with the keys 'x' and 'y'
     * @param int $width Width of the signature field
     * @param int $height Height of the signature field
     * @return SetaPDF_Signer_SignatureField
     */
    public function addSignatureField(
        $fieldName = SetaPDF_Signer_SignatureField::DEFAULT_FIELD_NAME,
        $pageNumber = 1,
        $xOrPosition = 0,
        $yOrTranslate = 0,
        $width = 0,
        $height = 0
    ) {
        return SetaPDF_Signer_SignatureField::add(
            $this->_document, $fieldName, $pageNumber, $xOrPosition, $yOrTranslate, $width, $height
        );
    }

    /**
     * Proxy method to {@link SetaPDF_Signer_SignatureField::get()}.
     *
     * @see SetaPDF_Signer_SignatureField::get()
     * @param string $fieldName The field name in UTF-8 encoding
     * @param bool $create Automatically creates a hidden field if none was found by the specified name
     * @return bool|SetaPDF_Signer_SignatureField
     */
    public function getSignatureField($fieldName = SetaPDF_Signer_SignatureField::DEFAULT_FIELD_NAME, $create = true)
    {
        return SetaPDF_Signer_SignatureField::get($this->_document, $fieldName, $create);
    }

    /**
     * Set the signature field name.
     *
     * This can be the name of an existing signature field or an individual name which will be used to create a
     * hidden field automatically.
     *
     * @param string $fieldName The field name in UTF-8 encoding
     */
    public function setSignatureFieldName($fieldName)
    {
        $this->_fieldName = $fieldName;
    }

    /**
     * Set a signature property.
     *
     * @param string $name See and use {@link SetaPDF_Signer::PROP_*} constants for valid names
     * @param string $value The value in UTF-8 encoding
     * @throws InvalidArgumentException if the name is not valid
     */
    public function setSignatureProperty($name, $value)
    {
        if (!array_key_exists($name, $this->_signatureProperties)) {
            throw new InvalidArgumentException('Invalid signature property: ' . $name);
        }

        $this->_signatureProperties[$name] = $value;
    }

    /**
     * Get a signature property.
     *
     * @param string $name See and use {@link SetaPDF_Signer::PROP_*} constants for valid names
     * @return bool|string If the name is not valid FALSE will be returned. Otherwise the value will be returned.
     */
    public function getSignatureProperty($name)
    {
        if (isset($this->_signatureProperties[$name])) {
            return $this->_signatureProperties[$name];
        }

        return false;
    }

    /**
     * Set the name of the person or authority signing the document.
     *
     * @see SetaPDF_Signer::setSignatureProperty()
     * @param string $name The value in UTF-8 encoding
     */
    public function setName($name)
    {
        $this->setSignatureProperty(self::PROP_NAME, $name);
    }

    /**
     * Get the name of the person or authority signing the document.
     *
     * @see SetaPDF_Signer::getSignatureProperty()
     * @return bool|string If no name exist FALSE will be returned. Otherwise the value will be returned.
     */
    public function getName()
    {
        return $this->getSignatureProperty(self::PROP_NAME);
    }

    /**
     * Set the host name or physical location of the signing.
     *
     * @see SetaPDF_Signer::setSignatureProperty()
     * @param string $location The physical location or host name in UTF-8 encoding
     */
    public function setLocation($location)
    {
        $this->setSignatureProperty(self::PROP_LOCATION, $location);
    }

    /**
     * Get the CPU host name or physical location of the signing.
     *
     * @see SetaPDF_Signer::getSignatureProperty()
     * @return bool|string If no location exist FALSE will be returned. Otherwise the value will be returned.
     */
    public function getLocation()
    {
        return $this->getSignatureProperty(self::PROP_LOCATION);
    }

    /**
     * Set the reason for the signing.
     *
     * @see SetaPDF_Signer::setSignatureProperty()
     * @param string $reason The reason for the signing in UTF-8 encoding
     */
    public function setReason($reason)
    {
        $this->setSignatureProperty(self::PROP_REASON, $reason);
    }

    /**
     * Get the reason for the signing.
     *
     * @see SetaPDF_Signer::getSignatureProperty()
     * @return bool|string If no name exist FALSE will be returned. Otherwise the value will be returned.
     */
    public function getReason()
    {
        return $this->getSignatureProperty(self::PROP_REASON);
    }

    /**
     * Set the time of signing.
     *
     * @see SetaPDF_Signer::setSignatureProperty()
     * @param string $timeOfSigning The time of signing in UTF-8 encoding
     */
    public function setTimeOfSigning($timeOfSigning)
    {
        $this->setSignatureProperty(self::PROP_TIME_OF_SIGNING, $timeOfSigning);
    }

    /**
     * Get the time of signing.
     *
     * @see SetaPDF_Signer::getSignatureProperty()
     * @return bool|SetaPDF_Core_DataStructure_Date If no name exist FALSE will be returned.
     *                                              Otherwise the value will be returned as {@link SetaPDF_Core_DataStructure_Date}.
     */
    public function getTimeOfSigning()
    {
        $property = $this->getSignatureProperty(self::PROP_TIME_OF_SIGNING);
        if (!$property instanceof SetaPDF_Core_DataStructure_Date) {
            $property = new SetaPDF_Core_DataStructure_Date($property);
        }
        return $property;
    }

    /**
     * Set the information provided by the signer to enable a recipient to contact the signer to verify the signature.
     *
     * @see SetaPDF_Signer::setSignatureProperty()
     * @param string $contactInfo The contact info in UTF-8 encoding
     */
    public function setContactInfo($contactInfo)
    {
        $this->setSignatureProperty(self::PROP_CONTACT_INFO, $contactInfo);
    }

    /**
     * Get the information provided by the signer to enable a recipient to contact the signer to verify the signature.
     *
     * @see SetaPDF_Signer::getSignatureProperty()
     * @return bool|string If no contact info exist FALSE will be returned. Otherwise the value will be returned.
     */
    public function getContactInfo()
    {
        return $this->getSignatureProperty(self::PROP_CONTACT_INFO);
    }

    /**
     * Set the certification level.
     *
     * @param integer $certificationLevel Possible values are defined in the {@link SetaPDF_Signer::CERTIFICATION_LEVEL_XXX} constants.
     * @see SetaPDF_Signer::CERTIFICATION_LEVEL_NONE
     * @see SetaPDF_Signer::CERTIFICATION_LEVEL_NO_CHANGES_ALLOWED
     * @see SetaPDF_Signer::CERTIFICATION_LEVEL_FORM_FILLING
     * @see SetaPDF_Signer::CERTIFICATION_LEVEL_FORM_FILLING_AND_ANNOTATIONS
     */
    public function setCertificationLevel($certificationLevel)
    {
        $this->_certificationLevel = (int)$certificationLevel;
    }

    /**
     * Get the certification level.
     *
     * Possible values are defined in the {@link SetaPDF_Signer::CERTIFICATION_LEVEL_*} constants.
     *
     * @return int
     * @see SetaPDF_Signer::CERTIFICATION_LEVEL_NONE
     * @see SetaPDF_Signer::CERTIFICATION_LEVEL_NO_CHANGES_ALLOWED
     * @see SetaPDF_Signer::CERTIFICATION_LEVEL_FORM_FILLING
     * @see SetaPDF_Signer::CERTIFICATION_LEVEL_FORM_FILLING_AND_ANNOTATIONS
     */
    public function getCertificationLevel()
    {
        return $this->_certificationLevel;
    }

    /**
     * Set the signature content length that will be used to reserve space for the final signature.
     *
     * @param integer $length The length of the signature content.
     */
    public function setSignatureContentLength($length)
    {
        if (($length % 2) === 1) {
            $length++;
        }

        $this->_signatureContentLength = (int)$length;
    }

    /**
     * Get the signature content length that will be used to reserve space for the final signature.
     *
     * @return integer
     */
    public function getSignatureContentLength()
    {
        return $this->_signatureContentLength;
    }

    /**
     * Set a flag specifying whether the signature content length (reserved space) could be changed automatically or not.
     *
     * If this value is set to true and the resulting signature is bigger than the reserved space (defined by
     * {@link SetaPDF_Signer::setSignatureContentLength()} the signature content length will be increased and the
     * signature process will restart.
     *
     * @param bool $allowSignatureContentLengthChange The flag status
     */
    public function setAllowSignatureContentLengthChange($allowSignatureContentLengthChange)
    {
        $this->_allowSignatureContentLengthChange = (boolean)$allowSignatureContentLengthChange;
    }

    /**
     * Get the flag specifying whether the signature content length (reserved space) could be changed automatically ot not.
     *
     * @return bool
     */
    public function getAllowSignatureContentLengthChange()
    {
        return $this->_allowSignatureContentLengthChange;
    }

    /**
     * Set a timestamp module.
     *
     * @param SetaPDF_Signer_Timestamp_Module_ModuleInterface $module The timestamp module instance
     */
    public function setTimestampModule(SetaPDF_Signer_Timestamp_Module_ModuleInterface $module = null)
    {
        $this->_timestampModule = $module;
    }

    /**
     * Get the current timestamp module.
     *
     * @return SetaPDF_Signer_Timestamp_Module_ModuleInterface
     */
    public function getTimestampModule()
    {
        return $this->_timestampModule;
    }

    /**
     * Set an appearance instance.
     *
     * @param SetaPDF_Signer_Signature_Appearance_AbstractAppearance $appearance The appearance instance
     */
    public function setAppearance(SetaPDF_Signer_Signature_Appearance_AbstractAppearance $appearance = null)
    {
        $this->_appearance = $appearance;
    }

    /**
     * Get the current appearance module.
     *
     * @return SetaPDF_Signer_Signature_Appearance_AbstractAppearance
     */
    public function getAppearance()
    {
        return $this->_appearance;
    }

    /**
     * Digital signs the initial document.
     *
     * The {@link SetaPDF_Core_Document} instance will be temporary saved and finished. It is not possible to
     * work on it further. The final PDF will be passed to the previously attached writer instance, so that the final
     * signed document will be written to the correct writer.
     *
     * @param SetaPDF_Signer_Signature_Module_ModuleInterface $module The module instance
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function sign(SetaPDF_Signer_Signature_Module_ModuleInterface $module)
    {
        $writers = $this->_prepareTemporaryVersion();
        $this->_sign($writers['mainWriter'], $writers['tempWriter']->getPath(), $module);
    }

    /**
     * Prepares a temporary document instance to be used further in an asynchronous signature workflow.
     *
     * @param SetaPDF_Core_Writer_FileInterface $writer
     * @param null|SetaPDF_Signer_Signature_DictionaryInterface $module
     * @return SetaPDF_Signer_TmpDocument
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Core_Exception
     * @see createSignature()
     */
    public function preSign(SetaPDF_Core_Writer_FileInterface $writer, $module = null)
    {
        $tmpDocument = new SetaPDF_Signer_TmpDocument($writer);
        $tmpDocument->setDocumentIdentification($this->_document);

        $writers = $this->_prepareTemporaryVersion(true);

        $this->_prepareTmpDocument('signature', $tmpDocument, $writers['tempWriter']->getPath(), $module);

        return $tmpDocument;
    }

    /**
     * Creates a signature based on a temporary document instance and a signature module.
     *
     * @param SetaPDF_Signer_TmpDocument $tmpDocument
     * @param SetaPDF_Signer_Signature_Module_ModuleInterface $module
     * @return string
     * @throws SetaPDF_Signer_Exception
     * @see preSign()
     * @see saveSignature()
     */
    public function createSignature(
        SetaPDF_Signer_TmpDocument $tmpDocument,
        SetaPDF_Signer_Signature_Module_ModuleInterface $module
    ) {

        if (!$tmpDocument->matchesDocument($this->_document)) {
            throw new SetaPDF_Signer_Exception("Document doesn't match the temporary version.");
        }

        $tmpPath = $tmpDocument->getHashFile();
        return $module->createSignature($tmpPath);
    }

    /**
     * Add a signature result to the temporary document instance and saves it to the main documents writer instance.
     *
     * @param SetaPDF_Signer_TmpDocument $tmpDocument
     * @param string $signature The PKCS7 byte string to be added to the temporary document.
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Signer_Exception_ContentLength
     */
    public function saveSignature(
        SetaPDF_Signer_TmpDocument $tmpDocument,
        $signature
    ) {
        if (!$tmpDocument->matchesDocument($this->_document)) {
            throw new SetaPDF_Signer_Exception("Document doesn't match the temporary version.");
        }

        $signature = str_pad($signature, $tmpDocument->getSignatureContentLength() / 2, "\0", STR_PAD_RIGHT);

        $signatureLength = strlen($signature) * 2;

        if ($signatureLength > $tmpDocument->getSignatureContentLength()) {
            $e = new SetaPDF_Signer_Exception_ContentLength(
                sprintf(
                    'The signature length (%s bytes) doesn\'t fit into the reserved space (%s bytes) and ' .
                    '"$this->_allowSignatureContentLengthChange" is set to false or a timestamp module is used.',
                    $signatureLength,
                    $tmpDocument->getSignatureContentLength()
                )
            );
            $e->setExpectedLength($signatureLength);

            throw $e;
        }

        $tmpDocument->writeSignature($signature);
        $tmpDocument->save($this->_document->getWriter());
    }

    /**
     * Adds a document level timestamp.
     *
     * The {@link SetaPDF_Core_Document} instance will be temporary saved and finished. It is not possible to
     * work on it further. The final PDF will be passed to the previously attached writer instance, so that the final
     * signed document will be written to the correct writer.
     *
     * @return string The timestamp token/signature
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Signer_Exception_ContentLength
     * @throws SetaPDF_Core_Exception
     */
    public function timestamp()
    {
        $writers = $this->_prepareTemporaryVersion();

        $signature = $this->_timestamp($writers['mainWriter'], $writers['tempWriter']->getPath());

        return $signature;
    }

    /**
     * Prepares a temporary document instance to be used further in an asynchronous timestamp workflow.
     *
     * @param SetaPDF_Core_Writer_FileInterface $writer
     * @param null|SetaPDF_Signer_Signature_DictionaryInterface $module
     * @return SetaPDF_Signer_TmpDocument
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Core_Exception
     * @see createSignature()
     */
    public function preTimestamp(SetaPDF_Core_Writer_FileInterface $writer, $module = null)
    {
        $tmpDocument = new SetaPDF_Signer_TmpDocument($writer);
        $tmpDocument->setDocumentIdentification($this->_document);

        $writers = $this->_prepareTemporaryVersion(true);

        $this->_prepareTmpDocument('timestamp', $tmpDocument, $writers['tempWriter']->getPath(), $module);

        return $tmpDocument;
    }

    /**
     * Creates a timestamp signature based on a temporary document instance.
     *
     * The timestamp module has to be added to the signer instance through the
     * {@link SetaPDF_Signer::setTimestampModule() setTimestampModule()} method.
     *
     * @param SetaPDF_Signer_TmpDocument $tmpDocument
     * @return string
     * @throws SetaPDF_Signer_Exception
     * @see preTimestamp()
     * @see saveSignature()
     * @see setTimestampModule()
     */
    public function createTimestampSignature(SetaPDF_Signer_TmpDocument $tmpDocument)
    {
        if (!$tmpDocument->matchesDocument($this->_document)) {
            throw new SetaPDF_Signer_Exception("Document doesn't match the temporary version.");
        }

        $timestampModule = $this->getTimestampModule();
        if (!$timestampModule instanceof SetaPDF_Signer_Timestamp_Module_ModuleInterface) {
            throw new InvalidArgumentException('No timestamp module passed.');
        }

        return (string)$timestampModule->createTimestamp($tmpDocument->getHashFile());
    }

    /**
     * Prepares a temporary version of the document that should be signed.
     *
     * @param boolean $ignoreMainWriter
     * @return array
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Core_Exception
     * @internal
     */
    protected function _prepareTemporaryVersion($ignoreMainWriter = false)
    {
        // rem the main writer
        $mainWriter = $this->_document->getWriter();
        if ($ignoreMainWriter === false && !($mainWriter instanceof SetaPDF_Core_Writer_WriterInterface)) {
            throw new SetaPDF_Signer_Exception('A writer instance needs to be set to the document instance.');
        }

        $this->_checkCertificationLevel();
        $this->_checkForXfaForm();
        $this->_checkForNeedAppearances();

        $this->_ensureSignatureField($this->_document);

        // let's create a copy of the original document
        $tempWriter = new SetaPDF_Core_Writer_TempFile();
        $this->_document->setWriter($tempWriter);
        $this->_document->setCleanUpObjects(false);
        $this->_document->save()->finish();
        // reset the writer instance
        $this->_document->setWriter($mainWriter);

        return [
            'mainWriter' => $mainWriter,
            'tempWriter' => $tempWriter
        ];
    }

    /**
     * Calls a defined callback that should authenticate on the documents security handler.
     *
     * @param SetaPDF_Core_Document $document The document instance
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    protected function _callSecHandlerCallback(SetaPDF_Core_Document $document)
    {
        if (is_callable($this->_secHandlerCallback) && $document->hasSecHandler()) {
            $authenticated = call_user_func($this->_secHandlerCallback, $document->getSecHandler(), $document);
            if ($authenticated === false) {
                throw new SetaPDF_Core_SecHandler_Exception('Authentication failed.');
            }
        }
    }

    /**
     * Checks if a document is already certified.
     *
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Core_Type_IndirectReference_Exception
     */
    protected function _checkCertificationLevel()
    {
        if ($this->getCertificationLevel() === self::CERTIFICATION_LEVEL_NONE) {
            return;
        }

        $root = $this->_document->getCatalog()->getDictionary();
        if ($root->offsetExists('Perms')) {
            /**
             * @var SetaPDF_Core_Type_Dictionary $perms
             */
            $perms = $root->offsetGet('Perms')->getValue()->ensure();
            if ($perms->offsetExists('DocMDP')) {
                throw new SetaPDF_Signer_Exception(
                    'Document is already certified. Only approval signatures are possible'
                );
            }
        }
    }

    /**
     * Checks if the document is a XFA form.
     *
     * The component is not able to digital sign XFA forms and will throw an exception at this point if necessary.
     *
     * @throws SetaPDF_Signer_Exception if the document is a XFA form
     * @internal
     */
    protected function _checkForXfaForm()
    {
        $acroForm = $this->_document->getCatalog()->getAcroForm()->getDictionary();
        if ($acroForm === false || !$acroForm->offsetExists('XFA')) {
            return;
        }

        throw new SetaPDF_Signer_Exception(
            'This document is a XFA form which is not supported.'
        );
    }

    /**
     * Checks for the NeedAppearances flag.
     *
     * If a document has this flag set a signature will not make sense, because its field appearances will be recreated
     * at viewing time, which will destroy the signature.
     *
     * The Acrobat Reader may ignore a digital signature if the NeedAppearance flag is set.
     *
     * @throws SetaPDF_Signer_Exception if the NeedAppearance flag is set
     * @internal
     */
    protected function _checkForNeedAppearances()
    {
        if ($this->_document->getCatalog()->getAcroForm()->isNeedAppearancesSet()) {
            throw new SetaPDF_Signer_Exception(
                'The NeedAppearances flag is set in this document instance. It is useless to digital sign it ' .
                'while instructing a viewer to recreate its field appearances.'
            );
        }
    }

    /**
     * Prepares the temporary document instance.
     *
     * @param string $type
     * @param SetaPDF_Signer_TmpDocument $tmpDocument
     * @param string $path
     * @param null|SetaPDF_Signer_Signature_DictionaryInterface $module
     *
     * @return SetaPDF_Signer_TmpDocument
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Signer_Exception
     */
    protected function _prepareTmpDocument(
        $type, SetaPDF_Signer_TmpDocument $tmpDocument, $path, $module = null
    )
    {
        $this->_tempDocument = SetaPDF_Core_Document::loadByFilename($path);
        $this->_tempDocument->setCleanUpObjects(false);
        $this->_callSecHandlerCallback($this->_tempDocument);

        /** @var SetaPDF_Signer_SignatureField $field */
        $field = $this->_ensureSignatureField($this->_tempDocument, true);

        $this->_updateAcroForm($this->_tempDocument);
        if ($type === 'timestamp') {
            $extensions = $this->_tempDocument->getCatalog()->getExtensions();
            $extensions->setExtension('ESIC', '1.7', 2);

            $this->_createTimestampSignatureDictionary($field, $module);
        } else {
            $signatureObject = $this->_createSignatureDictionary($field, $module);
        }

        if ($module instanceof SetaPDF_Signer_Signature_DocumentInterface) {
            $module->updateDocument($this->_tempDocument);
        }

        // Call appearance instance to create/adjust appearance -> pass document instance!
        $appearance = $this->getAppearance();
        if ($appearance instanceof SetaPDF_Signer_Signature_Appearance_AbstractAppearance) {
            $appearance->createAppearance($field, $this->_tempDocument, $this);
        }

        if ($type === 'signature' && $this->getCertificationLevel() != self::CERTIFICATION_LEVEL_NONE) {
            $root = $this->_tempDocument->getCatalog()->getDictionary();
            $root->offsetSet('Perms', new SetaPDF_Core_Type_Dictionary([
                'DocMDP' => $signatureObject
            ]));
        }

        // let's write the document including a reserved space
        $this->_tempWriter = $tmpDocument->getWriter();

        $this->_tempDocument->setWriter($this->_tempWriter);
        $this->_tempDocument->setDirectWrite(true);
        $this->_tempDocument->save();
        $offsetLength = $this->_tempWriter->getPos() - $this->_byteRange[2]->getValue();
        $this->_tempDocument->finish();

        $tmpDocument->updateLastByteOffset($this->_placeHolderByteOffset + 1, $offsetLength, $this->_offsetEndPlaceholder);
        $this->_byteRange[3] = new SetaPDF_Core_Type_Numeric($offsetLength);
        $tmpDocument->setByteRange($this->_byteRange->toPhp());
        $tmpDocument->updateIdentificationHash();

        return $tmpDocument;
    }

    /**
     * This internal method adds the digital signature to the document.
     *
     * The process is encapsulated into a separate method to allow a recursive call if a signature fails because
     * of a to low $_signatureContentLength value.
     *
     * @param SetaPDF_Core_Writer_WriterInterface $mainWriter The writer instance
     * @param string $path
     * @param SetaPDF_Signer_Signature_Module_ModuleInterface $module
     * @return string
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Signer_Exception_ContentLength if the $_signatureContentLength value is too low
     * @internal
     */
    protected function _sign(
        SetaPDF_Core_Writer_WriterInterface $mainWriter,
        $path,
        SetaPDF_Signer_Signature_Module_ModuleInterface $module
    )
    {
        $tmpDocument = new SetaPDF_Signer_TmpDocument();
        $tmpDocument->setDocumentIdentification($this->_document);

        $this->_prepareTmpDocument('signature', $tmpDocument, $path, $module);

        $signature = $this->createSignature($tmpDocument, $module);
        $signature = $this->addTimeStamp($signature, $tmpDocument);

        try {
            $this->saveSignature($tmpDocument, $signature);

        } catch (Exception $e) {
            if ($e instanceof SetaPDF_Signer_Exception_ContentLength && $this->_allowSignatureContentLengthChange) {
                $this->setSignatureContentLength($e->getExpectedLength());
                return $this->_sign($mainWriter, $path, $module);
            }

            throw $e;
        }

        return $signature;
    }

    /**
     * This internal method adds a document level timestamp.
     *
     * The process is encapsulated into a separate method to allow a recursive call if a signature fails because
     * of a to low $_signatureContentLength value.
     *
     * @param SetaPDF_Core_Writer_WriterInterface $mainWriter The writer instance
     * @param string $path
     * @return string The timestamp token/signature
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Signer_Exception_ContentLength
     * @internal
     */
    protected function _timestamp(
        SetaPDF_Core_Writer_WriterInterface $mainWriter,
        $path
    )
    {
        $timestampModule = $this->getTimestampModule();
        if (!$timestampModule instanceof SetaPDF_Signer_Timestamp_Module_ModuleInterface) {
            throw new InvalidArgumentException('No timestamp module passed.');
        }

        $tmpDocument = new SetaPDF_Signer_TmpDocument();
        $tmpDocument->setDocumentIdentification($this->_document);

        $this->_prepareTmpDocument('timestamp', $tmpDocument, $path, $timestampModule);

        $signature = $this->createTimestampSignature($tmpDocument);

        try {
            $this->saveSignature($tmpDocument, $signature);

        } catch (Exception $e) {
            if ($e instanceof SetaPDF_Signer_Exception_ContentLength && $this->_allowSignatureContentLengthChange) {
                $this->setSignatureContentLength($e->getExpectedLength());
                return $this->_timestamp($mainWriter, $path);
            }

            throw $e;
        }

        return (string)$signature;
    }

    /**
     * Forwards the signature to a timestamp module and merges the result into the signature container.
     *
     * @param string $signature
     * @param SetaPDF_Signer_TmpDocument $tmpDocument
     * @return string
     * @throws SetaPDF_Signer_Exception
     */
    public function addTimeStamp(
        $signature,
        SetaPDF_Signer_TmpDocument $tmpDocument
    ) {
        if ($this->getTimestampModule() === null) {
            return $signature;
        }

        if (!$tmpDocument->matchesDocument($this->_document)) {
            throw new SetaPDF_Signer_Exception("Document doesn't match the temporary version.");
        }

        // Set this to false, because a timestamp request could cost money
        $this->setAllowSignatureContentLengthChange(false);

        $signature = SetaPDF_Signer_Asn1_Element::parse($signature);

        $signerInfos = SetaPDF_Signer_Asn1_Element::findByPath('1/0/4', $signature);
        if (($signerInfos->getIdent() & "\xA1") === "\xA1") {
            $signerInfos = SetaPDF_Signer_Asn1_Element::findByPath('1/0/5', $signature);
        }
        /** @var SetaPDF_Signer_Asn1_Element $signerInfo */
        $signerInfo = $signerInfos->getChild(0);

        /**
         * SignerInfo ::= SEQUENCE {
         *   version CMSVersion,
         *   sid SignerIdentifier,
         *   digestAlgorithm DigestAlgorithmIdentifier,
         *   signedAttrs [0] IMPLICIT SignedAttributes OPTIONAL,
         *   signatureAlgorithm SignatureAlgorithmIdentifier,
         *   signature SignatureValue,
         *   unsignedAttrs [1] IMPLICIT UnsignedAttributes OPTIONAL
         * }
         *
         * Info: OpenSslCms will e.g. not use the signedAttrs entry.
         */
        if ($signerInfo->getChild(3)->getIdent() === "\xA0") {
            $hash = $signerInfo->getChild(5)->getValue();
            $unsignedAttrs = $signerInfo->getChild(6) !== false ? $signerInfo->getChild(6) : null;
        } else {
            $hash = $signerInfo->getChild(4)->getValue();
            $unsignedAttrs = $signerInfo->getChild(5) !== false ? $signerInfo->getChild(5) : null;
        }

        $tsToken = $this->getTimestampModule()->createTimestamp($hash);

        $tsEntry = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED,
            $tsToken
        );

        if ($unsignedAttrs === null) {
            $unsignedAttrs = new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | "\x01",
                '',
                []
            );
            $signerInfo->addChild($unsignedAttrs);
        }

        $unsignedAttrs->addChild(
            new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED,
                '',
                [
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                        SetaPDF_Signer_Asn1_Oid::encode('1.2.840.113549.1.9.16.2.14')
                    ),
                    $tsEntry
                ]
            )
        );

        return (string)$signature;
    }

    /**
     * Ensures a valid signature field instance.
     *
     * @param SetaPDF_Core_Document $document The document instance
     * @param bool $returnFieldInstance If true the field instance will be returned, otherwise null will be returned.
     * @return null|SetaPDF_Signer_SignatureField
     * @throws SetaPDF_Signer_Exception
     */
    protected function _ensureSignatureField(SetaPDF_Core_Document $document, $returnFieldInstance = false)
    {
        $field = SetaPDF_Signer_SignatureField::get($document, $this->_fieldName, true);
        if ($field->getValue() !== null) {
            throw new SetaPDF_Signer_Exception(sprintf(
                'Signaturefield "%s" is already used.', $this->_fieldName
            ));
        }

        if ($returnFieldInstance) {
            return $field;
        }

        return null;
    }

    /**
     * Updates the AcroForm dictionary.
     *
     * @param SetaPDF_Core_Document $document The document instance
     */
    protected function _updateAcroForm(SetaPDF_Core_Document $document)
    {
        $acroForm = $document->getCatalog()->getAcroForm();

        $dict = $acroForm->getDictionary();
        if (!$dict->offsetExists('SigFlags')) {
            $dict->offsetSet('SigFlags', new SetaPDF_Core_Type_Numeric(0));
        }

        $sigFlags = $dict->getValue('SigFlags')->ensure();
        $sigFlags->setValue($sigFlags->getValue() | 3);

        $acroForm->addDefaultEntriesAndValues();
    }

    /**
     * Creates the signature dictionary.
     *
     * @param SetaPDF_Signer_SignatureField $field The signature field
     * @param null|SetaPDF_Signer_Signature_DictionaryInterface $module
     * @return SetaPDF_Core_Type_IndirectObject
     */
    protected function _createSignatureDictionary(SetaPDF_Signer_SignatureField $field, $module = null)
    {
        $offsetEnd = new SetaPDF_Core_Type_Callback(new SetaPDF_Core_Type_Token($this->_offsetEndPlaceholder));
        $offsetEnd->addCallback('writeTo', [$this, '_onBeforeOffsetEndWrite']);

        $this->_byteRange = new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Numeric(0),
            new SetaPDF_Core_Type_Numeric(0),
            new SetaPDF_Core_Type_Numeric(0),
            $offsetEnd
        ]);

        $contents = new SetaPDF_Core_Type_Callback(
            new SetaPDF_Core_Type_Token(str_repeat("\0", $this->getSignatureContentLength() + 1))
        );

        $dictionary = new SetaPDF_Core_Type_Dictionary([
            'Type' => new SetaPDF_Core_Type_Name('Sig'),
            'Filter' => new SetaPDF_Core_Type_Name('Adobe.PPKMS'), // poss. configurable
            'SubFilter' => new SetaPDF_Core_Type_Name('adbe.pkcs7.detached'), // poss. configurable
            'Contents' => $contents,
            'ByteRange' => $this->_byteRange,
        ]);

        $contents->addCallback('writeTo', [$this, '_onBeforeContentsWrite']);
        $contents->addCallback('writeTo', [$this, '_onAfterContentsWrite'], false);

        foreach ($this->_signatureProperties AS $key => $value) {
            switch ($key) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case self::PROP_TIME_OF_SIGNING:
                    $value = $this->getTimeOfSigning()->getValue();

                default:
                    if ($value === null) {
                        continue 2;
                    }

                    if (!($value instanceof SetaPDF_Core_Type_AbstractType)) {
                        $value = new SetaPDF_Core_Type_String(SetaPDF_Core_Encoding::toPdfString($value));
                    }

                    $dictionary[$key] = $value;
                    break;
            }
        }

        // Add certification level information
        $this->_addDocMp($dictionary);

        if ($module instanceof SetaPDF_Signer_Signature_DictionaryInterface) {
            $module->updateSignatureDictionary($dictionary);
        }

        $signatureObject = $this->_tempDocument->createNewObject($dictionary);

        $dictionary = $field->getDictionary();
        $dictionary->offsetSet('V', $signatureObject);

        return $signatureObject;
    }

    /**
     * Creates the signature dictionary.
     *
     * @param SetaPDF_Signer_SignatureField $field The signature field
     * @param null|SetaPDF_Signer_Signature_DictionaryInterface $module
     */
    protected function _createTimestampSignatureDictionary(SetaPDF_Signer_SignatureField $field, $module = null)
    {
        $offsetEnd = new SetaPDF_Core_Type_Callback(new SetaPDF_Core_Type_Token($this->_offsetEndPlaceholder));
        $offsetEnd->addCallback('writeTo', [$this, '_onBeforeOffsetEndWrite']);

        $this->_byteRange = new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Numeric(0),
            new SetaPDF_Core_Type_Numeric(0),
            new SetaPDF_Core_Type_Numeric(0),
            $offsetEnd
        ]);

        $contents = new SetaPDF_Core_Type_Callback(
            new SetaPDF_Core_Type_Token(str_repeat("\0", $this->getSignatureContentLength() + 1))
        );
        $dictionary = new SetaPDF_Core_Type_Dictionary([
            'Type' => new SetaPDF_Core_Type_Name('DocTimeStamp'),
            'Filter' => new SetaPDF_Core_Type_Name('Adobe.PPKLite'),
            'SubFilter' => new SetaPDF_Core_Type_Name('ETSI.RFC3161'),
            'Contents' => $contents,
            'ByteRange' => $this->_byteRange,
            'V' => new SetaPDF_Core_Type_Numeric(0)
        ]);

        $contents->addCallback('writeTo', [$this, '_onBeforeContentsWrite']);
        $contents->addCallback('writeTo', [$this, '_onAfterContentsWrite'], false);

        foreach ($this->_signatureProperties AS $key => $value) {
            switch ($key) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case self::PROP_TIME_OF_SIGNING:
                    continue 2;

                default:
                    if ($value === null) {
                        continue 2;
                    }

                    if (!($value instanceof SetaPDF_Core_Type_AbstractType)) {
                        $value = new SetaPDF_Core_Type_String(SetaPDF_Core_Encoding::toPdfString($value));
                    }

                    $dictionary[$key] = $value;
                    break;
            }
        }

        if ($module instanceof SetaPDF_Signer_Signature_DictionaryInterface) {
            $module->updateSignatureDictionary($dictionary);
        }

        $signatureObject = $this->_tempDocument->createNewObject($dictionary);

        $dictionary = $field->getDictionary();
        $dictionary->offsetSet('V', $signatureObject);
    }

    /**
     * Adds the document modification detection and prevention data.
     *
     * @param SetaPDF_Core_Type_Dictionary $dictionary
     */
    protected function _addDocMp(SetaPDF_Core_Type_Dictionary $dictionary)
    {
        if ($this->getCertificationLevel() === self::CERTIFICATION_LEVEL_NONE) {
            return;
        }

        $dictionary->offsetSet('Reference', new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Dictionary([
                'TransformMethod' => new SetaPDF_Core_Type_Name('DocMDP'),
                'Type' => new SetaPDF_Core_Type_Name('SigRef'),
                'TransformParams' => new SetaPDF_Core_Type_Dictionary([
                    'P' => new SetaPDF_Core_Type_Numeric($this->getCertificationLevel()),
                    'V' => new SetaPDF_Core_Type_Name('1.2'),
                    'Type' => new SetaPDF_Core_Type_Name('TransformParams'),
                ])
            ])
        ]));
    }

    /**
     * Callback which is called just before the Content value is written to the writer instance.
     *
     * @see _createSignatureDictionary()
     * @internal
     */
    public function _onBeforeContentsWrite()
    {
        $this->_byteRange[1]->setValue($this->_tempWriter->getPos());
    }

    /**
     * Callback which is called just after the Content value is written to the writer instance.
     *
     * @see _createSignatureDictionary()
     * @internal
     */
    public function _onAfterContentsWrite()
    {
        $this->_byteRange[2]->setValue($this->_tempWriter->getPos());
    }

    /**
     * Callback which is called before the last ByteRange value is written to the writer instance.
     *
     * @see _createSignatureDictionary()
     * @internal
     */
    public function _onBeforeOffsetEndWrite()
    {
        $this->_placeHolderByteOffset = $this->_tempWriter->getPos();
    }
}