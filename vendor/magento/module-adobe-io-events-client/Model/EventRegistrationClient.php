<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model;

use Magento\AdobeIoEventsClient\Exception\InvalidConfigurationException;
use Magento\AdobeIoEventsClient\Model\Data\EventMetadata;
use Magento\AdobeIoEventsClient\Model\Data\EventMetadataFactory;
use Magento\AdobeIoEventsClient\Model\Data\EventRegistration;
use Magento\AdobeIoEventsClient\Model\Data\EventRegistrationFactory;
use Magento\AdobeIoEventsClient\Model\IOEventsApi\ApiRequestExecutor;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Client to manage Event Registrations via APIs
 */
class EventRegistrationClient
{
    /**
     * @param AdobeIOConfigurationProvider $configurationProvider
     * @param ApiRequestExecutor $requestExecutor
     * @param EventRegistrationFactory $eventRegistrationFactory
     * @param EventMetadataFactory $eventMetadataFactory
     * @param Json $json
     * @param TokenCacheHandler $tokenCacheHandler
     */
    public function __construct(
        private AdobeIOConfigurationProvider $configurationProvider,
        private ApiRequestExecutor $requestExecutor,
        private EventRegistrationFactory $eventRegistrationFactory,
        private EventMetadataFactory $eventMetadataFactory,
        private Json $json,
        private TokenCacheHandler $tokenCacheHandler
    ) {
    }

    /**
     * Returns a list of event registration of the project.
     *
     * @return EventRegistration[]
     * @throws NotFoundException
     * @throws InvalidConfigurationException
     * @throws AuthorizationException
     */
    public function getAll(): array
    {
        $response = $this->requestExecutor->executeRequest(
            ApiRequestExecutor::GET,
            $this->getUrl(AdobeIOConfigurationProvider::XML_PATH_ADOBE_IO_EVENT_REGISTRATIONS_LIST_URL)
        );

        if ($response->getStatusCode() == 401) {
            $this->tokenCacheHandler->removeTokenData();
            throw new AuthorizationException(__('Unable to authorize'));
        }

        if ($response->getStatusCode() == 404) {
            throw new NotFoundException(__('Event Registration list was not found'));
        }

        $data = $this->json->unserialize($response->getBody()->getContents());

        $registrations = [];
        foreach ($data['_embedded']['registrations'] as $registrationData) {
            $registrationData[EventRegistration::EVENTS] = $this->buildEvents($registrationData);

            $registrations[] = $this->eventRegistrationFactory->create(['data' => $registrationData]);
        }

        return $registrations;
    }

    /**
     * Returns API url by replacing placeholders with real values.
     *
     * @param string $urlConfigPath
     * @return string
     * @throws InvalidConfigurationException
     * @throws NotFoundException
     */
    private function getUrl(string $urlConfigPath): string
    {
        $project = $this->configurationProvider->getConfiguration()->getProject();
        return str_replace(
            ["#{ims_org_id}", "#{project_id}", "#{workspace_id}"],
            [
                $project->getOrganization()->getId(),
                $project->getId(),
                $project->getWorkspace()->getId(),
            ],
            $this->configurationProvider->getApiUrl() . '/' .
            $this->configurationProvider->getScopeConfig($urlConfigPath, AdobeIOConfigurationProvider::SCOPE_STORE)
        );
    }

    /**
     * Returns list of events based on event registration data
     *
     * @param EventMetadata[] $registrationData
     * @return array
     */
    private function buildEvents(array $registrationData): array
    {
        $events = [];
        foreach ($registrationData[EventRegistration::EVENTS_OF_INTEREST] ?? [] as $eventData) {
            $eventData[EventMetadata::DESCRIPTION] = $eventData['event_description'];
            $eventData[EventMetadata::LABEL] = $eventData['event_label'];
            $events[] = $this->eventMetadataFactory->create(['data' => $eventData]);
        }

        return $events;
    }
}
