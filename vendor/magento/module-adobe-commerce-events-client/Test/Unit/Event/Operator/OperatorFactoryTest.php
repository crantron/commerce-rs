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

use Magento\AdobeCommerceEventsClient\Event\Operator\OperatorException;
use Magento\AdobeCommerceEventsClient\Event\Operator\OperatorFactory;
use Magento\AdobeCommerceEventsClient\Event\Operator\OperatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see OperatorFactory class
 */
class OperatorFactoryTest extends TestCase
{
    public function testCreate()
    {
        $operatorMock = $this->createMock(OperatorInterface::class);
        $operatorFactory = new  OperatorFactory([
            'in' => $operatorMock
        ]);

        self::assertEquals($operatorMock, $operatorFactory->create('in'));
    }

    public function testCreateOperatorNotExists()
    {
        $this->expectException(OperatorException::class);
        $this->expectExceptionMessage('Operator notEqual is not registered');

        $operatorMock = $this->createMock(OperatorInterface::class);
        $operatorFactory = new OperatorFactory([
            'equal' => $operatorMock
        ]);

        $operatorFactory->create('notEqual');
    }

    public function testGetOperatorNames()
    {
        $operatorMock = $this->createMock(OperatorInterface::class);
        $validOperatorNames = ['in', 'equal', 'greaterThan', 'lessThan', 'regex'];

        $operatorFactory = new OperatorFactory(
            array_combine($validOperatorNames, array_fill(0, count($validOperatorNames), $operatorMock))
        );

        $operatorNames = $operatorFactory->getOperatorNames();

        self::assertEquals($validOperatorNames, $operatorNames);
    }

    public function testGetOperatorNamesInvalid()
    {
        $operatorMock = $this->createMock(OperatorInterface::class);
        $validOperatorNames = ['in', 'equal', 'greaterThan', 'lessThan', 'regex'];

        $operatorFactory = new  OperatorFactory([
            'test1' => $operatorMock,
            'test2' => $operatorMock,
            'test3' => $operatorMock,
            'test4' => $operatorMock,
            'regex' => $operatorMock
        ]);
        $operatorNames = $operatorFactory->getOperatorNames();

        self::assertNotEquals($validOperatorNames, $operatorNames);
    }
}
