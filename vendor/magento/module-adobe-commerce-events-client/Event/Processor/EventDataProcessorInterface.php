<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Processor;

use Magento\AdobeCommerceEventsClient\Event\Event;

/**
 * Interface for processor data object
 */
interface EventDataProcessorInterface
{
    /**
     * Creates an updated eventData array before sending the payload to Eventing service
     *
     * @param Event $event
     * @param array $eventData
     * @return array
     */
    public function process(Event $event, array $eventData): array;
}
