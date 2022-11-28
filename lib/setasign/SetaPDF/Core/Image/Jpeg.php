<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Jpeg.php 1582 2020-10-21 14:44:44Z jan.slabon $
 */

/**
 * Class representing an JPEG image
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Image
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Image_Jpeg extends SetaPDF_Core_Image
{
    /**
     * Handled JPEG marker
     *
     * @var string
     */
    const MARKER_SOS = "\xDA";

    /**
     * Handled JPEG marker
     *
     * @var string
     */
    const MARKER_APP0 = "\xE0"; // JFIF tag

    /**
     * Handled JPEG marker
     *
     * @var string
     */
    const MARKER_SOF0 = "\xC0"; // baseline

    /**
     * Handled JPEG marker
     *
     * @var string
     */
    const MARKER_SOF1 = "\xC1"; // extended sequential

    /**
     * Handled JPEG marker
     *
     * @var string
     */
    const MARKER_SOF2 = "\xC2"; // progressive

    /**
     * Handled JPEG marker
     *
     * @var string
     */
    const MARKER_SOI = "\xD8"; // Start of image

    /**
     * Handled JPEG marker
     *
     * @var string
     */
    const MARKER_EOI = "\xD9"; // End of image
    
    /**
     * Process the image data.
     * 
     * @see SetaPDF_Core_Image::_process()
     * @throws SetaPDF_Core_Image_Exception
     */
    protected function _process()
    {
        $this->_binaryReader->reset(2, 4);

        $app0found = false;
        while (1) {
            $marker = $this->_binaryReader->readByte();

            if ($marker === "\xFF") {
                $type = $this->_binaryReader->readByte();

                switch ($type) {
                    // If \xFF is followed by \x00 we are in compressed data and need to walk forward
                    case "\x00":
                        continue 2;

                    // We only care about the first APP0 marker
                    case self::MARKER_APP0: // Used by the JFIF format
                        $length = $this->_binaryReader->readUInt16();
                        if ($app0found === false) {
                            $identifier = $this->_binaryReader->readBytes(5);
                            if ($identifier !== "JFIF\x00") {
                                break;
                            }

                            $majorVersion = $this->_binaryReader->readInt8();
                            $this->_binaryReader->skip(1); // skip minor version
                            // $minorVersion = $this->_binaryReader->readInt8();
                            if ($majorVersion !== 1) {
                                throw new SetaPDF_Core_Image_Exception(sprintf('Invalid JPEG major version (%s)', $majorVersion));
                            }

                            $units = $this->_binaryReader->readInt8();
                            $xDensity = $this->_binaryReader->readUInt16();
                            $yDensity = $this->_binaryReader->readUInt16();

                            // dots/inch
                            if ($units === 1) {
                                $this->_dpiX = $xDensity;
                                $this->_dpiY = $yDensity;
                                // dots/cm
                            } elseif ($units === 2) {
                                $this->_dpiX = (int)($xDensity * 2.54);
                                $this->_dpiY = (int)($yDensity * 2.54);
                            }

                            $app0found = true;
                        }

                        $this->_binaryReader->skip($length - 14);
                        continue 2;
                        
                    // Adobe
                    case "\xEE": // APP 14
                        $length = $this->_binaryReader->readUInt16();
                        $content = $this->_binaryReader->readBytes($length - 2);
                        $colorTransform = ord($content[11]);
                        $this->_inverted = $colorTransform  > 0;
                        continue 2;
                    
                    // APP1 - APP15 (E1 - EF)
                    case "\xE1": 
                    case "\xE2":
                    case "\xE3":
                    case "\xE4":
                    case "\xE5":
                    case "\xE6":
                    case "\xE7":
                    case "\xE8":
                    case "\xE9":
                    case "\xEA":
                    case "\xEB":
                    case "\xEC":
                    case "\xED": // APP 13
                    case "\xEF":
                        $length = $this->_binaryReader->readUInt16();
                        $this->_binaryReader->skip($length - 2);
                        continue 2;
                    
                    case self::MARKER_SOF0:
                    case self::MARKER_SOF1:
                    case self::MARKER_SOF2:
                        $length = $this->_binaryReader->readUInt16();
                        $this->_bitsPerComponent = $this->_binaryReader->readUInt8(); // bits per component
                        $this->_height = $this->_binaryReader->readUInt16();
                        $this->_width = $this->_binaryReader->readUInt16();
                        $this->_colorSpace = $this->_binaryReader->readUInt8();
                        
                        $this->_binaryReader->skip($length - 8);
                        continue 2;
                        
                    case "\xDB": // DQT - Define quantization tables
                    case "\xC4": // DHT - Define Huffman table
                        $length = $this->_binaryReader->readUInt16();
                        $this->_binaryReader->skip($length - 2);
                        continue 2;

                    case self::MARKER_EOI:
                        break 2;

                    case self::MARKER_SOS:
                        $reader = $this->_binaryReader->getReader();
                        $start = $reader->getPos() + $reader->getOffset();
                        $bufferLen = 100000;
                        $reader->reset($start, $bufferLen);

                        while (($buffer = $reader->getBuffer()) !== '') {
                            $markerFound = strpos($buffer, "\xFF");
                            if ($markerFound === false) {
                                $start += ($bufferLen / 2);
                                $reader->reset($start, $bufferLen);
                                continue;
                            }

                            // if it is the last character, ignore
                            if ($markerFound === (strlen($buffer) - 1)) {
                                $start += ($bufferLen / 2);
                            } else {
                                if (strpos("\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\x00", $buffer[$markerFound + 1]) !== false) {
                                    $start += ($bufferLen / 2);
                                } else {
                                    // valid marker found:
                                    $this->_binaryReader->reset($start + $markerFound, 100);
                                    break;
                                }
                            }

                            $reader->reset($start, $bufferLen);
                        }
                        break;
                        
                    case "\xD0": //D0 - D7 = Restart marker
                    case "\xD1":
                    case "\xD2":
                    case "\xD3":
                    case "\xD4":
                    case "\xD5":
                    case "\xD6":
                    case "\xD7":
                        throw new SetaPDF_Core_Image_Exception(
                            'Restart marker (' . dechex(ord($type)) . ') shall only occur in compressed data.'
                        );

                    case "\xC3": // Start of frame, lossless
                    case "\xC5": // Start of frame, differential sequential
                    case "\xC6": // Start of frame, differential progressive
                    case "\xC7": // Start of frame, differential lossless
                    case "\xC9": // Start of frame, extended sequential, arithmetic coding
                    case "\xCA": // Start of frame, progressive, arithmetic coding
                        throw new SetaPDF_Core_Image_Exception('Unsupported JPEG marker: ' . dechex(ord($type)));
                }

            } elseif ($marker === false) {
                break;
            }
        }
    }
    
    /**
     * Converts the JPEG image to an external object.
     * 
     * @see SetaPDF_Core_Image::toXObject()
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_XObject_Image
     */
    public function toXObject(SetaPDF_Core_Document $document)
    {
        $colorSpace = 'DeviceRGB';
        switch ($this->getColorSpace()) {
            case 1:
                $colorSpace = 'DeviceGray';
                break;
            case 3:
                $colorSpace = 'DeviceRGB';
                break;
            case 4:
                $colorSpace = 'DeviceCMYK';
                break;
        }
        
        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('Type', new SetaPDF_Core_Type_Name('XObject', true));
        $dictionary->offsetSet('Subtype', new SetaPDF_Core_Type_Name('Image', true));
        $dictionary->offsetSet('Width', new SetaPDF_Core_Type_Numeric($this->getWidth()));
        $dictionary->offsetSet('Height', new SetaPDF_Core_Type_Numeric($this->getHeight()));
        $dictionary->offsetSet('ColorSpace', new SetaPDF_Core_Type_Name($colorSpace, true));
        if ($this->_inverted && 'DeviceCMYK' === $colorSpace) {
            $dictionary->offsetSet('Decode', new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric(1),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(1),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(1),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(1),
                new SetaPDF_Core_Type_Numeric(0)
            ]));
        }
        $dictionary->offsetSet('BitsPerComponent', new SetaPDF_Core_Type_Numeric($this->getBitsPerComponent()));
        // Length -> will be set automatically
        $dictionary->offsetSet('Filter', new SetaPDF_Core_Type_Name('DCTDecode', true));
        
        $streamContent = new SetaPDF_Core_Writer();
        $this->_binaryReader->getReader()->copyTo($streamContent);
        $stream = new SetaPDF_Core_Type_Stream($dictionary, (string)$streamContent);
        unset($streamContent);
        
        $object = $document->createNewObject($stream);
        
        return new SetaPDF_Core_XObject_Image($object);
    }
}