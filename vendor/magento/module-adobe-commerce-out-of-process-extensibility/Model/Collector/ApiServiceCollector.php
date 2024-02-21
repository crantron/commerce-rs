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
 *************************************************************************
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector;

use Exception;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util\FileOperator;
use Magento\Framework\App\Utility\ReflectionClassFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use SplFileInfo;

/**
 * Collects API interfaces events from the Adobe Commerce module.
 */
class ApiServiceCollector implements CollectorInterface
{
    /**
     * @param DriverInterface $filesystem
     * @param FileOperator $fileOperator
     * @param NameFetcher $nameFetcher
     * @param EventMethodCollector $eventMethodCollector
     * @param ReflectionClassFactory $reflectionClassFactory
     */
    public function __construct(
        private DriverInterface $filesystem,
        private FileOperator $fileOperator,
        private NameFetcher $nameFetcher,
        private EventMethodCollector $eventMethodCollector,
        private ReflectionClassFactory $reflectionClassFactory
    ) {
    }

    /**
     * Collects API interfaces events from the Adobe Commerce module.
     *
     * @param string $modulePath
     * @return EventData[]
     */
    public function collect(string $modulePath): array
    {
        $events = [];
        $realPath = $this->filesystem->getRealPath($modulePath . '/Api');

        try {
            if (!$realPath || !$this->filesystem->isDirectory($realPath)) {
                return $events;
            }
        } catch (FileSystemException $e) {
            return $events;
        }

        $directoryIterator = $this->fileOperator->getDirectoryIterator($realPath);

        foreach ($directoryIterator as $fileItem) {
            /** @var $fileItem SplFileInfo */
            if ($fileItem->isDir() || $fileItem->getExtension() !== 'php') {
                continue;
            }

            try {
                $interface = $this->nameFetcher->getNameFromFile($fileItem);
                $refClass = $this->reflectionClassFactory->create($interface);
            } catch (Exception $e) {
                continue;
            }

            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $events = array_merge($events, $this->eventMethodCollector->collect($refClass));
        }

        return $events;
    }
}
