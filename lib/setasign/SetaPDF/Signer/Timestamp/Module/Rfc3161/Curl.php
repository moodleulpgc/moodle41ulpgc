<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Curl.php 1425 2020-02-17 09:39:58Z jan.slabon $
 */

/**
 * A timestamp module using the RFC 3161 Standard
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Timestamp_Module_Rfc3161_Curl extends SetaPDF_Signer_Timestamp_Module_Rfc3161
implements SetaPDF_Signer_Timestamp_Module_ModuleInterface
{
    /**
     * User agent send in timestamp request
     *
     * Required by most TS servers
     *
     * @var string
     */
    static public $userAgent = 'SetaPDF-Signer Component TS-Module/2.0';

    /**
     * The last response of the timestamp server
     *
     * @var string
     */
    protected $_lastResponse;

    /**
     * Additional curl options
     *
     * @var array
     */
    protected $_curlOptions = [];

    /**
     * The constructor.
     *
     * @param string $url URL of the timestamp server
     */
    public function __construct($url = null)
    {
        if (null !== $url) {
            $this->setUrl($url);
        }
    }

    /**
     * Sets the URL of the timestamp server.
     *
     * @param string $url The url to the timestamp server
     */
    public function setUrl($url)
    {
        $this->setCurlOption(CURLOPT_URL, $url);
    }

    /**
     * Returns the URL of the timestamp server.
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
            foreach ($option AS $_option => $value) {
                $this->setCurlOption($_option, $value);
            }
        } else {
            $this->_curlOptions[$option] = $value;
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
     * Send the timestamp request and evaluates the response.
     *
     * @param string $timeStampRequest
     * @return bool
     * @throws SetaPDF_Signer_Exception
     */
    protected function _createTimestamp($timeStampRequest)
    {
        $curl = curl_init();
        $curlOptions = $this->_curlOptions;

        if ($this->getCurlOption(CURLOPT_USERAGENT) === null) {
            $curlOptions[CURLOPT_USERAGENT] = self::$userAgent;
        }

        $curlOptions[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/timestamp-query',
            'Accept: application/timestamp-reply, application/timestamp-response',
            'Content-Length: ' . strlen($timeStampRequest),
            'Pragma: no-cache'
        ];

        $curlOptions[CURLOPT_BINARYTRANSFER] = true;
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_POSTFIELDS] = $timeStampRequest;
        $curlOptions[CURLOPT_POST] = true;

        curl_setopt_array($curl, $curlOptions);

        $this->_lastResponse = curl_exec($curl);
        if ($this->getLastResponse() === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new SetaPDF_Signer_Exception('cURL error: ' . $error);
        }

        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpStatus !== 200) {
            curl_close($curl);
            throw new SetaPDF_Signer_Exception(
                sprintf('Timestamp server (%s) returned HTTP status: %s', $this->getUrl(), $httpStatus)
            );
        }

        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        if (
            strpos($contentType, 'application/timestamp-reply') !== 0 &&
            strpos($contentType, 'application/timestamp-response') !== 0
        )  {
            curl_close($curl);
            throw new SetaPDF_Signer_Exception(
                sprintf('Timestamp server (%s) returned invalid content type: %s', $this->getUrl(), $contentType)
            );
        }

        curl_close($curl);

        return true;
    }

    /**
     * Returns the last response of the timestamp server.
     *
     * @return string
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }
}