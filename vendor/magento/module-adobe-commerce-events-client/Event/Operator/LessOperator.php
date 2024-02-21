<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\Operator;

/**
 * Verifies that event data value is less than the value in the rule.
 */
class LessOperator implements OperatorInterface
{
    /**
     * Verifies that event data value is less than the value in the rule.
     *
     * @param string $ruleValue
     * @param mixed $fieldValue
     * @return bool
     */
    public function verify(string $ruleValue, $fieldValue): bool
    {
        return floatval($fieldValue) < floatval($ruleValue);
    }
}
