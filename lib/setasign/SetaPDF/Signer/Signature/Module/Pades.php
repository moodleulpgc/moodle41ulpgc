<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Pades.php 1480 2020-06-11 15:31:54Z jan.slabon $
 */

/**
 * A signature module to create PAdES-BES/B-B conform signatures.
 *
 * This modules allows you to create signatures conforming to the PAdES-BES profile as specified in
 * ETSI TS 102 778-3 or the PAdES baseline signature level B-B (PAdES-B-B) specified in ETSI EN 319 142-1.
 *
 * By adding a signature time-stamp through e.g. the {@link SetaPDF_Signer_Timestamp_Module_Rfc3161_Curl} class
 * you can add the optional signature time-stamp attribute to comply with e.g. PAdES-B-T (ETSI EN 319 142-1).
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Signature_Module_Pades extends SetaPDF_Signer_Signature_Module_Cms
implements SetaPDF_Signer_Signature_DictionaryInterface, SetaPDF_Signer_Signature_DocumentInterface
{
    /**
     * Data for the signingCertificatesV2 attribute
     *
     * @var array
     */
    protected $_signingCertificatesV2 = [];

    /**
     * Updates the signature dictionary.
     *
     * PAdES requires special Filter and SubFilter entries in the signature dictionary.
     *
     * @param SetaPDF_Core_Type_Dictionary $dictionary
     * @throws SetaPDF_Signer_Exception
     */
    public function updateSignatureDictionary(SetaPDF_Core_Type_Dictionary $dictionary)
    {
        /* do some checks:
         * - entry with the key M in the Signature Dictionary
         */
        if (!$dictionary->offsetExists('M')) {
            throw new SetaPDF_Signer_Exception(
                'The key M (the time of signing) shall be present in the signature dictionary to conform with PAdES.'
            );
        }

        $dictionary['SubFilter'] = new SetaPDF_Core_Type_Name('ETSI.CAdES.detached', true);
        $dictionary['Filter'] = new SetaPDF_Core_Type_Name('Adobe.PPKLite', true);
    }

    /**
     * Updates the document instance.
     *
     * @param SetaPDF_Core_Document $document
     * @see ETSI TS 102 778-3 V1.2.1 - 4.7 Extensions Dictionary
     * @see ETSI EN 319 142-1 V1.1.0 - 5.6 Extension dictionary
     */
    public function updateDocument(SetaPDF_Core_Document $document)
    {
        $extensions = $document->getCatalog()->getExtensions();
        $extensions->setExtension('ESIC', '1.7', 2);
    }

    /**
     * Set the signing certificate (PEM).
     *
     * @param string $certificate
     * @throws InvalidArgumentException
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function setCertificate($certificate)
    {
        parent::setCertificate($certificate);
        $this->addSigningCertificateV2($certificate);
    }

    /**
     * Set the digest algorithm to use when signing.
     *
     * Possible values are defined in TS 119 312.
     *
     * @see SetaPDF_Signer_Digest
     * @param string $digest
     */
    public function setDigest($digest)
    {
        switch ($digest) {
            case SetaPDF_Signer_Digest::MD5:
            case SetaPDF_Signer_Digest::SHA_1:      // ETSI TS 119 312 V1.1.1 - 5.3.1 SHA-1 is no more recommended
            case SetaPDF_Signer_Digest::RMD_160:    // ETSI TS 102 176-1 - 5.2.2 RIPEMD-160 is no more recommended
                throw new InvalidArgumentException(
                    'The passed digest algorithm ist no more recommended for PAdES signatures.'
                );
        }

        parent::setDigest($digest);
    }

    /**
     * Adds Signing Certificate Reference Attribute.
     *
     * @param string|string[]|SetaPDF_Signer_X509_Certificate|SetaPDF_Signer_X509_Certificate[] $certificate
     * @param string $hashAlgorithm
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function addSigningCertificateV2($certificate, $hashAlgorithm = SetaPDF_Signer_Digest::SHA_256)
    {
        if (is_array($certificate)) {
            foreach ($certificate AS $_certificate) {
                $this->addSigningCertificateV2($_certificate, $hashAlgorithm);
            }
            return;
        }

        if (!$certificate instanceof SetaPDF_Signer_X509_Certificate) {
            $certificate = SetaPDF_Signer_X509_Certificate::fromFileOrString($certificate);
        }

        $this->_signingCertificatesV2[$certificate->getDigest()] = [
            $hashAlgorithm,
            $certificate
        ];
    }

    /**
     * Creates and returns all signed attribues.
     *
     * Overwritten to add additional required signing attributes.
     *
     * @return SetaPDF_Signer_Asn1_Element[]|null
     * @throws SetaPDF_Signer_Exception
     */
    protected function _getSignedAttributes()
    {
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
        $signedAttributes = parent::_getSignedAttributes();

        /* ETSI TS 101 733
         * 5.7.3 Signing Certificate Reference Attributes
         *
         * The Signing certificate reference attributes are supported by using either the ESS signing-certificate
         * attribute or the ESS-signing-certificate-v2 attribute.
         *
         * WE ONLY IMPLEMENT V2
         */
        if (count($this->_signingCertificatesV2) > 0) {
            $signedAttributes[] = $this->_getSigningCertificateV2Attribute();
        }

        return $signedAttributes;
    }

    /**
     * Create and return the Signing Certificate Reference Attributes.
     *
     * @return SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    protected function _getSigningCertificateV2Attribute()
    {
        /**
         * id-aa-signingCertificateV2 OBJECT IDENTIFIER ::= { iso(1)
         *      member-body(2) us(840) rsadsi(113549) pkcs(1) pkcs9(9) smime(16) id-aa(2) 47
         * }
         *
         * SigningCertificateV2 ::= SEQUENCE {
         *   certs SEQUENCE OF ESSCertIDv2,
         *   policies SEQUENCE OF PolicyInformation OPTIONAL
         * }
         *
         *
         * ESSCertIDv2 ::= SEQUENCE {
         *   hashAlgorithm AlgorithmIdentifier DEFAULT {algorithm id-sha256},
         *   certHash Hash,
         *   issuerSerial IssuerSerial OPTIONAL
         * }
         *
         * Hash ::= OCTET STRING
         *
         * IssuerSerial ::= SEQUENCE {
         *   issuer GeneralNames,
         *   serialNumber CertificateSerialNumber
         * }
         *
         * issuer
         *   contains the issuer name of the certificate. For non-attribute
         *   certificates, the issuer MUST contain only the issuer name from
         *   the certificate encoded in the directoryName choice of
         *   GeneralNames. For attribute certificates, the issuer MUST contain
         *   the issuer name field from the attribute certificate.
         *
         * GeneralNames ::= SEQUENCE SIZE (1..MAX) OF GeneralName
         *
         * GeneralName ::= CHOICE {
         *   otherName [0] AnotherName,
         *   rfc822Name [1] IA5String,
         *   dNSName [2] IA5String,
         *   x400Address [3] ORAddress,
         *   directoryName [4] Name,
         *   ediPartyName [5] EDIPartyName,
         *   uniformResourceIdentifier [6] IA5String,
         *   iPAddress [7] OCTET STRING,
         *   registeredID [8] OBJECT IDENTIFIER
         * }
         *
         */
        $essCertIds = [];
        /** @var SetaPDF_Signer_X509_Certificate $certificate */
        foreach ($this->_signingCertificatesV2 AS list($hashAlgorithm, $certificate)) {
            $issuerSerial = new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                [
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                        [
                            new SetaPDF_Signer_Asn1_Element(
                                SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | "\x04", '',
                                $certificate->getIssuerNameRaw()
                            ),
                        ]
                    ),
                    $certificate->getSerialNumberRaw()
                ]
            );

            /**
             * ESSCertIDv2 ::= SEQUENCE {
             *   hashAlgorithm AlgorithmIdentifier DEFAULT {algorithm id-sha256},
             *   certHash Hash,
             *   issuerSerial IssuerSerial OPTIONAL
             * }
             */
            $essCertIds[] = new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                [
                    // AlgorithmIdentifier DEFAULT {algorithm id-sha256},
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
                    // Hash ::= OCTET STRING
                    new SetaPDF_Signer_Asn1_Element(
                        SetaPDF_Signer_Asn1_Element::OCTET_STRING,
                        $certificate->getDigest($hashAlgorithm, true)
                    ),
                    $issuerSerial,
                ]
            );
        }

        $signingCertificate = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
            [
                // certs SEQUENCE OF ESSCertID,
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                    $essCertIds
                )
            ]
        );

        return new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
            [
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                    SetaPDF_Signer_Asn1_Oid::encode('1.2.840.113549.1.9.16.2.47') // id-aa-signingCertificateV2
                ),
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::SET | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED, '',
                    $signingCertificate
                )
            ]
        );
    }
}