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
use Shopware\Components\Theme\Minifier\Css;
use Shopware\Components\Theme\Minifier\Js;
use Shopware\Models\Shop as Shop;

/**
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
    private $cssMinifier;

    /**
     * @var Js
     */
    private $jsMinifier;


    function __construct(
        \lessc $compiler,
        PathResolver $pathResolver,
        Inheritance $inheritance,
        Css $cssMinifier,
        Js $jsMinifier,
        \Enlight_Event_EventManager $eventManager
    )
    {
        $this->compiler = $compiler;
        $this->eventManager = $eventManager;
        $this->inheritance = $inheritance;
        $this->pathResolver = $pathResolver;
        $this->cssMinifier = $cssMinifier;
        $this->jsMinifier = $jsMinifier;
    }

    /**
     * Compiles all required resources for the passed shop and template.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     */
    public function compile($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $this->compiler->setFormatter("compressed");

        $this->buildConfig($template, $shop);

        $this->compileThemeLess($timestamp, $template, $shop);

        $this->compilePluginLess($timestamp, $template, $shop);

        $this->compileThemeCss($timestamp, $template, $shop);

        $this->compilePluginCss($timestamp, $template, $shop);

        $this->compileThemeJavascript($timestamp, $template, $shop);

        $this->compilePluginJavascript($timestamp, $template, $shop);
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
     * @return array
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

        return array();
    }

    /**
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
            $minified = $this->cssMinifier->minify(
                file_get_contents($file)
            );
            $output->fwrite($minified);
        }
    }

    /**
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
                    "Some plugin tries to minify a css file, but the file %s doesn't exist",
                    $file
                ));
            }
            $minified = $this->cssMinifier->minify(
                file_get_contents($file)
            );
            $output->fwrite($minified);
        }
    }

    /**
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
            $minified = $this->jsMinifier->minify($content);
            $output->fwrite($minified);
        }
    }

    /**
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
                    "Some plugin tries to minify a css file, but the file %s doesn't exist",
                    $file
                ));
            }
            $content = file_get_contents($file);
            $minified = $this->jsMinifier->minify($content);
            $output->fwrite($minified);
        }
    }

    /**
     * @param $timestamp
     * @param Shop\Shop $shop
     * @return string
     */
    private function getThemeCssFile($timestamp, Shop\Shop $shop)
    {
        return $this->pathResolver->buildCssPath($shop, self::THEME_FILE_NAME, $timestamp);
    }

    /**
     * @param $timestamp
     * @param Shop\Shop $shop
     * @return string
     */
    private function getPluginCssFile($timestamp, Shop\Shop $shop)
    {
        return $this->pathResolver->buildCssPath($shop, self::PLUGIN_FILE_NAME, $timestamp);
    }

    /**
     * @param $timestamp
     * @param Shop\Shop $shop
     * @return string
     */
    private function getThemeJavascriptFile($timestamp, Shop\Shop $shop)
    {
        return $this->pathResolver->buildJsPath($shop, self::THEME_FILE_NAME, $timestamp);
    }

    /**
     * @param $timestamp
     * @param Shop\Shop $shop
     * @return string
     */
    private function getPluginJavascriptFile($timestamp, Shop\Shop $shop)
    {
        return $this->pathResolver->buildJsPath($shop, self::PLUGIN_FILE_NAME, $timestamp);
    }

    /**
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
