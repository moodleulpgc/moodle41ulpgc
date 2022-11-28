<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: OpenSslCli.php 1505 2020-07-21 13:54:06Z jan.slabon $
 */

/**
 * A signature module which uses the S/MIME utility of OpenSSL via command line.
 *
 * The S/MIME utility is described here: {@link http://www.openssl.org/docs/apps/smime.html}.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Signature_Module_OpenSslCli implements
    SetaPDF_Signer_Signature_Module_ModuleInterface, SetaPDF_Signer_DigestInterface
{
    /**
     * The digest algorithm to use when signing
     *
     * Explicity set to null, to keep behaviour as of the old versions.
     *
     * @var string
     */
    protected $_digest = null;

    /**
     * The path to the signing certificate
     *
     * @var string
     */
    protected $_certificate;

    /**
     * The path to the private key to use when signing
     *
     * @var string
     */
    protected $_privateKey;

    /**
     * The private key password source
     *
     * @see http://www.openssl.org/docs/apps/openssl.html#PASS_PHRASE_ARGUMENTS
     * @var string
     */
    protected $_privateKeyPassword;

    /**
     * The path to the extra/other certificates
     *
     * @var string
     */
    protected $_extraCertificates;

    /**
     * A path to additional certificates to be specified
     *
     * @var string
     */
    protected $_openSslPath = '/usr/bin/';

    /**
     * A temporary path of the signed message
     *
     * @var string
     */
    protected $_outPath;

    /**
     * Returns the name of the OpenSSL utility.
     *
     * @return string
     */
    protected function _getUtility()
    {
        return 'smime';
    }

    /**
     * Set the path to the openssl binary.
     *
     * @param string $openSslPath
     */
    public function setOpenSslPath($openSslPath)
    {
        $this->_openSslPath = $openSslPath;
    }

    /**
     * Get the path to the openssl binary.
     *
     * @return string
     */
    public function getOpenSslPath()
    {
        return $this->_openSslPath;
    }

    /**
     * Set the path to the signing certificate.
     *
     * @param string $certificate
     * @throws InvalidArgumentException
     */
    public function setCertificate($certificate)
    {
        $certificate = realpath($certificate);
        if (false === $certificate) {
            throw new InvalidArgumentException('Path to certificate is invalid.');
        }
        $this->_certificate = $certificate;
    }

    /**
     * Get the path to the signing certificate.
     *
     * @return string
     */
    public function getCertificate()
    {
        return $this->_certificate;
    }

    /**
     * Set the path to the private key file and password argument.
     *
     * @param array|string $privateKey An array of private key and password or only a private key.
     * @param null $password
     *
     * @throws InvalidArgumentException
     */
    public function setPrivateKey($privateKey, $password = null)
    {
        if (is_array($privateKey)) {
            $password = $privateKey[1];
            $privateKey = $privateKey[0];
        }

        $privateKey = realpath($privateKey);
        if (false === $privateKey) {
            throw new InvalidArgumentException('Path to private key is invalid.');
        }

        $this->_privateKey = $privateKey;
        $this->setPrivateKeyPassword($password);
    }

    /**
     * Get the path to the private key file.
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->_privateKey;
    }

    /**
     * Set the private key password source.
     *
     * @see http://www.openssl.org/docs/apps/openssl.html#PASS_PHRASE_ARGUMENTS
     * @param string $password
     */
    public function setPrivateKeyPassword($password)
    {
        $this->_privateKeyPassword = $password;
    }

    /**
     * Get the private key password source.
     *
     * @return string
     */
    public function getPrivateKeyPassword()
    {
        return $this->_privateKeyPassword;
    }

    /**
     * Set the path to a file with additional certificates which will be included in the signature.
     *
     * If the certificates are saved in separate files, you will need to assemble them in one single file. Just copy
     * them with a text editor one after another.
     *
     * @param null|string $extraCertificates
     * @throws InvalidArgumentException
     */
    public function setExtraCertificates($extraCertificates)
    {
        if (null !== $extraCertificates) {
            $extraCertificates = realpath($extraCertificates);
            if (false === $extraCertificates) {
                throw new InvalidArgumentException('Path to extra certificates is invalid.');
            }
        }

        $this->_extraCertificates = $extraCertificates;
    }

    /**
     * Get the path of the file with additional certificates.
     *
     * @return string
     */
    public function getExtraCertificates()
    {
        return $this->_extraCertificates;
    }

    /**
     * Set the digest algorithm to use when signing.
     *
     * Possible values are defined in {@link SetaPDF_Signer_Digest}.
     *
     * @see SetaPDF_Signer_Digest
     * @param string $digest
     */
    public function setDigest($digest)
    {
        if (null !== $digest && !SetaPDF_Signer_Digest::isValidDigest($digest)) {
            throw  new InvalidArgumentException(sprintf('Invalid digest algorithm "%s".', $digest));
        }

        $this->_digest = $digest;
    }

    /**
     * Get the digest algorithm.
     *
     * @return string
     */
    public function getDigest()
    {
        return $this->_digest;
    }

    /**
     * Create the signature.
     *
     * This method creates a digital signature for the file available in $tmpPath and returns it in binary format.
     *
     * @param SetaPDF_Core_Reader_FilePath $tmpPath
     * @return string
     * @throws SetaPDF_Signer_Exception
     * @throws InvalidArgumentException
     */
    public function createSignature(SetaPDF_Core_Reader_FilePath $tmpPath)
    {
        if (!file_exists($tmpPath) || !is_readable($tmpPath)) {
            throw new InvalidArgumentException('Signature template file cannot be read.');
        }

        $tmpPath = realpath($tmpPath);
        $this->_outPath = SetaPDF_Core_Writer_TempFile::createTempPath();

        $cmd = $this->_createCommand($tmpPath);
        exec($cmd, $output, $retValue);

        if ($retValue !== 0) {
            throw new SetaPDF_Signer_Exception(
                sprintf('An error occurs while calling OpenSSL through CLI (exit code %s).', $retValue)
            );
        }

        $data = file_get_contents($this->_outPath);

        $this->_cleanUp();
        if ('' === $data) {
            throw new SetaPDF_Signer_Exception('Error while extracting the signature of the smime message');
        }

        return $data;
    }

    /**
     * Creates the command line command.
     *
     * @param string $inPath
     * @return string
     */
    protected function _createCommand($inPath)
    {
        return $this->getOpenSslPath()
            . 'openssl ' . $this->_getUtility() . ' -sign -noattr -binary -inform DER -outform DER'
            . ($this->getDigest() ? ' -md ' . escapeshellarg($this->getDigest()) : '')
            . ' -signer ' . escapeshellarg($this->getCertificate())
            . ($this->getPrivateKey() !== null ? ' -inkey ' . escapeshellarg($this->getPrivateKey()) : '')
            . ($this->getExtraCertificates() !== null ? ' -certfile ' . escapeshellarg($this->getExtraCertificates()) : '')
            . ' -passin pass:' . escapeshellarg($this->getPrivateKeyPassword())
            . ' -in ' . escapeshellarg($inPath)
            . ' -out ' . escapeshellarg($this->_outPath);
    }

    /**
     * Removes temporary file if needed.
     */
    protected function _cleanUp()
    {
        if (SetaPDF_Core_Writer_TempFile::getKeepFile() === false) {
            @unlink($this->_outPath);
        }
    }
}