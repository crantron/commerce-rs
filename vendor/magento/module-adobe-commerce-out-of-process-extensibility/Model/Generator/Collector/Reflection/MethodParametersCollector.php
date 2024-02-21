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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\Collector\Reflection;

use ReflectionMethod;

/**
 * Collects a list of parameters for the given method
 */
class MethodParametersCollector
{
    /**
     * Collects a list of parameters with their type and defaults for the given method
     *
     * @param ReflectionMethod $reflectionMethod
     * @return array
     */
    public function collect(ReflectionMethod $reflectionMethod): array
    {
        $params = [];

        foreach ($reflectionMethod->getParameters() as $param) {
            $methodParams = [
                'type' => $param->getType()?->getName(),
                'name' => $param->getName(),
                'isDefaultValueAvailable' => false,
            ];

            if ($param->isDefaultValueAvailable()) {
                $methodParams['isDefaultValueAvailable'] = true;
                $methodParams['defaultValue'] = $this->formatDefaultValue($param->getDefaultValue());
            }

            $params[] = $methodParams;
        }

        return $params;
    }

    /**
     * Convert default value to appropriate string format
     *
     * @param mixed $defaultValue
     * @return string
     */
    private function formatDefaultValue($defaultValue): string
    {
        if (is_string($defaultValue)) {
            return '\'' . $defaultValue . '\'';
        }

        if (is_array($defaultValue)) {
            return '[' . implode(', ', $defaultValue) . ']';
        }

        if ($defaultValue === null) {
            return 'null';
        }

        if (is_bool($defaultValue)) {
            return $defaultValue ? 'true' : 'false';
        }

        return (string)$defaultValue;
    }
}
