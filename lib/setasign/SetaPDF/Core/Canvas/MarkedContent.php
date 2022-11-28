<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: MarkedContent.php 1475 2020-06-03 10:40:29Z jan.slabon $
 */

/**
 * A canvas helper class for marked content operators
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Canvas
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Canvas_MarkedContent extends SetaPDF_Core_Canvas_Operators
{
    /**
     * Begin a marked content sequence.
     * 
     * @param string $tag
     * @param SetaPDF_Core_Resource|SetaPDF_Core_Type_Dictionary $properties
     * @return SetaPDF_Core_Canvas_MarkedContent
     */
    public function begin($tag, $properties = null)
    {
        SetaPDF_Core_Type_Name::writePdfString($this->_canvas, $tag);
        if ($properties === null) {
            $this->_canvas->write(" BMC\n");

        } else {
            if ($properties instanceof SetaPDF_Core_Resource) {
                $name = $this->_canvas->addResource($properties);
                SetaPDF_Core_Type_Name::writePdfString($this->_canvas, $name);
            } elseif ($properties instanceof SetaPDF_Core_Type_Dictionary) {
                try {
                    $string = $properties->toPdfString();
                    $this->_canvas->write($string);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException(
                        'Property list cannot hold indirect references. You need to create a ' .
                        'SetaPDF_Core_Resource instance in that case.',
                        null,
                        $e
                    );
                }
            } else {
                throw new InvalidArgumentException(
                    'Argument needs to be an instance of SetaPDF_Core_Resource or SetaPDF_Core_Type_Dictionary.'
                );
            }

            $this->_canvas->write(" BDC\n");
        }
        
        return $this;
    }
    
    /**
     * End a marked content stream.
     * 
     * @return SetaPDF_Core_Canvas_MarkedContent
     */
    public function end()
    {
        $this->_canvas->write("\nEMC\n");
        
        return $this;
    }

    // TODO: MP and DP
}