<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbBackend;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Class NullPersistenceBackend
 */
class NullPersistenceBackend extends Typo3DbBackend {

	/**
	 * @param PersistenceManagerInterface $manager
	 * @return void
	 */
	public function setPersistenceManager(PersistenceManagerInterface $manager) {

	}

}
