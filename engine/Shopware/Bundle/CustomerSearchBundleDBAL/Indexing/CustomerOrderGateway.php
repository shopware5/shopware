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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Indexing;

use Doctrine\DBAL\Connection;

class CustomerOrderGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CustomerOrderHydrator
     */
    private $hydrator;

    public function __construct(Connection $connection, CustomerOrderHydrator $hydrator)
    {
        $this->connection = $connection;
        $this->hydrator = $hydrator;
    }

    /**
     * @param int[] $customerIds
     *
     * @return CustomerOrder[] indexed by customer id
     */
    public function getList($customerIds)
    {
        $data = $this->getCustomerOrders($customerIds);

        $amount = $this->fetchAmount($customerIds);

        $canceled = $this->getCustomersWithCanceledOrders($customerIds);

        $products = $this->fetchProducts($customerIds);

        $structs = [];
        foreach ($customerIds as $customerId) {
            $customerData = [];
            if (array_key_exists($customerId, $data)) {
                $customerData = $data[$customerId];
            }

            if (array_key_exists($customerId, $products)) {
                $customerData = array_merge($customerData, $products[$customerId]);
            }

            if (array_key_exists($customerId, $amount)) {
                $customerData = array_merge($customerData, $amount[$customerId]);
            }

            $struct = $this->hydrator->hydrate($customerData);

            $struct->setHasCanceledOrders(in_array($customerId, $canceled));

            $structs[$customerId] = $struct;
        }

        return $structs;
    }

    /**
     * @param int[] $ids
     *
     * @return array
     */
    private function fetchAmount($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect([
            'orders.userID as customer_id',
            'ROUND(SUM(orders.invoice_amount / orders.currencyFactor), 2)  as invoice_amount_sum',
            'ROUND(AVG(orders.invoice_amount / orders.currencyFactor), 2) as invoice_amount_avg',
        ]);
        $query->from('s_order', 'orders');
        $query->andWhere('orders.status != :cancelStatus');
        $query->andWhere('orders.ordernumber IS NOT NULL');
        $query->andWhere("orders.ordernumber != '0'");
        $query->andWhere('orders.userID IN (:ids)');
        $query->setParameter(':cancelStatus', -1);
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->groupBy('orders.userID');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }

    /**
     * @param int[] $ids
     *
     * @return array
     */
    private function getCustomerOrders($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect([
            'orders.userID as customer_id',
            'COUNT(DISTINCT orders.id) count_orders',

            'MIN(orders.invoice_amount / orders.currencyFactor) as invoice_amount_min',
            'MAX(orders.invoice_amount / orders.currencyFactor) as invoice_amount_max',
            'MIN(orders.ordertime) as first_order_time',
            'MAX(orders.ordertime) as last_order_time',
            'ROUND(AVG(details.price / orders.currencyFactor), 2) as product_avg',
            "GROUP_CONCAT(DISTINCT paymentID SEPARATOR ',') as selected_payments",
            "GROUP_CONCAT(DISTINCT orders.dispatchID SEPARATOR ',') as selected_dispachtes",
            "GROUP_CONCAT(DISTINCT orders.subshopID SEPARATOR ',') as ordered_in_shops",
            "GROUP_CONCAT(DISTINCT orders.deviceType SEPARATOR ',') as ordered_with_devices",
            "GROUP_CONCAT(DISTINCT LOWER(DAYNAME(orders.ordertime)) SEPARATOR ',') as weekdays",
        ]);
        $query->from('s_order', 'orders');
        $query->andWhere('orders.status != :cancelStatus');
        $query->andWhere('orders.ordernumber IS NOT NULL');
        $query->andWhere("orders.ordernumber != '0'");
        $query->innerJoin('orders', 's_order_details', 'details', 'details.orderID = orders.id AND details.modus = 0');
        $query->andWhere('orders.userID IN (:ids)');
        $query->setParameter(':cancelStatus', -1);
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->groupBy('orders.userID');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }

    /**
     * @param int[] $ids
     *
     * @return int[]
     */
    private function getCustomersWithCanceledOrders($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect(['orders.userID']);
        $query->from('s_order', 'orders');
        $query->andWhere('orders.status = :cancelStatus');
        $query->andWhere('orders.userID IN (:ids)');
        $query->setParameter(':cancelStatus', -1);
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->groupBy('orders.userID');

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function fetchProducts($customerIds)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'orders.userID',
            "GROUP_CONCAT(DISTINCT details.articleordernumber SEPARATOR ',') as product_numbers",
            "GROUP_CONCAT(DISTINCT categories.categoryID SEPARATOR ',') as category_ids",
            "GROUP_CONCAT(DISTINCT product.supplierID SEPARATOR ',') as manufacturer_ids",
        ]);
        $query->from('s_order', 'orders');
        $query->innerJoin('orders', 's_core_shops', 'shops', 'shops.id = orders.subshopID');
        $query->innerJoin('orders', 's_order_details', 'details', 'details.orderID = orders.id AND details.modus = 0');
        $query->innerJoin('details', 's_articles', 'product', 'product.id = details.articleID');
        $query->innerJoin('details', 's_articles_categories_ro', 'mapping', 'mapping.articleID = details.articleID AND shops.category_id = mapping.categoryID');
        $query->innerJoin('mapping', 's_articles_categories_ro', 'categories', 'categories.articleID = mapping.articleID AND categories.parentCategoryID = mapping.parentCategoryID');

        $query->andWhere('orders.userID IN (:customerIds)');
        $query->setParameter(':customerIds', $customerIds, Connection::PARAM_INT_ARRAY);

        $query->andWhere('orders.status != :cancelStatus');
        $query->andWhere('orders.ordernumber IS NOT NULL');
        $query->andWhere("orders.ordernumber != '0'");
        $query->setParameter(':cancelStatus', -1);

        $query->groupBy('orders.userID');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }
}
