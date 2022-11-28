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
 * Class representing the X509 Subject key identifier extension.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_SubjectKeyIdentifier extends Extension
{
    /**
     * Extension OID.
     *
     * @var string
     */
    const OID = '2.5.29.14';

    /**
     * Get the key identifier.
     *
     * @param bool $hex Whether the return value should be hex encoded or not.
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getKeyIdentifier($hex = true)
    {
        $value = $this->getExtensionValue();

        return $hex
            ? SetaPDF_Core_Type_HexString::str2hex($value->getValue())
            : $value->getValue();
    }
}