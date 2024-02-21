<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsGenerator\Generator;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\ClassGeneratorInterface;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\ModuleBlock;
use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Generator\ModuleFileWriter;
use Magento\Framework\Exception\FileSystemException;

/**
 * Generates php classes to be included in a generated eventing module.
 */
class EventingClassGenerator implements ClassGeneratorInterface
{
    public const MODULE_VENDOR = 'Magento';
    public const MODULE_NAME = 'AdobeCommerceEvents';

    public const MODULE_PLUGIN_SPACE = 'Plugin';

    private const PLUGIN_API_INTERFACE_TPL = 'pluginApiInterface.phtml';
    private const PLUGIN_RESOURCE_MODEL_TPL = 'pluginResourceModel.phtml';
    private const OBSERVER_EVENT_PLUGIN_TPL = 'observerEventPlugin.phtml';

    private const PHP_REQ = '~7.4.0||~8.1.0||~8.2.0';

    /**
     * @param ModuleFileWriter $moduleFileWriter
     * @param string|null $templatesPath
     */
    public function __construct(
        private ModuleFileWriter $moduleFileWriter,
        private ?string $templatesPath = null
    ) {
        $this->templatesPath = $templatesPath ?: __DIR__ . '/../templates';
    }

    /**
     * @inheritDoc
     */
    public function generateClasses(ModuleBlock $moduleBlock, string $path): void
    {
        $this->generatePluginList($moduleBlock, $path);
        $this->generateObserverList($moduleBlock, $path);
        $this->generatePlugins($moduleBlock, $path);
        $this->generateObserverEventPlugin($moduleBlock, $path);
    }

    /**
     * @inheritDoc
     */
    public function getTemplatesPath(): string
    {
        return $this->templatesPath;
    }

    /**
     * @inheritDoc
     */
    public function getPhpVersion(): string
    {
        return self::PHP_REQ;
    }

    /**
     * Generates plugins for api interfaces
     *
     * @param ModuleBlock $moduleBlock
     * @param string $basePath
     * @return void
     * @throws FileSystemException
     */
    private function generatePlugins(ModuleBlock $moduleBlock, string $basePath): void
    {
        $module = $moduleBlock->getModule();
        foreach ($module->getPlugins() as $plugin) {
            $template = $plugin['type'] == PluginConverter::TYPE_RESOURCE_MODEL ?
                self::PLUGIN_RESOURCE_MODEL_TPL : self::PLUGIN_API_INTERFACE_TPL;

            $this->moduleFileWriter->createFileFromTemplate(
                $moduleBlock,
                $this->templatesPath . DIRECTORY_SEPARATOR . $template,
                $basePath . $plugin['path'],
                $plugin
            );
        }
    }

    /**
     * Generates plugin for emitting observer event data
     *
     * @param ModuleBlock $moduleBlock
     * @param string $basePath
     * @return void
     * @throws FileSystemException
     */
    private function generateObserverEventPlugin(ModuleBlock $moduleBlock, string $basePath): void
    {
        $module = $moduleBlock->getModule();
        $plugin = $module->getObserverEventPlugin();

        if (!empty($plugin)) {
            $this->moduleFileWriter->createFileFromTemplate(
                $moduleBlock,
                $this->templatesPath . DIRECTORY_SEPARATOR . self::OBSERVER_EVENT_PLUGIN_TPL,
                $basePath . $plugin['path'],
                $plugin
            );
        }
    }

    /**
     * Generates a class with a list of subscribed plugin events.
     *
     * @param ModuleBlock $moduleBlock
     * @param string $basePath
     * @return void
     * @throws FileSystemException
     */
    private function generatePluginList(ModuleBlock $moduleBlock, string $basePath): void
    {
        $this->moduleFileWriter->createFileFromTemplate(
            $moduleBlock,
            $this->templatesPath . DIRECTORY_SEPARATOR . 'eventCodeList.phtml',
            $basePath . '/EventCode/Plugin.php',
            [
                'name' => 'Plugin',
                'plugins' => $moduleBlock->getModule()->getPlugins()
            ]
        );
    }

    /**
     * Generates a class with a list of all subscribed observer events.
     *
     * @param ModuleBlock $moduleBlock
     * @param string $basePath
     * @return void
     * @throws FileSystemException
     */
    private function generateObserverList(ModuleBlock $moduleBlock, string $basePath): void
    {
        if (empty($moduleBlock->getModule()->getObserverEvents())) {
            return;
        }

        $this->moduleFileWriter->createFileFromTemplate(
            $moduleBlock,
            $this->templatesPath . DIRECTORY_SEPARATOR . 'eventCodeList.phtml',
            $basePath . '/EventCode/Observer.php',
            [
                'name' => 'Observer',
                'observers' => $moduleBlock->getModule()->getObserverEvents()
            ]
        );
    }
}
