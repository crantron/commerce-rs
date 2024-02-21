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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Queue\Consumer;

use Magento\AdobeCommerceEventsClient\Event\EventBatchSender;
use Magento\AdobeCommerceEventsClient\Event\Queue\Consumer\EventHandler;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for @see EventHandler class
 */
class EventHandlerTest extends TestCase
{
    /**
     * @var EventHandler
     */
    private EventHandler $eventHandler;

    /**
     * @var EventBatchSender|MockObject
     */
    private $eventBatchSenderMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->eventBatchSenderMock = $this->createMock(EventBatchSender::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->eventHandler = new EventHandler($this->eventBatchSenderMock, $this->loggerMock);
    }

    public function testExecuteSuccess()
    {
        $eventIds = [1, 2];
        $this->eventBatchSenderMock->expects(self::once())
            ->method('sendEventDataBatches');

        $this->eventHandler->execute($eventIds);
    }

    public function testExecuteFailure()
    {
        $eventIds = [1, 2];
        $exceptionMessage = 'Publishing of batch of events failed: some error';
        $this->expectExceptionMessage($exceptionMessage);

        $this->eventBatchSenderMock->expects(self::once())
            ->method('sendEventDataBatches')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

        $this->eventHandler->execute($eventIds);
    }
}
