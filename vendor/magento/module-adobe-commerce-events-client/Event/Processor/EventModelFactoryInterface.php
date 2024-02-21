<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Processor;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventInitializationException;
use Magento\AdobeCommerceEventsClient\Event\Metadata\EventMetadataException;
use Magento\AdobeCommerceEventsClient\Model\Event as EventModel;
use Magento\AdobeCommerceEventsClient\Model\EventException;

/**
 * Interface for create event model objects
 */
interface EventModelFactoryInterface
{
    /**
     * Creates event model object based on event configuration and event data.
     *
     * @param Event $event
     * @param array $eventData
     * @return EventModel
     * @throws EventException
     * @throws EventInitializationException
     * @throws EventMetadataException
     */
    public function create(Event $event, array $eventData): EventModel;
}
