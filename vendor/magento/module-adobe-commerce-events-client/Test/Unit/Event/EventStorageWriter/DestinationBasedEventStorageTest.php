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

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\DestinationBasedEventStorage;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventDestinationResolver;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventStorageException;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventStorageInterface;
use Magento\AdobeCommerceEventsClient\Model\Event as EventModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see DestinationBasedEventStorage class
 */
class DestinationBasedEventStorageTest extends TestCase
{
    /**
     * @var EventDestinationResolver|MockObject
     */
    private $destinationResolverMock;

    /**
     * @var EventStorageInterface|MockObject
     */
    private $eventStorageInterfaceMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    protected function setUp(): void
    {
        $this->destinationResolverMock = $this->createMock(EventDestinationResolver::class);
        $this->eventStorageInterfaceMock = $this->getMockForAbstractClass(EventStorageInterface::class);
        $this->eventMock = $this->createMock(Event::class);
    }

    public function testDestinationSaveSuccess()
    {
        $eventModelOne = $this->createMock(EventModel::class);
        $destination = 'commerce-destintation';
        $this->destinationResolverMock->expects(self::once())
            ->method('resolve')
            ->with($this->eventMock)
            ->willReturn($destination);
        $destinationBasedEventStorage = new DestinationBasedEventStorage(
            $this->destinationResolverMock,
            [$destination => $this->eventStorageInterfaceMock]
        );

        $destinationBasedEventStorage->save($this->eventMock, $eventModelOne);
    }

    public function testDestinationSaveFailure()
    {
        $this->expectException(EventStorageException::class);
        $this->expectExceptionMessage('The event storage writer class is not registered for the '.
            'destination: commerce-destination');

        $eventModelOne = $this->createMock(EventModel::class);
        $destination = 'commerce-destination';
        $this->destinationResolverMock->expects(self::once())
            ->method('resolve')
            ->with($this->eventMock)
            ->willReturn($destination);
        $destinationBasedEventStorage = new DestinationBasedEventStorage(
            $this->destinationResolverMock,
            ['test-destination' => $this->eventStorageInterfaceMock]
        );

        $destinationBasedEventStorage->save($this->eventMock, $eventModelOne);
    }
}
