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

namespace Shopware\Components\Plugin\Configuration;

use Shopware\Components\CacheManager;
use Shopware\Components\Plugin\Configuration\Layers\ConfigurationLayerInterface;
use Zend_Cache_Core;
use Zend_Cache_Exception;

class CachedReader implements ReaderInterface
{
    /**
     * @var ConfigurationLayerInterface
     */
    private $layer;

    /**
     * @var Zend_Cache_Core
     */
    private $cache;

    public function __construct(ConfigurationLayerInterface $lastLayer, Zend_Cache_Core $cache)
    {
        $this->layer = $lastLayer;
        $this->cache = $cache;
    }

    public function getByPluginName(string $pluginName, ?int $shopId = null): array
    {
        $cacheKey = $this->buildCacheKey($pluginName, $shopId);

        if ($this->cache->test($cacheKey) !== false) {
            $cacheResult = $this->cache->load($cacheKey, true);

            if (\is_array($cacheResult)) {
                return $cacheResult;
            }
        }

        $readValues = $this->layer->readValues($pluginName, $shopId);

        try {
            $this->cache->save(
                $readValues,
                $cacheKey,
                [CacheManager::ITEM_TAG_CONFIG, CacheManager::ITEM_TAG_PLUGIN_CONFIG . strtolower($pluginName)],
                86400
            );
        } catch (Zend_Cache_Exception $e) {
            // progress normally
        }

        return $readValues;
    }

    public function buildCacheKey(string $pluginName, ?int $shopId = null): string
    {
        return $shopId ? $shopId . $pluginName : $pluginName;
    }
}
