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
 * Helper class to decode UTCTime and GeneralizedTime structures.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Asn1_Time
{
    /**
     * Create a DateTime object based on matched values.
     *
     * @param $matches
     * @return DateTime
     */
    private static function _createDateTime($matches)
    {
        $matches = array_filter($matches, static function ($v) {
            return $v !== null;
        });

        $dateTime = $matches['year'] . '/' . $matches['month'] . '/' . $matches['day'] . ' ' .
            (isset($matches['hour']) && $matches['hour'] !== '' ? $matches['hour'] : '00') . ':' .
            (isset($matches['minute']) && $matches['minute'] !== '' ? $matches['minute'] : '00') . ':' .
            (isset($matches['second']) && $matches['second'] !== '' ? $matches['second'] : '00') .
            (isset($matches['fraction']) && $matches['fraction'] !== '' ? $matches['fraction'] : '') .
            (isset($matches['relationToUT']) && $matches['relationToUT'] !== '' ? $matches['relationToUT'] : '') .
            (isset($matches['hoursFromUT']) && $matches['hoursFromUT'] !== '' ? $matches['hoursFromUT'] : '') .
            (isset($matches['minutesFromUT']) && $matches['minutesFromUT'] !== '' ? $matches['minutesFromUT'] : '');

        try {
            return new DateTime($dateTime);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Time cannot be parsed.', 0, $e);
        }
    }

    /**
     * Parses an UTCTime value into a DateTime object.
     *
     * @param string $s
     * @return DateTime
     */
    public static function parseUtcTime($s)
    {
        /* UTCTime:
         * YYMMDDhhmm[ss] followed by Z or (+|-)hhmm
         */
        $matched = preg_match('/(?P<year>\d{2})' // YY
            . '(?P<month>\d{2})' // MM
            . '(?P<day>\d{2})' // DD
            . '(?P<hour>\d{2})' // hh
            . '(?P<minute>\d{2})' // mm
            . '(?P<second>\d{2})?' // ss
            . '(?P<relationToUT>[\-\+Z])' // O
            . '(?P<hoursFromUT>\d{2})?' // hh
            . '(?P<minutesFromUT>\d{2})?' // 'mm
            . '/',
            $s, $matches
        );

        if ($matched) {
            if ($matches['year'] >= 50) {
                $matches['year'] = '19' . $matches['year'];
            } else {
                $matches['year'] = '20' . $matches['year'];
            }

            return self::_createDateTime($matches);
        }

        throw new InvalidArgumentException('Invalid time type.');
    }

    /**
     * Parses an GeneralizedTime value into a DateTime object.
     *
     * @param string $s
     * @return DateTime
     */
    public static function parseGeneralizedTime($s)
    {
        /* GeneralizedTime: https://www.obj-sys.com/asn1tutorial/node14.html
         * YYYYMMDDHH[MM[SS[.fff]]]
         * YYYYMMDDHH[MM[SS[.fff]]]Z
         * YYYYMMDDHH[MM[SS[.fff]]]+-HHMM
         */
        $matched = preg_match('/(?P<year>\d{4})' // YY
            . '(?P<month>\d{2})' // MM
            . '(?P<day>\d{2})' // DD
            . '(?P<hour>\d{2})' // HH
            . '(?P<minute>\d{2})?' // MM
            . '(?P<second>\d{2})?' // SS
            . '(?P<fraction>\.\d{3})?'
            . '(?P<relationToUT>[\-\+Z])?' // O
            . '(?P<hoursFromUT>\d{2})?' // hh
            . '(?P<minutesFromUT>\d{2})?' // 'mm
            . '/',
            $s, $matches
        );

        if ($matched) {
            return self::_createDateTime($matches);
        }

        throw new InvalidArgumentException('Invalid time type.');
    }

    /**
     * Decodes a ASN.1 Time value into a DateTime object.
     *
     * @param SetaPDF_Signer_Asn1_Element $time
     * @return DateTime
     */
    public static function decode(SetaPDF_Signer_Asn1_Element $time)
    {
        /* Time ::= CHOICE {
         *     utcTime        UTCTime,
         *     generalTime    GeneralizedTime }
         */

        $ident = $time->getIdent();

        if ($ident === SetaPDF_Signer_Asn1_Element::UTC_TIME) {
            return self::parseUtcTime($time->getValue());
        }

        if ($ident === SetaPDF_Signer_Asn1_Element::GENERALIZED_TIME) {
            return self::parseGeneralizedTime($time->getValue());
        }

        throw new InvalidArgumentException('Invalid time type.');
    }
}