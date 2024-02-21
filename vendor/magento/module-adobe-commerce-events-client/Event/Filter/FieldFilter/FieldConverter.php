<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Filter\FieldFilter;

use Magento\AdobeCommerceEventsClient\Event\EventField;

/**
 * Converts event field expressions to aggregated Field objects
 */
class FieldConverter
{
    /**
     * Converts a list of fields expression to the list of Field objects.
     *
     * Ignore field expression if it is not a string.
     *
     * @param EventField[] $eventFields
     * @return Field[]
     */
    public function convert(array $eventFields): array
    {
        $fields = $registry = [];
        foreach ($eventFields as $eventField) {
            if (empty($eventField->getName())) {
                continue;
            }

            $fields[] = $this->buildField(
                explode('.', $eventField->getName()),
                $eventField,
                null,
                $registry
            );
        }
        return array_filter($fields);
    }

    /**
     * Builds a Field object based on parts of event field expression.
     *
     * @param array $fieldParts
     * @param EventField $eventField
     * @param Field|null $parent
     * @param array $registry
     * @return Field|null
     */
    private function buildField(
        array $fieldParts,
        EventField $eventField,
        Field $parent = null,
        array &$registry = []
    ): ?Field {
        if (str_contains($fieldParts[0], '[]')) {
            $field = new Field(str_replace('[]', '', $fieldParts[0]), $parent, true, $eventField->getConverter());
        } else {
            $field = new Field($fieldParts[0], $parent, false, $eventField->getConverter());
        }

        if (isset($registry[$field->getPath()])) {
            $field = $registry[$field->getPath()];
            if (count($fieldParts) > 1) {
                $child = $this->buildField(array_slice($fieldParts, 1), $eventField, $field, $registry);
                if ($child) {
                    $field->addChildren($child);
                }
            }
            return null;
        }

        $registry[$field->getPath()] = $field;

        if (count($fieldParts) > 1) {
            $field->addChildren($this->buildField(array_slice($fieldParts, 1), $eventField, $field, $registry));
        }

        return $field;
    }
}
