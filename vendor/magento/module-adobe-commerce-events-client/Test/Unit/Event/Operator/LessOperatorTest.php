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

use Magento\AdobeCommerceEventsClient\Event\Operator\LessOperator;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see LessOperator class
 */
class LessOperatorTest extends TestCase
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
        self::assertEquals($expectedResult, (new LessOperator())->verify($ruleValue, $fieldValue));
    }

    /**
     * @return array[]
     */
    public function verifyDataProvider(): array
    {
        return [
            ['4', '2.0', true],
            ['4', '8', false],
            ['-1,', '10', false],
            ['false', 'true', false]
        ];
    }
}
