<?php
/**
 * This file is part of the SetaPDF-Signer Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Element.php 1562 2020-09-24 15:47:29Z maximilian.kresse $
 */

/**
 * Class representing an ASN.1 element.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_Asn1_Element
{
    /**
     * Tag class constant
     */
    const TAG_CLASS_MASK                = "\xC0";

    /**
     * Tag class constant
     */
    const TAG_CLASS_UNIVERSAL           = "\x00";

    /**
     * Tag class constant
     */
    const TAG_CLASS_APPLICATION         = "\x40";

    /**
     * Tag class constant
     */
    const TAG_CLASS_CONTEXT_SPECIFIC    = "\x80";

    /**
     * Tag class constant
     */
    const TAG_CLASS_PRIVATE             = "\xC0";

    /**
     * Constructed?
     */
    const IS_CONSTRUCTED                = "\x20";


    /**
     * Tag mask
     */
    const TAG_MASK                      = "\x1F";

    /**
     * Subtype constant
     */
    const BOOLEAN                       = "\x01";

    /**
     * Subtype constant
     */
    const INTEGER                       = "\x02";

    /**
     * Subtype constant
     */
    const BIT_STRING                    = "\x03";

    /**
     * Subtype constant
     */
    const OCTET_STRING                  = "\x04";

    /**
     * Subtype constant
     */
    const NULL                          = "\x05";

    /**
     * Subtype constant
     */
    const OBJECT_IDENTIFIER             = "\x06";

    /**
     * Subtype constant
     */
    const OBJECT_DESCRIPTOR                = "\x07";

    /**
     * Subtype constant
     */
    const EXTERNAL                        = "\x08";

    /**
     * Subtype constant
     */
    const REAL                            = "\x09";

    /**
     * Subtype constant
     */
    const ENUMERATED                    = "\x0A";

    /**
     * Subtype constant
     */
    const UTF8_STRING                   = "\x0C";

    /**
     * Subtype constant
     */
    const RELATIVE_OID                  = "\x0D";

    /**
     * Subtype constant
     */
    const SEQUENCE                        = "\x10";

    /**
     * Subtype constant
     */
    const SET                             = "\x11";

    /**
     * Subtype constant
     */
    const NUMERIC_STRING                = "\x12";

    /**
     * Subtype constant
     */
    const PRINTABLE_STRING              = "\x13";

    /**
     * Subtype constant
     */
    const T61_STRING                    = "\x14";

    /**
     * Subtype constant
     */
    const VIDEOTEXT_STRING              = "\x15";

    /**
     * Subtype constant
     */
    const IA5_STRING                    = "\x16";

    /**
     * Subtype constant
     */
    const UTC_TIME                      = "\x17";

    /**
     * Subtype constant
     */
    const GENERALIZED_TIME              = "\x18";

    /**
     * Subtype constant
     */
    const GRAPHIC_STRING                = "\x19";

    /**
     * Subtype constant
     */
    const VISIBLE_STRING                = "\x1A";

    /**
     * Subtype constant
     */
    const GENERAL_STRING                = "\x1B";

    /**
     * Subtype constant
     */
    const UNIVERSAL_STRING              = "\x1C";

    /**
     * Subtype constant
     */
    const BMPSTRING                     = "\x1E";

    /**
     * The ident tag
     *
     * @var string
     */
    protected $_ident = "\x00";

    /**
     * The byte value
     *
     * @var string
     */
    protected $_value = '';

    /**
     * Array of child nodes
     *
     * @var array Array of {@link SetaPDF_Signer_Asn1_Element}
     */
    protected $_children = [];

    /**
     * The parent node
     *
     * @var SetaPDF_Signer_Asn1_Element
     */
    protected $_parent;

    /**
     * Parses a BER encoded string.
     *
     * @param string $s
     * @return SetaPDF_Signer_Asn1_Element
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    static public function parse($s)
    {
        $p = 0;
        $result = self::_parse($s, $p, true);

        if (count($result) === 0) {
            throw new SetaPDF_Signer_Asn1_Exception('String is not a valid ASN.1 structure.');
        }

        return reset($result);
    }

    /**
     * Parses a BER encoded string or sequence.
     *
     * @param string $s
     * @param integer $p
     * @param boolean $outer
     * @return array
     * @throws SetaPDF_Signer_Asn1_Exception
     */
    static protected function _parse($s, &$p = 0, $outer = false)
    {
        $result = [];

        $sLength = strlen($s);
        if ($sLength < 2) {
            throw new SetaPDF_Signer_Asn1_Exception('String is not a valid ASN.1 structure.');
        }

        while ($p < $sLength) {
            $ident = $s[$p++];
            $subType = $ident & self::TAG_MASK;

            // eof or terminated by a null byte
            if (!isset($s[$p]) || $ident === "\x00") {
                break;
            }

            if (!isset($s[$p + 1]) && $ident === self::NULL) {
                $result[] = new self($ident);
                break;
            }

            $length = ord($s[$p++]);
            if (($length & 128) === 128) {
                if ($length === 128) {
                    // indefinite form
                    // $p2 = $p;
                    $result[] = new self($ident, '', self::_parse($s, $p));

                    if (!(isset($s[$p]) && $s[$p] === "\x00")) {
                        throw new SetaPDF_Signer_Asn1_Exception(
                            'No EOC octect in indefinite length structure found.'
                        );
                    }

                    $p++;

                    // real read length would be: $p - $p2;
                    // var_dump($p - $p2);

                    continue;
                }

                // long definite form
                $tempLength = 0;
                for ($x = 0; $x < ($length & (128 - 1)); $x++) {
                    $tempLength = ord($s[$p++]) + ($tempLength * 256);
                }

                $length = $tempLength;
            }

            $value = (string)substr($s, $p, $length);
            if ($length > 0 && (strlen($s) - $p) <= 0) {
                throw new SetaPDF_Signer_Asn1_Exception('String is not a valid ASN.1 structure.');
            }

            switch ($subType) {
                case (($ident & self::IS_CONSTRUCTED) !== "\x00"):
                case self::SEQUENCE:
                case self::SET:
                    if ($value === '') {
                        $result[] = new self($ident, '');
                    } else {
                        $result[] = new self($ident, '', self::_parse($value));
                    }
                    break;
                default:
                    $result[] = new self($ident, $value);
            }

            $p += $length;

            if ($outer) {
                break;
            }
        }

        return $result;
    }

    /**
     * Finds a node by OID.
     *
     * @param string $oid OID in dot form
     * @param SetaPDF_Signer_Asn1_Element $value Root node to start from
     * @return SetaPDF_Signer_Asn1_Element|null
     */
    static public function findByOid($oid, SetaPDF_Signer_Asn1_Element $value)
    {
        $ident = $value->getIdent();
        $subType = $ident & self::TAG_MASK;

        switch ($subType) {
            case (($ident & self::IS_CONSTRUCTED) !== "\x00"):
            case self::SEQUENCE:
            case self::SET:
                foreach ($value->getChildren() as $child) {
                    $res = self::findByOid($oid, $child);
                    if ($res instanceof self) {
                        return $res;
                    }
                }

                break;
            case (($ident & self::OBJECT_IDENTIFIER) === self::OBJECT_IDENTIFIER):
                if (SetaPDF_Signer_Asn1_Oid::decode($value->getValue()) === $oid) {
                    return $value;
                }
                break;
        }

        return null;
    }

    /**
     * Finds a node by a path.
     *
     * @param string $path A string defining the path of the node (e.g. 1/2/3/5)
     * @param SetaPDF_Signer_Asn1_Element $value
     * @return SetaPDF_Signer_Asn1_Element|boolean
     */
    static public function findByPath($path, SetaPDF_Signer_Asn1_Element $value)
    {
        $path = trim($path, '/');
        $tmp = $value;
        foreach (explode('/', $path) as $childId) {
            $tmp = $tmp->getChild($childId);
            if ($tmp === false) {
                return $tmp;
            }
        }
        return $tmp;
    }

    /**
     * Returns the length byte in DER encoding.
     *
     * @param integer $length
     * @return string
     */
    static public function lengthToDer($length)
    {
        if ($length < 128) {
            return chr($length);
        }

        $out = '';
        while ($length >= 256) {
            $out = chr($length % 256) . $out;
            $length = floor($length / 256);
        }
        $out = chr($length) . $out;
        $out = chr(128 | strlen($out)) . $out;
        return $out;
    }

    /**
     * The constructor.
     *
     * @param string $ident The identifier byte
     * @param string $value The value in binary form
     * @param array $children Array of {@link SetaPDF_Signer_Asn1_Element} instances
     */
    public function __construct($ident, $value='', $children = [])
    {
        $this->setIdent($ident);
        $this->setValue($value);
        $this->setChildren($children);
    }

    /**
     * Set the identifier byte.
     *
     * @param string $ident
     */
    public function setIdent($ident)
    {
        $this->_ident = $ident;
    }

    /**
     * Get the identifier byte.
     *
     * @return string
     */
    public function getIdent()
    {
        return $this->_ident;
    }

    /**
     * Set the value.
     *
     * @param string $value Value in binary form
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Get the value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Set child nodes.
     *
     * @param SetaPDF_Signer_Asn1_Element[] $children Array of {@link SetaPDF_Signer_Asn1_Element} instances
     */
    public function setChildren($children = [])
    {
        if (!is_array($children)) {
            $children = [$children];
        }

        $this->_children = [];
        foreach ($children AS $child) {
            $this->addChild($child);
        }
    }

    /**
     * Get child nodes.
     *
     * @return SetaPDF_Signer_Asn1_Element[] Array of {@link SetaPDF_Signer_Asn1_Element}
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Get the child count.
     *
     * @return int
     */
    public function getChildCount()
    {
        return count($this->_children);
    }

    /**
     * Add a child node.
     *
     * @param SetaPDF_Signer_Asn1_Element $child
     */
    public function addChild(SetaPDF_Signer_Asn1_Element $child)
    {
        $this->_children[] = $child;
        $child->setParent($this);
    }

    /**
     * Remove a child node.
     *
     * @param SetaPDF_Signer_Asn1_Element $child
     * @return boolean
     */
    public function removeChild(SetaPDF_Signer_Asn1_Element $child)
    {
        foreach ($this->_children AS $k => $_child) {
            if ($child === $_child) {
                unset($this->_children[$k]);
                $this->_children = array_values($this->_children);
                return true;
            }
        }

        return false;
    }

    /**
     * Get a child by Id.
     *
     * @param integer $id
     * @return SetaPDF_Signer_Asn1_Element|boolean
     */
    public function getChild($id)
    {
        if (array_key_exists($id, $this->_children)) {
            return $this->_children[$id];
        }

        return false;
    }

    /**
     * Set parent node.
     *
     * @param SetaPDF_Signer_Asn1_Element $parent
     */
    public function setParent(self $parent)
    {
        $this->_parent = $parent;
    }

    /**
     * Get parent.
     *
     * @return SetaPDF_Signer_Asn1_Element
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Returns the BER encoded string.
     *
     * @return string
     */
    public function __toString()
    {
        $value = '';

        $subType = $this->_ident & self::TAG_MASK;

        switch ($subType) {
            case (($this->_ident & self::IS_CONSTRUCTED) !== "\x00"):
            case self::SEQUENCE:
            case self::SET:
                if (count($this->_children) > 0) {
                    foreach ($this->_children as $child) {
                        $value .= (string)$child;
                    }
                } else {
                    $value .= (string)$this->_value;
                }
                break;

            default:
                $value = $this->_value;
        }

        $length = self::lengthToDer(strlen($value));

        return $this->_ident . $length . $value;
    }
}