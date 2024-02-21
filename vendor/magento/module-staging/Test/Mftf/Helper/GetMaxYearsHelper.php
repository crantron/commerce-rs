<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Staging\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\Staging\Model\VersionManager;

/**
 * Class for MFTF helpers for Staging module.
 */
class GetMaxYearsHelper extends Helper
{
    /**
     * Get max years.
     *
     * @return float
     *
     */
    public function getMaxYears(): float
    {
        $currentDateTime = new \DateTime();
        $diff = abs(VersionManager::MAX_VERSION - $currentDateTime->getTimestamp());

        return ceil($diff / (365*60*60*24));
    }
}
