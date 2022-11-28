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

use SetaPDF_Signer_Pem as Pem;
use SetaPDF_Signer_X509_Certificate as Certificate;
use SetaPDF_Signer_X509_Collection as Collection;

/**
 * Class to handle CertsOnly CMS containers.
 *
 * @see https://tools.ietf.org/html/rfc2797#section-2.2
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Cms_CertsOnly
{
    /**
     * The message object.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_message;

    /**
     * The ASN.1 object referencing the Certificates.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_certificates;

    /**
     * The constructor
     *
     * @param string $message PEM or DER encoded
     */
    public function __construct($message)
    {
        if (
            strpos($message, '-----BEGIN PKCS7 CERTIFICATE CHAIN-----') === 0 ||
            strpos($message, '-----BEGIN PKCS7-----') === 0 ||
            strpos($message, '-----BEGIN CERTIFICATE CHAIN-----') === 0
        ) {
            $message = Pem::decode($message);
        }

        try {
            $message = SetaPDF_Signer_Asn1_Element::parse($message);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid data structure for a "certs-only" CMS container.', null, $e);
        }

        if ($message->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new InvalidArgumentException('Invalid data type in "certs-only" data structure (expected SEQUENCE).');
        }

        $contentType = $message->getChild(0);
        if (
            !$contentType ||
            $contentType->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER ||
            SetaPDF_Signer_Asn1_Oid::decode($contentType->getValue()) !== '1.2.840.113549.1.7.2'
        ) {
            throw new InvalidArgumentException('Invalid data type or content type in "certs-only" data structure.');
        }

        $signedData = $message->getChild(1);
        if (
            !$signedData ||
            $signedData->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC)
        ) {
            throw new InvalidArgumentException('Invalid data type of signedData in "certs-only" data structure.');
        }

        $signedData = $signedData->getChild(0);
        if (!$signedData ||
            $signedData->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new InvalidArgumentException('Invalid data type in "certs-only" data structure (expected SEQUENCE).');
        }

        if ($signedData->getChildCount() < 3) {
            throw new InvalidArgumentException('Invalid data in "certs-only" data structure.');
        }

        $encapContentInfo = $signedData->getChild(2);
        if (!$encapContentInfo ||
            $encapContentInfo->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new InvalidArgumentException('Invalid data type in "certs-only" data structure (expected SEQUENCE).');
        }

        $eContentType = $encapContentInfo->getChild(0);
        if (
            !$eContentType ||
            $eContentType->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER ||
            SetaPDF_Signer_Asn1_Oid::decode($eContentType->getValue()) !== '1.2.840.113549.1.7.1'
        ) {
            throw new InvalidArgumentException('Invalid data type or content type in "certs-only" data structure.');
        }

        $certificates = $signedData->getChild(3);
        if (
            $certificates && (
                $certificates->getIdent() !==
                (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC)
            )
        ) {
            throw new InvalidArgumentException('Invalid data type of certificates in "certs-only" data structure.');
        }

        $this->_certificates = $certificates ?: null;
        $this->_message = $message;
    }

    /**
     * Get all certificates from this container.
     *
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getAll()
    {
        $result = new Collection();
        if ($this->_certificates !== null) {
            foreach ($this->_certificates->getChildren() as $certificate) {
                $result->add(new Certificate($certificate));
            }
        }

        return $result;
    }
}