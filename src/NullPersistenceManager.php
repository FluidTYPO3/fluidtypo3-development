<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class NullPersistenceManager
 */
class NullPersistenceManager extends PersistenceManager {

	/**
	 * @return void
	 */
	public function __construct() {
		$this->backend = new NullPersistenceBackend();
		parent::__construct();
	}

	/**
	 * @return BackendInterface
	 */
	public function getBackend() {
		return $this->backend;
	}

}
