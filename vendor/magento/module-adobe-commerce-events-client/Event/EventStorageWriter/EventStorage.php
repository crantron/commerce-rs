<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\EventStorageWriter;

use Magento\AdobeCommerceEventsClient\Api\EventRepositoryInterface;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\Queue\Publisher\EventPublisherFactory;
use Magento\AdobeCommerceEventsClient\Model\Event as EventModel;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Stores event data to the database
 */
class EventStorage implements EventStorageInterface
{
    /**
     * @param EventRepositoryInterface $eventRepository
     * @param EventPublisherFactory $eventPublisherFactory
     */
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private EventPublisherFactory $eventPublisherFactory
    ) {
    }

    /**
     * Stores event data in the database and create message in the queue for priority events
     *
     * @param Event $event
     * @param EventModel $eventModel
     * @return void
     * @throws EventStorageException
     */
    public function save(Event $event, EventModel $eventModel): void
    {
        try {
            $this->eventRepository->save($eventModel);
            if ($event->isPriority()) {
                $publisher = $this->eventPublisherFactory->create();
                $publisher->execute($eventModel->getId());
            }
        } catch (AlreadyExistsException $exception) {
            throw new EventStorageException(__($exception->getMessage()));
        }
    }
}
