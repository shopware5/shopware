<?php

namespace Shopware\Framework\Routing;

use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;

class CachedSeoUrlReader implements SeoUrlReaderInterface
{
    /**
     * @var SeoUrlReaderInterface
     */
    private $decoratedReader;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        SeoUrlReaderInterface $decoratedReader,
        CacheItemPoolInterface $cache,
        Connection $connection
    ) {
        $this->decoratedReader = $decoratedReader;
        $this->cache = $cache;
        $this->connection = $connection;
    }


    public function fetchUrl(int $shopId, string $seoUrl): ?string
    {
        $urls = $this->fetchAll($shopId);

        $urls = array_flip($urls);

        if (array_key_exists($seoUrl, $urls)) {
            return $urls[$seoUrl];
        }
        return $this->decoratedReader->fetchUrl($shopId, $seoUrl);
    }

    public function fetchSeoUrl(int $shopId, string $url): ?string
    {
        $urls = $this->fetchAll($shopId);

        if (array_key_exists($url, $urls)) {
            return $urls[$url];
        }
        return $this->decoratedReader->fetchSeoUrl($shopId, $url);
    }

    private function fetchAll(int $shopId): array
    {
        $cacheKey = sprintf('shop_urls_%d', $shopId);

        $item = $this->cache->getItem($cacheKey);
        if ($item->isHit()) {
            return $item->get();
        }

        $query = $this->connection->createQueryBuilder();
        $query->select(['url', 'seo_url']);
        $query->from('seo_route');
        $query->andWhere('shop_id = :shopId');
        $query->setParameter(':shopId', $shopId);

        $urls = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
        $item->set($urls);

        $this->cache->save($item);

        return $urls;
    }
}