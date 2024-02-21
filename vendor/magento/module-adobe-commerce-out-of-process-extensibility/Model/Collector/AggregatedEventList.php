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

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\FullModuleList;

/**
 * Collects a list of events for specific collector for all modules
 */
class AggregatedEventList implements AggregatedEventListInterface
{
    /**
     * @param FullModuleList $fullModuleList
     * @param CollectorInterface $eventCollector
     * @param Dir $dir
     * @param IgnoredModulesList $ignoredModules
     */
    public function __construct(
        private FullModuleList $fullModuleList,
        private CollectorInterface $eventCollector,
        private Dir $dir,
        private IgnoredModulesList $ignoredModules
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getList(): array
    {
        $events = [];

        foreach ($this->fullModuleList->getAll() as $module) {
            if (in_array($module['name'], $this->ignoredModules->getList())) {
                continue;
            }

            $modulePath = $this->dir->getDir($module['name']);
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $events = array_merge($events, $this->eventCollector->collect($modulePath));
        }

        ksort($events);

        return $events;
    }
}
