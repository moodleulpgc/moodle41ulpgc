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
use SetaPDF_Signer_Asn1_DistinguishedName as DistinguishedName;

/**
 * Class representing the X509 Authority key identifier extension.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_AuthorityKeyIdentifier extends Extension
{
    /**
     * Extension OID.
     *
     * @var string
     */
    const OID = '2.5.29.35';

    /**
     * Get the key identifier.
     *
     * @param bool $hex Whether the return value should be hex encoded or not.
     * @return false|string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getKeyIdentifier($hex = true)
    {
        $value = $this->getExtensionValue();
        $keyIdentifier = $value->getChild(0);
        if ($keyIdentifier->getIdent() === SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC) {
            return $hex
                ? SetaPDF_Core_Type_HexString::str2hex($keyIdentifier->getValue())
                : $keyIdentifier->getValue();
        }

        return false;
    }

    /**
     * Get authority certificate serial number.
     *
     * @param bool $hex Whether the return value should be hex encoded or not.
     * @return false|string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getAuthorityCertificateSerialNumber($hex = true)
    {
        $value = $this->getExtensionValue();
        foreach ($value->getChildren() as $child) {
            if ($child->getIdent() === (SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | "\x02")) {
                return $hex
                    ? SetaPDF_Core_Type_HexString::str2hex($child->getValue())
                    : $child->getValue();
            }
        }

        return false;
    }

    /**
     * Get authority certificate issuer name.
     *
     * @return false|string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getAuthorityCertificateIssuer()
    {
        $value = $this->getExtensionValue();
        foreach ($value->getChildren() as $child) {
            $ident = SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | "\x01";
            if (($child->getIdent() & $ident) === $ident) {
                $name = $child->getChild(0);
                $ident = SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | "\x04";
                if (($name->getIdent() & $ident) === $ident) {
                    return DistinguishedName::getAsString($name->getChild(0));
                }
            }
        }

        return false;
    }
}