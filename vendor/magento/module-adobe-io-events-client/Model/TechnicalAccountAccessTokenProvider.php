<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model;

use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterfaceFactory;
use Magento\AdobeIoEventsClient\Api\AccessTokenProviderInterface;
use Magento\AdobeIoEventsClient\Exception\InvalidConfigurationException;
use Magento\AdobeIoEventsClient\Model\ImsJwtApi\JwtClient;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NotFoundException;

/**
 * Get the access token from the technical account JWT
 */
class TechnicalAccountAccessTokenProvider implements AccessTokenProviderInterface
{
    /**
     * @var TokenResponseInterfaceFactory
     */
    private TokenResponseInterfaceFactory $tokenResponseFactory;

    /**
     * @var JwtClient
     */
    private JwtClient $jwtClient;

    /**
     * @var TokenCacheHandler
     */
    private TokenCacheHandler $tokenCacheHandler;

    /**
     * @param TokenResponseInterfaceFactory $tokenResponseFactory
     * @param JwtClient $jwtClient
     * @param TokenCacheHandler $tokenCacheHandler
     */
    public function __construct(
        TokenResponseInterfaceFactory $tokenResponseFactory,
        JwtClient $jwtClient,
        TokenCacheHandler $tokenCacheHandler
    ) {
        $this->tokenResponseFactory = $tokenResponseFactory;
        $this->jwtClient = $jwtClient;
        $this->tokenCacheHandler = $tokenCacheHandler;
    }

    /**
     * Call IMS to fetch Access Token from Technical Account JWT
     *
     * @return TokenResponseInterface
     * @throws AuthorizationException
     * @throws InvalidConfigurationException
     */
    public function getAccessToken(): TokenResponseInterface
    {
        $tokenData = $this->tokenCacheHandler->getTokenData();
        if ($tokenData !== null) {
            return $this->tokenResponseFactory->create(['data' => $tokenData]);
        }

        $response = $this->jwtClient->fetchJwtTokenResponse();

        if (!is_array($response) || empty($response['access_token'])) {
            throw new AuthorizationException(__('Could not login to Adobe IMS.'));
        }

        // expires_in is in milliseconds for /ims/exchange/jwt API used by jwtClient
        $lifeTime = (int)($response['expires_in']/1000 * 0.9);
        unset($response['expires_in']);
        $this->tokenCacheHandler->saveTokenData($response, $lifeTime);

        return $this->tokenResponseFactory->create(['data' => $response]);
    }
}
