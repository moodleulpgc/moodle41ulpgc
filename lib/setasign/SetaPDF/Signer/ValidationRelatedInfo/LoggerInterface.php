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
 * Interface for the logger
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
{
    /**
     * This increases the depth level which should be forward to new log entry instances.
     *
     * By a "depth" value it is possible to visualize the process in more detailed levels.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function increaseDepth();

    /**
     * This decreases the depth level which should be forward to new log entry instances.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function decreaseDepth();

    /**
     * Log a message.
     *
     * @param $message
     * @param array $context
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function log($message, array $context = []);
}