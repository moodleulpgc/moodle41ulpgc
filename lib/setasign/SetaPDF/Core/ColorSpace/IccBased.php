<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: IccBased.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * ICCBased Color Space
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage ColorSpace
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_ColorSpace_IccBased
    extends SetaPDF_Core_ColorSpace
    implements SetaPDF_Core_Resource
{
    /**
     * Creates an instance of this color space.
     *
     * @param SetaPDF_Core_IccProfile_Stream $iccStream
     * @return SetaPDF_Core_ColorSpace_IccBased
     */
    static public function create(SetaPDF_Core_IccProfile_Stream $iccStream)
    {
        return new self(new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Name('ICCBased'),
            $iccStream->getIndirectObject()
        ]));
    }

    /**
     * Release profile stream instances by a document instance.
     *
     * @param SetaPDF_Core_Document $document
     */
    static public function freeCache(SetaPDF_Core_Document $document)
    {
        $document->clearCache(SetaPDF_Core_Document::CACHE_ICC_PROFILE);
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_AbstractType $definition
     * @throws InvalidArgumentException
     */
    public function __construct(SetaPDF_Core_Type_AbstractType $definition)
    {
        parent::__construct($definition);

        if (!$this->_value instanceof SetaPDF_Core_Type_Array) {
            throw  new InvalidArgumentException('IccBased color space needs to be defined by an array.');
        }

        if ($this->getFamily() !== 'ICCBased') {
            throw new InvalidArgumentException('ICCBased color space has to be named "ICCBased".');
        }

        if (!$this->getPdfValue()->offsetGet(1)->ensure() instanceof SetaPDF_Core_Type_Stream) {
            throw new InvalidArgumentException("ICCBased color space needs a ICC profile (stream object) in it's definition.");
        }
    }

    /**
     * Get an instance of the ICC Profile stream.
     *
     * @return SetaPDF_Core_IccProfile_Stream
     */
    public function getIccProfileStream()
    {
        /** @var SetaPDF_Core_Type_IndirectObjectInterface $indirectObject */
        $indirectObject = $this->getPdfValue()->offsetGet(1);

        $ident = $indirectObject->getObjectIdent();
        $document = $indirectObject->getOwnerPdfDocument();
        if ($document->hasCache(SetaPDF_Core_Document::CACHE_ICC_PROFILE, $ident)) {
            return $document->getCache(SetaPDF_Core_Document::CACHE_ICC_PROFILE, $ident);
        }

        $stream = new SetaPDF_Core_IccProfile_Stream($indirectObject);
        $document->addCache(SetaPDF_Core_Document::CACHE_ICC_PROFILE, $ident, $stream);

        return $stream;
    }

    /**
     * Gets an indirect object for this color space dictionary.
     *
     * @see SetaPDF_Core_Resource::getIndirectObject()
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws InvalidArgumentException
     */
    public function getIndirectObject(SetaPDF_Core_Document $document = null)
    {
        if (null === $this->_indirectObject) {
            if (null === $document) {
                throw new InvalidArgumentException('To initialize a new object $document parameter is not optional!');
            }

            $this->_indirectObject = $document->createNewObject($this->getPdfValue());
        }

        return $this->_indirectObject;
    }

    /**
     * Get the resource type of an implementation.
     *
     * @return string
     */
    public function getResourceType()
    {
        return SetaPDF_Core_Resource::TYPE_COLOR_SPACE;
    }

    /**
     * Get the alternate color space.
     *
     * @return null|SetaPDF_Core_ColorSpace|SetaPDF_Core_ColorSpace_DeviceCmyk|SetaPDF_Core_ColorSpace_DeviceGray|SetaPDF_Core_ColorSpace_DeviceRgb|SetaPDF_Core_ColorSpace_IccBased|SetaPDF_Core_ColorSpace_Separation|string
     */
    public function getAlternateColorSpace()
    {
        $stream = $this->getPdfValue()->offsetGet(1)->ensure();
        $dict = $stream->getValue();

        if (!$dict->offsetExists('Alternate')) {
            return null;
        }

        $colorSpace =  $dict->offsetGet('Alternate')->ensure()->getValue();

        return SetaPDF_Core_ColorSpace::createByDefinition($colorSpace);
    }

    /**
     * Get the color components of this color space.
     *
     * @return integer
     */
    public function getColorComponents()
    {
        $stream = $this->getPdfValue()->offsetGet(1)->ensure();
        $dict = $stream->getValue();

        return $dict->offsetGet('N')->ensure()->getValue();
    }

    /**
     * Get the default decode array of this color space.
     *
     * @return array
     */
    public function getDefaultDecodeArray()
    {
        $stream = $this->getPdfValue()->offsetGet(1)->ensure();
        $dict = $stream->getValue();

        if ($dict->offsetExists('Range')) {
            return $dict->offsetGet('Range')->ensure()->toPhp();
        }

        $result = [];
        for ($i = 0; $i < $this->getColorComponents(); $i++) {
            $result[] = 0.;
            $result[] = 1.;
        }

        return $result;
    }
}