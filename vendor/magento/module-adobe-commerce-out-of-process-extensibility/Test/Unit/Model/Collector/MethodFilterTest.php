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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Test\Unit\Model\Collector;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\MethodFilter;
use Magento\Framework\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the @see MethodFilter Class
 */
class MethodFilterTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testIsExclude(): void
    {
        $methodFilter = new MethodFilter([
            'method1',
            'method2',
            '/^get.*/',
        ]);

        self::assertTrue($methodFilter->isExcluded('method1'));
        self::assertTrue($methodFilter->isExcluded('method2'));
        self::assertTrue($methodFilter->isExcluded('getName'));
        self::assertFalse($methodFilter->isExcluded('nameGet'));
        self::assertFalse($methodFilter->isExcluded('method'));
        self::assertFalse($methodFilter->isExcluded('method3'));
    }

    public function testIsExcludeWrongExcludeMethods(): void
    {
        self::expectException(InvalidArgumentException::class);
        $methodFilter = new MethodFilter([
            ['wrong_type_of_method_parameter']
        ]);

        self::assertTrue($methodFilter->isExcluded('method1'));
    }
}
