<?php

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

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

    /**
     * @param Connection $connection
     * @param CustomerOrderHydrator $hydrator
     */
    public function __construct(Connection $connection, CustomerOrderHydrator $hydrator)
    {
        $this->connection = $connection;
        $this->hydrator = $hydrator;
    }

    /**
     * @param int[] $customerIds
     * @return CustomerOrderStruct[] indexed by customer id
     */
    public function getList($customerIds)
    {
        $data = $this->getCustomerOrders($customerIds);

        $structs = [];
        foreach ($customerIds as $customerId) {
            if (!array_key_exists($customerId, $data)) {
                $structs[$customerId] = new CustomerOrderStruct();
                continue;
            }
            $structs[$customerId] = $this->hydrator->hydrate($data[$customerId]);
        }
        return $structs;
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
            'COUNT(DISTINCT orders.id) count_orders',
            'ROUND(SUM(orders.invoice_amount), 2) as invoice_amount_sum',
            'ROUND(AVG(orders.invoice_amount), 2) as invoice_amount_avg',
            'MIN(orders.invoice_amount) as invoice_amount_min',
            'MAX(orders.invoice_amount) as invoice_amount_max',
            'MIN(orders.ordertime) as first_order_time',
            'MAX(orders.ordertime) as last_order_time',
            'ROUND(AVG(details.price), 2) as product_avg',
            "GROUP_CONCAT(DISTINCT paymentID SEPARATOR ',') as selected_payments",
            "GROUP_CONCAT(DISTINCT orders.dispatchID SEPARATOR ',') as selected_dispachtes",
            "GROUP_CONCAT(DISTINCT orders.subshopID SEPARATOR ',') as ordered_in_shops",
            "GROUP_CONCAT(DISTINCT orders.deviceType SEPARATOR ',') as ordered_with_devices",
            "GROUP_CONCAT(DISTINCT LOWER(DAYNAME(orders.ordertime)) SEPARATOR ',') as weekdays",
            '(SELECT 1 FROM s_order o2 WHERE status = -1 AND o2.userID = orders.userID LIMIT 1) as has_canceled_orders'
        ]);
        $query->from('s_order', 'orders');
        $query->where('orders.ordernumber != 0');
        $query->innerJoin('orders', 's_order_details', 'details', 'details.orderID = orders.id AND details.modus = 0');
        $query->where('orders.userID IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->groupBy('orders.userID');
        return $query->execute()->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
    }
}
