<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event;

use GuzzleHttp\Psr7\Response;
use Magento\AdobeCommerceEventsClient\Api\Data\EventInterface;
use Magento\AdobeCommerceEventsClient\Event\Client;
use Magento\AdobeCommerceEventsClient\Event\EventBatchSender;
use Magento\AdobeCommerceEventsClient\Event\EventResponseHandlerInterface;
use Magento\AdobeCommerceEventsClient\Event\EventRetrieverInterface;
use Magento\AdobeCommerceEventsClient\Event\EventStatusUpdater;
use Magento\AdobeCommerceEventsClient\Event\Processor\EventDataProcessor;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\TestFramework\Inspection\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for EventBatchSender class
 */
class EventBatchSenderTest extends TestCase
{
    /**
     * @var EventBatchSender
     */
    private EventBatchSender $batchSender;

    /**
     * @var Client|MockObject
     */
    private $clientMock;

    /**
     * @var EventRetrieverInterface|MockObject
     */
    private $eventRetrieverMock;

    /**
     * @var EventDataProcessor|MockObject
     */
    private $eventDataProcessorMock;

    /**
     * @var EventStatusUpdater|MockObject
     */
    private $eventStatusUpdaterMock;

    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManagerMock;

    /**
     * @var EventResponseHandlerInterface|MockObject
     */
    private $eventResponseHandlerMock;

    /**
     * @var array
     */
    private array $events = [
        '1' => ['data' => 'dataOne', 'eventCode' => 'codeOne'],
        '2' => ['data' => 'dataTwo', 'eventCode' => 'codeTwo']
    ];

    protected function setUp(): void
    {
        $this->lockManagerMock = $this->getMockForAbstractClass(LockManagerInterface::class);
        $this->clientMock = $this->createMock(Client::class);
        $this->eventRetrieverMock = $this->getMockForAbstractClass(EventRetrieverInterface::class);
        $this->eventDataProcessorMock = $this->createMock(EventDataProcessor::class);
        $this->eventStatusUpdaterMock = $this->createMock(EventStatusUpdater::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->eventResponseHandlerMock = $this->createMock(EventResponseHandlerInterface::class);

        $this->batchSender = new EventBatchSender(
            $this->lockManagerMock,
            $this->clientMock,
            $this->eventRetrieverMock,
            $this->eventDataProcessorMock,
            $this->eventStatusUpdaterMock,
            $loggerMock,
            $this->eventResponseHandlerMock
        );
    }

    /**
     * Tests that batch send are not running if the process is locked.
     *
     * @return void
     */
    public function testEventBatchSendingIsLocked()
    {
        $this->lockManagerMock->expects(self::once())
            ->method('lock')
            ->willReturn(false);
        $this->eventRetrieverMock->expects(self::never())
            ->method('getEventsWithLimit');

        $this->batchSender->sendEventDataBatches();
    }

    /**
     * Tests successful sending of a batch of event data.
     *
     * @return void
     */
    public function testSendEventDataBatchSuccess()
    {
        $eventIds = [1, 2];
        $response = new Response(200);
        $this->lockManagerMock->expects(self::once())
            ->method('lock')
            ->willReturn(true);
        $this->eventRetrieverMock->expects(self::never())
            ->method('getEvents');
        $this->eventRetrieverMock->expects(self::once())
            ->method('getEventsWithLimit')
            ->willReturn($this->events);
        $this->eventStatusUpdaterMock->expects(self::once())
            ->method('updateStatus')
            ->withConsecutive([$eventIds, EventInterface::SENDING_STATUS], [$eventIds, EventInterface::SUCCESS_STATUS]);
        $this->eventDataProcessorMock->expects(self::once())
            ->method('execute')
            ->willReturn($this->events);
        $this->clientMock->expects(self::once())
            ->method('sendEventDataBatch')
            ->with(array_values($this->events))
            ->willReturn($response);
        $this->eventResponseHandlerMock->expects(self::once())
            ->method('handle')
            ->with($response, $eventIds);
        $this->eventStatusUpdaterMock->expects(self::never())
            ->method('updateFailure');

        $this->batchSender->sendEventDataBatches();
    }

    /**
     * Tests failed sending of a batch of event data.
     *
     * @return void
     */
    public function testSendEventDataBatchFailure()
    {
        $eventIds = [1, 2];
        $this->lockManagerMock->expects(self::once())
            ->method('lock')
            ->willReturn(true);
        $this->eventRetrieverMock->expects(self::once())
            ->method('getEventsWithLimit')
            ->willReturn($this->events);
        $this->eventStatusUpdaterMock->expects(self::once())
            ->method('updateStatus')
            ->with($eventIds, EventInterface::SENDING_STATUS);
        $this->eventDataProcessorMock->expects(self::once())
            ->method('execute')
            ->willReturn($this->events);
        $this->clientMock->expects(self::once())
            ->method('sendEventDataBatch')
            ->with(array_values($this->events))
            ->willReturn(new Response(400, [], '{"message": "msg"}'));
        $this->eventResponseHandlerMock->expects(self::once())
            ->method('handle')
            ->willThrowException(new Exception('Failed Publishing batch of events'));
        $this->eventStatusUpdaterMock->expects(self::once())
            ->method('setFailure')
            ->with($eventIds, "Failed Publishing batch of events");

        $this->batchSender->sendEventDataBatches();
    }

    /**
     * Tests failed sending of a batch of event data that is caused by issues with connecting to the events service.
     *
     * @return void
     */
    public function testSendEventDataBatchConnectionFailure()
    {
        $eventIds = [1, 2];
        $this->lockManagerMock->expects(self::once())
            ->method('lock')
            ->willReturn(true);
        $this->eventRetrieverMock->expects(self::once())
            ->method('getEventsWithLimit')
            ->willReturn($this->events);
        $this->eventStatusUpdaterMock->expects(self::once())
            ->method('updateStatus')
            ->with($eventIds, EventInterface::SENDING_STATUS);
        $this->eventDataProcessorMock->expects(self::once())
            ->method('execute')
            ->willReturn($this->events);
        $this->clientMock->expects(self::once())
            ->method('sendEventDataBatch')
            ->willThrowException(new Exception("Event publishing failed: some error"));
        $this->eventStatusUpdaterMock->expects(self::once())
            ->method('setFailure')
            ->with($eventIds, "Event publishing failed: some error");

        $this->batchSender->sendEventDataBatches();
    }
}
