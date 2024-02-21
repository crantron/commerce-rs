<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Filter;

use Magento\AdobeCommerceEventsClient\Event\Converter\EventFieldConverter;
use Magento\AdobeCommerceEventsClient\Event\DataFilterInterface;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventList;
use Magento\AdobeCommerceEventsClient\Event\Filter\FieldFilter\Field;
use Magento\AdobeCommerceEventsClient\Event\Filter\FieldFilter\FieldConverter;
use Magento\Framework\App\ObjectManager;

/**
 * Filters event payload according to the list of configured fields
 */
class EventFieldsFilter implements DataFilterInterface
{
    /**
     * Event field value used to bypass filtering of the event payload.
     */
    public const FIELD_WILDCARD = '*';

    /**
     * @var EventList
     */
    private EventList $eventList;

    /**
     * @var FieldConverter
     */
    private FieldConverter $converter;

    /**
     * @var EventFieldConverter
     */
    private EventFieldConverter $eventFieldConverter;

    /**
     * @param EventList $eventList
     * @param FieldConverter $converter
     * @param EventFieldConverter|null $eventFieldConverter
     */
    public function __construct(
        EventList $eventList,
        FieldConverter $converter,
        EventFieldConverter $eventFieldConverter = null
    ) {
        $this->eventList = $eventList;
        $this->converter = $converter;
        $this->eventFieldConverter = $eventFieldConverter ?: ObjectManager::getInstance()->get(
            EventFieldConverter::class
        );
    }

    /**
     * @inheritDoc
     */
    public function filter(string $eventCode, array $eventData): array
    {
        $event = $this->eventList->get($eventCode);

        if (!$event instanceof Event ||
            empty($event->getFields()) ||
            in_array(self::FIELD_WILDCARD, $event->getFields())
        ) {
            return $eventData;
        }

        $filteredData = [];

        $fields = $this->converter->convert($event->getEventFields());

        foreach ($fields as $field) {
            $filteredData = array_replace_recursive(
                $filteredData,
                [$field->getName() => $this->processField($field, $eventData, $event)]
            );
        }

        return $filteredData;
    }

    /**
     * Processes eventData filtering according to given Field.
     *
     * @param Field $field
     * @param array $eventData
     * @param Event $event
     * @return array|mixed|null
     */
    private function processField(Field $field, array $eventData, Event $event)
    {
        $filteredData = [];

        $children = $field->getChildren();
        $converterClass = $field->getConverterClass();

        if (empty($children)) {
            $fieldValue = $this->processTailField($field, $eventData);
            if ($fieldValue !== null && $converterClass !== null) {
                $fieldValue = $this->eventFieldConverter->convertField($field, $fieldValue, $event);
            }
            return $fieldValue;
        }

        if ($field->isArray()) {
            if (!isset($eventData[$field->getName()]) || !is_array($eventData[$field->getName()])) {
                return [];
            }

            foreach ($eventData[$field->getName()] as $eventDataItem) {
                $filteredData[] = $this->processChildren(
                    $children,
                    is_array($eventDataItem) ? $eventDataItem : [],
                    $event
                );
            }
        } else {
            $filteredData = array_replace_recursive(
                $filteredData,
                $this->processChildren($children, $eventData[$field->getName()] ?? [], $event)
            );
        }

        return $filteredData;
    }

    /**
     * Process children field elements
     *
     * @param Field[] $children
     * @param array $eventData
     * @param Event $event
     * @return array
     */
    private function processChildren(array $children, array $eventData, Event $event): array
    {
        $result = [];

        foreach ($children as $child) {
            if ($child->hasChildren()) {
                $result[$child->getName()] = $this->processField($child, $eventData, $event);
            } else {
                $childValue = $this->processTailField($child, $eventData);
                if ($childValue != null && $child->getConverterClass() != null) {
                    $childValue = $this->eventFieldConverter->convertField($child, $childValue, $event);
                }
                $result[$child->getName()] = $childValue;
            }
        }
        return $result;
    }

    /**
     * Process field which has no children.
     *
     * Removes index from array type fields.
     *
     * @param Field $field
     * @param array $eventData
     * @return array|mixed|null
     */
    public function processTailField(Field $field, array $eventData)
    {
        if (!isset($eventData[$field->getName()])) {
            return null;
        }

        if ($field->isArray() && is_array($eventData[$field->getName()])) {
            return array_values($eventData[$field->getName()]);
        }

        return $eventData[$field->getName()];
    }
}
