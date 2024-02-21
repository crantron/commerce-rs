<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Model\ResourceModel\Event;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;

/**
 * Event collection factory
 */
class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        private ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function create(array $data = []): AbstractCollection
    {
        return $this->objectManager->create(Collection::class, $data);
    }
}
