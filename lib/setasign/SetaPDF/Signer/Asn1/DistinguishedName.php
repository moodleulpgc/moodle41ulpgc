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

/**
 * Helper class to convert a DistinguishedName ASN.1 struncture into a string.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Asn1_DistinguishedName
{
    /**
     * The default separator for the resulting string.
     *
     * @var string
     */
    public static $separator = '/';

    /**
     * Converts a DistinguishedName ASN.1 element into a string.
     *
     * @param SetaPDF_Signer_Asn1_Element $name
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public static function getAsString(SetaPDF_Signer_Asn1_Element $name)
    {
        // https://tools.ietf.org/html/rfc1779#page-4
        // https://www.alvestrand.no/objectid/2.5.4.html
        $oids = [
            '2.5.4.3' => 'CN', // CommonName
            '2.5.4.4' => 'SN', // Surname
            '2.5.4.5' => 'serialNumber', // Serial Number
            '2.5.4.6' => 'C', // CountryName
            '2.5.4.7' => 'L', // LocalityName
            '2.5.4.8' => 'ST', // StateOrProvinceName
            '2.5.4.9' => 'STREET', // StreetAddress
            '2.5.4.10' => 'O', // OrganizationName
            '2.5.4.11' => 'OU', // OrganizationalUnitName
            '2.5.4.12' => 'T', // Title
            '1.2.840.113549.1.9.1' => 'emailAddress', // Email
            '2.5.4.42' => 'G', // Given Name
            '2.5.4.43' => 'I', // Initials
        ];

        $pieces = [];
        foreach ($name->getChildren() as $child) {
            if (
                $child->getIdent() !==
                (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SET)) {
                throw new SetaPDF_Signer_Asn1_Exception('Invalid data type in ASN.1 structure (expected SET).');
            }

            $child = $child->getChild(0);

            $objectIdentifier = $child->getChild(0);
            if (
                !$objectIdentifier ||
                $objectIdentifier->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER
            ) {
                throw new SetaPDF_Signer_Asn1_Exception('Invalid data type in ASN.1 structure (expected OBJECT IDENTIFIER).');
            }

            $objectIdentifier = SetaPDF_Signer_Asn1_Oid::decode($objectIdentifier->getValue());
            $value = $child->getChild(1);
            if (!$value) {
                throw new SetaPDF_Signer_Asn1_Exception('Missing value in ASN.1 structure.');
            }

            $value = $value->getValue();

            if (isset($oids[$objectIdentifier])) {
                $pieces[] = $oids[$objectIdentifier] . '=' . $value;
            }
        }

        return self::$separator . implode(self::$separator, $pieces);
    }
}