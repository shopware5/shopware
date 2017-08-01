<?php
declare(strict_types=1);

namespace Shopware\Framework\Config;

use Psr\Cache\CacheItemPoolInterface;

class CachedConfigService implements ConfigServiceInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(ConfigServiceInterface $configService, CacheItemPoolInterface $cache)
    {
        $this->configService = $configService;
        $this->cache = $cache;
    }

    public function getByShop(array $shop): array
    {
        $cacheKey = sprintf('shop_config_%d', $shop['id']);

        $item = $this->cache->getItem($cacheKey);
        if ($item->isHit()) {
            return $item->get();
        }

        $config = $this->configService->getByShop($shop);
        $item->set($config);

        $this->cache->save($item);

        return $config;
    }

    public function hydrate(array $config): array
    {
        return $this->configService->hydrate($config);
    }
}