<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: ModuleInterface.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * The signature module interface
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Signer_Timestamp_Module_ModuleInterface
{
    /**
     * Create the timestamp signature.
     *
     * @param string|SetaPDF_Core_Reader_FilePath $data
     * @return string
     */
    public function createTimestamp($data);
}