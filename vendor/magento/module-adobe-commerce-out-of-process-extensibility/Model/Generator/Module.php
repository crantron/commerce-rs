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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator;

/**
 * Stores information needed for module files generation
 */
class Module
{
    /**
     * @var string
     */
    private string $vendor;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var array
     */
    private array $plugins = [];

    /**
     * @var array
     */
    private array $observerEventPlugin = [];

    /**
     * @var array
     */
    private array $observerEvents = [];

    /**
     * @var array
     */
    private array $dependencies = [];

    /**
     * @param string $vendor
     * @param string $name
     */
    public function __construct(string $vendor, string $name)
    {
        $this->vendor = $vendor;
        $this->name = $name;
    }

    /**
     * Sets list of plugins.
     *
     * @param array $plugins
     * @return void
     */
    public function setPlugins(array $plugins): void
    {
        $this->plugins = $plugins;
    }

    /**
     * Sets observer event plugin.
     *
     * @param array $observerEventPlugin
     * @return void
     */
    public function setObserverEventPlugin(array $observerEventPlugin): void
    {
        $this->observerEventPlugin = $observerEventPlugin;
    }

    /**
     * Returns module name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns module vendor.
     *
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * Returns list of plugins.
     *
     * @return array
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Returns observer event plugin.
     *
     * @return array
     */
    public function getObserverEventPlugin(): array
    {
        return $this->observerEventPlugin;
    }

    /**
     * Sets list of observer events.
     *
     * @param array $observerEvents
     * @return void
     */
    public function setObserverEvents(array $observerEvents): void
    {
        $this->observerEvents = $observerEvents;
    }

    /**
     * Returns list of observer events
     *
     * @return array
     */
    public function getObserverEvents(): array
    {
        return $this->observerEvents;
    }

    /**
     * Sets list of dependencies.
     *
     * @param array $dependencies
     * @return void
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Returns list of module dependencies
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
