<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Client interface for sending events in batches
 */
interface ClientInterface
{
    /**
     * Sends a batch of event data to the Events Service.
     *
     * @param array $messages
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws InvalidConfigurationException
     */
    public function sendEventDataBatch(array $messages): ResponseInterface;
}
