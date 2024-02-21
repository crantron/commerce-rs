<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Test\Unit\Model\Collector\ObserverEventsCollector;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\EventDataFactory;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\NameFetcher;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\ObserverEventsCollector\DispatchMethodCollector;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SplFileInfo;

/**
 * Tests for the @see DispatchMethodCollector Class
 */
class DispatchMethodCollectorTest extends TestCase
{
    /**
     * @var NameFetcher|MockObject
     */
    private $nameFetcherMock;

    /**
     * @var EventDataFactory|MockObject
     */
    private $eventDataFactoryMock;

    /**
     * @var DispatchMethodCollector
     */
    private DispatchMethodCollector $dispatchMethodCollector;

    protected function setUp(): void
    {
        $this->nameFetcherMock = $this->createMock(NameFetcher::class);
        $this->eventDataFactoryMock = $this->createMock(EventDataFactory::class);

        $this->dispatchMethodCollector = new DispatchMethodCollector(
            $this->nameFetcherMock,
            $this->eventDataFactoryMock
        );
    }

    /**
     * @throws LocalizedException
     */
    public function testEventFetcher(): void
    {
        $fileContent = file_get_contents(__DIR__ . '/_files/sample_code_method_dispatch.php');

        $events = $this->dispatchMethodCollector->fetchEvents($this->createMock(SplFileInfo::class), $fileContent);

        self::assertEquals(4, count($events));
        self::assertArrayNotHasKey('event_single_quotes_dynamic_', $events);
        self::assertArrayNotHasKey('event_single_quotes_dynamic_multiple_lines_', $events);
        self::assertArrayHasKey('observer.event_single_quotes', $events);
        self::assertArrayHasKey('observer.event_double_quotes', $events);
        self::assertArrayHasKey('observer.event_single_quotes_multiple_lines', $events);
        self::assertArrayHasKey('observer.event_double_quotes_multiple_lines', $events);
    }
}
