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

namespace Shopware\Bundle\SitemapBundle\Service;

use Shopware\Bundle\SitemapBundle\SitemapLockInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CoreCache;
use Shopware\Models\Shop\Shop;

class SitemapLock implements SitemapLockInterface
{
    /**
     * @var CoreCache
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @param string $cacheKey
     */
    public function __construct(CoreCache $cache, $cacheKey = 'sitemap-exporter-running-%s')
    {
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
    }

    /**
     * @param int $lifeTime
     *
     * @return bool
     */
    public function doLock(Shop $shop, $lifeTime = 3600)
    {
        if ($this->isLocked($shop)) {
            return false;
        }

        $data = sprintf('Locked: %s', (new \DateTime('NOW', new \DateTimeZone('UTC')))->format(\DateTime::ATOM));

        $this->cache->save($this->generateCacheKeyForShop($shop), $data, $lifeTime);

        return true;
    }

    /**
     * @return bool
     */
    public function unLock(Shop $shop)
    {
        $this->cache->save($this->generateCacheKeyForShop($shop), null, -1);

        return true;
    }

    /**
     * @return bool
     */
    public function isLocked(Shop $shop)
    {
        $cacheKey = $this->generateCacheKeyForShop($shop);

        return $this->cache->fetch($cacheKey) !== false;
    }

    /**
     * @return string
     */
    private function generateCacheKeyForShop(Shop $shop)
    {
        return sprintf($this->cacheKey, $shop->getId());
    }
}
