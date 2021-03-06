<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Class NullPackageManager
 */
class NullPackageManager extends AbstractNullPackageManager
{

    /**
     * @return void
     */
    public function initialize()
    {
        $this->virtualPackages = Bootstrap::getInstance()->getVirtualExtensionKeys();
    }

}
