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

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Shopware\Components\Plugin\Configuration\Layers\ConfigurationLayerInterface;

class CachedReader implements ReaderInterface
{
    /** @var ConfigurationLayerInterface */
    private $layer;

    /** @var CacheInterface */
    private $cache;

    public function __construct(ConfigurationLayerInterface $lastLayer, CacheInterface $cache)
    {
        $this->layer = $lastLayer;
        $this->cache = $cache;
    }

    public function getByPluginName(string $pluginName, ?int $shopId = null): array
    {
        $cacheKey = $this->buildCacheKey($pluginName, $shopId);

        try {
            if ($this->cache->has($cacheKey)) {
                return (array) $this->cache->get($cacheKey);
            }
        } catch (InvalidArgumentException $e) {
            // progress normally
        }

        $readValues = $this->layer->readValues($shopId, $pluginName);

        try {
            $this->cache->set($cacheKey, $readValues);
        } catch (InvalidArgumentException $e) {
            // progress normally
        }

        return $readValues;
    }

    public function buildCacheKey(string $pluginName, ?int $shopId = null): string
    {
        return $shopId ? $shopId . $pluginName : $pluginName;
    }
}
