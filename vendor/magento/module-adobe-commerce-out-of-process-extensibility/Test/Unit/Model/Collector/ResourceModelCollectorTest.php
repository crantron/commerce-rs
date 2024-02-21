<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Test\Unit\Model\Collector;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\EventMethodCollector;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\NameFetcher;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Collector\ResourceModelCollector;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util\FileOperator;
use Magento\Framework\App\Utility\ReflectionClassFactory;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use RegexIterator;

/**
 * Tests for the ApiServiceCollector class.
 */
class ResourceModelCollectorTest extends AbstractCollectorTest
{
    /**
     * @var ResourceModelCollector
     */
    private ResourceModelCollector $resourceModelCollector;

    /**
     * @var DriverInterface|MockObject
     */
    private $filesystemMock;

    /**
     * @var FileOperator|MockObject
     */
    private $fileOperatorMock;

    /**
     * @var NameFetcher|MockObject
     */
    public $nameFetcherMock;

    /**
     * @var EventMethodCollector|MockObject
     */
    private $eventMethodCollectorMock;

    /**
     * @var ReflectionClassFactory|MockObject
     */
    private $reflectionClassFactoryMock;

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(DriverInterface::class);
        $this->fileOperatorMock = $this->createMock(FileOperator::class);
        $this->nameFetcherMock = $this->createMock(NameFetcher::class);
        $this->eventMethodCollectorMock = $this->createMock(EventMethodCollector::class);
        $this->reflectionClassFactoryMock = $this->createMock(ReflectionClassFactory::class);

        $this->resourceModelCollector = new ResourceModelCollector(
            $this->filesystemMock,
            $this->fileOperatorMock,
            $this->nameFetcherMock,
            $this->eventMethodCollectorMock,
            $this->reflectionClassFactoryMock,
        );
    }

    public function testCollectApiDirectoryNotExists(): void
    {
        $this->filesystemMock->expects(self::once())
            ->method('getRealPath')
            ->with('/path/to/dir/Model/ResourceModel')
            ->willReturn('/realpath/to/dir');
        $this->filesystemMock->expects(self::once())
            ->method('isDirectory')
            ->with('/realpath/to/dir')
            ->willReturn(false);
        $this->fileOperatorMock->expects(self::never())
            ->method('getDirectoryIterator');

        self::assertEquals([], $this->resourceModelCollector->collect('/path/to/dir'));
    }

    public function testCollect(): void
    {
        $fileMockOne = $this->createFileInfoMock('php', false);
        $fileMockTwo = $this->createFileInfoMock('php', true);
        $this->filesystemMock->expects(self::once())
            ->method('getRealPath')
            ->with('/path/to/dir/Model/ResourceModel')
            ->willReturn('/realpath/to/dir');
        $this->filesystemMock->expects(self::once())
            ->method('isDirectory')
            ->with('/realpath/to/dir')
            ->willReturn(true);
        $regexIteratorMock = $this->createMock(RegexIterator::class);
        $this->fileOperatorMock->expects(self::once())
            ->method('getRecursiveFileIterator')
            ->willReturn($this->mockIterator($regexIteratorMock, [$fileMockOne, $fileMockTwo]));
        $this->nameFetcherMock->expects(self::once())
            ->method('getNameFromFile')
            ->with($fileMockOne)
            ->willReturn('Class\Name');
        $reflectionClassMock = $this->createMock(ReflectionClass::class);
        $reflectionClassMock->expects(self::once())
            ->method('isSubclassOf')
            ->with(AbstractResource::class)
            ->willReturn(true);
        $this->reflectionClassFactoryMock->expects(self::once())
            ->method('create')
            ->with('Class\Name')
            ->willReturn($reflectionClassMock);
        $this->eventMethodCollectorMock->expects(self::once())
            ->method('collect')
            ->with($reflectionClassMock)
            ->willReturn(['event1' => 'data']);

        self::assertEquals(['event1' => 'data'], $this->resourceModelCollector->collect('/path/to/dir'));
    }
}
