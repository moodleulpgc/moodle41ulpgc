<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Cms.php 1591 2020-11-19 15:07:17Z jan.slabon $
 */

/**
 * A signature module to create CMS signatures.
 *
 * This module creates a signature using the Cryptographic Message Syntax (CMS - described in RFC3852).
 *
 * It allows a low level access to the ASN.1 structure, including signed and unsigned attributes.
 * The final signature makes use of the {@link http://www.php.net/openssl_sign openssl_sign()} function.
 *
 * To add additional signed or unsigned attributes this class needs to be extended and own implementations of the
 * {@link SetaPDF_Signer_Signature_Module_Cms::_getSignedAttributes() _getSignedAttributes()} and
 * {@link SetaPDF_Signer_Signature_Module_Cms::_getUnsignedAttributes() _getUnsignedAttributes()} needs to be
 * implemented.
 *
 * By default the class makes use of signed attributes to reduce the overhead data that needs to be signed
 * (see {@link SetaPDF_Signer_Signature_Module_Cms::getDataToSign() getDataToSign()}).
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Signature_Module_Cms implements SetaPDF_Signer_Signature_Module_ModuleInterface
{
    /**
     * The signing certificate
     *
     * @var SetaPDF_Signer_X509_Certificate
     */
    protected $_certificate;

    /**
     * Get the original signing certificate argument
     *
     * @var string|SetaPDF_Signer_X509_Certificate
     */
    protected $_oCertificate;

    /**
     * The private key to use when signing
     *
     * @var resource|string
     */
    protected $_privateKey;

    /**
     * OCSP responses as a binary strings to be embedded in the RevocationInfoArchival attribute.
     *
     * @var SetaPDF_Signer_Asn1_Element[]
     */
    protected $_ocspResponses = [];

    /**
     * CRL responses as a binary strings to be embedded in the RevocationInfoArchival attribute.
     *
     * @var array
     */
    protected $_crls = [];

    /**
     * Additional certificates to be specified
     *
     * @var SetaPDF_Signer_X509_Certificate[]
     */
    protected $_extraCertificates = [];

    /**
     * The digest algorithm to use when signing
     *
     * @var string
     */
    protected $_digest = SetaPDF_Signer_Digest::SHA_256;

    /**
     * The CMS structure
     *
     * @var null|SetaPDF_Signer_Asn1_Element
     */
    protected $_cms;

    /**
     * The signature value object in the CMS structure
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_signatureValue;

    /**
     * The signature hash value object in the CMS structure
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_hashValue;

    /**
     * Ensures a certificate parameter and parses it into an ASN.1 element object structure.
     *
     * @param string $certificate A PEM encoded string or path to a PEM encoded X.509 certificate.
     * @return SetaPDF_Signer_Asn1_Element
     * @throws InvalidArgumentException
     * @throws SetaPDF_Signer_Asn1_Exception
     * @deprecated
     */
    static public function getParsedCertificate($certificate)
    {
        if ($certificate instanceof SetaPDF_Signer_X509_Certificate) {
            $certificate = $certificate->get(SetaPDF_Signer_X509_Format::PEM);
        }

        if (strpos($certificate, 'file://') === 0) {
            $certificate = substr($certificate, 7);
        }

        if (strpos($certificate, "\x00") !== false) {
            throw new InvalidArgumentException(
                'Certificate string includes a null character. It is not a PEM encoded certificate nor a file path.'
            );
        }

        if (file_exists($certificate)) {
            $certificate = file_get_contents($certificate);
        }

        $startPos = strpos($certificate, '-----BEGIN CERTIFICATE-----');
        $endPos = strpos($certificate, '-----END CERTIFICATE-----');
        if ($startPos === false || $endPos === false) {
            throw new InvalidArgumentException('Passed certificate data is not PEM encoded.');
        }

        $certificate = substr($certificate, $startPos + 27);
        $certificate = substr($certificate, 0, $endPos - $startPos - 27);
        $certificate = base64_decode(trim($certificate), true);

        if ($certificate === false) {
            throw new InvalidArgumentException('Passed certificate data cannot be decoded with MIME base64.');
        }

        // reassemble certificate string for PHP 5.4
        $_certificate = "-----BEGIN CERTIFICATE-----\n"
            . wordwrap(base64_encode($certificate), 75, "\n", true)
            . "\n-----END CERTIFICATE-----";

        // is this really a x509 certificate?
        if (openssl_x509_parse($_certificate) === false) {
            throw new InvalidArgumentException('Cannot parse PEM encoded X.509 certificate.');
        }

        unset($_certificate);

        return SetaPDF_Signer_Asn1_Element::parse($certificate);
    }

    /**
     * Set the signing certificate (PEM).
     *
     * @param string|SetaPDF_Signer_X509_Certificate $certificate PEM encoded certificate, path to the PEM encoded
     *                                                            certificate or a certificate instance.
     * @throws InvalidArgumentException
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function setCertificate($certificate)
    {
        $this->_oCertificate = $certificate;

        if (!$certificate instanceof SetaPDF_Signer_X509_Certificate) {
            $certificate = SetaPDF_Signer_X509_Certificate::fromFileOrString($certificate);
        }

        $this->_certificate = $certificate;
        $this->_cms = null;
    }

    /**
     * Get the certificate value.
     *
     * @return string|SetaPDF_Signer_X509_Certificate
     */
    public function getCertificate()
    {
        return $this->_oCertificate;
    }

    /**
     * Adds an OCSP response which will be embedded in the CMS structure.
     *
     * @param string|SetaPDF_Signer_Ocsp_Response $ocspResponse DER encoded OCSP response or OCSP response instance.
     * @throws SetaPDF_Signer_Exception
     */
    public function addOcspResponse($ocspResponse)
    {
        if (!$ocspResponse instanceof SetaPDF_Signer_Ocsp_Response) {
            $ocspResponse = new SetaPDF_Signer_Ocsp_Response($ocspResponse);
        }

        if ($ocspResponse->isGood() === false) {
            throw new SetaPDF_Signer_Exception(
                'OCSP response needs to be successful and the single responses need to be good.'
            );
        }

        $this->_ocspResponses[] = $ocspResponse;
    }

    /**
     * Alias for {@link addOcspResponse()}.
     *
     * @param string $ocspResponse DER encoded OCSP response.
     * @throws SetaPDF_Signer_Exception
     * @deprecated
     */
    public function setOcspResponse($ocspResponse)
    {
        $this->addOcspResponse($ocspResponse);
    }

    /**
     * Adds an CRL which will be embedded in the CMS structure.
     *
     * @param string|SetaPDF_Signer_X509_Crl $crl
     */
    public function addCrl($crl)
    {
        if (!$crl instanceof SetaPDF_Signer_X509_Crl) {
            $crl = new SetaPDF_Signer_X509_Crl($crl);
        }

        $this->_crls[] = $crl;
    }

    /**
     * Add additional certificates which are placed into the CMS structure.
     *
     * @param array|SetaPDF_Signer_X509_Collection $extraCertificates PEM encoded certificates or pathes to PEM encoded
     *                                                                certificates.
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function setExtraCertificates($extraCertificates)
    {
        $this->_extraCertificates = [];
        if ($extraCertificates instanceof SetaPDF_Signer_X509_Collection) {
            foreach ($extraCertificates->getAll() as $extraCertificate) {
                $this->_extraCertificates[] = $extraCertificate;
            }

        } else {
            foreach ($extraCertificates as $extraCertificate) {
                if (!$extraCertificate instanceof SetaPDF_Signer_X509_Certificate) {
                    $extraCertificate = SetaPDF_Signer_X509_Certificate::fromFileOrString($extraCertificate);
                }

                $this->_extraCertificates[] = $extraCertificate;
            }
        }

        $this->_cms = null;
    }

    /**
     * Set the the private key or a path to the private key file and password argument.
     *
     * @param resource|OpenSSLAsymmetricKey|string|array $privateKey A key, returned by openssl_get_privatekey() or a
     *                                                               PEM formatted key as a string. Or a string having
     *                                                               the format file://path/to/file.pem
     * @param string $passphrase The optional parameter passphrase must be used if the specified key is encrypted
     *                           (protected by a passphrase).
     * @throws InvalidArgumentException
     */
    public function setPrivateKey($privateKey, $passphrase = '')
    {
        if (is_array($privateKey)) {
            $privateKey = array_values($privateKey);
            list($privateKey, $passphrase) = $privateKey;
        }

        if (version_compare(PHP_VERSION, '8.0.0-alpha1', '>=')) {
            if (!$privateKey instanceof OpenSSLAsymmetricKey) {
                $privateKey = openssl_pkey_get_private($privateKey, $passphrase);
                if ($privateKey === false) {
                    throw new InvalidArgumentException('Unable to get private key.');
                }
            }
        } else {
            if (!is_resource($privateKey)) {
                $privateKey = openssl_pkey_get_private($privateKey, $passphrase);
                if ($privateKey === false) {
                    throw new InvalidArgumentException('Unable to get private key.');
                }
            }

            if (get_resource_type($privateKey) !== 'OpenSSL key') {
                throw new InvalidArgumentException('Unable to get key details from $privateKey parameter.');
            }
        }

        // we only could check rsa certificates. EC are not validateable without signing anything...
        $details = openssl_pkey_get_details($privateKey);
        if ($details['type'] === OPENSSL_KEYTYPE_RSA && !isset($details['rsa']['d'])) {
            throw new InvalidArgumentException('Supplied $privateKey param is a public key.');
        }

        $this->_privateKey = $privateKey;
    }

    /**
     * Set the digest algorithm to use when signing.
     *
     * Possible values are defined in {@link SetaPDF_Signer_Digest}.
     *
     * @see SetaPDF_Signer_Digest
     * @param string $digest
     */
    public function setDigest($digest)
    {
        if (!SetaPDF_Signer_Digest::isValidDigest($digest)) {
            throw  new InvalidArgumentException(sprintf('Invalid digest algorithm "%s".', $digest));
        }

        $this->_digest = $digest;
    }

    /**
     * Get the digest algorithm.
     *
     * @return string
     */
    public function getDigest()
    {
        return $this->_digest;
    }

    /**
     * Get the complete Cryptographic Message Syntax structure.
     *
     * @return SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    public function getCms()
    {
        if ($this->_cms !== null) {
            return $this->_cms;
        }

        if ($this->_certificate === null) {
            throw new BadMethodCallException('Missing certificate data.');
        }

        // Prepare the sid / SignerIdentifier data
        /**
         * IssuerAndSerialNumber ::= SEQUENCE {
         *   issuer Name,
         *   serialNumber CertificateSerialNumber
         * }
         */
        $issuerAndSerialNumber = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED,
            '',
            [
                $this->_certificate->getIssuerNameRaw(),
                $this->_certificate->getSerialNumberRaw()
            ]
        );

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
         * version is the syntax version number. If the SignerIdentifier is
         * the CHOICE issuerAndSerialNumber, then the version MUST be 1. If
         * the SignerIdentifier is subjectKeyIdentifier, then the version
         * MUST be 3.
         */
        $signerInfo = [
            /**
             * CMSVersion ::= INTEGER { v0(0), v1(1), v2(2), v3(3), v4(4), v5(5) }
             */
            new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::INTEGER, chr(1)),
            /**
             * SignerIdentifier ::= CHOICE {
             *   issuerAndSerialNumber IssuerAndSerialNumber,
             *   subjectKeyIdentifier [0] SubjectKeyIdentifier
             * }
             */
            $issuerAndSerialNumber,
            /**
             * OR (version MUST be 3)
             * SubjectKeyIdentifier ::= OCTET STRING
             */
            /**
             * digestAlgorithm DigestAlgorithmIdentifier ::= AlgorithmIdentifier
             *
             * digestAlgorithm identifies the message digest algorithm, and any associated parameters, used by the
             * signer.[...] "The message digest algorithm SHOULD be among those listed in the digestAlgorithms field of
             * the associated SignerData."
             */
            new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                [
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                        SetaPDF_Signer_Asn1_Oid::encode(SetaPDF_Signer_Digest::getOid($this->getDigest()))
                    ),
                    new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::NULL)
                ]
            )
        ];

        $signedAttributes = $this->_getSignedAttributes();
        if ($signedAttributes !== null) {
            $signerInfo[] = new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                $signedAttributes
            );
        }

        /**
         * SignatureAlgorithmIdentifier ::= AlgorithmIdentifier
         */
        $signerInfo[] = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
            $this->_getSignatureAlgorithmIdentifier()
        );

        /**
         * SignatureValue ::= OCTET STRING
         */
        $this->_signatureValue = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::OCTET_STRING,
            ''
        );

        $signerInfo[] = $this->_signatureValue;

        $unSignedAttributes = $this->_getUnsignedAttributes();
        if ($unSignedAttributes !== null) {
            $signerInfo[] = new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | "\x01", '',
                $unSignedAttributes
            );
        }

        /**
         * NOTE: Clause 5.1 of IETF RFC 5652 [7] requires that the CMS SignedData version be set to 3 if certificates
         *       from SignedData is present (TRUE) AND (any version 1 attribute certificates are present (FALSE) OR any
         *       SignerInfo structures are version 3 (FALSE) OR eContentType from encapContentInfo is other than
         *       id-data (FALSE)). Otherwise, the CMS SignedData version is required to be set to 1.
         */
        $version = 1;

        /**
         * SignedData ::= SEQUENCE {
         *   version CMSVersion,
         *   digestAlgorithms DigestAlgorithmIdentifiers,
         *   encapContentInfo EncapsulatedContentInfo,
         *   certificates [0] IMPLICIT CertificateSet OPTIONAL,
         *   crls [1] IMPLICIT RevocationInfoChoices OPTIONAL,
         *   signerInfos SignerInfos
         * }
         */
        $signedData = [
            /**
             * CMSVersion
             */
            new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::INTEGER, chr($version)),
            /**
             * digestAlgorithms DigestAlgorithmIdentifiers ::= SET OF DigestAlgorithmIdentifier
             */
            new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                [
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                        [
                            new SetaPDF_Signer_Asn1_Element(
                                SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                                SetaPDF_Signer_Asn1_Oid::encode(SetaPDF_Signer_Digest::getOid($this->getDigest()))
                            ),
                            new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::NULL)
                        ]
                    )
                ]
            ),
            /**
             * encapContentInfo EncapsulatedContentInfo ::= SEQUENCE {
             *   eContentType ContentType,
             *   eContent [0] EXPLICIT OCTET STRING OPTIONAL
             * }
             */
            new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                [
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                        SetaPDF_Signer_Asn1_Oid::encode('1.2.840.113549.1.7.1') // PKCS-7
                        // id-data OBJECT IDENTIFIER ::= { iso(1) member-body(2) us(840) rsadsi(113549) pkcs(1) pkcs7(7) 1 }
                    )
                ]
            )
        ];

        $certificates = [$this->_certificate->getDigest() => $this->_certificate->getAsn1()];
        foreach ($this->_extraCertificates as $extraCertificate) {
            $certificates[$extraCertificate->getDigest()] = $extraCertificate->getAsn1();
        }

        /**
         * certificates [0] IMPLICIT CertificateSet OPTIONAL
         *
         * CertificateSet ::= SET OF CertificateChoices
         */
        $signedData[] = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
            $certificates
        );

        /**
         * crls [1] IMPLICIT RevocationInfoChoices OPTIONAL ::= SET OF RevocationInfoChoice
         */

        /**
         * SignerInfos ::= SET OF SignerInfo
         */
        $signedData[] = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
            [
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
                 */
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                    $signerInfo
                )
            ]
        );

        /**
         * ContentInfo ::= SEQUENCE {
         *   contentType ContentType,
         *   content [0] EXPLICIT ANY DEFINED BY contentType
         * }
         */
        $this->_cms = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
            [
                /**
                 * ContentType ::= OBJECT IDENTIFIER
                 */
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                    SetaPDF_Signer_Asn1_Oid::encode('1.2.840.113549.1.7.2') // PKCS-7 signed
                ),
                /**
                 * content
                 */
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                    [
                        /**
                         * SignedData ::= SEQUENCE {
                         *   version CMSVersion,
                         *   digestAlgorithms DigestAlgorithmIdentifiers,
                         *   encapContentInfo EncapsulatedContentInfo,
                         *   certificates [0] IMPLICIT CertificateSet OPTIONAL,
                         *   crls [1] IMPLICIT RevocationInfoChoices OPTIONAL,
                         *   signerInfos SignerInfos
                         * }
                         */
                        new SetaPDF_Signer_Asn1_Element(
                            SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                            $signedData
                        )
                    ]
                )
            ]
        );

        return $this->_cms;
    }

    protected function _getSignatureAlgorithmIdentifier()
    {
        $pubKeyInfoAlgorithmIdentifier = $this->_certificate->getSubjectPublicKeyInfoAlgorithmIdentifier();

        if ($pubKeyInfoAlgorithmIdentifier[0] === '1.2.840.113549.1.1.10') {
            $signatureAlgorithmIdentifierAlgorithm = SetaPDF_Signer_Asn1_Oid::encode($pubKeyInfoAlgorithmIdentifier[0]);
            $signatureAlgorithmIdentifierParameter = $pubKeyInfoAlgorithmIdentifier[1];

        } else {
            $signatureAlgorithmIdentifierAlgorithm = SetaPDF_Signer_Asn1_Oid::encode(SetaPDF_Signer_Digest::getOid(
                $this->getDigest(), $pubKeyInfoAlgorithmIdentifier[0]
            ));
            $signatureAlgorithmIdentifierParameter = new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::NULL);
        }

        return [
            new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                $signatureAlgorithmIdentifierAlgorithm
            ),
            $signatureAlgorithmIdentifierParameter
        ];
    }

    /**
     * Creates and returns all signed attribues.
     *
     * Overwrite this method to add individual signed attributes.
     *
     * @return SetaPDF_Signer_Asn1_Element[]|null
     */
    protected function _getSignedAttributes()
    {
        $this->_hashValue = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::OCTET_STRING,
            ''
        );

        /**
         * SignedAttributes ::= SET SIZE (1..MAX) OF Attribute
         *
         * Attribute ::= SEQUENCE {
         *   attrType OBJECT IDENTIFIER,
         *   attrValues SET OF AttributeValue
         * }
         *
         * AttributeValue ::= ANY
         */
        $signedAttributes = [
            new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                [
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                        SetaPDF_Signer_Asn1_Oid::encode('1.2.840.113549.1.9.3') // content-type
                    ),
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                        new SetaPDF_Signer_Asn1_Element(
                            SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                            SetaPDF_Signer_Asn1_Oid::encode('1.2.840.113549.1.7.1') // PKCS-7
                        )
                    )
                ]
            ),
            new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                [
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                        SetaPDF_Signer_Asn1_Oid::encode('1.2.840.113549.1.9.4') // message-digest
                    ),
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                        $this->_hashValue
                    )
                ]
            )
        ];

        $hasOcspResponses = count($this->_ocspResponses);
        $hasCrls = count($this->_crls);
        if ($hasOcspResponses || $hasCrls) {
            $signedAttributes[] = new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                [
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                        SetaPDF_Signer_Asn1_Oid::encode('1.2.840.113583.1.1.8')
                    ),
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                        [
                            /**
                             * RevocationInfoArchival ::= SEQUENCE {
                             *   crl [0] EXPLICIT SEQUENCE of CRLs, OPTIONAL
                             *   ocsp [1] EXPLICIT SEQUENCE of OCSP Responses, OPTIONAL
                             *   otherRevInfo [2] EXPLICIT SEQUENCE of OtherRevInfo, OPTIONAL
                             * }
                             */
                            $revocationInfoArchival = new SetaPDF_Signer_Asn1_Element(
                                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                                []
                            )
                        ]
                    )
                ]
            );

            if ($hasCrls) {
                $revocationInfoArchival->addChild(new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | "\x00", '',
                    [
                        new SetaPDF_Signer_Asn1_Element(
                            SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                            array_map(static function(SetaPDF_Signer_X509_Crl $crl) {
                                return $crl->getAsn1();
                            }, $this->_crls)
                        )
                    ]
                ));
            }

            if ($hasOcspResponses) {
                $revocationInfoArchival->addChild(new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | "\x01", '',
                    [
                        new SetaPDF_Signer_Asn1_Element(
                            SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                            array_map(static function(SetaPDF_Signer_Ocsp_Response $ocspResponse) {
                                return $ocspResponse->getAsn1();
                            }, $this->_ocspResponses)
                        )
                    ]
                ));
            }
        }

        return $signedAttributes;
    }

    /**
     * Creates and returns unsigned attributes.
     *
     * @return SetaPDF_Signer_Asn1_Element[]|null
     */
    protected function _getUnsignedAttributes()
    {
        return null;
    }

    /**
     * Get the data which needs to be digitally signed.
     *
     * @param SetaPDF_Core_Reader_FilePath $tmpPath
     * @return string
     * @throws SetaPDF_Signer_Exception
     */
    public function getDataToSign(SetaPDF_Core_Reader_FilePath $tmpPath)
    {
        $cms = $this->getCms();

        $signerInfos = SetaPDF_Signer_Asn1_Element::findByPath('1/0/4', $cms);
        if (($signerInfos->getIdent() & "\xA1") === "\xA1") {
            $signerInfos = SetaPDF_Signer_Asn1_Element::findByPath('1/0/5', $cms);
        }

        $signedAttributes = SetaPDF_Signer_Asn1_Element::findByPath('0/3', $signerInfos);
        if ($signedAttributes && $signedAttributes->getIdent() === "\xA0") { // [0] IMPLICIT
            // set the hash value
            $this->_hashValue->setValue(hash_file($this->getDigest(), $tmpPath, true));

            // Sort SET OF in DER encoding.
            $allSignedAttributes = $signedAttributes->getChildren();
            usort($allSignedAttributes, static function($a, $b) {
                return (string)$a > (string)$b ? 1 : -1;
            });

            $signedAttributes->setChildren($allSignedAttributes);

            $signedAttributes = (string)$signedAttributes;
            /* The IMPLICIT [0] tag is not used for the DER encoding, rather an EXPLICIT SET OF tag is used. That is,
             * the DER encoding of the EXPLICIT SET OF tag, rather than of the IMPLICIT [0] tag, MUST be included along
             * with the length and content octets of the value.
             */
            $signedAttributes[0] = SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED;
            $dataToSign = $signedAttributes;
        } else {
            $dataToSign = file_get_contents($tmpPath);
        }

        return $dataToSign;
    }

    /**
     * Set the signature value.
     *
     * By default this needs to be the binary string of an RSASSA-PKCS1-v1_5 signature operation.
     *
     * @param string $signatureValue
     */
    public function setSignatureValue($signatureValue)
    {
        if ($this->_signatureValue === null) {
            throw new BadMethodCallException('CMS structure not created yet.');
        }

        $this->_signatureValue->setValue($signatureValue);
    }

    /**
     * Create a signature for the file in the given $tmpPath.
     *
     * @param SetaPDF_Core_Reader_FilePath $tmpPath
     * @throws BadMethodCallException
     * @throws SetaPDF_Signer_Exception
     * @return string
     */
    public function createSignature(SetaPDF_Core_Reader_FilePath $tmpPath)
    {
        if ($this->_privateKey === null) {
            throw new BadMethodCallException('Impossible to sign the cms without a private key.');
        }

        $details = openssl_pkey_get_details($this->_privateKey);
        if ($details['type'] === OPENSSL_KEYTYPE_DSA && $this->getDigest() !== SetaPDF_Signer_Digest::SHA_1) {
            throw new BadMethodCallException('When using a DSA certificate only SHA1 algorithm is supported.');
        }

        $dataToSign = $this->getDataToSign($tmpPath);

        $signatureValue = '';
        if (@openssl_sign(
                $dataToSign,
                $signatureValue,
                $this->_privateKey,
                $this->getDigest()) === false
        ) {
            $lastError = error_get_last();
            throw new SetaPDF_Signer_Exception(
                'An OpenSSL error occured during signature process' .
                ($lastError !== null ? ': ' . $lastError['message'] : '') . '.'
            );
        }

        $this->setSignatureValue($signatureValue);

        return (string)$this->getCms();
    }
}