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
use Magento\Framework\Exception\LocalizedException;
use SplFileInfo;

/**
 * Collects event names from dispatch methods.
 */
class DispatchMethodCollector
{
    /**
     * @param NameFetcher $nameFetcher
     * @param EventDataFactory $eventDataFactory
     */
    public function __construct(
        private NameFetcher $nameFetcher,
        private EventDataFactory $eventDataFactory
    ) {
    }

    /**
     * Parses and returns array of event names from dispatch methods.
     *
     * @param SplFileInfo $fileInfo
     * @param string $fileContent
     * @param bool $includeBeforeEvents
     * @return array
     * @throws LocalizedException
     */
    public function fetchEvents(SplFileInfo $fileInfo, string $fileContent, bool $includeBeforeEvents = false): array
    {
        $events = [];

        preg_match_all(
            '/->dispatch\([^\)\.]*?\n?[^\)\.]*?(?<eventName>(\'[^\']*\'|\"[^\"]*\"))\s*\,/im',
            $fileContent,
            $matches
        );

        if (!empty($matches['eventName'])) {
            $className = $this->nameFetcher->getNameFromFile($fileInfo, $fileContent);
            foreach ($matches['eventName'] as $eventName) {
                $eventName = trim($eventName, '"\'');
                if ($includeBeforeEvents || !str_contains($eventName, '_before')) {
                    $eventName = CollectorInterface::EVENT_TYPE_OBSERVER . '.' . $eventName;
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
