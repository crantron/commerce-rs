<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Observer;

use Magento\AdobeIoEventsClient\Model\TokenCacheHandler;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for removing a cached IMS access token for Adobe IO authorization.
 */
class AdobeIoConfigurationObserver implements ObserverInterface
{
    /**
     * @param TokenCacheHandler $tokenCacheHandler
     */
    public function __construct(
        private TokenCacheHandler $tokenCacheHandler
    ) {
    }

    /**
     * Removes a cached IMS access token.
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        $this->tokenCacheHandler->removeTokenData();
    }
}
