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
use SetaPDF_Signer_X509_Crl as Crl;
use SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface as LoggerInterface;
use SetaPDF_Signer_ValidationRelatedInfo_Logger as Logger;

/**
 * Class representing the X509 Certificate Revocation List distribution points extension.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_CrlDisributionPoint extends Extension
{
    /**
     * Extension OID.
     *
     * @var string
     */
    const OID = '2.5.29.31';

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
     * Get all CRL URIs from the extension.
     *
     * @return string[]
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getUris()
    {
        $result = [];
        $distributionPoints = $this->getExtensionValue();

        for ($i = 0, $c = $distributionPoints->getChildCount(); $i < $c; $i++) {
            /**
             *     DistributionPoint ::= SEQUENCE {
             *         distributionPoint         [0] DistributionPointName OPTIONAL,
             *         reasons                   [1] ReasonFlags OPTIONAL,
             *         cRLIssuer                 [2] GeneralNames OPTIONAL }
             *
             *     DistributionPointName ::= CHOICE {
             *         fullName                  [0] GeneralNames,
             *         nameRelativeToCRLIssuer   [1] RelativeDistinguishedName }
             *
             *     GeneralNames ::= SEQUENCE SIZE (1..MAX) OF GeneralName
             *
             *     GeneralName ::= CHOICE {
             *         otherName                 [0] INSTANCE OF OTHER-NAME,
             *         rfc822Name                [1] IA5String,
             *         dNSName                   [2] IA5String,
             *         x400Address               [3] ORAddress,
             *         directoryName             [4] Name,
             *         ediPartyName              [5] EDIPartyName,
             *         uniformResourceIdentifier [6] IA5String,
             *         iPAddress                 [7] OCTET STRING,
             *         registeredID              [8] OBJECT IDENTIFIER }
             *
             *     RelativeDistinguishedName ::= SET OF AttributeTypeAndValue
             *
             *     AttributeTypeAndValue ::= SEQUENCE {
             *         type    AttributeType,
             *         value   AttributeValue }
             *
             *     AttributeType ::= OBJECT IDENTIFIER
             *
             *     AttributeValue ::= ANY DEFINED BY AttributeType
             *
             */
            $distributionPoint = $distributionPoints->getChild($i);
            $distributionPointName = $distributionPoint->getChild(0);
            // we only support "fullName"
            if (!($distributionPointName->getIdent() & "\xA0")) {
                continue;
            }

            for ($ii = 0, $cc = $distributionPointName->getChildCount(); $ii < $cc; $ii++) {
                $fullNames = $distributionPointName->getChild($ii);
                // we only support "GeneralNames"
                if (!($fullNames->getChild(0)->getIdent() & "\xA0")) {
                    continue;
                }

                for ($iii = 0, $ccc = $fullNames->getChildCount(); $iii < $ccc; $iii++) {
                    $fullName = $fullNames->getChild($iii);

                    // uniformResourceIdentifier [6]
                    if ($fullName->getIdent() & "\x06") {
                        $uri = $fullName->getValue();
                        if ($uri) {
                            $result[] = $uri;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Fetch all CRLs.
     *
     * @param SetaPDF_Signer_InformationResolver_Manager $informationResolverManager
     * @param CacheInterface $cache
     * @return false|Crl[]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function fetchCrls(
        SetaPDF_Signer_InformationResolver_Manager $informationResolverManager,
        CacheInterface $cache = null
    )
    {
        $uris = $this->getUris();

        $this->getLogger()->log(
            'Found {uriCount} CRL Distriubution points. Try to fetch data from all.',
            ['uriCount' => count($uris)]
        );

        return $this->_fetchCrls(false, $uris, $informationResolverManager, $cache);
    }

    /**
     * Fetch the first found CRL.
     *
     * @param SetaPDF_Signer_InformationResolver_Manager $informationResolverManager
     * @param CacheInterface|null $cache
     * @return false|Crl
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function fetchCrl(
        SetaPDF_Signer_InformationResolver_Manager $informationResolverManager,
        CacheInterface $cache = null
    )
    {
        $uris = $this->getUris();

        $this->getLogger()->log(
            'Found {uriCount} CRL Distriubution points. Try to fetch data from one of them.',
            ['uriCount' => count($uris)]
        );

        return $this->_fetchCrls(true, $uris, $informationResolverManager, $cache);
    }

    /**
     * Fetches all or only the first found CRL.
     *
     * @param boolean $returnFirst
     * @param array $uris
     * @param SetaPDF_Signer_InformationResolver_Manager $informationResolverManager
     * @param CacheInterface|null $cache
     * @return false|Crl|Crl[]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function _fetchCrls(
        $returnFirst,
        $uris,
        SetaPDF_Signer_InformationResolver_Manager $informationResolverManager,
        CacheInterface $cache = null
    )
    {
        $crls = [];
        foreach ($uris as $uri) {
            $this->getLogger()->log(
                'Resolve data from CRL Distriubution point "{uri}".',
                ['uri' => $uri]
            );

            if ($cache instanceof CacheInterface) {
                $cacheKey = md5($uri);
                $uriResult = $cache->get($cacheKey);
                if ($uriResult !== null) {
                    $this->getLogger()->log(
                        'Data of the CRL Distribution point "{uri}" was found in the cache.',
                        ['uri' => $uri]
                    );

                    if ($returnFirst) {
                        return $uriResult;
                    }

                    $crls[$uriResult->getDigest()] = $uriResult;
                    continue;
                }
            }

            try {
                $response = $informationResolverManager->resolve($uri);
            } catch (SetaPDF_Signer_InformationResolver_NoResolverFoundException $e) {
                continue;
            }

            $crl = new Crl($response[1]);
            $this->getLogger()->log('Interpret data as a CRL.');

            if ($returnFirst) {
                return $crl;
            }

            if ($cache instanceof CacheInterface) {
                $nextUpdate = $crl->getNextUpdate();
                $ttl = $nextUpdate ? (new DateTime())->diff($nextUpdate) : null;
                /** @noinspection PhpUndefinedVariableInspection */
                $cache->set($cacheKey, $crl, $ttl);

                $this->getLogger()->log(
                    'Added CRL resolved from "{uri}" to cache.',
                    ['uri' => $uri]
                );
            }

            $crls[$crl->getDigest()] = $crl;
        }

        if ($returnFirst) {
            return false;
        }

        return $crls;
    }
}