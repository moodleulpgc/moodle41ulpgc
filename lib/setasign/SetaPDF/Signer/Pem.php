<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id$
 */

/**
 * Helper class for en- and decoding of PEM encoded data.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Pem
{
    /**
     * Encodes data to PEM.
     *
     * @param string $data
     * @param string $label
     * @return string
     */
    public static function encode($data, $label)
    {
        return '-----BEGIN ' . $label . "-----\n"
            . trim(chunk_split(base64_encode($data), 64, "\n")) . "\n"
            . '-----END ' . $label . "-----\n";
    }

    /**
     * Decode PEM encoded data.
     *
     * If no label is passed, the label of the first data package found is set.
     * Otherwise the predefined label is used to find the first data package with that label.
     *
     * @param string $data
     * @param null $label The label to match or the matched label if null.
     * @return string
     */
    public static function decode($data, &$label = null)
    {
        $labelRegex = ($label !== null ? preg_quote($label, '/') : '[^\-]*');
        $found = preg_match(
            '/(-----BEGIN (' . $labelRegex . ')-----)([^-]*)(-----END (\2)-----)/',
            $data,
            $matches
        );

        if ($found) {
            $content = base64_decode(preg_replace('~\s~', '', $matches[3]), true);
            if ($content) {
                $label = $matches[2];
                return $content;
            }
        }

        if ($label === null) {
            throw new InvalidArgumentException('Given data is not PEM encoded.');
        }

        throw new InvalidArgumentException(sprintf('Cannot find PEM message with label "%s".', $label));
    }

    /**
     * Extracts all PEM encoded strings from a bundle.
     *
     * @param string $bundle
     * @param null $label Use the label to limit the result to only strings, with a specific label ("-----BEGIN $label-----").
     * @return string[]
     */
    public static function extract($bundle, $label = null)
    {
        $labelRegex = ($label !== null ? preg_quote($label, '/') : '[^\-]*');

        $found = preg_match_all(
            '/(-----BEGIN (' . $labelRegex . ')-----)([^-]*)(-----END (\2)-----)/',
            $bundle,
            $matches
        );

        if ($found) {
            return $matches[0];
        }

        throw new InvalidArgumentException(
            'No PEM encoded data ' . ($label === null ? '' : "with label '$label' ") . 'found in $bundle.'
        );
    }

    /**
     * Extracts all PEM encoded strings from a file.
     *
     * @param string $bundlePath
     * @param null $label Use the label to limit the result to only strings, with a specific label ("-----BEGIN $label-----").
     * @return string[]
     */
    public static function extractFromFile($bundlePath, $label = null)
    {
        return self::extract(file_get_contents($bundlePath), $label);
    }
}