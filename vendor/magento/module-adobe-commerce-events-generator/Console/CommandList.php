<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsGenerator\Console;

use Magento\AdobeCommerceEventsGenerator\Console\Command\GenerateModuleCommand;
use Magento\Framework\Console\CommandListInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\ObjectManagerInterface;

/**
 * Provides list of commands to be available for uninstalled application
 */
class CommandList implements CommandListInterface
{
    /**
     * @param ObjectManagerInterface $objectManager
     * @param ModuleList $moduleList
     */
    public function __construct(
        private ObjectManagerInterface $objectManager,
        private ModuleList $moduleList
    ) {
    }

    /**
     * Gets list of command classes
     *
     * @return string[]
     */
    private function getCommandsClasses(): array
    {
        return [
            GenerateModuleCommand::class => [
                'Magento_AdobeCommerceEventsClient',
                'Magento_AdobeCommerceEventsGenerator',
                'Magento_AdobeCommerceOutOfProcessExtensibility',
            ],
        ];
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function getCommands(): array
    {
        $commands = [];
        foreach ($this->getCommandsClasses() as $class => $requiredModules) {
            if (!$this->checkModulesEnabled($requiredModules)) {
                continue;
            }

            if (class_exists($class)) {
                $commands[] = $this->objectManager->get($class);
            } else {
                throw new LocalizedException(__('Class ' . $class . ' does not exist'));
            }
        }

        return $commands;
    }

    /**
     * Checks if array of required modules are enabled
     *
     * @param array $requiredModules
     * @return bool
     */
    private function checkModulesEnabled(array $requiredModules): bool
    {
        foreach ($requiredModules as $moduleName) {
            if (!$this->moduleList->has($moduleName)) {
                return false;
            }
        }

        return true;
    }
}
