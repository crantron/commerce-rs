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

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Converter\EventCodeConverterInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * Collects event methods for the provided class
 */
class EventMethodCollector
{
    /**
     * @param EventDataFactory $eventDataFactory
     * @param EventCodeConverterInterface $eventCodeConverter
     * @param MethodFilter $methodFilter
     */
    public function __construct(
        private EventDataFactory $eventDataFactory,
        private EventCodeConverterInterface $eventCodeConverter,
        private MethodFilter $methodFilter
    ) {
    }

    /**
     * Collects public methods for the provided class and converts them to EventData
     *
     * @param ReflectionClass $reflectionClass
     * @return EventData[]
     */
    public function collect(ReflectionClass $reflectionClass): array
    {
        $events = [];

        $className = $reflectionClass->getName();
        $methodList = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methodList as $method) {
            $methodName = $method->getName();
            if (empty($methodName) || $this->methodFilter->isExcluded($methodName)) {
                continue;
            }

            $eventName = CollectorInterface::EVENT_TYPE_PLUGIN . '.' .
                $this->eventCodeConverter->convertToEventName($className, $methodName);

            $events[$eventName] = $this->eventDataFactory->create([
                EventData::EVENT_NAME => $eventName,
                EventData::EVENT_CLASS_EMITTER => $className,
            ]);
        }

        return $events;
    }
}
