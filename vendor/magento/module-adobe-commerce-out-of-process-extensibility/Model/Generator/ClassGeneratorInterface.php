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

/**
 * Creates PHP classes for a generated module.
 *
 * @api
 */
interface ClassGeneratorInterface
{
    /**
     * Runs PHP class generation.
     *
     * @param ModuleBlock $moduleBlock
     * @param string $path
     * @return void
     * @throws FileSystemException
     */
    public function generateClasses(ModuleBlock $moduleBlock, string $path): void;

    /**
     * Returns the path to the template files for the module.
     *
     * @return string
     */
    public function getTemplatesPath(): string;

    /**
     * Returns supported php versions for the generated classes.
     *
     * @return string
     */
    public function getPhpVersion(): string;
}
