<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model\Credentials;

use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterfaceFactory;
use Magento\AdobeIoEventsClient\Exception\InvalidConfigurationException;
use Magento\AdobeIoEventsClient\Model\AdobeIOConfigurationProvider;
use Magento\AdobeIoEventsClient\Model\Config\Env\EnvironmentConfigFactory;
use Magento\AdobeIoEventsClient\Model\Data\AdobeConsoleConfiguration\AdobeConsoleConfiguration;
use Magento\AdobeIoEventsClient\Model\Data\AdobeConsoleConfiguration\OAuth as OAuthCredentials;
use Magento\AdobeIoEventsClient\Model\TokenCacheHandler;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Oauth based credentials
 */
class OAuth implements CredentialsInterface
{
    /**
     * @param AdobeIOConfigurationProvider $configurationProvider
     * @param TokenResponseInterfaceFactory $tokenResponseFactory
     * @param CurlFactory $curlFactory
     * @param Json $json
     * @param TokenCacheHandler $tokenCacheHandler
     * @param EnvironmentConfigFactory $environmentConfigFactory
     */
    public function __construct(
        private AdobeIOConfigurationProvider $configurationProvider,
        private TokenResponseInterfaceFactory $tokenResponseFactory,
        private CurlFactory $curlFactory,
        private Json $json,
        private TokenCacheHandler $tokenCacheHandler,
        private EnvironmentConfigFactory $environmentConfigFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getClientId(): string
    {
        return $this->getOAuthCredentials()->getClientId();
    }

    /**
     * @inheritDoc
     */
    public function getImsOrgId(): string
    {
        $configuration = $this->getConfiguration();

        return $configuration->getProject()->getOrganization()->getImsOrgId();
    }

    /**
     * @inheritDoc
     */
    public function getToken(): TokenResponseInterface
    {
        $tokenData = $this->tokenCacheHandler->getTokenData();
        if ($tokenData !== null) {
            return $this->tokenResponseFactory->create(['data' => $tokenData]);
        }

        /** @var Curl $curl */
        $curl = $this->curlFactory->create();
        $curl->addHeader('cache-control', 'no-cache');
        try {
            $curl->post(
                $this->environmentConfigFactory->create()->getImsUrl(),
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->getClientId(),
                    'client_secret' => $this->getOAuthCredentials()->getClientSecret(),
                    'scope' => implode(',', $this->getOAuthCredentials()->getScopes())
                ]
            );
        } catch (NotFoundException $exception) {
            throw new InvalidConfigurationException(__($exception->getMessage()));
        }

        $response = $this->json->unserialize($curl->getBody());

        if (!is_array($response) || empty($response['access_token'])) {
            throw new AuthorizationException(__('Could not login to Adobe IMS.'));
        }

        $lifeTime = (int)($response['expires_in'] * 0.9);
        unset($response['expires_in']);
        $this->tokenCacheHandler->saveTokenData($response, $lifeTime);

        return $this->tokenResponseFactory->create(['data' => $response]);
    }

    /**
     * Returns OAuth credentials.
     *
     * @return OAuthCredentials
     * @throws InvalidConfigurationException
     */
    private function getOAuthCredentials(): OAuthCredentials
    {
        $credentials = $this->getConfiguration()->getFirstCredential();
        if (!$credentials->getOAuth() instanceof OAuthCredentials) {
            throw new InvalidConfigurationException(
                __('OAuth credentials is not found in the Adobe I/O Workspace Configuration')
            );
        }

        return $credentials->getOAuth();
    }

    /**
     * Returns console configuration.
     *
     * @return AdobeConsoleConfiguration
     * @throws InvalidConfigurationException
     */
    private function getConfiguration(): AdobeConsoleConfiguration
    {
        try {
            return $this->configurationProvider->getConfiguration();
        } catch (NotFoundException $exception) {
            throw new InvalidConfigurationException(__($exception->getMessage()));
        }
    }
}
