<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Processor;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventList;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\CreateEventValidator;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventStorageException;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventStorageInterface;
use Magento\AdobeCommerceEventsClient\Event\Metadata\EventMetadataException;
use Magento\AdobeCommerceEventsClient\Event\Operator\OperatorException;
use Magento\AdobeCommerceEventsClient\Event\Processor\EventModelFactoryInterface;
use Magento\AdobeCommerceEventsClient\Event\Processor\EventProcessor;
use Magento\AdobeCommerceEventsClient\Event\Processor\EventToEventModelConverter;
use Magento\AdobeCommerceEventsClient\Model\Event as EventModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for @see EventProcessor class
 */
class EventProcessorTest extends TestCase
{
    /**
     * @var EventProcessor
     */
    private EventProcessor $eventProcessor;

    /**
     * @var EventList|MockObject
     */
    private EventList|MockObject $eventListMock;

    /**
     * @var CreateEventValidator|MockObject
     */
    private CreateEventValidator|MockObject $validatorMock;

    /**
     * @var EventModelFactoryInterface|MockObject
     */
    private EventModelFactoryInterface|MockObject $eventModelFactory;

    /**
     * @var EventStorageInterface|MockObject
     */
    private EventStorageInterface|MockObject $eventStorage;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->eventListMock = $this->createPartialMock(EventList::class, ['getAll']);
        $this->validatorMock = $this->createMock(CreateEventValidator::class);
        $this->eventStorage = $this->getMockForAbstractClass(EventStorageInterface::class);
        $this->eventModelFactory = $this->getMockForAbstractClass(EventModelFactoryInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->eventProcessor = new EventProcessor(
            $this->eventListMock,
            $this->eventStorage,
            $this->eventModelFactory,
            $this->validatorMock,
            $this->loggerMock
        );
    }

    public function testProcessEventSuccess(): void
    {
        $eventCode = 'observer.test_event_success';
        $eventData = ['id' => 3];
        $eventModelMock = $this->createMock(EventModel::class);

        $eventMock = $this->createEventMock($eventCode);
        $eventMock->expects(self::once())
            ->method('getName')
            ->willReturn($eventCode);
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([$eventMock]);
        $this->validatorMock->expects(self::once())
            ->method('validate')
            ->with($eventMock, $eventData)
            ->willReturn(true);
        $this->eventModelFactory->expects(self::once())
            ->method('create')
            ->with($eventMock, $eventData)
            ->willReturn($eventModelMock);
        $this->eventStorage->expects(self::once())
            ->method('save')
            ->with($eventMock, $eventModelMock);

        $this->eventProcessor->processEvent($eventCode, $eventData);
    }

    public function testProcessEventValidationFailed(): void
    {
        $eventCode = 'observer.test_event';
        $eventData = ['id' => 3];

        $eventMock = $this->createEventMock($eventCode);
        $eventMock->expects(self::once())
            ->method('getName')
            ->willReturn($eventCode);
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([$eventMock]);
        $this->validatorMock->expects(self::once())
            ->method('validate')
            ->with($eventMock, $eventData)
            ->willReturn(false);
        $this->eventModelFactory->expects(self::never())
            ->method('create');
        $this->eventStorage->expects(self::never())
            ->method('save');

        $this->eventProcessor->processEvent($eventCode, $eventData);
    }

    public function testProcessEventValidationException(): void
    {
        $eventCode = 'observer.test_event';
        $eventData = ['id' => 3];

        $eventMock = $this->createEventMock($eventCode);
        $eventMock->expects(self::once())
            ->method('getName')
            ->willReturn($eventCode);
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([$eventMock]);
        $this->validatorMock->expects(self::once())
            ->method('validate')
            ->willThrowException(new OperatorException(__('Some error')));
        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with(
                'Could not check that event "com.adobe.commerce.observer.test_event" passed the rule, error: Some error'
            );
        $this->eventModelFactory->expects(self::never())
            ->method('create');
        $this->eventStorage->expects(self::never())
            ->method('save');

        $this->eventProcessor->processEvent($eventCode, $eventData);
    }

    public function testProcessEventNotRegistered(): void
    {
        $eventCode = 'observer.test_event';
        $eventData = ['id' => 3];
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([]);
        $this->validatorMock->expects(self::never())
            ->method('validate');
        $this->eventModelFactory->expects(self::never())
            ->method('create');
        $this->eventStorage->expects(self::never())
            ->method('save');

        $this->eventProcessor->processEvent($eventCode, $eventData);
    }

    public function testProcessEventStorageException()
    {
        $eventCode = 'observer.test_event';
        $eventData = ['id' => 3];
        $eventModelMock = $this->createMock(EventModel::class);

        $eventMock = $this->createEventMock($eventCode);
        $eventMock->expects(self::once())
            ->method('getName')
            ->willReturn($eventCode);
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([$eventMock]);
        $this->validatorMock->expects(self::once())
            ->method('validate')
            ->with($eventMock, $eventData)
            ->willReturn(true);
        $this->eventModelFactory->expects(self::once())
            ->method('create')
            ->with($eventMock, $eventData)
            ->willReturn($eventModelMock);
        $this->eventStorage->expects(self::once())
            ->method('save')
            ->with($eventMock, $eventModelMock)
            ->willThrowException(new EventStorageException(__("some error")));
        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with(
                'Could not create event "com.adobe.commerce.observer.test_event": some error'
            );

        $this->eventProcessor->processEvent($eventCode, $eventData);
    }

    public function testProcessEventEventMetaDataException()
    {
        $eventCode = 'observer.test_event';
        $eventData = ['id' => 3];

        $eventMock = $this->createEventMock($eventCode);
        $eventMock->expects(self::once())
            ->method('getName')
            ->willReturn($eventCode);
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([$eventMock]);
        $this->validatorMock->expects(self::once())
            ->method('validate')
            ->with($eventMock, $eventData)
            ->willReturn(true);
        $this->eventModelFactory->expects(self::once())
            ->method('create')
            ->with($eventMock, $eventData)
            ->willThrowException(new EventMetadataException(__("some error")));
        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with(
                'Could not collect required metadata for the event '.
                '"com.adobe.commerce.observer.test_event", error: some error'
            );

        $this->eventProcessor->processEvent($eventCode, $eventData);
    }

    /**
     * @param string $eventCode
     * @return MockObject
     */
    private function createEventMock(string $eventCode): MockObject
    {
        $eventMock = $this->createMock(Event::class);
        $eventMock->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);
        $eventMock->expects(self::once())
            ->method('isBasedOn')
            ->with($eventCode)
            ->willReturn(true);

        return $eventMock;
    }
}
