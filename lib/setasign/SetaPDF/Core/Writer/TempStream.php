<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id$
 */

/**
 * A writer class for temporary streams
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Writer_TempStream extends SetaPDF_Core_Writer_Stream
{
    /**
     * @var string
     */
    protected $_buffer = '';

    /**
     * @var int
     */
    protected $_bufferSize = 10000;

    /**
     * The constructor.
     *
     * @param null|int $memoryLimit Limit to use before writing to a temporary file. If null PHPs default (2 MB) is used.
     * @param int $bufferSize The buffer size of which needs to be reached until the data is written to the stream.
     */
    public function __construct($memoryLimit = null, $bufferSize = 10000)
    {
        $uri = 'php://temp';

        if ($memoryLimit !== null) {
            $uri .= '/maxmemory:' . $memoryLimit;
        }

        parent::__construct(fopen($uri, 'r+b'));

        $this->_bufferSize = (int)$bufferSize;
    }

    /**
     * The deconstructor closes the file handle.
     */
    public function __destruct()
    {
        fclose($this->_handle);
    }

    /**
     * Write content to the stream or buffer.
     *
     * @param string $s
     */
    public function write($s)
    {
        $this->_buffer .= $s;
        if (strlen($this->_buffer) > $this->_bufferSize) {
            $this->_flush();
        }
    }

    /**
     * Returns the current position.
     *
     * @return integer
     */
    public function getPos()
    {
        return parent::getPos() + strlen($this->_buffer);
    }

    /**
     * Finish methods flushes the buffer to the stream.
     */
    public function finish()
    {
        $this->_flush();
        parent::finish();
    }

    /**
     * Gets the handle of the stream.
     *
     * This method is overwritten to ensure that the handle is only accessed when the writer instance has the status
     * "finished".
     *
     * @return resource
     */
    public function getHandle()
    {
        if ($this->_status > SetaPDF_Core_Writer::FINISHED) {
            throw new BadMethodCallException(
                'This method can only be called if the writer instance is in "finished" status.'
            );
        }

        return parent::getHandle();
    }

    /**
     * Flushes the buffer to the stream and resets the buffer.
     */
    protected function _flush()
    {
        parent::write($this->_buffer);
        $this->_buffer = '';
    }
}