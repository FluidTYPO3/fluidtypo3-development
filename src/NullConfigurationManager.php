<?php
namespace FluidTYPO3\Development;

use TYPO3\CMS\Extbase\Configuration\AbstractConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class DummyConfigurationManager
 */
class NullConfigurationManager implements ConfigurationManagerInterface {

    /**
     * @param string $type
     * @param string $extensionName
     * @param string $pluginName
     * @return array
     */
    public function getConfiguration($type, $extensionName = NULL, $pluginName = NULL) {
        return $this->getTypoScriptSetup();
    }

    /**
     * @param string $featureName
     * @return boolean
     */
    public function isFeatureEnabled($featureName) {
        TRUE;
    }

    /**
     * @return ContentObjectRenderer
     */
    public function getContentObject() {
        return new ContentObjectRenderer();
    }

    /**
     * @param array $frameworkConfiguration
     * @return array
     */
    protected function getContextSpecificFrameworkConfiguration(array $frameworkConfiguration) {
        return array();
    }

    /**
     * @return array
     */
    public function getTypoScriptSetup() {
        return array(
            'config' => array(
                'tx_extbase' => array(
                    'features' => array(
                        'rewrittenPropertyMapper' => TRUE
                    )
                )
            )
        );
    }

    /**
     * @param string $extensionName
     * @param string|NULL $pluginName
     */
    protected function getPluginConfiguration($extensionName, $pluginName = null) {
        return array();
    }

    /**
     * @param string $extensionName
     * @param string $pluginName
     * @return array
     */
    protected function getSwitchableControllerActions($extensionName, $pluginName) {
        return array();
    }

    /**
     * @param string $storagePid
     * @param integer $recursionDepth
     * @return array
     */
    protected function getRecursiveStoragePids($storagePid, $recursionDepth = 0) {
        return array();
    }

    /**
     * @param ContentObjectRenderer|NULL $contentObject
     * @return void
     */
    public function setContentObject(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject = NULL) {
    }

    /**
     * @param array $configuration
     * @return void
     */
    public function setConfiguration(array $configuration = array()) {
    }

}
