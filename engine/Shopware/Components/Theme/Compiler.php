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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Theme\Compressor\Js;
use Shopware\Models\Shop;

/**
 * The Theme\Compiler class is used for the less compiling in the store front.
 * This class handles additionally the css and javascript minification.
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Compiler
{
    /**
     * Root directory
     *
     * @var string
     */
    private $rootDir;

    /**
     * @var LessCompiler
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
     * @var TimestampPersistor
     */
    private $timestampPersistor;

    /**
     * @var LessCollector
     */
    private $lessCollector;

    /**
     * @var JavascriptCollector
     */
    private $javascriptCollector;

    /**
     * @param $rootDir
     * @param LessCompiler                $compiler
     * @param PathResolver                $pathResolver
     * @param Inheritance                 $inheritance
     * @param Service                     $service
     * @param Js                          $jsCompressor
     * @param \Enlight_Event_EventManager $eventManager
     * @param TimestampPersistor          $timestampPersistor
     */
    public function __construct(
        $rootDir,
        LessCompiler $compiler,
        PathResolver $pathResolver,
        Inheritance $inheritance,
        Service $service,
        Js $jsCompressor,
        \Enlight_Event_EventManager $eventManager,
        TimestampPersistor $timestampPersistor
    ) {
        $this->rootDir = $rootDir;
        $this->compiler = $compiler;
        $this->service = $service;
        $this->eventManager = $eventManager;
        $this->inheritance = $inheritance;
        $this->pathResolver = $pathResolver;
        $this->jsCompressor = $jsCompressor;
        $this->timestampPersistor = $timestampPersistor;

        $this->lessCollector = new LessCollector(
            $pathResolver,
            $inheritance,
            $eventManager
        );

        $this->javascriptCollector = new JavascriptCollector(
            $inheritance,
            $eventManager
        );
    }

    /**
     * Helper function which compiles a shop with new theme.
     * The function is called when the template cache is cleared.
     *
     * @param Shop\Shop $shop
     */
    public function compile(Shop\Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $old = $this->getThemeTimestamp($shop);
        $timestamp = time();

        $this->compileLess($timestamp, $shop->getTemplate(), $shop);
        $this->compileJavascript($timestamp, $shop->getTemplate(), $shop);

        $this->createThemeTimestamp($shop, $timestamp);
        $this->clearThemeCache($shop, $old);
    }

    /**
     * @param Shop\Shop $shop
     *
     * @throws \Exception
     *
     * @return Configuration
     */
    public function getThemeConfiguration(Shop\Shop $shop)
    {
        $less = $this->lessCollector->collectLessDefinitions($shop->getTemplate(), $shop);
        $js = $this->javascriptCollector->collectJavascriptFiles($shop->getTemplate(), $shop);

        $config = $this->getConfig($shop->getTemplate(), $shop);
        $timestamp = $this->getThemeTimestamp($shop);

        $rootDir = $this->rootDir;
        $lessFiles = [];
        foreach ($less as $definition) {
            $config = array_merge($config, $definition->getConfig());
            $lessFiles = array_merge($lessFiles, $definition->getFiles());
        }

        $js = array_map(function ($file) use ($rootDir) {
            return ltrim(str_replace($this->rootDir, '', $file), '/');
        }, $js);

        $lessFiles = array_map(function ($file) use ($rootDir) {
            return ltrim(str_replace($this->rootDir, '', $file), '/');
        }, $lessFiles);

        $lessTarget = $this->pathResolver->getCssFilePath($shop, $timestamp);
        $lessTarget = ltrim(str_replace($this->rootDir, '', $lessTarget), '/');
        $jsTarget = $this->pathResolver->getJsFilePath($shop, $timestamp);
        $jsTarget = ltrim(str_replace($this->rootDir, '', $jsTarget), '/');

        return new Configuration(
            $lessFiles,
            $js,
            $config,
            $lessTarget,
            $jsTarget
        );
    }

    /**
     * Compiles all required resources for the passed shop and template.
     * The function compiles all theme and plugin less files and
     * compresses the theme and plugin javascript and css files
     * into one file.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop     $shop
     *
     * @throws \Exception
     */
    public function compileLess($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $file = $this->pathResolver->getCssFilePath($shop, $timestamp);
        $file = new \SplFileObject($file, 'a');
        if (!$file->flock(LOCK_EX)) {
            return;
        }
        $file->ftruncate(0);

        $this->compiler->setConfiguration(
            $this->getCompilerConfiguration($shop)
        );

        $config = $this->getConfig($template, $shop);
        $this->compiler->setVariables($config);

        $definitions = $this->lessCollector->collectLessDefinitions($template, $shop);
        foreach ($definitions as $definition) {
            $this->compileLessDefinition($shop, $definition);
        }

        $css = $this->compiler->get();
        $this->compiler->reset();

        $success = $file->fwrite($css);
        if ($success === null) {
            throw new \RuntimeException('Could not write to ' . $file->getPath());
        }
        $file->flock(LOCK_UN);   // release the lock
    }

    /**
     * Compiles the javascript files for the passed shop template.
     *
     * @param $timestamp
     * @param Shop\Template $template
     * @param Shop\Shop     $shop
     */
    public function compileJavascript($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $file = $this->pathResolver->getJsFilePath($shop, $timestamp);
        $file = new \SplFileObject($file, 'a');
        if (!$file->flock(LOCK_EX)) {
            return;
        }
        $file->ftruncate(0);

        $settings = $this->service->getSystemConfiguration(
            AbstractQuery::HYDRATE_OBJECT
        );

        $javascriptFiles = $this->javascriptCollector->collectJavascriptFiles($template, $shop);
        $content = '';
        foreach ($javascriptFiles as $jsFile) {
            $content .= file_get_contents($jsFile) . "\n";
        }

        if ($settings->getCompressJs()) {
            $content = $this->jsCompressor->compress($content);
        }

        $file->fwrite($content);
        $file->flock(LOCK_UN);   // release the lock
    }

    /**
     * Helper function which reads and creates the theme timestamp for the css and js files.
     *
     * @param Shop\Shop $shop
     *
     * @return int
     */
    public function getThemeTimestamp(Shop\Shop $shop)
    {
        return $this->timestampPersistor->getCurrentTimestamp($shop->getId());
    }

    /**
     * @param Shop\Shop $shop
     * @param $timestamp
     */
    public function createThemeTimestamp(Shop\Shop $shop, $timestamp)
    {
        $this->timestampPersistor->updateTimestamp($shop->getId(), $timestamp);
    }

    /**
     * Clear existing theme cache
     * Removes all assets and timestamp files
     *
     * @param Shop\Shop $shop
     * @param $timestamp
     */
    public function clearThemeCache(Shop\Shop $shop, $timestamp)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $files = [
            $this->pathResolver->buildTimestampName($timestamp, $shop, 'css'),
            $this->pathResolver->buildTimestampName($timestamp, $shop, 'js'),
        ];

        $this->clearDirectory($files);
    }

    /**
     * Helper function which compiles the passed less definition.
     * The shop parameter is required to build the shop url for the files.
     *
     * @param Shop\Shop      $shop
     * @param LessDefinition $definition
     */
    private function compileLessDefinition(Shop\Shop $shop, LessDefinition $definition)
    {
        //set unique import directory for less @import commands
        if ($definition->getImportDirectory()) {
            $this->compiler->setImportDirectories(
                [
                    $definition->getImportDirectory(),
                ]
            );
        }

        //allows to add own configurations for the current compile step.
        if ($definition->getConfig()) {
            $this->compiler->setVariables($definition->getConfig());
        }

        $this->eventManager->notify(
            'Theme_Compiler_Compile_Less', [
                'shop' => $shop,
                'less' => $definition,
            ]
        );

        //needs to iterate files, to generate source map if configured.
        foreach ($definition->getFiles() as $file) {
            if (!file_exists($file)) {
                continue;
            }

            //creates the url for the compiler, this url will be prepend to each relative path.
            //the url is additionally used for the source map generation.
            $url = $this->formatPathToUrl($file);

            $this->compiler->compile($file, $url);
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
     * @param Shop\Shop     $shop
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getConfig(Shop\Template $template, Shop\Shop $shop)
    {
        $config = $this->inheritance->buildConfig($template, $shop, true);
        $config['shopware-revision'] = \Shopware::REVISION;
        $config['shopware-theme-inheritance'] = $this->inheritance->getInheritancePath($template);

        $collection = new ArrayCollection();

        $this->eventManager->collect(
            'Theme_Compiler_Collect_Less_Config',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        foreach ($collection as $temp) {
            if (!is_array($temp)) {
                throw new \Exception("The passed plugin less config isn't an array!");
            }
            $config = array_merge($config, $temp);
        }

        return $config;
    }

    /**
     * Builds the configuration for the less compiler class.
     *
     * @param Shop\Shop $shop
     *
     * @return array
     */
    private function getCompilerConfiguration(Shop\Shop $shop)
    {
        $settings = $this->service->getSystemConfiguration(
            AbstractQuery::HYDRATE_OBJECT
        );

        $config = [
            'compress' => $settings->getCompressCss(),
            'sourceMap' => $settings->getCreateSourceMap(),
        ];

        if ($settings->getCreateSourceMap()) {
            $config += [
                'sourceMapRootpath' => '../../',
                'sourceMapBasepath' => $this->rootDir,
                'sourceMapWriteTo' => $this->pathResolver->getSourceMapPath(),
                'sourceMapURL' => $this->pathResolver->getSourceMapUrl($shop),
            ];
        }

        $config = $this->eventManager->filter(
            'Theme_Compiler_Configure', $config, [
                'shop' => $shop,
                'settings' => $settings,
            ]
        );

        return $config;
    }

    /**
     * Helper function which creates a url for the passed directory/file path.
     * This urls are used for the less compiler, to create the source map
     * and to prepend this url for each relative path.
     *
     * @param $path
     *
     * @return string
     */
    private function formatPathToUrl($path)
    {
        $path = str_replace($this->rootDir, '', $path);
        $path = '../..' . $path;

        return $path;
    }

    /**
     * Helper function to clear the theme cache directory
     * before the new css and js files are compiled.
     *
     * @param array $names
     */
    private function clearDirectory(array $names = [])
    {
        $dir = $this->pathResolver->getCacheDirectory();

        if (!file_exists($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $path */
        foreach ($iterator as $path) {
            if ($path->getFilename() === '.gitkeep') {
                continue;
            }

            if (!$this->fileNameMatch($path->getFilename(), $names)) {
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
     * @param string   $original
     * @param string[] $names
     *
     * @return bool
     */
    private function fileNameMatch($original, $names)
    {
        foreach ($names as $name) {
            if (strpos($original, $name) !== false) {
                return true;
            }
        }

        return false;
    }
}
