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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Setup;

use Magento\AdobeCommerceEventsClient\Event\Config;
use Magento\AdobeCommerceEventsClient\Event\Synchronizer\AdobeIoEventMetadataSynchronizer;
use Magento\AdobeCommerceEventsClient\Event\Synchronizer\SynchronizerException;
use Magento\AdobeCommerceEventsClient\Setup\RecurringData;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RecurringDataTest extends TestCase
{
    /**
     * @var RecurringData
     */
    private RecurringData $recurringData;

    /**
     * @var AdobeIoEventMetadataSynchronizer
     */
    private $eventMetadataSynchronizerMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ModuleDataSetupInterface|MockObject
     */
    private $moduleDataMock;

    /**
     * @var ModuleContextInterface|MockObject
     */
    private $moduleContextMock;

    protected function setUp(): void
    {
        $this->eventMetadataSynchronizerMock = $this->createMock(AdobeIoEventMetadataSynchronizer::class);
        $this->configMock = $this->createMock(Config::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->moduleDataMock = $this->createMock(ModuleDataSetupInterface::class);
        $this->moduleContextMock = $this->createMock(ModuleContextInterface::class);

        $this->recurringData = new RecurringData(
            $this->eventMetadataSynchronizerMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    public function testCannotRegisterEventMetaData()
    {
        $this->configMock->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->eventMetadataSynchronizerMock->expects(self::once())
            ->method('run')
            ->willThrowException(new SynchronizerException(__('Error message')));
        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with('Cannot register events metadata during setup:upgrade. Error message');

        $this->recurringData->install($this->moduleDataMock, $this->moduleContextMock);
    }

    public function testCannotRegisterEventingNotEnabled()
    {
        $this->configMock->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->eventMetadataSynchronizerMock->expects(self::never())
            ->method('run');
        $this->loggerMock->expects(self::never())
            ->method('info');

        $this->recurringData->install($this->moduleDataMock, $this->moduleContextMock);
    }

    public function testRegisterEventMetaData()
    {
        $this->configMock->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->eventMetadataSynchronizerMock->expects(self::once())
            ->method('run')
            ->willReturn(['testEventOne', 'testEventTwo']);
        $this->loggerMock->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(['testEventOne'], ['testEventTwo']);

        $this->recurringData->install($this->moduleDataMock, $this->moduleContextMock);
    }
}
