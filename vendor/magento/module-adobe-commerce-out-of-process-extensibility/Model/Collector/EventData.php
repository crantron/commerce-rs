<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 *************************************************************************
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector;

use Magento\Framework\DataObject;

/**
 * Event data object
 */
class EventData extends DataObject
{
    public const EVENT_NAME = 'event_name';
    public const EVENT_CLASS_EMITTER = 'event_class_emitter';

    /**
     * Returns event name
     *
     * @return string
     */
    public function getEventName(): string
    {
        return (string)$this->getData(self::EVENT_NAME);
    }

    /**
     * Returns class name where event is emitted
     *
     * @return string
     */
    public function getEventClassEmitter(): string
    {
        return (string)$this->getData(self::EVENT_CLASS_EMITTER);
    }
}
