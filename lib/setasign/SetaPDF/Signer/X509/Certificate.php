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
use SetaPDF_Signer_X509_Extension_AuthorityKeyIdentifier as AuthorityKeyIdentifier;
use SetaPDF_Signer_X509_Extension_SubjectKeyIdentifier as SubjectKeyIdentifier;
use SetaPDF_Signer_X509_Extensions as Extensions;
use SetaPDF_Signer_X509_Collection as Collection;
use SetaPDF_Signer_X509_Format as Format;
use SetaPDF_Signer_X509_Certificate as Certificate;

/**
 * Class representing a X509 Certificate.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Certificate extends Signed
{
    /**
     * The ASN.1 element holding the X509 certificate.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_certificate;

    /**
     * The extensions instance.
     *
     * @var Extensions
     */
    protected $_extensions;

    /**
     * A digest cache.
     *
     * @var array
     */
    protected $_digestCache = [];

    /**
     * A cache for the subject name.
     *
     * @var array
     */
    protected $_subjectName = [];

    /**
     * Create an instance by a path.
     *
     * @param string $path
     * @return SetaPDF_Signer_X509_Certificate
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public static function fromFile($path)
    {
        if (strpos($path, 'file://') === 0) {
            $path = substr($path, 7);
        }

        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf('Cannot read certificate from path "%s".', $path));
        }

        return new self(file_get_contents($path));
    }

    /**
     * Create an instance by a path or a string.
     *
     * @param string $path
     * @return SetaPDF_Signer_X509_Certificate
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public static function fromFileOrString($pathOrString)
    {
        if (
            strpos($pathOrString, 'file://') === 0 ||
            (strpos($pathOrString, "\x00") === false && file_exists($pathOrString))
        ) {
            return self::fromFile($pathOrString);
        }

        return new self($pathOrString);
    }

    /**
     * The constructor.
     *
     * @param string $certificate PEM or DER encoded string of the certificate.
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function __construct($certificate)
    {
        if (strpos($certificate, '-----BEGIN CERTIFICATE-----') === false) {
            if (($_certificate = base64_decode($certificate, true)) !== false) {
                $certificate = $_certificate;
            }

            $certificate = Pem::encode($certificate, 'CERTIFICATE');
        }

        $parsedCertificate = openssl_x509_parse($certificate);
        if ($parsedCertificate === false) {
            throw new InvalidArgumentException('Cannot parse X.509 certificate.');
        }

        $label = 'CERTIFICATE';
        $certificate = Pem::decode($certificate, $label);
        $this->_certificate = SetaPDF_Signer_Asn1_Element::parse($certificate);
    }

    /**
     * Get the ASN.1 instance of the certificate.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    public function getAsn1()
    {
        return $this->_certificate;
    }

    /**
     * Get the certificate encoded as DER or PEM.
     *
     * @param string $format
     * @return string
     */
    public function get($format = Format::PEM)
    {
        switch (strtolower($format)) {
            case Format::DER:
                return (string) $this->getAsn1();
            case Format::PEM:
                return Pem::encode((string)$this->getAsn1(), 'CERTIFICATE');
            default:
                throw new InvalidArgumentException(sprintf('Unknown format "%s".', $format));
        }
    }

    /**
     * Get the TBSCertificate value.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    private function _getTBSCertificate()
    {
        return $this->_certificate->getChild(0);
    }

    /**
     * @inheritDoc
     */
    public function getSignatureAlgorithm()
    {
        $signatureAlgorithm = $this->_certificate->getChild(1);
        $parameter = $signatureAlgorithm->getChild(1);

        return [
            SetaPDF_Signer_Asn1_Oid::decode($signatureAlgorithm->getChild(0)->getValue()),
            $parameter
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSignatureValue($hex = true)
    {
        $signatureValue = $this->_certificate->getChild(2)->getValue();
        $signatureValue = substr($signatureValue, 1);

        if ($hex) {
            return SetaPDF_Core_Type_HexString::str2hex($signatureValue);
        }

        return $signatureValue;
    }

    /**
     * Get the subject name.
     *
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getSubjectName()
    {
        if (isset($this->_subjectName[DistinguishedName::$separator])) {
            return $this->_subjectName[DistinguishedName::$separator];
        }
        $tbs = $this->_getTBSCertificate();
        $offset = 4;

        if ($tbs->getChild(0)->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $subject = $tbs->getChild($offset);
        $this->_subjectName[DistinguishedName::$separator] = DistinguishedName::getAsString($subject);
        return $this->_subjectName[DistinguishedName::$separator];
    }

    /**
     * Get the data of the Subject Public Key Info field.
     *
     * @return string
     * @throws SetaPDF_Signer_Exception
     */
    public function getSubjectPublicKeyInfoRaw()
    {
        $tbs = $this->_getTBSCertificate();
        $offset = 5;

        if ($tbs->getChild(0)->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $subjectPublicKeyInfo = $tbs->getChild($offset);
        if (
            $subjectPublicKeyInfo->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid subjectPublicKeyInfo structure in X509 certificate.');
        }

        $subjectPublicKey = $subjectPublicKeyInfo->getChild(1);
        if ($subjectPublicKey->getIdent() !== SetaPDF_Signer_Asn1_Element::BIT_STRING) {
            throw new SetaPDF_Signer_Exception('Invalid subjectPublicKey structure in X509 certificate.');
        }

        return substr($subjectPublicKey->getValue(), 1);
    }

    /**
     * Get the subject public key info algorithm identifier.
     *
     * @return array First entry is the OID of the identifier. The second entry are the raw parameters as ASN.1 structures.
     * @throws SetaPDF_Signer_Exception
     */
    public function getSubjectPublicKeyInfoAlgorithmIdentifier()
    {
        $tbs = $this->_getTBSCertificate();
        $offset = 5;

        if ($tbs->getChild(0)->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $subjectPublicKeyInfo = $tbs->getChild($offset);
        if (
            $subjectPublicKeyInfo->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid subjectPublicKeyInfo structure in X509 certificate.');
        }

        $algorithm = $subjectPublicKeyInfo->getChild(0);
        if ($algorithm->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid algorithm structure in X509 certificate.');
        }

        $parameter = $algorithm->getChild(1);

        return [
            SetaPDF_Signer_Asn1_Oid::decode($algorithm->getChild(0)->getValue()),
            $parameter
        ];
    }

    /**
     * Get the IssuerName field.
     *
     * @return SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    public function getIssuerNameRaw()
    {
        $tbs = $this->_getTBSCertificate();
        $offset = 2;

        if ($tbs->getChild(0)->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $issuerName = $tbs->getChild($offset);
        if (
            $issuerName->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid issuerName structure in X509 certificate.');
        }

        return $issuerName;
    }

    /**
     * Get the issuer name.
     *
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getIssuerName()
    {
        return DistinguishedName::getAsString($this->getIssuerNameRaw());
    }

    /**
     * Returns the first issuer found in the given Collection.
     *
     * @param Collection $collection
     * @return null|Certificate
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getIssuer(Collection $collection)
    {
        $all = $this->getIssuers($collection)->getAll();
        return count($all) ? $all[0] : null;
    }

    /**
     * Get all found issuers found in the given collection.
     *
     * @param SetaPDF_Signer_X509_Collection $collection
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getIssuers(Collection $collection)
    {
        /** @var AuthorityKeyIdentifier $authorityKeyIdentifierExtension */
        $authorityKeyIdentifierExtension = $this->getExtensions()->get(AuthorityKeyIdentifier::OID);
        if ($authorityKeyIdentifierExtension) {
            $keyIdentifier = $authorityKeyIdentifierExtension->getKeyIdentifier();
            $authorityCertificateIssuer = $authorityKeyIdentifierExtension->getAuthorityCertificateIssuer();
            $authorityCertificateSerialNumber = $authorityKeyIdentifierExtension->getAuthorityCertificateSerialNumber();

            // reduce possible certificates by SubjectKeyIdentifier extension
            $issuers = $collection->findByCallback(static function(Certificate $possibleIssuerCertificate) use (
                $keyIdentifier, $authorityCertificateIssuer, $authorityCertificateSerialNumber
            ) {
                /** @var SubjectKeyIdentifier $extension */
                $extension = $possibleIssuerCertificate->getExtensions()->get(SubjectKeyIdentifier::OID);
                if ($extension === false) {
                    return false;
                }

                if ($extension->getKeyIdentifier() === $keyIdentifier) {
                    return true;
                }

                // if one of it is set, we decide by them. But at the end both must match.
                if ($authorityCertificateSerialNumber || $authorityCertificateIssuer) {
                    if (!$authorityCertificateSerialNumber || !$authorityCertificateIssuer) {
                        return false;
                    }

                    if ($possibleIssuerCertificate->getSerialNumber() === $authorityCertificateSerialNumber &&
                        $possibleIssuerCertificate->getSubjectName() === $authorityCertificateIssuer
                    ) {
                        return true;
                    }
                }

                return false;
            });
        } else {
            $issuers = $collection->findBySubject($this->getIssuerName());
        }

        return $issuers;
    }

    /**
     * Get the serial number (hex encoded).
     *
     * @return string
     * @throws SetaPDF_Signer_Exception
     */
    public function getSerialNumber()
    {
        return SetaPDF_Core_Type_HexString::str2hex($this->getSerialNumberRaw()->getValue());
    }

    /**
     * Get the serial number as a raw ASN.1 element.
     *
     * @return bool|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    public function getSerialNumberRaw()
    {
        $tbs = $this->_getTBSCertificate();
        $offset = 0;

        if ($tbs->getChild(0)->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $serialNumber = $tbs->getChild($offset);
        if (!$serialNumber || $serialNumber->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            throw new SetaPDF_Signer_Exception('Invalid serialNumber structure in X509 certificate.');
        }

        return $serialNumber;
    }

    /**
     * Get the validity field.
     *
     * @return bool|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    protected function _getValidity()
    {
        $tbs = $this->_getTBSCertificate();
        $offset = 3;

        if ($tbs->getChild(0)->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $validity = $tbs->getChild($offset);
        if (!$validity || $validity->getIdent()!==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid validity structure in X509 certificate.');
        }

        if ($validity->getChildCount() !== 2) {
            throw new SetaPDF_Signer_Exception('Invalid validity structure in X509 certificate.');
        }

        return $validity;
    }

    /**
     * Get the "valid from" value.
     *
     * @param DateTimeZone|null $timeZone Default timezone is UTC.
     * @return DateTime
     * @throws Exception
     */
    public function getValidFrom(DateTimeZone $timeZone = null)
    {
        $validity = $this->_getValidity();
        $d = SetaPDF_Signer_Asn1_Time::decode($validity->getChild(0));
        if ($timeZone !== null) {
            $d->setTimezone($timeZone);
        }
        return $d;
    }

    /**
     * Get the "valid to" value.
     *
     * @param DateTimeZone|null $timeZone Default timezone is UTC.
     * @return DateTime
     * @throws Exception
     */
    public function getValidTo(DateTimeZone $timeZone = null)
    {
        $validity = $this->_getValidity();
        $d = SetaPDF_Signer_Asn1_Time::decode($validity->getChild(1));
        if ($timeZone !== null) {
            $d->setTimezone($timeZone);
        }
        return $d;
    }

    /**
     * Checks whether the certificate was valid at a given date and time.
     *
     * @param DateTimeInterface $dateTime
     * @param DateTimeZone|null $timeZone
     * @return bool
     * @throws Exception
     */
    public function isValidAt(DateTimeInterface $dateTime, DateTimeZone $timeZone = null)
    {
        return (
            $dateTime >= $this->getValidFrom($timeZone) &&
            $dateTime <= $this->getValidTo($timeZone)
        );
    }

    /**
     * Get the extensions object.
     *
     * @return SetaPDF_Signer_X509_Extensions
     */
    public function getExtensions()
    {
        if ($this->_extensions === null) {
            $this->_extensions = new Extensions($this->_getTBSCertificate());
        }

        return $this->_extensions;
    }

    /**
     * Get the digest of the certificate.
     *
     * @param string $algo
     * @param bool $raw
     * @return string
     */
    public function getDigest($algo = 'sha1', $raw = false)
    {
        $cacheKey = $algo . ($raw ? '-1' : '-0');
        if (!isset($this->_digestCache[$cacheKey])) {
            $this->_digestCache[$cacheKey] = hash($algo, $this->get(Format::DER), $raw);
        }

        return $this->_digestCache[$cacheKey];
    }

    /**
     * @inheritDoc
     */
    public function getSignedData()
    {
        return (string)$this->_getTBSCertificate();
    }

    /**
     * Verify the signed object.
     *
     * @param SetaPDF_Signer_X509_Certificate|null $issuerCertifcate If omitted the certificate instance is used as the
     *                                                               issuer (self-signed).
     * @return bool
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function verify(Certificate $issuerCertifcate = null)
    {
        if ($issuerCertifcate === null && $this->getIssuerName() === $this->getSubjectName()) {
            $issuerCertifcate = $this;
        }

        return parent::verify($issuerCertifcate);
    }
}