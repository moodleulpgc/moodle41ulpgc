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
 * Class representing a log entry.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_ValidationRelatedInfo_LogEntry
{
    /**
     * The message.
     *
     * @var string
     */
    protected $_message;

    /**
     * Context information.
     *
     * @var array
     */
    protected $_context;

    /**
     * Log depth.
     *
     * @var int
     */
    protected $_depth;

    /**
     * The constructor.
     *
     * @param string $message
     * @param array|null $context
     * @param int $depth
     */
    public function __construct($message, array $context = null, $depth = 0)
    {
        $this->_message = $message;
        $this->_context = $context;
        $this->_depth = (int) $depth;
    }

    /**
     * Get the message merged with context information.
     *
     * @return string
     */
    public function getMessage()
    {
        $message = $this->_message;
        foreach ($this->_context ?: [] as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'yes' : 'no';
            } else if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Get the depth.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->_depth;
    }

    /**
     * Get the message merged with context information as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getMessage();
    }
}