<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Digest.php 1505 2020-07-21 13:54:06Z jan.slabon $
 */

/**
 * Class offering digest constants and helper methods
 *
 * @see SetaPDF_Signer_Signature_Module_OpenSslCliCms::setDigest()
 * @see SetaPDF_Signer_Timestamp_Module_AbstractModule::setDigest()
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Digest
{
    /**
     * Digest algorithm
     *
     * @var string
     */
    const SHA_1 = 'sha1';

    /**
     * Digest algorithm
     *
     * @var string
     */
    const SHA_256 = 'sha256';

    /**
     * Digest algorithm
     *
     * @var string
     */
    const SHA_384 = 'sha384';

    /**
     * Digest algorithm
     *
     * @var string
     */
    const SHA_512 = 'sha512';

    /**
     * Digest algorithm
     *
     * @var string
     */
    const MD5 = 'md5';

    /**
     * Digest algorithm
     *
     * @var string
     */
    const RMD_160 = 'ripemd160';

    /**
     * Algorithm constant
     *
     * @var string
     */
    const RSA_ALGORITHM = 'rsa';

    /**
     * Algorithm constant
     *
     * @var string
     */
    const RSA_PSS_ALGORITHM = 'rsa-dss';

    /**
     * Algorithm constant
     *
     * @var string
     */
    const DSA_ALGORITHM = 'dsa';

    /**
     * Algorithm constant
     *
     * @var string
     */
    const ECDSA_ALGORITHM = 'ecdsa';

    /**
     * OIDs of specific digest algorithms
     *
     * @var array
     */
    static public $oids = [
        self::SHA_1 => '1.3.14.3.2.26',             // http://www.alvestrand.no/objectid/1.3.14.3.2.26.html
        self::SHA_256 => '2.16.840.1.101.3.4.2.1',  // http://www.alvestrand.no/objectid/2.16.840.1.101.3.4.2.1.html
        self::SHA_384 => '2.16.840.1.101.3.4.2.2',  // http://www.alvestrand.no/objectid/2.16.840.1.101.3.4.2.2.html
        self::SHA_512 => '2.16.840.1.101.3.4.2.3',  // http://www.alvestrand.no/objectid/2.16.840.1.101.3.4.2.3.html
        self::MD5 => '1.2.840.113549.2.5',          // http://www.alvestrand.no/objectid/1.2.840.113549.2.5.html
        self::RMD_160 => '1.3.36.3.2.1'             // http://www.alvestrand.no/objectid/1.3.36.3.2.1.html
    ];

    /**
     * Algorithm OIDs
     *
     * @var array
     */
    static public $algorithmOids = [
        '1.2.840.113549.1.1.1' => self::RSA_ALGORITHM,
        '1.2.840.113549.1.1.10' => self::RSA_PSS_ALGORITHM,
        '1.2.840.10040.4.1' => self::DSA_ALGORITHM,
        '1.2.840.10045.2.1' => self::ECDSA_ALGORITHM
    ];

    /**
     * OIDs for signature algorithms grouped by base algorithms.
     *
     * @var array
     */
    static public $encryptionOids = [
        self::RSA_ALGORITHM => [
            self::SHA_1 => '1.2.840.113549.1.1.5',     // http://www.alvestrand.no/objectid/1.2.840.113549.1.1.5.html
            self::SHA_256 => '1.2.840.113549.1.1.11',  // http://www.alvestrand.no/objectid/1.2.840.113549.1.1.11.html
            self::SHA_384 => '1.2.840.113549.1.1.12',  // http://oid-info.com/get/1.2.840.113549.1.1.12
            self::SHA_512 => '1.2.840.113549.1.1.13',  // http://oid-info.com/get/1.2.840.113549.1.1.13
            self::MD5 => '1.2.840.113549.1.1.4',       // http://www.alvestrand.no/objectid/1.2.840.113549.1.1.4.html
            self::RMD_160 => '1.3.36.3.3.1.2'          // http://www.alvestrand.no/objectid/1.3.36.3.3.1.2.html
        ],
        self::DSA_ALGORITHM => [
            self::SHA_1 => '1.2.840.10040.4.3',
            self::SHA_256 => '2.16.840.1.101.3.4.3.2',
        ],
        // https://tools.ietf.org/html/rfc5480#page-17
        self::ECDSA_ALGORITHM => [
            self::SHA_1   => '1.2.840.10045.4.1',
            self::SHA_256 => '1.2.840.10045.4.3.2',
            self::SHA_384 => '1.2.840.10045.4.3.3',
            self::SHA_512 => '1.2.840.10045.4.3.4'
        ]
    ];

    /**
     * Get the OID for a digest algorithm.
     *
     * @param string $digest Digest algorithm, use the constants in SetaPDF_Signer_Digest
     * @param null|string A algorithm constant
     * @return string
     * @throws InvalidArgumentException
     */
    static public function getOid($digest, $encryptionOid = null)
    {
        if ($encryptionOid === null && isset(self::$oids[$digest])) {
            return self::$oids[$digest];
        }

        if ($encryptionOid) {
            if (!isset(self::$algorithmOids[$encryptionOid])) {
                throw new InvalidArgumentException('Unsupported algorithm oid (' . $encryptionOid . ').');
            }

            $algorithm = self::$algorithmOids[$encryptionOid];

            if (isset(self::$encryptionOids[$algorithm][$digest])) {
                return self::$encryptionOids[$algorithm][$digest];
            }

            throw new InvalidArgumentException(
                'Unsupported digest (' . $algorithm . ' with ' . $digest . ').'
            );
        }

        throw new InvalidArgumentException('Digest "' . $digest . '" not supported.');
    }

    /**
     * Get an OID by a digest.
     *
     * @param string $oid
     * @return mixed
     */
    static public function getByOid($oid)
    {
        return array_search($oid, self::$oids, true);
    }

    /**
     * Check if a digest algorithm is valid/supported.
     *
     * @param $digest
     * @return bool
     */
    static public function isValidDigest($digest)
    {
        return isset(self::$oids[$digest]);
    }

    /**
     * This class should not be initiated.
     */
    private function __construct()
    {}
}