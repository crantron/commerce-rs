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
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php;

/**
 * Helper class used for template rendering and file writing.
 *
 * @api
 */
class ModuleFileWriter
{
    /**
     * @param Php $templateEngine
     * @param File $file
     */
    public function __construct(
        private Php $templateEngine,
        private File $file
    ) {
    }

    /**
     * Creates output file. Creates directory recursively if the directory does not exist.
     *
     * @param BlockInterface $block
     * @param string $templatePath
     * @param string $filePath
     * @param array $dictionary
     * @return void
     * @throws FileSystemException
     */
    public function createFileFromTemplate(
        BlockInterface $block,
        string $templatePath,
        string $filePath,
        array $dictionary = []
    ): void {
        $content = $this->templateEngine->render($block, $templatePath, $dictionary);

        $this->createFile($filePath, $content);
    }

    /**
     * Creates output file. Creates directory recursively if the directory does not exist.
     *
     * @param string $path
     * @param string $content
     * @return void
     * @throws FileSystemException
     */
    public function createFile(string $path, string $content): void
    {
        $dir = $this->file->getParentDirectory($path);

        $this->file->createDirectory($dir);

        $resource = $this->file->fileOpen($path, 'w');
        $this->file->fileWrite($resource, $content);
        $this->file->fileClose($resource);
    }

    /**
     * Removes the directory at the input path if it exists.
     *
     * @param string $dirPath
     * @return void
     * @throws FileSystemException
     */
    public function deleteDirectory(string $dirPath): void
    {
        if ($this->file->isDirectory($dirPath)) {
            $this->file->deleteDirectory($dirPath);
        }
    }
}
