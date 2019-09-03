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

use Doctrine\DBAL\Connection;
use Shopware\Components\CacheManager;
use Shopware\Components\ShopwareReleaseStruct;

/**
 * Shopware Config Model
 */
class Shopware_Components_Config implements ArrayAccess
{
    /**
     * @var Shopware\Models\Shop\Shop
     */
    protected $_shop;

    /**
     * @var Zend_Cache_Core
     */
    protected $_cache;

    /**
     * @var bool|int
     */
    protected $_cacheTime = false;

    /**
     * @var string[]
     */
    protected $_cacheTags = [CacheManager::ITEM_TAG_CONFIG];

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var Connection
     */
    protected $_db;

    /**
     * @var ShopwareReleaseStruct
     */
    protected $release;

    public function __construct(array $config)
    {
        $this->release = $config['release'];

        if (isset($config['cache'])
            && $config['cache'] instanceof Zend_Cache_Core) {
            $this->_cache = $config['cache'];
        }
        if (isset($config['db'])
            && $config['db'] instanceof Connection) {
            $this->_db = $config['db'];
        }
        if (isset($config['shop'])) {
            $this->setShop($config['shop']);
        } else {
            $this->load();
        }
    }

    /**
     * Magic getter
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * Magic getter
     *
     * @param string $name
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * Magic setter
     *
     * @param string $name
     */
    public function __set($name, $value)
    {
        return $this->offsetSet($name, $value);
    }

    /**
     * Magic caller method
     *
     * @param string $name
     * @param array  $args
     */
    public function __call($name, $args = null)
    {
        return $this->get($name);
    }

    /**
     * @param Shopware\Models\Shop\Shop $shop
     *
     * @return Shopware_Components_Config
     */
    public function setShop($shop)
    {
        $this->_shop = $shop;
        $this->load();
        $this->offsetSet('host', $shop->getHost());
        $this->offsetSet('basePath', $shop->getHost() . $shop->getBasePath());
        if ($shop->getTitle() !== null) {
            $this->offsetSet('shopName', $shop->getTitle());
        }

        return $this;
    }

    /**
     * Format name method
     *
     * @param string $name
     *
     * @return string
     */
    public function formatName($name)
    {
        if (strpos($name, 's') === 0 && preg_match('#^s[A-Z]#', $name)) {
            $name = substr($name, 1);
        }

        return str_replace('_', '', strtolower($name));
    }

    /**
     * Get config by namespace (form). Each config name is unique by namespace + name
     *
     * @param string $namespace
     * @param string $name
     */
    public function getByNamespace($namespace, $name, $default = null)
    {
        return $this->get($namespace . '::' . $name, $default);
    }

    /**
     * @param string $name
     */
    public function get($name, $default = null)
    {
        $value = $this->offsetGet($name);

        return $value !== null ? $value : $default;
    }

    /**
     * @param string $name
     */
    public function offsetGet($name)
    {
        if (!isset($this->_data[$name])) {
            $baseName = $this->formatName($name);
            if (!isset($this->_data[$baseName])) {
                $this->_data[$baseName] = null;
            }
            $this->_data[$name] = &$this->_data[$baseName];
        }

        return $this->_data[$name];
    }

    /**
     * @param string $name
     */
    public function offsetUnset($name)
    {
        $this->_data[$name] = null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        if (!isset($this->_data[$name])) {
            $baseName = $this->formatName($name);

            return isset($this->_data[$baseName]) && $this->_data[$baseName] !== null;
        }

        return true;
    }

    /**
     * @param string $name
     */
    public function offsetSet($name, $value)
    {
        $baseName = $this->formatName($name);

        return $this->_data[$baseName] = $value;
    }

    /**
     * Load data from cache or database
     */
    protected function load()
    {
        if ($this->_cache !== null) {
            $cacheId = CacheManager::ITEM_TAG_CONFIG;
            if ($this->_shop !== null) {
                $cacheId .= '_' . $this->_shop->getId();
            }
            if (($this->_data = $this->_cache->load($cacheId)) === false) {
                $this->_data = $this->readData();
                $this->_cache->save(
                    $this->_data,
                    $cacheId,
                    $this->_cacheTags,
                    $this->_cacheTime
                );
            }
        } else {
            $this->_data = $this->readData();
        }
    }

    /**
     * Read data with translations from database
     *
     * @return array
     */
    protected function readData()
    {
        $builder = $this->_db->createQueryBuilder();
        $builder
            ->select([
                'LOWER(REPLACE(e.name, "_", "")) as name',
                'COALESCE(currentShop.value, parentShop.value, fallbackShop.value, e.value) as value',
                'LOWER(REPLACE(forms.name, "_", "")) as form',
                'currentShop.value as currentShopval',
                'parentShop.value as parentShopval',
                'fallbackShop.value as fallbackShopval',
            ])
            ->from('s_core_config_elements', 'e')
            ->leftJoin('e', 's_core_config_values', 'currentShop',
                'currentShop.element_id = e.id AND currentShop.shop_id = :currentShopId')
            ->leftJoin('e', 's_core_config_values', 'parentShop',
                'parentShop.element_id = e.id AND parentShop.shop_id = :parentShopId')
            ->leftJoin('e', 's_core_config_values', 'fallbackShop',
                'fallbackShop.element_id = e.id AND fallbackShop.shop_id = :fallbackShopId')
            ->leftJoin('e', 's_core_config_forms', 'forms', 'forms.id = e.form_id');

        $builder->setParameters([
            'fallbackShopId' => 1, // Shop parent id
            'parentShopId' => isset($this->_shop) && $this->_shop->getMain() !== null ? $this->_shop->getMain()->getId() : 1,
            'currentShopId' => isset($this->_shop) ? $this->_shop->getId() : null,
        ]);

        $data = $builder->execute()->fetchAll();

        $result = [];
        foreach ($data as $row) {
            $value = !empty($row['value']) ? @unserialize($row['value'], ['allowed_classes' => false]) : null;
            $result[$row['name']] = $value;
            // Take namespaces (form names) into account
            $result[$row['form'] . '::' . $row['name']] = $value;
        }

        $result['version'] = $this->release->getVersion();
        $result['revision'] = $this->release->getRevision();
        $result['versiontext'] = $this->release->getVersionText();

        return $result;
    }
}
