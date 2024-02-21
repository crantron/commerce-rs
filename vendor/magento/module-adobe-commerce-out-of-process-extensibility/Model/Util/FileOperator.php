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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Helper class for filesystem operations
 */
class FileOperator
{
    /**
     * Returns recursive directory iterator for given path with given pattern for files to find
     *
     * @param string $dir
     * @param array $regexPatterns List of regex file pattern to find
     * @return RegexIterator
     */
    public function getRecursiveFileIterator(
        string $dir,
        array $regexPatterns
    ): RegexIterator {
        $dirIterator = new RecursiveDirectoryIterator($dir);
        $recursiveDirIterator = new RecursiveIteratorIterator($dirIterator);
        foreach ($regexPatterns as $pattern) {
            if (!empty($pattern)) {
                $recursiveDirIterator = new RegexIterator($recursiveDirIterator, $pattern, RegexIterator::MATCH);
            }
        }

        return $recursiveDirIterator;
    }

    /**
     * Factory method for creating DirectoryIterator object
     *
     * @param string $dir
     * @return DirectoryIterator
     */
    public function getDirectoryIterator(string $dir): DirectoryIterator
    {
        return new DirectoryIterator($dir);
    }
}
