<?php
/**
 * This file is part of the SetaPDF-Core Component
 * 
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: AbstractWriter.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Abstract class for a writer object
 * 
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Core_Writer_AbstractWriter
{
    /**
     * Status property
     * 
     * @var string
     */
    protected $_status = SetaPDF_Core_Writer::INACTIVE;
    
    /**
     * Method which should/will be called when the writing process starts.
     */
    public function start()
    {
        $this->_status = SetaPDF_Core_Writer::ACTIVE;
    }
    
    /**
     * Method which should/will be called when the writing process is finished.
     */
    public function finish()
    {
        $this->_status = SetaPDF_Core_Writer::FINISHED;
    }
    
    /**
     * Get the current status of the writer object.
     * 
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    /**
     * Method which should/will be called when the document objects cleanUp() method is called.
     */
    public function cleanUp()
    {
        $this->_status = SetaPDF_Core_Writer::CLEANED_UP;
    }
}