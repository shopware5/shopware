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

/**
 * Shopware Snippet Manager
 */
class Shopware_Components_Snippet_Manager extends Enlight_Components_Snippet_Manager
{
    /**
     * @var Shopware\Models\Shop\Locale
     */
    protected $locale;

    /**
     * @var Shopware\Models\Shop\Shop
     */
    protected $shop;

    /**
     * @var array
     */
    protected $extends = array();

    protected $cache;
    protected $fileAdapter;
    protected $defaultSection = array(1, 1);

    /**
     * @var array
     */
    protected $configDir = array();

    public function __construct()
    {
        $this->configDir[] = Shopware()->DocPath('snippets');
        $this->fileAdapter = new Enlight_Config_Adapter_File(array(
            'configDir' => $this->configDir
        ));
        $this->adapter = new Enlight_Config_Adapter_DbTable(array(
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => array('shopID', 'localeID')
        ));
    }

    /**
     * Returns a snippet model instance
     *
     * @param       string $namespace
     * @return      Enlight_Components_Snippet_Namespace
     * @deprecated  4.0 - 2012/04/01
     */
    public function getSnippet($namespace)
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
        $key = $namespace === null ? '__ignore' : (string)$namespace;
        if (!isset($this->namespaces[$key])) {
            if (isset($this->fileAdapter) && strpos($namespace, 'backend/') === 0) {
                if (isset($this->locale) && $this->locale->toString() == 'de_DE') {
                    $section = array('de_DE', 'default');
                } else {
                    $section = array('default');
                }
                $this->namespaces[$key] = new $this->defaultNamespaceClass(array(
                    'adapter' => $this->fileAdapter,
                    'name' => $namespace,
                    'section' => $section
                ));
            } elseif ($this->shop !== null) {
                $this->namespaces[$key] = new $this->defaultNamespaceClass(array(
                    'adapter' => $this->adapter,
                    'name' => $namespace,
                    'section' => array(
                        $this->shop->getId(),
                        $this->locale->getId()
                    ),
                    'extends' => $this->extends,
                ));
            } else {
                $this->namespaces[$key] = new $this->defaultNamespaceClass(array(
                    'adapter' => $this->adapter,
                    'name' => $namespace,
                    'section' => $this->defaultSection
                ));
            }
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
     * Set cache instance
     *
     * @param   Zend_Cache_Core $cache
     * @return  Shopware_Components_Snippet_Manager
     */
    public function setCache(Zend_Cache_Core $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Init extends info
     */
    protected function initExtends()
    {
        $extends = array();

        $shop = $this->shop;
        $locale = $this->locale;
        $main = $shop->getMain();
        if($main !== null && $main->getId() === 1 && $main->getLocale()->getId() === 1) {
            $main = null;
        }

        if($main !== null && $main->getId() !== 1) {
            $extends[] = array(
                $main->getId(),
                $locale->getId()
            );
        }
        if($shop->getId() !== 1) {
            $extends[] = array(
                1, $locale->getId()
            );
        }
        if($main !== null) {
            $extends[] = array(
                $main->getId(),
                $main->getLocale()->getId(),
            );
        }
        if($locale->getId() !== 1) {
            $extends[] = array(
                1, 1,
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
        $this->fileAdapter->addConfigDir($dir);
        return $this;
    }
}
