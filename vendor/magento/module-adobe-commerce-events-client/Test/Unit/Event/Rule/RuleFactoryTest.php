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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Rule;

use Magento\AdobeCommerceEventsClient\Event\Rule\RuleFactory;
use Magento\AdobeCommerceEventsClient\Event\Rule\RuleInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see RuleFactory class
 */
class RuleFactoryTest extends TestCase
{
    /**
     * @var RuleFactory
     */
    private RuleFactory $ruleFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var RuleInterface|MockObject
     */
    private $ruleMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->ruleMock = $this->getMockForAbstractClass(RuleInterface::class);

        $this->ruleFactory= new RuleFactory($this->objectManagerMock);
    }

    public function testCreateRuleFactory()
    {
        $ruleData = [
            'field' => 'order_id',
            'operator' => 'greaterThan',
            'value' => '2'
        ];

        $this->objectManagerMock->expects(self::once())
            ->method('create')
            ->with(RuleInterface::class, $ruleData)
            ->willReturn($this->ruleMock);

        self::assertInstanceOf(RuleInterface::class, $this->ruleFactory->create($ruleData));
    }
}
