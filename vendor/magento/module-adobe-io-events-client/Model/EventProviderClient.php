<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model;

use Magento\AdobeIoEventsClient\Api\EventProviderInterface;
use Magento\AdobeIoEventsClient\Exception\InvalidConfigurationException;
use Magento\AdobeIoEventsClient\Model\IOEventsApi\ApiRequestExecutor;
use Magento\AdobeIoEventsClient\Model\TokenCacheHandler;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Interaction with Event Providers on the IO Events API
 */
class EventProviderClient
{
    /**
     * @var AdobeIOConfigurationProvider
     */
    private AdobeIOConfigurationProvider $configurationProvider;

    /**
     * @var ApiRequestExecutor
     */
    private ApiRequestExecutor $requestExecutor;

    /**
     * @var TokenCacheHandler
     */
    private TokenCacheHandler $tokenCacheHandler;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @param AdobeIOConfigurationProvider $configurationProvider
     * @param ApiRequestExecutor $requestExecutor
     * @param TokenCacheHandler $tokenCacheHandler
     * @param Json $json
     */
    public function __construct(
        AdobeIOConfigurationProvider $configurationProvider,
        ApiRequestExecutor $requestExecutor,
        TokenCacheHandler $tokenCacheHandler,
        Json $json
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->requestExecutor = $requestExecutor;
        $this->tokenCacheHandler = $tokenCacheHandler;
        $this->json = $json;
    }

    /**
     * Call the API to create an event provider
     *
     * @param string $instanceId
     * @param EventProviderInterface $provider
     * @return array|bool|float|int|mixed|string|null
     * @throws AlreadyExistsException
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws InputException
     * @throws InvalidConfigurationException
     * @throws NotFoundException
     */
    public function createEventProvider(
        string $instanceId,
        EventProviderInterface $provider
    ) {
        $configuration = $this->configurationProvider->getConfiguration();

        $uri = str_replace(
            ["#{ims_org_id}", "#{project_id}", "#{workspace_id}"],
            [
                $configuration->getProject()->getOrganization()->getId(),
                $configuration->getProject()->getId(),
                $configuration->getProject()->getWorkspace()->getId()
            ],
            $this->configurationProvider->getScopeConfig(AdobeIOConfigurationProvider::XML_PATH_ADOBE_IO_PROVIDER_URL)
        );
        $uri = $this->configurationProvider->getApiUrl() . '/' . $uri;

        $params = [
            "json" => [
                "instance_id" => $instanceId,
                "label" => $provider->getLabel(),
                "description" => sprintf("%s (Instance %s)", $provider->getDescription(), $instanceId)
            ]
        ];

        $eventProviderMetadata = $this->configurationProvider->getEventProviderMetadata();
        if ($eventProviderMetadata) {
            $params['json']['provider_metadata'] = $eventProviderMetadata;
        }

        $response = $this->requestExecutor->executeRequest(
            ApiRequestExecutor::POST,
            $uri,
            $params
        );

        if ($response->getStatusCode() == 409) {
            throw new AlreadyExistsException(__("An event provider with the same instance ID already exists."));
        }

        if ($response->getStatusCode() == 401) {
            $this->tokenCacheHandler->removeTokenData();
            throw new AuthenticationException(__('Unable to authorize'));
        }

        if ($response->getStatusCode() != 201) {
            throw new InputException(__($response->getReasonPhrase()));
        }

        return $this->json->unserialize($response->getBody()->getContents());
    }

    /**
     * Calls the IO Events API to get details for the input event provider.
     *
     * @param EventProviderInterface $provider
     * @return array
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws InputException
     * @throws InvalidConfigurationException
     * @throws NotFoundException
     */
    public function getEventProvider(
        EventProviderInterface $provider
    ): array {
        $uri = str_replace(
            '#{provider_id}',
            $provider->getId(),
            $this->configurationProvider->getScopeConfig(
                AdobeIOConfigurationProvider::XML_PATH_ADOBE_IO_GET_PROVIDER_URL
            )
        );
        $uri = $this->configurationProvider->getApiUrl() . '/' . $uri;

        $response = $this->requestExecutor->executeRequest(
            ApiRequestExecutor::GET,
            $uri
        );

        if ($response->getStatusCode() == 401) {
            $this->tokenCacheHandler->removeTokenData();
            throw new AuthenticationException(__('Unable to authorize'));
        }

        if ($response->getStatusCode() != 200) {
            throw new InputException(__($response->getReasonPhrase()));
        }

        $responseBody = $this->json->unserialize($response->getBody()->getContents());

        if (!is_array($responseBody)) {
            throw new InputException(__('Unexpected provider details response: %1', $responseBody));
        }

        return $responseBody;
    }
}
