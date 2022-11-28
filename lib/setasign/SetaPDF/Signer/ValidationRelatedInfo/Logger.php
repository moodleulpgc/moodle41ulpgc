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
 * The standard logger implementation
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_ValidationRelatedInfo_Logger implements SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
{
    /**
     * The log entries.
     *
     * @var SetaPDF_Signer_ValidationRelatedInfo_LogEntry[]
     */
    protected $_logs = [];

    /**
     * Current depth.
     *
     * @var int
     */
    protected $_depth = 0;

    /**
     * The flag that defines whether a log message is output directly.
     *
     * @var bool
     */
    protected $_directOutput = false;

    /**
     * Set a flag that defines whether a log message is output directly.
     *
     * @param boolean $directOutput
     */
    public function setDirectOutput($directOutput)
    {
        $this->_directOutput = (boolean)$directOutput;
    }

    /**
     * This increases the depth level which should be forward to new log entry instances.
     *
     * By a "depth" value it is possible to visualize the process in more detailed levels.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function increaseDepth()
    {
        $this->_depth++;
        return $this;
    }

    /**
     * This decreases the depth level which should be forward to new log entry instances.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function decreaseDepth()
    {
        $this->_depth--;
        return $this;
    }

    /**
     * Log a message.
     *
     * @param $message
     * @param array $context
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function log($message, array $context = [])
    {
        $log = new SetaPDF_Signer_ValidationRelatedInfo_LogEntry($message, $context, $this->_depth);

        if ($this->_directOutput === true) {
            echo str_repeat(' ', $log->getDepth() * 4) . $log->getMessage() . PHP_EOL;
        }

        $this->_logs[] = $log;

        return $this;
    }

    /**
     * Get all logs.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_LogEntry[]
     */
    public function getLogs()
    {
        return $this->_logs;
    }
}