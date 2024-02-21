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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event;

use Magento\AdobeCommerceEventsClient\Api\Data\EventInterface;
use Magento\AdobeCommerceEventsClient\Event\EventStorageCleaner;
use Magento\AdobeCommerceEventsClient\Model\ResourceModel\Event as EventResourceModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Setup\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for @see EventStorageCleaner class
 */
class EventStorageCleanerTest extends TestCase
{
    /**
     * @var EventStorageCleaner
     */
    private EventStorageCleaner $eventStorageCleaner;

    /**
     * @var EventResourceModel|MockObject
     */
    private $eventResourceModelMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $this->eventResourceModelMock = $this->createMock(EventResourceModel::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->eventStorageCleaner = new EventStorageCleaner(
            $this->eventResourceModelMock,
            $this->loggerMock,
            $this->scopeConfigMock
        );
    }

    public function testDeleteClean()
    {
        $deleteStatuses = [
            EventInterface::SUCCESS_STATUS,
            EventInterface::FAILURE_STATUS
        ];
        $CONFIG_EVENT_RETENTION = 'adobe_io_events/eventing/event_retention';

        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with($CONFIG_EVENT_RETENTION)
            ->willReturn(20);

        $deleteCutoffTime = date(
            'Y-m-d h:i:s',
            strtotime(sprintf('-%u days', 20))
        );

        $deleteConditions = [
            'status in (?)' => $deleteStatuses,
            'created_at <= ?' => $deleteCutoffTime
        ];

        $this->eventResourceModelMock->expects(self::once())
            ->method('deleteConditionally')
            ->with($deleteConditions);

        $this->eventStorageCleaner->clean();
    }

    public function testCleanFailure()
    {
        $deleteStatuses = [
            EventInterface::SUCCESS_STATUS,
            EventInterface::FAILURE_STATUS
        ];
        $CONFIG_EVENT_RETENTION = 'adobe_io_events/eventing/event_retention';

        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with($CONFIG_EVENT_RETENTION)
            ->willReturn(20);

        $deleteCutoffTime = date(
            'Y-m-d h:i:s',
            strtotime(sprintf('-%u days', 20))
        );

        $deleteConditions = [
            'status in (?)' => $deleteStatuses,
            'created_at <= ?' => $deleteCutoffTime
        ];

        $this->eventResourceModelMock->expects(self::once())
            ->method('deleteConditionally')
            ->with($deleteConditions)
            ->willThrowException(new Exception(('some error')));
        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with('Unable to delete events: some error');

        $this->eventStorageCleaner->clean();
    }
}
