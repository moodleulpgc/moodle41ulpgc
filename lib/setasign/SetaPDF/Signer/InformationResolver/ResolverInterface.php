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

use SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface as LoggerInterface;

/**
 * Interface for information resolvers.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Signer_InformationResolver_ResolverInterface
{
    /**
     * Set a logger instance.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Checks whether the resolver can resolve the given URI.
     *
     * @param string $uri
     * @return boolean
     */
    public function accepts($uri);

    /**
     * Resolve the given URI.
     *
     * @param string $uri
     * @return array An array where index 0 is the content-type and 1 is the content itself.
     */
    public function resolve($uri);
}
