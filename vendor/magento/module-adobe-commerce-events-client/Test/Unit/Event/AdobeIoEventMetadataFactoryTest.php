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

use Magento\AdobeCommerceEventsClient\Event\AdobeIoEventMetadataFactory;
use Magento\AdobeIoEventsClient\Model\Data\EventMetadata;
use Magento\AdobeIoEventsClient\Model\Data\EventMetadataFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see AdobeIoEventMetadataFactory class
 */
class AdobeIoEventMetadataFactoryTest extends TestCase
{
    /**
     * @var AdobeIoEventMetadataFactory
     */
    private AdobeIoEventMetadataFactory $adobeIoEventMetadataFactory;

    /**
     * @var EventMetadataFactory|MockObject
     */
    private $eventMetadataFactoryMock;

    /**
     * @var EventMetadata|MockObject
     */
    private $eventMetadataMock;

    protected function setUp(): void
    {
        $this->eventMetadataFactoryMock = $this->createMock(EventMetadataFactory::class);
        $this->eventMetadataMock = $this->createMock(EventMetadata::class);

        $this->adobeIoEventMetadataFactory = new AdobeIoEventMetadataFactory($this->eventMetadataFactoryMock);
    }

    public function testGeneratePluginEventMetaData(): void
    {
        $eventCode = "plugin.eventCodeOneTest";
        $data = [
            'event_code' => $eventCode,
            'description' => 'Plugin event eventCodeOneTest',
            'label' => 'Plugin event eventCodeOneTest'
        ];

        $this->eventMetadataFactoryMock->expects(self::once())
            ->method('create')
            ->with(['data' => $data])
            ->willReturn($this->eventMetadataMock);

        self::assertInstanceOf(EventMetadata::class, $this->adobeIoEventMetadataFactory->generate($eventCode));
    }

    public function testGenerateObserverEventMetaData(): void
    {
        $eventCode = "observer.eventCodeOneTest";
        $data = [
            'event_code' => $eventCode,
            'description' => 'Observer event eventCodeOneTest',
            'label' => 'Observer event eventCodeOneTest'
        ];

        $this->eventMetadataFactoryMock->expects(self::once())
            ->method('create')
            ->with(["data" => $data])
            ->willReturn($this->eventMetadataMock);

        self::assertInstanceOf(EventMetadata::class, $this->adobeIoEventMetadataFactory->generate($eventCode));
    }
}
