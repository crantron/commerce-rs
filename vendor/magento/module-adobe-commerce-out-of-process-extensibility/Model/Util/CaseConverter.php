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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util;

/**
 * Helper class for converting to and from camel case format.
 */
class CaseConverter
{
    /**
     * Converts a string from snake case format to camel case format.
     *
     * @param string $str
     * @return string
     */
    public function snakeCaseToCamelCase(string $str): string
    {
        return implode('', array_map('ucfirst', explode('_', $str)));
    }

    /**
     * Converts a string from camel case format to snake case format.
     *
     * CamelCaseFormat => camel_case_format
     *
     * @param string $string
     * @return string
     */
    public function camelCaseToSnakeCase(string $string): string
    {
        return implode('_', array_map('strtolower', preg_split('/(?=[A-Z])/', $string, -1, PREG_SPLIT_NO_EMPTY)));
    }
}
