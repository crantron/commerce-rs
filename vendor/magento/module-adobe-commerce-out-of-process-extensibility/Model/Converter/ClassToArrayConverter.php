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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Converter;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util\ReflectionHelper;
use ReflectionException;

/**
 * Converts class to the array by converting getter methods to appropriate values
 */
class ClassToArrayConverter implements ClassToArrayConverterInterface
{
    /**
     * @param ReflectionHelper $reflectionHelper
     */
    public function __construct(private ReflectionHelper $reflectionHelper)
    {
    }

    /**
     * Converts class to the array by converting its `getter` and `is*` methods to appropriate values
     *
     * @param string $className
     * @param int $nestedLevel
     * @param int $level
     * @return array
     */
    public function convert(string $className, int $nestedLevel = self::NESTED_LEVEL, int $level = 1): array
    {
        $result = [];

        try {
            $objectProperties = $this->reflectionHelper->getObjectProperties($className);
        } catch (ReflectionException $e) {
            return [$className];
        }

        foreach ($objectProperties as $prop) {
            if ($this->reflectionHelper->isSimple($prop['type']) || $level >= $nestedLevel) {
                $result[$prop['name']] = $prop['type'];
                continue;
            }

            if (!$this->reflectionHelper->isArray($prop['type'])) {
                $result[$prop['name']] = $this->convert($prop['type'], $nestedLevel, $level + 1);
            } else {
                $result[$prop['name']] = [
                    $this->convert($this->reflectionHelper->arrayTypeToSingle($prop['type']), $nestedLevel, $level + 1)
                ];
            }
        }

        return $result;
    }
}
