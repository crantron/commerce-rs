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
use Magento\Framework\MessageQueue\PublisherInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see EventPublisher class
 */
class EventPublisherTest extends TestCase
{
    /**
     * @var EventPublisher
     */
    private EventPublisher $eventPublisher;

    public function testExecute(): void
    {
        $publisherMock = $this->getMockForAbstractClass(PublisherInterface::class);
        $this->eventPublisher = new EventPublisher($publisherMock);

        $event_topic_name = 'commerce.eventing.event.publish';
        $event_name = "com.event.test";

        $publisherMock->expects(self::once())
            ->method('publish')
            ->with($event_topic_name, $event_name);

        $this->eventPublisher->execute($event_name);
    }
}
