<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Screen.php 1443 2020-03-17 15:30:36Z jan.slabon $
 */

/**
 * Class representing a screen annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.18 Screen Annotations
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Screen
    extends SetaPDF_Core_Document_Page_Annotation
{
    /**
     * Creates a screen annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    static public function createAnnotationDictionary($rect)
    {
        return SetaPDF_Core_Document_Page_Annotation::_createAnnotationDictionary(
            $rect,
            SetaPDF_Core_Document_Page_Annotation_Link::TYPE_SCREEN
        );
    }

    /**
     * The constructor.
     *
     * A screen annotation instance can be created by an existing dictionary, indirect object/reference or by passing
     * the same parameter as for {@link createAnnotationDictionary()}.
     *
     * @param array|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface $objectOrDictionary
     * @throws InvalidArgumentException
     */
    public function __construct($objectOrDictionary)
    {
        $dictionary = $objectOrDictionary instanceof SetaPDF_Core_Type_AbstractType
            ? $objectOrDictionary->ensure(true)
            : $objectOrDictionary;

        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            $args = func_get_args();
            $objectOrDictionary = $dictionary = call_user_func_array(
                ['self', 'createAnnotationDictionary'],
                $args
            );
            unset($args);
        }

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Screen')) {
            throw new InvalidArgumentException('The Subtype entry in a screen annotation shall be "Screen".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Get the title of the screen annotation.
     *
     * @param string $encoding
     * @return string|null
     */
    public function getTitle($encoding = 'UTF-8')
    {
        if (!$this->_annotationDictionary->offsetExists('T')) {
            return null;
        }

        return SetaPDF_Core_Encoding::convertPdfString(
            $this->_annotationDictionary->getValue('T')->getValue(),
            $encoding
        );
    }

    /**
     * Set the title of the screen annotation.
     *
     * @param string|null $title
     * @param string $encoding
     */
    public function setTitle($title, $encoding = 'UTF-8')
    {
        if ($title === null) {
            $this->_annotationDictionary->offsetUnset('T');
            return;
        }

        $title = SetaPDF_Core_Encoding::toPdfString($title, $encoding);

        if (!$this->_annotationDictionary->offsetExists('T')) {
            $this->_annotationDictionary->offsetSet('T', new SetaPDF_Core_Type_String($title));
            return;
        }

        $this->_annotationDictionary->getValue('T')->setValue($title);
    }

    /**
     * Get the action of the annotation.
     *
     * If no action is defined false will be returned.
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

        if (!($action instanceof SetaPDF_Core_Type_Dictionary) || !$action->offsetExists('S')) {
            throw new InvalidArgumentException('Invalid $action parameter. SetaPDF_Core_Document_Action or SetaPDF_Core_Type_Dictionary with an S key needed.');
        }

        $this->_annotationDictionary->offsetSet('A', $action);
    }

    /**
     * Get the appearance characteristics object.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Document_Page_Annotation_AppearanceCharacteristics
     */
    public function getAppearanceCharacteristics($create = false)
    {
        $mk = $this->_annotationDictionary->getValue('MK');
        if ($mk === null) {
            if ($create == false) {
                return null;
            }

            $mk = new SetaPDF_Core_Type_Dictionary();
            $this->_annotationDictionary->offsetSet('MK', $mk);
        }

        return new SetaPDF_Core_Document_Page_Annotation_AppearanceCharacteristics($mk);
    }
}
