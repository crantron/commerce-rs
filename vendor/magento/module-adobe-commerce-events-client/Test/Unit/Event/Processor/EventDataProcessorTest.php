<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Processor;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventBatchSender;
use Magento\AdobeCommerceEventsClient\Event\EventList;
use Magento\AdobeCommerceEventsClient\Event\Processor\EventDataProcessor;
use Magento\AdobeCommerceEventsClient\Event\Processor\EventDataProcessorInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * Test for @see EventDataProcessor class
 */
class EventDataProcessorTest extends TestCase
{
    /**
     * @var EventList|MockObject
     */
    private $eventListMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var EventDataProcessorInterface|MockObject
     */
    private $sampleProcessorClassOneMock;

    /**
     * @var EventDataProcessorInterface|MockObject
     */
    private $sampleProcessorClassTwoMock;

    /**
     * @var EventBatchSender|MockObject
     */
    private $sampleProcessorClassThreeMock;

    /**
     * @var EventDataProcessor
     */
    private EventDataProcessor $eventDataProcessor;

    protected function setUp(): void
    {
        $this->eventListMock = $this->createMock(EventList::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->eventMock = $this->createMock(Event::class);
        $this->sampleProcessorClassOneMock = $this->getMockForAbstractClass(EventDataProcessorInterface::class);
        $this->sampleProcessorClassTwoMock = $this->getMockForAbstractClass(EventDataProcessorInterface::class);
        $this->sampleProcessorClassThreeMock = $this->createMock(EventBatchSender::class);

        $this->eventDataProcessor = new EventDataProcessor(
            $this->eventListMock,
            $this->loggerMock,
            $this->objectManagerMock
        );
    }

    /**
     * Tests execution of two valid processors.
     *
     * @return void
     */
    public function testEventDataProcessorSuccess(): void
    {
        $data = $this->getEventProcessorMockData();
        $this->eventListMock->expects(self::once())
            ->method('get')
            ->with($data['waitingEventsData'][0]['eventCode'])
            ->willReturn($this->eventMock);
        $this->eventMock->expects(self::exactly(2))
            ->method('getProcessors')
            ->willReturn($data['processorsData']);
        $this->objectManagerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($this->sampleProcessorClassOneMock, $this->sampleProcessorClassTwoMock);
        $this->sampleProcessorClassOneMock->expects(self::exactly(1))
            ->method('process')
            ->willReturn($data['eventData']);
        $this->sampleProcessorClassTwoMock->expects(self::exactly(1))
            ->method('process')
            ->willReturn(array_merge($data['eventData'][0], $data['eventData'][1]));

        self::assertEquals($data['expectedData'], $this->eventDataProcessor->execute($data['waitingEventsData']));
    }

    /**
     * Tests exception when processor class is not found.
     *
     * @return void
     */
    public function testProcessorClassNotFoundException(): void
    {
        $data = $this->getEventProcessorMockData();
        $this->eventListMock->expects(self::once())
            ->method('get')
            ->with($data['waitingEventsData'][0]['eventCode'])
            ->willReturn($this->eventMock);
        $this->eventMock->expects(self::exactly(2))
            ->method('getProcessors')
            ->willReturn($data['processorsData']);
        $this->objectManagerMock->expects(self::exactly(2))
            ->method('get')
            ->willThrowException(new ReflectionException());
        $this->sampleProcessorClassOneMock->expects(self::never())
            ->method('process')
            ->willReturn($data['eventData']);
        $this->loggerMock->expects(self::exactly(2))
            ->method('error')
            ->with($this->stringContains('The processor class has not been applied.'));

        $this->eventDataProcessor->execute($data['waitingEventsData']);
    }

    /**
     * Tests exception when processor class does not implement EventDataProcessorInterface.
     *
     * @return void
     */
    public function testProcessorNoInterfaceImplementationException(): void
    {
        $data = $this->getEventProcessorMockData();
        $this->eventListMock->expects(self::once())
            ->method('get')
            ->with($data['waitingEventsData'][0]['eventCode'])
            ->willReturn($this->eventMock);
        $this->eventMock->expects(self::exactly(2))
            ->method('getProcessors')
            ->willReturn([
                [
                    'class' => 'processorClass1',
                    'priority' => '30'
                ],
                [
                    'class' => 'processorClass2',
                    'priority' => '20'
                ],
                [
                    'class' => 'processorClass3',
                    'priority' => '10'
                ],
            ]);
        $this->objectManagerMock->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(
                ['processorClass3'],
                ['processorClass2'],
                ['processorClass1'],
            )
            ->willReturnOnConsecutiveCalls(
                $this->sampleProcessorClassThreeMock,
                $this->sampleProcessorClassTwoMock,
                $this->sampleProcessorClassOneMock,
            );
        $this->loggerMock->expects(self::exactly(1))
            ->method('error')
            ->with(sprintf(
                'The processor class has not been applied. Error: Processor class "processorClass3"' .
                ' for Event "%s" does not implement EventDataProcessorInterface',
                $data['waitingEventsData'][0]['eventCode']
            ));

        $this->eventDataProcessor->execute($data['waitingEventsData']);
    }

    /**
     * @return array[]
     */
    private function getEventProcessorMockData(): array
    {
        return [
                'waitingEventsData' => [
                    [
                        'eventCode' => 'observer.test_event_processor',
                        'eventData' => ['sku' => 40],
                        'metaData' => ['key_one' => 'value_one']
                    ]
                ],
                'processorsData' => [
                    [
                        'class' => 'processorClass1',
                        'priority' => 20
                    ],
                    [
                        'class' => 'processorClass2',
                        'priority' => 10
                    ]
                 ],
                'eventData' => [
                    [
                        'sku' => 40,
                        'test1' => 'Value1'
                    ],
                    [
                        'price' => 50,
                        'test2' => 'Value2'
                    ]
                ],
                'expectedData' => [
                    [
                        'eventCode' => 'observer.test_event_processor',
                        'eventData' => ['sku' => 40,'test1' => 'Value1','price' => 50,'test2' => 'Value2'],
                        'metaData' => ['key_one' => 'value_one']
                    ]
                ]
        ];
    }
}
