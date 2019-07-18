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
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Components\Theme\Compressor\CompressorInterface;
use Shopware\Components\Theme\Compressor\Js;
use Shopware\Models\Shop;

/**
 * The Theme\Compiler class is used for the less compiling in the store front.
 * This class handles additionally the css and javascript minification.
 */
class Compiler
{
    /**
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
     * @var CompressorInterface
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
     * @var ShopwareReleaseStruct
     */
    private $release;

    /**
     * @param string $rootDir
     */
    public function __construct(
        $rootDir,
        LessCompiler $compiler,
        PathResolver $pathResolver,
        Inheritance $inheritance,
        Service $service,
        CompressorInterface $jsCompressor,
        \Enlight_Event_EventManager $eventManager,
        TimestampPersistor $timestampPersistor,
        ShopwareReleaseStruct $release
    ) {
        $this->rootDir = $rootDir;
        $this->compiler = $compiler;
        $this->service = $service;
        $this->eventManager = $eventManager;
        $this->inheritance = $inheritance;
        $this->pathResolver = $pathResolver;
        $this->jsCompressor = $jsCompressor;
        $this->timestampPersistor = $timestampPersistor;
        $this->release = $release;

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
     * @throws \Exception
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

    public function recompile(Shop\Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $timestamp = $this->getThemeTimestamp($shop);

        $this->compileLess($timestamp, $shop->getTemplate(), $shop);
        $this->compileJavascript($timestamp, $shop->getTemplate(), $shop);
    }

    /**
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

        $lessFiles = [];
        foreach ($less as $definition) {
            $config = array_merge($config, $definition->getConfig());
            $lessFiles = array_merge($lessFiles, $definition->getFiles());
        }

        $js = array_map(function ($file) {
            return ltrim(str_replace($this->rootDir, '', $file), '/');
        }, $js);

        $lessFiles = array_map(function ($file) {
            return ltrim(str_replace($this->rootDir, '', $file), '/');
        }, $lessFiles);

        $lessTarget = $this->pathResolver->getCssFilePath($shop, $timestamp);
        $lessTarget = ltrim(str_replace($this->rootDir, '', $lessTarget), '/');
        $jsTarget = $this->pathResolver->getJsFilePath($shop, $timestamp);
        $jsTarget = ltrim(str_replace($this->rootDir, '', $jsTarget), '/');

        $inheritancePath = $this->inheritance->getInheritancePath($shop->getTemplate());

        return new Configuration(
            $lessFiles,
            $js,
            $config,
            $lessTarget,
            $jsTarget,
            $inheritancePath
        );
    }

    /**
     * Compiles all required resources for the passed shop and template.
     * The function compiles all theme and plugin less files and
     * compresses the theme and plugin javascript and css files
     * into one file.
     *
     * @param string $timestamp
     *
     * @throws \Exception
     */
    public function compileLess($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $file = $this->pathResolver->getTmpCssFilePath($shop, $timestamp);

        $dir = dirname($file);
        if (!is_dir($dir)) {
            if (@mkdir($dir, 0777, true) === false && !is_dir($dir)) {
                throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", 'web', $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", 'web', $dir));
        }

        $file = new \SplFileObject($file, 'w');
        if (!$file->flock(LOCK_EX | LOCK_NB)) {
            return;
        }

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

        rename($this->pathResolver->getTmpCssFilePath($shop, $timestamp), $this->pathResolver->getCssFilePath($shop, $timestamp));
    }

    /**
     * Compiles the javascript files for the passed shop template.
     *
     * @param string $timestamp
     *
     * @throws \Exception
     */
    public function compileJavascript($timestamp, Shop\Template $template, Shop\Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $file = $this->pathResolver->getTmpJsFilePath($shop, $timestamp);
        $file = new \SplFileObject($file, 'w');
        if (!$file->flock(LOCK_EX | LOCK_NB)) {
            return;
        }

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

        rename($this->pathResolver->getTmpJsFilePath($shop, $timestamp), $this->pathResolver->getJsFilePath($shop, $timestamp));
    }

    /**
     * Helper function which reads and creates the theme timestamp for the css and js files.
     *
     * @return string
     */
    public function getThemeTimestamp(Shop\Shop $shop)
    {
        return $this->timestampPersistor->getCurrentTimestamp($shop->getId());
    }

    /**
     * @param int $timestamp
     */
    public function createThemeTimestamp(Shop\Shop $shop, $timestamp)
    {
        $this->timestampPersistor->updateTimestamp($shop->getId(), $timestamp);
    }

    /**
     * Clear existing theme cache
     * Removes all assets and timestamp files
     *
     * @param int $timestamp
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
     * @throws \Enlight_Event_Exception
     */
    private function compileLessDefinition(Shop\Shop $shop, LessDefinition $definition)
    {
        // Set unique import directory for less @import commands
        if ($definition->getImportDirectory()) {
            $this->compiler->setImportDirectories(
                [
                    $definition->getImportDirectory(),
                ]
            );
        }

        // Allows to add own configurations for the current compile step.
        if ($definition->getConfig()) {
            $this->compiler->setVariables($definition->getConfig());
        }

        $this->eventManager->notify(
            'Theme_Compiler_Compile_Less', [
                'shop' => $shop,
                'less' => $definition,
            ]
        );

        // Need to iterate files, to generate source map if configured.
        foreach ($definition->getFiles() as $file) {
            if (!file_exists($file)) {
                continue;
            }

            // Creates the url for the compiler, this url will be prepend to each relative path.
            // The url is additionally used for the source map generation.
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
     * @throws \Exception
     *
     * @return array
     */
    private function getConfig(Shop\Template $template, Shop\Shop $shop)
    {
        $config = $this->inheritance->buildConfig($template, $shop);
        $config['shopware-revision'] = $this->release->getRevision();

        $collection = new ArrayCollection();

        $this->eventManager->collect(
            'Theme_Compiler_Collect_Less_Config',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        foreach ($collection as $temp) {
            if (!is_array($temp)) {
                throw new \Exception('The passed plugin less config isn\'t an array!');
            }
            $config = array_merge($config, $temp);
        }

        return $config;
    }

    /**
     * Builds the configuration for the less compiler class.
     *
     * @throws \Enlight_Event_Exception
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
     * @param string $path
     *
     * @return string
     */
    private function formatPathToUrl($path)
    {
        // Path normalizing
        $path = str_replace([$this->rootDir, '//'], ['', '/'], $path);

        return '../../' . ltrim($path, '/');
    }

    /**
     * Helper function to clear the theme cache directory
     * before the new css and js files are compiled.
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
