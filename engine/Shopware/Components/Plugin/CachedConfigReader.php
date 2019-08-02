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

namespace Shopware\Components\Plugin;

use Shopware\Components\CacheManager;
use Shopware\Models\Shop\Shop;
use Zend_Cache_Core as Cache;

class CachedConfigReader implements ConfigReader
{
    /**
     * @var ConfigReader
     */
    private $reader;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(ConfigReader $reader, Cache $cache)
    {
        $this->reader = $reader;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getByPluginName($pluginName, Shop $shop = null)
    {
        if ($shop) {
            $cacheKey = $pluginName . $shop->getId();
        } else {
            $cacheKey = $pluginName;
        }

        if ($this->cache->test($cacheKey)) {
            return $this->cache->load($cacheKey, true);
        }

        $config = $this->reader->getByPluginName($pluginName, $shop);

        $this->cache->save($config, $cacheKey, [CacheManager::ITEM_TAG_CONFIG, CacheManager::ITEM_TAG_PLUGIN_CONFIG . strtolower($pluginName)], 86400);

        return $config;
    }
}
