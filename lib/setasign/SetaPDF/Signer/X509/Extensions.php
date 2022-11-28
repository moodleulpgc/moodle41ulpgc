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
 * Class offering access to X509 extensions.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Extensions
{
    /**
     * The ASN.1 element of the tBSCertificate value.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_tBSCertificate;

    /**
     * All extensions.
     *
     * @var array
     */
    protected $_extensions = [];

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_Asn1_Element $tBSCertificate
     */
    public function __construct(SetaPDF_Signer_Asn1_Element $tBSCertificate)
    {
        $this->_tBSCertificate = $tBSCertificate;
    }

    /**
     * Get an extension by its OID.
     *
     * @param string $oid
     * @return Extension|false
     */
    public function get($oid)
    {
        $extensions = $this->getAll();
        if (isset($extensions[$oid])) {
            return $extensions[$oid];
        }

        return false;
    }

    /**
     * Get all extensions.
     *
     * @return Extension[]
     */
    public function getAll()
    {
        if (count($this->_extensions) !== 0) {
            return $this->_extensions;
        }

        $tbs = $this->_tBSCertificate;
        $version = $tbs->getChild(0);
        if (!$version || $version->getIdent() !== "\xA0") {
            return [];
        }

        $version = $version->getChild(0);
        $version = $version ? ord($version->getValue()) : 0;
        if ($version !== 2) {
            return [];
        }

        $extensions = [];
        $offset = 7;
        for (; $offset < $tbs->getChildCount(); $offset++) {
            if ($tbs->getChild($offset)->getIdent() === "\xA3") {
                $extensions = $tbs->getChild($offset)->getChild(0)->getChildren();
            }
        }

        foreach ($extensions as $extension) {
            $extension = Extension::create($extension);
            $this->_extensions[$extension->getOid()] = $extension;
        }

        return $this->_extensions;
    }
}