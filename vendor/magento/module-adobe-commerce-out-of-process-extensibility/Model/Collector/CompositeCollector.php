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

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Collects and caches events from different collectors
 */
class CompositeCollector implements CollectorInterface
{
    private const CACHE_ID = 'composite_events_collector';

    /**
     * @param EventDataFactory $eventDataFactory
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param array $collectors
     * @param string $cachePrefix
     */
    public function __construct(
        private EventDataFactory $eventDataFactory,
        private CacheInterface $cache,
        private SerializerInterface $serializer,
        private array $collectors,
        private string $cachePrefix = self::CACHE_ID
    ) {
    }

    /**
     * Collects events from the different collectors
     *
     * @param string $modulePath
     * @return EventData[]
     */
    public function collect(string $modulePath): array
    {
        $cacheId = $this->getCacheId($modulePath);
        $cachedEvents = $this->cache->load($cacheId);
        if ($cachedEvents && is_string($cachedEvents)) {
            return $this->unserializeEvents($cachedEvents);
        }

        $events = [];
        foreach ($this->collectors as $collector) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $events = array_merge($events, $collector->collect($modulePath));
        }
        $this->cache->save($this->serializeEvents($events), $cacheId);

        return $events;
    }

    /**
     * Returns cache identification.
     *
     * @param string $modulePath
     * @return string
     */
    private function getCacheId(string $modulePath): string
    {
         return $this->cachePrefix . '_' . implode('_', array_keys($this->collectors)) . '_' . $modulePath;
    }

    /**
     * Converts EventData object to simple array and serializes it.
     *
     * @param EventData[] $events
     * @return string
     */
    private function serializeEvents(array $events): string
    {
        $data = [];
        foreach ($events as $event) {
            $data[] = $event->getData();
        }

        return $this->serializer->serialize($data);
    }

    /**
     * Unserializes array of event data and creates array of EventData objects based on it.
     *
     * @param string $eventsData
     * @return array
     */
    private function unserializeEvents(string $eventsData): array
    {
        $events = [];

        $result = $this->serializer->unserialize($eventsData);
        if (is_array($result)) {
            foreach ($result as $data) {
                $events[$data[EventData::EVENT_NAME]] = $this->eventDataFactory->create($data);
            }
        }

        return $events;
    }
}
