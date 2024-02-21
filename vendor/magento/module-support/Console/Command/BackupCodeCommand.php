<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Support\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Math\Random;
use Magento\Support\Helper\Shell as ShellHelper;
use Magento\Support\Model\Backup\Config as BackupConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for displaying current index mode for indexers.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupCodeCommand extends AbstractBackupDumpCommand
{
    /**
     * @var array
     */
    protected $backupList = [
        'app',
        'bin',
        'composer.*',
        'dev',
        '*.php',
        'lib',
        'pub/*.php',
        'pub/errors',
        'setup',
        'vendor'
    ];

    /**
     * @var array
     */
    protected $backupLogsList = [
        'var/log',
        'var/report'
    ];

    /**
     * @var Filesystem
     * @deprecated 100.1.0
     * @see No longer needed
     */
    protected $filesystem;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Random
     */
    private $random;

    /**
     * BackupCodeCommand constructor
     *
     * @param ShellHelper $shellHelper
     * @param BackupConfig $backupConfig
     * @param DeploymentConfig $deploymentConfig
     * @param Filesystem $filesystem
     * @param Random|null $random
     * @param File|null $fileDriver
     * @param DirectoryList|null $directoryList
     *
     * @deprecated 100.1.0
     * @see No longer needed
     */
    public function __construct(
        ShellHelper $shellHelper,
        BackupConfig $backupConfig,
        DeploymentConfig $deploymentConfig,
        Filesystem $filesystem,
        ?Random $random = null,
        ?File $fileDriver = null,
        ?DirectoryList $directoryList = null
    ) {
        parent::__construct($shellHelper, $backupConfig, $deploymentConfig);
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver ?? ObjectManager::getInstance()->get(File::class);
        $this->random = $random ?? ObjectManager::getInstance()->get(Random::class);
        $this->directoryList = $directoryList ?? ObjectManager::getInstance()->get(DirectoryList::class);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('support:backup:code')
            ->setDescription('Create Code backup')
            ->setDefinition($this->getInputList());
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->shellHelper->setRootWorkingDirectory();

            $filePath    = $this->getOutputPath($input) . DIRECTORY_SEPARATOR . $this->getBackupName($input);
            $includeLogs = (bool) $input->getOption(self::INPUT_KEY_LOGS);

            $backupCodeCommand = $this->getBackupCodeCommand($filePath);
            $output->writeln($backupCodeCommand);
            $output->writeln($this->shellHelper->execute($backupCodeCommand));

            if ($includeLogs) {
                $backupLogsCommand = $this->getBackupLogsCommand($filePath);
                $output->writeln($backupLogsCommand);
                $output->writeln($this->shellHelper->execute($backupLogsCommand));
            }

            $output->writeln('Code dump was created successfully');
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            $previousException = $e->getPrevious();
            if ($previousException instanceof \Exception) {
                $output->writeln('More information:');
                $output->writeln($previousException->getMessage());
            }
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * Get console command for code backup
     *
     * @param string $filePath
     * @return string
     * @throws NotFoundException
     */
    protected function getBackupCodeCommand($filePath)
    {
        $fileExtension = $this->backupConfig->getBackupFileExtension('code');
        $filesFrom = $this->createTempFile($this->getBackupList($this->backupList));

        $command = sprintf(
            '%s -n 15 %s -czhf %s --files-from %s',
            $this->shellHelper->getUtility(ShellHelper::UTILITY_NICE),
            $this->shellHelper->getUtility(ShellHelper::UTILITY_TAR),
            $filePath . '.' . ($fileExtension ?: 'tar.gz'),
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            escapeshellarg($filesFrom)
        );

        return $command;
    }

    /**
     * Get console command for logs backup
     *
     * @param string $filePath
     * @return string
     * @throws NotFoundException
     */
    protected function getBackupLogsCommand($filePath)
    {
        $filesFrom = $this->createTempFile($this->getBackupList($this->backupLogsList));

        $command = sprintf(
            '%s -n 15 %s -czhf %s --files-from %s',
            $this->shellHelper->getUtility(ShellHelper::UTILITY_NICE),
            $this->shellHelper->getUtility(ShellHelper::UTILITY_TAR),
            $filePath . '.logs.tar.gz',
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            escapeshellarg($filesFrom)
        );

        return $command;
    }

    /**
     * Search if file exists
     *
     * @param array $fileList
     * @return array
     */
    private function getBackupList($fileList): array
    {
        return array_filter($fileList, function ($pathPattern) {
            return $this->shellHelper->pathExists($pathPattern);
        });
    }

    /**
     * Create a temporary file with filelist
     *
     * @param array $fileList
     * @return string
     */
    private function createTempFile($fileList): string
    {
        $fileList = $this->expandFileList($fileList);
        $systemPath = $this->directoryList->getPath(DirectoryList::SYS_TMP);
        $tempFile = $systemPath . DIRECTORY_SEPARATOR . 'Tar' . $this->random->getRandomString(5);

        $file = $this->fileDriver->fileOpen($tempFile, "wb");
        $this->fileDriver->fileWrite($file, implode("\n", $fileList));
        $this->fileDriver->fileClose($file);

        return $tempFile;
    }

    /**
     * Expand the fileList
     *
     * @param array $fileList
     * @return array
     */
    private function expandFileList($fileList): array
    {
        $expandedList = array_map(function ($eachFile) {
            return $this->fileDriver->search($eachFile, $this->directoryList->getRoot());
        }, $fileList);

        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        return call_user_func_array('array_merge', $expandedList);
    }
}
