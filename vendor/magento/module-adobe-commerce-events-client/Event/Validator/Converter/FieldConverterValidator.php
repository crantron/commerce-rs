<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Validator\Converter;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\EventField;
use Magento\AdobeCommerceEventsClient\Event\Filter\FieldConverterInterface;
use Magento\AdobeCommerceEventsClient\Event\Validator\EventValidatorInterface;
use Magento\AdobeCommerceEventsClient\Event\Validator\ValidatorException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Validation of field and converter class for a Event
 */
class FieldConverterValidator implements EventValidatorInterface
{
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(private ObjectManagerInterface $objectManager)
    {
    }

    /**
     * Validates an event subscription's fields and field converter classes
     *
     * @param Event $event
     * @param bool $force
     * @throws ValidatorException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(Event $event, bool $force = false): void
    {
        $fields = [];
        foreach ($event->getEventFields() as $eventField) {
            $this->checkValidFieldOptions($eventField);
            $fieldName = $eventField->getName();
            $converterClass = $eventField->getConverter();

            if (!$eventField->hasData(EventField::NAME)) {
                throw new ValidatorException(
                    __(
                        'Field name must be contained in the field options value for converter "%1"',
                        $eventField->getConverter()
                    )
                );
            }

            if (in_array($fieldName, $fields)) {
                throw new ValidatorException(
                    __('Field "%1" can not be provided twice in the event subscription', $fieldName)
                );
            }

            $fields[] = $fieldName;
            if (!empty($converterClass)) {
                $this->validateConverterClass($converterClass, $fieldName);
            }
        }
    }

    /**
     * Validates the subscribed field converter class exist and it should implement FieldConverterInterface
     *
     * @param string $converterClass
     * @param string $fieldName
     * @return void
     * @throws ValidatorException
     */
    public function validateConverterClass(string $converterClass, string $fieldName): void
    {
        try {
            $converterClassInstance = $this->objectManager->get($converterClass);
        } catch (\Exception $e) {
            throw new ValidatorException(
                __(
                    'Can\'t create a converter class "%1" for field "%2". Error: %3',
                    $converterClass,
                    $fieldName,
                    $e->getMessage()
                )
            );
        }

        if (!$converterClassInstance instanceof FieldConverterInterface) {
            throw new ValidatorException(
                __(
                    'Converter class "%1" for field "%2" does not implement FieldConverterInterface',
                    $converterClass,
                    $fieldName
                )
            );
        }
    }

    /**
     * Checks for valid field options
     *
     * @param EventField $eventField
     * @throws ValidatorException
     */
    private function checkValidFieldOptions(EventField $eventField): void
    {
        $validProperties = [EventField::NAME, EventField::CONVERTER];
        if (array_diff(array_keys($eventField->getData()), $validProperties)) {
            throw new ValidatorException(
                __(
                    'Only the following properties are allowed for an event field: %1',
                    implode(', ', $validProperties)
                )
            );
        }
    }
}
