<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Controller\Adminhtml\Connection;

use GuzzleHttp\Psr7\Response;
use Magento\AdobeCommerceEventsClient\Controller\Adminhtml\Connection\TestConnection;
use Magento\AdobeCommerceEventsClient\Event\Client;
use Magento\AdobeCommerceEventsClient\Event\InvalidConfigurationException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for @see TestConnection class
 */
class TestConnectionTest extends TestCase
{
    /**
     * @var TestConnection
     */
    private TestConnection $testConnection;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $jsonFactoryMock;

    /**
     * @var Client|MockObject
     */
    private $clientMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->jsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->clientMock = $this->createMock(Client::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->testConnection = new TestConnection(
            $this->contextMock,
            $this->jsonFactoryMock,
            $this->clientMock,
            $loggerMock
        );
    }

    /**
     * @return void
     * @throws NotFoundException
     * @dataProvider executeProvider
     */
    public function testExecute(int $responseCode): void
    {
        $this->clientMock->expects(self::once())
            ->method('sendEventDataBatch')
            ->willReturn(new Response($responseCode, [], ''));

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonMock->expects(self::once())
            ->method('setData')
            ->with(['status' => $responseCode]);

        $this->jsonFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $result = $this->testConnection->execute();

        $this->assertEquals($resultJsonMock, $result);
    }

    /**
     * @return array
     */
    public function executeProvider(): array
    {
        return [
            [200],
            [401],
            [500]
        ];
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testExecuteException(): void
    {
        $this->clientMock->expects(self::once())
            ->method('sendEventDataBatch')
            ->willThrowException(new InvalidConfigurationException(__()));

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $result = $this->testConnection->execute();

        $this->assertEquals($resultJsonMock, $result);
    }
}
