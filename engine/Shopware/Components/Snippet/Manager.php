<?php

declare(strict_types=1);
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

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Snippet\DbAdapter;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Exception\ShopLocaleNotSetException;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;

class Shopware_Components_Snippet_Manager extends Enlight_Components_Snippet_Manager
{
    /**
     * @var ModelManager the model manager
     */
    protected $modelManager;

    /**
     * @var array{readFromDb: bool, writeToDb: bool, readFromIni: bool, writeToIni: bool, showSnippetPlaceholder: bool} The config options provided in the global config.php file
     */
    protected $snippetConfig;

    /**
     * @var Locale|null
     */
    protected $locale;

    /**
     * @var Shop|null
     */
    protected $shop;

    /**
     * @var Enlight_Config_Adapter_File|null
     */
    protected $fileAdapter;

    /**
     * @var array<array{0: int, 1: int}>
     */
    protected $extends = [];

    /**
     * @var array{Default: string, Local: string, Community: string, ShopwarePlugins: string, ProjectPlugins: string}
     */
    private array $pluginDirectories;

    private Locale $fallbackLocale;

    /**
     * @param array{Default: string, Local: string, Community: string, ShopwarePlugins: string, ProjectPlugins: string}   $pluginDirectories
     * @param array{readFromDb: bool, writeToDb: bool, readFromIni: bool, writeToIni: bool, showSnippetPlaceholder: bool} $snippetConfig
     */
    public function __construct(ModelManager $modelManager, array $pluginDirectories, array $snippetConfig, ?string $themeDir = null)
    {
        $this->snippetConfig = $snippetConfig;
        $this->modelManager = $modelManager;
        $this->pluginDirectories = $pluginDirectories;

        $fallbackLocale = $this->modelManager->getRepository(Locale::class)->findOneBy(['locale' => 'en_GB']);
        if (!$fallbackLocale instanceof Locale) {
            throw new RuntimeException('Required fallback locale "en_GB" not found');
        }
        $this->fallbackLocale = $fallbackLocale;

        if ($this->snippetConfig['readFromIni']) {
            $configDir = $this->getConfigDirs($themeDir);
            $this->fileAdapter = new Enlight_Config_Adapter_File([
                'configDir' => $configDir,
                'allowWrites' => $snippetConfig['writeToIni'],
            ]);
        }

        // will set $this->adapter = DbAdapter
        parent::__construct(new DbAdapter([
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => ['shopID', 'localeID'],
            'allowWrites' => $snippetConfig['writeToDb'],
        ]));
    }

    /**
     * Returns a snippet model instance
     *
     * @param string|null $namespace
     *
     * @return Enlight_Components_Snippet_Namespace
     */
    public function getNamespace($namespace = null)
    {
        $key = $namespace === null ? '__ignore' : (string) $namespace;

        if (isset($this->namespaces[$key])) {
            return $this->namespaces[$key];
        }

        if ($this->readFromDb()) {
            $this->namespaces[$key] = $this->createDbNamespace(
                $namespace,
                $this->shop ? $this->shop->getId() : 1,
                $this->locale ? $this->locale->getId() : $this->getDefaultLocale()->getId()
            );
        }

        if ($this->readFromIni($key)) {
            $this->namespaces[$key] = $this->createIniNamespace($namespace);
        }

        if (!isset($this->namespaces[$key])) {
            $this->namespaces[$key] = new $this->defaultNamespaceClass(['name' => $namespace]);
        }

        $instance = $this->namespaces[$key];
        if ($this->requiresFallback($instance)) {
            $instance->setFallback($this->createDbNamespace($namespace, 1, $this->fallbackLocale->getId()));
        }

        return $this->namespaces[$key];
    }

    /**
     * Set locale instance
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
        $this->namespaces = [];

        return $this;
    }

    /**
     * Set shop instance
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function setShop(Shop $shop)
    {
        $this->shop = $shop;
        $this->locale = $shop->getLocale();
        $this->namespaces = [];
        $this->initExtends();

        return $this;
    }

    /**
     * Resets the currently set shop of the SnippetManager
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function resetShop()
    {
        $this->shop = null;
        $this->namespaces = [];
        $this->extends = [];

        return $this;
    }

    /**
     * @param string $dir
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function addConfigDir($dir)
    {
        if (!$this->fileAdapter) {
            return $this;
        }

        $this->fileAdapter->addConfigDir($dir);

        return $this;
    }

    /**
     * @return Locale
     */
    protected function getDefaultLocale()
    {
        return $this->modelManager->getRepository(Shop::class)->getDefault()->getLocale();
    }

    /**
     * Defines the 'extends' logic for snippet loading, responsible for the cascading fallbacks
     * between snippet sets
     *
     * @return void
     */
    protected function initExtends()
    {
        $extends = [];
        $shop = $this->shop;
        $locale = $this->locale;
        if (!$locale instanceof Locale) {
            throw new ShopLocaleNotSetException();
        }

        $main = $shop !== null ? $shop->getMain() : null;
        if ($main !== null && $main->getId() === 1) {
            $main = null;
        }

        // fallback to parent shop, current locale
        if ($main !== null && $main->getId() !== 1) {
            $extends[] = [
                $main->getId(),
                $locale->getId(),
            ];
        }

        // fallback to default shop, current locale
        if ($shop !== null && $shop->getId() !== 1) {
            $extends[] = [
                1,
                $locale->getId(),
            ];
        }

        /*
         * Fallback to parent shop, parent locale
         * If parent locale is the same as current locale, skip this step
         * as it was already added previously ("fallback to parent shop, current locale")
         **/
        if ($main !== null && $locale->getId() !== $main->getLocale()->getId()) {
            $extends[] = [
                $main->getId(),
                $main->getLocale()->getId(),
            ];
        }

        // fallback to default shop, default language
        // TODO SW-26555: this needs to be fixed, because it's wrong for non-english installations
        if ($locale->getId() !== 1) {
            $extends[] = [
                1,
                1,
            ];
        }

        $this->extends = $extends;
    }

    /**
     * @param string|null $themeDir
     *
     * @return string[]
     */
    protected function getConfigDirs($themeDir)
    {
        $configDir = [];
        if (file_exists(Shopware()->DocPath('snippets'))) {
            $configDir[] = Shopware()->DocPath('snippets');
        }

        // Default theme directories
        return array_merge($configDir, $this->getPluginDirs(), $this->getThemeDirs($themeDir));
    }

    /**
     * @return array<string>
     */
    private function getPluginDirs(): array
    {
        $configDir = [];

        $plugins = $this->modelManager->getRepository(Plugin::class)->findBy(['active' => true]);
        foreach ($plugins as $plugin) {
            if ($plugin->isLegacyPlugin()) {
                $pluginPath = rtrim($this->pluginDirectories[$plugin->getSource()], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $plugin->getNamespace() . DIRECTORY_SEPARATOR . $plugin->getName();

                $pluginSnippetPath = $pluginPath . DIRECTORY_SEPARATOR . 'Snippets' . DIRECTORY_SEPARATOR;
            } else {
                $pluginPath = rtrim($this->pluginDirectories[$plugin->getNamespace()], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $plugin->getName();

                $pluginSnippetPath = $pluginPath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'snippets' . DIRECTORY_SEPARATOR;
            }

            array_unshift($configDir, $pluginSnippetPath);

            $pluginThemePath = $pluginPath . DIRECTORY_SEPARATOR . 'Themes' . DIRECTORY_SEPARATOR . 'Frontend' . DIRECTORY_SEPARATOR;
            if (file_exists($pluginThemePath)) {
                // Add plugin theme snippets
                $directories = new DirectoryIterator(
                    $pluginThemePath
                );

                foreach ($directories as $directory) {
                    //check valid directory
                    if ($directory->isDot() || !$directory->isDir() || $directory->getFilename() === '_cache') {
                        continue;
                    }

                    $configDir['themes/' . strtolower($directory->getFilename()) . '/'] = $directory->getPathname() . '/_private/snippets/';
                }
            }
        }

        return $configDir;
    }

    /**
     * @return array<string, string>
     */
    private function getThemeDirs(?string $themeDir = null): array
    {
        $configDir = [];

        if ($themeDir === null) {
            return $configDir;
        }

        foreach (new DirectoryIterator(
            $themeDir
        ) as $directory) {
            //check valid directory
            if ($directory->isDot() || !$directory->isDir() || $directory->getFilename() === '_cache') {
                continue;
            }

            $configDir['themes/' . strtolower($directory->getFilename()) . '/'] = $directory->getPathname() . '/_private/snippets/';
        }

        return $configDir;
    }

    private function readFromDb(): bool
    {
        return $this->snippetConfig['readFromDb'];
    }

    private function readFromIni(string $key): bool
    {
        if (!$this->snippetConfig['readFromIni']) {
            return false;
        }
        if (!isset($this->fileAdapter)) {
            return false;
        }

        return !isset($this->namespaces[$key]) || \count($this->namespaces[$key]) === 0;
    }

    private function createDbNamespace(?string $namespace, int $shopId, int $localeId): Enlight_Components_Snippet_Namespace
    {
        return new $this->defaultNamespaceClass([
            'adapter' => $this->adapter,
            'name' => $namespace,
            'section' => [$shopId, $localeId],
            'extends' => $this->extends,
        ]);
    }

    private function createIniNamespace(?string $namespace): Enlight_Components_Snippet_Namespace
    {
        $fullNamespace = new $this->defaultNamespaceClass([
            'adapter' => $this->fileAdapter,
            'name' => $namespace,
            'section' => null,
        ]);

        $locale = $this->locale ? $this->locale->getLocale() : $this->getDefaultLocale()->getLocale();
        if (
            !\array_key_exists($locale, $fullNamespace->toArray())
            && \in_array($locale, ['en_GB', 'default'])
            && \count($fullNamespace->toArray())
        ) {
            $diff = array_diff(['en_GB', 'default'], [$locale]);
            if ($diff !== []) {
                $locale = array_shift($diff);
            }
        }

        $fullNamespace->setSection($locale);
        $fullNamespace->setData($fullNamespace->get($locale));

        return $fullNamespace;
    }

    private function requiresFallback(Enlight_Components_Snippet_Namespace $instance): bool
    {
        if ($instance->getFallback()) {
            return false;
        }
        if (!$this->locale) {
            return false;
        }
        if (\array_key_exists('showSnippetPlaceholder', $this->snippetConfig) && $this->snippetConfig['showSnippetPlaceholder']) {
            return false;
        }

        return $this->locale->getLocale() !== 'en_GB';
    }
}
