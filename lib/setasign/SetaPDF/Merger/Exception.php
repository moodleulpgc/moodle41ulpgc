<?php
/**
 * This file is part of the SetaPDF-Merger Component
 * 
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Exception.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * Merger Exception
 * 
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Merger
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Merger_Exception extends SetaPDF_Exception
{
    /**
     * The filename that was processed while the exception was created
     *
     * @var string
     */
    protected $_pdfFilename;

    /**
     * SetaPDF_Merger_Exception constructor.
     *
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code.
     * @param Exception|null $previous The previous exception used for the exception chaining.
     * @param null $pdfFilename The PDF filename that was processed while the exception was created.
     */
    public function __construct($message = '', $code = 0, Exception $previous = null, $pdfFilename = null)
    {
        parent::__construct($message, $code, $previous);
        $this->_pdfFilename = $pdfFilename;
    }

    /**
     * Get the PDF filename that was processed while the exception was created.
     *
     * @return string
     */
    public function getPdfFilename()
    {
        return $this->_pdfFilename;
    }
}