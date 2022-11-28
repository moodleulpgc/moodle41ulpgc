<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: OpenSslCliCms.php 1505 2020-07-21 13:54:06Z jan.slabon $
 */

/**
 * A signature module which uses the CMS utility of OpenSSL via command line.
 *
 * The CMS utility is described here: {@link http://www.openssl.org/docs/apps/cms.html}.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Signature_Module_OpenSslCliCms extends SetaPDF_Signer_Signature_Module_OpenSslCli
{
    /**
     * The digest algorithm to use when signing
     *
     * @var string
     */
    protected $_digest = SetaPDF_Signer_Digest::SHA_256;

    /**
     * Options which are used as -keyopt argument
     *
     * @var array
     */
    protected $_keyOptions = [];

    /**
     * Set a signing key options (passed via -keyopt argument).
     *
     * @param string $name
     * @param string $value
     */
    public function setKeyOption($name, $value)
    {
        $this->_keyOptions[$name] = $value;
    }

    /**
     * Get all signing key options.
     *
     * @return string[]
     */
    public function getKeyOptions()
    {
        return $this->_keyOptions;
    }

    /**
     * Returns the name of the OpenSSL utitlity.
     *
     * @return string
     */
    protected function _getUtility()
    {
        return 'cms';
    }

    /**
     * Creates the command line command.
     *
     * @param string $inPath
     * @return string
     */
    protected function _createCommand($inPath)
    {
        $cmd = parent::_createCommand($inPath);

        foreach ($this->getKeyOptions() as $name => $value) {
            $cmd .= ' -keyopt ' . escapeshellarg($name) . ':' . escapeshellarg($value);
        }

        return $cmd;
    }

}