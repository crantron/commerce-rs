<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Validator\EventCode;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventSubscriberInterface;
use Magento\AdobeCommerceEventsClient\Event\Validator\EventValidatorInterface;
use Magento\AdobeCommerceEventsClient\Event\Validator\ValidatorException;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\AggregatedEventListInterface;

/**
 * Validates that event code exists in the list of supported events.
 */
class EventCodeSupportedValidator implements EventValidatorInterface
{
    /**
     * @param AggregatedEventListInterface $aggregatedEventList
     */
    public function __construct(private AggregatedEventListInterface $aggregatedEventList)
    {
    }

    /**
     * Validates that event code exists in the list of supported events.
     *
     * Only applies for the plugin-type events.
     *
     * @param Event $event
     * @param bool $force
     *
     * {@inheritDoc}
     */
    public function validate(Event $event, bool $force = false): void
    {
        $eventCode = $event->getParent() ?? $event->getName();
        $events = $this->aggregatedEventList->getList();

        $eventCode = str_replace(EventSubscriberInterface::EVENT_PREFIX_COMMERCE, '', $eventCode);

        $eventCodeParts = explode('.', $eventCode, 2);

        if ($eventCodeParts[0] !== EventSubscriberInterface::EVENT_TYPE_PLUGIN) {
            return;
        }

        if (!isset($events[$eventCode])) {
            throw new ValidatorException(__(
                'Event "%1" is not defined in the list of supported events',
                $eventCode
            ));
        }
    }
}
