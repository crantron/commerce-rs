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
use Magento\AdobeCommerceEventsClient\Event\Validator\ValidatorException;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Retrieves and executes the processor class for subscribed event
 */
class EventDataProcessor
{
    public const PROCESSOR_CLASS = 'class';
    public const PROCESSOR_PRIORITY = 'priority';

    /**
     * @var EventList
     */
    private EventList $eventList;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param EventList $eventList
     * @param LoggerInterface $logger
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        EventList $eventList,
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager
    ) {
        $this->eventList = $eventList;
        $this->logger = $logger;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves and executes the processors defined for each waiting event.
     *
     * @param array $waitingEvents
     * @return array
     * @throws EventInitializationException
     */
    public function execute(array $waitingEvents): array
    {
        foreach ($waitingEvents as $waitingEventKey => $waitingEventsData) {
            $event = $this->eventList->get($waitingEventsData['eventCode']);
            if ($event !== null && !empty($event->getProcessors())) {
                $waitingEvents[$waitingEventKey] = $this->executeProcessors($event, $waitingEventsData);
            }
        }
        return $waitingEvents;
    }

    /**
     * Executes the event processors class
     *
     * @param Event $event
     * @param array $waitingEventsData
     * @return array
     */
    private function executeProcessors(Event $event, array $waitingEventsData): array
    {
        $eventProcessors = $this->orderProcessorPriority($event->getProcessors());
        foreach ($eventProcessors as $processor) {
            if (empty($processor[self::PROCESSOR_CLASS])) {
                continue;
            }
            try {
                $processorInstance = $this->getProcessorInstance(
                    $processor[self::PROCESSOR_CLASS],
                    $waitingEventsData
                );
                $waitingEventsData['eventData'] = $processorInstance->process(
                    $event,
                    $waitingEventsData['eventData']
                );
            } catch (\Exception $e) {
                $this->logger->error(sprintf(
                    'The processor class has not been applied. Error: %s',
                    $e->getMessage()
                ));
            }
        }
        return $waitingEventsData;
    }

    /**
     * Orders event processors based on the priority for execution.
     * Eg: [[ class => "testProcessor1", priority="20"], [ class => "testProcessor2", priority="10"]]
     * "testProcessor2" having priority "10" will be executed first
     *
     * @param array $eventProcessors
     * @return array
     */
    private function orderProcessorPriority(array $eventProcessors): array
    {
        array_multisort(
            array_column($eventProcessors, self::PROCESSOR_PRIORITY),
            SORT_ASC,
            SORT_NUMERIC,
            $eventProcessors,
        );
        return $eventProcessors;
    }

    /**
     * Validates the process class exists and the class implements EventDataProcessorInterface
     *
     * @param string $processorClass
     * @param array $waitingEventsData
     * @return EventDataProcessorInterface
     * @throws ValidatorException
     */
    private function getProcessorInstance(string $processorClass, array $waitingEventsData): EventDataProcessorInterface
    {
        try {
            $processorClassInstance =  $this->objectManager->get($processorClass);
        } catch (\Exception $e) {
            throw new ValidatorException(
                __(
                    'Can\'t create a processor class "%1" for Event "%2". %3',
                    $processorClass,
                    $waitingEventsData['eventCode'],
                    $e->getMessage()
                )
            );
        }

        if (!$processorClassInstance instanceof EventDataProcessorInterface) {
            throw new ValidatorException(
                __(
                    'Processor class "%1" for Event "%2" does not implement EventDataProcessorInterface',
                    $processorClass,
                    $waitingEventsData['eventCode']
                )
            );
        }
        return $processorClassInstance;
    }
}
