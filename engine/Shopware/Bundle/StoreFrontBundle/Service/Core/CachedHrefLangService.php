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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Service\HrefLangServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\CacheManager;
use Zend_Cache_Core as Cache;

class CachedHrefLangService implements HrefLangServiceInterface
{
    /**
     * @var HrefLangServiceInterface
     */
    private $hrefLangService;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(HrefLangServiceInterface $hrefLangService, Cache $cache)
    {
        $this->hrefLangService = $hrefLangService;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(array $parameters, ShopContextInterface $contextService)
    {
        $shop = $contextService->getShop();
        $cacheKey = md5(json_encode($parameters) . ($shop->getParentId() ?: $shop->getId()));

        if ($urls = $this->cache->load($cacheKey)) {
            return $urls;
        }

        $urls = $this->hrefLangService->getUrls($parameters, $contextService);

        $this->cache->save($urls, $cacheKey, [CacheManager::ITEM_TAG_CONFIG], 86400);

        return $urls;
    }
}
