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

use SetaPDF_Signer_X509_Certificate as Certificate;

/**
 * Interface representing a collection of X509 certificates.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Signer_X509_CollectionInterface extends Countable
{
    /**
     * Get all certificates.
     *
     * @return Certificate[]
     */
    public function getAll();

    /**
     * Checks if this collection contains the given certificate.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     * @return bool
     */
    public function contains(Certificate $certificate);

    /**
     * Get a certificate by a serial number.
     *
     * @param string $serialNumber The hex encoded serial number.
     * @return false|SetaPDF_Signer_X509_Certificate
     */
    public function getBySerialNumber($serialNumber);

    /**
     * Get certificate by subject key identifier.
     *
     * @param string $subjectKeyIdentifier
     * @return false|SetaPDF_Signer_X509_Certificate
     */
    public function getBySubjectKeyIdentifier($subjectKeyIdentifier);

    /**
     * Get all certificates by a subject name.
     *
     * @param string $subject
     * @param bool $fullMatch Whether only a substring matched or the whole subject should be compared.
     * @return SetaPDF_Signer_X509_CollectionInterface
     */
    public function findBySubject($subject, $fullMatch = false);

    /**
     * Get all certificates by a issuer name.
     *
     * @param string $issuer
     * @param bool $fullMatch Whether only a substring matched or the whole subject should be compared.
     * @return SetaPDF_Signer_X509_CollectionInterface
     */
    public function findByIssuer($issuer, $fullMatch = false);

    /**
     * Find all valid certificates by date and time.
     *
     * @param DateTimeInterface $dateTime
     * @param DateTimeZone|null $timeZone
     * @return SetaPDF_Signer_X509_CollectionInterface
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws Exception
     */
    public function findByValidAt(DateTimeInterface $dateTime, DateTimeZone $timeZone = null);
}