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
use SetaPDF_Signer_X509_CollectionInterface as CollectionInterface;
use SetaPDF_Signer_X509_Extension_SubjectKeyIdentifier as SubjectKeyIdentifier;

/**
 * Class representing a collection of X509 certificates.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_X509_Collection implements CollectionInterface
{
    /**
     * All items.
     *
     * @var Certificate[]|SetaPDF_Signer_X509_CollectionInterface[]
     */
    protected $_items = [];

    /**
     * The constructor.
     *
     * @param null|string|Certificate|Certificate[]|CollectionInterface $certificates
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function __construct($certificates = null)
    {
        if ($certificates !== null) {
            $this->add($certificates);
        }
    }

    /**
     * Get all items which are not direct certificates but implement the CollectionInterface.
     *
     * @return CollectionInterface[]
     */
    protected function _getCollectionItems()
    {
        return array_filter($this->_items, static function($item) {
            return $item instanceof CollectionInterface;
        });
    }

    /**
     * Add a certificate, certificates or collections.
     *
     * @param string|Certificate|Certificate[]|CollectionInterface $certificates
     * @return $this
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function add($certificates)
    {
        if (is_array($certificates)) {
            foreach ($certificates as $_certificate) {
                $this->add($_certificate);
            }
            return $this;
        }

        if ($certificates instanceof CollectionInterface) {
            $this->_items[spl_object_hash($certificates)] = $certificates;
            return $this;
        }

        if (!($certificates instanceof Certificate)) {
            $certificates = new Certificate($certificates);
        }

        $this->_items[$certificates->getDigest('md5', true)] = $certificates;

        return $this;
    }

    /**
     * Add a certificate from a path.
     *
     * @param string $path
     * @return $this
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function addFromFile($path)
    {
        $this->add(Certificate::fromFile($path));

        return $this;
    }

    /**
     * Count all items.
     *
     * Notice: that this method collects ALL items to count only unique elements.
     *
     * @return int
     */
    public function count()
    {
        return count($this->getAll());
    }

    /**
     * Get all certificates.
     *
     * @return SetaPDF_Signer_X509_Certificate[]
     */
    public function getAll()
    {
        $result = [];

        foreach ($this->_items as $item) {
            if ($item instanceof CollectionInterface) {
                foreach ($item->getAll() as $_item) {
                    $result[$_item->getDigest('md5', true)] = $_item;
                }
            } else {
                $result[$item->getDigest('md5', true)] = $item;
            }
        }

        return array_values($result);
    }

    /**
     * @inheritDoc
     */
    public function contains(Certificate $certificate)
    {
        $result = isset($this->_items[$certificate->getDigest('md5', true)]);
        if ($result === true) {
            return true;
        }

        foreach ($this->_getCollectionItems() as $collectionItem) {
            if ($collectionItem->contains($certificate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Exception
     */
    public function getBySerialNumber($serialNumber)
    {
        $serialNumber = strtolower($serialNumber);

        foreach ($this->_items as $certificate) {
            if ($certificate instanceof CollectionInterface) {
                $found = $certificate->getBySerialNumber($serialNumber);
                if ($found) {
                    return $found;
                }
                continue;
            }

            if ($certificate->getSerialNumber() === $serialNumber) {
                return $certificate;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getBySubjectKeyIdentifier($subjectKeyIdentifier)
    {
        foreach ($this->_items as $certificate) {
            if ($certificate instanceof CollectionInterface) {
                $found = $certificate->getBySubjectKeyIdentifier($subjectKeyIdentifier);
                if ($found) {
                    return $found;
                }
                continue;
            }

            /** @var SubjectKeyIdentifier $extension */
            $extension = $certificate->getExtensions()->get(SubjectKeyIdentifier::OID);
            if ($extension === false) {
                continue;
            }

            if ($extension->getKeyIdentifier() === $subjectKeyIdentifier) {
                return $certificate;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function findBySubject($subject, $fullMatch = false)
    {
        $result = new self();
        foreach ($this->_items as $certificate) {
            if ($certificate instanceof CollectionInterface) {
                $tmpResult = $certificate->findBySubject($subject, $fullMatch);
                if (count($tmpResult) > 0) {
                    $result->add($tmpResult);
                }
                continue;
            }

            $currentSubject = $certificate->getSubjectName();
            if (
                ($fullMatch && $currentSubject === $subject) ||
                (!$fullMatch && (strpos($currentSubject, $subject) !== false))
            ) {
                $result->add($certificate);
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function findByIssuer($issuer, $fullMatch = false)
    {
        $result = new self();
        foreach ($this->_items as $certificate) {
            if ($certificate instanceof CollectionInterface) {
                $tmpResult = $certificate->findByIssuer($issuer, $fullMatch);
                if (count($tmpResult) > 0) {
                    $result->add($tmpResult);
                }
                continue;
            }

            $currentIssuer = $certificate->getIssuerName();
            if (
                ($fullMatch && $currentIssuer === $issuer) ||
                (!$fullMatch && (strpos($currentIssuer, $issuer) !== false))
            ) {
                $result->add($certificate);
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws Exception
     */
    public function findByValidAt(DateTimeInterface $dateTime, DateTimeZone $timeZone = null)
    {
        $result = new self();
        foreach ($this->_items as $certificate) {
            if ($certificate instanceof CollectionInterface) {
                $tmpResult = $certificate->findByValidAt($dateTime, $timeZone);
                if (count($tmpResult) > 0) {
                    $result->add($tmpResult);
                }
                continue;
            }

            if ($certificate->isValidAt($dateTime, $timeZone)) {
                $result->add($certificate);
            }
        }

        return $result;
    }

    /**
     * Find all certificates by a callback.
     *
     * @param callable $callback
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function findByCallback($callback)
    {
        $result = new self();
        foreach ($this->getAll() as $certificate) {
            if ($callback($certificate) === true) {
                $result->add($certificate);
            }
        }

        return $result;
    }
}