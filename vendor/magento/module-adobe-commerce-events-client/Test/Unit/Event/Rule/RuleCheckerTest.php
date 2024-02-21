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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Rule;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\Operator\OperatorFactory;
use Magento\AdobeCommerceEventsClient\Event\Operator\OperatorInterface;
use Magento\AdobeCommerceEventsClient\Event\Rule\Rule;
use Magento\AdobeCommerceEventsClient\Event\Rule\RuleChecker;
use Magento\AdobeCommerceEventsClient\Event\Rule\RuleFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for @see RuleChecker class
 */
class RuleCheckerTest extends TestCase
{
    /**
     * @var RuleFactory|MockObject
     */
    private RuleFactory|MockObject $ruleFactoryMock;

    /**
     * @var OperatorFactory|MockObject
     */
    private OperatorFactory|MockObject $operatorFactoryMock;

    /**
     * @var Event|MockObject
     */
    private Event|MockObject $eventMock;

    /**
     * @var RuleChecker
     */
    private RuleChecker $ruleChecker;

    protected function setUp(): void
    {
        $this->ruleFactoryMock = $this->createMock(RuleFactory::class);
        $this->operatorFactoryMock = $this->createMock(OperatorFactory::class);
        $this->eventMock = $this->createMock(Event::class);

        $this->ruleChecker = new RuleChecker($this->ruleFactoryMock, $this->operatorFactoryMock);
    }

    public function testRulesAreEmpty()
    {
        $this->eventMock->expects(self::once())
            ->method('getRules')
            ->willReturn([]);
        $this->ruleFactoryMock->expects(self::never())
            ->method('create');
        $this->operatorFactoryMock->expects(self::never())
            ->method('create');

        self::assertTrue($this->ruleChecker->verify($this->eventMock, ['order_id' => 3]));
    }

    public function testFieldValueExist()
    {
        $rule = [
            'field' => 'order_id',
            'operator' => 'greaterThan',
            'value' => '2'
        ];
        $this->eventMock->expects(self::exactly(2))
            ->method('getRules')
            ->willReturn([$rule]);
        $this->ruleFactoryMock->expects(self::once())
            ->method('create')
            ->with($rule)
            ->willReturn(new Rule(...$rule));
        $operatorMock = $this->getMockForAbstractClass(OperatorInterface::class);
        $operatorMock->expects(self::once())
            ->method('verify')
            ->with('2', '3')
            ->willReturn(true);
        $this->operatorFactoryMock->expects(self::once())
            ->method('create')
            ->with($rule['operator'])
            ->willReturn($operatorMock);

        self::assertTrue($this->ruleChecker->verify($this->eventMock, [
            'order_id' => 3,
            'status' => 'pending'
        ]));
    }

    public function testNestedFieldValueExist()
    {
        $rule = [
            'field' => 'level_one.level_two.level_three.status',
            'operator' => 'equal',
            'value' => 'pending'
        ];
        $this->eventMock->expects(self::exactly(2))
            ->method('getRules')
            ->willReturn([$rule]);
        $this->ruleFactoryMock->expects(self::once())
            ->method('create')
            ->with($rule)
            ->willReturn(new Rule(...$rule));
        $operatorMock = $this->getMockForAbstractClass(OperatorInterface::class);
        $operatorMock->expects(self::once())
            ->method('verify')
            ->with('pending', 'pending_3')
            ->willReturn(true);
        $this->operatorFactoryMock->expects(self::once())
            ->method('create')
            ->with($rule['operator'])
            ->willReturn($operatorMock);

        self::assertTrue($this->ruleChecker->verify($this->eventMock, [
            'order_id' => 3,
            'status' => 'pending',
            'level_one' => [
                'level_two' => [
                    'level_three' => [
                        'payment_id' => 3,
                        'status' => 'pending_3'
                    ]
                ]
            ]
        ]));
    }

    public function testNestedFieldValueExistMultipleRules()
    {
        $ruleOne = [
            'field' => 'level_one.level_two.level_three.status',
            'operator' => 'equal',
            'value' => 'pending'
        ];
        $ruleTwo = [
            'field' => 'level_one.level_two.level_three.level_four.status',
            'operator' => 'not-equal',
            'value' => 'pending'
        ];
        $this->eventMock->expects(self::exactly(2))
            ->method('getRules')
            ->willReturn([$ruleOne, $ruleTwo]);
        $this->ruleFactoryMock->expects(self::exactly(2))
            ->method('create')
            ->withConsecutive([$ruleOne], [$ruleTwo])
            ->willReturnOnConsecutiveCalls(new Rule(...$ruleOne), new Rule(...$ruleTwo));
        $operatorMock = $this->getMockForAbstractClass(OperatorInterface::class);
        $operatorMock->expects(self::exactly(2))
            ->method('verify')
            ->withConsecutive(
                ['pending', 'pending_3'],
                ['pending', 'pending_4'],
            )
            ->willReturn(true);
        $this->operatorFactoryMock->expects(self::exactly(2))
            ->method('create')
            ->withConsecutive([$ruleOne['operator']], [$ruleTwo['operator']])
            ->willReturn($operatorMock);

        self::assertTrue($this->ruleChecker->verify($this->eventMock, [
            'order_id' => 3,
            'status' => 'pending',
            'level_one' => [
                'level_two' => [
                    'level_three' => [
                        'payment_id' => 3,
                        'status' => 'pending_3',
                        'level_four' => [
                            'status' => 'pending_4'
                        ]
                    ]
                ]
            ]
        ]));
    }

    public function testNestedFieldValueDoesNotExist()
    {
        $rule = [
            'field' => 'payment.status',
            'operator' => 'equal',
            'value' => 'pending'
        ];
        $this->eventMock->expects(self::exactly(2))
            ->method('getRules')
            ->willReturn([$rule]);
        $this->ruleFactoryMock->expects(self::once())
            ->method('create')
            ->with($rule)
            ->willReturn(new Rule(...$rule));
        $operatorMock = $this->getMockForAbstractClass(OperatorInterface::class);
        $operatorMock->expects(self::never())
            ->method('verify');
        $this->operatorFactoryMock->expects(self::once())
            ->method('create')
            ->with($rule['operator'])
            ->willReturn($operatorMock);

        self::assertFalse($this->ruleChecker->verify($this->eventMock, [
            'order_id' => 3,
            'status' => 'pending'
        ]));
    }
}
