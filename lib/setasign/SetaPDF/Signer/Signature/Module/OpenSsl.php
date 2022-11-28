<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: OpenSsl.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * A signature module which uses the PHP builtin openssl functions.
 *
 * This module make use of the function {@link http://www.php.net/openssl_pkcs7_sign openssl_pkcs7_sign()} to create
 * the signature.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Signature_Module_OpenSsl implements SetaPDF_Signer_Signature_Module_ModuleInterface
{
    /**
     * The certificate parameter
     *
     * @see http://php.net/openssl.certparams
     * @var mixed
     */
    protected $_certificate;

    /**
     * The private key parameter
     *
     * @see http://php.net/openssl.certparams
     * @var mixed
     */
    protected $_privateKey;

    /**
     * The path to a file containing bunch of extra certificates to include in the signature
     *
     * @var string
     */
    protected $_extraCertificates;

    /**
     * File pointer for the signed message
     *
     * @var resource
     */
    protected $_fp;

    /**
     * A temporary path of the signed message
     *
     * @var string
     */
    protected $_outPath;

    /**
     * The constructor.
     *
     * @throws SetaPDF_Signer_Exception
     */
    public function __construct()
    {
        if (!function_exists('openssl_pkcs7_sign')) {
            throw new SetaPDF_Signer_Exception('OpenSSL extension needs to be installed/activated in php.ini');
        }
    }

    /**
     * Set the certificate parameter.
     *
     * This module make use of the build in openssl functions. So the certificate parameter has to be passed as defined
     * here: {@link http://php.net/openssl.certparams}.
     *
     * Some examples:
     * <code>
     * $module->setCertificate(file_get_contents('path/to/certificate.pem'));
     * // or
     * $module->setCertificate('file://path/to/certificate.pem');
     * </code>
     *
     * Notice the "file://" prefix if you need to pass the certificate path instead of its content.
     *
     * @param mixed $certificate
     */
    public function setCertificate($certificate)
    {
        $this->_certificate = $certificate;
    }

    /**
     * Get the certificate parameter.
     *
     * @return mixed
     */
    public function getCertificate()
    {
        return $this->_certificate;
    }

    /**
     * Set the private key parameter.
     *
     * This module make use of the build in openssl functions. So the private key parameter has to be passed as defined
     * here: {@link http://php.net/openssl.certparams}.
     *
     * @param mixed $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->_privateKey = $privateKey;
    }

    /**
     * Get the private key parameter
     *
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->_privateKey;
    }

    /**
     * Set the extra certificate parameter.
     *
     * This parameter specifies the name of a file containing a bunch of extra certificates to include in the signature
     * which can for example be used to help the recipient to verify the certificate that you used.
     *
     * If the certificates are saved in separate files, you will need to assemble them in one single file. Just copy
     * them with a text editor one after another.
     *
     * @param string $extraCertificates
     */
    public function setExtraCertificates($extraCertificates)
    {
        $this->_extraCertificates = $extraCertificates;
    }

    /**
     * Get the extra certificate parameter.
     *
     * @return string
     */
    public function getExtraCertificates()
    {
        return $this->_extraCertificates;
    }

    /**
     * Create the signature.
     *
     * This method creates a digital signature for the file available in $tmpPath and returns it in binary format.
     *
     * @param SetaPDF_Core_Reader_FilePath $tmpPath
     * @return string
     * @throws SetaPDF_Signer_Exception
     * @throws InvalidArgumentException if the $tmpPath isn't readable
     */
    public function createSignature(SetaPDF_Core_Reader_FilePath $tmpPath)
    {
        if (!file_exists($tmpPath) || !is_readable($tmpPath)) {
            throw new InvalidArgumentException('Signature template file cannot be read.');
        }

        $tmpPath = realpath($tmpPath);
        $this->_outPath = SetaPDF_Core_Writer_TempFile::createTempPath();

        if ($this->_extraCertificates !== null) {
            $stat = @openssl_pkcs7_sign(
                $tmpPath,
                $this->_outPath,
                $this->_certificate,
                $this->_privateKey,
                [],
                PKCS7_BINARY | PKCS7_DETACHED,
                $this->_extraCertificates
            );
        } else {
            $stat = @openssl_pkcs7_sign(
                $tmpPath,
                $this->_outPath,
                $this->_certificate,
                $this->_privateKey,
                [],
                PKCS7_BINARY | PKCS7_DETACHED
            );
        }

        if ($stat === false) {
            $this->_cleanUp();
            $errorMsgs = [];
            while ($errorMsg = openssl_error_string()) {
                $errorMsgs[] = $errorMsg;
            }

            throw new SetaPDF_Signer_Exception('OpenSSL error: ' . implode(', ', $errorMsgs));
        }

        if ($stat === null) {
            $lastError = error_get_last();
            throw new SetaPDF_Signer_Exception('OpenSSL error' .
                ($lastError !== null ? ': ' . $lastError['message'] : '') . '.'
            );
        }

        $this->_fp = fopen($this->_outPath, 'rb');

        fseek($this->_fp, -100, SEEK_END);
        $stack = fread($this->_fp, 100);

        if (!preg_match('/-{6}([a-f0-9]{32})-{2}/i', $stack, $m)) {
            $this->_cleanUp();
            throw new SetaPDF_Signer_Exception('Cannot find boundary id in smime message.');
        }

        $boundaryId = $m[1];
        $length = 32;
        $filesize = filesize($this->_outPath);
        // let's read backwards
        fseek($this->_fp, -$length, SEEK_END);
        $pos = ftell($this->_fp);
        $stack = fread($this->_fp, $length);

        // ...until we match the initial boundary id
        while (!preg_match('/-{6}' . $boundaryId . '(?:(\n|\r|\r\n))(.*?)(?:(\n|\r|\r\n]))-{6}/ms', $stack, $m) && $pos > 0) {
            fseek($this->_fp, -(min(strlen($stack) + $length, $filesize)), SEEK_END);
            $pos = ftell($this->_fp);
            $stack = fread($this->_fp, $length) . $stack;
        }

        unset($stack);
        if (!isset($m[2])) {
            $this->_cleanUp();
            throw new SetaPDF_Signer_Exception('Cannot find opening boundary in smime message.');
        }

        $data = preg_split("/(\r\n\r\n|\n\n|\r\r)/", $m[2], 2);
        $data = base64_decode(trim($data[1]));

        $this->_cleanUp();
        if ($data === '') {
            throw new SetaPDF_Signer_Exception('Error while extracting the signature of the smime message');
        }

        return $data;
    }

    /**
     * Close file handles and remove temporary files if needed.
     */
    protected function _cleanUp()
    {
        if (is_resource($this->_fp)) {
            fclose($this->_fp);
        }

        if (SetaPDF_Core_Writer_TempFile::getKeepFile() === false) {
            @unlink($this->_outPath);
        }
    }
}