<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Handles the caching of an IMS access token used for Adobe IO authorization.
 */
class TokenCacheHandler
{
    private const CACHE_ID = 'adobe-io-ims-access-token';

    /**
     * @param CacheInterface $cache
     * @param EncryptorInterface $encryptor
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        private CacheInterface $cache,
        private EncryptorInterface $encryptor,
        private Json $json,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Retrieves the IMS access token from the cache.
     *
     * @return array|null
     */
    public function getTokenData(): ?array
    {
        $cachedTokenData = $this->cache->load(self::CACHE_ID);
        if ($cachedTokenData) {
            $tokenDataString = $this->encryptor->decrypt($cachedTokenData);
            try {
                $tokenData = $this->json->unserialize($tokenDataString);
                if (is_array($tokenData)) {
                    return $tokenData;
                }

                $this->logger->error('Cached access token data has an unexpected type. Expected an array.');
            } catch (\InvalidArgumentException $e) {
                $this->logger->error(
                    sprintf(
                        'Unable to deserialize cached access token data: %s',
                        $e->getMessage()
                    )
                );
            }
        }

        return null;
    }

    /**
     * Saves the IMS access token.
     *
     * @param array $tokenData
     * @param int $lifeTime
     * @return void
     */
    public function saveTokenData(array $tokenData, int $lifeTime): void
    {
        try {
            $tokenDataString = $this->json->serialize($tokenData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error(
                sprintf(
                    'Unable to serialize access token data for caching: %s',
                    $e->getMessage()
                )
            );
            return;
        }

        $this->cache->save(
            $this->encryptor->encrypt($tokenDataString),
            self::CACHE_ID,
            [],
            $lifeTime
        );
    }

    /**
     * Removes the IMS access token.
     *
     * @return void
     */
    public function removeTokenData(): void
    {
        $this->cache->remove(self::CACHE_ID);
    }
}
