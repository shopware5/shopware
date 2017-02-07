<?php

namespace Shopware\Bundle\CustomerSearchBundle;

use Doctrine\DBAL\Connection;
use PDO;

class CustomerSearch
{
    /**
     * @var CustomerNumberSearch
     */
    private $numberSearch;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param CustomerNumberSearch $numberSearch
     * @param Connection $connection
     */
    public function __construct(CustomerNumberSearch $numberSearch, Connection $connection)
    {
        $this->numberSearch = $numberSearch;
        $this->connection = $connection;
    }

    public function search(Criteria $criteria)
    {
        $result = $this->numberSearch->search($criteria);

        $customers = $this->loadData($result->getIds());

        return new CustomerSearchResult(
            $result->getTotal(),
            $customers
        );
    }

    private function loadData($ids)
    {
        $customers = $this->fetchCustomers($ids);

        $aggregation = $this->createAmountQuery($ids);
        foreach ($aggregation as $customerId => $orders) {
            $customers[$customerId]['aggregation'] = $orders;
        }

        $interests = $this->createInterestsQuery($ids);
        foreach ($interests as $customerId => $orders) {
            $customers[$customerId]['interests'] = $orders;
        }

        return $customers;
    }


    /**
     * @param int[] $ids
     * @return array
     */
    private function createAmountQuery($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect([
            'orders.userID as customer_id',
            'COUNT(orders.id) count_orders',
            'SUM(orders.invoice_amount) as invoice_amount_sum',
            'AVG(orders.invoice_amount) as invoice_amount_avg',
            'MIN(orders.invoice_amount) as invoice_amount_min',
            'MAX(orders.invoice_amount) as invoice_amount_max',
            'MIN(orders.ordertime) as first_order_time',
            'MAX(orders.ordertime) as last_order_time',
        ]);
        $query->from('s_order', 'orders');
        $query->where('orders.ordernumber != 0');
        $query->andWhere('orders.status = 2');
        $query->where('orders.userID IN (:ids)');
        $query->setParameter(':ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);
        $query->groupBy('orders.userID');
        return $query->execute()->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);
    }

    /**
     * @param int[] $ids
     * @return array
     */
    private function createInterestsQuery($ids)
    {
        $sub = $this->connection->createQueryBuilder();
        $sub->select([
            'orders.userID',
            'details.articleID',
            'COUNT(details.articleID) as sales',
            'SUM(details.quantity) as quantity',
            'COUNT(details.articleID) + (SUM(details.quantity) / 30) as ranking',
            'multiplier.id as multiplierId',
        ]);
        $sub->from('s_order', 'orders');
        $sub->from('multiplier', 'multiplier');
        $sub->innerJoin('orders', 's_order_details', 'details', 'orders.id = details.orderID');

        $sub->where('multiplier.id <= 2');
        $sub->andWhere('details.modus = 0');
        $sub->andWhere('details.articleID > 0');
        $sub->addGroupBy('orders.userID');
        $sub->addGroupBy('details.articleID');
        $sub->addGroupBy('multiplier.id');
        $sub->orderBy('multiplier.id');
        $sub->addOrderBy('ranking', 'DESC');
        $sub->setMaxResults(2);

        $query = $this->connection->createQueryBuilder();
        $query->select([
            'u.id',
            'interests.*',
            'c.description as category',
            's.name as manufacturer'
        ]);

        $query->from('s_user', 'u');
        $query->innerJoin('u', '(' . $sub->getSQL() . ')', 'interests', 'interests.userID = u.id');
        $query->innerJoin('u', 's_articles_categories', 'ac', 'ac.articleID = interests.articleID');
        $query->innerJoin('u','s_categories', 'c', 'c.id = ac.categoryID');
        $query->innerJoin('u','s_articles', 'a', 'a.id = interests.articleID');
        $query->innerJoin('u','s_articles_supplier', 's', 's.id = a.supplierID');
        $query->groupBy('u.id');
        $query->addGroupBy('ac.articleID');

        $query->where('u.id IN (:ids)');
        $query->setParameter(':ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);
        return $query->execute()->fetchAll(PDO::FETCH_GROUP);
    }

    /**
     * @param int[] $ids
     * @return array
     */
    private function fetchCustomers($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'customer.id',
            'customer.id'
        ]);
        $query->from('s_user', 'customer');
        $query->where('customer.id IN (:ids)');
        $query->setParameter(':ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }
}