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

use SetaPDF_Signer_X509_Extension_Extension as Extension;

/**
 * Class representing the X509 Basic Constraints extension.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_BasicConstraints extends Extension
{
    /**
     * Extension OID.
     *
     * @var string
     */
    const OID = '2.5.29.19';

    /**
     * Get the cA value.
     *
     * @return bool
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function isCa()
    {
        $extValue = $this->getExtensionValue();

        $cA = $extValue->getChild(0);
        if ($cA) {
            return $cA->getValue() !== "\x00";
        }

        return false;
    }

    /**
     * Get the maximum number of CA certificates that may follow this certificate in a certification path.
     *
     * @return false|int
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getPathLengthConstraint()
    {
        $extValue = $this->getExtensionValue();

        $pathLengthConstraint = $extValue->getChild(0);
        if ($pathLengthConstraint && $pathLengthConstraint->getIdent() === SetaPDF_Signer_Asn1_Element::BOOLEAN) {
            $pathLengthConstraint = $extValue->getChild(1);
        }

        if (!$pathLengthConstraint) {
            return false;
        }

        return ord($pathLengthConstraint->getValue());
    }
}