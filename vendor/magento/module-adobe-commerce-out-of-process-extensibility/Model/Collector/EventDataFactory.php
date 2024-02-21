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

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for EventData
 */
class EventDataFactory
{
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(private ObjectManagerInterface $objectManager)
    {
    }

    /**
     * Creates EventData class instance with specified parameters
     *
     * @param array $data
     * @return EventData
     */
    public function create(array $data = []): EventData
    {
        return $this->objectManager->create(EventData::class, ['data' => $data]);
    }
}
