<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Event\EventStorageWriter;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception is thrown in a case of failed saving event data to the storage.
 */
class EventStorageException extends LocalizedException
{

}
