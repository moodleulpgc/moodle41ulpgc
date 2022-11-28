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
use SetaPDF_Signer_X509_Extension_Extension as Extension;
use SetaPDF_Signer_Cms_CertsOnly as CertsOnly;
use SetaPDF_Signer_X509_Certificate as Certificate;
use SetaPDF_Signer_X509_Collection as Collection;
use SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface as LoggerInterface;
use SetaPDF_Signer_ValidationRelatedInfo_Logger as Logger;

/**
 * Class representing the X509 Certificate Authority Information Access extension.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_AuthorityInformationAccess extends Extension
{
    /**
     * Extension OID.
     *
     * @var string
     */
    const OID = '1.3.6.1.5.5.7.1.1';

    /**
     * Cache for access location URIs.
     *
     * @var array
     */
    private $_accessLocationUris = [];

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $_logger;

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
     * Get all URIs from the extension.
     *
     * @return array
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    private function _getAllAccessLocationUris()
    {
        if (count($this->_accessLocationUris) !== 0) {
            return $this->_accessLocationUris;
        }

        $accessDescriptions = $this->getExtensionValue();

        foreach ($accessDescriptions->getChildren() as $accessDescription) {
            $accessMethod = SetaPDF_Signer_Asn1_Oid::decode($accessDescription->getChild(0)->getValue());
            // uniformResourceIdentifier [6]
            if ($accessDescription->getChild(1)->getIdent() & "\x06") {
                if (!isset($this->_accessLocationUris[$accessMethod])) {
                    $this->_accessLocationUris[$accessMethod] = [];
                }

                $this->_accessLocationUris[$accessMethod][] = $accessDescription->getChild(1)->getValue();
            }
        }

        return $this->_accessLocationUris;
    }

    /**
     * Get URIs by OID.
     *
     * @param $oid
     * @return bool|array
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    private function _getAccessLocationUris($oid)
    {
        $uris = $this->_getAllAccessLocationUris();
        if (!isset($uris[$oid])) {
            return false;
        }

        return $uris[$oid];
    }

    /**
     * Get OCSP URIs.
     *
     * @return false|string[]
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getOcspUris()
    {
        return $this->_getAccessLocationUris('1.3.6.1.5.5.7.48.1');
    }

    /**
     * Get certificate authority issuers URIs.
     *
     * @return false|string[]
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getCertificationAuthorityIssuerUris()
    {
        return $this->_getAccessLocationUris('1.3.6.1.5.5.7.48.2');
    }

    /**
     * Fetch all data through certificate authority issuers URIs.
     *
     * @param SetaPDF_Signer_InformationResolver_Manager $informationResolverManager
     * @param CacheInterface|null $cache
     * @return SetaPDF_Signer_X509_Collection
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws Exception
     */
    public function fetchIssuers(
        SetaPDF_Signer_InformationResolver_Manager $informationResolverManager,
        Psr\SimpleCache\CacheInterface $cache = null
    )
    {
        $issuers = new Collection();

        $uris = $this->getCertificationAuthorityIssuerUris();
        if (!$uris) {
            $this->getLogger()->log('No issuer URIs found');
            return $issuers;
        }

        // query a same named file only once
        $fileNameFetched = [];

        $this->getLogger()->log('{issuerUriCount} issuer URI(s) found.', ['issuerUriCount' => count($uris)]);

        foreach ($uris as $uri) {
            /** @var string $fileName */
            $fileName = pathinfo($uri, PATHINFO_BASENAME);
            if (isset($fileNameFetched[$fileName])) {
                $this->getLogger()->log(
                    'Ignore URI "{uri}" because file "{fileName}" was already resolved in a previous run.',
                    ['fileName' => $fileName, 'uri' => $uri]
                );
                continue;
            }

            if ($cache instanceof Psr\SimpleCache\CacheInterface) {
                $cacheKey = md5($uri);
                $uriResult = $cache->get($cacheKey);
                if ($uriResult !== null) {
                    $this->getLogger()->log('URI "{uri}" resolved from cache.', ['uri' => $uri]);
                    $issuers->add($uriResult);
                    $fileNameFetched[$fileName] = true;
                    continue;
                }
            }

            $uriResult = new Collection();

            try {
                list($contentType, $data) = $informationResolverManager->resolve($uri);
            } catch (SetaPDF_Signer_InformationResolver_NoResolverFoundException $e) {
                continue;
            }

            $urlPath = parse_url($uri, PHP_URL_PATH);
            $extension = pathinfo($urlPath, PATHINFO_EXTENSION);
            switch ($contentType) {
                case 'application/pkix-cert':
                case 'application/pkix-ca':
                case 'application/x-x509-ca-cert':
                case 'application/x-x509-user-cert':
                    $uriResult->add(new Certificate($data));
                    $this->getLogger()->log(
                        'Interpreted data by content-type ({contentType}) as X509 certificate.',
                        ['contentType' => $contentType]
                    );
                    break;
                case 'application/pkcs7-mime':
                    $certsOnly = new CertsOnly($data);
                    $certs = $certsOnly->getAll();
                    $uriResult->add($certs);
                    $this->getLogger()->log(
                        'Interpreted data by content-type ({contentType}) as "CertsOnly" container with {certsCount} certificates.',
                        ['contentType' => $contentType, 'certsCount' => count($certs)]
                    );
                    break;
                default:
                    switch ($extension) {
                        case 'crt':
                        case 'cer':
                        case 'pem':
                            $uriResult->add(new Certificate($data));
                            $this->getLogger()->log(
                                'Interpreted resolved data by file extension ({extension}) as X509 certificate.',
                                ['extension' => $extension]
                            );
                            break;
                        case 'p7c':
                        case 'p7b':
                            $certsOnly = new CertsOnly($data);
                            $certs = $certsOnly->getAll();
                            $uriResult->add($certs);
                            $this->getLogger()->log(
                                'Interpreted data by file extension ({extension}) as "CertsOnly" container with {certsCount} certificates.',
                                ['extension' => $extension, 'certsCount' => count($certs)]
                            );
                            break;
                        default:
                            $this->getLogger()->log(
                                'Unsupported content-type ({contentType}) and extension ({extension}).',
                                ['contentType' => $contentType, 'extension' => $extension]
                            );
                            throw new SetaPDF_Signer_Exception(
                                sprintf('Unsupported content-type (%s) and extension (%s).', $contentType, $extension)
                            );
                    }
            }

            if ($cache instanceof Psr\SimpleCache\CacheInterface) {
                $minValidTo = null;
                /** @var Certificate $cert */
                foreach ($uriResult->getAll() as $cert) {
                    $validTo = $cert->getValidTo();
                    $minValidTo = ($minValidTo === null) ? $validTo : min($minValidTo, $validTo);
                }

                $ttl = (new DateTime())->diff($minValidTo);

                $this->getLogger()->log(
                    'Added result of URI ({uri}) to cache.',
                    ['uri' => $uri, 'ttl' => $ttl]
                );

                /** @noinspection PhpUndefinedVariableInspection */
                $cache->set($cacheKey, $uriResult, $ttl);
            }

            $issuers->add($uriResult);
            $fileNameFetched[$fileName] = true;
        }

        return $issuers;
    }
}