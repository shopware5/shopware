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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;

class OrdersProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'orders';
    }

    /**
     * @return array
     */
    public function getBenchmarkData()
    {
        return [
            'dateTime' => date('Y-m-d H:i:s'),
            'numbers' => $this->getOrderNumbers(),
            'list' => $this->getOrdersList(),
        ];
    }

    /**
     * @return array
     */
    private function getOrderNumbers()
    {
        return [
            'total' => $this->getTotalOrders(),
            'revenue' => $this->getTotalOrderAmount(),
        ];
    }

    /**
     * @return array
     */
    private function getOrdersList()
    {
        $config = $this->getOrderConfig();
        $batch = (int) $config['orders_batch_size'];
        $lastTime = (int) $config['last_order_id'];

        $orderData = $this->getOrderData($batch, $lastTime);
        $orderData = $this->hydrateData($orderData);

        $lastOrder = end($orderData);

        if ($lastOrder) {
            $this->updateLastOrderId($lastOrder['orderId']);
        }

        return $orderData;
    }

    /**
     * @return int
     */
    private function getTotalOrders()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(orders.id)')
            ->from('s_order', 'orders')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return float[]
     */
    private function getTotalOrderAmount()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $prices = $queryBuilder->select('SUM(orders.invoice_amount) as totalGross, SUM(orders.invoice_amount_net) as totalNet')
            ->from('s_order', 'orders')
            ->execute()
            ->fetch();

        return array_map('floatval', $prices);
    }

    /**
     * @param int $batch
     * @param int $lastOrderId
     *
     * @return array
     */
    private function getOrderData($batch, $lastOrderId)
    {
        $ordersBasicData = $this->getOrdersBasicData($batch, $lastOrderId);

        $orderIds = array_keys($ordersBasicData);
        $dispatchIds = array_keys(array_flip(array_column($ordersBasicData, 'dispatchID')));
        $paymentIds = array_keys(array_flip(array_column($ordersBasicData, 'paymentID')));

        $orderDetails = $this->getOrderDetails($orderIds);
        $dispatchData = $this->getDispatchData($dispatchIds);
        $paymentData = $this->getPaymentData($paymentIds);

        foreach ($orderDetails as $detailsId => $orderDetail) {
            $ordersBasicData[$orderDetail['orderID']]['details'][] = $orderDetail;
        }

        foreach ($ordersBasicData as $orderId => &$basicOrder) {
            $basicOrder['dispatch'] = $dispatchData[$basicOrder['dispatchID']];
            $basicOrder['payment'] = $paymentData[$basicOrder['paymentID']];
        }

        return $ordersBasicData;
    }

    /**
     * @param int $batch
     * @param int $lastId
     *
     * @return array
     */
    private function getOrdersBasicData($batch, $lastId)
    {
        $ordersQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $ordersQueryBuilder->select('orders.*')
            ->from('s_order', 'orders')
            ->where('orders.id > :lastId')
            ->orderBy('orders.id', 'ASC')
            ->setMaxResults($batch)
            ->setParameter(':lastId', $lastId)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /**
     * @param array $orderData
     *
     * @return array
     */
    private function hydrateData(array $orderData)
    {
        $hydratedOrders = [];

        $currentHydratedOrder = [];
        foreach ($orderData as $orderId => $order) {
            $currentHydratedOrder['orderId'] = $orderId;
            $currentHydratedOrder['datetime'] = $order['ordertime'];

            $currentHydratedOrder['analytics'] = [
                'device' => $order['deviceType'],
                'referer' => $order['referer'],
            ];

            $currentHydratedOrder['shipment'] = [
                'name' => $order['dispatch']['name'],
                'cost' => [
                    'minPrice' => $order['dispatch']['minPrice'],
                    'maxPrice' => $order['dispatch']['maxPrice'],
                ],
            ];

            $currentHydratedOrder['payment'] = [
                'name' => $order['payment']['name'],
                'cost' => [
                    'percentCosts' => $order['payment']['percentCosts'],
                    'absoluteCosts' => $order['payment']['absoluteCosts'],
                    'absoluteCostsPerCountry' => $order['payment']['absoluteCostsPerCountry'],
                ],
            ];

            $currentHydratedOrder['items'] = $order['details'];

            $hydratedOrders[] = $currentHydratedOrder;
        }

        return $hydratedOrders;
    }

    /**
     * @param array $orderIds
     *
     * @return array
     */
    private function getOrderDetails(array $orderIds)
    {
        $orderDetailsQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $orderDetailsQueryBuilder->select([
                'details.id',
                'details.orderID',
                'MD5(IFNULL(details.ean, details.articleordernumber)) as ean',
                'details.price as unitPrice',
                'details.price * details.quantity as totalPrice',
                'details.quantity as amount',
                'details.pack_unit as packUnit',
                'details.unit as purchaseUnit',
            ])
            ->from('s_order_details', 'details')
            ->where('details.orderID IN (:orderIds)')
            ->setParameter(':orderIds', $orderIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /**
     * @param array $dispatchIds
     *
     * @return array
     */
    private function getDispatchData(array $dispatchIds)
    {
        $dispatchQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $dispatchQueryBuilder->select('dispatch.id, dispatch.name, MIN(costs.value) as minPrice, MAX(costs.value) as maxPrice')
            ->from('s_premium_dispatch', 'dispatch')
            ->innerJoin('dispatch', 's_premium_shippingcosts', 'costs', 'dispatch.id = costs.dispatchID')
            ->where('dispatch.id IN (:dispatchIds)')
            ->setParameter(':dispatchIds', $dispatchIds, Connection::PARAM_INT_ARRAY)
            ->groupBy('dispatch.id')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /**
     * @param array $paymentIds
     *
     * @return array
     */
    private function getPaymentData(array $paymentIds)
    {
        $paymentQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $paymentQueryBuilder->select([
                'payment.id',
                'payment.name',
                'payment.debit_percent as percentCosts',
                'payment.surcharge as absoluteCosts',
                'payment.surchargeString as absoluteCostsPerCountry',
            ])
            ->from('s_core_paymentmeans', 'payment')
            ->where('payment.id IN (:paymentIds)')
            ->setParameter(':paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    private function getOrderConfig()
    {
        $configsQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $configsQueryBuilder->select('configs.*')
            ->from('s_benchmark_config', 'configs')
            ->execute()
            ->fetch();
    }

    /**
     * @param int $lastOrderId
     */
    private function updateLastOrderId($lastOrderId)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $queryBuilder->update('s_benchmark_config')
            ->set('last_order_id', ':lastOrderId')
            ->setParameter(':lastOrderId', $lastOrderId)
            ->execute();
    }
}
