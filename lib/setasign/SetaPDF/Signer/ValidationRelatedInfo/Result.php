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
use SetaPDF_Signer_ValidationRelatedInfo_CertificateResult as CertificateResult;

/**
 * Class representing a validation related information result.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_ValidationRelatedInfo_Result
{
    /**
     * All validation related informationy by certificates.
     *
     * @var SetaPDF_Signer_ValidationRelatedInfo_CertificateResult[]
     */
    protected $_vri = [];

    /**
     * Get all certificates indexed by their digest.
     *
     * @return SetaPDF_Signer_X509_Certificate[]
     * @see Certificate::getDigest()
     */
    public function getCertificates()
    {
        $result = [];
        foreach ($this->_vri as $digest => $certificateResult) {
            $result[$digest] = $certificateResult->getCertificate();
        }

        return $result;
    }

    /**
     * Get all OCSP responses indexed by their certificate digests.
     *
     * @return SetaPDF_Signer_Ocsp_Response[]
     * @see Certificate::getDigest()
     */
    public function getOcspResponses()
    {
        $result = [];
        foreach ($this->_vri as $digest => $certificateResult) {
            if (!$certificateResult->hasOcspResponse()) {
                continue;
            }

            $result[$digest] = $certificateResult->getOcspResponse();
        }

        return $result;
    }

    /**
     * Get all CRLs indexed by their certificate digests.
     *
     * @return SetaPDF_Signer_X509_Crl[]
     */
    public function getCrls()
    {
        $result = [];
        foreach ($this->_vri as $digest => $certificateResult) {
            if (!$certificateResult->hasCrl()) {
                continue;
            }

            $crl = $certificateResult->getCrl();
            $result[$crl->getDigest()] = $crl;
        }

        return $result;
    }

    /**
     * Get validation related information by a certificate.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @return SetaPDF_Signer_ValidationRelatedInfo_CertificateResult|false
     */
    public function getValidationRelatedInfoByCertificate(Certificate $certificate)
    {
        if (!$this->hasValidationRelatedInforForCertificate($certificate)) {
            return false;
        }

        return $this->_vri[$certificate->getDigest()];
    }

    /**
     * Checks whether this instance has validation related information of a specific certificate.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @return bool
     */
    public function hasValidationRelatedInforForCertificate(Certificate $certificate)
    {
        return isset($this->_vri[$certificate->getDigest()]);
    }

    /**
     * Add a certificate result.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_CertificateResult $result
     */
    public function add(CertificateResult $result)
    {
        $this->_vri[$result->getCertificate()->getDigest()] = $result;
    }
}