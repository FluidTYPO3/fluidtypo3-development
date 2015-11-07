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
use TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface;

/**
 * Class NullPersistenceBackend
 */
class NullPersistenceBackend extends Typo3DbBackend implements BackendInterface {

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objects
	 * @return void
	 */
	public function setAggregateRootObjects(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $objects) {

	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $entities
	 * @return void
	 */
	public function setDeletedEntities(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $entities) {

	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $entities
	 * @return void
	 */
	public function setChangedEntities(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $entities) {

	}

	/**
	 * @return void
	 */
	public function commit() {

	}

	/**
	 * @param string $identifier
	 * @param string $className
	 * @return void
	 */
	public function getObjectByIdentifier($identifier, $className) {

	}

	/**
	 * @param object $object
	 * @return boolean
	 */
	public function isNewObject($object) {

	}

	/**
	 * @param PersistenceManagerInterface $manager
	 * @return void
	 */
	public function setPersistenceManager(PersistenceManagerInterface $manager) {

	}

	/**
	 * @param object $object
	 * @return string
	 */
	public function getIdentifierByObject($object) {
		return spl_object_hash($object);
	}

}
