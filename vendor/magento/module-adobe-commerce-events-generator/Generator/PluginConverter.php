<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsGenerator\Generator;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\PluginConverter\InterfaceProcessor;

/**
 * Converts classes to the data needed for plugin generation
 */
class PluginConverter
{
    public const TYPE_API_INTERFACE = 'Api';
    public const TYPE_RESOURCE_MODEL = 'ResourceModel';

    /**
     * @param InterfaceProcessor $interfaceProcessor
     */
    public function __construct(
        private InterfaceProcessor $interfaceProcessor
    ) {
    }

    /**
     * Converts list of classes or interfaces with their methods to the array of data for plugins generation
     *
     * @param array $interfaces
     * @param string|null $type
     * @return array
     */
    public function convert(array $interfaces, ?string $type = null): array
    {
        $plugins = [];

        foreach ($interfaces as $interface => $methods) {
            $interfaceData = $this->interfaceProcessor->process(
                $interface,
                EventingClassGenerator::MODULE_VENDOR,
                EventingClassGenerator::MODULE_NAME,
                EventingClassGenerator::MODULE_PLUGIN_SPACE,
                $type
            );

            $pluginNamespace = $interfaceData[InterfaceProcessor::PLUGIN_NAMESPACE];
            $interfaceNameShort = $interfaceData[InterfaceProcessor::INTERFACE_NAME_SHORT];

            $plugins[] = [
                'class' => $pluginNamespace . '\\' . $interfaceNameShort . 'Plugin',
                'namespace' => $pluginNamespace,
                'interface' => $interface,
                'interfaceShort' => $interfaceNameShort,
                'pluginName' => implode('_', array_map(
                    'strtolower',
                    [
                        $interfaceData[InterfaceProcessor::VENDOR],
                        $interfaceData[InterfaceProcessor::MODULE],
                        $interfaceNameShort,
                        'Plugin'
                    ]
                )),
                'name' => $interfaceNameShort . 'Plugin',
                'methods' => $this->convertForPlugins($methods, $interface),
                'path' => $interfaceData[InterfaceProcessor::PATH] . $interfaceNameShort . 'Plugin.php',
                'type' => $type
            ];
        }

        return $plugins;
    }

    /**
     * Converts list of methods for plugin generator suitable format
     *
     * @param array $methods
     * @param string $interface
     * @return array
     */
    private function convertForPlugins(array $methods, string $interface): array
    {
        $result = [];

        $prefix = '';
        $namespaceParts = explode('\\', preg_replace('/Interface$/', '', $interface));
        foreach ($namespaceParts as $namespacePart) {
            $prefix .= $this->convertCamelCases($namespacePart) . '.';
        }

        foreach ($methods as $methodData) {
            $methodName = $methodData['name'];
            $result[] = [
                'name' => ucfirst($methodName),
                'nameLower' => lcfirst($methodName),
                'eventCode' => $prefix . $this->convertCamelCases($methodName),
                'params' => $methodData['params'] ?? []
            ];
        }

        return $result;
    }

    /**
     * Convert camel case to lowercase with underscores.
     *
     * CamelCaseFormat => camel_case_format
     *
     * @param string $string
     * @return string
     */
    private function convertCamelCases(string $string): string
    {
        return implode('_', array_map('strtolower', preg_split('/(?=[A-Z])/', $string, -1, PREG_SPLIT_NO_EMPTY)));
    }
}
