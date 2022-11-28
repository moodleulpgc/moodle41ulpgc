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
interface SetaPDF_Signer_Signature_Module_ModuleInterface
{
    /**
     * Create a signature for the file in the given $tmpPath.
     *
     * @param SetaPDF_Core_Reader_FilePath $tmpPath
     * @return string
     */
    public function createSignature(SetaPDF_Core_Reader_FilePath $tmpPath);
}