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

use SetaPDF_Signer_X509_Extension_BasicConstraints as BasicConstraints;
use SetaPDF_Signer_X509_Collection as Collection;
use SetaPDF_Signer_X509_CollectionInterface as CollectionInterface;
use SetaPDF_Signer_X509_Certificate as Certificate;
use SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface as LoggerInterface;
use SetaPDF_Signer_ValidationRelatedInfo_Logger as Logger;

/**
 * Helper class to build certificate paths.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Chain
{
    /**
     * The trusted certificates.
     *
     * @var Collection
     */
    protected $_trustedCertificates;

    /**
     * The extra certificates.
     *
     * @var Collection
     */
    protected $_extraCertificates;

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * The constructor.
     *
     * @param null|string|Certificate|Certificate[]|CollectionInterface $trustedCertificates $trustedCertificates
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function __construct($trustedCertificates = null)
    {
        if ($trustedCertificates !== null) {
            $this->getTrustedCertificates()->add($trustedCertificates);
        }
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
     * Get the trusted certificates collection instance.
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
     * Get the extra certificates collection instance.
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
     * Find issuers by a certificate.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    protected function _findIssuer(Certificate $certificate)
    {
        $result = new Collection();

        // Check if certificate is self signed
        if ($certificate->getSubjectName() === $certificate->getIssuerName()) {
            if (!$certificate->verify()) {
                $this->getLogger()->log(
                    'Verification failed for self-signed certificate "{subjectName}".',
                    ['subjectName' => $certificate->getSubjectName()]
                );
                return $result;
            }

            $this->getLogger()->log(
                'Verification successful of self-signed certificate "{subjectName}".',
                ['subjectName' => $certificate->getSubjectName()]
            );

            $result->add($certificate);
            return $result;
        }

        $all = new Collection($this->getTrustedCertificates());
        $all->add($this->getExtraCertificates());

        $result = $certificate->getIssuers($all);

        // verify the certificates
        $result = $result->findByCallback(
            function(Certificate $possibleIssuerCertificate) use ($certificate) {
                $logContext = [
                    'subjectName' => $certificate->getSubjectName(),
                    'issuerName' => $possibleIssuerCertificate->getSubjectName()
                ];

                $this->getLogger()->log(
                    'Start verification for certificate "{subjectName}" with issuer certificate "{issuerName}".',
                    $logContext
                );

                $verified = $certificate->verify($possibleIssuerCertificate);

                if ($verified) {
                    $this->getLogger()->log(
                        'Certificate verification successful for certificate "{subjectName}" with issuer certificate "{issuerName}".',
                        $logContext
                    );
                } else {
                    $this->getLogger()->log(
                        'Certificate verification failed for certificate "{subjectName}" with issuer certificate "{issuerName}".',
                        $logContext
                    );
                }

                return $verified;
            }
        );

        return $result;
    }

    /**
     * Build all possible paths.
     *
     * @param Certificate $certificate
     * @param $paths
     * @param Certificate[] $currentPath
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    protected function _buildPaths(Certificate $certificate, &$paths, $currentPath = [])
    {
        $this->getLogger()->log(
            'Start to build paths for "{subjectName}".',
            ['subjectName' => $certificate->getSubjectName()]
        )->increaseDepth();

        // catch recursion
        if (count($currentPath)) {
            $digest = $certificate->getDigest('md5', true);
            foreach ($currentPath as $_certificate) {
                if ($digest === $_certificate->getDigest('md5', true)) {
                    $this->getLogger()->log(
                        'Recursion in certificate chain recognized "{subjectName}".',
                        ['subjectName' => $certificate->getSubjectName()]
                    );
                    return;
                }
            }
        }

        $possibleIssuers = $this->_findIssuer($certificate);
        $possibleIssuersCount = count($possibleIssuers);
        $this->getLogger()->log(
            'Found {issuerCount} possible issuer(s) for certificate "{subjectName}".',
            [
                'issuerCount' => $possibleIssuersCount,
                'subjectName' => $certificate->getSubjectName()
            ]
        )->decreaseDepth();

        if ($possibleIssuersCount === 0) {
            return;
        }

        $currentPath[] = $certificate;

        foreach ($possibleIssuers->getAll() as $possibleIssuer) {
            if ($possibleIssuer->getSubjectName() === $certificate->getSubjectName()) {
                $paths[] = $currentPath;
                return;
            }

            $this->_buildPaths($possibleIssuer, $paths, $currentPath);
        }
    }

    /**
     * Build all possible paths.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @return SetaPDF_Signer_X509_Certificate[]
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function buildPaths(Certificate $certificate)
    {
        $paths = [];
        $this->_buildPaths($certificate, $paths);

        return $paths;
    }

    /**
     * Build a valid certificate path.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @param DateTimeInterface|null $dateTime If a date time is given only certificates which are valid at this will be
     *                                         part of the path.
     * @param DateTimeZone|null $timeZone
     * @return false|SetaPDF_Signer_X509_Certificate[]
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws Exception
     */
    public function buildPath(
        Certificate $certificate,
        DateTimeInterface $dateTime = null,
        DateTimeZone $timeZone = null
    ) {
        /** @var Certificate[][] $paths */
        $paths = [];
        $this->_buildPaths($certificate, $paths);

        $this->getLogger()->log(
            '{pathCount} certificate path(s) created.', ['pathCount' => count($paths)]
        );

        foreach ($paths as $i => $path) {
            $this->getLogger()->log(
                'Validate path #{pathIndex}', ['pathIndex' => $i]
            );

            // remove self-signed "paths" or paths without
            if (count($path) <= 1) {
                $this->getLogger()->log('Removed self-signed path.');
                unset($paths[$i]);
                continue;
            }

            // check if root certificates ends in a certificate in the trusted certificates property
            $rootCert = $path[count($path) - 1];
            if (!$this->getTrustedCertificates()->contains($rootCert)) {
                $this->getLogger()->log(
                    'Root certificate of path doesn\'t end in a trusted root certificate ({rootCert}).',
                    ['rootCert' => $rootCert->getSubjectName()]
                );
                unset($paths[$i]);
                continue;
            }

            // remove path if any certificate but the leaf is not a CA certificate
            foreach (array_slice($path, 1) as $_certificate) {
                /** @var BasicConstraints $extension */
                $extension = $_certificate->getExtensions()->get(BasicConstraints::OID);
                if (!$extension || $extension->isCa() === false) {
                    $this->getLogger()->log(
                        'Intermediate certificate is not a CA certificate ({subjectName}).',
                        ['subjectName' => $_certificate->getSubjectName()]
                    );
                    unset($paths[$i]);
                    continue 2;
                }
            }

            // remove paths which uses certificates which are either expired or not valid yet
            if ($dateTime !== null) {
                foreach ($path as $_certificate) {
                    if (!$_certificate->isValidAt($dateTime, $timeZone)) {
                        $_dateTime = DateTimeImmutable::createFromMutable($dateTime);
                        if ($timeZone !== null) {
                            $_dateTime = $_dateTime->setTimezone($timeZone);
                        }

                        $this->getLogger()->log(
                            'Certificate "{subjectName}" is not valid at the given time ({dateTime}, valid from: {validFrom}, valid to: {validTo}).',
                            [
                                'subjectName' => $_certificate->getSubjectName(),
                                'dateTime' => $_dateTime,
                                'validFrom' => $_certificate->getValidFrom($timeZone),
                                'validTo' => $_certificate->getValidTo($timeZone)
                            ]
                        );
                        unset($paths[$i]);
                        continue 2;
                    }
                }
            } else {
                $this->getLogger()->log(
                    'Validation time frame is ignored.'
                );
            }

            $this->getLogger()->log(
                'Path #{pathIndex} is a strong candidate with the length of {pathLength} certificates.',
                ['pathIndex' => $i, 'pathLength' => count($paths[$i])]
            );
            // Info: Chain cycles are already resolved during _buildPaths()
        }

        $paths = array_values($paths);

        if (count($paths) === 0) {
            return false;
        }

        // TODO: Remove paths if count($paths) > 1 which includes expired certificates but no $dateTime value was passed

        // we currently simply use the shortest path
        $pathLengths = array_map('count', $paths);
        /** @var int $index */
        $index = array_search(min($pathLengths), $pathLengths, true);

        $this->getLogger()->log(
            '{pathsCount} strong path(s) found. Shortest path is used.', ['pathsCount' => count($paths)]
        );

        return $paths[$index];
    }
}