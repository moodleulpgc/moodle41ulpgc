<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage DataStructure
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: DataStructureInterface.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Interface for data structure classes
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage DataStructure
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Core_DataStructure_DataStructureInterface
{
    /**
     * Get the PDF value object.
     *
     * @return SetaPDF_Core_Type_AbstractType
     */
    public function getValue();

    /**
     * Get the data as a PHP value.
     *
     * @return mixed
     */
    public function toPhp();
}