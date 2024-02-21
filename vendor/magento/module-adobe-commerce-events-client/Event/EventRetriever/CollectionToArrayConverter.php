<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\EventRetriever;

use Magento\AdobeCommerceEventsClient\Api\Data\EventInterface;
use Magento\AdobeCommerceEventsClient\Event\EventStatusUpdater;
use Magento\AdobeCommerceEventsClient\Model\Event;
use Magento\AdobeCommerceEventsClient\Model\EventException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Converts event collection to appropriate array.
 */
class CollectionToArrayConverter
{
    /**
     * @var EventStatusUpdater
     */
    private EventStatusUpdater $statusUpdater;

    /**
     * @param EventStatusUpdater $statusUpdater
     */
    public function __construct(EventStatusUpdater $statusUpdater)
    {
        $this->statusUpdater = $statusUpdater;
    }

    /**
     * Converts event collection to appropriate array of events.
     *
     * Updates event status to failure in case if not possible to convert event data.
     *
     * @param AbstractCollection $collection
     * @return array
     * @throws LocalizedException
     */
    public function convert(AbstractCollection $collection): array
    {
        $events = [];

        /** @var Event $event */
        foreach ($collection->getItems() as $event) {
            try {
                $events[$event->getId()] = [
                    'eventCode' => $event->getEventCode(),
                    'eventData' => $event->getEventData(),
                    'metadata' => $event->getMetadata(),
                    'eventTrackId' => $event->getTrackId(),
                    'eventId' => $event->getId()
                ];
            } catch (EventException $e) {
                $this->statusUpdater->updateStatus(
                    [$event->getId()],
                    EventInterface::FAILURE_STATUS,
                    'Failed to process event data: ' . $e->getMessage()
                );
            }
        }

        return $events;
    }
}
