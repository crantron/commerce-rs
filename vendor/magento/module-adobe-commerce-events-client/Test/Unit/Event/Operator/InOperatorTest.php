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

use Magento\AdobeCommerceEventsClient\Event\Operator\InOperator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see InOperator class
 */
class InOperatorTest extends TestCase
{
    /**
     * @param string $ruleValue
     * @param mixed $fieldValue
     * @param $expectedResult
     * @return void
     *
     * @dataProvider verifyDataProvider
     */
    public function testVerify(string $ruleValue, mixed $fieldValue, $expectedResult): void
    {
        self::assertEquals($expectedResult, (new InOperator())->verify($ruleValue, $fieldValue));
    }

    /**
     * @return array[]
     */
    public function verifyDataProvider(): array
    {
        return [
            ['1,2,3', '1', true],
            ['2,3,5', '1', false],
            ['status, test1', 'status', true],
            ['false, true, 1', 'true', false],
            ['status, done, completed', null, false],
            ['status, null, completed', 'null', false]
        ];
    }
}
