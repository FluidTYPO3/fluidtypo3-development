<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Core\Localization\Parser\XliffParser;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Package\DependencyResolver;
use TYPO3\CMS\Core\Package\FailsafePackageManager;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\ServiceProvider;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use Composer\Autoload\ClassLoader;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * Class Bootstrap
 */
class Bootstrap extends \TYPO3\CMS\Core\Core\Bootstrap
{
    const CACHE_NULL = 'null';
    const CACHE_PHP_NULL = 'phpnull';
    const CACHE_MEMORY = 'memory';

    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var Container
     */
    protected $objectContainer;

    /**
     * @var ContainerInterface
     */
    protected $container;

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

    public static function getInstance(): self
    {
        return static::$instance ?? (static::$instance = new static('Testing'));
    }

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
    public static function initialize(ClassLoader $classLoader, array $cacheDefinitions, array $virtualExtensionKeys = array())
    {
        $requestId = substr(md5(StringUtility::getUniqueId()), 0, 13);

        $instance = static::getInstance();
        $instance->applicationContext = new ApplicationContext('Testing');
        if (method_exists($instance, 'setRequestType')) {
            $instance->setRequestType(1);
        } elseif (class_exists(SystemEnvironmentBuilder::class) && defined(SystemEnvironmentBuilder::class . '::REQUESTTYPE_CLI')) {
            SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
        } else {

        }
        $instance->initializeClassLoader($classLoader);
        $instance->initializeConstants();
        $instance->setVirtualExtensionKeys($virtualExtensionKeys);
        $instance->initializeConfiguration();
        $instance->initializeCaches($cacheDefinitions);

        $instance->initializeObjectContainer()->initializeReplacementImplementations();

        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
        if (method_exists($instance, 'initializeCachingFramework')) {
            $instance->initializeCachingFramework();
        }

        if (($packageManagerClassName = static::getPackageManagerClassName()) === NullLegacyPackageManager::class) {
            $instance->initializePackageManagement($packageManagerClassName);
            $packageManager = static::createPackageManager(
                $packageManagerClassName,
                GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_core')
            );
            GeneralUtility::setSingletonInstance(PackageManager::class, $packageManager);
            ExtensionManagementUtility::setPackageManager($packageManager);
        }

        $GLOBALS['TYPO3_CONF_VARS']['LOG'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['interceptors'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale'] = 'en_US.UTF-8';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['checkStoredRecords'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['BE']['checkStoredRecordsLoose'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['BE']['warning_email_addr'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['sessionTimeout'] = 0;
        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockIP'] = 1;
        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockIPv6'] = 1;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP'] = 1;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIPv6'] = 1;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = 'png';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPermissions'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'] = '';

        if (method_exists(static::class, 'createConfigurationManager')) {
            $configurationManager = static::createConfigurationManager();
            if (!static::checkIfEssentialConfigurationExists($configurationManager)) {
                $failsafe = true;
            }
            $configurationManager->writeLocalConfiguration($GLOBALS['TYPO3_CONF_VARS']);
            static::populateLocalConfiguration($configurationManager);
        }

        $instance->defineLoggingAndExceptionConstants();
        $instance->baseSetup(0);

        /** @var NullPackageManager $packageManager */
        $packageManager = static::createPackageManager(
            NullPackageManager::class,
            $cacheManager->getCache(
                $cacheManager->hasCache('core') ? 'core' : 'cache_core'
            )
        );

        $packageManager->initialize();

        GeneralUtility::setSingletonInstance(PackageManager::class, $packageManager);
        ExtensionManagementUtility::setPackageManager($packageManager);

        $logManager = new LogManager($requestId);

        $container = null;
        if (class_exists(ContainerBuilder::class)) {
            $bootState = new \stdClass();
            $bootState->done = false;
            $bootState->cacheDisabled = true;

            $reflectionService = GeneralUtility::makeInstance(ReflectionService::class);

            $dependencyInjectionContainerCache = static::createCache('di', false);
            $coreCache = static::createCache('core', true);
            $assetsCache = static::createCache('assets', true);
            $builder = new ContainerBuilder([
                ClassLoader::class => $classLoader,
                ApplicationContext::class => Environment::getContext(),
                LogManager::class => $logManager,
                'cache.di' => $dependencyInjectionContainerCache,
                'cache.core' => $coreCache,
                'cache.assets' => $assetsCache,
                PackageManager::class => $packageManager,
                ReflectionService::class => $reflectionService,
                // @internal
                'boot.state' => $bootState,
            ]);

            $container = $builder->createDependencyInjectionContainer($packageManager, $dependencyInjectionContainerCache, false);

            // Push the container to GeneralUtility as we want to make sure its
            // makeInstance() method creates classes using the container from now on.
            GeneralUtility::setContainer($container);

            $instance->setContainer($container);

            $bootState->done = true;
        }

        $instance->initializeObjectContainer($container);

        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManager);
        GeneralUtility::removeSingletonInstance(PackageManager::class, $packageManager);
        GeneralUtility::removeSingletonInstance(CacheManager::class, $cacheManager);

        return $instance;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function getObjectContainer(): ?Container
    {
        return $this->objectContainer;
    }

    /**
     * @return $this
     */
    public function defineLoggingAndExceptionConstants()
    {
        if (method_exists(parent::class, 'defineLoggingAndExceptionConstants')) {
            parent::defineLoggingAndExceptionConstants();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function initializeObjectContainer(?ContainerInterface $container = null)
    {
        $container = GeneralUtility::makeInstance(Container::class, $container);
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
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['parser']['xlf'] = XliffParser::class;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_DLOG'] = FALSE;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_exceptionDLOG'] = FALSE;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_errorDLOG'] = FALSE;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['preProcessors'] = [
            \TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\EscapingModifierTemplateProcessor::class,
            \TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\PassthroughSourceModifierTemplateProcessor::class,
            \TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor::class
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['expressionNodeTypes'] = [
            \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode::class,
            \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode::class,
            \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode::class
        ];
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

    protected static function getPackageManagerClassName()
    {
        $packageManagerClassName = NullPackageManager::class;
        $packageManagerClassReflection = new \ReflectionClass(PackageManager::class);
        $initializeMethodReflection = $packageManagerClassReflection->getMethod('initialize');
        if ($initializeMethodReflection->getNumberOfRequiredParameters() > 0) {
            $packageManagerClassName = NullLegacyPackageManager::class;
        }
        return $packageManagerClassName;
    }

    public static function createPackageManager($packageManagerClassName, FrontendInterface $coreCache): PackageManager
    {
        $packageManagerClassName = static::getPackageManagerClassName();
        $dependencyOrderingService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\DependencyOrderingService::class);
        /** @var \TYPO3\CMS\Core\Package\PackageManager $packageManager */
        $packageManager = new $packageManagerClassName($dependencyOrderingService);
        $packageManager->injectCoreCache($coreCache);
        $packageManager->initialize();

        return $packageManager;
    }
}
