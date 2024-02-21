<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Support\Test\Unit\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Math\Random;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Support\Console\Command\BackupCodeCommand;
use Magento\Support\Helper\Shell;
use Magento\Support\Model\Backup\Config;

class BackupCodeCommandTest extends TestCase
{
    /**
     * Application Root
     */
    public const APP_ROOT_PATH = '/app/root/';

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Shell|MockObject
     */
    protected $shellHelper;

    /**
     * @var Config|MockObject
     */
    protected $backupConfig;

    /**
     * @var File|MockObject
     */
    protected $fileDriver;

    /**
     * @var Random|MockObject
     */
    protected $random;

    /**
     * @var BackupCodeCommand
     */
    protected $model;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->shellHelper = $this->getMockBuilder(Shell::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backupConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileDriver = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->random = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManagerHelper->getObject(
            BackupCodeCommand::class,
            [
                'shellHelper' => $this->shellHelper,
                'backupConfig' => $this->backupConfig,
                'outputPath' => 'var/output/path',
                'backupName' => 'backup_name',
                'random' => $this->random,
                'fileDriver' => $this->fileDriver,
            ]
        );
    }

    /**
     * @dataProvider existingBackupListProvider
     * @param $existingBackupList
     * @return void
     * @throws \Exception
     */
    public function testExecute($existingBackupList): void
    {
        $tmpname = 'TEMPEXP';
        $tmppath = DIRECTORY_SEPARATOR . 'Tar' . $tmpname;

        $expectedBackupCmd = 'nice -n 15 tar -czhf var/output/path/backup_name.tar.gz '
            . '--files-from ' . escapeshellarg($tmppath);
        $inputInterface = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $outputInterface = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->shellHelper->expects($this->any())->method('setRootWorkingDirectory');
        $this->shellHelper->expects($this->any())->method('getUtility')->willReturnMap([
            ['nice', 'nice'],
            ['tar', 'tar']
        ]);
        $this->shellHelper->expects($this->atLeastOnce())->method('execute')->with($expectedBackupCmd)
            ->willReturn($expectedBackupCmd);
        $this->shellHelper->method('pathExists')
            ->willReturnCallback(function ($param) use ($existingBackupList) {
                if (in_array($param, $existingBackupList)) {
                    return true;
                }
                return false;
            });
        $this->backupConfig->expects($this->any())->method('getBackupFileExtension')->with('code')
            ->willReturn('tar.gz');
        $this->random->expects($this->any())->method('getRandomString')->with('5')
            ->willReturn($tmpname);
        $this->fileDriver->expects(self::atLeastOnce())
            ->method('search')
            ->willReturnCallback(
                function ($arg1) {
                    return [$arg1];
                }
            );
        $outputInterface
            ->method('writeln')
            ->withConsecutive(
                [$expectedBackupCmd],
                [$expectedBackupCmd],
                ['Code dump was created successfully']
            );

        $this->model->run($inputInterface, $outputInterface);
    }

    /**
     * @return array
     */
    public function existingBackupListProvider(): array
    {
        return [
            [['vendor']],
            [['app', 'bin', 'composer.*']],
            [['bin', 'vendor']],
            [
                [
                    'app',
                    'bin',
                    'composer.*',
                    'dev',
                    '*.php',
                    'lib',
                    'pub/*.php'
                ]
            ]
        ];
    }
}
