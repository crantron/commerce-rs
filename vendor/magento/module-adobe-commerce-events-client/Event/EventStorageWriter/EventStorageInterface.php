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
 * Interface for saving event data in storage.
 */
interface EventStorageInterface
{
    /**
     * Stores event data to the storage.
     *
     * @param Event $event
     * @param EventModel $eventModel
     * @return void
     * @throws EventStorageException
     */
    public function save(Event $event, EventModel $eventModel): void;
}
