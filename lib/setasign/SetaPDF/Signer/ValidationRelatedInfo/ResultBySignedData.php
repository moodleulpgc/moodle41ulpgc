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

use SetaPDF_Signer_ValidationRelatedInfo_Result as Result;
use SetaPDF_Signer_X509_Certificate as Certificate;
use SetaPDF_Signer_Cms_SignedData as SignedData;

/**
 * Class representing a validation related information result by a SignedData object.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_ValidationRelatedInfo_ResultBySignedData extends Result
{
    /**
     * The signing certificate.
     *
     * @var Certificate
     */
    protected $_signingCertificate;

    /**
     * The SigneData object.
     *
     * @var SignedData
     */
    protected $_signedData;

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_Result $parent
     * @param SetaPDF_Signer_X509_Certificate $signingCertificate
     * @param SetaPDF_Signer_Cms_SignedData $signedData
     */
    public function __construct(Result $parent, Certificate $signingCertificate, SignedData $signedData)
    {
        $this->_vri = $parent->_vri;
        $this->_signingCertificate = $signingCertificate;
        $this->_signedData = $signedData;
    }

    /**
     * Get the signing certificate.
     *
     * @return SetaPDF_Signer_X509_Certificate
     */
    public function getSigningCertificate()
    {
        return $this->_signingCertificate;
    }

    /**
     * Get the SignedData object.
     *
     * @return SetaPDF_Signer_Cms_SignedData
     */
    public function getSignedData()
    {
        return $this->_signedData;
    }
}