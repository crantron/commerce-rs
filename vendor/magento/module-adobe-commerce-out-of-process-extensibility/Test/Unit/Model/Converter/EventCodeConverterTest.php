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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Test\Unit\Model\Converter;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Converter\EventCodeConverter;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util\CaseConverter;
use PHPUnit\Framework\TestCase;

/**
 * Tests for EventCodeConverter class.
 */
class EventCodeConverterTest extends TestCase
{
    /**
     * @var EventCodeConverter
     */
    private EventCodeConverter $converter;

    public function setUp(): void
    {
        $this->converter = new EventCodeConverter(new CaseConverter());
    }

    /**
     * Tests conversion of an event code to a FQCN class name.
     *
     * @param string $eventCode
     * @param string $expectedFqcn
     * @return void
     * @dataProvider convertToFqcnDataProvider
     */
    public function testConvertToFqcn(string $eventCode, string $expectedFqcn): void
    {
        self::assertEquals($expectedFqcn, $this->converter->convertToFqcn($eventCode));
    }

    /**
     * @return array
     */
    public function convertToFqcnDataProvider(): array
    {
        return[
            ['plugin.magento.theme.api.design_config_repository.save', 'Magento\Theme\Api\DesignConfigRepository'],
            ['plugin.magento.rule.model.resource_model.rule.save', 'Magento\Rule\Model\ResourceModel\Rule'],
        ];
    }

    /**
     * Tests extraction of a method name from an event code.
     *
     * @param string $eventCode
     * @param string $expectedMethodName
     * @return void
     * @dataProvider extractMethodNameDataProvider
     */
    public function testExtractMethodName(string $eventCode, string $expectedMethodName): void
    {
        self::assertEquals($expectedMethodName, $this->converter->extractMethodName($eventCode));
    }

    /**
     * @return array
     */
    public function extractMethodNameDataProvider(): array
    {
        return[
            ['plugin.magento.catalog.resource_model.product.save', 'save'],
            ['magento.eav.api.attribute_repository.delete_by_id', 'deleteById'],
            ['magento.eav.api.attribute_repository.delete', 'delete'],
        ];
    }
}
