<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model\Config\Env;

/**
 * Production environment configurations
 */
class Production implements EnvironmentConfigInterface
{
    private const API_URL_PROD = 'https://api.adobe.io';
    private const IMS_URL_PROD = 'https://ims-na1.adobelogin.com/ims/token/v2';
    private const IMS_JWT_URL_PROD = 'https://adobeid-na1.services.adobe.com/ims/exchange/jwt';
    private const IMS_BASE_URL_JWT_TOKEN_PROD = 'https://ims-na1.adobelogin.com';

    /**
     * @inheritDoc
     */
    public function getAdobeApiUrl(): string
    {
        return self::API_URL_PROD;
    }

    /**
     * @inheritDoc
     */
    public function getImsUrl(): string
    {
        return self::IMS_URL_PROD;
    }

    /**
     * @inheritDoc
     */
    public function getImsJwtUrl(): string
    {
        return self::IMS_JWT_URL_PROD;
    }

    /**
     * @inheritDoc
     */
    public function getImsJwtToken(): string
    {
        return self::IMS_BASE_URL_JWT_TOKEN_PROD;
    }
}
