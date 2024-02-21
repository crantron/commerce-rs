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

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\ObserverEventsCollector\DispatchMethodCollector;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\ObserverEventsCollector\EventPrefixesCollector;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util\FileOperator;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Exception;

/**
 * Collects observer events list
 */
class ObserverEventsCollector implements CollectorInterface
{
    /**
     * @param FileOperator $fileOperator
     * @param DriverInterface $filesystem
     * @param LoggerInterface $logger
     * @param DispatchMethodCollector $dispatchMethodCollector
     * @param EventPrefixesCollector $eventPrefixesCollector
     * @param string $excludeDirPattern
     * @param bool $includeBeforeEvents
     */
    public function __construct(
        private FileOperator $fileOperator,
        private DriverInterface $filesystem,
        private LoggerInterface $logger,
        private DispatchMethodCollector $dispatchMethodCollector,
        private EventPrefixesCollector $eventPrefixesCollector,
        private string $excludeDirPattern = '/^((?!test|Test|dev).)*$/',
        private bool $includeBeforeEvents = false
    ) {
    }

    /**
     * @inheritDoc
     */
    public function collect(string $modulePath): array
    {
        $result = [];

        $regexIterator = $this->fileOperator->getRecursiveFileIterator(
            $modulePath,
            ['/\.php$/', $this->excludeDirPattern]
        );

        foreach ($regexIterator as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            try {
                $fileContent = $this->filesystem->fileGetContents($fileInfo->getPathname());
                if (strpos($fileContent, '$_eventPrefix') !== false) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $result = array_merge(
                        $result,
                        $this->eventPrefixesCollector->fetchEvents($fileInfo, $fileContent, $this->includeBeforeEvents)
                    );
                } elseif (strpos($fileContent, '->dispatch(') !== false) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $result = array_merge(
                        $result,
                        $this->dispatchMethodCollector->fetchEvents($fileInfo, $fileContent, $this->includeBeforeEvents)
                    );
                }
            } catch (FileSystemException $e) {
                $this->logger->error(sprintf(
                    'Unable to get file content during observer events collecting. File %s. Error: %s',
                    $fileInfo->getPathname(),
                    $e->getMessage()
                ));
                continue;
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Unable to collect observer events from the file %s. Error: %s',
                    $fileInfo->getPathname(),
                    $e->getMessage()
                ));
                continue;
            }
        }

        return $result;
    }
}
