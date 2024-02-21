<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event;

use Magento\AdobeCommerceEventsClient\Api\Data\EventInterface;
use Magento\AdobeCommerceEventsClient\Event\EventResponseHandler;
use Magento\AdobeCommerceEventsClient\Event\EventStatusUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class EventResponseHandlerTest extends TestCase
{
    /**
     * @var EventResponseHandler
     */
    private EventResponseHandler $eventResponseHandler;

    /**
     * @var EventStatusUpdater|MockObject
     */
    private $eventStatusUpdaterMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var StreamInterface|MockObject
     */
    private $streamMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->eventStatusUpdaterMock = $this->createMock(EventStatusUpdater::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->streamMock = $this->createMock(StreamInterface::class);

        $this->eventResponseHandler = new EventResponseHandler(
            $this->eventStatusUpdaterMock,
            $this->loggerMock
        );
    }

    /**
     * Tests updating success status for a batch of events
     *
     * @return void
     */
    public function testSuccessEventResponse()
    {
        $eventIds = [1, 2];
        $this->responseMock->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);
        $this->loggerMock->expects(self::once())
            ->method('info')
            ->with(
                'Event data batch of '.count($eventIds).' events was successfully published.'
            );
        $this->eventStatusUpdaterMock->expects(self::once())
            ->method('updateStatus')
            ->with($eventIds, EventInterface::SUCCESS_STATUS);
        $this->eventStatusUpdaterMock->expects(self::never())
            ->method('updateFailure');

        $this->eventResponseHandler->handle($this->responseMock, $eventIds);
    }

    /**
     * Tests updating failure status for a batch of events
     *
     * @return void
     */
    public function testFailureEventResponse()
    {
        $eventIds = [1, 2];
        $errorMessage = 'Error code: 400; reason: Server Error Invalid IMS Data';

        $this->responseMock->expects(self::exactly(2))
            ->method('getStatusCode')
            ->willReturn(400);
        $this->responseMock->expects(self::once())
            ->method('getReasonPhrase')
            ->willReturn('Server Error');
        $this->responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($this->streamMock);
        $this->streamMock->expects(self::once())
            ->method('getContents')
            ->willReturn('Invalid IMS Data');
        $this->eventStatusUpdaterMock->expects(self::never())
            ->method('updateStatus');
        $this->eventStatusUpdaterMock->expects(self::once())
            ->method('setFailure')
            ->with($eventIds, $errorMessage);

        $this->eventResponseHandler->handle($this->responseMock, $eventIds);
    }
}
