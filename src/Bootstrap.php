<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use Composer\Autoload\ClassLoader;

/**
 * Class Bootstrap
 */
class Bootstrap extends \TYPO3\CMS\Core\Core\Bootstrap {

	const CACHE_NULL = 'null';
	const CACHE_PHP_NULL = 'phpnull';
	const CACHE_MEMORY = 'memory';

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
		),
		self::CACHE_MEMORY => array(
			'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
			'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\TransientMemoryBackend'
		)
	);

	/**
	 * @var array
	 */
	protected static $virtualExtensionKeys = array();

	/**
	 * @param Container $container
	 * @return $this
	 */
	public function setObjectContainer(Container $container) {
		$this->objectContainer = $container;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getVirtualExtensionKeys() {
		return static::$virtualExtensionKeys;
	}

	/**
	 * @param array $virtualExtensionKeys
	 * @return void
	 */
	public function setVirtualExtensionKeys(array $virtualExtensionKeys) {
		static::$virtualExtensionKeys = $virtualExtensionKeys;
		return $this;
	}

	/**
	 * @param ClassLoader $classLoader
	 * @param array $cacheDefinitions
	 * @param array $virtualExtensionKeys
	 * @return Bootstrap
	 */
	public static function initialize(ClassLoader $classLoader, array $cacheDefinitions, array $virtualExtensionKeys = array()) {
	    $packageManagerClassName = NullPackageManager::class;
	    $packageManagerClassReflection = new \ReflectionClass(PackageManager::class);
	    $initializeMethodReflection = $packageManagerClassReflection->getMethod('initialize');
	    if ($initializeMethodReflection->getNumberOfRequiredParameters() > 0) {
	        $packageManagerClassName = NullLegacyPackageManager::class;
        }
		$instance = static::getInstance();
        $instance->applicationContext = new ApplicationContext('Testing');
		if (method_exists($instance, 'setRequestType')) {
			$instance->setRequestType(1);
		}
		$instance->initializeClassLoader($classLoader);
		$instance->initializeConstants()
			->initializeClassLoader($classLoader)
			->setVirtualExtensionKeys($virtualExtensionKeys)
			->initializeConfiguration()
			->initializeCaches($cacheDefinitions)
            ->initializeCachingFramework()
            ->defineLoggingAndExceptionConstants()
            ->baseSetup(0)
            ->initializePackageManagement($packageManagerClassName)
            ->initializeObjectContainer()
			->initializeReplacementImplementations()
        ;

		return $instance;
	}

    /**
     * @return $this
     */
	public function initializeObjectContainer()
    {
        $container = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\Container\\Container');
        $this->setObjectContainer($container);
        return $this;
    }

	/**
	 * @return $this
	 */
	public function initializeConstants() {
        define('PATH_site', rtrim(getenv('TYPO3_PATH_ROOT'), '/') . '/');
        define('PATH_thisScript', PATH_site . '/typo3/index.php');
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
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_DLOG'] = FALSE;
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_exceptionDLOG'] = FALSE;
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_errorDLOG'] = FALSE;
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
		// this next line has a single purpose: loading the class so that the reflection framework
		// can read the class on php7. On this particular version of php, reflecting a class does
		// not trigger class loading and on php 5.4 (which some dependencies still test on) we
		// cannot use the ::class notation here which would *also* solve the problem. So we cheat
		// and create a throwaway instance here which loads the class and keeps it loaded.
		new NullConfigurationManager();
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface',
			'FluidTYPO3\\Development\\NullConfigurationManager'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface',
			'FluidTYPO3\\Development\\NullPersistenceManager'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\BackendInterface',
			'FluidTYPO3\\Development\\NullPersistenceBackend'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\QueryInterface',
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\QueryResultInterface',
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QueryResult'
		);
		$this->objectContainer->registerImplementation(
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface',
			'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings'
		);
		return $this;
	}

}
