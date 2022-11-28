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

use SetaPDF_Signer_Asn1_DistinguishedName as DistinguishedName;
use SetaPDF_Signer_Asn1_Signed as Signed;
use SetaPDF_Signer_Pem as Pem;
use SetaPDF_Signer_X509_Certificate as Certificate;
use SetaPDF_Signer_X509_Collection as Collection;

/**
 * Class representing a CMS signed data container.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Cms_SignedData extends Signed
{
    /**
     * The message object.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_message;

    /**
     * The detached signed data.
     *
     * @var string|SetaPDF_Core_Reader_FilePath
     */
    protected $_detachedSignedData;

    /**
     * All signed attributes. Indexed by their OID.
     *
     * @var SetaPDF_Signer_Asn1_Element[]
     */
    protected $_signedAttributes;

    /**
     * All unsigned attributes. Indexed by their OID.
     *
     * @var SetaPDF_Signer_Asn1_Element[]
     */
    protected $_unsignedAttributes;

    /**
     * The constructor.
     *
     * @param string $message PEM or DER encoded message.
     */
    public function __construct($message)
    {
        if (
            strpos($message, '-----BEGIN PKCS7-----') === 0 ||
            strpos($message, '-----BEGIN CMS-----') === 0
        ) {
            $message = Pem::decode($message);
        }

        try {
            $message = SetaPDF_Signer_Asn1_Element::parse($message);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid data structure for a signedData CMS container.', null, $e);
        }

        if ($message->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new InvalidArgumentException('Invalid data type in signedData data structure (expected SEQUENCE).');
        }

        $contentType = $message->getChild(0);
        if (
            !$contentType ||
            $contentType->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER ||
            SetaPDF_Signer_Asn1_Oid::decode($contentType->getValue()) !== '1.2.840.113549.1.7.2'
        ) {
            throw new InvalidArgumentException('Invalid data type or content type in "signedData" data structure.');
        }

        $signedData = $message->getChild(1);
        if (
            !$signedData ||
            $signedData->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC)
        ) {
            throw new InvalidArgumentException('Invalid data type in signedData data structure.');
        }

        $signedData = $signedData->getChild(0);
        if (!$signedData ||
            $signedData->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new InvalidArgumentException('Invalid data type in signedData data structure (expected SEQUENCE).');
        }

        if ($signedData->getChildCount() < 3) {
            throw new InvalidArgumentException('Invalid data in signedData data structure.');
        }

        $encapContentInfo = $signedData->getChild(2);
        if (!$encapContentInfo ||
            $encapContentInfo->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new InvalidArgumentException('Invalid data type in signedData data structure (expected SEQUENCE).');
        }

        $eContentType = $encapContentInfo->getChild(0);
        if (
            !$eContentType ||
            $eContentType->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER ||
            SetaPDF_Signer_Asn1_Oid::decode($eContentType->getValue()) !== '1.2.840.113549.1.7.1'
        ) {
            throw new InvalidArgumentException('Invalid data type or content type in signedData data structure.');
        }

        $this->_message = $message;
    }

    /**
     * Get the ASN.1 instance of the SignedData element.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    public function getAsn1()
    {
        return $this->_message;
    }

    /**
     * Get SignedData element from the ASN.1 structure.
     *
     * @return bool|SetaPDF_Signer_Asn1_Element
     */
    protected function _getSignedData()
    {
        return $this->_message->getChild(1)->getChild(0);
    }

    /**
     * Get all certificates embedded in the SignedData structure.
     *
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getCertificates()
    {
        $signedData = $this->_getSignedData();

        $result = new Collection();

        // CertificateSet ::= SET OF CertificateChoices
        // CertificateChoices -> https://tools.ietf.org/html/rfc5652#section-10.2.2
        $certificates = $signedData->getChild(3);
        if (!$certificates || $certificates->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC)) {
            return $result;
        }

        foreach ($certificates->getChildren() as $certificate) {
            // Certificate
            if ($certificate->getIdent() ===
                (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE))
            {
                $result->add(new Certificate($certificate));
            }

            // TODO: Support other certificate types?
        }

        return $result;
    }

    /**
     * Get the SignerInfo element from the ASN.1 structure.
     *
     * @return bool|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    protected function _getSignerInfo()
    {
        $signedData = $this->_getSignedData();

        $offset = 3;
        $element = $signedData->getChild($offset);
        while ($element && $element->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SET)
        ) {
            $element = $signedData->getChild(++$offset);
        }
        if (!$element) {
            throw new SetaPDF_Signer_Exception('SignerInfo cannot be found in SignedData structure.');
        }

        return $element->getChild(0);
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignatureAlgorithm()
    {
        $signerInfo = $this->_getSignerInfo();
        $offset = 3;
        $element = $signerInfo->getChild($offset);
        while ($element && $element->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            $element = $signerInfo->getChild(++$offset);
        }
        if (!$element) {
            throw new SetaPDF_Signer_Exception('Cannot find signature algorithm in SignedData structure.');
        }

        return [
            SetaPDF_Signer_Asn1_Oid::decode($element->getChild(0)->getValue()),
            $element->getChild(1)
        ];
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Exception
     */
    public function getDigestAlgorithm()
    {
        $signerInfo = $this->_getSignerInfo();
        $offset = 2;
        $element = $signerInfo->getChild($offset);
        if (!$element || $element->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Cannot find digest algorithm in signed data structure.');
        }

        return [
            SetaPDF_Signer_Asn1_Oid::decode($element->getChild(0)->getValue()),
            $element->getChild(1)
        ];
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignatureValue($hex = true)
    {
        $signerInfo = $this->_getSignerInfo();
        $offset = 3;
        $element = $signerInfo->getChild($offset);
        while ($element && $element->getIdent() !== SetaPDF_Signer_Asn1_Element::OCTET_STRING) {
            $element = $signerInfo->getChild(++$offset);
        }
        if (!$element) {
            throw new SetaPDF_Signer_Exception('Cannot find signature value in SignedData structure.');
        }

        $signatureValue = $element->getValue();

        return $hex
            ? SetaPDF_Core_Type_HexString::str2hex($signatureValue)
            : $signatureValue;
    }

    /**
     * Get the digest algorithms and parameters.
     *
     * @return array An array of arrays where the first value holds the OID of the algorithm. The second value is the
     *               ASN.1 structure of the parameters.
     * @throws SetaPDF_Signer_Exception
     */
    public function getDigestAlgorithms()
    {
        $signedData = $this->_getSignedData();
        $digestAlgorithms = $signedData->getChild(1);
        foreach ($digestAlgorithms->getChildren() AS $algorithm) {
            $algorithmOid = SetaPDF_Signer_Asn1_Oid::decode($algorithm->getChild(0)->getValue());
            $digest = SetaPDF_Signer_Digest::getByOid($algorithmOid);
            if ($digest === false) {
                throw new SetaPDF_Signer_Exception('Unknown or invalid digest algorithm OID: ' . $algorithmOid);
            }
            $digests[] = [
                $digest,
                $algorithm->getChild(1)
            ];
        }

        return $digests;
    }

    /**
     * Get all signed attributes.
     *
     * @return array|SetaPDF_Signer_Asn1_Element[]
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignedAttributes()
    {
        if (isset($this->_signedAttributes)) {
            return $this->_signedAttributes;
        }

        $this->_signedAttributes = [];
        $signerInfo = $this->_getSignerInfo();

        // Check for signed attributes
        $_signedAttributes = $signerInfo->getChild(3);
        if ($_signedAttributes->getIdent() === "\xA0") { // [0] IMPLICIT
            foreach ($_signedAttributes->getChildren() AS $attribute) {
                $attrType = $attribute->getChild(0)->getValue();
                $attrTypeOid = SetaPDF_Signer_Asn1_Oid::decode($attrType);
                $this->_signedAttributes[$attrTypeOid] = $attribute->getChild(1);
            }
        }

        return $this->_signedAttributes;
    }

    /**
     * Get a signed attribute by its OID.
     *
     * @param string $oid
     * @return bool|mixed|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignedAttribute($oid)
    {
        $signedAttributes = $this->getSignedAttributes();
        if (!isset($signedAttributes[$oid])) {
            return false;
        }

        return $signedAttributes[$oid];
    }

    /**
     * Get all unsigned attributes.
     *
     * @return array|SetaPDF_Signer_Asn1_Element[]
     * @throws SetaPDF_Signer_Exception
     */
    public function getUnsignedAttributes()
    {
        if (isset($this->_unsignedAttributes)) {
            return $this->_unsignedAttributes;
        }

        $this->_unsignedAttributes = [];
        $signerInfo = $this->_getSignerInfo();

        // Check for signed attributes
        $_unsignedAttributes = $signerInfo->getChild($signerInfo->getChildCount() - 1);
        if ($_unsignedAttributes->getIdent() === "\xA1") { // [1] IMPLICIT
            foreach ($_unsignedAttributes->getChildren() AS $attribute) {
                $attrType = $attribute->getChild(0)->getValue();
                $attrTypeOid = SetaPDF_Signer_Asn1_Oid::decode($attrType);
                $this->_unsignedAttributes[$attrTypeOid] = $attribute->getChild(1);
            }
        }

        return $this->_unsignedAttributes;
    }

    /**
     * Get an unsigned attribute by its OID.
     *
     * @param string $oid
     * @return bool|mixed|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    public function getUnsignedAttribute($oid)
    {
        $unsignedAttributes = $this->getUnsignedAttributes();
        if (!isset($unsignedAttributes[$oid])) {
            return false;
        }

        return $unsignedAttributes[$oid];
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignedData()
    {
        $signerInfo = $this->_getSignerInfo();

        // Check for signed attributes
        $signedAttributes = $this->getSignedAttributes();
        if (count($signedAttributes)) {
            // check for mandatory attributes
            if (!isset($signedAttributes['1.2.840.113549.1.9.3'])) { // content-type
                throw new SetaPDF_Signer_Exception('Mandatory attribute "content-type" is missing.');
            }

            if (!isset($signedAttributes['1.2.840.113549.1.9.4'])) { // message-digest
                throw new SetaPDF_Signer_Exception('Mandatory attribute "message-digest" is missing.');
            }

            $data = $signerInfo->getChild(3)->__toString();
            $data[0] = SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED;
        } else {
            if ($this->_detachedSignedData === null) {
                throw new SetaPDF_Signer_Exception(
                    'Unable to get signed attributes from signedData data structure and no detached signature data passed before.'
                );
            }

            return $this->_detachedSignedData;
        }

        return $data;
    }

    /**
     * Get issuer and serial number of the signee.
     *
     * @return array|bool
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getIssuerAndSerialNumber()
    {
        $signerInfo = $this->_getSignerInfo();
        $sid = $signerInfo->getChild(1);
        /* SignerIdentifier ::= CHOICE {
         *   issuerAndSerialNumber IssuerAndSerialNumber,
         *   subjectKeyIdentifier [0] SubjectKeyIdentifier }
         *
         * IssuerAndSerialNumber ::= SEQUENCE {
         *   issuer Name,
         *   serialNumber CertificateSerialNumber }
         *
         * SubjectKeyIdentifier ::= OCTET STRING
         */
        if ($sid->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            return false;
        }

        return [
            'issuer' => DistinguishedName::getAsString($sid->getChild(0)),
            'serialNumber' => SetaPDF_Core_Type_HexString::str2hex($sid->getChild(1)->getValue())
        ];
    }

    /**
     * Get the subject key identifier.
     *
     * @return bool|string
     * @throws SetaPDF_Signer_Exception
     */
    public function getSubjectKeyIdentifier()
    {
        $signerInfo = $this->_getSignerInfo();
        $sid = $signerInfo->getChild(1);

        if ($sid->getIdent() !== SetaPDF_Signer_Asn1_Element::OCTET_STRING) {
            return false;
        }

        return $sid->getValue();
    }

    /**
     * Get the signing certificate.
     *
     * @param SetaPDF_Signer_X509_Collection $extraCertificates
     * @return bool|false|SetaPDF_Signer_X509_Certificate|SetaPDF_Signer_X509_CollectionInterface
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getSigningCertificate(Collection $extraCertificates = null)
    {
        $collection = new Collection();
        if ($extraCertificates !== null) {
            $collection->add($extraCertificates);
        }

        $collection->add($this->getCertificates());

        $subjectKeyIdentifier = $this->getSubjectKeyIdentifier();
        if ($subjectKeyIdentifier) {
            $signingCertificate = $collection->getBySubjectKeyIdentifier($subjectKeyIdentifier);
        } else {
            $issuerAndSerialNumber = $this->getIssuerAndSerialNumber();
            if ($issuerAndSerialNumber !== false) {
                $signingCertificate = $collection
                    ->findByIssuer($issuerAndSerialNumber['issuer'])
                    ->getBySerialNumber($issuerAndSerialNumber['serialNumber']);
            } else {
                $signingCertificate = false;
            }
        }

        if ($signingCertificate === false) {
            throw new SetaPDF_Signer_Exception('Signing certificate could not be found.');
        }

        return $signingCertificate;
    }

    /**
     * Set the detached signed data.
     *
     * @param string|SetaPDF_Core_Reader_FilePath $detachedSignedData
     */
    public function setDetachedSignedData($detachedSignedData)
    {
        $this->_detachedSignedData = $detachedSignedData;
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function verify(Certificate $signerCertificate)
    {
        $signedAttributes = $this->getSignedAttributes();

        // signed attributes are in use let's check if the hash matches
        if (count($signedAttributes) > 0) {
            if (!isset($this->_detachedSignedData)) {
                throw new BadMethodCallException('No detached singed data passed prior calling this method.');
            }

            $digestAlgorithms = $this->getDigestAlgorithms();
            $hashs = [];
            foreach ($digestAlgorithms as $digestAlgorithm) {
                if ($this->_detachedSignedData instanceof SetaPDF_Core_Reader_FilePath) {
                    $hashs[$digestAlgorithm[0]] = hash_file(
                        $digestAlgorithm[0], $this->_detachedSignedData->getPath(), true
                    );
                } else {
                    $hashs[$digestAlgorithm[0]] = hash($digestAlgorithm[0], $this->_detachedSignedData, true);
                }
            }

            $hash = $this->getSignedAttribute('1.2.840.113549.1.9.4')->getChild(0)->getValue();

            $digestAlgorithm = SetaPDF_Signer_Digest::getByOid($this->getDigestAlgorithm()[0]);
            if (isset($hashs[$digestAlgorithm]) && $hash !== $hashs[$digestAlgorithm]) {
                return false;
            }
        }

        return parent::verify($signerCertificate);
    }
}