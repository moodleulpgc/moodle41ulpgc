<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Identity.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * A class representing a Identity CMAP.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Font_Cmap_Identity implements SetaPDF_Core_Font_Cmap_CmapInterface
{
    /**
     * Do a reverse lookup.
     *
     * @param string $dest
     * @return bool|mixed
     */
    public function reverseLookup($dest)
    {
        return $dest;
    }

    /**
     * Do a reverse CID lookup.
     *
     * @param string $dest
     * @return bool|mixed
     */
    public function reverseCidLoopkup($dest)
    {
        return SetaPDF_Core_Encoding::unicodePointToUtf16Be($dest);
    }

    /**
     * Lookup a unicode value.
     *
     * @param string $src
     * @return bool|number|string
     */
    public function lookup($src)
    {
        // Identity maps only UTF-16 character codes.
        if (strlen($src) < 2 || SetaPDF_Core_Encoding::strlen($src, 'UTF-16BE') < 1) {
            return false;
        }

        return $src;
    }

    /**
     * Lookup for a CID.
     *
     * @param string $src
     * @return bool|number|string
     */
    public function lookupCid($src)
    {
        return hexdec(SetaPDF_Core_Type_HexString::str2hex($src));
    }
}