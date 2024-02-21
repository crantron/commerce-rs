<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Config;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Config\Reader\Filesystem;

/**
 * Reader for io_events.xml configuration files
 */
class Reader extends Filesystem
{
    private const CONFIGURATION_FILE = 'io_events.xml';

    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = ['/config/event' => 'name', '/config/event/fields/field' => 'name'];

    /**
     * @param FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = self::CONFIGURATION_FILE
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName
        );
    }

    /**
     * Load configuration scope and merge it with primary configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $fileList = $this->_fileResolver->get($this->_fileName, 'primary');
        if (!count($fileList)) {
            return parent::read($scope);
        }

        return array_merge(parent::read($scope), $this->_readFiles($fileList));
    }
}
