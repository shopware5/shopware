<?php
declare(strict_types=1);

namespace Shopware\Framework\Config;

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

    public function isOptional(): bool
    {
        return false;
    }

    /**
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir): void
    {
        $shops = $this->getShops();

        foreach ($shops as $shop) {
            $this->configService->getByShop($shop);
        }
    }

    private function getShops(): array
    {
        $builder = $this->connection->createQueryBuilder();

        return $builder->select(['shop.*'])
                ->from('s_core_shops', 'shop')
                ->execute()
                ->fetchAll();
    }
}