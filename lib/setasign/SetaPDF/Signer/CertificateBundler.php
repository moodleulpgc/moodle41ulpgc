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
use SetaPDF_Signer_X509_CollectionInterface as CollectionInterface;
use SetaPDF_Signer_X509_Format as Format;

/**
 * Class for creation of certificate bundles.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_CertificateBundler
{
    /**
     * The output path.
     *
     * @var string
     */
    protected $outputPath;

    /**
     * Bundle paths.
     *
     * @var array
     */
    protected $bundlePaths = [];

    /**
     * All added bundles.
     *
     * @var array
     */
    protected $bundles = [];

    /**
     * The constructor.
     *
     * @param string $outputPath
     */
    public function __construct($outputPath)
    {
        $this->outputPath = (string)$outputPath;
    }

    /**
     * Get the output path.
     *
     * @return string
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * Add a bundle path.
     *
     * @param string $path
     */
    public function addBundlePath($path)
    {
        $path = (string)$path;
        if (!is_readable($path)) {
            throw new InvalidArgumentException('Path to certificate bundle is not readable.');
        }

        $this->bundlePaths[] = $path;
    }

    /**
     * Add bundle paths.
     *
     * @param iterable $iterator
     */
    public function addBundlePaths($iterator)
    {
        foreach ($iterator as $path) {
            if ($path instanceof SplFileInfo) {
                $this->addBundlePath($path->getPathname());
            } else {
                $this->addBundlePath($path);
            }
        }
    }

    /**
     * Add a single bundle.
     *
     * @param $bundle
     */
    public function addBundle($bundle)
    {
        $this->bundles[] = (string)$bundle;
    }

    /**
     * Add a certificate.
     *
     * @param SetaPDF_Signer_X509_Certificate $certificate
     */
    public function addCertificate(Certificate $certificate)
    {
        $this->bundles[] = $certificate->get(Format::PEM);
    }

    /**
     * Add certificates.
     *
     * @param CollectionInterface|iterable $certificates
     */
    public function addCertificates($certificates)
    {
        if ($certificates instanceof CollectionInterface) {
            $certificates = $certificates->getAll();
        }
        foreach ($certificates as $certificate) {
            $this->addCertificate($certificate);
        }
    }

    /**
     * Save the bundle.
     *
     * The file will only be re-created if anything in the bundle was changed.
     *
     * @param bool $forceUpdate
     * @return bool
     */
    public function save($forceUpdate = false)
    {
        $hash = '';
        foreach ($this->bundlePaths as $bundle) {
            $hash = hash('sha256', hash_file('sha256', $bundle) . $hash);
        }

        foreach ($this->bundles as $bundle) {
            $hash = hash('sha256', hash('sha256', $bundle) . $hash);
        }

        if ($hash === '') {
            throw new BadMethodCallException('No certificate bundles defined.');
        }

        $currentHash = null;
        if ($forceUpdate === false && file_exists($this->outputPath)) {
            $fh = fopen($this->outputPath, 'rb');
            fseek($fh, 7);
            $currentHash = fread($fh, 64);
            fclose($fh);
        }

        if ($forceUpdate || $currentHash !== $hash) {
            $tmpName = tempnam(sys_get_temp_dir(), 'CERT-BUNDLE');
            $tmpFh = fopen($tmpName, 'wb');
            fwrite($tmpFh, 'sha256:' . $hash . "\n" . 'time:' . date('c') . "\n");
            foreach ($this->bundlePaths as $bundle) {
                $fh = fopen($bundle, 'rb');
                fwrite($tmpFh, "\n");
                stream_copy_to_stream($fh, $tmpFh);
                fclose($fh);
            }

            foreach ($this->bundles as $bundle) {
                fwrite($tmpFh, "\n" . $bundle);
            }

            fclose($tmpFh);
            rename($tmpName, $this->outputPath);
            return true;
        }

        return false;
    }
}