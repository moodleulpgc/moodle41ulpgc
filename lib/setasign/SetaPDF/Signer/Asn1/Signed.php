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
use SetaPDF_Signer_X509_Format as Format;

/**
 * Abstract class for signed ASN.1 strucutres.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Signer_Asn1_Signed
{
    /**
     * @var array
     */
    private $_verifyCache = [];

    /**
     * A callable that can be used to implement individual verification logic.
     *
     * @var null|callable
     */
    static public $verifyCallback;

    /**
     * Flag defining whether usage of phpseclib or default OpenSSL methods for RSA_PSS validation.
     *
     * @var bool
     */
    static public $usePhpseclibForRsaPss = true;

    /**
     * Get the signature algorithm and parameter.
     *
     * @return array The first value holds the OID of the algorithm. The second value is the ASN.1 structure of the
     *               parameters.
     */
    abstract public function getSignatureAlgorithm();

    /**
     * Get the signature value.
     *
     * @param bool $hex
     * @return string
     */
    abstract public function getSignatureValue($hex = true);

    /**
     * Get the signed data.
     *
     * @return SetaPDF_Core_Reader_FilePath|string
     */
    abstract public function getSignedData();

    /**
     * Verify the signed object.
     *
     * @param SetaPDF_Signer_X509_Certificate $signerCertificate
     * @return bool
     * @throws SetaPDF_Signer_Asn1_Exception
     * @throws SetaPDF_Signer_Exception
     */
    public function verify(Certificate $signerCertificate)
    {
        $signedData = $this->getSignedData();
        if ($signedData === false) {
            return false;
        }

        $signingCertificateDigest = $signerCertificate->getDigest('md5', true);

        if (isset($this->_verifyCache[$signingCertificateDigest])) {
            return $this->_verifyCache[$signingCertificateDigest];
        }

        if (is_callable(self::$verifyCallback)) {
            $result = call_user_func(self::$verifyCallback, $this);
            if (is_bool($result)) {
                return $this->_verifyCache[$signingCertificateDigest] = $result;
            }
        }

        $signatureAlgorithm = $this->getSignatureAlgorithm();
        $algorithm = false;

        if ($signatureAlgorithm[0] === '1.2.840.113549.1.1.10') {
            /* RSASSA-PSS-params  ::=  SEQUENCE  {
             *    hashAlgorithm      [0] HashAlgorithm DEFAULT
             *                             sha1Identifier,
             *    maskGenAlgorithm   [1] MaskGenAlgorithm DEFAULT
             *                             mgf1SHA1Identifier,
             *    saltLength         [2] INTEGER DEFAULT 20,
             *    trailerField       [3] INTEGER DEFAULT 1  }
             */
            $parameters = [
                0 => SetaPDF_Signer_Digest::SHA_1,
                1 => SetaPDF_Signer_Digest::SHA_1,
                2 => null, // will be resolved automatically by phpseclib or extracted
                3 => 1
            ];
            /** @var SetaPDF_Signer_Asn1_Element $parameter */
            /** @var SetaPDF_Signer_Asn1_Element[] $signatureAlgorithm */
            foreach ($signatureAlgorithm[1]->getChildren() as $parameter) {
                $key = ord(
                    $parameter->getIdent() ^ (
                        SetaPDF_Signer_Asn1_Element::TAG_CLASS_CONTEXT_SPECIFIC |
                        SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED
                    )
                );

                switch ($key) {
                    case 0:
                        $algorithmOid = SetaPDF_Signer_Asn1_Oid::decode($parameter->getChild(0)->getChild(0)->getValue());
                        $parameters[0] = SetaPDF_Signer_Digest::getByOid($algorithmOid);
                        break;
                    case 1:
                        $maskGen = $parameter->getChild(0);
                        $maskGenOid = SetaPDF_Signer_Asn1_Oid::decode($maskGen->getChild(0)->getValue());
                        if ($maskGenOid !== '1.2.840.113549.1.1.8') {
                            throw new SetaPDF_Signer_Asn1_Exception(
                                sprintf('Unsupported mask generation function (%s).', $maskGenOid)
                            );
                        }

                        $algorithmOid = SetaPDF_Signer_Asn1_Oid::decode($maskGen->getChild(1)->getChild(0)->getValue());
                        $parameters[1] = SetaPDF_Signer_Digest::getByOid($algorithmOid);
                        break;
                    case 2:
                        $parameters[2] = ord($parameter->getChild(0)->getValue());
                        break;
                }
            }

            if (self::$usePhpseclibForRsaPss && class_exists(phpseclib\Crypt\RSA::class)) {
                $rsa = new phpseclib\Crypt\RSA();
                $rsa->loadKey($signerCertificate->getSubjectPublicKeyInfoRaw());
                $rsa->setHash($parameters[0]);
                $rsa->setMGFHash($parameters[1]);
                if ($parameters[2] !== null) {
                    $rsa->setSaltLength($parameters[2]);
                }

                if ($signedData instanceof SetaPDF_Core_Reader_FilePath) {
                    return $this->_verifyCache[$signingCertificateDigest] = $rsa->verify(
                        file_get_contents($signedData->getPath()),
                        $this->getSignatureValue(false)
                    );
                }

                return $this->_verifyCache[$signingCertificateDigest] = $rsa->verify(
                    $signedData,
                    $this->getSignatureValue(false)
                );
            }

            $algorithm = $parameters[0];
        }

        if ($algorithm === false) {
            $algorithm = array_search(
                $signatureAlgorithm[0],
                SetaPDF_Signer_Digest::$encryptionOids[SetaPDF_Signer_Digest::DSA_ALGORITHM],
                true
            );
        }

        if ($algorithm === false) {
            $algorithm = array_search(
                $signatureAlgorithm[0],
                SetaPDF_Signer_Digest::$encryptionOids[SetaPDF_Signer_Digest::ECDSA_ALGORITHM],
                true
            );
        }

        if ($algorithm !== false) {
            if ($signedData instanceof SetaPDF_Core_Reader_FilePath) {
                $signedData = file_get_contents($signedData);
            }

            $result = \openssl_verify(
                $signedData,
                $this->getSignatureValue(false),
                $signerCertificate->get(Format::PEM),
                $algorithm
            );

            return $this->_verifyCache[$signingCertificateDigest] = ($result === 1);
        }

        $algorithm = array_search(
            $signatureAlgorithm[0],
            SetaPDF_Signer_Digest::$encryptionOids[SetaPDF_Signer_Digest::RSA_ALGORITHM],
            true
        );

        // These are all "rsa" signature algorithms
        if ($algorithm === false && $signatureAlgorithm[0] !== '1.2.840.113549.1.1.1') {
            throw new SetaPDF_Signer_Asn1_Exception(sprintf(
                'Unsupported signature algorithm "%s".',
                $signatureAlgorithm[0]
            ));
        }

        if (openssl_public_decrypt($this->getSignatureValue(false), $result, $signerCertificate->get(Format::PEM))) {
            $decryptedResult = SetaPDF_Signer_Asn1_Element::parse($result);

            if ($decryptedResult->getChildCount() < 2) {
                return false;
            }

            $decryptedDigestAlgorithm = $decryptedResult->getChild(0);
            if (!$decryptedDigestAlgorithm || $decryptedDigestAlgorithm->getIdent() !==
                (SetaPDF_Signer_Asn1_Element::IS_CONSTRUCTED | SetaPDF_Signer_Asn1_Element::SEQUENCE) ||
                $decryptedDigestAlgorithm->getChildCount() < 1
            ) {
                return false;
            }

            $decryptedDigestAlgorithm = $decryptedDigestAlgorithm->getChild(0)->getValue();

            $hashOid = SetaPDF_Signer_Asn1_Oid::decode($decryptedDigestAlgorithm);

            $digestAlgorithm = SetaPDF_Signer_Digest::getByOid($hashOid);
            if (!$digestAlgorithm) {
                throw new SetaPDF_Signer_Asn1_Exception(sprintf('Unsupported digest algorithm "%s".', $hashOid));
            }

            $decryptedDigest = $decryptedResult->getChild(1)->getValue();
            if ($signedData instanceof SetaPDF_Core_Reader_FilePath) {
                $digest = hash_file($digestAlgorithm, $signedData->getPath(), true);
            } else {
                $digest = hash($digestAlgorithm, $signedData, true);
            }

            return $this->_verifyCache[$signingCertificateDigest] = ($decryptedDigest === $digest);
        }

        return $this->_verifyCache[$signingCertificateDigest] = false;
    }

}