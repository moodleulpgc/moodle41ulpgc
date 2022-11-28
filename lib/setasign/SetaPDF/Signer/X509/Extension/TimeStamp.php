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
 * Class representing the Adobe proprietary X509 Time-stamp extension.
 *
 * Provides the server with the URL to use to signatures created using this credential.
 *
 * @see https://www.adobe.com/devnet-docs/etk_deprecated/tools/DigSig/oids.html#x-509-extension-oids
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_TimeStamp extends Extension
{
    /**
     * Extension OID.
     *
     * @var string
     */
    const OID = '1.2.840.113583.1.1.9.1';

    /**
     * Get the extension version.
     *
     * @return int
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getVersion()
    {
        $extValue = $this->getExtensionValue();
        return ord($extValue->getChild(0)->getValue());
    }

    /**
     * Get the Location of the time stamp server.
     *
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getLocation()
    {
        if ($this->getVersion() !== 1) {
            throw new SetaPDF_Signer_Exception(
                sprintf('Unsupported version (%s) in Time-stamp extension.', $this->getVersion())
            );
        }

        $extValue = $this->getExtensionValue();
        return $extValue->getChild(1)->getValue();
    }

    /**
     * Get whether the server requires authentication or not.
     *
     * @return bool
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function requiresAuth()
    {
        $extValue = $this->getExtensionValue();

        $requiresAuth = $extValue->getChild(2);
        if ($requiresAuth) {
            return $requiresAuth->getValue() !== "\x00";
        }

        return false;
    }
}
