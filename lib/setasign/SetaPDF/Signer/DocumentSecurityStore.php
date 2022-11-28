<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: DocumentSecurityStore.php 1444 2020-03-17 20:17:45Z jan.slabon $
 */

use SetaPDF_Signer_Asn1_Signed as Signed;
use SetaPDF_Signer_Ocsp_Response as OcspResponse;
use SetaPDF_Signer_X509_Certificate as Certificate;
use SetaPDF_Signer_X509_Crl as Crl;
use SetaPDF_Signer_X509_Format as Format;

/**
 * Class representing a "Document Security Store" in a PDF document.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_DocumentSecurityStore
{
    /**
     * The document instance
     *
     * @var SetaPDF_Core_Document
     */
    protected $_document;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document $document
     */
    public function __construct(SetaPDF_Core_Document $document)
    {
        $this->_document = $document;
    }

    /**
     * Release cycled references.
     */
    public function cleanUp()
    {
        $this->_document = null;
    }

    /**
     * Get and/or creates the DSS dictionary.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary($create = false)
    {
        $catalogDict = $this->_document->getCatalog()->getDictionary($create);
        if ($catalogDict === null) {
            return null;
        }

        if (!$catalogDict->offsetExists('DSS')) {
            if ($create === false) {
                return null;
            }

            $dss = new SetaPDF_Core_Type_Dictionary([
                'Type' => new SetaPDF_Core_Type_Name('DSS', true)
            ]);

            $catalogDict->offsetSet('DSS', $this->_document->createNewObject($dss));
        }

        return $catalogDict->getValue('DSS')->ensure();
    }

    /**
     * Adds a stream to the DSS data.
     *
     * @param string $type The type/key to which the data should be added.
     * @param string $data
     * @return SetaPDF_Core_Type_IndirectObject
     */
    protected function _addStream($type, $data)
    {
        $dictionary = $this->getDictionary(true);
        if (!$dictionary->offsetExists($type)) {
            $dictionary->offsetSet($type, $this->_document->createNewObject(new SetaPDF_Core_Type_Array()));
        }

        $array = $dictionary->offsetGet($type)->ensure();

        foreach ($array AS $_object) {
            $object = $_object->ensure();
            if ($object->getStream() == $data) {
                return $_object;
            }
        }

        $stream = new SetaPDF_Core_Type_Stream(new SetaPDF_Core_Type_Dictionary([
            'Filter' => new SetaPDF_Core_Type_Name('FlateDecode', true)
        ]));

        $stream->setStream($data);
        $object = $this->_document->createNewObject($stream);
        $array[] = $object;

        return $object;
    }

    /**
     * Get a stream by its type from the DSS dictionary.
     *
     * @param string $type
     * @return array
     */
    protected function _getStreams($type)
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null || !$dictionary->offsetExists($type)) {
            return [];
        }

        $array = $dictionary->offsetGet($type)->ensure();
        $result = [];

        foreach ($array AS $object) {
            $stream = $object->ensure();
            $result[] = $stream->getStream();
        }

        return $result;
    }

    /**
     * Add a certificate to the Certs entry in the DSS dictionary.
     *
     * @param string|Certificate $certificate
     * @return SetaPDF_Core_Type_IndirectObject
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function addCertificate($certificate)
    {
        if (!$certificate instanceof Certificate) {
            $certificate = new Certificate($certificate);
        }

        $certificate = $certificate->get(Format::DER);

        return $this->_addStream('Certs', $certificate);
    }

    /**
     * Add certificates to the Certs entry in the DSS dictionary.
     *
     * @param string[]|Certificate[] $certificates
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function addCertificates(array $certificates)
    {
        foreach ($certificates AS $certificate) {
            $this->addCertificate($certificate);
        }
    }

    /**
     * Get all certificates from the Certs entry in the DSS dictionary.
     *
     * @return string[]
     */
    public function getCertificates()
    {
        return $this->_getStreams('Certs');
    }

    /**
     * Add a OCSP response to the OCSPs entry in the DSS dictionary.
     *
     * @param string|OcspResponse $ocspResponse
     * @return SetaPDF_Core_Type_IndirectObject
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function addOCSP($ocspResponse)
    {
        if (!$ocspResponse instanceof OcspResponse) {
            $ocspResponse = new OcspResponse($ocspResponse);
        }

        $ocspResponse = $ocspResponse->get();

        return $this->_addStream('OCSPs', $ocspResponse);
    }

    /**
     * Add OCSP responses to the OCSPs entry in the DSS dictionary.
     *
     * @param string[]|OcspResponse $ocsps
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function addOCSPs(array $ocsps)
    {
        foreach ($ocsps AS $ocsp) {
            $this->addOCSP($ocsp);
        }
    }

    /**
     * Get all OCSP responses from the OCSPs entry in the DSS dictionary.
     *
     * @return string[]
     */
    public function getOCSPs()
    {
        return $this->_getStreams('OCSPs');
    }

    /**
     * Add a CRL to the CRLs entry in the DSS dictionary.
     *
     * @param string|Crl $crl
     * @return SetaPDF_Core_Type_IndirectObject
     */
    public function addCRL($crl)
    {
        if (!$crl instanceof Crl) {
            $crl = new Crl($crl);
        }

        $crl = $crl->get(Format::DER);

        return $this->_addStream('CRLs', $crl);
    }

    /**
     * Add CRLs to the CRLs entry in the DSS dictionary.
     *
     * @param string[]|Crl[] $crls
     */
    public function addCRLs(array $crls)
    {
        foreach ($crls AS $crl) {
            $this->addCRL($crl);
        }
    }

    /**
     * Get all CRLs the OCSPs entry in the DSS dictionary.
     *
     * @return string[]
     */
    public function getCRLs()
    {
        return $this->_getStreams('CRLs');
    }

    /**
     * Add validation related information to the VRI dictionary of the DSS dictionary.
     *
     * @param string $key The sha1 digest of the signature.
     * @param array $crls An array of strings, {@link SetaPDF_Signer_X509_Crl} instances or
     *                    {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the CRLs.
     * @param array $ocsps An array of strings, {@link SetaPDF_Signer_Ocsp_Response} instances or
     *                     {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the OCSPs.
     * @param array $certs An array of strings, SetaPDF_Signer_X509_Certificate instances or
     *                     {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the certs.
     * @param null|SetaPDF_Core_DataStructure_Date|DateTime|string $timestamp
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function addValidationRelatedInfo(
        $key,
        array $crls = [],
        array $ocsps = [],
        array $certs = [],
        $timestamp = null
    ) {
        $dictionary = $this->getDictionary(true);
        if (!$dictionary->offsetExists('VRI')) {
            $vri = new SetaPDF_Core_Type_Dictionary();
            $object = $this->_document->createNewObject($vri);
            $dictionary->offsetSet('VRI', $object);
        }

        $vri = $dictionary->offsetGet('VRI')->ensure();
        if (!$vri->offsetExists($key)) {
            $vri->offsetSet($key, $this->_document->createNewObject(new SetaPDF_Core_Type_Dictionary([
                'Type' => new SetaPDF_Core_Type_Name('VRI', true)
            ])));
        }

        $vriDictionary = $vri->offsetGet($key)->ensure();

        if (count($certs) > 0) {
            $certsArray = new SetaPDF_Core_Type_Array();
            $vriDictionary->offsetSet('Cert', $this->_document->createNewObject($certsArray));
            foreach ($certs AS $cert) {
                if (!$cert instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
                    $cert = $this->addCertificate($cert);
                }

                if ($certsArray->indexOf($cert) === -1) {
                    $certsArray[] = $cert;
                }
            }
        }

        if (count($crls) > 0) {
            $crlsArray = new SetaPDF_Core_Type_Array();
            $vriDictionary->offsetSet('CRL', $this->_document->createNewObject($crlsArray));
            foreach ($crls AS $crl) {
                if (!$crl instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
                    $crl = $this->addCRL($crl);
                }

                if ($crlsArray->indexOf($crl) === -1) {
                    $crlsArray[] = $crl;
                }
            }
        }

        if (count($ocsps) > 0) {
            $ocspArray =  new SetaPDF_Core_Type_Array();
            $vriDictionary->offsetSet('OCSP', $this->_document->createNewObject($ocspArray));

            foreach ($ocsps AS $ocsp) {
                if (!$ocsp instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
                    $ocsp = $this->addOCSP($ocsp);
                }

                if ($ocspArray->indexOf($ocsp) === -1) {
                    $ocspArray[] = $ocsp;
                }
            }
        }

        if (null !== $timestamp) {
            if ($timestamp instanceof SetaPDF_Core_DataStructure_Date) {
                $vriDictionary->offsetSet('TU', $timestamp->getValue());
            } elseif ($timestamp instanceof DateTime) {
                $timestamp = new SetaPDF_Core_DataStructure_Date($timestamp);
                $vriDictionary->offsetSet('TU', $timestamp->getValue());

            } else {
                $ts = new SetaPDF_Core_Type_Stream(new SetaPDF_Core_Type_Dictionary([
                    'Filter' => new SetaPDF_Core_Type_Name('FlateDecode', true)
                ]));
                $ts->setStream($timestamp);
                $object = $this->_document->createNewObject($ts);
                $vriDictionary->offsetSet('TS', $object);
            }
        }

        $extensions = $this->_document->getCatalog()->getExtensions();
        $extensions->setExtension('ESIC', '1.7', 1);
    }

    /**
     * Add validation related information to the VRI dictionary of the DSS dictionary by a specific signature field.
     *
     * @param string $fieldName The signature field name.
     * @param array $crls An array of strings or {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the CRLs.
     * @param array $ocsps An array of strings or {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the OCSPs.
     * @param array $certs An array of strings or {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the certs.
     * @param null|SetaPDF_Core_DataStructure_Date|DateTime|string $timestamp
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function addValidationRelatedInfoByFieldName(
        $fieldName,
        array $crls = [],
        array $ocsps = [],
        array $certs = [],
        $timestamp = null
    ) {
        $this->addValidationRelatedInfo($this->getVriNameByFieldName($fieldName), $crls, $ocsps, $certs, $timestamp);
    }

    /**
     * @param string $fieldName The signature field name.
     * @param array $crls An array of strings or {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the CRLs.
     * @param array $ocsps An array of strings or {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the OCSPs.
     * @param array $certs An array of strings or {@link SetaPDF_Core_Type_IndirectObjectInterface} to streams of the certs.
     * @param null|SetaPDF_Core_DataStructure_Date|DateTime|string $timestamp
     * @throws SetaPDF_Signer_Asn1_Exception
     * @see addValidationRelatedInfoByFieldName()
     * @deprecated
     */
    public function addValidationRelatedInfoByField(
        $fieldName,
        array $crls = [],
        array $ocsps = [],
        array $certs = [],
        $timestamp = null
    ) {
        $this->addValidationRelatedInfoByFieldName($fieldName, $crls, $ocsps, $certs, $timestamp);
    }

    /**
     * Get validation related information.
     *
     * @param null|string $vriKey The sha1 digest of the signature to get a specific information. Otherwise all found
     *                            validation data is returned.
     * @return array|array[]|bool
     */
    public function getValidationRelatedInfo($vriKey = null)
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null || !$dictionary->offsetExists('VRI')) {
            return false;
        }

        /**
         * @var SetaPDF_Core_Type_Dictionary $vri
         */
        $vri = $dictionary->getValue('VRI')->ensure();

        if ($vriKey === null) {
            $result = [];
            foreach ($vri->getKeys() AS $key) {
                $result[$key] = $this->getValidationRelatedInfo($key);
            }
            return $result;
        }

        if (!$vri->offsetExists($vriKey)) {
            return false;
        }

        $result = [
            'certs' => [],
            'crls' => [],
            'ocsps' => [],
            'timestamp' => null
        ];

        /**
         * @var $vriDictionary SetaPDF_Core_Type_Dictionary
         */
        $vriDictionary = $vri->getValue($vriKey)->ensure();

        foreach (['certs' => 'Cert', 'crls' => 'CRL', 'ocsps' => 'OCSP'] AS $resKey => $key) {
            if ($vriDictionary->offsetExists($key)) {
                foreach ($vriDictionary->getValue($key)->ensure() AS $cert) {
                    $result[$resKey][] = $cert->ensure()->getStream();
                }
            }
        }

        if ($vriDictionary->offsetExists('TU')) {
            $tu = $vriDictionary->getValue('TU')->ensure()->getValue();
            $tu = SetaPDF_Core_DataStructure_Date::stringToDateTime($tu);
            $result['timestamp'] = $tu;

        } elseif ($vriDictionary->offsetExists('TS')) {
            $ts = $vriDictionary->getValue('TS')->ensure()->getStream();
            $result['timestamp'] = $ts;
        }

        return $result;
    }

    /**
     * Get validation related information by a signature field name.
     *
     * @param string $fieldName The signature field name.
     * @return array|array[]|bool
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getValidationRelatedInfoByFieldName($fieldName)
    {
        return $this->getValidationRelatedInfo($this->getVriNameByFieldName($fieldName));
    }

    /**
     * @param string $fieldName The signature field name.
     * @return array|array[]|bool
     * @throws SetaPDF_Signer_Asn1_Exception
     * @deprecated
     * @see getValidationRelatedInfoByFieldName()
     */
    public function getValidationRelatedInfoByField($fieldName)
    {
        return $this->getValidationRelatedInfoByFieldName($fieldName);
    }

    /**
     * @param string $fieldName
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     * @see getVriNameByFieldName()
     * @deprecated
     */
    public function getSignatureDigest($fieldName)
    {
        return $this->getVriNameByFieldName($fieldName);
    }

    /**
     * Get the signature digest of a signature field, which can be used as an index in the VRI dictionary.
     *
     * <quote>
     * For a document signature the bytes that are hashed are those of the signature's DER-encoded PKCS#7 (and
     * its derivatives) binary data object (base-16 decoded byte string in the Contents entry in the signature
     * dictionary).
     *
     * For the signatures of the CRL and OCSP response, it is the respective signature object
     * represented as a BER-encoded OCTET STRING encoded with primitive encoding.
     *
     * For a Time-stamp's signature it is the bytes of the Time-stamp itself since the Time-stamp token is
     * a signed data object.
     * </quote>
     *
     * @param string $fieldName
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getVriNameByFieldName($fieldName)
    {
        $field = SetaPDF_Signer_SignatureField::get($this->_document, $fieldName, false);
        if ($field === false) {
            throw new InvalidArgumentException('Unknown signature field ("' . $fieldName . '")');
        }

        /**
         * @var $value SetaPDF_Core_Type_Dictionary
         */
        $value = $field->getValue()->ensure();
        $contents = $value->getValue('Contents')->ensure()->getValue();

        // Remove null bytes values in case of a time stamp signature
        if ($value->getValue('SubFilter')->getValue() === 'ETSI.RFC3161') {
            $asn1 = SetaPDF_Signer_Asn1_Element::parse($contents);
            $contents = (string)$asn1;
        }

        return strtoupper(sha1($contents));
    }

    /**
     * Get the signature digest of a CRL or OCSP response which can be used as an index in the VRI dictionary.
     *
     * @param Signed $object
     * @return string
     */
    public function getVriName(Signed $object)
    {
        /* [...]the bytes that are hashed[...] For the signatures of the CRL and OCSP response, it is the
         * respective signature object represented as a BER-encoded OCTET STRING encoded with primitive encoding.
         */
        $signatureValue = $object->getSignatureValue(false);
        $octetStringValue = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::OCTET_STRING,
            $signatureValue
        );

        return strtoupper(sha1($octetStringValue));
    }
}