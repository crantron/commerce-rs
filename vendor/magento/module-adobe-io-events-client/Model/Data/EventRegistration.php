<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model\Data;

use Magento\Framework\DataObject;

/**
 * Event Registration Data Object
 */
class EventRegistration extends DataObject
{
    public const NAME = 'name';
    public const ID = 'registration_id';
    public const ENABLED = 'enabled';
    public const EVENTS = 'events';
    public const EVENTS_OF_INTEREST = 'events_of_interest';

    /**
     * Returns Event Registration Name
     *
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    /**
     * Returns Event Registration Id
     *
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->getData(self::ID);
    }

    /**
     * Returns true if Event Registration is enabled otherwise false
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->getData(self::ENABLED);
    }

    /**
     * Returns list of registration events
     *
     * @return EventMetadata[]
     */
    public function getEvents(): array
    {
        return $this->getData(self::EVENTS);
    }
}
