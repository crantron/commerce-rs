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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Queue\Publisher;

use Magento\AdobeCommerceEventsClient\Event\Queue\Publisher\EventPublisher;
use Magento\AdobeCommerceEventsClient\Event\Queue\Publisher\EventPublisherFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see EventPublisherFactory class
 */
class EventPublisherFactoryTest extends TestCase
{
    /**
     * @var EventPublisherFactory
     */
    private EventPublisherFactory $eventPublisherFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var EventPublisher|MockObject
     */
    private $eventPublisherMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->eventPublisherMock = $this->createMock(EventPublisher::class);

        $this->eventPublisherFactory = new EventPublisherFactory($this->objectManagerMock);
    }

    public function testCreateEventClass(): void
    {
        $this->objectManagerMock->expects(self::once())
            ->method('get')
            ->with(EventPublisher::class)
            ->willReturn($this->eventPublisherMock);

        self::assertInstanceOf(EventPublisher::class, $this->eventPublisherFactory->create());
    }
}
