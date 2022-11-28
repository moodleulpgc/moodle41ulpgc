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

use SetaPDF_Signer_Asn1_Time as Time;
use SetaPDF_Signer_Ocsp_CertId as CertId;

/**
 * Class representing a SingleResponse structure of an OCSP response.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Ocsp_SingleResponse
{
    /**
     * Certificate status constant.
     *
     * @var int
     */
    const CERT_STATUS_GOOD = 0;

    /**
     * Certificate status constant.
     *
     * @var int
     */
    const CERT_STATUS_REVOKED = 1;

    /**
     * Certificate status constant.
     *
     * @var int
     */
    const CERT_STATUS_UNKNOWN = 2;

    /**
     * The ASN.1 element of the single response.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_singleResponse;

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_Asn1_Element $singleResponse
     */
    public function __construct(SetaPDF_Signer_Asn1_Element $singleResponse)
    {
        if ($singleResponse->getChildCount() < 3) {
            throw new InvalidArgumentException('Invalid SingleResponse structure.');
        }

        $this->_singleResponse = $singleResponse;
    }

    /**
     * Get the CertId instance of this single response.
     *
     * @return SetaPDF_Signer_Ocsp_CertId
     */
    public function getCertId()
    {
        return new CertId($this->_singleResponse->getChild(0));
    }

    /**
     * Get the certificate status element.
     *
     * @return bool|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Exception
     */
    protected function _getCertStatus()
    {
        $certStatus = $this->_singleResponse->getChild(1);
        if (
            ($certStatus->getIdent() & SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC) !==
            SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC
        ) {
            throw new SetaPDF_Signer_Exception('Invalid certStatus in SingleResponse structure.');
        }

        return $certStatus;
    }

    /**
     * Get the certificate status.
     *
     * @return int
     * @throws SetaPDF_Signer_Exception
     */
    public function getCertStatus()
    {
        $certStatus = $this->_getCertStatus();

        // good [0]
        if ($certStatus->getIdent() === SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC) {
            return self::CERT_STATUS_GOOD;
        }

        return ord(
            $certStatus->getIdent() ^
            (SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED)
        );
    }

    /**
     * Get the DateTime value of the "ThisUpdate" field.
     *
     * @return DateTime
     * @throws SetaPDF_Signer_Exception
     */
    public function getThisUpdate()
    {
        $thisUpdate = $this->_singleResponse->getChild(2);
        if ($thisUpdate->getIdent() !== SetaPDF_Signer_Asn1_Element::GENERALIZED_TIME) {
            throw new SetaPDF_Signer_Exception('Invalid thisUpdate type in SingleResponse structure.');
        }

        return Time::decode($thisUpdate);
    }

    /**
     * Get the DateTime value of the "NextUpdate" field.
     *
     * @return bool|DateTime
     */
    public function getNextUpdate()
    {
        $nextUpdate = $this->_singleResponse->getChild(3);
        if ($nextUpdate->getIdent() ===
            (SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED)
        ) {
            $nextUpdate = $nextUpdate->getChild(0);

            if (!$nextUpdate || $nextUpdate->getIdent() !== SetaPDF_Signer_Asn1_Element::GENERALIZED_TIME) {
                return false;
            }

            return Time::decode($nextUpdate);
        }

        return false;
    }

    /* TODO
    public function getRevocationInfo()
    {
        $certStatus = $this->_getCertStatus();
        $status = ord($certStatus->getIdent() ^ SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC);
        if ($status !== self::CERT_STATUS_REVOKED) {
            return false;
        }

        // TODO
    }
    */
}