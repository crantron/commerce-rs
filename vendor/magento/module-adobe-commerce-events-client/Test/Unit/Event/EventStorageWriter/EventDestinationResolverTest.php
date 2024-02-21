<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\EventStorageWriter;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventDestinationResolver;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see EventDestinationResolver
 */
class EventDestinationResolverTest extends TestCase
{
    /**
     * @param string $destination
     * @param string $expectedDestination
     * @return void
     *
     * @dataProvider resolveDataProvider
     */
    public function testResolve(string $destination, string $expectedDestination): void
    {
        $eventDestinationResolver = new EventDestinationResolver([
            'custom-destination' => 'destination',
            'custom-destination2' => 'destination',
        ]);

        $eventMock = $this->createMock(Event::class);
        $eventMock->expects(self::any())
            ->method('getDestination')
            ->willReturn($destination);

        self::assertEquals($expectedDestination, $eventDestinationResolver->resolve($eventMock));
    }

    /**
     * @return array
     */
    public function resolveDataProvider(): array
    {
        return [
            ['default', 'default'],
            ['destination', 'destination'],
            ['custom-destination', 'destination'],
            ['custom-destination2', 'destination'],
        ];
    }
}
