<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: SignatureField.php 1479 2020-06-11 09:14:35Z jan.slabon $
 */

/**
 * Helper class to create/handle a signature field
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_SignatureField extends SetaPDF_Core_Document_Page_Annotation_Widget
{
    /**
     * The default signature field name
     *
     * @var string
     */
    const DEFAULT_FIELD_NAME = 'Signature';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_LEFT_TOP = 'LT';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_LEFT_MIDDLE = 'LM';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_LEFT_BOTTOM = 'LB';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_CENTER_TOP = 'CT';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_CENTER_MIDDLE = 'CM';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_CENTER_BOTTOM = 'CB';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_RIGHT_TOP = 'RT';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_RIGHT_MIDDLE = 'RM';

    /**
     * Position constant
     *
     * @var string
     */
    const POSITION_RIGHT_BOTTOM = 'RB';

    /**
     * Lock value
     *
     * @see setLock()
     * @var string
     */
    const LOCK_DOCUMENT_NONE = false;

    /**
     * Lock value
     *
     * @see setLock()
     * @var string
     */
    const LOCK_DOCUMENT_ALL = 'All';

    /**
     * Lock value
     *
     * @see setLock()
     * @var string
     */
    const LOCK_DOCUMENT_INCLUDE = 'Include';

    /**
     * Lock value
     *
     * @see setLock()
     * @var string
     */
    const LOCK_DOCUMENT_EXCLUDE = 'Exclude';

    /**
     * Function to create a signature field annotation dictionary.
     *
     * @param string $fieldName The field name in UTF-8 encoding.
     * @param array|SetaPDF_Core_DataStructure_Rectangle $rect
     * @return SetaPDF_Core_Type_Dictionary
     */
    static public function createAnnotationDictionary($fieldName, $rect = [0, 0, 0, 0])
    {
        $dictionary = parent::_createAnnotationDictionary(
            $rect, SetaPDF_Core_Document_Page_Annotation_Link::TYPE_WIDGET
        );

        $dictionary['FT'] = new SetaPDF_Core_Type_Name('Sig', true);
        $dictionary['H'] = new SetaPDF_Core_Type_Name('P', true);
        $dictionary['F'] = new SetaPDF_Core_Type_Numeric(4);
        $dictionary['T'] = new SetaPDF_Core_Type_String(SetaPDF_Core_Encoding::toPdfString($fieldName));

        return $dictionary;
    }

    /**
     * Adds a new signature field to a documents page.
     *
     * It is possible to position the field relatively to the page boundary by passing a string value to the
     * $xOrPosition parameter.
     *
     * Additionally it is possible to define own x- and y-values through the desired parameter. Anyhow this will not
     * take care of page rotation.
     *
     * @param SetaPDF_Core_Document $document,
     * @param string $fieldName The field name in UTF-8 encoding
     * @param int $pageNumber The page number on which the signature field shall appear.
     * @param int|string $xOrPosition Integer with the x-position or {@link SetaPDF_Signer_SignatureField::POSITION_XXX}
     * @param int|array $yOrTranslate Integer with the y-position (if $xOrPosition is an integer) or an array with the keys 'x' and 'y'
     * @param int $width Width of the signature field
     * @param int $height Height of the signature field
     * @return SetaPDF_Signer_SignatureField
     * @throws InvalidArgumentException
     */
    static public function add(
        SetaPDF_Core_Document $document,
        $fieldName = self::DEFAULT_FIELD_NAME,
        $pageNumber = 1,
        $xOrPosition = 0,
        $yOrTranslate = 0,
        $width = 0,
        $height = 0
    ) {
        $page = $document->getCatalog()->getPages()->getPage($pageNumber);
        $box = $page->getBoundary();

        if (!is_numeric($xOrPosition)) {
            if ($yOrTranslate === 0) {
                $yOrTranslate = [];
            }

            if (!is_array($yOrTranslate)) {
                throw new InvalidArgumentException('Translate parameter has to be an array');
            }

            $yOrTranslate['x'] = isset($yOrTranslate['x']) ? $yOrTranslate['x'] : 0;
            $yOrTranslate['y'] = isset($yOrTranslate['y']) ? $yOrTranslate['y'] : 0;

            $pageRotation = $page->getRotation();
            $position = $xOrPosition;

            if ($pageRotation !== 0) {
                $fieldRotation = 0;
                for ($i = $pageRotation; $i > 0; $i -= 90) {
                    switch ($position) {
                        case self::POSITION_LEFT_TOP:
                            $position = self::POSITION_LEFT_BOTTOM;
                            break;
                        case self::POSITION_LEFT_MIDDLE:
                            $position = self::POSITION_CENTER_BOTTOM;
                            break;
                        case self::POSITION_LEFT_BOTTOM:
                            $position = self::POSITION_RIGHT_BOTTOM;
                            break;
                        case self::POSITION_CENTER_TOP:
                            $position = self::POSITION_LEFT_MIDDLE;
                            break;
                        case self::POSITION_CENTER_BOTTOM:
                            $position = self::POSITION_RIGHT_MIDDLE;
                            break;
                        case self::POSITION_RIGHT_TOP:
                            $position = self::POSITION_LEFT_TOP;
                            break;
                        case self::POSITION_RIGHT_MIDDLE:
                            $position = self::POSITION_CENTER_TOP;
                            break;
                        case self::POSITION_RIGHT_BOTTOM:
                            $position = self::POSITION_RIGHT_TOP;
                            break;
                    }

                    $oTranslate = $yOrTranslate;
                    $yOrTranslate['x'] = $oTranslate['y'] * -1;
                    $yOrTranslate['y'] = $oTranslate['x'];

                    $tmpHeight = $height;
                    $height = $width;
                    $width = $tmpHeight;

                    $fieldRotation += 90;
                }
            }

            switch ($position) {
                case self::POSITION_LEFT_TOP:
                case self::POSITION_LEFT_MIDDLE:
                case self::POSITION_LEFT_BOTTOM:
                    $x = $box->getLlx();
                    break;
                case self::POSITION_CENTER_TOP:
                case self::POSITION_CENTER_MIDDLE:
                case self::POSITION_CENTER_BOTTOM:
                    $x = $box->getLlx() + $box->getWidth() / 2 - $width / 2;
                    break;

                case self::POSITION_RIGHT_TOP:
                case self::POSITION_RIGHT_MIDDLE:
                case self::POSITION_RIGHT_BOTTOM:
                    $x = $box->getLlx() + $box->getWidth() - $width;
                    break;
            }

            switch ($position) {
                case self::POSITION_LEFT_TOP:
                case self::POSITION_CENTER_TOP:
                case self::POSITION_RIGHT_TOP:
                    $y = $box->getUry() - $height;
                    break;

                case self::POSITION_LEFT_MIDDLE:
                case self::POSITION_CENTER_MIDDLE:
                case self::POSITION_RIGHT_MIDDLE:
                    $y = $box->getLly() + $box->getHeight() / 2 - $height / 2;
                    break;

                case self::POSITION_LEFT_BOTTOM:
                case self::POSITION_RIGHT_BOTTOM:
                case self::POSITION_CENTER_BOTTOM:
                    $y = $box->getLly();
                    break;
            }

            $x += $yOrTranslate['x'];
            $y += $yOrTranslate['y'];

        } else {
            $x = $xOrPosition;
            $y = $yOrTranslate;
        }

        $acroForm = $document->getCatalog()->getAcroForm();

        // Ensure unique field name
        $fieldNames = [];
        foreach ($acroForm->getTerminalFieldsObjects() as $terminalObject) {
            $name = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($terminalObject->ensure());
            $fieldNames[$name] = $name;
        }

        $i = 1;
        $oFieldName = $fieldName;
        while (isset($fieldNames[$fieldName])) {
            $fieldName = $oFieldName . '_' . ($i++);
        }

        $field = new SetaPDF_Signer_SignatureField($fieldName, [$x, $y, $x + $width, $y + $height]);
        $fields = $acroForm->getFieldsArray(true);
        $fields->push($field->getIndirectObject($document));

        $annotations = $page->getAnnotations();
        $annotations->add($field);

        $field->getDictionary()->offsetSet('P', $page->getObject());

        if (isset($fieldRotation)) {
            $field->getAppearanceCharacteristics(true)->setRotation($fieldRotation);
        }

        return $field;
    }

    /**
     * Get a signature field instance and creates it if it is not already available.
     *
     * If no field is found a hidden field on page one will be created automatically.
     *
     * @param SetaPDF_Core_Document $document
     * @param string $fieldName The field name in UTF-8 encoding
     * @param bool $create Automatically creates a hidden field if none was found by the specified name
     * @return bool|SetaPDF_Signer_SignatureField
     */
    static public function get(
        SetaPDF_Core_Document $document,
        $fieldName = self::DEFAULT_FIELD_NAME,
        $create = true
    ) {
        $acroForm = $document->getCatalog()->getAcroForm();
        foreach ($acroForm->getTerminalFieldsObjects() as $terminalObject) {
            $name = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($terminalObject->ensure());
            if ($name === $fieldName) {
                try {
                    return new SetaPDF_Signer_SignatureField($terminalObject);
                } catch (InvalidArgumentException $e) {
                    throw new SetaPDF_Signer_Exception(
                        sprintf('Field "%s" is not a signature field.', $fieldName),
                        0,
                        $e
                    );
                }
            }
        }

        if ($create === false) {
            return false;
        }

        return self::add($document, $fieldName);
    }

    /**
     * The constructor.
     *
     * An instance could be created by an existing dictionary, indirect object/reference or by the same parameters
     * as {@link createAnnotationDictionary()}.
     *
     * @see createAnnotationDictionary()
     * @param string|array|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface $objectOrDictionary
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
                ['SetaPDF_Signer_SignatureField', 'createAnnotationDictionary'],
                $args
            );
            unset($args);
        }

        $fieldType = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($dictionary, 'FT', false);
        if (!$fieldType || $fieldType->getValue() !== 'Sig') {
            throw new InvalidArgumentException('The field type in a signature widget annotation shall be "Sig".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Returns the qualified name.
     *
     * @return string
     */
    public function getQualifiedName()
    {
        $dict = $this->getDictionary();
        $dict = SetaPDF_Core_Type_Dictionary_Helper::resolveDictionaryByAttribute($dict, 'T');

        return SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($dict);
    }

    /**
     * Set if an how the document should be locked after a signature is applied to this field.
     *
     * NOTICE: Currently only {@link LOCK_DOCUMENT_ALL} or none is supported.
     *
     * @param SetaPDF_Core_Document $document
     * @param string $action A name which, in conjunction with Fields, indicates the set of $fields that should be locked.
     * @param null|array $fields
     * @throws SetaPDF_Exception_NotImplemented
     */
    public function setLock(SetaPDF_Core_Document $document, $action = self::LOCK_DOCUMENT_ALL, $fields = null)
    {
        $dictionary = $this->getDictionary();

        $action = in_array($action, [
            self::LOCK_DOCUMENT_ALL, self::LOCK_DOCUMENT_EXCLUDE, self::LOCK_DOCUMENT_INCLUDE
        ], true) ? $action : self::LOCK_DOCUMENT_NONE;

        if ($action === self::LOCK_DOCUMENT_NONE && $dictionary->offsetExists('Lock')) {
            $dictionary->offsetUnset('Lock');
            return;
        }

        $lock = $dictionary->getValue('Lock');
        if (null === $lock) {
            $lock = $document->createNewObject(new SetaPDF_Core_Type_Dictionary([
                'Type' => new SetaPDF_Core_Type_Name('SigFieldLock'),
            ]));
            $dictionary->offsetSet('Lock', $lock);
            $lock = $lock->ensure();
        }

        $lock['Action'] = new SetaPDF_Core_Type_Name($action);
        // This is needed by Acrobat but undocumented ...
        $lock['P'] = new SetaPDF_Core_Type_Numeric($action === self::LOCK_DOCUMENT_ALL ? 1 : 2);

        if ((is_array($fields) && $action === self::LOCK_DOCUMENT_INCLUDE) || $action === self::LOCK_DOCUMENT_EXCLUDE) {
            /* The handling of specific fields is not supported atm.
             * It will require much more work in the signature creation -> MDP
             */
            throw new SetaPDF_Exception_NotImplemented(
                'Locking of specific fields is not implemented yet.'
            );

            /*$lock['Fields'] = new SetaPDF_Core_Type_Array();
            foreach ($fields AS $field) {
                $lock['Fields']->getValue()->push(new SetaPDF_Core_Type_String($field));
            }*/
        } else {
            $lock->offsetUnset('Fields');
        }
    }

    /**
     * Get information about lock data if available.
     *
     * @return array|bool
     */
    public function getLock()
    {
        $lock = $this->getDictionary()->getValue('Lock');
        if (null === $lock) {
            return false;
        }

        /**
         * @var $lock SetaPDF_Core_Type_Dictionary
         */
        $lock = $lock->ensure();

        $fields = [];
        $action = $lock->getValue('Action')->ensure()->getValue();
        $_fields = $lock->getValue('Fields');
        if ($_fields && ($action === self::LOCK_DOCUMENT_INCLUDE || $action === self::LOCK_DOCUMENT_EXCLUDE)) {
            $_fields = $_fields->ensure();
            foreach ($_fields AS $field) {
                $fields[] = $field->getValue();
            }
        }

        return ['action' => $action, 'fields' => $fields];
    }

    /**
     * Get the signature value dictionary of this field, if present.
     *
     * @return null|SetaPDF_Core_Type_Dictionary
     */
    public function getValue()
    {
        $dictionary = $this->getDictionary();
        $value = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($dictionary, 'V');

        return $value;
    }
}