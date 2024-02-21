<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2024 Adobe
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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Operator;

use Magento\AdobeCommerceEventsClient\Event\Operator\GreaterOperator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see GreaterOperator class
 */
class GreaterOperatorTest extends TestCase
{
    /**
     * @param string $ruleValue
     * @param $fieldValue
     * @param $expectedResult
     * @return void
     *
     * @dataProvider verifyDataProvider
     */
    public function testVerify(string $ruleValue, $fieldValue, $expectedResult): void
    {
        self::assertEquals($expectedResult, (new GreaterOperator())->verify($ruleValue, $fieldValue));
    }

    /**
     * @return array[]
     */
    public function verifyDataProvider(): array
    {
        return [
            ['categoryOne', 'categoryOne', false],
            ['categoryOn', 'categoryOne', false],
            ['33.33', 10.33, false],
            ['10.33', 33.333, true],
        ];
    }
}
