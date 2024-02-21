<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Console;

use Magento\AdobeIoEventsClient\Model\Data\EventMetadata;
use Magento\AdobeIoEventsClient\Model\Data\EventRegistration;
use Magento\AdobeIoEventsClient\Model\EventRegistrationClient;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Command for displaying a list of event registrations of your project
 */
class EventRegistrationListCommand extends Command
{
    /**
     * @param EventRegistrationClient $eventRegistrationClient
     * @param string|null $name
     */
    public function __construct(
        private EventRegistrationClient $eventRegistrationClient,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('events:registrations:list')
            ->setDescription('Lists event registrations in your App Builder project');

        parent::configure();
    }

    /**
     * Displays a list of events registration and linked events.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $registrations = $this->eventRegistrationClient->getAll();

            if (!count($registrations)) {
                $output->writeln('No registrations were found in your project');
            } elseif ($output->isVerbose()) {
                $table = new Table($output);

                foreach ($registrations as $eventRegistration) {
                    $table->setHeaders([
                        EventRegistration::NAME,
                        EventRegistration::ID,
                        EventRegistration::ENABLED,
                        EventRegistration::EVENTS,
                    ]);
                    $table->addRow([
                        EventRegistration::NAME => $eventRegistration->getName(),
                        EventRegistration::ID => $eventRegistration->getId(),
                        EventRegistration::ENABLED => $eventRegistration->isEnabled(),
                        EventRegistration::EVENTS => $this->formatEvents($eventRegistration->getEvents(), $output),
                    ]);
                }

                $table->render();
            } else {
                foreach ($registrations as $eventRegistration) {
                    $output->writeln(sprintf('- %s (%s)', $eventRegistration->getName(), $eventRegistration->getId()));
                }
            }

            return Cli::RETURN_SUCCESS;
        } catch (Throwable $e) {
            $output->writeln($e->getMessage());
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * Converts an array of event metadata to a string to be output within a Table.
     *
     * @param EventMetadata[] $eventMetadata
     * @param OutputInterface $output
     * @return string
     */
    private function formatEvents(array $eventMetadata, OutputInterface $output): string
    {
        $events = [];
        foreach ($eventMetadata as $metadata) {
            if ($output->isVeryVerbose()) {
                $events[] = sprintf(
                    '{ code: %s, provider_id: %s, label: %s }',
                    $metadata->getEventCode(),
                    $metadata->getData('provider_id') ?? 'N/A',
                    $metadata->getLabel()
                );
            } else {
                $events[] = sprintf('%s', $metadata->getEventCode());
            }
        }

        return str_replace(
            ['"{', '}"', '\/',],
            ['{', '}', '/',],
            json_encode($events, JSON_PRETTY_PRINT)
        );
    }
}
