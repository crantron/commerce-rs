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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\Collector;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Converter\EventCodeConverterInterface;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\Collector\Reflection\MethodParametersCollector;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\CollectorInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Collects resource model plugin information by event code.
 */
class ResourceModelCollector implements CollectorInterface
{
    /**
     * @var ModuleCollector
     */
    private ModuleCollector $moduleCollector;

    /**
     * @var EventCodeConverterInterface
     */
    private EventCodeConverterInterface $eventCodeConverter;

    /**
     * @var MethodParametersCollector
     */
    private MethodParametersCollector $parametersCollector;

    /**
     * @param EventCodeConverterInterface $eventCodeConverter
     * @param ModuleCollector $moduleCollector
     * @param MethodParametersCollector $parametersCollector
     */
    public function __construct(
        EventCodeConverterInterface $eventCodeConverter,
        ModuleCollector $moduleCollector,
        MethodParametersCollector $parametersCollector
    ) {
        $this->eventCodeConverter = $eventCodeConverter;
        $this->moduleCollector = $moduleCollector;
        $this->parametersCollector = $parametersCollector;
    }

    /**
     * Collects resource model plugin information by event code.
     *
     * @param string $eventCode
     * @return array
     * @throws CollectorException
     */
    public function collect(string $eventCode): array
    {
        $className = $this->eventCodeConverter->convertToFqcn($eventCode);
        $methodName = $this->eventCodeConverter->extractMethodName($eventCode);

        try {
            $resourceModelReflection = new ReflectionClass($className);
        } catch (ReflectionException $exception) {
            throw new CollectorException(sprintf(
                'Resource model class "%s" for event code "%s" was not found',
                $className,
                $eventCode
            ));
        }

        try {
            $methodReflection = $resourceModelReflection->getMethod($methodName);
        } catch (ReflectionException $exception) {
            throw new CollectorException(sprintf(
                'Could not find a method: "%s" in the resource model class "%s" for event code "%s"',
                $methodName,
                $className,
                $eventCode
            ));
        }

        $this->moduleCollector->collect($resourceModelReflection);

        return [
            $className => [
                [
                    'name' => $methodReflection->getName(),
                    'params' => $this->parametersCollector->collect($methodReflection)
                ]
            ]
        ];
    }
}
