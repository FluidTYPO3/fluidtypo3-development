<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Core\Bootstrap as CoreBootstrap;

/**
 * Class NullLegacyPackageManager
 */
class NullLegacyPackageManager extends AbstractNullPackageManager
{

    /**
     * @param CoreBootstrap $bootstrap
     * @return void
     */
    public function initialize(CoreBootstrap $bootstrap)
    {
        $this->virtualPackages = Bootstrap::getInstance()->getVirtualExtensionKeys();
    }

}
