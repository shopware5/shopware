<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Theme;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Enlight_Event_EventManager;
use Enlight_Event_Exception;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Shopware\Bundle\PluginInstallerBundle\Service\UniqueIdGeneratorInterface;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Components\Theme\Compressor\CompressorInterface;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use SplFileInfo;
use SplFileObject;

/**
 * The Theme\Compiler class is used for the less compiling in the store front.
 * This class handles additionally the css and javascript minification.
 */
class Compiler
{
    private string $rootDir;

    private LessCompiler $compiler;

    private PathResolver $pathResolver;

    private Inheritance $inheritance;

    private Enlight_Event_EventManager $eventManager;

    private CompressorInterface $jsCompressor;

    private Service $service;

    private TimestampPersistor $timestampPersistor;

    private LessCollector $lessCollector;

    private JavascriptCollector $javascriptCollector;

    private ShopwareReleaseStruct $release;

    private UniqueIdGeneratorInterface $uniqueIdGenerator;

    public function __construct(
        string $rootDir,
        LessCompiler $compiler,
        PathResolver $pathResolver,
        Inheritance $inheritance,
        Service $service,
        CompressorInterface $jsCompressor,
        Enlight_Event_EventManager $eventManager,
        TimestampPersistor $timestampPersistor,
        ShopwareReleaseStruct $release,
        UniqueIdGeneratorInterface $uniqueIdGenerator
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
        $this->uniqueIdGenerator = $uniqueIdGenerator;

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
     * @throws Exception
     *
     * @return void
     */
    public function compile(Shop $shop)
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
     * @return void
     */
    public function recompile(Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $timestamp = $this->getThemeTimestamp($shop);

        $this->compileLess($timestamp, $shop->getTemplate(), $shop);
        $this->compileJavascript($timestamp, $shop->getTemplate(), $shop);
    }

    /**
     * @throws Exception
     *
     * @return Configuration
     */
    public function getThemeConfiguration(Shop $shop)
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
     * @throws Exception
     *
     * @return void
     */
    public function compileLess($timestamp, Template $template, Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $file = $this->pathResolver->getTmpCssFilePath($shop, $timestamp);

        $dir = \dirname($file);
        if (!is_dir($dir)) {
            if (@mkdir($dir, 0777, true) === false && !is_dir($dir)) {
                throw new RuntimeException(sprintf("Unable to create the %s directory (%s)\n", 'web', $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", 'web', $dir));
        }

        $file = new SplFileObject($file, 'w');
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
            throw new RuntimeException('Could not write to ' . $file->getPath());
        }
        $file->flock(LOCK_UN);   // release the lock

        $file = null; // release file handles, else Windows still locks the file

        rename($this->pathResolver->getTmpCssFilePath($shop, $timestamp), $this->pathResolver->getCssFilePath($shop, $timestamp));
    }

    /**
     * Compiles the javascript files for the passed shop template.
     *
     * @param string $timestamp
     *
     * @throws Exception
     *
     * @return void
     */
    public function compileJavascript($timestamp, Template $template, Shop $shop)
    {
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $file = $this->pathResolver->getTmpJsFilePath($shop, $timestamp);
        $file = new SplFileObject($file, 'w');
        if (!$file->flock(LOCK_EX | LOCK_NB)) {
            return;
        }

        $settings = $this->service->getSystemConfiguration(
            AbstractQuery::HYDRATE_OBJECT
        );

        $javascriptFiles = $this->javascriptCollector->collectJavascriptFiles($template, $shop);
        $content = '';
        foreach ($javascriptFiles as $jsFile) {
            $content .= file_get_contents($jsFile) . ";\n";
        }

        if ($settings->getCompressJs()) {
            $content = $this->jsCompressor->compress($content);
        }

        $file->fwrite($content);
        $file->flock(LOCK_UN);   // release the lock

        $file = null; // release file handles, else Windows still locks the file

        rename($this->pathResolver->getTmpJsFilePath($shop, $timestamp), $this->pathResolver->getJsFilePath($shop, $timestamp));
    }

    /**
     * Helper function which reads and creates the theme timestamp for the css and js files.
     *
     * @return string
     */
    public function getThemeTimestamp(Shop $shop)
    {
        return $this->timestampPersistor->getCurrentTimestamp($shop->getId());
    }

    /**
     * @param int $timestamp
     *
     * @return void
     */
    public function createThemeTimestamp(Shop $shop, $timestamp)
    {
        $this->timestampPersistor->updateTimestamp($shop->getId(), $timestamp);
    }

    /**
     * Clear existing theme cache
     * Removes all assets and timestamp files
     *
     * @param int $timestamp
     *
     * @return void
     */
    public function clearThemeCache(Shop $shop, $timestamp)
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
     * @throws Enlight_Event_Exception
     */
    private function compileLessDefinition(Shop $shop, LessDefinition $definition): void
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
            'Theme_Compiler_Compile_Less',
            [
                'shop' => $shop,
                'less' => $definition,
            ]
        );

        // Need to iterate files, to generate source map if configured.
        foreach ($definition->getFiles() as $file) {
            if (!file_exists($file)) {
                continue;
            }

            // Creates the url for the compiler, this url will be prepended to each relative path.
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
     * @throws Exception
     */
    private function getConfig(Template $template, Shop $shop): array
    {
        $config = $this->inheritance->buildConfig($template, $shop);
        $config['shopware-revision'] = md5($this->release->getRevision() . $this->uniqueIdGenerator->getUniqueId());

        /** @var ArrayCollection<int, mixed> $collection */
        $collection = new ArrayCollection();

        $this->eventManager->collect(
            'Theme_Compiler_Collect_Less_Config',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        foreach ($collection as $temp) {
            if (!\is_array($temp)) {
                throw new Exception("The passed plugin less config isn't an array!");
            }
            $config = array_merge($config, $temp);
        }

        return $config;
    }

    /**
     * Builds the configuration for the less compiler class.
     *
     * @throws Enlight_Event_Exception
     */
    private function getCompilerConfiguration(Shop $shop): array
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

        return $this->eventManager->filter(
            'Theme_Compiler_Configure',
            $config,
            [
                'shop' => $shop,
                'settings' => $settings,
            ]
        );
    }

    /**
     * Helper function which creates a url for the passed directory/file path.
     * This urls are used for the less compiler, to create the source map
     * and to prepend this url for each relative path.
     */
    private function formatPathToUrl(string $path): string
    {
        // Path normalizing
        $path = str_replace([$this->rootDir, '//'], ['', '/'], $path);

        return '../../' . ltrim($path, '/');
    }

    /**
     * Helper function to clear the theme cache directory
     * before the new css and js files are compiled.
     */
    private function clearDirectory(array $names = []): void
    {
        $dir = $this->pathResolver->getCacheDirectory();

        if (!file_exists($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $path */
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
     * @param array<string> $names
     */
    private function fileNameMatch(string $original, array $names): bool
    {
        foreach ($names as $name) {
            if (strpos($original, $name) !== false) {
                return true;
            }
        }

        return false;
    }
}
