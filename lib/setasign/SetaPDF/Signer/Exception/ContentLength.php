<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: ContentLength.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * An exception which is thrown if the reserved space in a signature template is to small
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Exception_ContentLength extends SetaPDF_Signer_Exception
{
    /**
     * The expected Length.
     *
     * @var int
     */
    private $_expectedLength;

    /**
     * Set the expected length value.
     *
     * @param int $expectedLength
     */
    public function setExpectedLength($expectedLength)
    {
        $this->_expectedLength = $expectedLength;
    }

    /**
     * Get the expected length value.
     *
     * @return int
     */
    public function getExpectedLength()
    {
        return $this->_expectedLength;
    }
}