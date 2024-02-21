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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\ObserverEventsCollector;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\CollectorInterface;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\EventData;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\EventDataFactory;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\NameFetcher;
use Magento\Framework\App\Utility\ReflectionClassFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use ReflectionException;
use SplFileInfo;

/**
 * Collects events for classes that contains $_eventPrefix variable.
 */
class EventPrefixesCollector
{
    /**
     * Array of events for Magento\Framework\Model\AbstractModel class
     *
     * @var array
     */
    private array $abstractModelEvents = [
        '_save_before',
        '_save_commit_after',
        '_save_after',
        '_delete_before',
        '_delete_after',
        '_delete_commit_after',
    ];

    /**
     * @param NameFetcher $nameFetcher
     * @param EventDataFactory $eventDataFactory
     * @param ReflectionClassFactory $reflectionClassFactory
     */
    public function __construct(
        private NameFetcher $nameFetcher,
        private EventDataFactory $eventDataFactory,
        private ReflectionClassFactory $reflectionClassFactory
    ) {
    }

    /**
     * Collects events for classes that contains $_eventPrefix variable
     * and instance of Magento\Framework\Model\AbstractModel.
     * If the class is not an instance of mentioned above class we can't generate event codes for it.
     *
     * @param SplFileInfo $fileInfo
     * @param string $fileContent
     * @param bool $includeBeforeEvents
     * @return array
     * @throws LocalizedException
     * @throws ReflectionException
     */
    public function fetchEvents(SplFileInfo $fileInfo, string $fileContent, bool $includeBeforeEvents = false): array
    {
        $events = [];

        $className = $this->nameFetcher->getNameFromFile($fileInfo, $fileContent);
        $refClass = $this->reflectionClassFactory->create($className);

        preg_match('/\$_eventPrefix\s=\s(?<eventPrefix>(\'.*?\'|\".*?\"));/im', $fileContent, $matches);

        if (!isset($matches['eventPrefix'])) {
            throw new LocalizedException(
                __('Event prefix name cannot be fetched from the file: %1', $fileInfo->getPathname())
            );
        }

        $prefix = CollectorInterface::EVENT_TYPE_OBSERVER . '.' . trim($matches['eventPrefix'], '\'"');
        if ($refClass->isSubclassOf(AbstractModel::class)) {
            foreach ($this->abstractModelEvents as $eventSuffix) {
                if ($includeBeforeEvents || strpos($eventSuffix, '_before') === false) {
                    $eventName = $prefix . $eventSuffix;
                    $events[$eventName] = $this->eventDataFactory->create([
                        EventData::EVENT_NAME => $eventName,
                        EventData::EVENT_CLASS_EMITTER => $className,
                    ]);
                }
            }
        }

        return $events;
    }
}
