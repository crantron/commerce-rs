<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event;

use Magento\AdobeCommerceEventsClient\Api\Data\EventInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class for updating success and failure status of commerce events
 */
class EventResponseHandler implements EventResponseHandlerInterface
{
    /**
     * @param EventStatusUpdater $statusUpdater
     * @param LoggerInterface $logger
     */
    public function __construct(
        private EventStatusUpdater $statusUpdater,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Updates events status based on the success or failure of sending the data.
     *
     * @param ResponseInterface $response
     * @param array $eventIds
     * @return void
     * @throws LocalizedException
     */
    public function handle(ResponseInterface $response, array $eventIds): void
    {
        if ($response->getStatusCode() == 200) {
            $this->logger->info(sprintf(
                'Event data batch of %s events was successfully published.',
                count($eventIds)
            ));
            $this->statusUpdater->updateStatus($eventIds, EventInterface::SUCCESS_STATUS);
        } else {
            $errorMessage = sprintf(
                'Error code: %d; reason: %s %s',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $response->getBody()->getContents()
            );
            $this->statusUpdater->setFailure($eventIds, $errorMessage);
        }
    }
}
