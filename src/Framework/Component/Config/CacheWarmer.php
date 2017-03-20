<?php

namespace Shopware\Framework\Component\Config;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheWarmer implements CacheWarmerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    public function __construct(Connection $connection, ConfigServiceInterface $configService)
    {
        $this->connection = $connection;
        $this->configService = $configService;
    }

    public function isOptional()
    {
        return false;
    }

    /**
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $shops = $this->getShops();

        foreach ($shops as $shop) {
            $this->configService->getByShop($shop);
        }
    }

    private function getShops()
    {
        $builder = $this->connection->createQueryBuilder();

        return $builder->select(['shop.*'])
                ->from('s_core_shops', 'shop')
                ->execute()
                ->fetchAll();
    }
}