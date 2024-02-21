<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\EventStorageWriter;

use Magento\AdobeCommerceEventsClient\Event\Event;

/**
 * Resolves event destination
 */
class EventDestinationResolver
{
    /**
     * @param array $destinationMap
     */
    public function __construct(private array $destinationMap = [])
    {
    }

    /**
     * Resolves event destination based on destination map
     *
     * @param Event $event
     * @return string
     */
    public function resolve(Event $event): string
    {
        return $this->destinationMap[$event->getDestination()] ?? $event->getDestination();
    }
}
