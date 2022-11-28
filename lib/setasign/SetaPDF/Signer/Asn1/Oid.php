<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Oid.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * Helper class to de- and encode OIDs
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Asn1_Oid
{
    /**
     * Encodes an OID.
     *
     * @param string $oidStr OID in dot form
     * @return string OID in binary form
     */
    static public function encode($oidStr)
    {
        $pieces = explode('.', $oidStr);
        $oid = chr(40 * $pieces[0] + $pieces[1]);

        for ($i = 2, $len = count($pieces); $i < $len; $i++) {
            $current = (int)$pieces[$i];
            if (($current - 1) > 0x80) {
                $add = chr($current % 0x80);
                $current = floor($current / 0x80);
                while ($current > 127) {
                    $add = chr(($current % 0x80) | 0x80) . $add;
                    $current = floor($current / 0x80);
                }
                $add = chr(($current % 0x80) | 0x80) . $add;
                $oid .= $add;
            } else {
                $oid .= chr($current);
            }
        }

        return $oid;
    }

    /**
     * Decodes an OID.
     *
     * @param string $oid OID in binary form
     * @return string The OID in dot form
     */
    static public function decode($oid)
    {
        $oidStr = '';
        $p = 0;
        $len = strlen($oid);
        $b = ord($oid[$p++]);

        $oidStr .= (string)((int)($b / 40));
        $oidStr .= '.' . (string)((int)($b % 40));

        while ($p < $len) {
            $v = 0;
            while (1) {
                $b = ord($oid[$p++]);
                $v <<= 7;
                $v += ($b & 0x7f);
                if (($b & 0x80) == 0) {
                    break;
                }
            }
            $oidStr .= '.' . $v;
        }

        return $oidStr;
    }
}