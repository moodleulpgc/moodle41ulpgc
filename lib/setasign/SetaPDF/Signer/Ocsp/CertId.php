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

use SetaPDF_Signer_X509_Certificate as Certificate;

/**
 * Helper class to create and decode CertID structures.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Ocsp_CertId
{
    /**
     * The CertId ASN.1 structure.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_certId;

    /**
     * Static function to create an CertId ASN.1 structure by a certificate, issuer certificate and hash algorithm.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @param SetaPDF_Signer_X509_Certificate $issuerCertificate
     * @param string $hashAlgorithm
     * @return static
     * @throws SetaPDF_Signer_Exception
     */
    public static function create(
        Certificate $certificate, Certificate $issuerCertificate, $hashAlgorithm = SetaPDF_Signer_Digest::SHA_1
    ) {
        $certId = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
            '',
            [
                // hashAlgorithm
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                    [
                        new SetaPDF_Signer_Asn1_Element(
                            SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                            SetaPDF_Signer_Asn1_Oid::encode(SetaPDF_Signer_Digest::getOid($hashAlgorithm))
                        ),
                        new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::NULL)
                    ]
                ),
                /* issuerNameHash
                 * ... is the hash of the Issuer's distinguished name. The
                 * hash shall be calculated over the DER encoding of the issuer's name
                 * field in the certificate being checked.
                 */
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::OCTET_STRING,
                    hash($hashAlgorithm, $certificate->getIssuerNameRaw(), true)
                ),
                // issuerKeyHash
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::OCTET_STRING,
                    hash($hashAlgorithm, $issuerCertificate->getSubjectPublicKeyInfoRaw(), true)
                ),
                // serialNumber
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::INTEGER,
                    SetaPDF_Core_Type_HexString::hex2str($certificate->getSerialNumber())
                )
            ]
        );

        return new static($certId);
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_Asn1_Element $certId The CertId ASN.1 element.
     */
    public function __construct(SetaPDF_Signer_Asn1_Element $certId)
    {
        if (
            $certId->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
            || $certId->getChildCount() !== 4
        ) {
            throw new InvalidArgumentException('Invalid CertID structure.');
        }

        $hashAlgorithm = $certId->getChild(0);
        if (
            $hashAlgorithm->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
            || $hashAlgorithm->getChildCount() < 1
        ) {
            throw new InvalidArgumentException('Invalid CertID structure (hashAlgorithm).');
        }

        $hashAlgorithmIdentifier = $hashAlgorithm->getChild(0);
        if (
            $hashAlgorithmIdentifier->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER
        ) {
            throw new InvalidArgumentException('Invalid CertID structure (hashAlgorithm > hashAlgorithmIdentifier).');
        }

        $issuerNameHash = $certId->getChild(1);
        if ($issuerNameHash->getIdent() !== SetaPDF_Signer_Asn1_Element::OCTET_STRING) {
            throw new InvalidArgumentException('Invalid CertID structure (issuerNameHash).');
        }

        $issuerKeyHash = $certId->getChild(2);
        if ($issuerKeyHash->getIdent() !== SetaPDF_Signer_Asn1_Element::OCTET_STRING) {
            throw new InvalidArgumentException('Invalid CertID structure (issuerKeyHash).');
        }

        $serialNumber = $certId->getChild(3);
        if ($serialNumber->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            throw new InvalidArgumentException('Invalid CertID structure (serialNumber).');
        }

        $this->_certId = $certId;
    }

    /**
     * Get the CertId ASN.1 element.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    public function get()
    {
        return $this->_certId;
    }

    /**
     * Check if this CertId value is equal to another instance.
     *
     * @param SetaPDF_Signer_Ocsp_CertId $certId
     * @return bool
     */
    public function equals(SetaPDF_Signer_Ocsp_CertId $certId)
    {
        return ((string)$certId->get()) === ((string)$this->get());
    }
}