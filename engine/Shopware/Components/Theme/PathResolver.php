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

use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop;

/**
 * The Theme\PathResolver class is a helper
 * class which handles all path operations
 * for themes. For example the class
 * contains a getDirectory function which
 * returns the Theme directory of the passed shop template.
 */
class PathResolver
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $templateDir;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var array
     */
    private $pluginDirectories;

    /**
     * @var ShopwareReleaseStruct
     */
    private $release;

    /**
     * @param string $rootDir
     * @param string $templateDir
     * @param string $cacheDir
     */
    public function __construct($rootDir, array $pluginDirectories, $templateDir, $cacheDir, ShopwareReleaseStruct $release)
    {
        $this->rootDir = $rootDir;
        $this->pluginDirectories = $pluginDirectories;
        $this->templateDir = $templateDir;
        $this->cacheDir = $cacheDir;
        $this->release = $release;
    }

    /**
     * @return string|null
     */
    public function getDirectory(Shop\Template $template)
    {
        return $this->getThemeDirectory($template);
    }

    /**
     * @return string|null
     */
    public function getDirectoryByArray(array $template)
    {
        if ($template['plugin_id'] === null) {
            return $this->getFrontendThemeDirectory() . DIRECTORY_SEPARATOR . $template['template'];
        }

        if (in_array($template['plugin_namespace'], ['ShopwarePlugins', 'ProjectPlugins'], true)) {
            return implode(
                DIRECTORY_SEPARATOR,
                [
                    $this->pluginDirectories[$template['plugin_namespace']] . '/' . $template['plugin_name'] . '/Resources',
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
     * @return string
     */
    public function getPluginPath(Plugin $plugin)
    {
        if ($plugin->isLegacyPlugin()) {
            return $this->pluginDirectories[$plugin->getSource()] . $plugin->getNamespace() . DIRECTORY_SEPARATOR . $plugin->getName();
        }

        return $this->pluginDirectories[$plugin->getNamespace()] . '/' . $plugin->getName() . '/Resources';
    }

    /**
     * Returns the Frontend folder of the Themes directory.
     *
     * @return string
     */
    public function getFrontendThemeDirectory()
    {
        return $this->getBaseThemeDirectory() . DIRECTORY_SEPARATOR . 'Frontend';
    }

    /**
     * Returns the backend theme directory
     * of the default theme location.
     *
     * @return string
     */
    public function getBackendThemeDirectory()
    {
        return $this->getBaseThemeDirectory() . DIRECTORY_SEPARATOR . 'Backend';
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
     * @return string
     */
    public function getThemeLessFile(Shop\Template $template)
    {
        return $this->getLessDirectory($template) .
        DIRECTORY_SEPARATOR . 'all.less';
    }

    /**
     * Helper function which returns the cache directory
     *
     * @return string
     */
    public function getCacheDirectory()
    {
        return rtrim($this->cacheDir, '/');
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function formatPathToUrl($path, Shop\Shop $shop)
    {
        return str_replace([rtrim($this->rootDir, '/\\'), '\\', '//'], [$shop->getBasePath(), '/', '/'], $path);
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
     * @return string
     */
    public function getSourceMapUrl(Shop\Shop $shop)
    {
        return $this->formatPathToUrl($this->getSourceMapPath(), $shop);
    }

    /**
     * Helper function which builds the directory path to the passed
     * css file.
     * This function is used for the less smarty function.
     * The smarty function checks if this file is
     * already exists, if this isn't the case, the smarty
     * function starts the theme compiler operations.
     *
     * @param string $timestamp
     *
     * @return string
     */
    public function getCssFilePath(Shop\Shop $shop, $timestamp)
    {
        return $this->getCacheDirectory() . '/' . $this->buildTimestampName($timestamp, $shop, 'css');
    }

    /**
     * Helper function which builds the directory path to the tmp
     * passed css file.
     * This function is used for generating a .css.tmp file
     * while writing the theme cache.
     * The tmp file prevents serving a zero content css file.
     */
    public function getTmpCssFilePath(Shop\Shop $shop, string $timestamp): string
    {
        return $this->getCacheDirectory() . '/' . $this->buildTimestampName($timestamp, $shop, 'css.tmp');
    }

    /**
     * Builds the path to the passed javascript file.
     * This function is used for the javascript smarty function.
     * The smarty function checks if this file is
     * already exists, if this isn't the case, the smarty
     * function starts the theme compiler operations.
     *
     * @param string $timestamp
     *
     * @return string
     */
    public function getJsFilePath(Shop\Shop $shop, $timestamp)
    {
        return $this->getCacheDirectory() . '/' . $this->buildTimestampName($timestamp, $shop, 'js');
    }

    /**
     * Helper function which builds the directory path to the tmp
     * passed js file.
     * This function is used for generating a .js.tmp file
     * while writing the theme cache.
     * The tmp file prevents serving a zero content js file.
     */
    public function getTmpJsFilePath(Shop\Shop $shop, string $timestamp): string
    {
        return $this->getCacheDirectory() . '/' . $this->buildTimestampName($timestamp, $shop, 'js.tmp');
    }

    /**
     * Helper function to build a unique file name.
     *
     * @param string $timestamp
     * @param string $suffix
     *
     * @return string
     */
    public function buildTimestampName($timestamp, Shop\Shop $shop, $suffix)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $filename = $timestamp . '_' . md5($timestamp . $shop->getTemplate()->getId() . $shop->getId() . $this->release->getRevision());

        return $filename . '.' . $suffix;
    }

    /**
     * @return string
     */
    private function getBaseThemeDirectory()
    {
        return rtrim($this->templateDir, DIRECTORY_SEPARATOR);
    }

    /**
     * Helper function which returns the theme directory for the passed
     * shop template.
     *
     * @return string
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
