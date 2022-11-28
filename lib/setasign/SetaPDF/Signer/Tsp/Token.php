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

use SetaPDF_Signer_Cms_SignedData as SignedData;
use SetaPDF_Signer_Asn1_Time as Time;
use SetaPDF_Signer_X509_Certificate as Certificate;

/**
 * Class representing a Timestamp Token
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Tsp_Token extends SignedData
{
    /**
     * The token message.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_message;

    /**
     * @var
     */
    protected $_tstInfo;

    /**
     * {@link SetaPDF_Signer_Tsp_Token}  constructor.
     *
     * @param SetaPDF_Signer_Asn1_Element $message
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function __construct(SetaPDF_Signer_Asn1_Element $message)
    {
        /* TimeStampToken ::= ContentInfo
         *   -- contentType is id-signedData ([CMS]) <-- 1.2.840.113549.1.7.2
         *   -- content is SignedData ([CMS])
         */
        $tsToken = $message;

        /* SignedData ::= SEQUENCE {
         *   version Version,
         *   digestAlgorithms DigestAlgorithmIdentifiers,
         *   contentInfo ContentInfo,
         *   certificates
         *      [0] IMPLICIT ExtendedCertificatesAndCertificates
         *        OPTIONAL,
         *   crls
         *      [1] IMPLICIT CertificateRevocationLists OPTIONAL,
         *   signerInfos SignerInfos }
         */
        $content = SetaPDF_Signer_Asn1_Element::findByPath('1/0', $tsToken);

        if (!$content || $content->getChildCount() < 3) {
            throw new InvalidArgumentException(
                'SignedData structure in timestamp response not found or invalid.'
            );
        }
        $contentInfo = $content->getChild(2);

        $contentType = SetaPDF_Signer_Asn1_Oid::decode($contentInfo->getChild(0)->getValue());
        if ($contentType !== '1.2.840.113549.1.9.16.1.4') {
            throw new InvalidArgumentException(
                sprintf('Timestamp response uses invalid content type: %s', $contentType)
            );
        }

        $tstInfo = SetaPDF_Signer_Asn1_Element::parse(
            SetaPDF_Signer_Asn1_Element::findByPath('1/0', $contentInfo)->getValue()
        );

        // check for messageImprint
        $messageImprint = $tstInfo->getChild(2);
        if (!$messageImprint ||  $messageImprint->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new InvalidArgumentException(
                'Timestamp response has no or invalid message imprint.'
            );
        }

        $this->_tstInfo = $tstInfo;
        $this->_message = $message;
    }

    /**
     * Get the timestamp token info element.
     *
     * @return SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    protected function _getTstInfo()
    {
        return $this->_tstInfo;
    }

    /**
     * Get the message imprint element.
     *
     * @return bool|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getMessageImprint()
    {
        return $this->_getTstInfo()->getChild(2);
    }

    /**
     * Get the serial number of the token.
     *
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getSerialNumber()
    {
        return SetaPDF_Core_Type_HexString::str2hex(
            $this->_getTstInfo()->getChild(3)->getValue()
        );
    }

    /**
     * Get the gerneation time.
     *
     * @return DateTime
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getGenerationTime()
    {
        $genTime = $this->_getTstInfo()->getChild(4);
        return Time::decode($genTime);
    }

    /**
     * Get the none value.
     *
     * @return bool|string
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function getNonce()
    {
        $tstInfo = $this->_getTstInfo();
        $offset = 5;

        $element = $tstInfo->getChild($offset);
        while ($element && $element->getIdent() !== SetaPDF_Signer_Asn1_Element::INTEGER) {
            $element = $tstInfo->getChild(++$offset);
        }

        if ($element === false) {
            return false;
        }

        return $element->getValue();
    }

    /**
     * Overwritte to disable this method. It is not allowed to use it in a timestamp token instance.
     *
     * @param SetaPDF_Core_Reader_FilePath|string $detachedSignedData
     */
    public function setDetachedSignedData($detachedSignedData)
    {
        throw new BadMethodCallException('A timestamp response does not requires detached signed data.');
    }

    /**
     * @inheritDoc
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function verify(Certificate $signerCertificate)
    {
        $this->_detachedSignedData = $this->_getTstInfo();
        $result = parent::verify($signerCertificate);
        $this->_detachedSignedData = null;

        return $result;
    }

    /**
     * Verifies the message imprint.
     *
     * @param string|SetaPDF_Core_Reader_FilePath $data
     * @return bool
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function verifyMessageImprint($data)
    {
        $messageImprint = $this->getMessageImprint();
        $hashAlgorithmOid = SetaPDF_Signer_Asn1_Oid::decode($messageImprint->getChild(0)->getChild(0)->getValue());
        $hashAlgorithm = SetaPDF_Signer_Digest::getByOid($hashAlgorithmOid);
        if ($hashAlgorithm) {
            if ($data instanceof SetaPDF_Core_Reader_FilePath) {
                $hash = hash_file($hashAlgorithm, $data->getPath(), true);
            } else {
                $hash = hash($hashAlgorithm, $data, true);
            }

            return ($hash === $messageImprint->getChild(1)->getValue());
        }

        throw new SetaPDF_Signer_Exception(sprintf('Unsupported hash algorithm (%s).', $hashAlgorithmOid));
    }
}