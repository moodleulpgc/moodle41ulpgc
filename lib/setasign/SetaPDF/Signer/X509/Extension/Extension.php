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

use SetaPDF_Signer_X509_Extension_AuthorityInformationAccess as AuthorityInformationAccess;
use SetaPDF_Signer_X509_Extension_KeyUsage as KeyUsage;
use SetaPDF_Signer_X509_Extension_BasicConstraints as BasicConstraints;
use SetaPDF_Signer_X509_Extension_CrlDisributionPoint as CrlDisributionPoint;
use SetaPDF_Signer_X509_Extension_ExtendedKeyUsage as ExtendedKeyUsage;
use SetaPDF_Signer_X509_Extension_SubjectKeyIdentifier as SubjectKeyIdentifier;
use SetaPDF_Signer_X509_Extension_AuthorityKeyIdentifier as AuthorityKeyIdentifier;
use SetaPDF_Signer_X509_Extension_OcspNoCheck as OcspNoCheck;
use SetaPDF_Signer_X509_Extension_TimeStamp as TimeStamp;

/**
 * Base class for X509 extensions.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extension_Extension
{
    /**
     * The extension OID.
     *
     * @var string
     */
    protected $_oid;

    /**
     * The extension element.
     *
     * Extension  ::=  SEQUENCE  {
     *  extnID      OBJECT IDENTIFIER,
     *  critical    BOOLEAN DEFAULT FALSE,
     *  extnValue   OCTET STRING
     *  -- contains the DER encoding of an ASN.1 value
     *  -- corresponding to the extension type identified
     *  -- by extnID
     *  }
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_extension;

    /**
     * Create an extension instance.
     *
     * @param SetaPDF_Signer_Asn1_Element $extension
     * @return SetaPDF_Signer_X509_Extension_AuthorityInformationAccess|SetaPDF_Signer_X509_Extension_AuthorityKeyIdentifier|SetaPDF_Signer_X509_Extension_BasicConstraints|SetaPDF_Signer_X509_Extension_CrlDisributionPoint|SetaPDF_Signer_X509_Extension_ExtendedKeyUsage|SetaPDF_Signer_X509_Extension_Extension|SetaPDF_Signer_X509_Extension_KeyUsage|SetaPDF_Signer_X509_Extension_OcspNoCheck|SetaPDF_Signer_X509_Extension_SubjectKeyIdentifier
     */
    public static function create(SetaPDF_Signer_Asn1_Element $extension)
    {
        $extnID = SetaPDF_Signer_Asn1_Oid::decode($extension->getChild(0)->getValue());

        switch ($extnID) {
            case AuthorityInformationAccess::OID:
                return new AuthorityInformationAccess($extension);
            case KeyUsage::OID:
                return new KeyUsage($extension);
            case BasicConstraints::OID:
                return new BasicConstraints($extension);
            case CrlDisributionPoint::OID:
                return new CrlDisributionPoint($extension);
            case ExtendedKeyUsage::OID:
                return new ExtendedKeyUsage($extension);
            case SubjectKeyIdentifier::OID:
                return new SubjectKeyIdentifier($extension);
            case AuthorityKeyIdentifier::OID:
                return new AuthorityKeyIdentifier($extension);
            case OcspNoCheck::OID:
                return new OcspNoCheck($extension);
            case TimeStamp::OID:
                return new TimeStamp($extension);
            default:
                return new self($extension);
        }
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_Asn1_Element $extension
     */
    public function __construct(SetaPDF_Signer_Asn1_Element $extension)
    {
        $this->_oid = SetaPDF_Signer_Asn1_Oid::decode($extension->getChild(0)->getValue());
        $this->_extension = $extension;
    }

    /**
     * Get the OID.
     *
     * @return string
     */
    public function getOid()
    {
        return $this->_oid;
    }

    /**
     * Get the critical flag.
     *
     * @return bool
     */
    public function isCritical()
    {
        if ($this->_extension->getChild(1)->getIdent() === SetaPDF_Signer_Asn1_Element::BOOLEAN) {
            return $this->_extension->getChild(1)->getValue() !== "\x00";
        }

        return false;
    }

    /**
     * Get the extensions value.
     *
     * @return SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getExtensionValue()
    {
        $offset = 1;
        if ($this->_extension->getChild($offset)->getIdent() === SetaPDF_Signer_Asn1_Element::BOOLEAN) {
            $offset++;
        }

        return SetaPDF_Signer_Asn1_Element::parse($this->_extension->getChild($offset)->getValue());
    }
}