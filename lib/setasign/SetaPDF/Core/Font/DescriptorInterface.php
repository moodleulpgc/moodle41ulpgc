<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: DescriptorInterface.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Interface for fonts with a font descriptor.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Core_Font_DescriptorInterface
{
    /**
     * Get the font descriptor object of this font.
     *
     * @return SetaPDF_Core_Font_Descriptor
     */
    public function getFontDescriptor();
}