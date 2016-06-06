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
     * @param $rootDir
     * @param LessCompiler $compiler
     * @param PathResolver $pathResolver
     * @param Inheritance $inheritance
     * @param Service $service
     * @param Js $jsCompressor
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(
        $rootDir,
        LessCompiler $compiler,
        PathResolver $pathResolver,
        Inheritance $inheritance,
        Service $service,
        Js $jsCompressor,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->rootDir = $rootDir;
        $this->compiler = $compiler;
        $this->service = $service;
        $this->eventManager = $eventManager;
        $this->inheritance = $inheritance;
        $this->pathResolver = $pathResolver;
        $this->jsCompressor = $jsCompressor;
    }

    /**
     * Helper function which compiles a shop with new theme.
     * The function is called when the template cache is cleared.
     *
     * @param \Shopware\Models\Shop\Shop $shop
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
     * @return Configuration
     * @throws \Exception
     */
    public function getThemeConfiguration(Shop\Shop $shop)
    {
        $less       = $this->collectLessDefinitions($shop->getTemplate(), $shop);
        $js         = $this->collectJavascriptFiles($shop->getTemplate(), $shop);
        $config     = $this->getConfig($shop->getTemplate(), $shop);
        $timestamp  = $this->getThemeTimestamp($shop);

        $rootDir   = $this->rootDir;
        $lessFiles = [];
        foreach ($less as $definition) {
            $config    = array_merge($config, $definition->getConfig());
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
        $jsTarget   = $this->pathResolver->getJsFilePath($shop, $timestamp);
        $jsTarget   = ltrim(str_replace($this->rootDir, '', $jsTarget), '/');

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
     * @param Shop\Shop $shop
     * @throws \Exception
     */
    public function compileLess($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $file = $this->pathResolver->getCssFilePath($shop, $timestamp);
        $file = new \SplFileObject($file, "a");
        if (!$file->flock(LOCK_EX)) {
            return;
        }
        $file->ftruncate(0);

        $this->compiler->setConfiguration(
            $this->getCompilerConfiguration($shop)
        );

        $config = $this->getConfig($template, $shop);
        $this->compiler->setVariables($config);

        $definitions = $this->collectLessDefinitions($template, $shop);
        foreach ($definitions as $definition) {
            $this->compileLessDefinition($shop, $definition);
        }

        $css = $this->compiler->get();
        $this->compiler->reset();

        $success = $file->fwrite($css);
        if ($success === null) {
            throw new \RuntimeException("Could not write to " . $file->getPath());
        }
        $file->flock(LOCK_UN);   // release the lock
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
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $file = $this->pathResolver->getJsFilePath($shop, $timestamp);
        $file = new \SplFileObject($file, "a");
        if (!$file->flock(LOCK_EX)) {
            return;
        }
        $file->ftruncate(0);

        $settings = $this->service->getSystemConfiguration(
            AbstractQuery::HYDRATE_OBJECT
        );

        $javascriptFiles = $this->collectJavascriptFiles($template, $shop);
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
    private function getConfig(Shop\Template $template, Shop\Shop $shop)
    {
        $config = $this->inheritance->buildConfig($template, $shop, true);
        $config['shopware-revision'] = \Shopware::REVISION;

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
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @return LessDefinition[]
     */
    private function collectLessDefinitions(Shop\Template $template, Shop\Shop $shop)
    {
        $inheritances = $this->inheritance->buildInheritances($template);

        $definitions = $this->collectInheritanceLess($inheritances['bare']);

        $definitions = array_merge(
            $definitions,
            $this->collectInheritanceCss($inheritances['bare'])
        );

        $definitions = array_merge(
            $definitions,
            $this->collectPluginLess($template, $shop)
        );

        $definitions = array_merge(
            $definitions,
            $this->collectPluginCss($template, $shop)
        );

        $definitions = array_merge(
            $definitions,
            $this->collectInheritanceLess($inheritances['custom'])
        );

        $definitions = array_merge(
            $definitions,
            $this->collectInheritanceCss($inheritances['custom'])
        );

        return $definitions;
    }

    /**
     * Helper function which reads and creates the theme timestamp for the css and js files.
     *
     * @param \Shopware\Models\Shop\Shop $shop
     * @return int
     */
    public function getThemeTimestamp(Shop\Shop $shop)
    {
        /**@var $pathResolver \Shopware\Components\Theme\PathResolver */
        $file = $this->pathResolver->getCacheDirectory() . DIRECTORY_SEPARATOR . 'timestamp' . $shop->getId() . '.txt';

        if (file_exists($file)) {
            $timestamp = file_get_contents($file);
        } else {
            $timestamp = time();
            $this->createThemeTimestamp($shop, $timestamp);
        }

        return (int)$timestamp;
    }

    /**
     * @param Shop\Shop $shop
     * @param $timestamp
     */
    public function createThemeTimestamp(Shop\Shop $shop, $timestamp)
    {
        $file = $this->pathResolver->getCacheDirectory() . DIRECTORY_SEPARATOR . 'timestamp' . $shop->getId() . '.txt';
        file_put_contents($file, $timestamp);
    }

    /**
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @return array
     * @throws \Exception
     */
    private function collectJavascriptFiles(Shop\Template $template, Shop\Shop $shop)
    {
        $inheritances = $this->inheritance->buildInheritances($template);

        $files = $this->collectInheritanceJavascript($inheritances['bare']);

        $files = array_merge(
            $files,
            $this->collectPluginJavascript($shop, $template)
        );

        $files = array_merge(
            $files,
            $this->collectInheritanceJavascript($inheritances['custom'])
        );

        return $files;
    }

    /**
     * @param $inheritance
     * @return string[]
     */
    private function collectInheritanceJavascript($inheritance)
    {
        $files = [];
        foreach (array_reverse($inheritance) as $template) {
            $files = array_merge(
                $files,
                $this->inheritance->getTemplateJavascriptFiles($template)
            );
        }

        return $files;
    }

    /**
     * @param Shop\Shop $shop
     * @param Shop\Template $template
     * @return string[]
     * @throws \Enlight_Event_Exception
     * @throws \Exception
     */
    private function collectPluginJavascript(Shop\Shop $shop, Shop\Template $template)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect(
            'Theme_Compiler_Collect_Plugin_Javascript',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        foreach ($collection as $file) {
            if (!file_exists($file)) {
                throw new \Exception(
                    sprintf("Some plugin tries to compress a javascript file, but the file %s doesn't exist", $file)
                );
            }
        }

        return $collection->toArray();
    }

    /**
     * @param $inheritance
     * @return array|LessDefinition[]
     */
    private function collectInheritanceLess($inheritance)
    {
        $definitions = [];
        //use array_reverse to compile the bare themes first.
        foreach (array_reverse($inheritance) as $shopTemplate) {
            $definition = new LessDefinition();

            $definition->setImportDirectory(
                $this->pathResolver->getPublicDirectory($shopTemplate)
            );

            $definition->setFiles([
                $this->pathResolver->getThemeLessFile($shopTemplate)
            ]);

            $definitions[] = $definition;
        }

        return $definitions;
    }

    /**
     * @param $inheritance
     * @return array|LessDefinition[]
     */
    private function collectInheritanceCss($inheritance)
    {
        $files = [];
        foreach (array_reverse($inheritance) as $template) {
            $files = array_merge(
                $files,
                $this->inheritance->getTemplateCssFiles($template)
            );
        }
        if (empty($files)) {
            return [];
        }

        $definition = new LessDefinition();
        $definition->setFiles($files);

        return [$definition];
    }

    /**
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @return array|LessDefinition[]
     * @throws \Enlight_Event_Exception
     */
    private function collectPluginLess(Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect(
            'Theme_Compiler_Collect_Plugin_Less',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        if ($collection->count() <= 0) {
            return [];
        }
        return $collection->toArray();
    }

    /**
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @return array|LessDefinition[]
     * @throws \Enlight_Event_Exception
     */
    private function collectPluginCss(Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect(
            'Theme_Compiler_Collect_Plugin_Css',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        if ($collection->count() <= 0) {
            return [];
        }

        $definition = new LessDefinition();
        $definition->setFiles($collection->toArray());
        return [$definition];
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
                'sourceMapRootpath' => '../../',
                'sourceMapBasepath' => $this->rootDir,
                'sourceMapWriteTo' => $this->pathResolver->getSourceMapPath(),
                'sourceMapURL' => $this->pathResolver->getSourceMapUrl($shop)
            );
        }

        $config = $this->eventManager->filter(
            'Theme_Compiler_Configure', $config, array(
                'shop' => $shop,
                'settings' => $settings
            )
        );

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
            $this->compiler->setImportDirectories(
                array(
                    $definition->getImportDirectory()
                )
            );
        }

        //allows to add own configurations for the current compile step.
        if ($definition->getConfig()) {
            $this->compiler->setVariables($definition->getConfig());
        }

        $this->eventManager->notify(
            'Theme_Compiler_Compile_Less', array(
                'shop' => $shop,
                'less' => $definition
            )
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
     * Helper function which creates a url for the passed directory/file path.
     * This urls are used for the less compiler, to create the source map
     * and to prepend this url for each relative path.
     *
     * @param $path
     * @return string
     */
    private function formatPathToUrl($path)
    {
        $path = str_replace($this->rootDir, '', $path);
        $path = '../..' . $path;
        return $path;
    }

    /**
     * Clear existing theme cache
     * Removes all assets and timestamp files
     *
     * @param \Shopware\Models\Shop\Shop $shop
     * @param $timestamp
     */
    public function clearThemeCache(Shop\Shop $shop, $timestamp)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $files = [
            $this->pathResolver->buildTimestampName($timestamp, $shop, 'css'),
            $this->pathResolver->buildTimestampName($timestamp, $shop, 'js')
        ];

        $this->clearDirectory($files);
    }

    /**
     * Helper function to clear the theme cache directory
     * before the new css and js files are compiled.
     *
     * @param array $names
     */
    private function clearDirectory($names = array())
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
     * @param string $original
     * @param string[] $names
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
