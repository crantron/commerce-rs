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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\EventStorageWriter;

use Magento\AdobeCommerceEventsClient\Api\Data\EventInterface;
use Magento\AdobeCommerceEventsClient\Api\EventRepositoryInterface;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventStorage;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventStorageException;
use Magento\AdobeCommerceEventsClient\Event\Queue\Publisher\EventPublisher;
use Magento\AdobeCommerceEventsClient\Event\Queue\Publisher\EventPublisherFactory;
use Magento\AdobeCommerceEventsClient\Model\Event as EventModel;
use Magento\Framework\Exception\AlreadyExistsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see EventStorage class
 */
class EventStorageTest extends TestCase
{
    /**
     * @var EventStorage
     */
    private EventStorage $eventStorage;

    /**
     * @var EventRepositoryInterface|MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EventPublisherFactory|MockObject
     */
    private $eventPublisherFactoryMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    protected function setUp(): void
    {
        $this->eventRepositoryMock = $this->getMockForAbstractClass(EventRepositoryInterface::class);
        $this->eventPublisherFactoryMock = $this->createMock(EventPublisherFactory::class);
        $this->eventMock = $this->createMock(Event::class);

        $this->eventStorage = new EventStorage($this->eventRepositoryMock, $this->eventPublisherFactoryMock);
    }

    public function testSaveSuccess()
    {
        $eventModelOne = $this->createMock(EventModel::class);
        $eventPublisherMock = $this->createMock(EventPublisher::class);
        $event = $this->getMockForAbstractClass(EventInterface::class);

        $this->eventRepositoryMock->expects(self::once())
            ->method('save')
            ->with($eventModelOne)
            ->willReturn($event);
        $this->eventMock->expects(self::once())
            ->method('isPriority')
            ->willReturn(true);
        $this->eventPublisherFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($eventPublisherMock);
        $eventModelOne->expects(self::once())
            ->method('getId')
            ->willReturn('120');
        $eventPublisherMock->expects(self::once())
            ->method('execute')
            ->with('120');

        $this->eventStorage->save($this->eventMock, $eventModelOne);
    }

    public function testSaveFailure()
    {
        $this->expectException(EventStorageException::class);
        $this->expectExceptionMessage('Unique constraint violation found');

        $eventModelOne = $this->createMock(EventModel::class);
        $this->eventRepositoryMock->expects(self::once())
            ->method('save')
            ->with($eventModelOne)
            ->willThrowException(new AlreadyExistsException(__('Unique constraint violation found')));

        $this->eventStorage->save($this->eventMock, $eventModelOne);
    }
}
