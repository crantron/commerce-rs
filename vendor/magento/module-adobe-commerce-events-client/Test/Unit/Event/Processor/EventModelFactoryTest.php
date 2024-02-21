<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Processor;

use Magento\AdobeCommerceEventsClient\Event\DataFilterInterface;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventMetadataCollector;
use Magento\AdobeCommerceEventsClient\Event\Metadata\EventMetadataException;
use Magento\AdobeCommerceEventsClient\Event\Processor\EventModelFactory;
use Magento\AdobeCommerceEventsClient\Event\Tracking\TrackIdGeneratorInterface;
use Magento\AdobeCommerceEventsClient\Model\EventFactory;
use Magento\AdobeCommerceEventsClient\Model\Event as EventModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see EventModelFactory
 */
class EventModelFactoryTest extends TestCase
{
    /**
     * @var EventFactory|MockObject
     */
    private $eventFactoryMock;

    /**
     * @var DataFilterInterface|MockObject
     */
    private $dataFilterMock;

    /**
     * @var EventMetadataCollector|MockObject
     */
    private $metadataCollectorMock;

    /**
     * @var TrackIdGeneratorInterface|MockObject
     */
    private $trackIdGeneratorMock;

    /**
     * @var EventModelFactory
     */
    private EventModelFactory $eventModelFactory;

    protected function setUp(): void
    {
        $this->eventFactoryMock = $this->createMock(EventFactory::class);
        $this->dataFilterMock = $this->getMockForAbstractClass(DataFilterInterface::class);
        $this->metadataCollectorMock = $this->createMock(EventMetadataCollector::class);
        $this->trackIdGeneratorMock = $this->createMock(TrackIdGeneratorInterface::class);

        $this->eventModelFactory = new EventModelFactory(
            $this->eventFactoryMock,
            $this->dataFilterMock,
            $this->metadataCollectorMock,
            $this->trackIdGeneratorMock
        );
    }

    public function testCreate(): void
    {
        $eventMock = $this->createEventMock('observer.test_event', false);
        $eventData = [
            'id' => 3,
            'name' => 'test'
        ];
        $eventModel = $this->createMock(EventModel::class);
        $eventModel->expects(self::once())
            ->method('setEventCode')
            ->with('com.adobe.commerce.observer.test_event');
        $eventModel->expects(self::once())
            ->method('setEventData')
            ->with(['id' => 3]);
        $eventModel->expects(self::once())
            ->method('setMetadata')
            ->with(['metadata' => 'value']);
        $eventModel->expects(self::once())
            ->method('setPriority')
            ->with(0);
        $eventModel->expects(self::once())
            ->method('setTrackId')
            ->with('track-id');
        $this->eventFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($eventModel);
        $this->trackIdGeneratorMock->expects(self::once())
            ->method('generate')
            ->with($eventMock, $eventData)
            ->willReturn('track-id');
        $this->dataFilterMock->expects(self::once())
            ->method('filter')
            ->with('com.adobe.commerce.observer.test_event', $eventData)
            ->willReturn(['id' => 3]);
        $this->metadataCollectorMock->expects(self::once())
            ->method('getMetadata')
            ->willReturn(['metadata' => 'value']);

        $this->eventModelFactory->create($eventMock, $eventData);
    }

    public function testCreateWithMetadataException(): void
    {
        $this->expectException(EventMetadataException::class);

        $this->metadataCollectorMock->expects(self::once())
            ->method('getMetadata')
            ->willThrowException(new EventMetadataException(__('Some error')));

        $eventMock = $this->createEventMock('observer.test_event', true);
        $this->eventModelFactory->create($eventMock, []);
    }

    /**
     * @param string $eventName
     * @param bool $isPriority
     * @return MockObject|Event
     */
    private function createEventMock(string $eventName, bool $isPriority): MockObject|Event
    {
        $eventMock = $this->createMock(Event::class);
        $eventMock->expects(self::once())
            ->method('getName')
            ->willReturn($eventName);
        $eventMock->expects(self::any())
            ->method('isPriority')
            ->willReturn($isPriority);

        return $eventMock;
    }
}
