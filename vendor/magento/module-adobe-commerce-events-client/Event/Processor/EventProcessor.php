<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Processor;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventInitializationException;
use Magento\AdobeCommerceEventsClient\Event\EventList;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\CreateEventValidator;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventStorageException;
use Magento\AdobeCommerceEventsClient\Event\EventStorageWriter\EventStorageInterface;
use Magento\AdobeCommerceEventsClient\Event\EventSubscriberInterface;
use Magento\AdobeCommerceEventsClient\Event\Metadata\EventMetadataException;
use Magento\AdobeCommerceEventsClient\Event\Operator\OperatorException;
use Magento\AdobeCommerceEventsClient\Model\EventException;
use Psr\Log\LoggerInterface;

/**
 * Processes event according to event registration.
 */
class EventProcessor implements EventProcessorInterface
{
    /**
     * @param EventList $eventList
     * @param EventStorageInterface $eventStorage
     * @param EventModelFactoryInterface $eventModelFactory
     * @param CreateEventValidator $createEventValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        private EventList $eventList,
        private EventStorageInterface $eventStorage,
        private EventModelFactoryInterface $eventModelFactory,
        private CreateEventValidator $createEventValidator,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Checks if there are registered events that depend on this eventCode.
     *
     * Process events for all appropriate registration.
     *
     * @param string $eventCode
     * @param array $eventData
     * @return void
     * @throws EventException
     * @throws EventInitializationException
     */
    public function processEvent(string $eventCode, array $eventData): void
    {
        $eventCode = $this->eventList->removeCommercePrefix($eventCode);
        foreach ($this->eventList->getAll() as $event) {
            if ($event->isEnabled() && $event->isBasedOn($eventCode)) {
                $this->saveEvent($event, $eventData);
            }
        }
    }

    /**
     * Creates an Event with the specified event code and event data and adds it to storage.
     *
     * @param Event $event
     * @param array $eventData
     * @return void
     * @throws EventException
     * @throws EventInitializationException
     */
    private function saveEvent(Event $event, array $eventData): void
    {
        $eventCode = EventSubscriberInterface::EVENT_PREFIX_COMMERCE . $event->getName();
        try {
            if (!$this->createEventValidator->validate($event, $eventData)) {
                return;
            }

            $eventModel = $this->eventModelFactory->create($event, $eventData);
            $this->eventStorage->save($event, $eventModel);
        } catch (EventStorageException $e) {
            $this->logger->error(sprintf(
                'Could not create event "%s": %s',
                $eventCode,
                $e->getMessage()
            ));
        } catch (OperatorException $e) {
            $this->logger->error(sprintf(
                'Could not check that event "%s" passed the rule, error: %s',
                $eventCode,
                $e->getMessage()
            ));
        } catch (EventMetadataException $e) {
            $this->logger->error(sprintf(
                'Could not collect required metadata for the event "%s", error: %s',
                $eventCode,
                $e->getMessage()
            ));
        }
    }
}
