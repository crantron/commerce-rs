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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Tracking;

use Magento\AdobeCommerceEventsClient\Event\Config;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\Tracking\TrackIdGenerator;
use Magento\Framework\Math\Random;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see TrackIdGenerator
 */
class TrackIdGeneratorTest extends TestCase
{
    /**
     * @var TrackIdGenerator
     */
    private TrackIdGenerator $trackIdGenerator;

    /**
     * @var Config|MockObject
     */
    private Config|MockObject $configMock;

    /**
     * @var Random|MockObject
     */
    private Random|MockObject $randomMock;

    /**
     * @var Json|MockObject
     */
    private Json|MockObject $jsonMock;

    protected function setUp(): void
    {
        $this->randomMock = $this->createMock(Random::class);
        $this->configMock = $this->createMock(Config::class);
        $this->jsonMock = $this->createMock(Json::class);

        $this->trackIdGenerator = new TrackIdGenerator(
            $this->configMock,
            $this->randomMock,
            $this->jsonMock,
        );
    }

    public function testHashGenerated()
    {
        $eventMock = $this->createMock(Event::class);
        $eventData = ['product' => 'test'];

        $this->configMock->expects(self::once())
            ->method('getEnvironmentId')
            ->willReturn('envId');
        $this->configMock->expects(self::once())
            ->method('getMerchantId')
            ->willReturn('merchantId');
        $this->jsonMock->expects(self::once())
            ->method('serialize')
            ->with($eventData)
            ->willReturn('serialized_string');
        $this->randomMock->expects(self::once())
            ->method('getRandomString')
            ->willReturn('random_string');

        $hash = $this->trackIdGenerator->generate($eventMock, $eventData);
        self::assertEquals(64, strlen($hash));
    }
}
