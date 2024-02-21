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

namespace Magento\AdobeCommerceOutOfProcessExtensibility\Test\Unit\Model\Util\_files;

use Magento\Framework\DataObject;

class SampleClass extends DataObject
{
    public function isAvailable(): bool
    {
        return $this->_getData('available');
    }

    public function getItemName(): string
    {
        return $this->getData('item_name');
    }

    public function getCount($index): mixed
    {
        return $this->data['count'][$index];
    }

    public function resetName(): void
    {
        $this->data['item_name'] = '';
    }

    public function getQuantity(): int
    {
        return $this->data['quantity'];
    }

    public function getPrice(): float
    {
        return $this->_data['price'];
    }
}
