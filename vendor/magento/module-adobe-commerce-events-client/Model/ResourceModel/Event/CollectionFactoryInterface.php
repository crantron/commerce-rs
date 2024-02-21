<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Model\ResourceModel\Event;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Interface for creating instance of event collection
 */
interface CollectionFactoryInterface
{
    /**
     * Create collection instance with specified parameters
     *
     * @param array $data
     * @return AbstractCollection
     */
    public function create(array $data = []): AbstractCollection;
}
