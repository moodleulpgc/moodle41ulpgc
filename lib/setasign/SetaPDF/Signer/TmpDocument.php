<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: TmpDocument.php 1543 2020-09-15 14:57:30Z jan.slabon $
 */

/**
 * Class for handling an intermediate version of the document that should be signed.
 *
 * This class is used for an asyncron signature flow.
 * Otherwise it is used internally.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_TmpDocument
{
    /**
     * The file writer for the temporary version
     *
     * @var SetaPDF_Core_Writer_FileInterface
     */
    protected $_writer;

    /**
     * The writer instance for the 2nd temporary file, that represents the data to hash
     *
     * @var SetaPDF_Core_Writer_TempFile
     */
    protected $_hashFileWriter;

    /**
     * Document identification related data
     *
     * @var array
     */
    protected $_documentIdentification = [
        0 => null,
        1 => null
    ];

    /**
     * Hash of the last state of the intermediate document version.
     *
     * @var string
     */
    protected $_identficationHash;

    /**
     * The byte range value
     *
     * @var array
     */
    protected $_byteRange;

    /**
     * The constructor
     *
     * The $writer parameter is optional and can be used in an asyncron workflow to have control over the
     * temporary version of the document.
     *
     * @param SetaPDF_Core_Writer_FileInterface $writer
     */
    public function __construct(SetaPDF_Core_Writer_FileInterface $writer = null)
    {
        if (null === $writer) {
            $writer = new SetaPDF_Core_Writer_TempFile();
        }

        $this->setWriter($writer);
    }

    /**
     * Get the writer instance.
     *
     * @return SetaPDF_Core_Writer_FileInterface
     */
    public function getWriter()
    {
        return $this->_writer;
    }

    /**
     * Set the writer instance.
     *
     * @param SetaPDF_Core_Writer_FileInterface $writer
     */
    public function setWriter(SetaPDF_Core_Writer_FileInterface $writer)
    {
        $this->_writer = $writer;
    }

    /**
     * Set the document identification related data.
     *
     * @param SetaPDF_Core_Document $document
     */
    public function setDocumentIdentification(SetaPDF_Core_Document $document)
    {
        $this->_documentIdentification = [
            $document->getInstanceIdent(),
            $document->getFileIdentifier(false, false)
        ];

        if ($this->_documentIdentification[1] === null) {
            $this->_documentIdentification[2] = $this->_getHashByDocument($document);
        }
    }

    /**
     * Updates the identification hash of the intermediate document version.
     */
    public function updateIdentificationHash()
    {
        $this->_identficationHash = hash_file('sha256', $this->_writer->getPath());
    }

    /**
     * Get the SHA-256 hash by a document instance.
     *
     * @param SetaPDF_Core_Document $document
     * @return string
     * @throws SetaPDF_Signer_Exception
     */
    private function _getHashByDocument(SetaPDF_Core_Document $document)
    {
        $reader = $document->getParser()->getReader();
        if ($reader instanceof SetaPDF_Core_Reader_Stream) {
            $data = stream_get_meta_data($reader->getStream());
            return hash_file('sha256', $data['uri']);
        }

        if ($reader instanceof SetaPDF_Core_Reader_String) {
            return hash('sha256', $reader->getString());
        }

        throw new SetaPDF_Signer_Exception(
            'Unsupported reader instance. Cannot get a hash value of the document content'
        );
    }

    /**
     * Checks whether the document instance matches this temporary version.
     *
     * The checks are done by the instance identification of the class instance frist.
     * If this does not match, because of an asyncron workflow the document identifications are compared.
     *
     * @param SetaPDF_Core_Document $document
     * @return bool
     */
    public function matchesDocument(SetaPDF_Core_Document $document)
    {
        return ($document->getInstanceIdent() === $this->_documentIdentification[0]
            || $document->getFileIdentifier(false, false) === $this->_documentIdentification[1]
            || (
                isset($this->_documentIdentification[2]) &&
                $this->_documentIdentification[2] === $this->_getHashByDocument($document)
            )) && hash_file('sha256', $this->_writer->getPath()) === $this->_identficationHash;
    }

    /**
     * Get the signature content length.
     *
     * @return integer
     */
    public function getSignatureContentLength()
    {
        $byteRange = $this->getByteRange();

        return $byteRange[2] - $byteRange[1] - 2;
    }

    /**
     * Updates the last byte offset in the /ByteRange entry in the to be signed document.
     *
     * @param int $offset
     * @param int $position
     * @param int $length
     */
    public function updateLastByteOffset($offset, $position, $length)
    {
        $fh = fopen($this->getWriter()->getPath(), 'rb+');
        fseek($fh, $offset);
        fwrite($fh, str_pad($position, strlen($length), ' ', STR_PAD_RIGHT));
        fclose($fh);
    }

    /**
     * Set the final byte range.
     *
     * @param array $byteRange
     */
    public function setByteRange(array $byteRange)
    {
        $this->_byteRange = $byteRange;
    }

    /**
     * Get the byte range.
     *
     * @return array
     */
    public function getByteRange()
    {
        return $this->_byteRange;
    }

    /**
     * Create and get the file path to a temporary file that represents the bytes that should be be used for hashing.
     *
     * @return SetaPDF_Core_Reader_FilePath
     */
    public function getHashFile()
    {
        $this->_hashFileWriter = new SetaPDF_Core_Writer_TempFile();
        $this->_hashFileWriter->start();

        $byteRange = $this->getByteRange();

        $fh = fopen($this->getWriter()->getPath(), 'rb+');

        foreach ([
             $byteRange[0] => $byteRange[1],
             $byteRange[2] => $byteRange[3]
                 ] AS $start => $toRead) {
            fseek($fh, $start);
            while ($toRead > 0) {
                $data = fread($fh, min(16192, $toRead));
                $this->_hashFileWriter->write($data);
                $toRead -= strlen($data);
            }
        }

        $this->_hashFileWriter->finish();
        fclose($fh);

        return new SetaPDF_Core_Reader_FilePath($this->_hashFileWriter->getPath());
    }

    /**
     * Writes the signature into the reserved gap.
     *
     * The signature string needs to have the same length as the gap.
     *
     * @param string $signature
     */
    public function writeSignature($signature)
    {
        if ((strlen($signature) * 2) != $this->getSignatureContentLength()) {
            throw new InvalidArgumentException(sprintf(
                'The signature byte-length (%s) does not match the reserved space (%s).',
                strlen($signature) * 2,
                $this->getSignatureContentLength()
            ));
        }

        $byteRange = $this->getByteRange();

        $fh = fopen($this->getWriter()->getPath(), 'rb+');
        fseek($fh, $byteRange[0] + $byteRange[1]);

        $stringWriter = new SetaPDF_Core_Writer();
        SetaPDF_Core_Type_HexString::writePdfString($stringWriter, $signature);
        fwrite($fh, $stringWriter);
        fclose($fh);
    }

    /**
     * Saves the temporary document to a writer instance.
     *
     * @param SetaPDF_Core_Writer_WriterInterface $writer
     */
    public function save(SetaPDF_Core_Writer_WriterInterface $writer)
    {
        $writer->start();
        $reader = new SetaPDF_Core_Reader_File($this->getWriter()->getPath());
        $reader->copyTo($writer);
        $writer->finish();
        $reader->cleanUp();
    }

    /**
     * Unlinks the temporary file.
     */
    public function destroy()
    {
        unlink($this->getWriter()->getPath());
    }
}
