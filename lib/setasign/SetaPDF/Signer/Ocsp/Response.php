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

use SetaPDF_Signer_Asn1_DistinguishedName as DistinguishedName;
use SetaPDF_Signer_Asn1_Signed as Signed;
use SetaPDF_Signer_Asn1_Time as Time;
use SetaPDF_Signer_Ocsp_SingleResponse as SingleResponse;
use SetaPDF_Signer_X509_Certificate as Certificate;
use SetaPDF_Signer_X509_Collection as Collection;

/**
 * Class representing an OCSPResponse structure.
 *
 * OCSPResponse ::= SEQUENCE {
 *     responseStatus         OCSPResponseStatus,
 *     responseBytes          [0] EXPLICIT ResponseBytes OPTIONAL }
 *
 * OCSPResponseStatus ::= ENUMERATED {
 *     successful            (0),  --Response has valid confirmations
 *     malformedRequest      (1),  --Illegal confirmation request
 *     internalError         (2),  --Internal error in issuer
 *     tryLater              (3),  --Try again later
 *                                 --(4) is not used
 *     sigRequired           (5),  --Must sign the request
 *     unauthorized          (6)   --Request unauthorized
 * }
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Ocsp_Response extends Signed
{
    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_SUCCESSFUL = 0;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_MALFORMED_REQUEST = 1;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_INTERNAL_ERROR = 2;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_TRY_LATER = 3;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_SIG_REQUIRED = 5;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_UNAUTHORIZED = 6;

    /**
     * The response message.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_response;

    /**
     * The basic response element if available.
     *
     * @var null|SetaPDF_Signer_Asn1_Element
     */
    protected $_basicResponse;

    /**
     * An array of the single responses.
     *
     * @var SingleResponse[]
     */
    protected $_singleResponses;

    /**
     * The constructor.
     *
     * @param string $response
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    public function __construct($response)
    {
        $response = SetaPDF_Signer_Asn1_Element::parse($response);
        if (
            $response->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new InvalidArgumentException('Invalid OCSP response structure.');
        }

        $this->_response = $response;
    }

    /**
     * Get the ASN.1 instance of the OCSP response.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    public function getAsn1()
    {
        return $this->_response;
    }

    /**
     * Get the response as an ASN.1 string.
     *
     * @return string
     */
    public function get()
    {
        return (string)$this->getAsn1();
    }

    /**
     * Get the status of the response.
     *
     * @return int See {@link self::STATUS_*} constants.
     * @throws SetaPDF_Signer_Exception
     */
    public function getStatus()
    {
        $responseStatus = $this->_response->getChild(0);
        if (!$responseStatus || $responseStatus->getIdent() !== SetaPDF_Signer_Asn1_Element::ENUMERATED) {
            throw new SetaPDF_Signer_Exception('Invalid OCSP response status structure.');
        }

        return ord($responseStatus->getValue());
    }

    /**
     * Get the basic response element.
     *
     * @return bool|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Asn1_Exception|SetaPDF_Signer_Exception
     */
    protected function _getBasicResponse()
    {
        if ($this->_basicResponse !== null) {
            return $this->_basicResponse;
        }

        $responseBytes = $this->_response->getChild(1);
        if (!$responseBytes ||
            $responseBytes->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED)
        ) {
            return false;
        }

        $responseBytes = $responseBytes->getChild(0);
        if (!$responseBytes ||
            $responseBytes->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid OCSP response bytes structure.');
        }

        $responseType = $responseBytes->getChild(0);
        if (!$responseType || $responseType->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER) {
            throw new SetaPDF_Signer_Exception('Invalid OCSP response type.');
        }

        $responseType = SetaPDF_Signer_Asn1_Oid::decode($responseType->getValue());
        if ($responseType !== '1.3.6.1.5.5.7.48.1.1') { // basic-response
            throw new SetaPDF_Signer_Exception('OCSP response is not a basic-response type.');
        }

        $response = $responseBytes->getChild(1);
        if (!$response || $response->getIdent() !== SetaPDF_Signer_Asn1_Element::OCTET_STRING) {
            throw new SetaPDF_Signer_Exception('Invalid data in OCSP response.');
        }

        $this->_basicResponse = SetaPDF_Signer_Asn1_Element::parse($response->getValue());

        return $this->_basicResponse;
    }

    /**
     * Get the responseData.
     *
     * @return bool|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    protected function _getResponseData()
    {
        $basicResponse = $this->_getBasicResponse();
        if ($basicResponse === false) {
            return false;
        }

        $responseData = $basicResponse->getChild(0);
        if (!$responseData ||
            $responseData->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid data in OCSP response (responseData).');
        }

        return $responseData;
    }

    /**
     * Get the producedAt information.
     *
     * @return DateTime|null
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getProducedAt()
    {
        $responseData = $this->_getResponseData();
        if ($responseData === false) {
            return null;
        }

        $offset = 1;

        // if version is set
        if ($responseData->getChild($offset)->getIdent() === SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $producedAt = $responseData->getChild($offset);

        if ($producedAt->getIdent() === SetaPDF_Signer_Asn1_Element::GENERALIZED_TIME) {
            return Time::decode($producedAt);
        }

        throw new SetaPDF_Signer_Exception('Invalid data in OCSP response (responseData > producedAt).');
    }

    /**
     * Get all certificates embedded in the response.
     *
     * @return SetaPDF_Signer_X509_Collection
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getCertificates()
    {
        $collection = new Collection();

        $basicResponse = $this->_getBasicResponse();
        if ($basicResponse === false) {
            return $collection;
        }

        $certificates = $basicResponse->getChild(3);
        if (!$certificates) {
            return $collection;
        }

        if (
            $certificates->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid data in OCSP response (certificates).');
        }

        $certificates = $certificates->getChild(0);
        if (!$certificates ||
            $certificates->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid data in OCSP response (certificates).');
        }

        for ($i = 0, $c = $certificates->getChildCount(); $i < $c; $i++) {
            $certificate = (string)$certificates->getChild($i);
            $collection->add(new Certificate($certificate));
        }

        return $collection;
    }

    /**
     * Get an extension from the response by its OID.
     *
     * @param string $extensionOid
     * @return bool|SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    protected function _getExtension($extensionOid)
    {
        $responseData = $this->_getResponseData();
        $offset = 3;

        // if version is set
        if ($responseData->getChild(0)->getIdent() === SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $responseExtensions = $responseData->getChild($offset);
        if (!$responseExtensions || $responseExtensions->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC | SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | "\x01")
        ) {
            return false;
        }

        $responseExtensions = $responseExtensions->getChild(0);
        if (!$responseExtensions ||
            $responseExtensions->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            return false;
        }

        foreach ($responseExtensions->getChildren() as $responseExtension) {
            if ($responseExtension->getIdent() !==
                (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
            ) {
                continue;
            }

            $oid = $responseExtension->getChild(0);
            if (!$oid || $oid->getIdent() !== SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER) {
                continue;
            }

            $oid = SetaPDF_Signer_Asn1_Oid::decode($oid->getValue());

            if ($oid === $extensionOid) {
                return $responseExtension;
            }
        }

        return false;
    }

    /**
     * Get the nonce value from the response.
     *
     * @return bool|string
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getNonce()
    {
        $nonceExtension = $this->_getExtension('1.3.6.1.5.5.7.48.1.2');
        if (!$nonceExtension) {
            return false;
        }

        $nonce = $nonceExtension->getChild(1);
        return $nonce->getValue();
    }

    /**
     * Get the single responses from the response.
     *
     * @return SingleResponse[]
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getSingleResponses()
    {
        if ($this->_singleResponses !== null) {
            return $this->_singleResponses;
        }

        $responseData = $this->_getResponseData();
        if ($responseData === false) {
            $this->_singleResponses = [];
            return $this->_singleResponses;
        }

        $offset = 2;

        // if version is set
        if ($responseData->getChild($offset)->getIdent() === SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $responses = $responseData->getChild($offset);
        if (!$responses ||
            $responses->getIdent() !==
            (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE)
        ) {
            throw new SetaPDF_Signer_Exception('Invalid data in OCSP response (responsedata > responses).');
        }

        $this->_singleResponses = [];
        foreach ($responses->getChildren() as $response) {
            $this->_singleResponses[] = new SingleResponse($response);
        }

        return $this->_singleResponses;
    }

    /**
     * Checks if this response object is "good".
     *
     * Evaluates to true if the status of the whole response is {@link self::STATUS_SUCCESSFUL} and the certificate
     * status of all single responses are {@link SingleResponse::CERT_STATUS_GOOD}.
     *
     * @return bool
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function isGood()
    {
        if ($this->getStatus() !== self::STATUS_SUCCESSFUL) {
            return false;
        }

        foreach ($this->getSingleResponses() as $singleResponse) {
            if ($singleResponse->getCertStatus() !== SingleResponse::CERT_STATUS_GOOD) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the responder id from the responder data.
     *
     * @return string|null
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getResponderId()
    {
        $responseData = $this->_getResponseData();
        if ($responseData === false) {
            return null;
        }

        $offset = 0;

        // if version is set
        if ($responseData->getChild($offset)->getIdent() === SetaPDF_Signer_Asn1_Element::INTEGER) {
            $offset++;
        }

        $responderId = $responseData->getChild($offset);

        $constructed = SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC;

        if ($responderId->getIdent() === ($constructed | "\x01")) {
            return DistinguishedName::getAsString($responderId->getChild(0));
        }

        if ($responderId->getIdent() === ($constructed | "\x02")) {
            return SetaPDF_Core_Type_HexString::str2hex($responderId->getChild(0)->getValue());
        }

        throw new SetaPDF_Signer_Exception('Invalid data in OCSP response (responseData > responderId).');
    }

    /**
     * @inheritDoc
     * @return array|null
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignatureAlgorithm()
    {
        $basicResponse = $this->_getBasicResponse();
        if (!$basicResponse) {
            return null;
        }

        $signatureAlgorithm = $basicResponse->getChild(1);

        return [
            SetaPDF_Signer_Asn1_Oid::decode($signatureAlgorithm->getChild(0)->getValue()),
            $signatureAlgorithm->getChild(1)
        ];
    }

    /**
     * @inheritDoc
     * @return string|null
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignatureValue($hex = true)
    {
        $basicResponse = $this->_getBasicResponse();
        if (!$basicResponse) {
            return null;
        }

        $signatureValue = $basicResponse->getChild(2)->getValue();
        $signatureValue = substr($signatureValue, 1);

        if ($hex) {
            return SetaPDF_Core_Type_HexString::str2hex($signatureValue);
        }

        return $signatureValue;
    }

    /**
     * @inheritDoc
     * @return bool|string
     * @throws SetaPDF_Signer_Exception
     */
    public function getSignedData()
    {
        $basicResponse = $this->_getBasicResponse();
        if (!$basicResponse) {
            return false;
        }

        return (string)$basicResponse->getChild(0);
    }
}