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

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\View\Element\BlockFactory;

/**
 * Generates Magento module skeleton: composer.json, registration.php, etc/module.xml
 */
class ModuleGenerator
{
    /**
     * @var array
     */
    private array $composerSkeleton = [
        "name" => null,
        "description" => "N/A",
        "config" => ["sort-packages" => true],
        "require" => null,
        "type" => "magento2-module",
        "version" => null,
        "license" => ["OSL-3.0", "AFL-3.0"],
        "autoload" => [
            "files" => ["registration.php"],
            "psr-4" => null
        ]
    ];

    /**
     * @var string
     */
    private string $outputDir;

    /**
     * @param BlockFactory $blockFactory
     * @param ModuleFileWriter $moduleFileWriter
     * @param ClassGeneratorInterface $classGenerator
     */
    public function __construct(
        private BlockFactory $blockFactory,
        private ModuleFileWriter $moduleFileWriter,
        private ClassGeneratorInterface $classGenerator
    ) {
    }

    /**
     * Generates module skeleton including `composer.json`, `registration.php`, `etc/module.xml`.
     *
     * @param Module $module
     * @param string|null $version
     * @return void
     * @throws FileSystemException
     */
    public function run(
        Module $module,
        ?string $version,
    ): void {
        $path = $this->getPath($module);
        $this->moduleFileWriter->deleteDirectory($path);

        /** @var ModuleBlock $moduleBlock */
        $moduleBlock = $this->blockFactory->createBlock(ModuleBlock::class, [
            'module' => $module
        ]);

        $fileMap = [
            'registrationPhp.phtml' => 'registration.php',
            'readmeMd.phtml' => 'README.md',
            'moduleXml.phtml' => 'etc/module.xml',
            'license.phtml' => 'LICENSE.txt',
            'licenseAfl.phtml' => 'LICENSE_AFL.txt',
            'di.phtml' => 'etc/di.xml',
        ];

        foreach ($fileMap as $templateFile => $targetFilePath) {
            $this->moduleFileWriter->createFileFromTemplate(
                $moduleBlock,
                $this->classGenerator->getTemplatesPath() . DIRECTORY_SEPARATOR . $templateFile,
                $path . DIRECTORY_SEPARATOR . $targetFilePath,
            );
        }

        $module = $moduleBlock->getModule();
        $this->generateComposer(
            $module->getVendor(),
            $module->getName(),
            $path,
            $module->getDependencies(),
            $version
        );

        $this->classGenerator->generateClasses($moduleBlock, $path);
    }

    /**
     * Returns module base path.
     *
     * @param Module $module
     * @return string
     */
    private function getPath(Module $module): string
    {
        return $this->outputDir . '/' . $module->getVendor() . '/' . $module->getName();
    }

    /**
     * Generates composer json file.
     *
     * @param string $vendor
     * @param string $module
     * @param string $path
     * @param array $dependencies
     * @param string|null $version
     * @return void
     * @throws FileSystemException
     */
    private function generateComposer(
        string $vendor,
        string $module,
        string $path,
        array $dependencies,
        ?string $version
    ): void {
        $name = $this->getModuleName($module);
        $version = $version ?? '0.0.1';

        $composerContent = $this->composerSkeleton;
        $composerContent['name'] = strtolower($vendor) . '/module-' . $name;
        $composerContent['version'] = $version;
        $composerContent['autoload']['psr-4'] = [$vendor . '\\' . $module . '\\' => ""];
        $composerContent['require'] = [
            'php' => $this->classGenerator->getPhpVersion(),
            'magento/framework' => '*'
        ];
        $composerContent['suggest'] = $this->generateSuggestedList($dependencies);

        $composerContent = json_encode($composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->moduleFileWriter->createFile($path . '/composer.json', $composerContent . "\n");
    }

    /**
     * Sets output directory where the module will be generated.
     *
     * @param string $outputDir
     * @return void
     */
    public function setOutputDir(string $outputDir): void
    {
        $this->outputDir = $outputDir;
    }

    /**
     * Generates the suggest section for a generated composer.json file given an array containing module dependencies.
     *
     * @param array $dependencies
     * @return string[]
     */
    private function generateSuggestedList(array $dependencies): array
    {
        $suggested = [];

        foreach ($dependencies as $dependency) {
            $suggested[$dependency['packageName']] = '*';
        }

        return $suggested;
    }

    /**
     * Converts camel case name to hyphen separated lower case words.
     *
     * @param string $value
     * @return string
     */
    private function getModuleName(string $value): string
    {
        $pattern = '/(?:^|[A-Z])[a-z]+/';
        preg_match_all($pattern, $value, $matches);
        return strtolower(implode('-', $matches[0]));
    }
}
