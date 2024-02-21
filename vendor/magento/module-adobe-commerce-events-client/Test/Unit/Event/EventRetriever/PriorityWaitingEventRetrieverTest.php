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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\EventRetriever;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\AdobeCommerceEventsClient\Event\EventRetriever\CollectionToArrayConverter;
use Magento\AdobeCommerceEventsClient\Event\EventRetriever\EventRetryFilter;
use Magento\AdobeCommerceEventsClient\Event\EventRetriever\PriorityWaitingEventRetriever;
use Magento\AdobeCommerceEventsClient\Model\ResourceModel\Event\Collection;
use Magento\AdobeCommerceEventsClient\Model\ResourceModel\Event\CollectionFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see PriorityWaitingEventRetriever class
 */
class PriorityWaitingEventRetrieverTest extends TestCase
{
    /**
     * @var PriorityWaitingEventRetriever
     */
    private PriorityWaitingEventRetriever $priorityWaitingEventRetriever;

    /**
     * @var CollectionFactoryInterface|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var CollectionToArrayConverter|MockObject
     */
    private $collectionToArrayConverterMock;

    /**
     * @var EventRetryFilter|MockObject
     */
    private $eventRetryFilterMock;

    /**
     * @var AbstractCollection|MockObject
     */
    private $abstractCollectionMock;

    /**
     * @var AbstractDb|MockObject
     */
    private $abstractDbMock;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createMock(CollectionFactoryInterface::class);
        $this->abstractCollectionMock = $this->createMock(AbstractCollection::class);
        $this->abstractDbMock = $this->createMock(AbstractDb::class);
        $this->collectionToArrayConverterMock = $this->createMock(CollectionToArrayConverter::class);
        $this->eventRetryFilterMock = $this->createMock(EventRetryFilter::class);
        $this->collectionMock = $this->createMock(Collection::class);

        $this->priorityWaitingEventRetriever = new PriorityWaitingEventRetriever(
            $this->collectionFactoryMock,
            $this->collectionToArrayConverterMock,
            $this->eventRetryFilterMock
        );
    }

    public function testGetEvents()
    {
        $this->collectionFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->abstractCollectionMock);
        $this->abstractCollectionMock->expects(self::exactly(2))
            ->method('addFieldToFilter')
            ->withConsecutive(['status', '0'], ['priority', '1'])
            ->willReturn($this->abstractDbMock);
        $this->collectionToArrayConverterMock->expects(self::once())
            ->method('convert')
            ->with($this->abstractCollectionMock)
            ->willReturn([]);
        self::assertEquals([], $this->priorityWaitingEventRetriever->getEvents());
    }

    public function testGetEventsWithLimit()
    {
        $limit = 20;
        $this->collectionFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->abstractCollectionMock);
        $this->abstractCollectionMock->expects(self::exactly(2))
            ->method('addFieldToFilter')
            ->withConsecutive(['status', '0'], ['priority', '1'])
            ->willReturn($this->abstractDbMock);
        $this->abstractCollectionMock->expects(self::once())
            ->method('setPageSize')
            ->with($limit)
            ->willReturn($this->collectionMock);
        $this->eventRetryFilterMock->expects(self::once())
            ->method('addRetryFilter')
            ->with($this->abstractCollectionMock)
            ->willReturn($this->abstractCollectionMock);

        self::assertEquals([], $this->priorityWaitingEventRetriever->getEventsWithLimit($limit));
    }
}
