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

namespace   Shopware\Models\Order;

use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the order model (Shopware\Models\Order\Order).
 *
 * The order model repository is responsible to load all order data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Returns a query-object for all known payment status
     *
     * @param null $filter
     * @param null $order
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
     */
    public function getPaymentStatusQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getPaymentStatusQueryBuilder($filter, $order);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getPaymentStatusQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPaymentStatusQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'status.id as id',
            'status.name as name',
            'status.description as description'
        ));
        $builder->from('Shopware\Models\Order\Status', 'status')
                ->where('status.group = ?1')
                ->setParameter(1, 'payment');

        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns a query-object for all known order stati
     *
     * @param null $filter
     * @param null $order
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
     */
    public function getOrderStatusQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getOrderStatusQueryBuilder($filter, $order);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getOrderStatusQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderStatusQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'status.id as id',
            'status.name as name',
            'status.description as description'
        ));
        $builder->from('Shopware\Models\Order\Status', 'status');
        $builder->where('status.group = ?1')
                ->setParameter(1, 'state');


        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param null $filters
     * @param null $orderBy
     * @param null $offset
     * @param null $limit
     * @internal param $ids
     * @return \Doctrine\ORM\Query
     */
    public function getOrdersQuery($filters = null, $orderBy = null, $offset = null, $limit = null)
    {
        $builder = $this->getOrdersQueryBuilder($filters, $orderBy);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getOrdersQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $filters
     * @param      $orderBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrdersQueryBuilder($filters = null, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'orders',
            'details',
            'documents',
            'payment',
            'customer',
            'paymentInstances',
            'shipping',
            'billing',
            'billingCountry',
            'shippingCountry',
            'shop',
            'dispatch',
            'paymentStatus',
            'orderStatus',
            'documentType',
            'billingAttribute',
            'attribute',
            'detailAttribute',
            'documentAttribute',
            'shippingAttribute',
            'paymentAttribute',
            'dispatchAttribute',
            'subShop',
            'locale',
        ));

        $builder->from('Shopware\Models\Order\Order', 'orders');
        $builder->leftJoin('orders.details', 'details')
                ->leftJoin('orders.documents', 'documents')
                ->leftJoin('documents.type', 'documentType')
                ->leftJoin('orders.payment', 'payment')
                ->leftJoin('orders.paymentStatus', 'paymentStatus')
                ->leftJoin('orders.orderStatus', 'orderStatus')
                ->leftJoin('orders.customer', 'customer')
                ->leftJoin('orders.paymentInstances', 'paymentInstances')
                ->leftJoin('orders.billing', 'billing')
                ->leftJoin('billing.country', 'billingCountry')
                ->leftJoin('orders.shipping', 'shipping')
                ->leftJoin('orders.shop', 'shop')
                ->leftJoin('orders.dispatch', 'dispatch')
                ->leftJoin('payment.attribute', 'paymentAttribute')
                ->leftJoin('dispatch.attribute', 'dispatchAttribute')
                ->leftJoin('billing.attribute', 'billingAttribute')
                ->leftJoin('shipping.attribute', 'shippingAttribute')
                ->leftJoin('details.attribute', 'detailAttribute')
                ->leftJoin('documents.attribute', 'documentAttribute')
                ->leftJoin('orders.attribute', 'attribute')
                ->leftJoin('orders.languageSubShop', 'subShop')
                ->leftJoin('subShop.locale', 'locale')
                ->leftJoin('shipping.country', 'shippingCountry');

        if (!empty($filters)) {
            $builder = $this->filterListQuery($builder, $filters);
        }
        $builder->andWhere($builder->expr()->notIn('orders.status', array('-1')));
        $builder->andWhere('orders.number IS NOT NULL');

        if (!empty($orderBy)) {
            //add order by path
            $builder->addOrderBy($orderBy);
        }
        return $builder;
    }


    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param null $filters
     * @param null $orderBy
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
     */
    public function getBackendOrdersQuery($filters = null, $orderBy = null, $offset = null, $limit = null)
    {
        $builder = $this->getBackendOrdersQueryBuilder($filters, $orderBy);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getOrdersQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $filters
     * @param      $orderBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBackendOrdersQueryBuilder($filters = null, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
                'orders',
                'customer',
                'payment',
                'billing',
                'billingCountry',
                'billingState',
                'shop',
                'dispatch',
                'paymentStatus',
                'orderStatus'
            ));

        $builder->from('Shopware\Models\Order\Order', 'orders');
        $builder->leftJoin('orders.payment', 'payment')
                ->leftJoin('orders.paymentStatus', 'paymentStatus')
                ->leftJoin('orders.orderStatus', 'orderStatus')
                ->leftJoin('orders.billing', 'billing')
                ->leftJoin('orders.customer', 'customer')
                ->leftJoin('billing.country', 'billingCountry')
                ->leftJoin('billing.state', 'billingState')
                ->leftJoin('orders.shop', 'shop')
                ->leftJoin('orders.dispatch', 'dispatch');

        if (!empty($filters)) {
            $builder = $this->filterListQuery($builder, $filters);
        }
        $builder->andWhere($builder->expr()->notIn('orders.status', array('-1')));
        $builder->andWhere('orders.number IS NOT NULL');

        if (!empty($orderBy)) {
            //order by path of company, lastName and firstName instead of customerId 
            if(isset($orderBy[0]['property']) && $orderBy[0]['property'] === 'orders.customerId'){
                $orderBy[0] = [
                    'property' => 'billing.company',
                    'direction' => $orderBy[0]['direction']
                ];
                array_splice($orderBy, 1, 0, [
                    [
                        'property' => 'billing.lastName',
                        'direction' => $orderBy[0]['direction']
                    ],
                    [
                        'property' => 'billing.firstName',
                        'direction' => $orderBy[0]['direction']
                    ]
                ]);
            }
            //add order by path
            $builder->addOrderBy($orderBy);
        }
        return $builder;
    }

    /**
     * This method returns the additional order data for the backend list
     *
     * @param $orderNumber
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBackendAdditionalOrderDataQuery($orderNumber)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
                'orders',
                'details',
                'documents',
                'documentType',
                'customer',
                'paymentInstances',
                'shipping',
                'shippingCountry',
                'shippingState',
                'subShop',
                'locale'
            ));
        $builder->from('Shopware\Models\Order\Order', 'orders');
        $builder->leftJoin('orders.documents', 'documents')
                ->leftJoin('documents.type', 'documentType')
                ->leftJoin('orders.details', 'details')
                ->leftJoin('orders.customer', 'customer')
                ->leftJoin('orders.paymentInstances', 'paymentInstances')
                ->leftJoin('orders.shipping', 'shipping')
                ->leftJoin('shipping.state', 'shippingState')
                ->leftJoin('shipping.country', 'shippingCountry')
                ->leftJoin('orders.languageSubShop', 'subShop')
                ->leftJoin('subShop.locale', 'locale');

        $builder->where('orders.number = :orderNumber');
        $builder->setParameter('orderNumber', $orderNumber);
        return $builder->getQuery();
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @return \Doctrine\ORM\Query
     */
    public function getDetailStatusQuery()
    {
        $builder = $this->getDetailStatusQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailStatusQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailStatusQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('detailStatus')
                ->from('Shopware\Models\Order\DetailStatus', 'detailStatus')
                ->orderBy('detailStatus.position', 'ASC');
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param      $orderId
     * @param null $orderBy
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
     */
    public function getOrderStatusHistoryListQuery($orderId, $orderBy = null, $offset=null, $limit=null)
    {
        $builder = $this->getOrderStatusHistoryListQueryBuilder($orderId, $orderBy);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getStatusHistoryListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param      $orderId
     * @param null $orderBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderStatusHistoryListQueryBuilder($orderId, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'history.changeDate',
            'user.name as userName',
            'history.previousOrderStatusId as prevOrderStatusId',
            'history.orderStatusId as currentOrderStatusId',
            'history.previousPaymentStatusId as prevPaymentStatusId',
            'history.paymentStatusId as currentPaymentStatusId'
        ));
        $builder->from('Shopware\Models\Order\History', 'history')
                ->leftJoin('history.user',  'user')
                ->where($builder->expr()->eq('history.orderId', '?1'))
                ->setParameter(1, $orderId);

        $builder->addOrderBy($orderBy);
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @return \Doctrine\ORM\Query
     */
    public function getDocumentTypesQuery()
    {
        $builder = $this->getDocumentTypesQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDocumentTypesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDocumentTypesQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('types'))
                ->from('Shopware\Models\Order\Document\Type', 'types');
        return $builder;
    }

    /**
     * Filters the displayed fields by the passed filter value.
     *
     * @param \Doctrine\ORM\QueryBuilder $builder
     * @param array|null $filters
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function filterListQuery(\Doctrine\ORM\QueryBuilder $builder, $filters=null)
    {
        $expr = Shopware()->Models()->getExpressionBuilder();

        if (!empty($filters)) {
            foreach ($filters as $filter) {
                if (empty($filter['property']) || $filter['value'] === null || $filter['value'] === '') {
                    continue;
                }
                switch ($filter['property']) {
                    case "free":
                        $builder->andWhere(
                            $expr->orX(
                                $expr->like('orders.number', '?1'),
                                $expr->like('orders.invoiceAmount', '?1'),
                                $expr->like('orders.transactionId', '?1'),
                                $expr->like('billing.company', '?3'),
                                $expr->like('customer.email', '?3'),
                                $expr->like('billing.lastName', '?3'),
                                $expr->like('billing.firstName', '?3'),
                                $expr->like('orders.comment', '?3'),
                                $expr->like('orders.customerComment', '?3'),
                                $expr->like('orders.internalComment', '?3')
                            )
                        );
                        $builder->setParameter(1,       $filter['value'] . '%');
                        $builder->setParameter(3, '%' . $filter['value'] . '%');
                        break;
                    case "from":
                        $tmp = new \DateTime($filter['value']);
                        $builder->andWhere('orders.orderTime >= :orderTimeFrom');
                        $builder->setParameter('orderTimeFrom', $tmp->format('Ymd'));
                        break;
                    case "to":
                        $tmp = new \Zend_Date($filter['value']);
                        $tmp->setHour('23');
                        $tmp->setMinute('59');
                        $tmp->setSecond('59');
                        $builder->andWhere('orders.orderTime <= :orderTimeTo');
                        $builder->setParameter('orderTimeTo', $tmp->get('yyyy-MM-dd HH:mm:ss'));
                        break;
                    case 'details.articleNumber':
                        $builder->leftJoin('orders.details', 'details');
                        $builder->andWhere('details.articleNumber LIKE :articleNumber');
                        $builder->setParameter('articleNumber',  $filter['value']);
                        break;
                    default:
                        $builder->addFilter(array($filter));
                }
            }
        }

        return $builder;
    }

    /**
     * Selects all individual vouchers which have a valid to date greater than today
     * or an empty valid to date.
     * The query contains the following fields:
     *  - voucher.id
     *  - voucher.description
     *  - voucher.voucherCode
     *  - voucher.value
     *  - voucher.minimumCharge
     *
     * @return \Doctrine\ORM\Query
     */
    public function getVoucherQuery()
    {
        $today = new \DateTime();
        $today = "'" . $today->format('Y-m-d') . "'";

        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('voucher.id', 'voucher.description', 'voucher.voucherCode', 'voucher.value', 'voucher.minimumCharge'))
                       ->from('Shopware\Models\Voucher\Voucher', 'voucher')
                       ->join('voucher.codes', 'codes')
                       ->where(
                           $builder->expr()->orX(
                               $builder->expr()->gte('voucher.validTo', $today),
                               $builder->expr()->isNull('voucher.validTo')
                           )
                       )
                       ->andWhere($builder->expr()->isNull('codes.customerId'))
                       ->andWhere($builder->expr()->eq('codes.cashed', 0))
                       ->andWhere($builder->expr()->eq('voucher.modus', 1))
                       ->getQuery();
    }
}
