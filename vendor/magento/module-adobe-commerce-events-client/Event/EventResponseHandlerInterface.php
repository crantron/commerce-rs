<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event;

use Magento\Framework\Exception\LocalizedException;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for handling response from commerce service.
 */
interface EventResponseHandlerInterface
{
    /**
     * Based on response updates the status of events
     *
     * @param ResponseInterface $response
     * @param array $eventIds
     * @throws LocalizedException
     */
    public function handle(ResponseInterface $response, array $eventIds);
}
