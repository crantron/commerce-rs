<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\EventStorageWriter;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Model\Event as EventModel;

/**
 * Stores event data based on event destination
 */
class DestinationBasedEventStorage implements EventStorageInterface
{
    /**
     * @param EventDestinationResolver $destinationResolver
     * @param EventStorageInterface[] $eventStorageWriters
     */
    public function __construct(
        private EventDestinationResolver $destinationResolver,
        private array $eventStorageWriters = []
    ) {
    }

    /**
     * Calls an appropriate event storage class to save event data based on event destination.
     *
     * @param Event $event
     * @param EventModel $eventModel
     * @return void
     * @throws EventStorageException
     */
    public function save(Event $event, EventModel $eventModel): void
    {
        $destination = $this->destinationResolver->resolve($event);
        if (!isset($this->eventStorageWriters[$destination])) {
            throw new EventStorageException(
                __('The event storage writer class is not registered for the destination: %1', $destination)
            );
        }

        $this->eventStorageWriters[$destination]->save($event, $eventModel);
    }
}
