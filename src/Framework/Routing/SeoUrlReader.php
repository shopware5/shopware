<?php

namespace Shopware\Framework\Routing;

use Doctrine\DBAL\Connection;

class SeoUrlReader implements SeoUrlReaderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchUrl(int $shopId, string $seoUrl): ?string
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['url']);
        $query->from('seo_route');
        $query->andWhere('seo_url = :seoUrl');
        $query->andWhere('shop_id = :shopId');
        $query->setParameter(':shopId', $shopId);
        $query->setParameter(':seoUrl', $seoUrl);

        $url = $query->execute()->fetch(\PDO::FETCH_COLUMN);
        return $url ?: null;
    }

    public function fetchSeoUrl(int $shopId, string $url): ?string
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['seo_url']);
        $query->from('seo_route');
        $query->andWhere('shop_id = :shopId');
        $query->andWhere('url = :url');
        $query->setParameter(':shopId', $shopId);
        $query->setParameter(':url', $url);

        $seoUrl = $query->execute()->fetch(\PDO::FETCH_COLUMN);
        return $seoUrl ?: null;
    }

    public function fetchAll(int $shopId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['url', 'seo_url']);
        $query->from('seo_route');
        $query->andWhere('shop_id = :shopId');
        $query->setParameter(':shopId', $shopId);

        return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}