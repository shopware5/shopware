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
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class OrdersProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var int
     */
    private $shopId;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        $this->shopId = $shopContext->getShop()->getId();

        return [
            'list' => $this->getOrdersList(),
        ];
    }

    /**
     * @return array
     */
    private function getOrdersList()
    {
        $config = $this->getOrderConfig();
        $batch = (int) $config['batch_size'];
        $lastOrderId = (int) $config['last_order_id'];

        $orderData = $this->getOrderData($batch, $lastOrderId);
        $orderData = $this->hydrateData($orderData);
        $lastOrder = end($orderData);

        if ($lastOrder) {
            $this->updateLastOrderId($lastOrder['orderId']);
        }

        return $orderData;
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
        $dispatchIds = $this->getUniqueColumnValues($ordersBasicData, 'dispatchID');
        $paymentIds = $this->getUniqueColumnValues($ordersBasicData, 'paymentID');
        $customerIds = $this->getUniqueColumnValues($ordersBasicData, 'userID');

        $orderDetails = $this->getOrderDetails($orderIds);
        $dispatchData = $this->getDispatchData($dispatchIds);
        $paymentData = $this->getPaymentData($paymentIds);
        $customerData = $this->getCustomerData($customerIds);
        $billingCountries = $this->getBillingCountry($orderIds);
        $shippingCountries = $this->getShippingCountry($orderIds);

        foreach ($orderDetails as $detailsId => $orderDetail) {
            $orderId = $orderDetail['orderID'];
            unset($orderDetail['orderID']);
            $ordersBasicData[$orderId]['details'][] = $orderDetail;
        }

        foreach ($ordersBasicData as $orderId => &$basicOrder) {
            $basicOrder['dispatch'] = $dispatchData[$basicOrder['dispatchID']];
            $basicOrder['payment'] = $paymentData[$basicOrder['paymentID']];

            $basicOrder['customer'] = $customerData[$basicOrder['userID']];
            $basicOrder['customer']['billing']['country'] = $billingCountries[$orderId];
            $basicOrder['customer']['shipping']['country'] = $shippingCountries[$orderId];
        }

        return $ordersBasicData;
    }

    /**
     * @param int $batch
     * @param int $lastOrderId
     *
     * @return array
     */
    private function getOrdersBasicData($batch, $lastOrderId)
    {
        $ordersQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $ordersQueryBuilder->select('orders.*')
            ->from('s_order', 'orders')
            ->where('orders.id > :lastOrderId')
            ->andWhere('orders.subshopID = :shopId')
            ->orderBy('orders.id', 'ASC')
            ->setMaxResults($batch)
            ->setParameter(':lastOrderId', $lastOrderId)
            ->setParameter(':shopId', $this->shopId)
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
            $currentHydratedOrder['currency'] = $order['currency'];
            $currentHydratedOrder['shippingCosts'] = $order['invoice_shipping'];
            $currentHydratedOrder['customer'] = $order['customer'];

            $currentHydratedOrder['analytics'] = [
                'device' => $order['deviceType'],
                'referer' => $order['referer'] ? true : false,
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
            ->where('configs.shop_id = :shopId')
            ->setParameter(':shopId', $this->shopId)
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
            ->where('shop_id = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->setParameter(':lastOrderId', $lastOrderId)
            ->execute();
    }

    /**
     * @param array $customerIds
     *
     * @return array
     */
    private function getCustomerData(array $customerIds)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $customers = $queryBuilder->select([
                'customer.id',
                'customer.accountmode = 0 as registered',
                'YEAR(customer.birthday) as birthYear',
                'MONTH(customer.birthday) as birthMonth',
                'customer.salutation as gender',
                'customer.firstlogin as registerDate',
                'newsletter.id IS NOT NULL as hasNewsletter',
            ])
            ->from('s_user', 'customer')
            ->leftJoin('customer', 's_campaigns_mailaddresses', 'newsletter', 'newsletter.email = customer.email AND newsletter.customer = 1')

            ->where('customer.id IN (:customerIds)')
            ->setParameter(':customerIds', $customerIds, Connection::PARAM_INT_ARRAY)
            ->orderBy('customer.id')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        return array_map([$this, 'matchGenders'], $customers);
    }

    /**
     * @param array $orderIds
     *
     * @return array
     */
    private function getBillingCountry(array $orderIds)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('billingAddress.orderID, country.countryiso')
            ->from('s_order_billingaddress', 'billingAddress')
            ->innerJoin('billingAddress', 's_core_countries', 'country', 'country.id = billingAddress.countryID')
            ->where('billingAddress.orderID IN (:orderIds)')
            ->setParameter(':orderIds', $orderIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param array $orderIds
     *
     * @return array
     */
    private function getShippingCountry(array $orderIds)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('shippingAddress.orderID, country.countryiso')
            ->from('s_order_shippingaddress', 'shippingAddress')
            ->innerJoin('shippingAddress', 's_core_countries', 'country', 'country.id = shippingAddress.countryID')
            ->where('shippingAddress.orderID IN (:orderIds)')
            ->setParameter(':orderIds', $orderIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param array $customer
     *
     * @return array
     */
    private function matchGenders(array $customer)
    {
        if ($customer['gender'] === 'mr') {
            $customer['gender'] = 'male';

            return $customer;
        }

        if (in_array($customer['gender'], ['mrs', 'ms'])) {
            $customer['gender'] = 'female';

            return $customer;
        }

        $customer['gender'] = 'unknown';

        return $customer;
    }

    /**
     * Fetches a column of an associative array and returns the unique values.
     *
     * @param array  $dataSet
     * @param string $column
     *
     * @return array
     */
    private function getUniqueColumnValues(array $dataSet, $column)
    {
        $columnValues = array_column($dataSet, $column);

        // Values unique this way, faster than array_unique
        return array_keys(array_flip($columnValues));
    }
}
