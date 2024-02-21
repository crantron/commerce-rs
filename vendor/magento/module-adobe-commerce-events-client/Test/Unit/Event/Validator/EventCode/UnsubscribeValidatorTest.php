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

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Validator\EventCode;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventList;
use Magento\AdobeCommerceEventsClient\Event\EventSubscriberInterface;
use Magento\AdobeCommerceEventsClient\Event\Validator\EventCode\UnsubscribeValidator;
use Magento\AdobeCommerceEventsClient\Event\Validator\ValidatorException;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see UnsubscribeValidator class
 */
class UnsubscribeValidatorTest extends TestCase
{
    /**
     * @var UnsubscribeValidator
     */
    private UnsubscribeValidator $unsubscribeValidator;

    /**
     * @var EventList|MockObject
     */
    private $eventListMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    protected function setUp(): void
    {
        $this->eventListMock = $this->createMock(EventList::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->eventMock = $this->createMock(Event::class);

        $this->unsubscribeValidator = new UnsubscribeValidator($this->eventListMock, $this->deploymentConfigMock);
    }

    public function testEventUnsubscribeSuccess()
    {
        $this->deploymentConfigMock->expects(self::once())
            ->method('get')
            ->with(EventSubscriberInterface::IO_EVENTS_CONFIG_NAME)
            ->willReturn($this->getEventData());
        $this->eventMock->expects(self::once())
            ->method('getName')
            ->willReturn('plugin.some_event_code');
        $eventMock = $this->createMock(Event::class);
        $eventMock->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([
                'plugin.some_event_code' => $eventMock
            ]);

        $this->unsubscribeValidator->validate($this->eventMock, false);
    }

    public function testEventNotInConfigFile()
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Cannot unsubscribe "plugin.some_event_code_one" because it is not registered '.
            'in the "io_events" section of the config.php file.');

        $this->deploymentConfigMock->expects(self::once())
            ->method('get')
            ->with(EventSubscriberInterface::IO_EVENTS_CONFIG_NAME)
            ->willReturn($this->getEventData());
        $this->eventMock->expects(self::once())
            ->method('getName')
            ->willReturn('plugin.some_event_code_one');

        $this->unsubscribeValidator->validate($this->eventMock, false);
    }

    public function testEventNotRegistered()
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('The "plugin.some_event_code" event is not registered. '.
            'You cannot unsubscribe from it.');

        $this->deploymentConfigMock->expects(self::once())
            ->method('get')
            ->with(EventSubscriberInterface::IO_EVENTS_CONFIG_NAME)
            ->willReturn($this->getEventData());
        $this->eventMock->expects(self::once())
            ->method('getName')
            ->willReturn('plugin.some_event_code');
        $eventMock = $this->createMock(Event::class);
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([
                'plugin.some_event_code_one' => $eventMock,
                'plugin.some_event_code_two' => $eventMock
            ]);

        $this->unsubscribeValidator->validate($this->eventMock, false);
    }

    public function testEventAlreadyUnsubscribed()
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('The "plugin.some_event_code" event has already been unsubscribed.');

        $this->deploymentConfigMock->expects(self::once())
            ->method('get')
            ->with(EventSubscriberInterface::IO_EVENTS_CONFIG_NAME)
            ->willReturn($this->getEventData());
        $this->eventMock->expects(self::once())
            ->method('getName')
            ->willReturn('plugin.some_event_code');
        $eventMock = $this->createMock(Event::class);
        $eventMock->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->eventListMock->expects(self::once())
            ->method('getAll')
            ->willReturn([
                'plugin.some_event_code' => $eventMock
            ]);

        $this->unsubscribeValidator->validate($this->eventMock, false);
    }

    private function getEventData() : array
    {
        return [
            'plugin.some_event_code' => [
                'fields' => ['id'],
                'enabled' => 1
            ],
            'observer.some_event_code' => [
                'fields' => ['name'],
                'enabled' => 0,
                'priority' => 1
            ]
        ];
    }
}
