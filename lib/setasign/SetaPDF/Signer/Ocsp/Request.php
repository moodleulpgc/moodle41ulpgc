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
use SetaPDF_Signer_Ocsp_CertId as CertId;

/**
 * Class for creating an OCSPRequest structure.
 *
 * OCSPRequest     ::=     SEQUENCE {
 *     tbsRequest                  TBSRequest,
 *     optionalSignature   [0]     EXPLICIT Signature OPTIONAL }
 *
 * TBSRequest      ::=     SEQUENCE {
 *     version             [0]     EXPLICIT Version DEFAULT v1,
 *     requestorName       [1]     EXPLICIT GeneralName OPTIONAL,
 *     requestList                 SEQUENCE OF Request,
 *     requestExtensions   [2]     EXPLICIT Extensions OPTIONAL }
 *
 * Signature       ::=     SEQUENCE {
 *     signatureAlgorithm      AlgorithmIdentifier,
 *     signature               BIT STRING,
 *     certs               [0] EXPLICIT SEQUENCE OF Certificate
 *     OPTIONAL}
 *
 * Version         ::=             INTEGER  {  v1(0) }
 *
 * Request         ::=     SEQUENCE {
 *     reqCert                     CertID,
 *     singleRequestExtensions     [0] EXPLICIT Extensions OPTIONAL }
 *
 *
 * CertID          ::=     SEQUENCE {
 *     hashAlgorithm       AlgorithmIdentifier,
 *     issuerNameHash      OCTET STRING, -- Hash of Issuer's DN
 *     issuerKeyHash       OCTET STRING, -- Hash of Issuers public key
 *     serialNumber        CertificateSerialNumber }
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Ocsp_Request
{
    /**
     * The request ASN.1 structure.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_ocspRequest;

    /**
     * The requestList element.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_requestList;

    /**
     * A reference to the nonce element.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_nonce;

    /**
     * The constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->_requestList = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE
        );

        if (function_exists('random_bytes')) {
            $nonce = random_bytes(16);
        } else {
            $nonce = openssl_random_pseudo_bytes(16);
        }

        $this->_nonce = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::OCTET_STRING,
            $nonce
        );

        $requestExtensions = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | "\x02",
            '',
            [
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
                    '',
                    [
                        new SetaPDF_Signer_Asn1_Element(
                            SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
                            '',
                            [
                                new SetaPDF_Signer_Asn1_Element(
                                    SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                                    SetaPDF_Signer_Asn1_Oid::encode('1.3.6.1.5.5.7.48.1.2')
                                ),
                                new SetaPDF_Signer_Asn1_Element(
                                    SetaPDF_Signer_Asn1_Element::OCTET_STRING,
                                    $this->_nonce
                                )
                            ]
                        )
                    ]
                )
            ]
        );

        $this->_ocspRequest = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
            '',
            [
                // tbsRequest
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
                    '',
                    [
                        $this->_requestList,
                        $requestExtensions
                    ]
                )
            ]
        );
    }

    /**
     * Add a certificate to the reuqest list.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @param SetaPDF_Signer_X509_Certificate $issuerCertificate
     * @param string $hashAlgorithm
     * @throws SetaPDF_Signer_Exception
     */
    public function add(
        Certificate $certificate, Certificate $issuerCertificate, $hashAlgorithm = SetaPDF_Signer_Digest::SHA_1
    ) {
        $certId = CertId::create($certificate, $issuerCertificate, $hashAlgorithm);

        $request = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
            '',
            [$certId->get()]
        );

        $this->_requestList->addChild($request);
    }

    /**
     * Get the nonce value.
     *
     * @return string
     */
    public function getNonce()
    {
        return (string)$this->_nonce;
    }

    /**
     * Get the request message.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    public function get()
    {
        return $this->_ocspRequest;
    }
}