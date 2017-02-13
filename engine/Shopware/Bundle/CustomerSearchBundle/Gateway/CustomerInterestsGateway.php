<?php

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

use Doctrine\DBAL\Connection;
use PDO;

class CustomerInterestsGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int[] $customerIds
     * @return array
     */
    public function getList($customerIds)
    {
        $data = $this->fetchInterests($customerIds, $this->getProductIds($customerIds));

        $interests = [];
        foreach ($data as $customerId => $rows) {
            $interests[$customerId] = array_map(function ($row) {
                $this->hydrate($row);
            }, $rows);
        }
        return $interests;
    }

    /**
     * @param int[] $ids
     * @param $products
     * @return array
     */
    private function fetchInterests($ids, $products)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'orders.userID',
            'details.articleID',
            'details.articleordernumber',
            'COUNT(details.articleID) + (SUM(details.quantity) / 30) as ranking',
            'category.description as category',
            'category.id as categoryId',
            'product.name as product',
            'manufacturer.name as manufacturer',
            'manufacturer.id as manufacturerId',
            'COUNT(details.articleID) as sales',
        ]);

        $query->from('s_order', 'orders');
        $query->innerJoin('orders', 's_order_details', 'details', 'orders.id = details.orderID');
        $query->innerJoin('details', 's_articles', 'product', 'product.id = details.articleID');
        $query->innerJoin('product', 's_articles_categories', 'mapping', 'mapping.articleID = product.id');
        $query->innerJoin('mapping', 's_categories', 'category', 'category.id = mapping.categoryID');
        $query->innerJoin('product', 's_articles_supplier', 'manufacturer', 'manufacturer.id = product.supplierID');
        $query->andWhere('orders.userID IN (:users)');
        $query->andWhere('details.articleID IN (:products)');
        $query->andWhere('details.modus = 0');
        $query->addGroupBy('orders.userID');
        $query->addGroupBy('category.articleID');
        $query->setParameter(':users', $ids, Connection::PARAM_INT_ARRAY);
        $query->setParameter(':products', $products, Connection::PARAM_INT_ARRAY);
        $ranking = $query->execute()->fetchAll(PDO::FETCH_GROUP);

        foreach ($ranking as $customerId => &$interests) {
            usort($interests, function ($a, $b) {
                return $a['ranking'] < $b['ranking'];
            });
        }

        return $ranking;
    }

    /**
     * @param int[] $customerIds
     * @return int[]
     */
    private function getProductIds($customerIds)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('DISTINCT details.articleID');
        $query->from('s_order_details', 'details');
        $query->innerJoin('details', 's_order', 'orders', 'orders.id = details.orderID');
        $query->where('orders.userID IN (:ids)');
        $query->setParameter(':ids', $customerIds, Connection::PARAM_INT_ARRAY);
        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    private function hydrate($row)
    {
        return new InterestsStruct();
    }
}
