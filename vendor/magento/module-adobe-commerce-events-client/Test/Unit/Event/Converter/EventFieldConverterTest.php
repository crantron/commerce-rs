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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Converter;

use Magento\AdobeCommerceEventsClient\Event\Converter\EventFieldConverter;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\Filter\FieldFilter\Field;
use Magento\AdobeCommerceEventsClient\Event\Validator\Converter\FieldConverterValidator;
use Magento\AdobeCommerceEventsClient\Event\Validator\ValidatorException;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for @see EventFieldConverter class
 */
class EventFieldConverterTest extends TestCase
{
    /**
     * @var EventFieldConverter
     */
    private EventFieldConverter $eventFieldConverter;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var FieldConverterValidator|MockObject
     */
    private $fieldConverterValidatorMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fieldConverterValidatorMock = $this->createMock(FieldConverterValidator::class);
        $this->eventMock = $this->createMock(Event::class);
        $this->fieldMock = $this->createMock(Field::class);

        $this->eventFieldConverter = new EventFieldConverter(
            $this->objectManagerMock,
            $this->loggerMock,
            $this->fieldConverterValidatorMock
        );
    }

    public function testFieldConversionSuccess()
    {
        $this->fieldMock->expects(self::once())
            ->method('getConverterClass')
            ->willReturn('path\converter\class');
        $this->fieldMock->expects(self::once())
            ->method('getName')
            ->willReturn('status');
        $this->fieldConverterValidatorMock->expects(self::once())
            ->method('validateConverterClass')
            ->with('path\converter\class', 'status');
        $this->fieldConverterValidatorMock->expects(self::never())
            ->method('validate');

        $this->eventFieldConverter->convertField($this->fieldMock, '2', $this->eventMock);
    }

    public function testFieldConversionFailure()
    {
        $exceptionMessage = 'The converter class was not applied to the field for event "com.test.event". '.
        'Error: validation error';

        $this->fieldMock->expects(self::once())
            ->method('getConverterClass')
            ->willReturn('path\converter\class');
        $this->fieldMock->expects(self::once())
            ->method('getName')
            ->willReturn('status');
        $this->fieldConverterValidatorMock->expects(self::once())
            ->method('validateConverterClass')
            ->with('path\converter\class', 'status')
            ->willThrowException(new ValidatorException(__('validation error')));
        $this->eventMock->expects(self::once())
            ->method('getName')
            ->willReturn('com.test.event');
        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with($exceptionMessage);

        $this->eventFieldConverter->convertField($this->fieldMock, '2', $this->eventMock);
    }
}
