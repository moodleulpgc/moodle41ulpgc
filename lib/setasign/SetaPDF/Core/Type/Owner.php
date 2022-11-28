<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Owner.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Interface representing an owner object which encapsulates other data.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Core_Type_Owner
extends SplObserver
{
    /**
     * Returns the owner document.
     *
     * Currently deactivated because of incompatibility with PHP 5.2
     *
     * @return SetaPDF_Core_Document|null
     */
    //public function getOwnerPdfDocument();
}