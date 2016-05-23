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


use Shopware\Components\Model\ModelManager;
use Shopware\Components\Snippet\DbAdapter;

/**
 * @category  Shopware
 * @package   Shopware\Components\Snippet
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Snippet_Manager extends Enlight_Components_Snippet_Manager
{
    /**
     * @var ModelManager the model manager
     */
    protected $modelManager;

    /**
     * @var array The config options provided in the global config.php file
     */
    protected $snippetConfig;

    /**
     * @var Shopware\Models\Shop\Locale
     */
    protected $locale;

    /**
     * @var Shopware\Models\Shop\Shop
     */
    protected $shop;

    /**
     * @var Enlight_Config_Adapter_File
     */
    protected $fileAdapter;

    /**
     * @var array
     */
    protected $extends = array();

    /**
     * @var array
     */
    private $pluginDirectories;

    /**
     * @param ModelManager $modelManager
     * @param array $pluginDirectories
     * @param array $snippetConfig
     */
    public function __construct(ModelManager $modelManager, array $pluginDirectories, array $snippetConfig)
    {
        $this->snippetConfig = $snippetConfig;
        $this->modelManager  = $modelManager;
        $this->pluginDirectories = $pluginDirectories;

        if ($this->snippetConfig['readFromIni']) {
            $configDir = $this->getConfigDirs();
            $this->fileAdapter = new Enlight_Config_Adapter_File(array(
                'configDir'   => $configDir,
                'allowWrites' => $snippetConfig['writeToIni']
            ));
        }

        $this->adapter = new DbAdapter(array(
            'table'           => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn'   => array('shopID', 'localeID'),
            'allowWrites'     => $snippetConfig['writeToDb']
        ));
    }

    /**
     * Returns a snippet model instance
     *
     * @param       string $namespace
     * @return      Enlight_Components_Snippet_Namespace
     * @deprecated  4.0 - 2012/04/01
     */
    public function getSnippet($namespace = null)
    {
        return parent::getNamespace($namespace);
    }

    /**
     * Returns a snippet model instance
     *
     * @param   string $namespace
     * @return  Enlight_Components_Snippet_Namespace
     */
    public function getNamespace($namespace = null)
    {
        $key = $namespace === null ? '__ignore' : (string) $namespace;

        if (!isset($this->namespaces[$key])) {
            if ($this->snippetConfig['readFromDb']) {
                $this->namespaces[$key] = new $this->defaultNamespaceClass(array(
                    'adapter' => $this->adapter,
                    'name' => $namespace,
                    'section' => array(
                        $this->shop ? $this->shop->getId() : 1,
                        $this->locale ? $this->locale->getId() : $this->getDefaultLocale()->getId(),
                    ),
                    'extends' => $this->extends,
                ));
            }

            if ($this->snippetConfig['readFromIni'] && (!isset($this->namespaces[$key]) || count($this->namespaces[$key]) == 0) && isset($this->fileAdapter)) {

                /** @var \Enlight_Components_Snippet_Namespace $fullNamespace */
                $fullNamespace = new $this->defaultNamespaceClass(array(
                    'adapter' => $this->fileAdapter,
                    'name' => $namespace,
                    'section' => null
                ));

                $locale = $this->locale ? $this->locale->getLocale() : $this->getDefaultLocale()->getLocale();
                if (
                    !array_key_exists($locale, $fullNamespace->toArray())
                    && in_array($locale, array('en_GB', 'default'))
                    && count(array_keys($fullNamespace->toArray()))
                ) {
                    $locale = array_shift(array_diff(array('en_GB', 'default'), array($locale)));
                }

                $fullNamespace->setSection($locale);
                $fullNamespace->setData($fullNamespace->get($locale));

                $this->namespaces[$key] = $fullNamespace;
            }
        }

        if (!isset($this->namespaces[$key])) {
            $this->namespaces[$key] = new $this->defaultNamespaceClass(array(
                'name' => $namespace,
            ));
        }
        return $this->namespaces[$key];
    }

    /**
     * Set locale instance
     *
     * @param   \Shopware\Models\Shop\Locale $locale
     * @return  Shopware_Components_Snippet_Manager
     */
    public function setLocale(\Shopware\Models\Shop\Locale $locale)
    {
        $this->locale = $locale;
        $this->namespaces = array();
        return $this;
    }

    /**
     * Set shop instance
     *
     * @param   \Shopware\Models\Shop\Shop $shop
     * @return  Shopware_Components_Snippet_Manager
     */
    public function setShop(\Shopware\Models\Shop\Shop $shop)
    {
        $this->shop = $shop;
        $this->locale = $shop->getLocale();
        $this->namespaces = array();
        $this->initExtends();
        return $this;
    }

    /**
     * @return \Shopware\Models\Shop\Locale
     */
    protected function getDefaultLocale()
    {
        return $this->modelManager->getRepository('Shopware\Models\Shop\Shop')->getDefault()->getLocale();
    }

    /**
     * Defines the 'extends' logic for snippet loading, responsible for the cascading fallbacks
     * between snippet sets
     */
    protected function initExtends()
    {
        $extends = array();
        $shop   = $this->shop;
        $locale = $this->locale;

        $main = $shop->getMain();
        if ($main !== null && $main->getId() === 1) {
            $main = null;
        }

        // fallback to parent shop, current locale
        if ($main !== null && $main->getId() !== 1) {
            $extends[] = array(
                $main->getId(),
                $locale->getId()
            );
        }

        // fallback to default shop, current locale
        if ($shop && $shop->getId() !== 1) {
            $extends[] = array(
                1,
                $locale->getId()
            );
        }


        /**
         * Fallback to parent shop, parent locale
         * If parent locale is the same as current locale, skip this step
         * as it was already added previously ("fallback to parent shop, current locale")
         **/
        if ($main !== null && $locale->getId() != $main->getLocale()->getId()) {
            $extends[] = array(
                $main->getId(),
                $main->getLocale()->getId(),
            );
        }

        // fallback to default shop, default language
        // this needs to be fixed, because its wrong for non-english installations
        if ($locale->getId() !== 1) {
            $extends[] = array(
                1,
                1,
            );
        }

        $this->extends = $extends;
    }

    /**
     * @param   $dir
     * @return  Shopware_Components_Snippet_Manager
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
     * @return string[]
     */
    protected function getConfigDirs()
    {
        $configDir = array();
        if (file_exists(Shopware()->DocPath('snippets'))) {
            $configDir[] = Shopware()->DocPath('snippets');
        }

        /** @var \Shopware\Models\Plugin\Plugin[] $plugins */
        $plugins = $this->modelManager->getRepository('Shopware\Models\Plugin\Plugin')->findByActive(true);
        foreach ($plugins as $plugin) {
            $pluginPath = $this->pluginDirectories[$plugin->getSource()] . $plugin->getNamespace() . DIRECTORY_SEPARATOR . $plugin->getName();

            // Add plugin snippets
            $pluginSnippetPath = $pluginPath . DIRECTORY_SEPARATOR . 'Snippets' . DIRECTORY_SEPARATOR;

            array_unshift($configDir, $pluginSnippetPath);

            $pluginThemePath = $pluginPath . DIRECTORY_SEPARATOR . 'Themes' . DIRECTORY_SEPARATOR . 'Frontend' . DIRECTORY_SEPARATOR;
            if (file_exists($pluginThemePath)) {
                // Add plugin theme snippets
                $directories = new \DirectoryIterator(
                    $pluginThemePath
                );

                /** @var $directory \DirectoryIterator */
                foreach ($directories as $directory) {
                    //check valid directory
                    if ($directory->isDot() || !$directory->isDir() || $directory->getFilename() == '_cache') {
                        continue;
                    }

                    $configDir['themes/' . strtolower($directory->getFilename()) . '/'] = $directory->getPathname() . '/_private/snippets/';
                }
            }
        }

        return $configDir;
    }
}
