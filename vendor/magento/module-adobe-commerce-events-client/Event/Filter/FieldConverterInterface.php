<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Filter;

use Magento\AdobeCommerceEventsClient\Event\Event;

/**
 * Interface for converting field value
 */
interface FieldConverterInterface
{
    /**
     * Converts a field value
     *
     * @param mixed $value
     * @param Event $event
     * @return mixed
     */
    public function convert(mixed $value, Event $event);
}
