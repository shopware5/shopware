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

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;
use Shopware\Models\Country\Country as CountryModel;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Customer as CustomerModel;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\DetailStatus;
use Shopware\Models\Order\Order as OrderModel;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Order\Status;
use Shopware\Models\Partner\Partner;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;

/**
 * Order API Resource
 */
class Order extends Resource
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(OrderModel::class);
    }

    /**
     * Little helper function for the ...ByNumber methods
     *
     * @param string $number
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     *
     * @return int
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var OrderModel|null $orderModel */
        $orderModel = $this->getRepository()->findOneBy(['number' => $number]);

        if (!$orderModel) {
            throw new ApiException\NotFoundException(sprintf('Order by number %s not found', $number));
        }

        return $orderModel->getId();
    }

    /**
     * @param string $number
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|OrderModel
     */
    public function getOneByNumber($number)
    {
        $id = $this->getIdFromNumber($number);

        return $this->getOne($id);
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|OrderModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $filters = [['property' => 'orders.id', 'expression' => '=', 'value' => $id]];
        $builder = $this->getRepository()->getOrdersQueryBuilder($filters);
        /** @var OrderModel|array|null $order */
        $order = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$order) {
            throw new ApiException\NotFoundException(sprintf('Order by id %d not found', $id));
        }

        if (is_array($order)) {
            $order['paymentStatusId'] = $order['cleared'];
            $order['orderStatusId'] = $order['status'];
            unset($order['cleared'], $order['status']);
        }

        return $order;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('orders')
            ->addSelect(['attribute'])
            ->leftJoin('orders.attribute', 'attribute');

        $builder->addFilter($criteria);
        $builder->addOrderBy($orderBy);
        $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        $builder->addSelect(['partial customer.{id,email}']);
        $builder->leftJoin('orders.customer', 'customer');
        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the order data
        $orders = $paginator->getIterator()->getArrayCopy();

        foreach ($orders as &$order) {
            if (is_array($order)) {
                $order['paymentStatusId'] = $order['cleared'];
                $order['orderStatusId'] = $order['status'];
                unset($order['cleared']);
                unset($order['status']);
            }
        }

        return ['data' => $orders, 'total' => $totalResult];
    }

    /**
     * @throws ApiException\ValidationException
     *
     * @return OrderModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareCreateOrderData($params);

        // Remove empty fields that are not-nullable in the s_order table, they will be set with an empty string by default
        foreach (['comment', 'customerComment', 'internalComment', 'temporaryId', 'trackingCode', 'transactionId', 'referer'] as $key) {
            if (empty($params[$key])) {
                unset($params[$key]);
            }
        }

        // Create model
        $order = new OrderModel();

        // Setting default values, necessary because of not-nullable table colums
        $order->setComment('');
        $order->setCustomerComment('');
        $order->setInternalComment('');
        $order->setTemporaryId('');
        $order->setTransactionId('');
        $order->setTrackingCode('');
        $order->setReferer('');

        $order->fromArray($params);

        $violations = $this->getManager()->validate($order);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->prepareCreateAddresses($params, $order);

        // Generate an order number if none was provided. Doing it after validation since
        // the generation of the order number cannot be reverted in a simple manner.
        if ($order->getNumber() === null) {
            $orderNumberGenerator = Shopware()->Container()->get('shopware.number_range_incrementer');
            $orderNumber = $orderNumberGenerator->increment('invoice');

            $order->setNumber($orderNumber);
            foreach ($order->getDetails() as $detail) {
                $detail->setNumber($orderNumber);
            }
        }

        $this->getManager()->persist($order);
        $this->flush();

        return $order;
    }

    /**
     * @param string $number
     * @param array  $params
     *
     * @return OrderModel
     */
    public function updateByNumber($number, $params)
    {
        $id = $this->getIdFromNumber($number);

        return $this->update($id, $params);
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     *
     * @return OrderModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var OrderModel|null $order */
        $order = $this->getRepository()->find($id);

        if (!$order) {
            throw new ApiException\NotFoundException(sprintf('Order by id %d not found', $id));
        }

        $params = $this->prepareOrderData($params);

        $order->fromArray($params);

        $violations = $this->getManager()->validate($order);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $order;
    }

    /**
     * Helper method to prepare the order data
     *
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     *
     * @return array
     */
    public function prepareCreateOrderData(array $params)
    {
        $params = $this->prepareCreateOrderDetailsData($params);

        $orderWhiteList = [
            'attribute',
            'billing',
            'clearedDate',
            'comment',
            'customerComment',
            'customer',
            'customerId',
            'currency',
            'currencyFactor',
            'deviceType',
            'details',
            'dispatchId',
            'documents',
            'internalComment',
            'invoiceAmount',
            'invoiceAmountNet',
            'invoiceShipping',
            'invoiceShippingNet',
            'languageIso',
            'net',
            'number',
            'orderStatus',
            'orderStatusId',
            'orderTime',
            'partnerId',
            'paymentId',
            'paymentStatus',
            'paymentStatusId',
            'referer',
            'remoteAddress',
            'taxFree',
            'temporaryId',
            'trackingCode',
            'transactionId',
            'shipping',
            'shopId',
        ];

        $params = array_intersect_key($params, array_flip($orderWhiteList));

        if (!array_key_exists('customerId', $params)) {
            throw new ApiException\ParameterMissingException('customerId');
        }

        if (!array_key_exists('orderStatusId', $params)) {
            throw new ApiException\ParameterMissingException('orderStatusId');
        }

        if (!array_key_exists('paymentStatusId', $params)) {
            throw new ApiException\ParameterMissingException('paymentStatusId');
        }

        if (!array_key_exists('paymentId', $params)) {
            throw new ApiException\ParameterMissingException('paymentId');
        }

        if (!array_key_exists('dispatchId', $params)) {
            throw new ApiException\ParameterMissingException('dispatchId');
        }

        if (!array_key_exists('shopId', $params)) {
            throw new ApiException\ParameterMissingException('shopId');
        }

        $params['customer'] = $this->getContainer()->get('models')->find(CustomerModel::class, $params['customerId']);
        if (empty($params['customer'])) {
            throw new ApiException\NotFoundException(sprintf('Customer by id %s not found', $params['customerId']));
        }
        unset($params['customerId']);

        $params['orderStatus'] = $this->getContainer()->get('models')->getRepository(Status::class)->findOneBy([
            'id' => $params['orderStatusId'],
            'group' => 'state',
        ]);
        if (empty($params['orderStatus'])) {
            throw new ApiException\NotFoundException(sprintf('OrderStatus by id %s not found', $params['orderStatusId']));
        }
        unset($params['orderStatusId']);

        $params['paymentStatus'] = $this->getContainer()->get('models')->getRepository(Status::class)->findOneBy([
            'id' => $params['paymentStatusId'],
            'group' => 'payment',
        ]);
        if (empty($params['paymentStatus'])) {
            throw new ApiException\NotFoundException(sprintf('PaymentStatus by id %s not found', $params['paymentStatusId']));
        }
        unset($params['paymentStatusId']);

        $params['payment'] = $this->getContainer()->get('models')->find(Payment::class, $params['paymentId']);
        if (empty($params['payment'])) {
            throw new ApiException\NotFoundException(sprintf('Payment by id %s not found', $params['paymentId']));
        }
        unset($params['paymentId']);

        $params['dispatch'] = $this->getContainer()->get('models')->find(Dispatch::class, $params['dispatchId']);
        if (empty($params['dispatch'])) {
            throw new ApiException\NotFoundException(sprintf('Dispatch by id %s not found', $params['dispatchId']));
        }
        unset($params['dispatchId']);

        if (!empty($params['partnerId'])) {
            $params['partner'] = $this->getContainer()->get('models')->find(Partner::class, $params['partnerId']);

            if (empty($params['partner'])) {
                throw new ApiException\NotFoundException(
                    sprintf('Partner by id %s not found', $params['partnerId'])
                );
            }

            unset($params['partnerId']);
        }

        $params['shop'] = $this->getContainer()->get('models')->find(Shop::class, $params['shopId']);
        if (empty($params['shop'])) {
            throw new ApiException\NotFoundException(sprintf('Shop by id %s not found', $params['shopId']));
        }

        unset($params['shopId']);

        return $params;
    }

    /**
     * Helper method to prepare the order detail data
     *
     * @throws ApiException\NotFoundException
     * @throws ApiException\ValidationException
     *
     * @return array
     */
    public function prepareCreateOrderDetailsData(array $params)
    {
        $detailWhiteList = [
            'articleId',
            'articleName',
            'articleNumber',
            'articleDetailID',
            'attribute',
            'config',
            'ean',
            'esdArticle',
            'mode',
            'number',
            'packUnit',
            'price',
            'quantity',
            'releaseDate',
            'shipped',
            'shippedGroup',
            'status',
            'statusId',
            'unit',
            'taxId',
            'taxRate',
        ];

        $details = $params['details'];

        if (empty($details)) {
            unset($params['details']);

            return $params;
        }

        foreach ($details as &$detail) {
            // Apply whiteList
            $detail = array_intersect_key($detail, array_flip($detailWhiteList));

            if (!array_key_exists('statusId', $detail)) {
                throw new ApiException\NotFoundException('details.statusId');
            }

            if (!array_key_exists('taxId', $detail)) {
                throw new ApiException\NotFoundException('details.taxId');
            }

            // If no order number was specified for the details we use the one from the order if there is one
            if ((!array_key_exists('number', $detail) || $detail['number'] !== $params['number'])
                && !empty($params['number'])) {
                $detail['number'] = $params['number'];
            }

            $detailModel = new Detail();
            $detailModel->fromArray($detail);

            /** @var DetailStatus|null $status */
            $status = $this->getContainer()->get('models')->find(DetailStatus::class, $detail['statusId']);
            if (!$status) {
                throw new ApiException\NotFoundException(sprintf('DetailStatus by id %s not found', $detail['statusId']));
            }
            $detailModel->setStatus($status);
            unset($detail['statusId']);

            $tax = $this->getContainer()->get('models')->find(Tax::class, $detail['taxId']);
            if (!$tax) {
                throw new ApiException\NotFoundException(sprintf('Tax by id %s not found', $detail['taxId']));
            }
            $detailModel->setTax($tax);
            unset($detail['taxId']);

            // Set shipped flag
            if (array_key_exists('shipped', $detail)) {
                $detailModel->setShipped($detail['shipped']);
            }

            $violations = $this->getManager()->validate($detailModel);
            if ($violations->count() > 0) {
                throw new ApiException\ValidationException($violations);
            }

            $detail = $detailModel;
        }

        $params['details'] = $details;

        return $params;
    }

    /**
     * Helper method to prepare the order data
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array
     */
    public function prepareOrderData(array $params)
    {
        $params = $this->prepareOrderDetailsData($params);

        $orderWhiteList = [
            'paymentStatusId',
            'orderStatusId',
            'trackingCode',
            'comment',
            'customerComment',
            'internalComment',
            'transactionId',
            'clearedDate',
            'attribute',
            'details',
        ];

        $params = array_intersect_key($params, array_flip($orderWhiteList));

        if (isset($params['orderStatusId'])) {
            $params['orderStatus'] = Shopware()->Models()->getRepository(Status::class)->findOneBy(
                [
                    'id' => $params['orderStatusId'],
                    'group' => 'state',
                ]
            );

            if (empty($params['orderStatus'])) {
                throw new ApiException\NotFoundException(sprintf(
                    'OrderStatus by id %s not found',
                    $params['orderStatusId']
                ));
            }
        }

        if (isset($params['paymentStatusId'])) {
            $params['paymentStatus'] = Shopware()->Models()->getRepository(Status::class)->findOneBy(
                [
                    'id' => $params['paymentStatusId'],
                    'group' => 'payment',
                ]
            );

            if (empty($params['paymentStatus'])) {
                throw new ApiException\NotFoundException(sprintf(
                    'PaymentStatus by id %s not found',
                    $params['paymentStatusId']
                ));
            }

            return $params;
        }

        return $params;
    }

    /**
     * Helper method to prepare the order detail data
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException|ApiException\CustomValidationException
     *
     * @return array
     */
    public function prepareOrderDetailsData(array $params)
    {
        $detailWhiteList = [
            'status',
            'shipped',
            'id',
        ];

        $details = $params['details'];

        if (empty($details)) {
            unset($params['details']);

            return $params;
        }

        foreach ($details as &$detail) {
            // Apply whiteList
            $detail = array_intersect_key($detail, array_flip($detailWhiteList));

            // Technically "articleID" and "articleordernumber" are not unique per orderId,
            // so we cannot use those to identify order positions.
            if (!isset($detail['id']) || empty($detail['id'])) {
                throw new ApiException\CustomValidationException('You need to specify the id of the order positions you want to modify');
            }

            // Check order detail model
            /** @var Detail|null $detailModel */
            $detailModel = Shopware()->Models()->find(Detail::class, $detail['id']);
            if (!$detailModel) {
                throw new ApiException\NotFoundException(sprintf(
                    'Detail by id %s not found',
                    $detail['id']
                ));
            }

            if (isset($detail['status'])) {
                /** @var DetailStatus|null $status */
                $status = Shopware()->Models()->find(DetailStatus::class, $detail['status']);

                if (!$status) {
                    throw new ApiException\NotFoundException(sprintf(
                        'DetailStatus by id %s not found',
                        $detail['status']
                    ));
                }

                $detailModel->setStatus($status);
            }

            // Set shipped flag
            if (isset($detail['shipped'])) {
                $detailModel->setShipped($detail['shipped']);
            }

            $detail = $detailModel;
        }

        $params['details'] = $details;

        return $params;
    }

    /**
     * @throws ApiException\NotFoundException
     * @throws ApiException\ValidationException
     * @throws ApiException\ParameterMissingException
     */
    private function prepareCreateAddresses(array $params, OrderModel $order)
    {
        if (!array_key_exists('billing', $params)) {
            throw new ApiException\ParameterMissingException('billing');
        }

        if (!array_key_exists('shipping', $params)) {
            throw new ApiException\ParameterMissingException('shipping');
        }

        $billing = $params['billing'];
        $country = null;
        $state = null;

        if (!array_key_exists('countryId', $billing)) {
            throw new ApiException\ParameterMissingException('billing.countryId');
        }

        if (isset($billing['stateId'])) {
            $state = $this->getContainer()->get('models')->find(State::class, (int) $billing['stateId']);
            if (!$state instanceof State) {
                throw new ApiException\NotFoundException(sprintf(
                    'Billing State by id %s not found',
                    $billing['stateId']
                ));
            }
        } else {
            $billing['stateId'] = 0;
        }

        $country = $this->getContainer()->get('models')->find(CountryModel::class, $billing['countryId']);
        if (!$country) {
            throw new ApiException\NotFoundException(sprintf(
                'Billing Country by id %s not found',
                $billing['countryId']
            ));
        }

        $billingAddress = new Billing();
        $billingAddress->fromArray($billing);
        $billingAddress->setCustomer($order->getCustomer());
        $billingAddress->setCountry($country);
        $billingAddress->setState($state);

        $violations = $this->getManager()->validate($billingAddress);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $shipping = $params['shipping'];
        $country = null;
        $state = null;

        if (!array_key_exists('countryId', $shipping)) {
            throw new ApiException\ParameterMissingException('shipping.countryId');
        }

        if (isset($shipping['stateId'])) {
            $state = $this->getContainer()->get('models')->find(State::class, (int) $shipping['stateId']);
            if (!$state instanceof State) {
                throw new ApiException\NotFoundException(sprintf(
                    'Shipping State by id %s not found',
                    $shipping['stateId']
                ));
            }
        } else {
            $shipping['stateId'] = 0;
        }

        $country = $this->getContainer()->get('models')->find(CountryModel::class, $shipping['countryId']);
        if (!$country) {
            throw new ApiException\NotFoundException(sprintf(
                'Shipping Country by id %s not found',
                $shipping['countryId']
            ));
        }

        $shippingAddress = new Shipping();
        $shippingAddress->fromArray($shipping);
        $shippingAddress->setCustomer($order->getCustomer());
        $shippingAddress->setCountry($country);
        $shippingAddress->setState($state);

        $violations = $this->getManager()->validate($shippingAddress);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $order->setBilling($billingAddress);
        $order->setShipping($shippingAddress);
    }
}
