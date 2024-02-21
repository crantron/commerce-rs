<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\EventInfo;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventInfo;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Converter\ClassToArrayConverterInterface;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Converter\EventCodeConverterInterface;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util\ReflectionHelper;
use ReflectionException;

/**
 * Reflection utility for converting info from event objects to payload
 */
class EventInfoReflection
{
    /**
     * @param ReflectionHelper $reflectionHelper
     * @param ClassToArrayConverterInterface $classToArrayConverter
     * @param EventCodeConverterInterface $codeConverter
     */
    public function __construct(
        private ReflectionHelper $reflectionHelper,
        private ClassToArrayConverterInterface $classToArrayConverter,
        private EventCodeConverterInterface $codeConverter
    ) {
    }

    /**
     * Returns payload info for given event.
     *
     * @param Event $event
     * @param int $nestedLevel
     * @return array
     * @throws ReflectionException
     */
    public function getPayloadInfo(Event $event, int $nestedLevel = EventInfo::NESTED_LEVEL): array
    {
        $className = $this->getClassNameFromEventName($event->getName());
        $interfaceReflection = new ClassReflection($className);
        $methodName = $this->codeConverter->extractMethodName($event->getName());
        $methodReflection = $interfaceReflection->getMethod($methodName);

        if (strpos($className, 'ResourceModel') !== false) {
            $returnType = str_replace('\ResourceModel', '', $className);
        } else {
            $returnType = $this->reflectionHelper->getReturnType($methodReflection, $interfaceReflection);
        }

        if ($returnType === 'void') {
            $result = [];
        } elseif (in_array($returnType, ['bool', 'boolean'])) {
            $result = $this->getReturnBasedOnParameters($methodReflection, $nestedLevel);
        } else {
            $isArray = $this->reflectionHelper->isArray($returnType);
            if ($isArray) {
                $returnType = $this->reflectionHelper->arrayTypeToSingle($returnType);
            }

            if ($this->reflectionHelper->isSimple($returnType)) {
                $result[] = $returnType;
            } else {
                $result = $this->classToArrayConverter->convert($returnType, $nestedLevel);
            }

            if ($isArray) {
                $result = [$result];
            }
        }

        return $result;
    }

    /**
     * Returns info for observer event type
     *
     * @param string $eventClassEmitter
     * @param int $nestedLevel
     * @return array
     */
    public function getInfoForObserverEvent(
        string $eventClassEmitter,
        int $nestedLevel = EventInfo::NESTED_LEVEL
    ): array {
        return $this->classToArrayConverter->convert(
            $eventClassEmitter,
            $nestedLevel
        );
    }

    /**
     * Returns result based on method parameters in case when plugin method returns bool
     *
     * @param MethodReflection $methodReflection
     * @param int $nestedLevel
     * @return array
     */
    private function getReturnBasedOnParameters(
        MethodReflection $methodReflection,
        int $nestedLevel = EventInfo::NESTED_LEVEL
    ): array {
        $methodParams = $this->reflectionHelper->getMethodParameters($methodReflection);

        $result = [];
        foreach ($methodParams as $param) {
            if ($this->reflectionHelper->isSimple($param['type'])) {
                $result[$param['name']] = $param['type'];
            } else {
                $result[$param['name']] = $this->classToArrayConverter->convert($param['type'], $nestedLevel);
            }
        }

        return $result;
    }

    /**
     * Add Interface suffix to `api` type plugins
     *
     * @param string $eventName
     * @return string
     */
    private function getClassNameFromEventName(string $eventName): string
    {
        $className = $this->codeConverter->convertToFqcn($eventName);
        if (strpos($eventName, 'resource_model') === false && strpos($eventName, '.api.') !== false) {
            $className .= 'Interface';
        }

        return $className;
    }
}
