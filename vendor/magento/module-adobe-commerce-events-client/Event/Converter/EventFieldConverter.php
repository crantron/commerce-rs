<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Converter;

use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\AdobeCommerceEventsClient\Event\Filter\FieldFilter\Field;
use Magento\AdobeCommerceEventsClient\Event\Validator\Converter\FieldConverterValidator;
use Magento\AdobeCommerceEventsClient\Event\Validator\ValidatorException;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class for converting data to event suitable format
 */
class EventFieldConverter
{
    /**
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     * @param FieldConverterValidator $fieldConverterValidator
     */
    public function __construct(
        private ObjectManagerInterface $objectManager,
        private LoggerInterface $logger,
        private FieldConverterValidator $fieldConverterValidator
    ) {
    }

    /**
     * Updated the field value using the instance of converter class
     *
     * @param Field $field
     * @param mixed $fieldValue
     * @param Event $event
     * @return mixed
     */
    public function convertField(Field $field, mixed $fieldValue, Event $event)
    {
        $converterClass = $field->getConverterClass();
        $fieldName = $field->getName();
        try {
            $this->fieldConverterValidator->validateConverterClass($converterClass, $fieldName);
        } catch (ValidatorException $ve) {
            $this->logger->error(sprintf(
                'The converter class was not applied to the field for event "%s". Error: %s',
                $event->getName(),
                $ve->getMessage()
            ));
            return $fieldValue;
        }

        try {
            $classInstance = $this->objectManager->get($converterClass);
            return $classInstance->convert($fieldValue, $event);
        } catch (Throwable $e) {
            $this->logger->error(sprintf(
                'Field conversion failed for field %s, Error: %s',
                $fieldName,
                $e->getMessage()
            ));
            return $fieldValue;
        }
    }
}
