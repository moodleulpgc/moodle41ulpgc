<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id$
 */

use SetaPDF_Signer_Cms_SignedData as SignedData;
use SetaPDF_Signer_Tsp_Token as TspToken;

/**
 * Class representing an integrity result of a signature by its field name.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_ValidationRelatedInfo_IntegrityResult
{
    /**
     * Status for a valid signature.
     *
     * @var int
     */
    const STATUS_VALID = 1;

    /**
     * Status for a field that is not signed.
     *
     * @var int
     */
    const STATUS_NOT_SIGNED = 0;

    /**
     * Status if the signature data resolved by the byte range has more data then a single signature value.
     *
     * @var int
     */
    const STATUS_INVALID_DATA_IN_BYTE_RANGE = -1;

    /**
     * Status if the signature data of the field is different to the one resolved by the byte range.
     *
     * @var int
     */
    const STATUS_DIFFERENT_SIGNED_DATA_VALUE = -2;

    /**
     * Status if a signature could not be validated.
     *
     * @var int
     */
    const STATUS_INVALID_SIGNATURE = -3;

    /**
     * Status if a timestamp signature could not be validated.
     *
     * @var int
     */
    const STATUS_INVALID_TIMESTAMP_SIGNATURE = -4;

    /**
     * Status if the message imprint of the timestamp signature doesn't match the data of the document.
     *
     * @var int
     */
    const STATUS_INVALID_TIMESTAMP_MESSAGE_IMPRINT = -5;

    /**
     * Status if the signature is a PAdES one but the required signed attribute (SigningCertificateV2) is missing.
     *
     * @var int
     */
    const STATUS_CADES_SIGNING_CERTIFICATE_V2_MISSING = -6;

    /**
     * Status if the signed attribut SigningCertificateV2 doesn't matched the signing certificate.
     *
     * @var int
     */
    const STATUS_CADES_INVALID_SIGNING_CERTIFICATE = -7;

    /**
     * Status if the eContent element is missing but required.
     *
     * @var int
     */
    const STATUS_MISSING_ENCAPSULATED_CONTENT = -8;

    /**
     * The signature field.
     *
     * @var SetaPDF_Signer_SignatureField
     */
    protected $_field;

    /**
     * The status.
     *
     * @var int
     */
    protected $_status;

    /**
     * The signed data object.
     *
     * @var SignedData|TspToken
     */
    protected $_signedData;

    /**
     * Wether the signature covers the whole document or only a revision.
     *
     * @var bool
     */
    protected $_isSignedRevision;

    /**
     * Create an integrity result instance.
     *
     * @param SetaPDF_Core_Document $document
     * @param string $fieldName The signature field name in UTF-8 encoding
     * @return SetaPDF_Signer_ValidationRelatedInfo_IntegrityResult
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Parser_Pdf_InvalidTokenException
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    static public function create(SetaPDF_Core_Document $document, $fieldName)
    {
        $result = new self;

        $field = SetaPDF_Signer_SignatureField::get($document, $fieldName, false);
        if ($field === false) {
            throw new InvalidArgumentException('Unknown signature field ("' . $fieldName . '")');
        }

        $result->_field = $field;

        /**
         * @var $value SetaPDF_Core_Type_Dictionary
         */
        $value = $field->getValue();
        if (!$value) {
            $result->_status = self::STATUS_NOT_SIGNED;
            return $result;
        }

        $message =  SetaPDF_Core_Type_Dictionary_Helper::getValue($value, 'Contents', null, true);
        $subFilter = SetaPDF_Core_Type_Dictionary_Helper::getValue($value, 'SubFilter', null, true);
        if ($subFilter === 'ETSI.RFC3161') {
            $result->_signedData = new TspToken(SetaPDF_Signer_Asn1_Element::parse($message));
        } else {
            $result->_signedData = new SignedData($message);
        }

        $byteRange = $value->getValue('ByteRange')->toPhp();

        $tmpWriter = new SetaPDF_Core_Writer_TempFile();
        $tmpWriter->start();
        $reader = $document->getParser()->getReader();
        $reader->reset($byteRange[0], $byteRange[1]);
        $tmpWriter->write($reader->readBytes($byteRange[1]));
        $reader->reset($byteRange[2], $byteRange[3]);
        $tmpWriter->write($reader->readBytes($byteRange[3]));
        $lastByte = $reader->readByte();
        $result->_isSignedRevision = !($lastByte === false);
        $tmpWriter->finish();

        // compare signature value with signature value of the field itself.
        $reader->reset($byteRange[1], $byteRange[2] - $byteRange[1]);
        $reader = new SetaPDF_Core_Reader_String($reader->readBytes($byteRange[2] - $byteRange[1]));
        $parser = new SetaPDF_Core_Parser_Pdf($reader);
        $signedValue = $parser->readValue();
        if ($parser->readValue() !== false) {
            $result->_status = self::STATUS_INVALID_DATA_IN_BYTE_RANGE;
            return $result;
        }

        if ($message !== $signedValue->getValue()) {
            $result->_status = self::STATUS_DIFFERENT_SIGNED_DATA_VALUE;
            return $result;
        }

        $detachedSignedData = new SetaPDF_Core_Reader_FilePath($tmpWriter->getPath());

        if ($subFilter === 'adbe.pkcs7.sha1') {
            // this is ensured in the constructor of the SignedData class
            $encapContentInfo = $result->_signedData->getAsn1()->getChild(1)->getChild(0)->getChild(2);
            $eContent = $encapContentInfo->getChild(1);
            if (!$eContent || !$eContent->getChild(0)) {
                $result->_status = self::STATUS_MISSING_ENCAPSULATED_CONTENT;
                return $result;
            }

            $content = $eContent->getChild(0)->getValue();
            if ($content !== hash_file('sha1', $detachedSignedData->getPath(), true)) {
                $result->_status = self::STATUS_INVALID_SIGNATURE;
                return $result;
            }

            $detachedSignedData = $content;
        }

        // Check for document timestamp signature
        $signingCertificate = $result->_signedData->getSigningCertificate();
        if ($result->_signedData instanceof TspToken) {
            if (!$result->_signedData->verify($signingCertificate)) {
                $result->_status = self::STATUS_INVALID_TIMESTAMP_SIGNATURE;
                return $result;
            }

            // Check if timestamp belongs to the signature it is part of
            if (!$result->_signedData->verifyMessageImprint($detachedSignedData)) {
                $result->_status = self::STATUS_INVALID_TIMESTAMP_MESSAGE_IMPRINT;
                return $result;
            }
        } else {
            $result->_signedData->setDetachedSignedData($detachedSignedData);
            if (!$result->_signedData->verify($signingCertificate)) {
                $result->_status = self::STATUS_INVALID_SIGNATURE;
                return $result;
            }
        }

        if ($subFilter === 'ETSI.CAdES.detached') {
            $signinCertificateV2 = $result->_signedData->getSignedAttribute('1.2.840.113549.1.9.16.2.47'); // id-aa-signingCertificateV2
            if ($signinCertificateV2 === false) {
                $result->_status = self::STATUS_CADES_SIGNING_CERTIFICATE_V2_MISSING;
                return $result;
            }

            $essCertIDv2 = SetaPDF_Signer_Asn1_Element::findByPath('0/0/0', $signinCertificateV2);
            if ($essCertIDv2->getChild(0)->getIdent() === SetaPDF_Signer_Asn1_Element::OCTET_STRING) {
                $hashAlgorithm = SetaPDF_Signer_Digest::SHA_256;
                $certHash = $essCertIDv2->getChild(0)->getValue();
            } else {
                $hashAlgorithmOid = SetaPDF_Signer_Asn1_Oid::decode($essCertIDv2->getChild(0)->getChild(0)->getValue());
                $hashAlgorithm = SetaPDF_Signer_Digest::getByOid($hashAlgorithmOid);
                $certHash = $essCertIDv2->getChild(1)->getValue();
            }

            // validate hashes of ESS-signing-certificate-v2 attribute.
            if ($signingCertificate->getDigest($hashAlgorithm, true) !== $certHash) {
                $result->_status = self::STATUS_CADES_INVALID_SIGNING_CERTIFICATE;
                return $result;
            }
        }

        $result->_status = self::STATUS_VALID;
        return $result;
    }

    /**
     * Checks whether the integrity of the signature of the given field is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->_status === self::STATUS_VALID;
    }

    /**
     * Get the status of the integrity of the signature field.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Get the signature field instance.
     *
     * @return SetaPDF_Signer_SignatureField
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Get access to the signed data object of the signature field.
     *
     * @return SignedData|TspToken
     */
    public function getSignedData()
    {
        if ($this->getStatus() === self::STATUS_NOT_SIGNED) {
            throw new BadMethodCallException('The field is not signed.');
        }

        return $this->_signedData;
    }

    /**
     * Checks whether the signature covers the whole document or only a revision.
     *
     * @return bool
     */
    public function isSignedRevision()
    {
        if ($this->getStatus() === self::STATUS_NOT_SIGNED) {
            throw new BadMethodCallException('The field is not signed.');
        }

        return $this->_isSignedRevision;
    }
}