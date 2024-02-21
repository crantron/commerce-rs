<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Config;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\Processor\EventDataProcessor;
use Magento\AdobeCommerceEventsClient\Event\EventField;
use Magento\Framework\Config\ConverterInterface;

/**
 * Converts data from io_events.xml files to the array of events
 */
class Converter implements ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param DOMDocument $source
     * @return array
     * @throws InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        $events = $source->getElementsByTagName('event');
        /** @var DOMElement $eventConfig */
        foreach ($events as $eventConfig) {
            $rules = [];
            $fields = $this->processFields($eventConfig);
            foreach ($eventConfig->getElementsByTagName('rule') as $ruleNode) {
                $ruleData = [];
                foreach ($ruleNode->getElementsByTagName('*') as $ruleField) {
                    $ruleData[$ruleField->nodeName] = $ruleField->nodeValue;
                }

                $rules[] = $ruleData;
            }

            $processors = $this->getEventProcessors($eventConfig);

            $eventName = strtolower($eventConfig->getAttribute(Event::EVENT_NAME));
            $eventParent = strtolower($eventConfig->getAttribute(Event::EVENT_PARENT)) ?: null;
            $priority = $eventConfig->getAttribute(Event::EVENT_PRIORITY) ?: false;
            $destination = $eventConfig->getAttribute(Event::EVENT_DESTINATION) ?: Event::DESTINATION_DEFAULT;

            $output[$eventName] = [
                Event::EVENT_NAME => $eventName,
                Event::EVENT_FIELDS => $fields,
                Event::EVENT_RULES => $rules,
                Event::EVENT_PROCESSORS => $processors,
                Event::EVENT_PARENT => $eventParent,
                Event::EVENT_PRIORITY => $priority,
                Event::EVENT_DESTINATION => $destination
            ];
        }

        return $output;
    }

    /**
     * Creates a processor array if its defined in the io_events.xml
     *
     * @param DOMElement $eventConfig
     * @return array
     */
    private function getEventProcessors(DOMElement $eventConfig): array
    {
        $processors = [];
        foreach ($eventConfig->getElementsByTagName('processor') as $processorNode) {
            if ($processorNode->parentNode->nodeName == 'processors') {
                $processors[] = [
                    EventDataProcessor::PROCESSOR_CLASS => $processorNode->getAttribute('class'),
                    EventDataProcessor::PROCESSOR_PRIORITY => $processorNode->getAttribute('priority')
                ];
            }
        }
        return $processors;
    }

    /**
     * Creates a field array having field name and its converter
     *
     * @param DOMElement $eventConfig
     * @return array
     */
    private function processFields(DOMElement $eventConfig): array
    {
        $fields = [];
        foreach ($eventConfig->getElementsByTagName('field') as $field) {
            if ($field->parentNode->nodeName == 'fields') {
                $fields[] = [
                    EventField::NAME => $field->getAttribute('name'),
                    EventField::CONVERTER => $field->getAttribute('converter') ?: null
                ];
            }
        }

        return $fields;
    }
}
