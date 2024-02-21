<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\PluginConverter;

/**
 * Process an interface name and extracts data for plugin generation
 */
class InterfaceProcessor
{
    public const TYPE_API_INTERFACE = 'Api';
    public const TYPE_RESOURCE_MODEL = 'ResourceModel';

    public const VENDOR = 'vendor';
    public const MODULE = 'module';
    public const INTERFACE_NAME_SHORT = 'interfaceNameShort';
    public const PATH = 'path';
    public const PLUGIN_NAMESPACE = 'pluginNamespace';

    /**
     * Processes an interface name and extracts data for plugin generation
     *
     * @param string $interface
     * @param string $moduleVendor
     * @param string $moduleName
     * @param string $modulePluginSpace
     * @param string|null $type
     * @return array
     */
    public function process(
        string $interface,
        string $moduleVendor,
        string $moduleName,
        string $modulePluginSpace,
        ?string $type = null
    ): array {
        $namespaceChunk = explode('\\', $interface);

        [$vendor, $module] = [$namespaceChunk[0], $namespaceChunk[1]];

        $pluginNamespace = rtrim(implode(
            '\\',
            [
                $moduleVendor,
                $moduleName,
                $modulePluginSpace,
                $namespaceChunk[1],
                $type
            ]
        ), '\\');

        $interfaceNameShort = $namespaceChunk[count($namespaceChunk) - 1];
        $path = '/' . $modulePluginSpace . '/' . $namespaceChunk[1] . '/';
        if ($type !== null) {
            $path .= $type . '/';
        }
        if ($type === self::TYPE_RESOURCE_MODEL) {
            $resourceModelPos = array_flip($namespaceChunk)[self::TYPE_RESOURCE_MODEL];
            $additionalParts = array_slice($namespaceChunk, $resourceModelPos + 1, -1);
            if (!empty($additionalParts)) {
                $path .= implode('/', $additionalParts) . '/';
                $pluginNamespace .= '\\' . implode('\\', $additionalParts);
            }
        }

        return [
            self::VENDOR => $vendor,
            self::MODULE => $module,
            self::INTERFACE_NAME_SHORT => $interfaceNameShort,
            self::PATH => $path,
            self::PLUGIN_NAMESPACE => $pluginNamespace
        ];
    }
}
