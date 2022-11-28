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
use SetaPDF_Signer_Asn1_Time as Time;
use SetaPDF_Signer_Pem as Pem;
use SetaPDF_Signer_X509_Format as Format;
use SetaPDF_Signer_X509_Certificate as Certificate;

/**
 * Class representing a CRL.
 *
 * CertificateList  ::=  SEQUENCE  {
 *     tbsCertList          TBSCertList,
 *     signatureAlgorithm   AlgorithmIdentifier,
 *     signatureValue       BIT STRING  }
 *
 * TBSCertList  ::=  SEQUENCE  {
 *     version                 Version OPTIONAL,
 *                                  -- if present, MUST be v2
 *     signature               AlgorithmIdentifier,
 *     issuer                  Name,
 *     thisUpdate              Time,
 *     nextUpdate              Time OPTIONAL,
 *     revokedCertificates     SEQUENCE OF SEQUENCE  {
 *         userCertificate         CertificateSerialNumber,
 *         revocationDate          Time,
 *         crlEntryExtensions      Extensions OPTIONAL
 *                                  -- if present, version MUST be v2
 *                                  }  OPTIONAL,
 *     crlExtensions           [0]  EXPLICIT Extensions OPTIONAL
 *                                   -- if present, version MUST be v2
 *                               }
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Crl extends Signed
{
    /**
     * The ASN.1 element holding the CRL.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_crl;

    /**
     * Cache of revoked certificates. Indexed by serial numbers (hex encoded).
     *
     * @var array
     */
    protected $_revokedCertificates;

    /**
     * Creates an instance from a file path.
     *
     * @param string $path
     * @return SetaPDF_Signer_X509_Crl
     */
    public static function fromFile($path)
    {
        return new self(file_get_contents($path));
    }

    /**
     * The constructor.
     * @param string $crl PEM or DER encoded string.
     */
    public function __construct($crl)
    {
        try {
            if (strpos($crl, '-----BEGIN X509 CRL-----') === 0 || strpos($crl, '-----BEGIN CRL-----') === 0) {
                $crl = Pem::decode($crl);
            }

            $this->_crl = SetaPDF_Signer_Asn1_Element::parse($crl);

        } catch (SetaPDF_Signer_Asn1_Exception $e) {
            throw new InvalidArgumentException('Could not parse CRL.', 0, $e);
        }

        if ($this->_crl->getChildCount() !== 3) {
            throw new InvalidArgumentException('CertificateList sequence is out of bounds.');
        }
    }

    /**
     * Get the ASN.1 instance of the CRL.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    public function getAsn1()
    {
        return $this->_crl;
    }

    /**
     * Get the CRL encoded as DER or PEM.
     *
     * @param string $format
     * @return string
     */
    public function get($format = Format::PEM)
    {
        switch (strtolower($format)) {
            case Format::DER:
                return (string)$this->getAsn1();
            case Format::PEM:
                return Pem::encode((string)$this->getAsn1(), 'X509 CRL');
            default:
                throw new InvalidArgumentException(sprintf('Unknown format "%s".', $format));
        }
    }

    /**
     * Get the tbsCertList value.
     *
     * @return SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    private function _getTBSCertList()
    {
        $tbsCertList = $this->_crl->getChild(0);
        if (
            $tbsCertList->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)) {
            throw new SetaPDF_Signer_Exception('Invalid CRL structure in TBSCertList.');
        }

        return $tbsCertList;
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignatureAlgorithm()
    {
        $signatureAlgorithm = $this->_crl->getChild(1);
        if (
            $signatureAlgorithm->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)) {
            throw new SetaPDF_Signer_Exception('Invalid data type in ASN.1 structure (expected SEQUENCE).');
        }

        $algorithm = $signatureAlgorithm->getChild(0);
        if (!$algorithm || $algorithm->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER) {
            throw new SetaPDF_Signer_Exception('Invalid data type in ASN.1 structure (expected OBJECT IDENTIFIER).');
        }

        $parameter = $signatureAlgorithm->getChild(1);

        return [
            SetaPDF_Signer_Asn1_Oid::decode($algorithm->getValue()),
            $parameter
        ];
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignatureValue($hex = true)
    {
        $signatureValue = $this->_crl->getChild(2);
        if ($signatureValue->getIdent() !== SetaPDF_Signer_Asn1_Element::BIT_STRING) {
            throw new SetaPDF_Signer_Exception('Invalid data type in ASN.1 structure (expected BIT STRING).');
        }

        $signatureValue = substr($signatureValue->getValue(), 1);

        if ($hex) {
            return SetaPDF_Core_Type_HexString::str2hex($signatureValue);
        }

        return $signatureValue;
    }

    /**
     * Get the issuer name of the CRL.
     *
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getIssuerName()
    {
        $tbs = $this->_getTBSCertList();
        $offset = 1;

        if ($tbs->getChild(0)->getIdent() === SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $issuer = $tbs->getChild($offset);
        if (!$issuer) {
            throw new SetaPDF_Signer_Exception('Invalid data type in ASN.1 structure (expected NAME).');
        }

        return DistinguishedName::getAsString($issuer);
    }

    /**
     * Get the issue date of the CRL.
     *
     * @return DateTime
     * @throws SetaPDF_Signer_Exception
     */
    public function getThisUpdate()
    {
        $tbs = $this->_getTBSCertList();
        $offset = 2;

        if ($tbs->getChild(0)->getIdent() === SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $thisUpdate = $tbs->getChild($offset);

        return Time::decode($thisUpdate);
    }

    /**
     * Get the date by which the next CRL will be issued.
     *
     * @return false|DateTime
     * @throws SetaPDF_Signer_Exception
     */
    public function getNextUpdate()
    {
        $tbs = $this->_getTBSCertList();
        $offset = 3;

        if ($tbs->getChild(0)->getIdent() === SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $nextUpdate = $tbs->getChild($offset);
        if (
            $nextUpdate->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED)
        ) {
            return Time::decode($nextUpdate);
        }

        return false;
    }

    /**
     * Get all revoked certificates.
     *
     * @return array The index is the hex encoded serial number of the certificate. The value is an array with detailed
     *               information (currently only "revocationDate").
     * @throws SetaPDF_Signer_Exception
     */
    public function getRevokedCertificates()
    {
        if ($this->_revokedCertificates !== null) {
            return $this->_revokedCertificates;
        }
        $tbs = $this->_getTBSCertList();
        $offset = 3;

        if ($tbs->getChild(0)->getIdent() === SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $nextUpdate = $tbs->getChild($offset);
        if (
            $nextUpdate->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED)
        ) {
            $offset++;
        }

        $this->_revokedCertificates = [];
        $revokedCertificates = $tbs->getChild($offset);
        if (!$revokedCertificates ||
            $revokedCertificates->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::SEQUENCE | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED)
        ) {
            return $this->_revokedCertificates;
        }

        foreach ($revokedCertificates->getChildren() as $sequence) {
            $serialNumber = SetaPDF_Core_Type_HexString::str2hex($sequence->getChild(0)->getValue());
            $this->_revokedCertificates[$serialNumber] = [
                'revocationDate' => Time::decode($sequence->getChild(1))
            ];
        }

        return $this->_revokedCertificates;
    }

    /**
     * Get the digest of the CRL.
     *
     * @param string $algo
     * @param bool $raw
     * @return string
     */
    public function getDigest($algo = 'sha1', $raw = false)
    {
        return hash($algo, (string)$this->_crl, $raw);
    }

    /**
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @return bool
     * @throws SetaPDF_Signer_Exception
     */
    public function isRevoked(Certificate $certificate)
    {
        $serialNumber = $certificate->getSerialNumber();
        $revokedCertificates = $this->getRevokedCertificates();

        return isset($revokedCertificates[$serialNumber]);
    }

    /**
     * @inheritDoc
     * @return string
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignedData()
    {
        return (string)$this->_getTBSCertList();
    }
}