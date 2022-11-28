<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: DocumentInterface.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * An interface that can be used in a signature or timestamp modul to update the document before the signature creation.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Signer_Signature_DocumentInterface
{
    /**
     * Method to allow updates onto the document instance.
     *
     * @param SetaPDF_Core_Document $document
     */
    public function updateDocument(SetaPDF_Core_Document $document);
}