<?php

namespace Shopware\Framework\Routing;

use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\RequestContext;

class ShopFinder
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findShopByRequest(RequestContext $requestContext): array
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['shop.*', 'locale.locale']);
        $query->from('s_core_shops', 'shop');
        $query->innerJoin('shop', 's_core_locales', 'locale', 'locale.id=shop.locale_id');

        $shops = $query->execute()->fetchAll();

        array_walk($shops, function (&$shop) {
            $shop['base_path'] = rtrim($shop['base_path'], '/') . '/';
        });

        $url = rtrim($requestContext->getPathInfo(), '/') . '/';

        $matching = array_filter($shops, function($shop) use ($url) {
            return strpos($url, $shop['base_path']) === 0;
        });

        $bestMatch = ['id' => null, 'base_path' => null];
        foreach ($matching as $match) {
            if (strlen($match['base_path']) > strlen($bestMatch['base_path'])) {
                $bestMatch = $match;
            }
        }

        return $bestMatch;
    }
}