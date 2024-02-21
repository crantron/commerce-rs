<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Console;

use Magento\AdobeIoEventsClient\Model\AdobeIOConfigurationProvider;
use Magento\AdobeIoEventsClient\Model\EventProviderClient;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command to get details for configured event provider
 */
class EventProviderInfo extends Command
{
    public const COMMAND_NAME = 'events:provider:info';

    /**
     * @param AdobeIOConfigurationProvider $configurationProvider
     * @param EventProviderClient $eventProviderClient
     */
    public function __construct(
        private AdobeIOConfigurationProvider $configurationProvider,
        private EventProviderClient $eventProviderClient
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(
            "Returns details about the configured event provider"
        );

        parent::configure();
    }

    /**
     * @inheritDoc
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = $this->configurationProvider->getProvider();

        if ($provider === null) {
            $output->writeln('No configured event provider found');
            return Cli::RETURN_FAILURE;
        }

        try {
            $providerInfo = $this->eventProviderClient->getEventProvider($provider);
        } catch (LocalizedException $exception) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $exception->getMessage()
            ));

            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Configured event provider details:</info>');
        foreach (["id", "label", "description"] as $attribute) {
            if (isset($providerInfo[$attribute])) {
                $output->writeln(sprintf("- %s: %s", $attribute, $providerInfo[$attribute]));
            }
        }

        return Cli::RETURN_SUCCESS;
    }
}
