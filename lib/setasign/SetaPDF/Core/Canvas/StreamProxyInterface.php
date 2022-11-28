<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: StreamProxyInterface.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Interface of a StreamProxy
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Core_Canvas_StreamProxyInterface
    extends SetaPDF_Core_WriteInterface
{
    /**
     * Clears the complete canvas content.
     */
    public function clear();

    /**
     * Get the whole byte stream of the canvas.
     *
     * @return string
     */
    public function getStream();
}