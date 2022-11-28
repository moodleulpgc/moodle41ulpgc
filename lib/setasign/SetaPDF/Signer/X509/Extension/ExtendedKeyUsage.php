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
 * Class representing the X509 Extended key usage extension.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_ExtendedKeyUsage extends Extension
{
    /**
     * Extension OID.
     *
     * @var string
     */
    const OID = '2.5.29.37';

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_SERVER_AUTH = '1.3.6.1.5.5.7.3.1';

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_CLIENT_AUTH = '1.3.6.1.5.5.7.3.2';

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_CODE_SIGNING = '1.3.6.1.5.5.7.3.3';

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_EMAIL_PROTECTION = '1.3.6.1.5.5.7.3.4';

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_TIME_STAMPING = '1.3.6.1.5.5.7.3.8';

    /**
     * Purspose OID.
     *
     * @var string
     */
    const PURPOSE_OCSP_SIGNING = '1.3.6.1.5.5.7.3.9';

    /**
     * Get the purposes.
     *
     * @return string[] The OIDs of all purposes.
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getPurposes()
    {
        $purposes = $this->getExtensionValue();

        $result = [];
        foreach ($purposes->getChildren() as $purpose) {
            $result[] = SetaPDF_Signer_Asn1_Oid::decode($purpose->getValue());
        }

        return $result;
    }

    /**
     * Checks the purpose by a given OID.
     *
     * @param string $oid
     * @return bool
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function is($oid)
    {
        return in_array($oid, $this->getPurposes(), true);
    }
}