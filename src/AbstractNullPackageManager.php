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
use TYPO3\CMS\Core\Package\PackageInterface;

/**
 * Class AbstractNullPackageManager
 */
abstract class AbstractNullPackageManager extends FailsafePackageManager
{

	/**
	 * Array of packages whose classes are loaded but do
	 * not (necessarily) report as installed by TYPO3.
	 *
	 * @var array
	 */
	protected $virtualPackages = array();

	/**
	 * @var array
	 */
	protected $packageStatesConfiguration = array(
		'packages' => array()
	);

	/**
	 * @param Bootstrap $bootstrap
	 * @return void
	 */
	public function setBootstrap(Bootstrap $bootstrap)
    {
		$this->bootstrap = $bootstrap;
	}

	/**
	 * @param string $packageKey
	 * @return boolean
	 */
	public function isPackageActive($packageKey)
    {
		return in_array($packageKey, $this->getLoadedPackageKeys()) || in_array($packageKey, $this->virtualPackages);
	}

	/**
	 * @param string $packageKey
	 * @return boolean
	 */
	public function isPackageAvailable($packageKey)
    {
		return in_array($packageKey, $this->getLoadedPackageKeys()) || in_array($packageKey, $this->virtualPackages);
	}

    public function getActivePackages()
    {
        $keys = $this->getLoadedPackageKeys();
        return array_combine($keys, array_map([$this, 'getPackage'], $keys));
    }

	/**
	 * @param string $packageKey
	 * @return PackageInterface
	 */
	public function getPackage($packageKey)
    {
        $pwd = trim(shell_exec('pwd'));
        $json = json_decode(file_get_contents($pwd . '/composer.json'), true);
        $folder = $pwd . (($json['extra']['typo3/cms']['web-dir'] ?? false) ? '/' . $json['extra']['typo3/cms']['web-dir'] . '/' : '');
        if (file_exists($folder . '/public/typo3conf/ext/' . $packageKey . '/ext_emconf.php')) {
            $path = realpath($folder . '/public/typo3conf/ext/' . $packageKey) . '/';
        } elseif (file_exists($folder . '/typo3conf/ext/' . $packageKey . '/ext_emconf.php')) {
            $path = realpath($folder . '/typo3conf/ext/' . $packageKey) . '/';
        } elseif (file_exists($folder . '/typo3/sysext/' . $packageKey . '/ext_emconf.php')) {
            $path = realpath($folder . '/typo3/sysext/' . $packageKey) . '/';
		} else {
            $path = realpath(__DIR__ . '/../../' . $packageKey) . '/';
            if (FALSE === file_exists($path . 'ext_emconf.php')) {
                $path = realpath(__DIR__ . '/../../../../') . '/';
            }
        }
		$package = new Package($this, $packageKey, $path, $path . 'Classes/');
		return $package;
	}

    /**
     * @return array
     */
    protected function getLoadedPackageKeys()
    {
        $root = trim(shell_exec('pwd'));
        if (!file_exists($root . '/composer.json')) {
            $root = realpath(__DIR__ . '/../');
        }
        $composerFile = $root . '/composer.json';
        $parsed = json_decode(file_get_contents($composerFile), true);
        if ($parsed['name'] ?? false) {
            $key = $this->getExtensionKeyFromComposerName($parsed['name']);
            $loaded = [$key];
        } else {
            $loaded = [];
        }
        if (isset($parsed['require-dev'])) {
            foreach (array_keys($parsed['require-dev']) as $packageName) {
                $loaded[] = $this->getExtensionKeyFromComposerName($packageName);
            }
        }
        if (isset($parsed['require'])) {
            foreach (array_keys($parsed['require']) as $packageName) {
                $loaded[] = $this->getExtensionKeyFromComposerName($packageName);
            }
        }
        return $loaded;
    }

    protected function getExtensionKeyFromComposerName(string $key)
    {
        if (strncmp($key, 'typo3/cms-', 10) === 0) {
            $key = substr($key, 10);
        } elseif ($position = strpos($key, '/')) {
            $key = substr($key, $position + 1);
        }
        return $key;
    }
}
