<?php

namespace Shopware\Framework\Routing;

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

    public function __construct(SeoUrlReaderInterface $decoratedReader, CacheItemPoolInterface $cache)
    {
        $this->decoratedReader = $decoratedReader;
        $this->cache = $cache;
    }

    public function fetchUrl(int $shopId, string $seoUrl): ?string
    {
        $urls = $this->fetchAll($shopId);

        $urls = array_flip($urls);

        if (array_key_exists($seoUrl, $urls)) {
            return $urls[$seoUrl];
        }
        return null;
    }

    public function fetchSeoUrl(int $shopId, string $url): ?string
    {
        $urls = $this->fetchAll($shopId);

        if (array_key_exists($url, $urls)) {
            return $urls[$url];
        }
        return null;
    }

    public function fetchAll(int $shopId): array
    {
        $cacheKey = sprintf('shop_urls_%d', $shopId);

        $item = $this->cache->getItem($cacheKey);
        if ($item->isHit()) {
            return $item->get();
        }

        $urls = $this->decoratedReader->fetchAll($shopId);

        $item->set($urls);

        $this->cache->save($item);

        return $urls;
    }
}