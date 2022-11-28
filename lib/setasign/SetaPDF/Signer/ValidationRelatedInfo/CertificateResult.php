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
use SetaPDF_Signer_Ocsp_Response as OcspResponse;
use SetaPDF_Signer_X509_Crl as Crl;

/**
 * Class representing the validation related information result of a certificate.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_ValidationRelatedInfo_CertificateResult
{
    /**
     * The certificate.
     *
     * @var SetaPDF_Signer_X509_Certificate
     */
    protected $_certificate;

    /**
     * The OCSP response for the certificate.
     *
     * @var SetaPDF_Signer_Ocsp_Response
     */
    protected $_ocspResponse;

    /**
     * The CRL for the certificate.
     *
     * @var SetaPDF_Signer_X509_Crl
     */
    protected $_crl;

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @param SetaPDF_Signer_Ocsp_Response|null $ocspResponse
     * @param SetaPDF_Signer_X509_Crl|null $crl
     */
    public function __construct(
        Certificate $certificate,
        OcspResponse $ocspResponse = null,
        Crl $crl = null
    )
    {
        $this->_certificate = $certificate;
        $this->_ocspResponse = $ocspResponse;
        $this->_crl = $crl;
    }

    /**
     * Get the certificate.
     *
     * @return SetaPDF_Signer_X509_Certificate
     */
    public function getCertificate()
    {
        return $this->_certificate;
    }

    /**
     * Checks whether an OCSP response is available or not.
     *
     * @return bool
     */
    public function hasOcspResponse()
    {
        return $this->_ocspResponse !== null;
    }

    /**
     * Get the OCSP response.
     *
     * @return bool|SetaPDF_Signer_Ocsp_Response
     */
    public function getOcspResponse()
    {
        if (!$this->hasOcspResponse()) {
            return false;
        }

        return $this->_ocspResponse;
    }

    /**
     * Checks whether a CRL is available or not.
     *
     * @return bool
     */
    public function hasCrl()
    {
        return $this->_crl !== null;
    }

    /**
     * Get the CRL.
     *
     * @return bool|SetaPDF_Signer_X509_Crl
     */
    public function getCrl()
    {
        if (!$this->hasCrl()) {
            return false;
        }

        return $this->_crl;
    }
}