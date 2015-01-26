<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Package\FailsafePackageManager;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\PackageInterface;

/**
 * Class NullPackageManager
 */
class NullPackageManager extends FailsafePackageManager {

	/**
	 * @var array
	 */
	protected $packageStatesConfiguration = array(
		'packages' => array()
	);

	/**
	 * @param Bootstrap $bootstrap
	 */
	public function initialize(Bootstrap $bootstrap) {
		$this->bootstrap = $bootstrap;
	}

	/**
	 * @param string $packageKey
	 * @return boolean
	 */
	public function isPackageActive($packageKey) {
		return ('vhs' === $packageKey);
	}

	/**
	 * @param string $packageKey
	 * @return boolean
	 */
	public function isPackageAvailable($packageKey) {
		return ('vhs' === $packageKey);
	}

	/**
	 * @param string $packageKey
	 * @return PackageInterface
	 */
	public function getPackage($packageKey) {
		$path = realpath(dirname(__FILE__) . '/../../..') . '/';
		$package = new Package($this, $packageKey, $path, $path . 'Classes/');
		return $package;
	}

}
