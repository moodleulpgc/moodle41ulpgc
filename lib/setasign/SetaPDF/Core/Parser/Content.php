<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Parser
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Content.php 1549 2020-09-18 07:49:21Z jan.slabon $
 */

/**
 * A parser for PDF content
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Parser
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Parser_Content
{
    /**
     * The stream to parse
     *
     * @var string
     */
    protected $_stream;

    /**
     * Token stack
     *
     * @var array
     */
    protected $_stack = [];

    /**
     * Registered operators and their callbacks
     *
     * @var array
     */
    protected $_operators = [];

    /**
     * @var SetaPDF_Core_Parser_RawPdf
     */
    protected $_parser;

    /**
     * The constructor.
     *
     * @param string $stream
     */
    public function __construct($stream)
    {
        $this->_stream = (string)$stream;
    }

    /**
     * Release memory / cycled references
     */
    public function cleanUp()
    {
        if (null !== $this->_parser) {
            $this->_parser->cleanUp();
            $this->_parser = null;
        }
    }

    /**
     * Register a callback for an operator token.
     *
     * @param string|array $operator
     * @param callable $callback
     */
    public function registerOperator($operator, $callback)
    {
        if (is_array($operator)) {
            foreach ($operator AS $_operator) {
                $this->registerOperator($_operator, $callback);
            }

            return;
        }

        $this->_operators[$operator] = $callback;
    }

    /**
     * Unregister an operator and its callback.
     *
     * @param string $operator
     */
    public function unregisterOperator($operator)
    {
        unset($this->_operators[$operator]);
    }

    /**
     * Process the stream.
     */
    public function process()
    {
        $parser = $this->getParser();
        while (($value = $parser->readValue()) !== false) {
            if ($value->type !== SetaPDF_Core_Parser_RawPdf::TYPE_TOKEN) {
                $this->_stack[] = $value;
                continue;
            }

            if (!isset($this->_operators[$value->value])) {
                $this->_stack = [];
                continue;
            };

            $result = call_user_func(
                $this->_operators[$value->value],
                array_map([$parser, 'convertToObject'], $this->_stack),
                $value->value
            );
            $this->_stack = [];

            if ($result === false) {
                break;
            }
        }

        $this->_parser->cleanUp();
        $this->_parser = null;
    }

    /**
     * Processes the stream until a specifc oprator is matched.
     *
     * This method can be used to disable the process method for a specific token range.
     *
     * @param string $operator
     * @return bool
     * @throws SetaPDF_Core_Exception
     * @deprecated
     */
    public function skipUntil($operator)
    {
        return $this->getParser()->skipUntilToken($operator, true);
    }


    /**
     * Get the pdf parser instance for the passed content stream.
     *
     * @return SetaPDF_Core_Parser_RawPdf
     */
    public function getParser()
    {
        if ($this->_parser === null) {
            $this->_parser = new SetaPDF_Core_Parser_RawPdf(new SetaPDF_Core_Reader_String($this->_stream));
        }

        return $this->_parser;
    }
}