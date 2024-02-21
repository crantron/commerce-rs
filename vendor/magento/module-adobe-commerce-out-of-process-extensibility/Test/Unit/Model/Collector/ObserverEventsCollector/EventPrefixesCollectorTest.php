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

use ReflectionClass;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\EventDataFactory;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\NameFetcher;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\ObserverEventsCollector\EventPrefixesCollector;
use Magento\Framework\App\Utility\ReflectionClassFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SplFileInfo;
use Exception;

/**
 * Tests for the @see EventPrefixesCollector Class
 */
class EventPrefixesCollectorTest extends TestCase
{
    /**
     * @var EventDataFactory|MockObject
     */
    private $eventDataFactoryMock;

    /**
     * @var ReflectionClassFactory|MockObject
     */
    private $reflectionClassFactoryMock;

    /**
     * @var EventPrefixesCollector
     */
    private EventPrefixesCollector $eventPrefixesCollector;

    protected function setUp(): void
    {
        $this->eventDataFactoryMock = $this->createMock(EventDataFactory::class);
        $this->reflectionClassFactoryMock = $this->createMock(ReflectionClassFactory::class);

        $this->eventPrefixesCollector = new EventPrefixesCollector(
            new NameFetcher(),
            $this->eventDataFactoryMock,
            $this->reflectionClassFactoryMock
        );
    }

    /**
     * @dataProvider eventFetcherDataProvider
     * @throws Exception
     */
    public function testEventFetcher(string $filename, string $className, bool $includeBeforeEvents): void
    {
        $fileContent = file_get_contents(__DIR__ . '/_files/' . $filename);
        $expectedEventCount = $includeBeforeEvents ? 6 : 4;

        $refClassMock = $this->createMock(ReflectionClass::class);
        $refClassMock->expects(self::once())
            ->method('isSubclassOf')
            ->willReturn(true);
        $this->reflectionClassFactoryMock->expects(self::once())
            ->method('create')
            ->with($className)
            ->willReturn($refClassMock);
        $this->eventDataFactoryMock->expects(self::exactly($expectedEventCount))
            ->method('create');

        $events = $this->eventPrefixesCollector->fetchEvents(
            $this->createMock(SplFileInfo::class),
            $fileContent,
            $includeBeforeEvents
        );

        self::assertArrayHasKey('observer.sample_class_save_commit_after', $events);
        self::assertArrayHasKey('observer.sample_class_save_after', $events);
        self::assertArrayHasKey('observer.sample_class_delete_after', $events);
        self::assertArrayHasKey('observer.sample_class_delete_commit_after', $events);
        self::assertEquals($expectedEventCount, count($events));

        if ($includeBeforeEvents) {
            self::assertArrayHasKey('observer.sample_class_save_before', $events);
            self::assertArrayHasKey('observer.sample_class_delete_before', $events);
        }
    }

    /**
     * @return array
     */
    public function eventFetcherDataProvider(): array
    {
        return [
            [
                'sample_code_event_prefixes.php',
                'Magento\Framework\Module\SampleClass',
                false
            ],
            [
                'sample_code_event_prefixes.php',
                'Magento\Framework\Module\SampleClass',
                true
            ],
            [
                'sample_code_event_prefixes_double_quotes.php',
                'Magento\Framework\Module\SampleClassDoubleQuotes',
                false
            ]
        ];
    }

    /**
     * @throws Exception
     */
    public function testEventFetcherNotSubclassOfAbstractModel(): void
    {
        $fileContent = file_get_contents(__DIR__ . '/_files/sample_code_event_prefixes.php');

        $refClassMock = $this->createMock(ReflectionClass::class);
        $refClassMock->expects(self::once())
            ->method('isSubclassOf')
            ->willReturn(false);
        $this->reflectionClassFactoryMock->expects(self::once())
            ->method('create')
            ->with('Magento\Framework\Module\SampleClass')
            ->willReturn($refClassMock);
        $this->eventDataFactoryMock->expects(self::never())
            ->method('create');

        $events = $this->eventPrefixesCollector->fetchEvents($this->createMock(SplFileInfo::class), $fileContent);

        self::assertEquals(0, count($events));
    }
}
