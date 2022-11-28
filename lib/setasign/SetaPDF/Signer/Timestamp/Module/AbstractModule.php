<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: AbstractModule.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Abstract class representing a timestamp module
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Signer_Timestamp_Module_AbstractModule
{
    /**
     * The message digest
     *
     * @var string
     */
    protected $_digest = SetaPDF_Signer_Digest::SHA_256;

    /**
     * Set the digest algorithm to use.
     *
     * @see SetaPDF_Signer_Digest
     * @param string $digest Possible values are defined in {@link SetaPDF_Signer_Digest}
     */
    public function setDigest($digest)
    {
        $this->_digest = $digest;
    }

    /**
     * Get the digest algorithm.
     *
     * @return string
     */
    public function getDigest()
    {
        return $this->_digest;
    }

    /**
     * Get the hash that should be timestamped.
     *
     * @param string|SetaPDF_Core_Reader_FilePath $data The hash of the main signature
     * @return string
     */
    protected function _getHash($data)
    {
        if ($data instanceof SetaPDF_Core_Reader_FilePath) {
            return hash_file($this->getDigest(), $data->getPath(), true);
        }

        return hash($this->getDigest(), $data, true);
    }
}