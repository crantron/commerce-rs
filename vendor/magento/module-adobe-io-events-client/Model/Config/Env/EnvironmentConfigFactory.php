<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model\Config\Env;

use Magento\AdobeIoEventsClient\Model\AdobeIOConfigurationProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Creates environment object based on environment type set in core_config_data table.
 */
class EnvironmentConfigFactory
{
    /**
     * @param array $environments
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private array $environments,
        private ObjectManagerInterface $objectManager,
        private ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Creates environment object based on a given environment type.
     *
     * @return EnvironmentConfigInterface
     * @throws NotFoundException
     */
    public function create(): EnvironmentConfigInterface
    {
        $envType = $this->scopeConfig->getValue(AdobeIOConfigurationProvider::XML_ADOBE_IO_PATH_ENVIRONMENT);

        if (!isset($this->environments[$envType])) {
            throw new NotFoundException(
                __(
                    'Environment with type "%1" does not exist. Available environment types are: "%2"',
                    $envType,
                    implode(",", array_keys($this->environments))
                )
            );
        }
        return $this->objectManager->create($this->environments[$envType]);
    }
}
