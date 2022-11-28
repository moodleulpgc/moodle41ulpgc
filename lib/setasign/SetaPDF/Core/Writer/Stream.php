<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: String.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * A writer class for streams
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Writer_Stream
    extends SetaPDF_Core_Writer_AbstractWriter
    implements SetaPDF_Core_Writer_WriterInterface
{
    /**
     * The file stream resource
     *
     * @var resource
     */
    protected $_handle;

    /**
     * The constructor.
     *
     * @param resource $handle
     */
    public function __construct($handle)
    {
        $this->_setHandle($handle);
    }

    /**
     * Set the stream handle.
     *
     * @param resource $handle
     */
    protected function _setHandle($handle)
    {
        if (!is_resource($handle) || get_resource_type($handle) !== 'stream') {
            throw new InvalidArgumentException(
                'No stream handle given.'
            );
        }

        $metaData = stream_get_meta_data($handle);
        if ($metaData['mode'] === 'r' || $metaData['mode'] === 'rb') { // all other modes are for writing
            throw new InvalidArgumentException(
                'Stream mode needs to be writable.'
            );
        }

        $this->_handle = $handle;
    }

    /**
     * Get the stream handle.
     *
     * @return resource
     */
    public function getHandle()
    {
        return $this->_handle;
    }

    /**
     * Write the content to the output file.
     *
     * @param string $s
     */
    public function write($s)
    {
        fwrite($this->_handle, $s);
    }

    /**
     * Returns the current position of the output file.
     *
     * @return integer
     */
    public function getPos()
    {
        return ftell($this->_handle);
    }

    /**
     * Copies an existing stream into the target stream.
     *
     * @param resource|SetaPDF_Core_Writer_Stream $source
     */
    public function copy($source)
    {
        if ($source instanceof self) {
            $source = $source->getHandle();
        }

        if (!is_resource($source) || get_resource_type($source) !== 'stream') {
            throw new InvalidArgumentException('The $source argument needs a stream as param.');
        }

        $metaData = stream_get_meta_data($source);
        if ($metaData['mode'] === 'a' || $metaData['mode'] === 'ab' ||
            $metaData['mode'] === 'c' || $metaData['mode'] === 'cb') {
            throw new InvalidArgumentException('Stream mode needs to be readable.');
        }

        if ($metaData['seekable'] === false) {
            throw new InvalidArgumentException('Stream mode be seekable.');
        }

        $sourcePos = ftell($source);
        fseek($source, 0);
        stream_copy_to_stream($source, $this->_handle);
        fseek($source, $sourcePos);
    }
}