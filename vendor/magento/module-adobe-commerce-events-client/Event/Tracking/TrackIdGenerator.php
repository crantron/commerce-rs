<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2024 Adobe
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
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Tracking;

use Magento\AdobeCommerceEventsClient\Event\Config;
use Magento\AdobeCommerceEventsClient\Event\Event;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Exception;

/**
 * Generates a tracking identification based on Event object and event data.
 */
class TrackIdGenerator implements TrackIdGeneratorInterface
{
    private const HASH_ALGO = 'sha256';

    /**
     * @param Config $config
     * @param Random $random
     * @param Json $json
     */
    public function __construct(
        private Config $config,
        private Random $random,
        private Json $json
    ) {
    }

    /**
     * @inheritDoc
     */
    public function generate(Event $event, array $eventData): string
    {
        $data = $this->config->getMerchantId() . $this->config->getEnvironmentId() . $this->getRandomString();

        try {
            $data .= $this->json->serialize($eventData);
        } catch (\InvalidArgumentException $e) {
            $data .= uniqid();
        }

        $data .= microtime();

        return hash(self::HASH_ALGO, $data);
    }

    /**
     * Returns a random string.
     *
     * @return string
     */
    private function getRandomString(): string
    {
        try {
            return $this->random->getRandomString(32);
        } catch (LocalizedException $e) {
            try {
                return (string)random_int(10000000, 99999999);
            } catch (Exception $e) {
                return '';
            }
        }
    }
}
