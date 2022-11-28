<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Link.php 1589 2020-11-16 10:10:41Z jan.slabon $
 */

/**
 * Class representing a Link annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.5
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Link
    extends SetaPDF_Core_Document_Page_Annotation
{
    use SetaPDF_Core_Document_Page_Annotation_QuadPointsTrait;

    /**
     * @var SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    protected $_borderStyle;

    /**
     * Creates an link annotation dictionary.
     *
     * If the $actionOrDestination parameter is a scalar value it will become an
     * {@link SetaPDF_Core_Document_Action_Uri Uri action}.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @param string|SetaPDF_Core_Document_Destination|SetaPDF_Core_Document_Action $actionOrDestination
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    static public function createAnnotationDictionary($rect, $actionOrDestination)
    {
        $dictionary = parent::_createAnnotationDictionary($rect, self::TYPE_LINK);

        if (is_scalar($actionOrDestination)) {
            $actionOrDestination = new SetaPDF_Core_Document_Action_Uri($actionOrDestination);
        }

        if ($actionOrDestination instanceof SetaPDF_Core_Document_Action) {
            $dictionary->offsetSet('A', $actionOrDestination->getActionDictionary());
        } else if ($actionOrDestination instanceof SetaPDF_Core_Document_Destination) {
            $dictionary->offsetSet('Dest', $actionOrDestination->getDestinationArray());
        } else {
            throw new InvalidArgumentException(
                '$actionOrDestination argument has to be type of SetaPDF_Core_Document_Action or SetaPDF_Core_Document_Destination'
            );
        }

        $dictionary->offsetSet('Border', new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Numeric(0),
            new SetaPDF_Core_Type_Numeric(0),
            new SetaPDF_Core_Type_Numeric(0)
        ]));
        
        return $dictionary;
    }
    
    /**
     * The constructor.
     *
     * A link annotation instance can be created by an existing dictionary, indirect object/reference or by passing
     * the same parameter as for {@link createAnnotationDictionary()}.
     *
     * @param bool|int|float|string|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_DataStructure_Rectangle|array $objectOrDictionary
     * @throws InvalidArgumentException
     */
    public function __construct($objectOrDictionary)
    {
        $dictionary = $objectOrDictionary instanceof SetaPDF_Core_Type_AbstractType
            ? $objectOrDictionary->ensure(true)
            : $objectOrDictionary;

        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            $args = func_get_args();
            $dictionary = $objectOrDictionary = call_user_func_array(
                ['self', 'createAnnotationDictionary'],
                $args
            );
            unset($args);
        }
    
        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Link')) {
            throw new InvalidArgumentException('The Subtype entry in a Link annotation shall be "Link".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Release memory/cycled references.
     */
    public function cleanUp()
    {
        parent::cleanUp();

        if (null !== $this->_borderStyle) {
            $this->_borderStyle->cleanUp();
            $this->_borderStyle = null;
        }
    }

    /**
     * Get the destination of the item.
     *
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_Document_Destination|false
     * @throws BadMethodCallException
     */
    public function getDestination(SetaPDF_Core_Document $document = null)
    {
        if (!$this->_annotationDictionary->offsetExists('Dest')) {
            return false;
        }
    
        $dest = $this->_annotationDictionary->getValue('Dest')->ensure();
        if ($dest instanceof SetaPDF_Core_Type_StringValue || $dest instanceof SetaPDF_Core_Type_Name) {
            if ($document === null) {
                throw new BadMethodCallException('To resolve a named destination the $document parameter has to be set.');
            }
    
            return SetaPDF_Core_Document_Destination::findByName($document, $dest->getValue());
        }
    
        return new SetaPDF_Core_Document_Destination($dest);
    }
    
    /**
     * Set the destination of the item.
     *
     * @param SetaPDF_Core_Document_Destination|SetaPDF_Core_Type_Array|SetaPDF_Core_Type_String $destination
     * @throws InvalidArgumentException
     */
    public function setDestination($destination)
    {
        if ($destination instanceof SetaPDF_Core_Document_Destination) {
            $destination = $destination->getDestinationArray();
        }
    
        if (!($destination instanceof SetaPDF_Core_Type_Array) &&
            !($destination instanceof SetaPDF_Core_Type_StringValue) &&
            !($destination instanceof SetaPDF_Core_Type_Name))
        {
            throw new InvalidArgumentException('Only valid destination values allowed (SetaPDF_Core_Type_Array, SetaPDF_Core_Type_StringValue, SetaPDF_Core_Type_Name or SetaPDF_Core_Document_Destination)');
        }
    
        $this->_annotationDictionary->offsetSet('Dest', $destination);
        $this->_annotationDictionary->offsetUnset('A');
    }
    
    /**
     * Get the action of the item.
     *
     * @return bool|SetaPDF_Core_Document_Action
     */
    public function getAction()
    {
        if (!$this->_annotationDictionary->offsetExists('A')) {
            return false;
        }
    
        return SetaPDF_Core_Document_Action::byObjectOrDictionary($this->_annotationDictionary->getValue('A'));
    }

    /**
     * Set the action of the annotation.
     *
     * The action could be an instance of {@link SetaPDF_Core_Document_Action} or a plain dictionary representing
     * the action.
     *
     * @throws InvalidArgumentException
     * @param SetaPDF_Core_Document_Action|SetaPDF_Core_Type_Dictionary $action
     */
    public function setAction($action)
    {
        if ($action instanceof SetaPDF_Core_Document_Action) {
            $action = $action->getActionDictionary();
        }
    
        if (!($action instanceof SetaPDF_Core_Type_Dictionary) || !$action->offsetExists('S'))
        {
            throw new InvalidArgumentException('Invalid $action parameter. SetaPDF_Core_Document_Action or SetaPDF_Core_Type_Dictionary with an S key needed.');
        }
    
        $this->_annotationDictionary->offsetSet('A', $action);
        $this->_annotationDictionary->offsetUnset('Dest');
    }

    /**
     * Get the border style object.
     *
     * @return null|SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    public function getBorderStyle()
    {
        if (null === $this->_borderStyle) {
            $this->_borderStyle = new SetaPDF_Core_Document_Page_Annotation_BorderStyle($this);
        }

        return $this->_borderStyle;
    }
}