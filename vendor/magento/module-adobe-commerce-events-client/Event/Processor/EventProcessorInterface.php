<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Processor;

use Magento\AdobeCommerceEventsClient\Event\EventInitializationException;
use Magento\AdobeCommerceEventsClient\Model\EventException;

/**
 * Processes event according to event registration.
 */
interface EventProcessorInterface
{
   /**
    * Processes event according to event registration.
    *
    * @param string $eventCode
    * @param array $eventData
    * @return void
    * @throws EventException
    * @throws EventInitializationException
    */
    public function processEvent(string $eventCode, array $eventData): void;
}
