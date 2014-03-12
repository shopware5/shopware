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

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Theme\Compressor\Css;
use Shopware\Components\Theme\Compressor\Js;
use Shopware\Models\Shop as Shop;

/**
 * The Theme\Compiler class is used for the less compiling in the store front.
 * This class handles additionally the css and javascript minification.
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Compiler
{
    /**
     * File name for theme css and js files.
     */
    const THEME_FILE_NAME = 'theme';

    /**
     * File name for plugin css and js files.
     */
    const PLUGIN_FILE_NAME = 'plugin';

    /**
     * @var \lessc
     */
    private $compiler;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var Inheritance
     */
    private $inheritance;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var Css
     */
    private $cssCompressor;

    /**
     * @var Js
     */
    private $jsCompressor;


    /**
     * @param \lessc $compiler
     * @param PathResolver $pathResolver
     * @param Inheritance $inheritance
     * @param Css $cssCompressor
     * @param Js $jsCompressor
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(
        \lessc $compiler,
        PathResolver $pathResolver,
        Inheritance $inheritance,
        Css $cssCompressor,
        Js $jsCompressor,
        \Enlight_Event_EventManager $eventManager
    )
    {
        $this->compiler = $compiler;
        $this->eventManager = $eventManager;
        $this->inheritance = $inheritance;
        $this->pathResolver = $pathResolver;
        $this->cssCompressor = $cssCompressor;
        $this->jsCompressor = $jsCompressor;
    }

    /**
     * Compiles all required resources for the passed shop and template.
     * The function compiles all theme and plugin less files and
     * compresses the theme and plugin javascript and css files
     * into one file.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     */
    public function compile($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $this->compiler->setFormatter("compressed");

        $this->clearDirectory();

        $this->buildConfig($template, $shop);

        $this->compileThemeLess($timestamp, $template, $shop);

        $this->compilePluginLess($timestamp, $template, $shop);

        $this->compileThemeCss($timestamp, $template, $shop);

        $this->compilePluginCss($timestamp, $template, $shop);

        $this->compileThemeJavascript($timestamp, $template, $shop);

        $this->compilePluginJavascript($timestamp, $template, $shop);
    }

    /**
     * Helper function to clear the theme cache directory
     * before the new css and js files are compiled.
     */
    private function clearDirectory()
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->pathResolver->getCacheDirectory(),
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $path */
        foreach ($iterator as $path) {
            if ($path->getFilename() === '.gitkeep') {
                continue;
            }

            if ($path->isDir()) {
                rmdir($path->__toString());
            } else {
                unlink($path->__toString());
            }
        }
    }

    /**
     * Builds the less configuration.
     * The function loads first the inheritance config of the passed
     * template and shop instance.
     * After the theme configuration is set into the less compiler,
     * the function throws the event `Theme_Compiler_Collect_Plugin_Less_Config`
     * to allow plugins to override the theme configuration.
     *
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function buildConfig(Shop\Template $template, Shop\Shop $shop)
    {
        $config = $this->inheritance->buildConfig($template, $shop);
        $this->compiler->setVariables($config);

        $collection = new ArrayCollection();
        $this->eventManager->collect('Theme_Compiler_Collect_Less_Config', $collection, array(
            'shop' => $shop,
            'template' => $template
        ));

        foreach ($collection as $config) {
            if (!is_array($config)) {
                throw new \Exception("The passed plugin less config isn't an array!");
            }
            $this->compiler->setVariables($config);
        }
    }

    /**
     * Compiles all less files of the theme inheritance of the passed shop template.
     * The timestamp is required for file caching.
     *
     * Shopware implements the convention that each theme, which wants to implement less compiling,
     * has a all.less file within the /THEME-DIR/frontend/_public/src/less directory.
     * This file will be compiled into the theme.css file.
     *
     * Notice: The theme _public directory will be configured into the less compiler as import directory.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     */
    protected function compileThemeLess($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $hierarchy = $this->inheritance->buildInheritance($template);

        //creates the theme css file name for the passed timestamp and shop id.
        $themeFile = $fileName = $this->getThemeCssFile($timestamp, $shop);
        $output = new \SplFileObject($themeFile, "w+");

        foreach (array_reverse($hierarchy) as $shopTemplate) {
            $dir = $this->pathResolver->getPublicDirectory($shopTemplate);

            //set unique import directory for less @import commands
            $this->compiler->setImportDir(array($dir));

            $lessFile = $this->pathResolver->getLessDirectory($shopTemplate) . DIRECTORY_SEPARATOR . 'all.less';

            if (!file_exists($lessFile)) {
                continue;
            }

            $compiled = $this->compiler->compile(
                file_get_contents($lessFile)
            );
            $output->fwrite($compiled);
        }
    }

    /**
     * This function is responsible to allow plugins to compile less files into the plugin.css file.
     * The event fires the Theme_Compiler_Collect_Plugin_Less collect event to collect all plugin less definintions.
     *
     * Example to add an own plugin less compiling step:
     * <code>
     *   public function eventListener(Enlight_Event_EventArgs $args)
     *   {
     *       $less = new \Shopware\Components\Theme\PluginLess(
     *           //configuration
     *           array(
     *               'color1' => '#fff',
     *               'color2' => '#000'
     *           ),
     *
     *           //less files to compile
     *           array(
     *               __DIR__ . DIRECTORY_SEPARATOR . 'event1.less',
     *               __DIR__ . DIRECTORY_SEPARATOR . 'event2.less'
     *           ),
     *
     *           //import directory
     *           __DIR__
     *       );
     *
     *       return new ArrayCollection(array($less));
     *   }
     * </code>
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function compilePluginLess($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect('Theme_Compiler_Collect_Plugin_Less', $collection, array(
            'shop' => $shop,
            'template' => $template
        ));

        //creates the plugin css file name for the passed timestamp and shop id.
        $fileName = $fileName = $this->getPluginCssFile($timestamp, $shop);
        $output = new \SplFileObject($fileName, "w+");
        $output->fwrite('');

        if ($collection->count() <= 0) {
            return;
        }

        /**@var $pluginLess PluginLess */
        foreach ($collection as $pluginLess) {
            if (!$pluginLess instanceof PluginLess) {
                throw new \Exception(
                    "Some plugin tries to extends less compiling, but the passed config object isn't an instance of \\Shopware\\Components\\Theme\\PluginLess"
                );
            }

            $files = $pluginLess->getFiles();
            if (empty($files)) {
                throw new \Exception(
                    "Some plugin tries to extends less compiling, but the files array are empty."
                );
            }

            //set unique import directory for less @import commands
            $this->compiler->setImportDir(array($pluginLess->getImportDirectory()));

            //set plugin variables for the next compiling step
            $this->compiler->setVariables($pluginLess->getConfig());

            $content = $this->concatenateFileContents($pluginLess->getFiles());

            $content = $this->compiler->compile($content);

            $output->fwrite($content);
        }
    }

    /**
     * This function allows to define simple css files within a theme which compressed
     * into one theme.css file for the frontend.
     *
     * To define which css files of the theme should be compressed, the Theme.php $css property is used.
     * Shopware expects that all css file of this property is stored within the /frontend/_public/src/css
     * directory.
     *
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function compileThemeCss($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $cssFiles = $this->inheritance->getCssFiles($template);

        $fileName = $this->getThemeCssFile($timestamp, $shop);

        $output = new \SplFileObject($fileName, "a+");

        foreach ($cssFiles as $file) {
            if (!file_exists($file)) {
                throw new \Exception(sprintf(
                    "Theme css file %s doesn't exists",
                    $file
                ));
            }
            $minified = $this->cssCompressor->compress(
                file_get_contents($file)
            );
            $output->fwrite($minified);
        }
    }

    /**
     * Compress the plugin css files which can be added
     * over the `Theme_Compiler_Collect_Plugin_Css` event.
     * Each file will be minified by the Theme\Compressor\Css class.
     * The compressed css content will be added to the plugin.css file
     * which stored in the theme cache directory.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function compilePluginCss($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect('Theme_Compiler_Collect_Plugin_Css', $collection, array(
            'shop' => $shop,
            'template' => $template
        ));

        if ($collection->count() <= 0) {
            return;
        }

        $fileName = $this->getPluginCssFile($timestamp, $shop);
        $output = new \SplFileObject($fileName, "a+");

        foreach ($collection as $file) {
            if (!file_exists($file)) {
                throw new \Exception(sprintf(
                    "Some plugin tries to compress a css file, but the file %s doesn't exist",
                    $file
                ));
            }
            $minified = $this->cssCompressor->compress(
                file_get_contents($file)
            );
            $output->fwrite($minified);
        }
    }

    /**
     * Compress the theme javascript files.
     * Each file will be minified by the Theme\Compressor\Js class.
     * The compressed js content will be added to the theme.js file
     * which stored in the theme cache directory.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function compileThemeJavascript($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $files = $this->inheritance->getJavascriptFiles($template);

        $fileName = $this->getThemeJavascriptFile($timestamp, $shop);

        $output = new \SplFileObject($fileName, "w+");

        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new \Exception(sprintf(
                    "Theme javascript file %s doesn't exists",
                    $file
                ));
            }
            $content = file_get_contents($file);
            $minified = $this->jsCompressor->compress($content);
            $output->fwrite($minified);
        }
    }

    /**
     * Compress the plugin javascript files which can be added
     * over the `Theme_Compiler_Collect_Plugin_Javascript` event.
     * Each file will be minified by the Theme\Compressor\Js class.
     * The compressed js content will be added to the plugin.js file
     * which stored in the theme cache directory.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function compilePluginJavascript($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect('Theme_Compiler_Collect_Plugin_Javascript', $collection, array(
            'shop' => $shop,
            'template' => $template
        ));

        $fileName = $this->getPluginJavascriptFile($timestamp, $shop);
        $output = new \SplFileObject($fileName, "w+");
        $output->fwrite('');

        if ($collection->count() <= 0) {
            return;
        }

        foreach ($collection as $file) {
            if (!file_exists($file)) {
                throw new \Exception(sprintf(
                    "Some plugin tries to compress a css file, but the file %s doesn't exist",
                    $file
                ));
            }
            $content = file_get_contents($file);
            $minified = $this->jsCompressor->compress($content);
            $output->fwrite($minified);
        }
    }

    /**
     * Helper function which returns the css file for the themes.
     * The file name are build over the shop id and current compiler timestamp.
     * @param $timestamp
     * @param Shop\Shop $shop
     * @return string
     */
    private function getThemeCssFile($timestamp, Shop\Shop $shop)
    {
        return $this->pathResolver->buildCssPath($shop, self::THEME_FILE_NAME, $timestamp);
    }

    /**
     * Helper function which returns the css file for the plugins.
     * The file name are build over the shop id and current compiler timestamp.
     *
     * @param $timestamp
     * @param Shop\Shop $shop
     * @return string
     */
    private function getPluginCssFile($timestamp, Shop\Shop $shop)
    {
        return $this->pathResolver->buildCssPath($shop, self::PLUGIN_FILE_NAME, $timestamp);
    }

    /**
     * Helper function which returns the js file for the themes.
     * The file name are build over the shop id and current compiler timestamp.
     *
     * @param $timestamp
     * @param Shop\Shop $shop
     * @return string
     */
    private function getThemeJavascriptFile($timestamp, Shop\Shop $shop)
    {
        return $this->pathResolver->buildJsPath($shop, self::THEME_FILE_NAME, $timestamp);
    }

    /**
     * Helper function which returns the js file for the plugins.
     * The file name are build over the shop id and current compiler timestamp.
     *
     * @param $timestamp
     * @param Shop\Shop $shop
     * @return string
     */
    private function getPluginJavascriptFile($timestamp, Shop\Shop $shop)
    {
        return $this->pathResolver->buildJsPath($shop, self::PLUGIN_FILE_NAME, $timestamp);
    }

    /**
     * Helper function to concatenate the content of the passed files.
     *
     * @param array $files
     * @return string
     * @throws \Exception
     */
    private function concatenateFileContents(array $files)
    {
        $content = '';
        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new \Exception(sprintf(
                    "Tried to compile %s file which doesn't exist",
                    $file
                ));
            }

            $content .= file_get_contents($file) . "\n";
        }
        return $content;
    }
}
