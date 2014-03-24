<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
use Shopware\Models\Shop as Shop;

/**
 * The Theme\PathResolver class is a helper
 * class which handles all path operations
 * for themes. For example the class
 * contains a getDirectory function which
 * returns the Theme directory of the passed shop template.
 *
 * @category  Shopware
 * @package   Shopware\Components\Theme
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
     * @param string $rootDir
     * @param \Enlight_Template_Manager $templateManager
     */
    function __construct($rootDir, \Enlight_Template_Manager $templateManager)
    {
        $this->rootDir = $rootDir;
        $this->templateManager = $templateManager;
    }

    /**
     * @param Shop\Template $template
     * @return null|string
     */
    public function getDirectory(Shop\Template $template)
    {
        if ($template->getVersion() < 3) {
            return $this->getTemplateDirectory($template);
        } else {
            return $this->getThemeDirectory($template);
        }
    }

    /**
     * Helper function to build the path to the passed plugin.
     * @param Plugin $plugin
     * @return string
     */
    public function getPluginPath(Plugin $plugin)
    {
        $namespace = strtolower($plugin->getNamespace());
        $source = strtolower($plugin->getSource());
        $name = $plugin->getName();

        return $this->rootDir .
        DIRECTORY_SEPARATOR . 'engine' .
        DIRECTORY_SEPARATOR . 'Shopware' .
        DIRECTORY_SEPARATOR . 'Plugins' .
        DIRECTORY_SEPARATOR . ucfirst($source) .
        DIRECTORY_SEPARATOR . ucfirst($namespace) .
        DIRECTORY_SEPARATOR . ucfirst($name);
    }

    /**
     * Returns the default templates directory.
     * @return string
     */
    public function getDefaultTemplateDirectory()
    {
        return $this->rootDir . '/templates';
    }

    /**
     * Helper function which returns the default shopware theme directory.
     * @return string
     */
    public function getDefaultThemeDirectory()
    {
        return $this->rootDir .
        DIRECTORY_SEPARATOR . 'engine' .
        DIRECTORY_SEPARATOR . 'Shopware' .
        DIRECTORY_SEPARATOR . 'Themes';
    }

    /**
     * Returns the less directory for the passed theme.
     * @param Shop\Template $template
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
     * @param Shop\Template $template
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
     * @param Shop\Template $template
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
     * @return string
     */
    public function getThemeLessFile(Shop\Template $template)
    {
        return $this->getLessDirectory($template) .
        DIRECTORY_SEPARATOR . 'all.less';
    }

    /**
     * Helper function which returns the default shopware theme directory.
     * @return string
     */
    public function getCacheDirectory()
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'cache';
    }


    public function formatPathToUrl($path, Shop\Shop $shop)
    {
        return str_replace($this->rootDir, $shop->getBaseUrl(), $path);
    }

    /**
     * Returns the directory path to the compiler source map.
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
     * @return array
     */
    public function getCssFilePaths(Shop\Shop $shop, $timestamp)
    {
        return array(
            'default' => $this->getCacheDirectory() . DIRECTORY_SEPARATOR . $this->buildTimestampName($timestamp, $shop, 'css')
        );
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
     * @return array
     */
    public function getJsFilePaths(Shop\Shop $shop, $timestamp)
    {
        return array(
            'default' => $this->getCacheDirectory() . DIRECTORY_SEPARATOR . $this->buildTimestampName($timestamp, $shop, 'js')
        );
    }

    /**
     * Helper function to build a unique file name.
     * @param $timestamp
     * @param Shop\Shop $shop
     * @param $suffix
     * @return string
     */
    private function buildTimestampName($timestamp, Shop\Shop $shop, $suffix)
    {
        return $timestamp . '_' . 'theme' . $shop->getId() . '.' . $suffix;
    }

    /**
     * Helper function which returns the theme directory for the passed
     * shop template.
     *
     * @param Shop\Template $theme
     * @return null|string
     */
    private function getThemeDirectory(Shop\Template $theme)
    {
        if ($theme->getPlugin()) {

            return $this->getPluginPath($theme->getPlugin()) .
            DIRECTORY_SEPARATOR .
            'Themes' .
            DIRECTORY_SEPARATOR .
            $theme->getTemplate();

        } else {
            return $this->getDefaultThemeDirectory() . DIRECTORY_SEPARATOR . $theme->getTemplate();
        }
    }

    /**
     * Returns the template directory of the passed shop template.
     * To get this use the getDirectory function.
     *
     * @param Shop\Template $template
     * @return string
     */
    private function getTemplateDirectory(Shop\Template $template)
    {
        return $this->templateManager->resolveTemplateDir(
            $template->getTemplate()
        );
    }

}