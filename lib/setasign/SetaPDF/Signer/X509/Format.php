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
 * Helper class for X509 format constants.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Signer_X509_Format
{
    /**
     * Constant for PEM encoding.
     *
     * @var string
     */
    const PEM = 'pem';

    /**
     * Constant for DER encoding.
     *
     * @var string
     */
    const DER = 'der';
}