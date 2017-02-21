<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Theme;

use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop;

/**
 * The Theme\PathResolver class is a helper
 * class which handles all path operations
 * for themes. For example the class
 * contains a getDirectory function which
 * returns the Theme directory of the passed shop template.
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PathResolver
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var \Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * @var array
     */
    private $pluginDirectories;

    /**
     * @param string                    $rootDir
     * @param array                     $pluginDirectories
     * @param \Enlight_Template_Manager $templateManager
     */
    public function __construct($rootDir, array $pluginDirectories, \Enlight_Template_Manager $templateManager)
    {
        $this->rootDir = $rootDir;
        $this->templateManager = $templateManager;
        $this->pluginDirectories = $pluginDirectories;
    }

    /**
     * @param Shop\Template $template
     *
     * @return null|string
     */
    public function getDirectory(Shop\Template $template)
    {
        return $this->getThemeDirectory($template);
    }

    /**
     * @param array $template
     *
     * @return null|string
     */
    public function getDirectoryByArray(array $template)
    {
        if ($template['plugin_id'] === null) {
            return $this->getFrontendThemeDirectory() . DIRECTORY_SEPARATOR . $template['template'];
        }

        if ($template['plugin_namespace'] == 'ShopwarePlugins') {
            return implode(
                DIRECTORY_SEPARATOR,
                [
                    $this->pluginDirectories['ShopwarePlugins'] . '/' . $template['plugin_name'] . '/Resources',
                    'Themes',
                    'Frontend',
                    $template['template'],
                ]
            );
        }

        return implode(
            DIRECTORY_SEPARATOR,
            [
                $this->pluginDirectories[$template['plugin_source']],
                $template['plugin_namespace'],
                $template['plugin_name'],
                'Themes',
                'Frontend',
                $template['template'],
            ]
        );
    }

    /**
     * Helper function to build the path to the passed plugin.
     *
     * @param Plugin $plugin
     *
     * @return string
     */
    public function getPluginPath(Plugin $plugin)
    {
        if ($plugin->isLegacyPlugin()) {
            return $this->pluginDirectories[$plugin->getSource()] . $plugin->getNamespace() . DIRECTORY_SEPARATOR . $plugin->getName();
        }

        return $this->pluginDirectories['ShopwarePlugins'] . '/' . $plugin->getName() . '/Resources';
    }

    /**
     * Returns the Frontend folder of the Themes directory.
     *
     * @return string
     */
    public function getFrontendThemeDirectory()
    {
        return $this->getBaseThemeDirectory() .
        DIRECTORY_SEPARATOR .
        'Frontend';
    }

    /**
     * Returns the backend theme directory
     * of the default theme location.
     *
     * @return string
     */
    public function getBackendThemeDirectory()
    {
        return $this->getBaseThemeDirectory() .
        DIRECTORY_SEPARATOR .
        'Backend';
    }

    /**
     * Returns the current Ext JS backend theme which
     * is used for the shopware backend.
     *
     * @return string
     */
    public function getExtJsThemeDirectory()
    {
        return $this->getBackendThemeDirectory() .
        DIRECTORY_SEPARATOR .
        'ExtJs';
    }

    /**
     * Returns the less directory for the passed theme.
     *
     * @param Shop\Template $template
     *
     * @return string
     */
    public function getPublicDirectory(Shop\Template $template)
    {
        return $this->getThemeDirectory($template) .
        DIRECTORY_SEPARATOR .
        'frontend' .
        DIRECTORY_SEPARATOR .
        '_public';
    }

    /**
     * Returns the fix defined snippet directory of the passed theme.
     *
     * @param Shop\Template $template
     *
     * @return string
     */
    public function getSnippetDirectory(Shop\Template $template)
    {
        return $this->getThemeDirectory($template) .
        DIRECTORY_SEPARATOR .
        '_private' .
        DIRECTORY_SEPARATOR .
        'snippets' .
        DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the fix defined snippet directory of the passed theme.
     *
     * @param Shop\Template $template
     *
     * @return string
     */
    public function getSmartyDirectory(Shop\Template $template)
    {
        return $this->getThemeDirectory($template) .
        DIRECTORY_SEPARATOR .
        '_private' .
        DIRECTORY_SEPARATOR .
        'smarty' .
        DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the less directory for the passed theme.
     *
     * @param Shop\Template $template
     *
     * @return string
     */
    public function getLessDirectory(Shop\Template $template)
    {
        return $this->getPublicDirectory($template) .
        DIRECTORY_SEPARATOR .
        'src' .
        DIRECTORY_SEPARATOR .
        'less';
    }

    /**
     * Returns the less directory for the passed theme.
     *
     * @param Shop\Template $template
     *
     * @return string
     */
    public function getCssDirectory(Shop\Template $template)
    {
        return $this->getPublicDirectory($template) .
        DIRECTORY_SEPARATOR .
        'src' .
        DIRECTORY_SEPARATOR .
        'css';
    }

    /**
     * @param Shop\Template $template
     *
     * @return string
     */
    public function getThemeLessFile(Shop\Template $template)
    {
        return $this->getLessDirectory($template) .
        DIRECTORY_SEPARATOR . 'all.less';
    }

    /**
     * Helper function which returns the default shopware theme directory.
     *
     * @return string
     */
    public function getCacheDirectory()
    {
        return $this->rootDir . '/web/cache';
    }

    /**
     * @param string    $path
     * @param Shop\Shop $shop
     * @param bool      $isSecureRequest
     *
     * @return string
     */
    public function formatPathToUrl($path, Shop\Shop $shop, $isSecureRequest = false)
    {
        if ($isSecureRequest && $shop->getSecureBasePath()) {
            $targetPath = $shop->getSecureBasePath();
        } else {
            $targetPath = $shop->getBasePath();
        }

        return str_replace($this->rootDir, $targetPath, $path);
    }

    /**
     * Returns the directory path to the compiler source map.
     *
     * @return string
     */
    public function getSourceMapPath()
    {
        return $this->getCacheDirectory() . DIRECTORY_SEPARATOR . 'css.source.map';
    }

    /**
     * Returns the shop url to the generated compiler source map.
     *
     * @param Shop\Shop $shop
     *
     * @return string
     */
    public function getSourceMapUrl(Shop\Shop $shop)
    {
        return $shop->getBasePath() . '/web/cache/css.source.map';
    }

    /**
     * Helper function which build the directory path to the passed
     * css file.
     * This function is used for the less smarty function.
     * The smarty function checks if this file is
     * already exists, if this isn't the case, the smarty
     * function starts the theme compiler operations.
     *
     * @param Shop\Shop $shop
     * @param $timestamp
     * @return string
     */
    public function getCssFilePath(Shop\Shop $shop, $timestamp)
    {
        return $this->getCacheDirectory() . '/' . $this->buildTimestampName($timestamp, $shop, 'css');
    }

    /**
     * Builds the path to the passed javascript file.
     * This function is used for the javascript smarty function.
     * The smarty function checks if this file is
     * already exists, if this isn't the case, the smarty
     * function starts the theme compiler operations.
     *
     * @param Shop\Shop $shop
     * @param $timestamp
     * @return string
     */
    public function getJsFilePath(Shop\Shop $shop, $timestamp)
    {
        return $this->getCacheDirectory() . '/' . $this->buildTimestampName($timestamp, $shop, 'js');
    }

    /**
     * Helper function to build a unique file name.
     *
     * @param string    $timestamp
     * @param Shop\Shop $shop
     * @param string    $suffix
     *
     * @return string
     */
    public function buildTimestampName($timestamp, Shop\Shop $shop, $suffix)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $filename = $timestamp . '_' . md5($timestamp . $shop->getTemplate()->getId() . $shop->getId() . \Shopware::REVISION);

        return $filename . '.' . $suffix;
    }

    /**
     * @return string
     */
    private function getBaseThemeDirectory()
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . 'themes';
    }

    /**
     * Helper function which returns the theme directory for the passed
     * shop template.
     *
     * @param Shop\Template $theme
     *
     * @return null|string
     */
    private function getThemeDirectory(Shop\Template $theme)
    {
        if ($theme->getPlugin()) {
            return $this->getPluginPath($theme->getPlugin()) .
            DIRECTORY_SEPARATOR .
            'Themes' .
            DIRECTORY_SEPARATOR .
            'Frontend' .
            DIRECTORY_SEPARATOR .
            $theme->getTemplate();
        }

        return $this->getFrontendThemeDirectory() . DIRECTORY_SEPARATOR . $theme->getTemplate();
    }
}
