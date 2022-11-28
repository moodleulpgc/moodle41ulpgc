<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: File.php 1490 2020-06-23 15:23:06Z jan.slabon $
 */

/**
 * A writer class for files or writable streams
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Writer_File
    extends SetaPDF_Core_Writer_Stream
    implements SetaPDF_Core_Writer_FileInterface
{
    /**
     * Path to the output file
     *
     * @var string
     */
    protected $_path;

    /**
     * The constructor.
     *
     * @param string $path The path to the output file
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * Get the file path of the writer.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Get the stream handle.
     *
     * @return resource|null
     */
    public function getHandle()
    {
        if ($this->_status !== SetaPDF_Core_Writer::ACTIVE) {
            throw new BadMethodCallException('Handle is only available for active writer instance.');
        }

        return $this->_handle;
    }

    /**
     * Method called when the writing process starts.
     *
     * It setups the file handle for this writer.
     */
    public function start()
    {
        // TODO: Handle this without @-sign
        $handle = @fopen($this->_path, 'wb');
        if ($handle === false) {
            throw new SetaPDF_Core_Writer_Exception(
                sprintf('Unable to open "%s" for writing.', $this->_path)
            );
        }

        $this->_setHandle($handle);
        parent::start();
    }

    /**
     * This method is called when the writing process is finished.
     *
     * It closes the file handle.
     */
    public function finish()
    {
        fclose($this->getHandle());
        parent::finish();
    }

    /**
     * Close the file handle if needed.
     *
     * @see SetaPDF_Core_Writer_AbstractWriter::cleanUp()
     */
    public function cleanUp()
    {
        if ($this->_status === SetaPDF_Core_Writer::ACTIVE) {
            fclose($this->getHandle());
        }

        parent::cleanUp();
    }
}