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

/**
 * Converts class to the array by given class name.
 *
 * @api
 */
interface ClassToArrayConverterInterface
{
    public const NESTED_LEVEL = 2;

    /**
     * Converts class to the array by given class name.
     *
     * @param string $className
     * @param int $nestedLevel
     * @param int $level
     * @return array
     */
    public function convert(string $className, int $nestedLevel, int $level = 1): array;
}
