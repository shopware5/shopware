<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Api\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query;
use Doctrine\ORM\TransactionRequiredException;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\OrmException as ShopwareOrmException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\PrivilegeException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\NumberRangeIncrementerInterface;
use Shopware\Models\Country\Country as CountryModel;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Customer as CustomerModel;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\DetailStatus;
use Shopware\Models\Order\Order as OrderModel;
use Shopware\Models\Order\Repository;
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
     * @return Repository
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
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return int
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ParameterMissingException('number');
        }

        $orderModel = $this->getRepository()->findOneBy(['number' => $number]);

        if (!$orderModel) {
            throw new NotFoundException(sprintf('Order by number %s not found', $number));
        }

        return $orderModel->getId();
    }

    /**
     * @param string $number
     *
     * @throws NonUniqueResultException
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws PrivilegeException
     *
     * @return array<string, mixed>|OrderModel
     */
    public function getOneByNumber($number)
    {
        $id = $this->getIdFromNumber($number);

        return $this->getOne($id);
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws NonUniqueResultException
     * @throws PrivilegeException
     *
     * @return array<string, mixed>|OrderModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $filters = [['property' => 'orders.id', 'expression' => '=', 'value' => $id]];
        $order = $this->getRepository()->getOrdersQueryBuilder($filters)->getQuery()
            ->getOneOrNullResult($this->getResultMode());

        if ($order === null) {
            throw new NotFoundException(sprintf('Order by id %d not found', $id));
        }

        if (\is_array($order)) {
            $order['paymentStatusId'] = $order['cleared'];
            $order['orderStatusId'] = $order['status'];
            unset($order['cleared'], $order['status']);
        }

        return $order;
    }

    /**
     * @param int                                                                                                            $offset
     * @param int                                                                                                            $limit
     * @param array<string, mixed>|array<array{property: string, value: mixed, expression?: string, operator?: string|null}> $criteria
     * @param array<array{property: string, direction?: string}>                                                             $orderBy
     *
     * @throws PrivilegeException
     *
     * @return array{data: array<array<string, mixed>|OrderModel>, total: int}
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('orders')
            ->addSelect(['attribute'])
            ->leftJoin('orders.attribute', 'attribute')
            ->addFilter($criteria)
            ->addOrderBy($orderBy)
            ->setFirstResult($offset)
                ->setMaxResults($limit)
            ->addSelect(['partial customer.{id,email}'])
            ->leftJoin('orders.customer', 'customer');
        /** @var Query<OrderModel|array<string, mixed>> $query */
        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the order data
        $orders = iterator_to_array($paginator);

        foreach ($orders as &$order) {
            if (\is_array($order)) {
                $order['paymentStatusId'] = $order['cleared'];
                $order['orderStatusId'] = $order['status'];
                unset($order['cleared'], $order['status']);
            }
        }

        return ['data' => $orders, 'total' => $totalResult];
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws PrivilegeException
     * @throws ValidationException
     * @throws ORMException
     * @throws ShopwareOrmException
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

        // Setting default values, necessary because of not-nullable table columns
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
            throw new ValidationException($violations);
        }

        $this->createAddresses($params, $order);

        // Generate an order number if none was provided. Doing it after validation since
        // the generation of the order number cannot be reverted in a simple manner.
        if ($order->getNumber() === null) {
            $orderNumberGenerator = Shopware()->Container()->get(NumberRangeIncrementerInterface::class);
            $orderNumber = $orderNumberGenerator->increment('invoice');

            $order->setNumber((string) $orderNumber);
            foreach ($order->getDetails() as $detail) {
                $detail->setNumber((string) $orderNumber);
            }
        }

        $this->getManager()->persist($order);
        $this->flush();

        return $order;
    }

    /**
     * @param string               $number
     * @param array<string, mixed> $params
     *
     * @throws CustomValidationException
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws PrivilegeException
     * @throws ShopwareOrmException
     * @throws ValidationException
     *
     * @return OrderModel
     */
    public function updateByNumber($number, $params)
    {
        $id = $this->getIdFromNumber($number);

        return $this->update($id, $params);
    }

    /**
     * @param int                  $id
     * @param array<string, mixed> $params
     *
     * @throws CustomValidationException
     * @throws NotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ParameterMissingException
     * @throws PrivilegeException
     * @throws ShopwareOrmException
     * @throws TransactionRequiredException
     * @throws ValidationException
     *
     * @return OrderModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $order = $this->getRepository()->find($id);

        if (!$order) {
            throw new NotFoundException(sprintf('Order by id %d not found', $id));
        }

        $params = $this->prepareOrderData($params, $order);

        $order->fromArray($params);

        $violations = $this->getManager()->validate($order);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->flush();

        return $order;
    }

    /**
     * Helper method to prepare the order data
     *
     * @param array<string, mixed> $params
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws ValidationException
     *
     * @return array<string, mixed>
     */
    public function prepareCreateOrderData(array $params)
    {
        $params = $this->prepareCreateOrderDetailsData($params);

        $allowedOrderFields = [
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
            'invoiceShippingTaxRate',
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

        $params = array_intersect_key($params, array_flip($allowedOrderFields));

        if (!\array_key_exists('customerId', $params)) {
            throw new ParameterMissingException('customerId');
        }

        if (!\array_key_exists('orderStatusId', $params)) {
            throw new ParameterMissingException('orderStatusId');
        }

        if (!\array_key_exists('paymentStatusId', $params)) {
            throw new ParameterMissingException('paymentStatusId');
        }

        if (!\array_key_exists('paymentId', $params)) {
            throw new ParameterMissingException('paymentId');
        }

        if (!\array_key_exists('dispatchId', $params)) {
            throw new ParameterMissingException('dispatchId');
        }

        if (!\array_key_exists('shopId', $params)) {
            throw new ParameterMissingException('shopId');
        }

        $params['customer'] = $this->getContainer()->get(ModelManager::class)->find(CustomerModel::class, $params['customerId']);
        if (empty($params['customer'])) {
            throw new NotFoundException(sprintf('Customer by id %s not found', $params['customerId']));
        }
        unset($params['customerId']);

        $params['orderStatus'] = $this->getContainer()->get(ModelManager::class)->getRepository(Status::class)->findOneBy([
            'id' => $params['orderStatusId'],
            'group' => 'state',
        ]);
        if (empty($params['orderStatus'])) {
            throw new NotFoundException(sprintf('OrderStatus by id %s not found', $params['orderStatusId']));
        }
        unset($params['orderStatusId']);

        $params['paymentStatus'] = $this->getContainer()->get(ModelManager::class)->getRepository(Status::class)->findOneBy([
            'id' => $params['paymentStatusId'],
            'group' => 'payment',
        ]);
        if (empty($params['paymentStatus'])) {
            throw new NotFoundException(sprintf('PaymentStatus by id %s not found', $params['paymentStatusId']));
        }
        unset($params['paymentStatusId']);

        $params['payment'] = $this->getContainer()->get(ModelManager::class)->find(Payment::class, $params['paymentId']);
        if (empty($params['payment'])) {
            throw new NotFoundException(sprintf('Payment by id %s not found', $params['paymentId']));
        }
        unset($params['paymentId']);

        $params['dispatch'] = $this->getContainer()->get(ModelManager::class)->find(Dispatch::class, $params['dispatchId']);
        if (empty($params['dispatch'])) {
            throw new NotFoundException(sprintf('Dispatch by id %s not found', $params['dispatchId']));
        }
        unset($params['dispatchId']);

        if (!empty($params['partnerId'])) {
            $params['partner'] = $this->getContainer()->get(ModelManager::class)->find(Partner::class, $params['partnerId']);

            if (empty($params['partner'])) {
                throw new NotFoundException(sprintf('Partner by id %s not found', $params['partnerId']));
            }

            unset($params['partnerId']);
        }

        $params['shop'] = $this->getContainer()->get(ModelManager::class)->find(Shop::class, $params['shopId']);
        if (empty($params['shop'])) {
            throw new NotFoundException(sprintf('Shop by id %s not found', $params['shopId']));
        }

        unset($params['shopId']);

        return $params;
    }

    /**
     * Helper method to prepare the order detail data
     *
     * @param array<string, mixed> $params
     *
     * @throws NotFoundException
     * @throws ValidationException
     *
     * @return array<string, mixed>
     */
    public function prepareCreateOrderDetailsData(array $params)
    {
        $allowedOrderDetailFields = [
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
            $detail = array_intersect_key($detail, array_flip($allowedOrderDetailFields));

            if (!\array_key_exists('statusId', $detail)) {
                throw new NotFoundException('details.statusId');
            }

            if (!\array_key_exists('taxId', $detail)) {
                throw new NotFoundException('details.taxId');
            }

            // If no order number was specified for the details we use the one from the order if there is one
            if ((!\array_key_exists('number', $detail) || $detail['number'] !== $params['number'])
                && !empty($params['number'])) {
                $detail['number'] = $params['number'];
            }

            $detailModel = new Detail();
            $detailModel->fromArray($detail);

            $status = $this->getContainer()->get(ModelManager::class)->find(DetailStatus::class, $detail['statusId']);
            if (!$status) {
                throw new NotFoundException(sprintf('DetailStatus by id %s not found', $detail['statusId']));
            }
            $detailModel->setStatus($status);
            unset($detail['statusId']);

            $tax = $this->getContainer()->get(ModelManager::class)->find(Tax::class, $detail['taxId']);
            if (!$tax) {
                throw new NotFoundException(sprintf('Tax by id %s not found', $detail['taxId']));
            }
            $detailModel->setTax($tax);
            unset($detail['taxId']);

            // Set shipped flag
            if (\array_key_exists('shipped', $detail)) {
                $detailModel->setShipped($detail['shipped']);
            }

            $violations = $this->getManager()->validate($detailModel);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $detail = $detailModel;
        }
        unset($detail);

        $params['details'] = $details;

        return $params;
    }

    /**
     * Helper method to prepare the order data
     *
     * @param array<string, mixed> $params
     *
     * @throws CustomValidationException
     * @throws NotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return array<string, mixed>
     */
    public function prepareOrderData(array $params, OrderModel $order)
    {
        $params = $this->prepareOrderDetailsData($params, $order);

        $allowedOrderFields = [
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

        $params = array_intersect_key($params, array_flip($allowedOrderFields));

        if (isset($params['orderStatusId'])) {
            $params['orderStatus'] = Shopware()->Models()->getRepository(Status::class)->findOneBy(
                [
                    'id' => $params['orderStatusId'],
                    'group' => 'state',
                ]
            );

            if (empty($params['orderStatus'])) {
                throw new NotFoundException(sprintf('OrderStatus by id %s not found', $params['orderStatusId']));
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
                throw new NotFoundException(sprintf('PaymentStatus by id %s not found', $params['paymentStatusId']));
            }

            return $params;
        }

        return $params;
    }

    /**
     * Helper method to prepare the order detail data
     *
     * @param array<string, mixed> $params
     *
     * @throws CustomValidationException
     * @throws NotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return array<string, mixed>
     */
    public function prepareOrderDetailsData(array $params, OrderModel $order)
    {
        $allowedOrderDetailFields = [
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
            $detail = array_intersect_key($detail, array_flip($allowedOrderDetailFields));
        }
        unset($detail);

        $detailModels = $this->checkDataReplacement(new ArrayCollection($order->getDetails()->toArray()), $params, 'details', true);

        foreach ($details as $detail) {
            $detailModel = $this->getOneToManySubElement($order->getDetails(), $detail, Detail::class);

            if (empty($detailModel->getId())) {
                // skip new entries
                continue;
            }

            if (isset($detail['status'])) {
                $status = Shopware()->Models()->find(DetailStatus::class, $detail['status']);

                if (!$status) {
                    throw new NotFoundException(sprintf('DetailStatus by id %s not found', $detail['status']));
                }

                $detailModel->setStatus($status);
            }

            // Set shipped flag
            if (isset($detail['shipped'])) {
                $detailModel->setShipped($detail['shipped']);
            }

            $detailModels->add($detailModel);
        }

        $params['details'] = $detailModels->toArray();

        return $params;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws NotFoundException
     * @throws ValidationException
     * @throws ParameterMissingException
     */
    private function createAddresses(array $params, OrderModel $order): void
    {
        if (!\array_key_exists('billing', $params)) {
            throw new ParameterMissingException('billing');
        }

        if (!\array_key_exists('shipping', $params)) {
            throw new ParameterMissingException('shipping');
        }

        $billing = $params['billing'];
        $state = null;

        if (!\array_key_exists('countryId', $billing)) {
            throw new ParameterMissingException('billing.countryId');
        }

        if (isset($billing['stateId'])) {
            $state = $this->getContainer()->get(ModelManager::class)->find(State::class, (int) $billing['stateId']);
            if (!$state instanceof State) {
                throw new NotFoundException(sprintf('Billing State by id %s not found', $billing['stateId']));
            }
        } else {
            $billing['stateId'] = 0;
        }

        $country = $this->getContainer()->get(ModelManager::class)->find(CountryModel::class, $billing['countryId']);
        if (!$country instanceof CountryModel) {
            throw new NotFoundException(sprintf('Billing Country by id %s not found', $billing['countryId']));
        }

        if (!$order->getCustomer() instanceof CustomerModel) {
            throw new NotFoundException(sprintf('Order with ID "%s" has no customer', $order->getId()));
        }

        $billingAddress = new Billing();
        $billingAddress->fromArray($billing);
        $billingAddress->setCustomer($order->getCustomer());
        $billingAddress->setCountry($country);
        $billingAddress->setState($state);

        $violations = $this->getManager()->validate($billingAddress);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $shipping = $params['shipping'];
        $country = null;
        $state = null;

        if (!\array_key_exists('countryId', $shipping)) {
            throw new ParameterMissingException('shipping.countryId');
        }

        if (isset($shipping['stateId'])) {
            $state = $this->getContainer()->get(ModelManager::class)->find(State::class, (int) $shipping['stateId']);
            if (!$state instanceof State) {
                throw new NotFoundException(sprintf('Shipping State by id %s not found', $shipping['stateId']));
            }
        } else {
            $shipping['stateId'] = 0;
        }

        $country = $this->getContainer()->get(ModelManager::class)->find(CountryModel::class, $shipping['countryId']);
        if (!$country) {
            throw new NotFoundException(sprintf('Shipping Country by id %s not found', $shipping['countryId']));
        }

        $shippingAddress = new Shipping();
        $shippingAddress->fromArray($shipping);
        $shippingAddress->setCustomer($order->getCustomer());
        $shippingAddress->setCountry($country);
        $shippingAddress->setState($state);

        $violations = $this->getManager()->validate($shippingAddress);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $order->setBilling($billingAddress);
        $order->setShipping($shippingAddress);
    }
}
