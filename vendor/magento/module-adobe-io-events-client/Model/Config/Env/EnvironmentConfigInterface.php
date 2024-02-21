<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIoEventsClient\Model\Config\Env;

/**
 * Interface for connection configuration details based on environment type
 */
interface EnvironmentConfigInterface
{
    /**
     * Get the base url for the current environment
     *
     * @return string
     */
    public function getAdobeApiUrl(): string;

    /**
     * Get the IMS url for the current environment
     *
     * @return string
     */
    public function getImsUrl(): string;

    /**
     * Get the IMS JWT url for the current environment
     *
     * @return string
     */
    public function getImsJwtUrl(): string;

    /**
     * Get the IMS JWT token url for the current environment
     *
     * @return string
     */
    public function getImsJwtToken(): string;
}
