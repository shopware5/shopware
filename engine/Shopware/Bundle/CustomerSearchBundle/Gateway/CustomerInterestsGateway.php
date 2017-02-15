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
     * @param null  $lastDays
     *
     * @return array
     */
    public function getInterests($customerIds, $lastDays = null)
    {
        $query = $this->createQuery(
            $customerIds,
            $this->getProductIds($customerIds),
            $lastDays
        );

        $data = $this->sortInterests(
            $query->execute()->fetchAll(PDO::FETCH_GROUP)
        );

        $interests = [];
        foreach ($data as $customerId => $rows) {
            $interests[$customerId] = $this->hydrate($rows);
        }

        return $interests;
    }

    /**
     * @param int[] $customerIds
     * @param null  $lastDays
     *
     * @return \int[]
     */
    private function getProductIds($customerIds, $lastDays = null)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('DISTINCT details.articleID');
        $query->from('s_order_details', 'details');
        $query->innerJoin('details', 's_order', 'orders', 'orders.id = details.orderID');
        $query->where('orders.userID IN (:ids)');
        $query->setParameter(':ids', $customerIds, Connection::PARAM_INT_ARRAY);

        if ($lastDays !== null) {
            $date = (new \DateTime())->sub(new \DateInterval('P' . (int) $lastDays . 'D'));
            $query->andWhere('orders.orderTime >= :date');
            $query->setParameter(':date', $date->format('Y-m-d'));
        }

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    private function hydrate(array $rows)
    {
        $structs = [];
        foreach ($rows as $row) {
            $struct = new InterestsStruct();
            $struct->setProductId($row['articleID']);
            $struct->setProductNumber($row['number']);
            $struct->setRanking($row['ranking']);
            $struct->setProductName($row['product']);
            $struct->setCategoryId($row['categoryId']);
            $struct->setCategoryName($row['category']);
            $struct->setManufacturerId($row['manufacturerId']);
            $struct->setManufacturerName($row['manufacturer']);
            $struct->setSales((int) $row['sales']);
            $structs[] = $struct;
        }

        return $structs;
    }

    /**
     * @param int[]    $ids
     * @param int[]    $products
     * @param int|null $lastDays
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createQuery($ids, $products, $lastDays = null)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'orders.userID',
            'details.articleID',
            'details.articleordernumber as number',
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
        $query->andWhere('orders.status != :cancelStatus');
        $query->andWhere('details.modus = 0');
        $query->andWhere('orders.ordernumber IS NOT NULL');
        $query->addGroupBy('orders.userID');
        $query->addGroupBy('mapping.articleID');
        $query->setParameter(':cancelStatus', -1);
        $query->setParameter(':users', $ids, Connection::PARAM_INT_ARRAY);
        $query->setParameter(':products', $products, Connection::PARAM_INT_ARRAY);

        if ($lastDays !== null) {
            $date = (new \DateTime())->sub(new \DateInterval('P' . (int) $lastDays . 'D'));
            $query->andWhere('orders.orderTime >= :date');
            $query->setParameter(':date', $date->format('Y-m-d'));
        }

        return $query;
    }

    /**
     * @param array $ranking
     *
     * @return array
     */
    private function sortInterests($ranking)
    {
        foreach ($ranking as $customerId => &$interests) {
            usort($interests, function ($a, $b) {
                return $a['ranking'] < $b['ranking'];
            });
        }

        return $ranking;
    }
}
