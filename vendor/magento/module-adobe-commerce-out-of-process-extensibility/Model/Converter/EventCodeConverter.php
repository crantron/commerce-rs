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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Model\Converter;

use Magento\AdobeCommerceOutOfProcessExtensibility\Model\Util\CaseConverter;

/**
 * Converts event codes to FQCN class
 */
class EventCodeConverter implements EventCodeConverterInterface
{
    /**
     * @param CaseConverter $caseConverter
     */
    public function __construct(private CaseConverter $caseConverter)
    {
    }

    /**
     * Convert event code to FQCN class name and removes method name from the path.
     * For example:
     * plugin.magento.theme.api.design_config_repository.save => Magento\Theme\Api\DesignConfigRepository
     *
     * @param string $eventCode
     * @return string
     */
    public function convertToFqcn(string $eventCode): string
    {
        $eventCodeParts = array_slice(explode('.', $eventCode), 0, -1);
        if (in_array($eventCodeParts[0], ['plugin', 'observer'])) {
            $eventCodeParts = array_slice($eventCodeParts, 1);
        }

        $class = '';
        foreach ($eventCodeParts as $part) {
            $class .= $this->caseConverter->snakeCaseToCamelCase($part) . '\\';
        }

        return rtrim($class, '\\');
    }

    /**
     * Converts class name to the event name.
     *
     * The slashes in the class name are replaced with dots and name parts are converted to underscore format.
     * For example:
     * Magento\Theme\Api\DesignConfigRepository => magento.theme.api.design_config_repository
     *
     * @param string $className
     * @param string $methodName
     * @return string
     */
    public function convertToEventName(string $className, string $methodName): string
    {
        $eventName = '';
        $namespaceParts = explode('\\', preg_replace('/Interface$/', '', $className));
        foreach ($namespaceParts as $namespacePart) {
            $eventName .= $this->caseConverter->camelCaseToSnakeCase($namespacePart) . '.';
        }

        return $eventName . $this->caseConverter->camelCaseToSnakeCase($methodName);
    }

    /**
     * Extract method name from event code.
     *
     * It is expected that the method name be the last dot-separated part of the event name.
     * Underscored method name is converted to the camel case format.
     * For example:
     * get_product_list => getProductList
     *
     * @param string $eventCode
     * @return string
     */
    public function extractMethodName(string $eventCode): string
    {
        $eventCodeParts = explode('.', $eventCode);

        return lcfirst($this->caseConverter->snakeCaseToCamelCase(end($eventCodeParts)));
    }
}
