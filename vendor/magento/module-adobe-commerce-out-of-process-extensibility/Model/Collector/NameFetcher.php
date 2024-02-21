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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector;

use Magento\Framework\Exception\LocalizedException;
use SplFileInfo;

/**
 * Used for fetching fully qualified class or interface names from the file
 */
class NameFetcher
{
    /**
     * Simple way to get class or interface name from Class.
     *
     * Can be improved by parsing file with php token.
     *
     * @param SplFileInfo $fileInfo
     * @param string|null $fileContent
     * @return string
     * @throws LocalizedException
     */
    public function getNameFromFile(SplFileInfo $fileInfo, string $fileContent = null): string
    {
        if (empty($fileContent)) {
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            $fileContent = file_get_contents($fileInfo->getPathname());
        }

        preg_match('/^namespace\s+(?<namespace>.*).*;/im', $fileContent, $matches);

        if (empty($matches['namespace'])) {
            throw new LocalizedException(__('Could not fetch namespace from the file: %1', $fileInfo->getPathname()));
        }
        $namespace = $matches['namespace'];

        $patterns = [
            '/^(abstract\s)?class\s+(?<class>\w*)/im' => 'class',
            '/^interface\s+(?<interface>\w*)/im' => 'interface',
        ];

        foreach ($patterns as $pattern => $match) {
            preg_match($pattern, $fileContent, $matches);

            if (!empty($matches[$match])) {
                return $namespace . '\\' . $matches[$match];
            }
        }

        throw new LocalizedException(
            __('Could not fetch Class or Interface name from the file: %1', $fileInfo->getPathname())
        );
    }
}
