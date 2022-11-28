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
 * Manager for information resolver instances.
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Signer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Signer_InformationResolver_Manager
{
    /**
     * All resolvers.
     *
     * @var SetaPDF_Signer_InformationResolver_ResolverInterface[]
     */
    protected $_resolvers;

    /**
     * A logger instance.
     *
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Set a logger instance.
     *
     * @param SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Get the logger instance.
     *
     * If no logger instance was passed before a new instance of {@link SetaPDF_Signer_ValidationRelatedInfo_Logger} is
     * returned.
     *
     * @return SetaPDF_Signer_ValidationRelatedInfo_LoggerInterface
     */
    public function getLogger()
    {
        if ($this->_logger === null) {
            $this->_logger = new SetaPDF_Signer_ValidationRelatedInfo_Logger();
        }

        return $this->_logger;
    }

    /**
     * Add a resolver instance.
     *
     * @param SetaPDF_Signer_InformationResolver_ResolverInterface $resolver
     */
    public function addResolver(SetaPDF_Signer_InformationResolver_ResolverInterface $resolver)
    {
        $this->_resolvers[] = $resolver;
    }

    /**
     * Resolve the given URI by the first accepting resolver instance.
     *
     * @param string $uri
     * @return array An array where index 0 is the content-type and 1 is the content itself.
     * @throws SetaPDF_Signer_InformationResolver_NoResolverFoundException
     */
    public function resolve($uri)
    {
        foreach ($this->_resolvers as $resolver) {
            $resolver->setLogger($this->getLogger());
            if ($resolver->accepts($uri)) {
                return $resolver->resolve($uri);
            }
        }

        $this->getLogger()->log('No resolver found for URI "{uri}".', ['uri' => $uri]);

        throw new SetaPDF_Signer_InformationResolver_NoResolverFoundException(
            sprintf('No resolver for URI "%s" found.', $uri)
        );
    }
}
