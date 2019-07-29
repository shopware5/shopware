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

namespace Shopware\Components\Routing\RewriteGenerator;

use Doctrine\DBAL\Query\QueryBuilder;
use Zend_Cache_Core as Cache;

class CachedRepository implements RepositoryInterface
{
    /**
     * @var RepositoryInterface
     */
    private $innerService;

    private $cache;

    private $lifeTime;

    private $tags;

    public function __construct(RepositoryInterface $innerService, Cache $cache, int $lifeTime = 86400, array $tags = null)
    {
        $this->innerService = $innerService;
        $this->cache = $cache;
        if ($tags === null) {
            $tags = [str_replace('\\', '_', RepositoryInterface::class)];
        }
        $this->tags = $tags;
        $this->lifeTime = $lifeTime;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->innerService->getQueryBuilder();
    }

    public function rewriteList(array $list, $shopId): array
    {
        $keys = array_keys($list);
        $list = array_values($list);

        $cacheKey = $this->getCacheKey($list, $shopId);

        if ($this->cache->test($cacheKey)) {
            $result = $this->cache->load($cacheKey, true);

            return array_combine($keys, $result);
        }

        $result = [];
        foreach ($list as $key => $row) {
            $rowCacheKey = $this->getCacheKey([$row], $shopId);
            if ($this->cache->test($rowCacheKey)) {
                $value = $this->cache->load($rowCacheKey, true);
                $result[$key] = array_pop($value);
            }
        }

        if (count($list) === count($result)) {
            return $this->merge($keys, $result);
        }

        $result = $this->innerService->rewriteList($list, $shopId);

        $this->cache->save($result, $cacheKey, $this->tags, $this->lifeTime);

        if (count($list) > 1) {
            foreach ($list as $key => $row) {
                if (!empty($result[$key])) {
                    $rowCacheKey = $this->getCacheKey([$row], $shopId);
                    $this->cache->save([$result[$key]], $rowCacheKey, $this->tags, $this->lifeTime);
                }
            }
        }

        return $this->merge($keys, $result);
    }

    private function merge($keys, $result)
    {
        $result2 = [];
        foreach ($keys as $index => $key) {
            $result2[$key] = $result[$index];
        }

        return $result2;
    }

    private function getCacheKey(array $list, int $shopId): string
    {
        return str_replace('\\', '_', RepositoryInterface::class) . '_' . $shopId . '_' . md5(implode($list, '?'));
    }
}
