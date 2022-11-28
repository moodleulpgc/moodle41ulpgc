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

use SetaPDF_Signer_ValidationRelatedInfo_ResultBySignedData as ResultBySignedData;
use SetaPDF_Signer_ValidationRelatedInfo_IntegrityResult as IntegrityResult;

/**
 * Class representing a validation related information result by a signature field name.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_ValidationRelatedInfo_ResultByField extends ResultBySignedData
{
    /**
     * The integrity result of a signature field.
     *
     * @var IntegrityResult
     */
    protected $_integrityResult;

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_ResultBySignedData $parent
     * @param SetaPDF_Signer_ValidationRelatedInfo_IntegrityResult $integrityResult
     */
    public function __construct(
        ResultBySignedData $parent,
        IntegrityResult $integrityResult
    )
    {
        parent::__construct($parent, $parent->_signingCertificate, $parent->_signedData);
        $this->_integrityResult = $integrityResult;
    }

    /**
     * Get the integrity result object of the signature field.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_IntegrityResult
     */
    public function getIntegrityResult()
    {
        return $this->_integrityResult;
    }

    /**
     * Get the properties from the PDF signature value.
     *
     * @return array
     */
    public function getSignatureProperties()
    {
        $value = $this->getIntegrityResult()->getField()->getValue()->ensure();
        $signatureProperties = [];
        foreach ([
                     SetaPDF_Signer::PROP_NAME,
                     SetaPDF_Signer::PROP_LOCATION,
                     SetaPDF_Signer::PROP_CONTACT_INFO,
                     SetaPDF_Signer::PROP_REASON,
                     SetaPDF_Signer::PROP_TIME_OF_SIGNING
                 ] AS $property) {
            if (!$value->offsetExists($property)) {
                continue;
            }

            $propertyValue = $value->getValue($property)->ensure()->getValue();
            if ($property === SetaPDF_Signer::PROP_TIME_OF_SIGNING) {
                $propertyValue = SetaPDF_Core_DataStructure_Date::stringToDateTime($propertyValue);
            } else {
                $propertyValue = SetaPDF_Core_Encoding::convertPdfString($propertyValue);
            }

            $signatureProperties[$property] = $propertyValue;
        }

        return $signatureProperties;
    }

    /**
     * Check whether the signature of this field is a certified signature or not.
     *
     * @return bool
     */
    public function isCertified()
    {
        $value = $this->getIntegrityResult()->getField()->getValue()->ensure();
        $references = $value->getValue('Reference');
        // TODO: Check references for allowed changes
        return  $references !== null;
    }
}