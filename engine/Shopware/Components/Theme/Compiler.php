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
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Theme\Compressor\Js;
use Shopware\Models\Shop as Shop;

/**
 * The Theme\Compiler class is used for the less compiling in the store front.
 * This class handles additionally the css and javascript minification.
 *
 * @category  Shopware
 * @package   Shopware\Components\Theme
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Compiler
{
    /**
     * Root directory
     * @var string
     */
    private $rootDir;

    /**
     * @var \Less_Parser
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
     * @var Js
     */
    private $jsCompressor;

    /**
     * @var Service
     */
    private $service;

    /**
     * @param $rootDir
     * @param \Less_Parser $compiler
     * @param PathResolver $pathResolver
     * @param Inheritance $inheritance
     * @param Service $service
     * @param Js $jsCompressor
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(
        $rootDir,
        \Less_Parser $compiler,
        PathResolver $pathResolver,
        Inheritance $inheritance,
        Service $service,
        Js $jsCompressor,
        \Enlight_Event_EventManager $eventManager
    )
    {
        $this->rootDir = $rootDir;
        $this->compiler = $compiler;
        $this->service = $service;
        $this->eventManager = $eventManager;
        $this->inheritance = $inheritance;
        $this->pathResolver = $pathResolver;
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
    public function compileLess($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $this->compiler->SetOptions(
            $this->getCompilerConfiguration($shop)
        );

        $this->clearDirectory(array('css'));

        $this->buildConfig($template, $shop);

        $this->compileThemeLess($template, $shop);

        $this->compilePluginLess($template, $shop);

        $this->compressThemeCss($template, $shop);

        $this->compressPluginCss($template, $shop);

        $this->outputCompiledCss($timestamp, $shop);
    }

    /**
     * Compiles the javascript files for the passed shop template.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     */
    public function compileJavascript($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $this->clearDirectory(array('js'));

        $this->compressThemeJavascript($timestamp, $template, $shop);

        $this->compressPluginJavascript($timestamp, $template, $shop);
    }

    /**
     * Builds the configuration for the less compiler class.
     *
     * @param \Shopware\Models\Shop\Shop $shop
     * @return array
     */
    private function getCompilerConfiguration(Shop\Shop $shop)
    {
        $settings = $this->service->getSystemConfiguration(
            AbstractQuery::HYDRATE_OBJECT
        );

        $config = array(
            'compress' => $settings->getCompressCss(),
            'sourceMap' => $settings->getCreateSourceMap()
        );

        if ($settings->getCreateSourceMap()) {
            $config += array(
                'sourceMapWriteTo' => $this->pathResolver->getSourceMapPath(),
                'sourceMapURL' => $this->pathResolver->getSourceMapUrl($shop)
            );
        }

        $config = $this->eventManager->filter('Theme_Compiler_Configure', $config, array(
            'shop' => $shop,
            'settings' => $settings
        ));

        return $config;
    }

    /**
     * Helper function which compiles the passed less definition.
     * The shop parameter is required to build the shop url for the files.
     *
     * @param Shop\Shop $shop
     * @param LessDefinition $definition
     */
    private function compileLessDefinition(Shop\Shop $shop, LessDefinition $definition)
    {
        //set unique import directory for less @import commands
        if ($definition->getImportDirectory()) {
            $this->compiler->SetImportDirs(array(
                $definition->getImportDirectory()
            ));
        }

        //allows to add own configurations for the current compile step.
        if ($definition->getConfig()) {
            $this->compiler->ModifyVars(
                $definition->getConfig()
            );
        }

        $this->eventManager->notify('Theme_Compiler_Compile_Less', array(
            'shop' => $shop,
            'less' => $definition
        ));

        //needs to iterate files, to generate source map if configured.
        foreach ($definition->getFiles() as $file) {
            if (!file_exists($file)) {
                continue;
            }

            //creates the url for the compiler, this url will be prepend to each relative path.
            //the url is additionally used for the source map generation.
            $url = $this->formatPathToUrl(
                $shop, $file
            );

            $this->compiler->parseFile(
                $file, $url
            );
        }
    }

    /**
     * After all less file are compiled, the css output will be
     * written into the theme_shopId.css file in the /web/cache directory.
     *
     * @param $timestamp
     * @param Shop\Shop $shop
     */
    private function outputCompiledCss($timestamp, Shop\Shop $shop)
    {
        $file = $this->pathResolver->getCssFilePaths($shop, $timestamp);
        $file = $file['default'];

        $output = new \SplFileObject($file, "w+");

        $css = $this->compiler->getCss();

        $output->fwrite($css);
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
        $config = $this->inheritance->buildConfig($template, $shop, true);

        $this->compiler->ModifyVars($config);

        $collection = new ArrayCollection();

        $this->eventManager->collect('Theme_Compiler_Collect_Less_Config', $collection, array(
            'shop' => $shop,
            'template' => $template
        ));

        foreach ($collection as $config) {
            if (!is_array($config)) {
                throw new \Exception("The passed plugin less config isn't an array!");
            }

            $this->compiler->ModifyVars($config);
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
     * Notice: The theme _public directory will be configured into the less compiler as import directory and root uri.
     *
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     */
    protected function compileThemeLess(Shop\Template $template, Shop\Shop $shop)
    {
        $hierarchy = $this->inheritance->buildInheritance($template);

        //use array_reverse to compile the bare themes first.
        foreach (array_reverse($hierarchy) as $shopTemplate) {
            $definition = new LessDefinition();

            $definition->setImportDirectory(
                $this->pathResolver->getPublicDirectory($shopTemplate)
            );

            $definition->setFiles(array(
                $this->pathResolver->getThemeLessFile($shopTemplate)
            ));

            $this->compileLessDefinition($shop, $definition);
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
     *       $less = new \Shopware\Components\Theme\LessDefinition(
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
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function compilePluginLess(Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect('Theme_Compiler_Collect_Plugin_Less', $collection, array(
            'shop' => $shop,
            'template' => $template
        ));

        if ($collection->count() <= 0) {
            return;
        }

        /**@var $pluginLess LessDefinition */
        foreach ($collection as $definition) {
            if (!$definition instanceof LessDefinition) {
                throw new \Exception(
                    "Some plugin tries to extends less compiling, but the passed config object isn't an instance of \\Shopware\\Components\\Theme\\LessDefinition"
                );
            }

            $this->compileLessDefinition($shop, $definition);
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
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function compressThemeCss(Shop\Template $template, Shop\Shop $shop)
    {
        $definition = new LessDefinition();

        $definition->setFiles(
            $this->inheritance->getCssFiles($template)
        );

        $this->compileLessDefinition($shop, $definition);
    }

    /**
     * Compress the plugin css files which can be added
     * over the `Theme_Compiler_Collect_Plugin_Css` event.
     * Each file will be minified by the Theme\Compressor\Css class.
     * The compressed css content will be added to the plugin.css file
     * which stored in the theme cache directory.
     *
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    protected function compressPluginCss(Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect('Theme_Compiler_Collect_Plugin_Css', $collection, array(
            'shop' => $shop,
            'template' => $template
        ));

        if ($collection->count() <= 0) {
            return;
        }

        $definition = new LessDefinition();

        $definition->setFiles($collection->toArray());

        $this->compileLessDefinition($shop, $definition);
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
    protected function compressThemeJavascript($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $files = $this->inheritance->getJavascriptFiles($template);

        $fileName = $this->pathResolver->getJsFilePaths($shop, $timestamp);
        $fileName = $fileName['default'];

        $output = new \SplFileObject($fileName, "w+");

        $settings = $this->service->getSystemConfiguration(
            AbstractQuery::HYDRATE_OBJECT
        );

        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new \Exception(sprintf(
                    "Theme javascript file %s doesn't exists",
                    $file
                ));
            }

            $content = file_get_contents($file);

            if ($settings->getCompressJs()) {
                $content = $this->jsCompressor->compress($content);
            }

            $output->fwrite($content);
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
    protected function compressPluginJavascript($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect('Theme_Compiler_Collect_Plugin_Javascript', $collection, array(
            'shop' => $shop,
            'template' => $template
        ));

        $fileName = $this->pathResolver->getJsFilePaths($shop, $timestamp);
        $fileName = $fileName['default'];

        $output = new \SplFileObject($fileName, "w+");
        $output->fwrite('');

        if ($collection->count() <= 0) {
            return;
        }
        $settings = $this->service->getSystemConfiguration(
            AbstractQuery::HYDRATE_OBJECT
        );

        foreach ($collection as $file) {
            if (!file_exists($file)) {
                throw new \Exception(sprintf(
                    "Some plugin tries to compress a css file, but the file %s doesn't exist",
                    $file
                ));
            }
            $content = file_get_contents($file);

            if ($settings->getCompressJs()) {
                $content = $this->jsCompressor->compress($content);
            }

            $output->fwrite($content);
        }
    }

    /**
     * Helper function which creates a url for the passed directory/file path.
     * This urls are used for the less compiler, to create the source map
     * and to prepend this url for each relative path.
     *
     * @param \Shopware\Models\Shop\Shop $shop
     * @param $path
     * @return string
     */
    private function formatPathToUrl(Shop\Shop $shop, $path)
    {
        $path = str_replace($this->rootDir, '', $path);
        $path = '//' . $shop->getHost() . $shop->getBasePath() . $path;
        return $path;
    }

    /**
     * Helper function to clear the theme cache directory
     * before the new css and js files are compiled.
     */
    private function clearDirectory($extensions = array())
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
            if ($path->getFilename() == '.gitkeep') {
                continue;
            }

            if (!empty($extensions) && !in_array($path->getExtension(), $extensions)) {
                continue;
            }

            if ($path->isDir()) {
                rmdir($path->__toString());
            } else {
                unlink($path->__toString());
            }
        }
    }
}
