<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;

/**
 * Class Bootstrap
 */
class Bootstrap {

	const CACHE_NULL = 'null';
	const CACHE_PHP_NULL = 'phpnull';

	/**
	 * @var Container
	 */
	protected $objectContainer;

	/**
	 * @var array
	 */
	protected $cacheDefinitions = array(
		self::CACHE_NULL => array(
			'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
			'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\NullBackend'
		),
		self::CACHE_PHP_NULL => array(
			'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\PhpFrontend',
			'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\NullBackend'
		)
	);

	/**
	 * @return Bootstrap
	 */
	public static function getInstance() {
		$container = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\Container\\Container');
		$object = new self();
		$object->setObjectContainer($container);
		return $object;
	}

	/**
	 * @param ObjectContainer $container
	 * @return $this
	 */
	public function setObjectContainer(ObjectContainer $container) {
		$this->objectContainer = $container;
		return $this;
	}

	/**
	 * @param array $cacheDefinitions
	 * @return Bootstrap
	 */
	public static function initialize(array $cacheDefinitions) {
		$instance = self::getInstance();
		return $instance->initializeConstants()
			->initializeConfiguration()
			->initializeCaches($cacheDefinitions)
			->initializeCmsContext()
			->initializeReplacementImplementations();
	}

	/**
	 * @return $this
	 */
	public function initializeCmsContext() {
		\TYPO3\CMS\Core\Core\Bootstrap::getInstance()
			->baseSetup('typo3/')
			->initializeClassLoader()
			->initializeCachingFramework()
			->initializePackageManagement('FluidTYPO3\\Development\\NullPackageManager');
		return $this;
	}

	/**
	 * @return $this
	 */
	public function initializeConstants() {
		define('PATH_thisScript', realpath('vendor/typo3/cms/typo3/index.php'));
		define('TYPO3_MODE', 'BE');
		putenv('TYPO3_CONTEXT=Testing');
		return $this;
	}

	/**
	 * @return $this
	 */
	public function initializeConfiguration() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.?';
		$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash'] = array();
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['parser']['xlf'] = 'TYPO3\\CMS\\Core\\Localization\\Parser\\XliffParser';
		return $this;
	}

	/**
	 * @param array $caches
	 * @return $this
	 */
	public function initializeCaches(array $caches) {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'] = array();
		foreach ($caches as $cacheName => $cacheType) {
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName] = $this->cacheDefinitions[$cacheType];
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function initializeReplacementImplementations() {
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface',
			'FluidTYPO3\\Development\\DummyConfigurationManager'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface',
			'FluidTYPO3\\Vhs\\Tests\\Fixtures\\Classes\\DummyPersistenceManager'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\BackendInterface',
			'FluidTYPO3\\Development\\DummyPersistenceBackend'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\QueryInterface',
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface',
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings'
		);
		return $this;
	}

}
