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
 * Class representing the X509 Key usage extension.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_KeyUsage extends Extension
{
    /**
     * Extension OID.
     *
     * @var string
     */
    const OID = '2.5.29.15';

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_DIGITAL_SIGNATURE = 0x80;

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_NON_REPUDIATION = 0x40;

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_KEY_ENCIPHERMENT = 0x20;

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_DATA_ENCIPHERMENT = 0x10;

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_KEY_AGREEMENT = 0x08;

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_KEY_CERT_SIGN = 0x04;

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_CRL_SIGN = 0x02;

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_ENCIPHER_ONLY = 0x01;

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_DECIPHER_ONLY = 0x8000;

    /**
     * Get the purposes value.
     *
     * @return int
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getPurposes()
    {
        $bitString = $this->getExtensionValue()->getValue();
        // $unusedBits = \ord($bitString[0]);
        $bytes = substr($bitString, 1);

        return SetaPDF_Core_BitConverter::formatFromUInt($bytes, strlen($bytes));
    }

    /**
     * Checks the purpose by a given OID.
     *
     * @param int $purpose
     * @return bool
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function is($purpose)
    {
        return ($this->getPurposes() & $purpose) === $purpose;
    }
}