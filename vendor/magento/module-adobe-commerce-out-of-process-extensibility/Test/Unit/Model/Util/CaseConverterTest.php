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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Test\Unit\Model\Util;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util\CaseConverter;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the @see CaseConverter Class
 */
class CaseConverterTest extends TestCase
{
    /**
     * @var CaseConverter
     */
    private CaseConverter $caseConverter;

    public function setUp(): void
    {
        $this->caseConverter = new CaseConverter();
    }

    /**
     * @dataProvider caseConversionProvider
     * @param string $snakeCase
     * @param string $camelCase
     * @return void
     */
    public function testSnakeCaseToCamelCase(string $snakeCase, string $camelCase): void
    {
        $this->assertEquals($camelCase, $this->caseConverter->snakeCaseToCamelCase($snakeCase));
    }

    /**
     * @dataProvider caseConversionProvider
     * @param string $snakeCase
     * @param string $camelCase
     * @return void
     */
    public function testCamelCaseToSnakeCase(string $snakeCase, string $camelCase): void
    {
        $this->assertEquals($snakeCase, $this->caseConverter->camelCaseToSnakeCase($camelCase));
    }

    /**
     * @return array
     */
    public function caseConversionProvider(): array
    {
        return [
            [
                'snakeCase' => 'register',
                'camelCase' => 'Register'
            ],
            [
                'snakeCase' => 'get_data',
                'camelCase' => 'GetData'
            ],
            [
                'snakeCase' => 'get_class_property',
                'camelCase' => 'GetClassProperty'
            ],
            [
                'snakeCase' => 'get_number1_property',
                'camelCase' => 'GetNumber1Property'
            ]
        ];
    }
}
