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

        $aggregation = $this->getCustomerOrders($ids);
        $products = [];
        foreach ($aggregation as $customerId => $orders) {
            $products = array_merge($products, explode(',', $orders['products']));
            unset($orders['products']);
            $customers[$customerId]['aggregation'] = $orders;
        }

        $products = array_keys(array_flip($products));

        $interests = $this->fetchInterests($ids, $products);
        foreach ($interests as $customerId => &$interest) {
            $customers[$customerId]['interests'] = $interest;
        }

        foreach ($customers as &$customer) {
            $customer['age'] = null;

            if ($customer['birthday']) {
                $customer['age'] = (new \DateTime($customer['birthday']))->diff(new \DateTime())->y;
            }
        }

        return $customers;
    }


    /**
     * @param int[] $ids
     * @return array
     */
    private function getCustomerOrders($ids)
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
            'GROUP_CONCAT(DISTINCT details.articleID) as products'
        ]);
        $query->from('s_order', 'orders');
        $query->where('orders.ordernumber != 0');
        $query->innerJoin('orders', 's_order_details', 'details', 'details.orderID = orders.id AND details.modus = 0');
//        $query->andWhere('orders.status = 2');
        $query->where('orders.userID IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->groupBy('orders.userID');
        return $query->execute()->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);
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
            'category.description as category',
            'product.name as product',
            'manufacturer.name as manufacturer',
            'COUNT(details.articleID) as sales',
            'COUNT(details.articleID) + (SUM(details.quantity) / 30) as ranking'
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
        $query->addGroupBy('details.articleID');
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
     * @param int[] $ids
     * @return array
     */
    private function fetchCustomers($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'customer.id',
            'customer.id',
            'customer.active',
            'customer.accountmode',
            'customer.firstlogin',
            'customer.newsletter',
            'customer.subshopID as shopId',
            'customer.default_billing_address_id',
            'customer.title',
            'customer.salutation',
            'customer.firstname',
            'customer.lastname',
            'customer.birthday',
            'customer.customernumber',
            'customerGroups.id as customerGroupId',
            'customerGroups.description as customerGroup',
            'payment.id as paymentId',
            'payment.description as payment',
            'shop.name as shop',
            'address.company',
            'address.department',
            'address.street',
            'address.zipcode',
            'address.city',
            'address.phone',
            'address.additional_address_line1',
            'address.additional_address_line2',
            'country.id as countryId',
            'country.countryname as country',
            'state.id as stateId',
            'state.name as state'
        ]);
        $query->from('s_user', 'customer');
        $query->where('customer.id IN (:ids)');
        $query->innerJoin('customer', 's_core_customergroups', 'customerGroups', 'customerGroups.groupkey = customer.customergroup');
        $query->leftJoin('customer', 's_core_paymentmeans', 'payment', 'payment.id = customer.paymentID');
        $query->leftJoin('customer', 's_core_shops', 'shop', 'shop.id = customer.subshopID');
        $query->leftJoin('customer', 's_user_addresses', 'address', 'address.id = customer.default_billing_address_id');
        $query->leftJoin('address', 's_core_countries', 'country', 'country.id = address.country_id');
        $query->leftJoin('address', 's_core_countries_states', 'state', 'state.id = address.state_id');

        $query->groupBy('customer.id');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }
}
