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
 * Used for converting class names to event codes and vise versa.
 *
 * @api
 */
interface EventCodeConverterInterface
{
    /**
     * Convert event code to FQCN class name.
     *
     * @param string $eventCode
     * @return string
     */
    public function convertToFqcn(string $eventCode): string;

    /**
     * Converts class name to the event name.
     *
     * @param string $className
     * @param string $methodName
     * @return string
     */
    public function convertToEventName(string $className, string $methodName): string;

    /**
     * Extract method name from event code.
     *
     * @param string $eventCode
     * @return string
     */
    public function extractMethodName(string $eventCode): string;
}
