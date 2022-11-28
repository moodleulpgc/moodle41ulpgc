<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Rfc3161.php 1596 2020-12-16 16:13:10Z jan.slabon $
 */

/**
 * Abstract class representing a timestamp module of the standard RFC 3161
 *
 * RFC 3161 describes the format of a request sent to a Time Stamp Authority and of the response that is returned.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Signer_Timestamp_Module_Rfc3161 extends SetaPDF_Signer_Timestamp_Module_AbstractModule
{
    /**
     * PKI Status values
     *
     * @var array
     */
    static public $statusCodes = [
        SetaPDF_Signer_Tsp_Response::STATUS_GRANTED => 'granted',
        SetaPDF_Signer_Tsp_Response::STATUS_GRANTED_WITH_MODS => 'grantedWithMods',
        SetaPDF_Signer_Tsp_Response::STATUS_REJECTION => 'rejection',
        SetaPDF_Signer_Tsp_Response::STATUS_WAITING => 'waiting',
        SetaPDF_Signer_Tsp_Response::STATUS_REVOCATION_WARNING => 'revocationWarning'
    ];

    /**
     * The value for reqPolicy in timestamp request
     *
     * @var string
     */
    protected $_reqPolicy = null;

    /**
     * Defines if the nonce value should be included in the timestamp request
     *
     * @var boolean
     */
    protected $_nonce = true;

    /**
     * The value of the nonce value
     *
     * @var string
     */
    protected $_nonceValue = null;

    /**
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_lastMessageImprint;

    /**
     * Set the reqPolicy value / OID.
     *
     * "2.4.1 Request Format:
     *  [...]
     *  The reqPolicy field, if included, indicates the TSA policy under
     *  which the TimeStampToken SHOULD be provided."
     *
     * @see RFC 3161 - 2.4.1. Request Format
     * @param string $reqPolicy
     */
    public function setReqPolicy($reqPolicy)
    {
        $this->_reqPolicy = $reqPolicy;
    }

    /**
     * Get the reqPolicy value / OID.
     *
     * @see setReqPolicy()
     * @return string
     */
    public function getReqPolicy()
    {
        return $this->_reqPolicy;
    }

    /**
     * Define if the nonce value should be set or not
     *
     * "2.4.1 Request Format:
     *  [...]
     *  The nonce, if included, allows the client to verify the timeliness of
     *  the response when no local clock is available.  The nonce is a large
     *  random number with a high probability that the client generates it
     *  only once (e.g., a 64 bit integer).  In such a case the same nonce
     *  value MUST be included in the response, otherwise the response shall
     *  be rejected."
     *
     * @see RFC 3161 - 2.4.1. Request Format
     * @param boolean $nonce
     */
    public function setNonce($nonce)
    {
        $this->_nonce = (boolean)$nonce;
    }

    /**
     * Queries if nonce should be set.
     *
     * @see setNonce()
     * @return boolean
     */
    public function getNonce()
    {
        return $this->_nonce;
    }

    /**
     * Create the timestamp signature.
     *
     * @param string $data |SetaPDF_Core_File_Reader
     * @return string
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function createTimestamp($data)
    {
        $timeStampRequest = $this->_getTimestampRequest($data);

        $this->_createTimestamp($timeStampRequest);

        try {
            $tsResponse = new SetaPDF_Signer_Tsp_Response($this->getLastResponse());
        } catch (InvalidArgumentException $e) {
            throw new SetaPDF_Signer_Timestamp_Module_Rfc3161_Exception($e->getMessage(), null, $e);
        }

        $this->_verifyResponse($tsResponse);

        return (string)$tsResponse->getToken()->getAsn1();
    }

    /**
     * Returns the last response of the timestamp server.
     *
     * @return string
     */
    abstract public function getLastResponse();

    /**
     * @param string $timeStampRequest
     * @return bool
     */
    abstract protected function _createTimestamp($timeStampRequest);

    /**
     * Creates the timestamp request structure.
     *
     * @param string|SetaPDF_Core_Reader_FilePath $data
     * @return string
     */
    protected function _getTimestampRequest($data)
    {
        $this->_lastMessageImprint = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
            '',
            [
                // hashAlgorithm
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
                    '',
                    [
                        new SetaPDF_Signer_Asn1_Element(
                            SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                            SetaPDF_Signer_Asn1_Oid::encode(SetaPDF_Signer_Digest::getOid($this->getDigest()))
                        ),
                        new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::NULL)
                    ]
                ),
                // hashedMessage
                new SetaPDF_Signer_Asn1_Element(
                    SetaPDF_Signer_Asn1_Element::OCTET_STRING,
                    $this->_getHash($data)
                )
            ]
        );

        $_data = [
            new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::INTEGER, "\x01"),
            $this->_lastMessageImprint
        ];

        // reqPolicy: optional
        if ($this->getReqPolicy()) {
            $_data[] = new SetaPDF_Signer_Asn1_Element(
                SetaPDF_Signer_Asn1_Element::OBJECT_IDENTIFIER,
                SetaPDF_Signer_Asn1_Oid::encode($this->getReqPolicy())
            );
        }

        // nonce: optional
        if ($this->_nonce) {
            $this->_nonceValue = SetaPDF_Core_SecHandler::generateRandomBytes(8);
            // make sure that the first byte is not completely set or empty because some timestamp servers drop it otherwise (DFN)
            $this->_nonceValue[0] = ($this->_nonceValue[0] & "\x7F") | "\x01";
            $_data[] = new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::INTEGER, $this->_nonceValue);
        }

        // certReq : optional - if not set Acrobat will fail to validate the signature
        $_data[] = new SetaPDF_Signer_Asn1_Element(SetaPDF_Signer_Asn1_Element::BOOLEAN, "\xFF");

        $timeStampRequest = new SetaPDF_Signer_Asn1_Element(
            SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE,
            '',
            $_data
        );

        return (string)$timeStampRequest;
    }

    protected function _verifyResponse(SetaPDF_Signer_Tsp_Response $tsResponse)
    {
        $status = $tsResponse->getStatus();
        if ($status !== SetaPDF_Signer_Tsp_Response::STATUS_GRANTED) {
            throw new SetaPDF_Signer_Timestamp_Module_Rfc3161_Exception(
                sprintf(
                    'Timestamp response returned status flag %s: %s',
                    $status,
                    isset(self::$statusCodes[$status]) ? self::$statusCodes[$status] : 'unknown'
                ),
                $status
            );
        }

        $token = $tsResponse->getToken();
        if ((string)$token->getMessageImprint() !== (string)$this->_lastMessageImprint) {
            throw new SetaPDF_Signer_Timestamp_Module_Rfc3161_Exception(
                'Timestamp response returned different MessageImprint value.'
            );
        }

        if ($this->_nonceValue !== null && $this->_nonceValue !== $token->getNonce()) {
            throw new SetaPDF_Signer_Exception(
                sprintf(
                    'Timestamp response returned invalid nonce value (0x%s vs. 0x%s).',
                    SetaPDF_Core_Type_HexString::str2hex($this->_nonceValue),
                    SetaPDF_Core_Type_HexString::str2hex($token->getNonce())
                )
            );
        }

        return true;
    }
}