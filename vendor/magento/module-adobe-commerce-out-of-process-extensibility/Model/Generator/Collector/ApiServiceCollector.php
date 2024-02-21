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
 * Collects Api interface plugin information by event code.
 */
class ApiServiceCollector implements CollectorInterface
{
    /**
     * @var EventCodeConverterInterface
     */
    private EventCodeConverterInterface $eventCodeConvertor;

    /**
     * @var ModuleCollector
     */
    private ModuleCollector $moduleCollector;

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
        $this->eventCodeConvertor = $eventCodeConverter;
        $this->moduleCollector = $moduleCollector;
        $this->parametersCollector = $parametersCollector;
    }

    /**
     * Collects Api interface plugin information by event code.
     *
     * @param string $eventCode
     * @return array
     * @throws CollectorException
     */
    public function collect(string $eventCode): array
    {
        $interfaceName = $this->eventCodeConvertor->convertToFqcn($eventCode) . 'Interface';
        $methodName = $this->eventCodeConvertor->extractMethodName($eventCode);

        try {
            $interfaceReflection = new ReflectionClass($interfaceName);
        } catch (ReflectionException $exception) {
            throw new CollectorException(sprintf(
                'Interface "%s" for event code "%s" was not found',
                $interfaceName,
                $eventCode
            ));
        }

        try {
            $methodReflection = $interfaceReflection->getMethod($methodName);
        } catch (ReflectionException $exception) {
            throw new CollectorException(sprintf(
                'Could not find a method: "%s" in the api interface "%s" for event code "%s"',
                $methodName,
                $interfaceName,
                $eventCode
            ));
        }

        $this->moduleCollector->collect($interfaceReflection);

        return [
            $interfaceName => [
                [
                    'name' => $methodName,
                    'params' => $this->parametersCollector->collect($methodReflection)
                ]
            ]
        ];
    }
}
