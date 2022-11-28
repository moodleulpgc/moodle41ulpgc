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

use SetaPDF_Signer_Tsp_Token as Token;

/**
 * Class representing a Timestamp Response.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Tsp_Response
{
    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_GRANTED = 0;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_GRANTED_WITH_MODS = 1;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_REJECTION = 2;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_WAITING = 3;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_REVOCATION_WARNING = 4;

    /**
     * Status constant.
     *
     * @var int
     */
    const STATUS_REVOCATION_NOTIFICATION = 5;

    /**
     * The timestamp token instance.
     *
     * @var Token|null
     */
    protected $_token;

    /**
     * The response message.
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_message;

    /**
     * The constructor.
     *
     * @param SetaPDF_Signer_Asn1_Element|string $response
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function __construct($response)
    {
        if (!$response instanceof SetaPDF_Signer_Asn1_Element) {
            try {
                $response = SetaPDF_Signer_Asn1_Element::parse($response);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Invalid data structure for a Timestamp response.', null, $e);
            }
        }

        // We need at least PKI Status Info
        if ($response->getChildCount() < 1) {
            throw new InvalidArgumentException('Timestamp response has an invalid TimeStampResp structure.');
        }

        /* Verify the status error
         *
         * PKIStatusInfo ::= SEQUENCE {
         *    status        PKIStatus,
         *    statusString  PKIFreeText     OPTIONAL,
         *    failInfo      PKIFailureInfo  OPTIONAL  }
         */
        $pkiStatusInfo = $response->getChild(0);
        if ($pkiStatusInfo->getChildCount() < 1) {
            throw new InvalidArgumentException('Timestamp response has invalid PKIStatusInfo structure.');
        }

        $status = ord($pkiStatusInfo->getChild(0)->getValue());
        if ($status === self::STATUS_GRANTED || $status === self::STATUS_GRANTED_WITH_MODS) {
            if ($response->getChildCount() < 2) {
                throw new InvalidArgumentException(
                    'Timestamp response has invalid TimeStampResp structure (timeStampToken is missing).'
                );
            }

            $this->_token = new Token($response->getChild(1));

        } elseif ($status > 5) {
            throw new SetaPDF_Signer_Exception(
                sprintf('Timestamp response has invalid status (%s).', $status)
            );
        }

        $this->_message = $response;
    }

    /**
     * Get the ASN.1 instance of the timestamp response.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    public function getAsn1()
    {
        return $this->_message;
    }

    /**
     * Get the status of the timestamp response.
     *
     * @return int
     */
    public function getStatus()
    {
        $pkiStatusInfo = $this->_message->getChild(0);
        return ord($pkiStatusInfo->getChild(0)->getValue());
    }

    /**
     * Get the timestamp token of the response.
     *
     * @return SetaPDF_Signer_Tsp_Token|null
     */
    public function getToken()
    {
        return $this->_token;
    }
}
