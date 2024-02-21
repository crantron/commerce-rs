<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event;

use Magento\AdobeCommerceEventsClient\Event\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see Config class
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->config = new Config($this->scopeConfigMock);
    }

    public function testGetMerchantId()
    {
        $merchantId = 'demo';
        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with('adobe_io_events/eventing/merchant_id')
            ->willReturn($merchantId);

        self::assertEquals(
            $merchantId,
            $this->config->getMerchantId()
        );
    }

    public function testGetEnvironmentId()
    {
        $environmentId = 'demo';
        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with('adobe_io_events/eventing/env_id')
            ->willReturn($environmentId);

        self::assertEquals(
            $environmentId,
            $this->config->getEnvironmentId()
        );
    }

    public function testIsEnabled()
    {
        $isEnabled = true;
        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with('adobe_io_events/eventing/enabled')
            ->willReturn($isEnabled);

        self::assertEquals(
            $isEnabled,
            $this->config->isEnabled()
        );
    }

    public function testGetInstanceId()
    {
        $instanceId = 'demo';
        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with('adobe_io_events/integration/instance_id')
            ->willReturn($instanceId);

        self::assertEquals(
            $instanceId,
            $this->config->getInstanceId()
        );
    }

    /**
     * @param string $envType
     * @param string $envUrl
     * @return void
     *
     * @dataProvider verifyDataProvider
     */
    public function testGetStageEndPointUrl(string $envType, string $envUrl): void
    {
        $this->scopeConfigMock->expects(self::atLeastOnce())
            ->method('getValue')
            ->with('adobe_io_events/integration/adobe_io_environment')
            ->willReturn($envType);

        self::assertEquals(
            $envUrl,
            $this->config->getEndpointUrl()
        );
    }

    public function testGetDevEndPointUrl()
    {
        $devEndPoint = 'https://commerce-eventing-dev.adobe.io';
        $devEnv = 'development';

        $this->scopeConfigMock->expects(self::exactly(2))
            ->method('getValue')
            ->with('adobe_io_events/integration/adobe_io_environment')
            ->willReturn($devEnv);

        self::assertEquals(
            $devEndPoint,
            $this->config->getEndpointUrl()
        );
    }

    /**
     * @return array[]
     */
    public function verifyDataProvider(): array
    {
        return [
            ['staging','https://commerce-eventing-stage.adobe.io'],
            ['development','https://commerce-eventing-dev.adobe.io'],
            ['test','https://commerce-eventing.adobe.io']
        ];
    }
}
