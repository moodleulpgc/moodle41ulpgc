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

use Psr\SimpleCache\CacheInterface;
use SetaPDF_Signer_Asn1_DistinguishedName as DistinguishedName;
use SetaPDF_Signer_Cms_SignedData as SignedData;
use SetaPDF_Signer_Ocsp_Client as Client;
use SetaPDF_Signer_Ocsp_Request as Request;
use SetaPDF_Signer_Ocsp_Response as OcspResponse;
use SetaPDF_Signer_Tsp_Token as TspToken;
use SetaPDF_Signer_X509_Certificate as Certificate;
use SetaPDF_Signer_X509_Chain as Chain;
use SetaPDF_Signer_X509_CollectionInterface as CollectionInterface;
use SetaPDF_Signer_X509_Collection as Collection;
use SetaPDF_Signer_X509_Crl as Crl;
use SetaPDF_Signer_X509_Extension_AuthorityInformationAccess as AuthorityInformationAccess;
use SetaPDF_Signer_X509_Extension_BasicConstraints as BasicConstraints;
use SetaPDF_Signer_X509_Extension_CrlDisributionPoint as CrlDisributionPoint;
use SetaPDF_Signer_X509_Extension_ExtendedKeyUsage as ExtendedKeyUsage;
use SetaPDF_Signer_X509_Extension_OcspNoCheck as OcspNoCheck;
use SetaPDF_Signer_ValidationRelatedInfo_Result as Result;
use SetaPDF_Signer_ValidationRelatedInfo_CertificateResult as CertificateResult;
use SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface as LoggerInterface;
use SetaPDF_Signer_ValidationRelatedInfo_Logger as Logger;
use SetaPDF_Signer_ValidationRelatedInfo_IntegrityResult as IntegrityResult;

/**
 * Class offering methods to collect validation related information.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_ValidationRelatedInfo_Collector
{
    /**
     * Source constant.
     *
     * @var int
     */
    const SOURCE_OCSP = 2;

    /**
     * Source constant.
     *
     * @var int
     */
    const SOURCE_CRL = 4;

    /**
     * Source constant.
     *
     * @var int
     */
    const SOURCE_OCSP_OR_CRL = 6;

    /**
     * Source constant.
     *
     * @var int
     */
    const SOURCE_OCSP_AND_CRL = 7;

    /**
     * A collection of trusted certificates.
     *
     * @var Collection
     */
    protected $_trustedCertificates;

    /**
     * A collection of extra certificates.
     *
     * @var Collection
     */
    protected $_extraCertificates;

    /**
     * A cache instance for resolved issuer certificates.
     *
     * @var CacheInterface|null
     */
    protected $_issuerCache;

    /**
     * A cache instance for CRLs.
     *
     * @var CacheInterface|null
     */
    protected $_crlCache;

    /**
     * The informatio resolver manager.
     *
     * @var SetaPDF_Signer_InformationResolver_Manager
     */
    protected $_informationResolverManager;

    /**
     * Certificate that needs to be processed.
     *
     * @var Certificate[]
     */
    protected $_leftCertificates;

    /**
     * CRL list indexed by md5 hash of the issuers public key.
     *
     * @var array
     */
    protected $_crls;

    /**
     * The OCSP client instance.
     *
     * @var Client
     */
    protected $_ocspClient;

    /**
     * A logger instance.
     *
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Returns all signature field names.
     *
     * @return string[]
     */
    static public function getSignatureFieldNames(\SetaPDF_Core_Document $document)
    {
        $signatureFieldNames = [];

        foreach ($document->getCatalog()->getAcroForm()->getTerminalFieldsObjects() AS $fieldData) {
            /** @var SetaPDF_Core_Type_Dictionary $fieldData */
            $fieldData = $fieldData->ensure();
            $ft = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($fieldData, 'FT');
            if (!$ft || $ft->getValue() !== 'Sig') {
                continue;
            }

            $name = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($fieldData);
            $signatureFieldNames[] = $name;
        }

        return $signatureFieldNames;
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_X509_CollectionInterface|null $trustedCertificates
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function __construct(CollectionInterface $trustedCertificates = null)
    {
        if ($trustedCertificates !== null) {
            $this->getTrustedCertificates()->add($trustedCertificates);
        }
    }

    /**
     * Set a cache instance for resolved issuer certificates.
     *
     * @param CacheInterface|null $issuerCache
     */
    public function setIssuerCache(CacheInterface $issuerCache = null)
    {
        $this->_issuerCache = $issuerCache;
    }

    /**
     * Get the cache instance for resolved issuer certificates.
     *
     * @return CacheInterface|null
     */
    public function getIssuerCache()
    {
        return $this->_issuerCache;
    }

    /**
     * Set a cache instance for CRLs.
     *
     * @param CacheInterface|null $crlCache
     */
    public function setCrlCache(CacheInterface $crlCache = null)
    {
        $this->_crlCache = $crlCache;
    }

    /**
     * Get the cache instance for CRLs.
     *
     * @return CacheInterface|null
     */
    public function getCrlCache()
    {
        return $this->_crlCache;
    }

    /**
     * Set an information resolver manager instance.
     *
     * @param SetaPDF_Signer_InformationResolver_Manager $manager
     */
    public function setInformationResolverManager(SetaPDF_Signer_InformationResolver_Manager $manager)
    {
        $this->_informationResolverManager = $manager;
    }

    /**
     * Get the information resolver manager instance.
     *
     * If none was explicity was passed before it will create a standard instance with an HTTP resolver that uses cURL.
     *
     * @return SetaPDF_Signer_InformationResolver_Manager
     */
    public function getInformationResolverManager()
    {
        if ($this->_informationResolverManager === null) {
            // create standard manager and resolver
            $this->_informationResolverManager = new SetaPDF_Signer_InformationResolver_Manager();
            $this->_informationResolverManager->addResolver(
                new SetaPDF_Signer_InformationResolver_HttpCurlResolver()
            );
        }

        $this->_informationResolverManager->setLogger($this->getLogger());

        return $this->_informationResolverManager;
    }

    /**
     * Set a logger instance.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Get the logger instance.
     *
     * If no logger instance was passed before a new instance of {@link SetaPDF_Signer_ValidationRelatedInfo_Logger} is
     * returned.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function getLogger()
    {
        if ($this->_logger === null) {
            $this->_logger = new Logger();
        }

        return $this->_logger;
    }

    /**
     * Get the trusted certificates collection.
     *
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getTrustedCertificates()
    {
        if ($this->_trustedCertificates === null) {
            $this->_trustedCertificates = new Collection();
        }

        return $this->_trustedCertificates;
    }

    /**
     * Get the extra certificates collection.
     *
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getExtraCertificates()
    {
        if ($this->_extraCertificates === null) {
            $this->_extraCertificates = new Collection();
        }

        return $this->_extraCertificates;
    }

    /**
     * Get a collection with all certificates (trusted + extra).
     *
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    protected function _getAllCertificates()
    {
        $collection = new Collection($this->getTrustedCertificates());
        $collection->add($this->getExtraCertificates());

        return $collection;
    }

    /**
     * Build a certificate path by a certificate, date and time.
     *
     * Internally the method tries to gather issuer certificates through the AIA extension of the certificates if the
     * path couldn't be created.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeZone|null $timeZone
     * @return SetaPDF_Signer_X509_Certificate[]
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws SetaPDF_Signer_ValidationRelatedInfo_Exception
     */
    protected function _buildPath(
        Certificate $certificate,
        DateTimeInterface $dateTime = null,
        DateTimeZone $timeZone = null
    ) {
        $chain = new Chain($this->getTrustedCertificates());
        $chain->setLogger($this->getLogger());
        $chain->getExtraCertificates()->add($this->getExtraCertificates());

        /** @var Certificate[] $currentCertificates */
        $currentCertificates = [$certificate];
        /* Let's try to build the path by resolving the issuer certificates through the AIA extension. Let's do this
         * until we have a valid path or no certificate could be fetched via the AIA extension
         */
        while (($path = $chain->buildPath($certificate, $dateTime, $timeZone)) === false) {
            /** @var Certificate[] $newIssuers */
            $newIssuers = [];
            if (count($currentCertificates) === 0) {
                break;
            }

            $this->getLogger()->log(
                'Try to get intermediate certificates through the AIA extension.'
            )->increaseDepth();

            foreach ($currentCertificates as $currentCertificate) {
                /** @var AuthorityInformationAccess $aia */
                $aia = $currentCertificate->getExtensions()->get(AuthorityInformationAccess::OID);
                if ($this->_logger instanceof LoggerInterface) {
                    $this->_logger->log('AIA extension ' . ($aia ? 'found.' : 'not found.'));
                }

                if ($aia === false) {
                    $this->getLogger()->decreaseDepth();
                    break 2;
                }

                $aia->setLogger($this->_logger);
                $issuers = $aia->fetchIssuers($this->getInformationResolverManager(), $this->getIssuerCache());
                $issuerCount = count($issuers);
                $this->getLogger()->log(
                    'Resolved {issuerCount} certificate(s) through AIA extension.',
                    ['issuerCount' => $issuerCount]
                );

                if ($issuerCount === 0) {
                    $this->getLogger()->decreaseDepth();
                    break 2;
                }

                /** @var Certificate[] $issuer */
                foreach ($issuers->getAll() as $issuer) {
                    $newIssuers[] = $issuer;
                    $this->getExtraCertificates()->add($issuer);
                }
            }

            $this->getLogger()->log(
                'Re-try to build certificate path with help of resolved certificates for "{subjectName}".',
                ['subjectName' => $certificate->getSubjectName()]
            )->decreaseDepth();

            $currentCertificates = $newIssuers;
        }

        if ($path === false) {
            throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                sprintf(
                    'Certificate path could not be build for "%s". Check your trusted certificates.',
                    $certificate->getSubjectName()
                )
            );
        }

        return $path;
    }

    /**
     * Get an OCSP response by a certificate.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @return bool|SetaPDF_Signer_Ocsp_Response
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    protected function _getOcspResponse(Certificate $certificate)
    {
        $aia = $certificate->getExtensions()->get(AuthorityInformationAccess::OID);

        if (!$aia) {
            $this->getLogger()->log(
                'No AIA extension found in certificate "{subjectName}".',
                ['subjectName' => $certificate->getSubjectName()]
            );

            return false;
        }

        /** @var AuthorityInformationAccess $aia */
        $ocspUris = $aia->getOcspUris();
        if ($ocspUris === false) {
            $this->getLogger()->log('No OCSP uris found in AIA extension of certificate.');
            return false;
        }

        $ocspClient = $this->getOcspClient();
        foreach ($ocspUris as $ocspUri) {
            try {
                $this->getLogger()->log(
                    'Create OCSP request for certificate "{subjectName}".',
                    ['subjectName' => $certificate->getSubjectName()]
                );

                $request = new Request();
                $issuer = $certificate->getIssuer($this->_getAllCertificates());
                if ($issuer === null) {
                    $this->getLogger()->log(
                        'Cannot create OCSP request because no issuer certificate for "{subjectName}" was found.',
                        ['subjectName' => $certificate->getSubjectName()]
                    );

                    throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                        sprintf('Issuer cannot be found for certificate "%s".', $certificate->getSubjectName())
                    );
                }
                $request->add($certificate, $issuer);
                $ocspClient->setUrl($ocspUri);
                $response = $ocspClient->send($request);

                if ($response->isGood()) {
                    $this->getLogger()->log(
                        'Good OCSP response for certificate "{subjectName}".',
                        ['subjectName' => $certificate->getSubjectName()]
                    );

                    return $response;
                }

                $this->getLogger()->log(
                    'Bad OCSP response for certificate "{subjectName}".',
                    ['subjectName' => $certificate->getSubjectName()]
                );

            } catch (SetaPDF_Signer_Exception $e) {
                $this->getLogger()->log(
                    'An error occured during OCSP request for certificate "{subjectName}".',
                    ['subjectName' => $certificate->getSubjectName()]
                );
            }
        }

        return false;
    }

    /**
     * Get validation related information by left certificates.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_Result $result
     * @param Certificate[] $certificates
     * @param int $sources
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeZone|null $timeZone
     * @return SetaPDF_Signer_ValidationRelatedInfo_Result
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws SetaPDF_Signer_ValidationRelatedInfo_Exception
     */
    protected function _getByLeftCertificates(
        Result $result,
        array $certificates,
        $sources = self::SOURCE_OCSP_OR_CRL,
        DateTimeInterface $dateTime = null,
        DateTimeZone $timeZone = null
    )
    {
        $this->_leftCertificates = $certificates;
        $this->_crls = [];
        while (count($this->_leftCertificates) > 0) {
            $this->_processCertificate($result, $sources, $dateTime, $timeZone);
        }

        $validatedCertificates = $result->getCertificates();
        $this->getLogger()->log(
            'Resolved validation related information for {certificateCount} certificates with {crlCount} CRL(s) and ' .
            '{ocspCount} OCSP response(s).',
            [
                'certificateCount' => count($validatedCertificates),
                'crlCount' => count($result->getCrls()),
                'ocspCount' => count($result->getOcspResponses())
            ]
        );

        $this->getLogger()->log('Start to check certificates without validation related information.');

        // validate that revocation data are available for all but the root and certificates with OcspNoCheck extensions:
        /**
         * @var Certificate $certificate
         */
        foreach ($validatedCertificates as $certificate) {
            if ($certificate->getSubjectName() === $certificate->getIssuerName()) {
                $trustedIssuer = $certificate->getIssuer($this->getTrustedCertificates());
                if ($trustedIssuer && $trustedIssuer->getDigest() === $certificate->getDigest()) {
                    /** @var BasicConstraints $basicConstraints */
                    $basicConstraints = $trustedIssuer->getExtensions()->get(BasicConstraints::OID);
                    if ($basicConstraints && $basicConstraints->isCa()) {
                        $this->getLogger()->log(
                            'Certificate passed without validation related information because it is a trusted root ' .
                            'CA certificate ("{subjectName}").',
                            ['subjectName' => $certificate->getSubjectName()]
                        );
                        continue;
                    }
                }
            }

            $vriByCertificate = $result->getValidationRelatedInfoByCertificate($certificate);
            // no revocation data available?
            if ($vriByCertificate->hasCrl() === false && $vriByCertificate->hasOcspResponse() === false) {
                // check if this is correct:
                $extensions = $certificate->getExtensions();
                $ocspNoCheck = $extensions->get(OcspNoCheck::OID);
                if (!$ocspNoCheck instanceof OcspNoCheck) {
                    $this->getLogger()->log(
                        'No revocation data could be resolved for certificate "{subjectName}".',
                        ['subjectName' => $certificate->getSubjectName()]
                    );

                    throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                        sprintf(
                            'No revocation data could be resolved for certificate "%s".',
                            $certificate->getSubjectName()
                        )
                    );
                }

                $this->getLogger()->log(
                    'Certificate passed without validation related information because it has the ' .
                    'OCSP No Check Extension set ("{subjectName}").',
                    ['subjectName' => $certificate->getSubjectName()]
                );
            }
        }

        $this->_crls = null;

        return $result;
    }

    /**
     * Processes a certificate popped from the leftCertificates array.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_Result $result
     * @param int $sources
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeZone|null $timeZone
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Signer_ValidationRelatedInfo_Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function _processCertificate(
        Result $result,
        $sources = self::SOURCE_OCSP_OR_CRL,
        DateTimeInterface $dateTime = null,
        DateTimeZone $timeZone = null
    )
    {
        /** @var Certificate $certificate */
        $certificate = array_pop($this->_leftCertificates);

        if (!$dateTime instanceof DateTimeInterface) {
            $dateTime = new DateTime('now', $timeZone);
        }

        if (is_array($certificate)) {
            list($certificate, $dateTime) = $certificate;
        }

        if (!$certificate->isValidAt($dateTime, $timeZone)) {
            $this->getLogger()->log(
                'Certificate "{subjectName}" is not valid at the given time ({dateTime}, valid from: {validFrom}, valid to: {validTo}).',
                [
                    'subjectName' => $certificate->getSubjectName(),
                    'dateTime' => $dateTime,
                    'validFrom' => $certificate->getValidFrom($timeZone),
                    'validTo' => $certificate->getValidTo($timeZone)
                ]
            );

            throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                sprintf(
                    'Certificate "%s" is not valid at the given time (%s, valid from: %s, valid to: %s).',
                    $certificate->getSubjectName(),
                    $dateTime->format('Y-m-d H:i:s'),
                    $certificate->getValidFrom($timeZone)->format('Y-m-d H:i:s'),
                    $certificate->getValidTo($timeZone)->format('Y-m-d H:i:s')
                )
            );
        }

        if ($result->hasValidationRelatedInforForCertificate($certificate)) {
            return;
        }

        $this->getLogger()->log(
            'Start to process certificate "{subjectName}".',
            ['subjectName' => $certificate->getSubjectName()]
        )->increaseDepth();

        // do not build a path for a root certificate
        $selfSigned = $certificate->getSubjectName() === $certificate->getIssuerName();
        if ($selfSigned) {
            $this->getLogger()->log(
                'Certificate is a self-signed certificate ("{subjectName}").',
                ['subjectName' => $certificate->getSubjectName()]
            );

            $trustedCertificate = $certificate->getIssuer($this->getTrustedCertificates());
            if ($trustedCertificate === null) {
                $this->getLogger()->log(
                    'Self-signed certificate not located in the trusted certificate store ("{subjectName}").',
                    ['subjectName' => $certificate->getSubjectName()]
                );
                throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                    'Self-signed certificate not located in the trusted certificate strore.'
                );
            }

            $this->getLogger()->log(
                'Self-signed certificate was found in the trusted certificate store ("{subjectName}").',
                ['subjectName' => $certificate->getSubjectName()]
            );

            $certificatesPath = [$certificate];
        } else {
            $certificatesPath = array_reverse($this->_buildPath($certificate, $dateTime, $timeZone));
        }

        // check root certificate to be a self-signed root:
        $rootCertificate = $certificatesPath[0];
        if ($rootCertificate->getIssuerName() !== $rootCertificate->getSubjectName()) {
            $this->getLogger()->log(
                'Certificate path incomplete. Root certificate is not a self-signed certificate.',
                ['subjectName' => $certificate->getSubjectName(), 'issuerName' => $certificate->getIssuerName()]
            );

            throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                'Certificate path incomplete. Root certificate is not a self-signed certificate.'
            );
        }

        $certificateDigest = $certificate->getDigest();
        foreach ($certificatesPath as $_certificate) {
            if ($_certificate->getDigest() === $certificateDigest) {
                continue;
            }

            $this->_leftCertificates[] = $_certificate;
        }

        /** @var Certificate $issuer */
        $issuer = $certificate->getIssuer(new Collection($certificatesPath));

        $ocspResponse = false;
        if (!$selfSigned && ($sources & self::SOURCE_OCSP) === self::SOURCE_OCSP) {
            $this->getLogger()->log(
                'Try to get OCSP throug AIA extension for certificate "{subjectName}".',
                ['subjectName' => $certificate->getSubjectName()]
            )->increaseDepth();
            $ocspResponse = $this->_getOcspResponse($certificate);
            if ($ocspResponse instanceof OcspResponse) {
                $this->getLogger()->log('Get signing certificate of OCSP response.');

                $searchIn = new Collection($this->getTrustedCertificates());
                // If we do this we could get duplicates/same named certificates with different keys in the $searchIn
                // collection which cannot be resolved correctly by their "SubjectName".
                // $searchIn->add($this->getExtraCertificates());
                $embeddedCertificates = $ocspResponse->getCertificates();
                $searchIn->add($embeddedCertificates);
                $searchIn->add($issuer);

                $responderId = $ocspResponse->getResponderId();
                if ($responderId[0] === DistinguishedName::$separator) {
                    $ocspResponderCertificates = $searchIn->findBySubject($responderId, true);
                } else {
                    $ocspResponderCertificates = $searchIn->findByCallback(
                        static function (Certificate $certificate) use ($responderId) {
                            return sha1($certificate->getSubjectPublicKeyInfoRaw()) === $responderId;
                        }
                    );
                }

                if (count($ocspResponderCertificates) === 0) {
                    $this->getLogger()->log('Signing certificate of OCSP response could not be found.');
                    throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                        'Signing certificate of OCSP response could not be found.'
                    );
                }

                $this->getExtraCertificates()->add($embeddedCertificates);

                /** @var Certificate $ocspResponderCertificate */
                $ocspResponderCertificate = $ocspResponderCertificates->getAll()[0];

                $logContext = [
                    'subjectName' => $ocspResponderCertificate->getSubjectName(),
                    'issuerName' => $ocspResponderCertificate->getIssuerName()
                ];

                $this->getLogger()->log('Found OCSP signing certificate "{subjectName}".', $logContext);

                if (!$ocspResponse->verify($ocspResponderCertificate)) {
                    $this->getLogger()->log(
                        'Verification failed for OCSP response with signing certificate "{subjectName}".',
                        $logContext
                    );
                    throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                        'Verification failed for OCSP response.'
                    );
                }

                $this->getLogger()->log(
                    'Verification successful for OCSP response with signing certificate "{subjectName}".',
                    $logContext
                );

                if ($ocspResponderCertificate->getDigest() === $certificate->getDigest()) {
                    $this->getLogger()->log(
                        'OCSP signing certificate "{subjectName}" is the same certificate that is currently processed.'
                        . ' Ignore this OCSP response.',
                        $logContext
                    );

                    $ocspResponse = false;

                } elseif ($ocspResponderCertificate->getDigest() === $issuer->getDigest()) {
                    $this->getLogger()->log(
                        'OCSP signing certificate "{subjectName}" is the issuer of the currently processed'
                        . ' certificate.',
                        $logContext
                    );

                } else {
                    /** @var ExtendedKeyUsage $extension */
                    $extension = $ocspResponderCertificate->getExtensions()->get(ExtendedKeyUsage::OID);
                    if (!$extension || !$extension->is(ExtendedKeyUsage::PURPOSE_OCSP_SIGNING)) {
                        $this->getLogger()->log(
                            'Signing certificate of OCSP response has no extended key usage (OCSP Signing).',
                            $logContext
                        );

                        throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                            'OCSP signing certificate missing extended key usage (OCSP Signing).'
                        );
                    }

                    $this->_leftCertificates[] = [$ocspResponderCertificate, new DateTime()];

                    $this->getLogger()->log('Certificate saved for further processing ("{subjectName}").', $logContext);
                }
            } else {
                $this->getLogger()->log(
                    'No OCSP available for certificate "{subjectName}".',
                    ['subjectName' => $certificate->getSubjectName()]
                );
            }

            $this->getLogger()->decreaseDepth();
        }

        $crl = false;
        // now try CRL
        if (
            !$selfSigned &&
            (($sources & self::SOURCE_CRL) === self::SOURCE_CRL) &&
            ($ocspResponse === false || (($sources & 1) === 1))
        ) {
            $this->getLogger()->log(
                'Try to get CRL for certificate "{subjectName}".',
                ['subjectName' => $certificate->getSubjectName()]
            );

            /** @var CrlDisributionPoint $crlDistributionPoint */
            $crlDistributionPoint = $certificate->getExtensions()->get(CrlDisributionPoint::OID);
            if ($crlDistributionPoint) {
                $crlDistributionPoint->setLogger($this->getLogger());

                $this->getLogger()->log(
                    'Certificate has CRL Distribution Point extension ("{subjectName}").',
                    ['subjectName' => $certificate->getSubjectName()]
                );
                $pubKeyHash = md5($issuer->getSubjectPublicKeyInfoRaw());
                if (isset($this->_crls[$pubKeyHash])) {
                    $this->getLogger()->log(
                        'CRL of the certificate issuer was already resolved ("{issuerName}").',
                        ['issuerName' => $certificate->getIssuerName()]
                    );
                    /** @var Crl $crl */
                    $crl = $this->_crls[$pubKeyHash];
                    if ($crl->isRevoked($certificate)) {
                        $this->getLogger()->log(
                            'Certificate is listed as revoked in a CRL ("{subjectName}").',
                            ['subjectName' => $certificate->getSubjectName()]
                        );

                        throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                            sprintf('Certificate "%s" is listed as revoked in a CRL.', $certificate->getSubjectName())
                        );
                    }

                    $this->getLogger()->log(
                        'Certificate was not found in the CRL ("{subjectName}").',
                        ['subjectName' => $certificate->getSubjectName()]
                    );

                } else {
                    $crl = $crlDistributionPoint->fetchCrl(
                        $this->getInformationResolverManager(),
                        $this->getCrlCache()
                    );
                    if ($crl instanceof Crl) {
                        if ($crl->isRevoked($certificate)) {
                            $this->getLogger()->log(
                                'Certificate is listed as revoked in a CRL ("{subjectName}").',
                                ['subjectName' => $certificate->getSubjectName()]
                            );

                            throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(sprintf(
                                'Certificate "%s" is listed as revoked on a CRL.',
                                $certificate->getSubjectName()
                            ));
                        }

                        $issuerName = $crl->getIssuerName();
                        $crlIssuer = $this->_getAllCertificates()->findBySubject($issuerName, true);
                        if (count($crlIssuer) === 0) {
                            $this->getLogger()->log('Signing certificate of CRL could not be found.');
                            throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                                'Signing certificate of CRL could not be found.'
                            );
                        }

                        $crlIssuer = $crlIssuer->getAll()[0];
                        $logContext = ['subjectName' => $crlIssuer->getSubjectName()];
                        $this->getLogger()->log(
                            'Found CLR signing certificate "{subjectName}".',
                            $logContext
                        );

                        if (!$crl->verify($crlIssuer)) {
                            $this->getLogger()->log(
                                'Verification failed for CRL with signing certificate "{subjectName}".',
                                $logContext
                            );
                            throw new SetaPDF_Signer_ValidationRelatedInfo_Exception('CRL verification failed.');
                        }

                        $this->getLogger()->log(
                            'Verification successful for CRL with signing certificate "{subjectName}".',
                            $logContext
                        );

                        $this->_leftCertificates[] = $crlIssuer;

                        $this->getLogger()->log(
                            'Certificate saved for further processing ("{subjectName}").', $logContext
                        );

                        $this->_crls[md5($crlIssuer->getSubjectPublicKeyInfoRaw())] = $crl;
                    }
                }
            } else {
                $this->getLogger()->log(
                    'No CRL endpoint available for certificate "{subjectName}".',
                    ['subjectName' => $certificate->getSubjectName()]
                );
            }
        }

        $this->getLogger()->log(
            'Verification data (CRL: {crl}, OCSP: {ocsp}) for certificate "{subjectName}" resolved.',
            [
                'subjectName' => $certificate->getSubjectName(),
                'crl' => $crl !== false,
                'ocsp' => $ocspResponse !== false
            ]
        )->decreaseDepth();

        $result->add(new CertificateResult($certificate, $ocspResponse ?: null, $crl ?: null));
    }

    /**
     * Get the OCSP client instance.
     *
     * @return SetaPDF_Signer_Ocsp_Client
     */
    public function getOcspClient()
    {
        if ($this->_ocspClient === null) {
            $this->_ocspClient = new Client();
            $this->_ocspClient->setLogger($this->getLogger());
        }

        return $this->_ocspClient;
    }

    /**
     * Get validation related information by a certificate.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @param int $sources
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeZone|null $timeZone
     * @param SetaPDF_Signer_ValidationRelatedInfo_Result|null $result
     * @return SetaPDF_Signer_ValidationRelatedInfo_Result
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws SetaPDF_Signer_ValidationRelatedInfo_Exception
     */
    public function getByCertificate(
        Certificate $certificate,
        $sources = self::SOURCE_OCSP_OR_CRL,
        DateTimeInterface $dateTime = null,
        DateTimeZone $timeZone = null,
        Result $result = null
    )
    {
        if ($result === null) {
            $result = new Result();
        }

        $this->_getByLeftCertificates(
            $result,
            [$certificate],
            $sources,
            $dateTime,
            $timeZone
        );

        return $result;
    }

    /**
     * Get validation related information by a SignedData object.
     *
     * @param SetaPDF_Signer_Cms_SignedData $signedData
     * @param int $sources
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeZone|null $timeZone
     * @param SetaPDF_Signer_ValidationRelatedInfo_Result|null $result
     * @return SetaPDF_Signer_ValidationRelatedInfo_ResultBySignedData
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Signer_ValidationRelatedInfo_Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getBySignedData(
        SignedData $signedData,
        $sources = self::SOURCE_OCSP_OR_CRL,
        DateTimeInterface $dateTime = null,
        DateTimeZone $timeZone = null,
        Result $result = null
    )
    {
        if ($result === null) {
            $result = new Result();
        }

        $all = $this->_getAllCertificates();
        $all->add($signedData->getCertificates());
        $signingCertificate = $signedData->getSigningCertificate($all);

        $certificates = [$signingCertificate];

        $timestampAttribute = $signedData->getUnsignedAttribute('1.2.840.113549.1.9.16.2.14');
        if ($timestampAttribute) {
            $tspToken = new TspToken($timestampAttribute->getChild(0));
            $tspCertificate = $tspToken->getSigningCertificate($this->getExtraCertificates());

            if (!$tspToken->verify($tspCertificate)) {
                throw new SetaPDF_Signer_ValidationRelatedInfo_Exception('Timestamp certificate verification failed.');
            }

            // Check if timestamp belongs to the signature it is part of
            if (!$tspToken->verifyMessageImprint($signedData->getSignatureValue(false))) {
                throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                    'Timestamp has a different message imprint than the outer CMS container.'
                );
            }

            $certificates[] = $tspCertificate;
        }

        $result = $this->_getByLeftCertificates(
            $result,
            $certificates,
            $sources,
            $dateTime,
            $timeZone
        );

        $result = new SetaPDF_Signer_ValidationRelatedInfo_ResultBySignedData(
            $result,
            $signingCertificate,
            $signedData
        );

        return $result;
    }

    /**
     * Get validation related information by a signatue field name.
     *
     * @param SetaPDF_Core_Document $document
     * @param string $fieldName
     * @param int $sources
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeZone|null $timeZone
     * @param SetaPDF_Signer_ValidationRelatedInfo_Result|null $result
     * @return SetaPDF_Signer_ValidationRelatedInfo_Result|SetaPDF_Signer_ValidationRelatedInfo_ResultByField|SetaPDF_Signer_ValidationRelatedInfo_ResultBySignedData
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Parser_Pdf_InvalidTokenException
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Signer_ValidationRelatedInfo_Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getByFieldName(
        SetaPDF_Core_Document $document,
        $fieldName,
        $sources = self::SOURCE_OCSP_OR_CRL,
        DateTimeInterface $dateTime = null,
        DateTimeZone $timeZone = null,
        Result $result = null
    )
    {
        $integrityResult = IntegrityResult::create($document, $fieldName);
        if (!$integrityResult->isValid()) {
            throw new SetaPDF_Signer_ValidationRelatedInfo_Exception(
                'Signature integrity is invalid.'
            );
        }

        return $this->getByIntegrityResult(
            $integrityResult,
            $sources,
            $dateTime,
            $timeZone,
            $result
        );
    }

    /**
     * Get validation related information by an integrity result.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_IntegrityResult $integrityResult
     * @param int $sources
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeZone|null $timeZone
     * @param SetaPDF_Signer_ValidationRelatedInfo_Result|null $result
     * @return SetaPDF_Signer_ValidationRelatedInfo_Result|SetaPDF_Signer_ValidationRelatedInfo_ResultByField
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     * @throws SetaPDF_Signer_ValidationRelatedInfo_Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getByIntegrityResult(
        IntegrityResult $integrityResult,
        $sources = self::SOURCE_OCSP_OR_CRL,
        DateTimeInterface $dateTime = null,
        DateTimeZone $timeZone = null,
        Result $result = null
    )
    {
        if ($result === null) {
            $result = new Result();
        }

        $this->getExtraCertificates()->add($integrityResult->getSignedData()->getCertificates());

        $result = new SetaPDF_Signer_ValidationRelatedInfo_ResultByField(
            $this->getBySignedData(
                $integrityResult->getSignedData(),
                $sources,
                $dateTime,
                $timeZone,
                $result
            ),
            $integrityResult
        );

        return $result;
    }
}