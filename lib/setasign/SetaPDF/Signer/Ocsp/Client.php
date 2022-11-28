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

use SetaPDF_Signer_Ocsp_Request as Request;
use SetaPDF_Signer_Ocsp_Response as Response;
use SetaPDF_Signer_InformationResolver_HttpCurlResolver as HttpResolver;
use SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface as LoggerInterface;
use SetaPDF_Signer_ValidationRelatedInfo_Logger as Logger;

/**
 * Class representing an OCSP client.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Ocsp_Client
{
    /**
     * The last response of the OCSP responder
     *
     * @var array
     */
    protected $_lastResponse;

    /**
     * Additional curl options
     *
     * @var array
     */
    protected $_curlOptions = [];

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * The constructor.
     *
     * @param null|string $url The URL of the OCSP responder.
     */
    public function __construct($url = null)
    {
        if ($url !== null) {
            $this->setUrl($url);
        }
    }

    /**
     * Set a logger instance.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Get the logger instance.
     *
     * If no logger instance was passed before a new instance of {@link SetaPDF_Signer_ValidationRelatedInfo_Logger} is
     * returned.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function getLogger()
    {
        if ($this->_logger === null) {
            $this->_logger = new Logger();
        }

        return $this->_logger;
    }

    /**
     * Sets the URL of the OCSP responder.
     *
     * @param string $url The url to the OCSP responder
     */
    public function setUrl($url)
    {
        $this->setCurlOption(CURLOPT_URL, (string)$url);
    }

    /**
     * Returns the URL of the OCSP responder.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->getCurlOption(CURLOPT_URL);
    }

    /**
     * Sets one or more Curl options with the assigned value.
     *
     * @see http://www.php.net/curl-setopt
     * @param int|array $option The {@link http://www.php.net/curl.constants CURLOPT_XXX} option to set
     * @param string|null $value The value to be set on option
     */
    public function setCurlOption($option, $value = null)
    {
        if (is_array($option)) {
            foreach ($option AS $_option => $_value) {
                $this->setCurlOption($_option, $_value);
            }
        } else {
            $this->_curlOptions[(int)$option] = $value;
        }
    }

    /**
     * Returns a defined Curl option or null if not set.
     *
     * @param int $option The {@link http://www.php.net/curl.constants CURLOPT_XXX} option to set
     * @return string|null
     */
    public function getCurlOption($option)
    {
        if (isset($this->_curlOptions[$option])) {
            return $this->_curlOptions[$option];
        }

        return null;
    }

    /**
     * Send the OCSP request to the responder.
     *
     * @param SetaPDF_Signer_Ocsp_Request $request
     * @return SetaPDF_Signer_Ocsp_Response
     * @throws SetaPDF_Signer_Asn1_Exception|SetaPDF_Signer_Exception
     */
    public function send(Request $request)
    {
        $requestData = $request->get();

        $curlOptions = $this->_curlOptions;
        $curlOptions[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/ocsp-request',
            'Accept: application/ocsp-response',
            'Content-Length: ' . strlen($requestData),
            'Pragma: no-cache'
        ];

        $curlOptions[CURLOPT_BINARYTRANSFER] = true;
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_POSTFIELDS] = (string)$requestData;
        $curlOptions[CURLOPT_POST] = true;

        $resolver = new HttpResolver($curlOptions);
        $resolver->setLogger($this->getLogger());

        $this->_lastResponse = $resolver->resolve();

        if (strpos($this->_lastResponse[0], 'application/ocsp-response') !== 0)  {
            $this->getLogger()->log(
                'OCSP server ({uri}) responds with invalid content-type "{contentType}".',
                ['uri' => $this->getUrl(), 'contentType' => $this->_lastResponse[0]]
            );

            throw new SetaPDF_Signer_Exception(
                sprintf('OCSP responder (%s) returned invalid content type: %s', $this->getUrl(), $this->_lastResponse[0])
            );
        }

        return new Response($this->_lastResponse[1]);
    }

    /**
     * Returns the last response of the OCSP responder.
     *
     * @return array
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }
}