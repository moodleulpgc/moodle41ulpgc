<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: DigestInterface.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * The interface for modules that supports different digest algorithms
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Signer_DigestInterface
{
    /**
     * Set the digest algorithm.
     *
     * @param $digest
     */
    public function setDigest($digest);

    /**
     * Get the digest algorithm.
     *
     * @return string
     */
    public function getDigest();
}