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

namespace Shopware\Models\Order;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Document\Document;

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
     * Limits the result of the search term queries
     */
    const SEARCH_TERM_LIMIT = 400;

    /**
     * Returns a query-object for all known payment status
     *
     * @param array|null                              $filter
     * @param string|\Doctrine\ORM\Query\Expr\OrderBy $order
     * @param int|null                                $offset
     * @param int|null                                $limit
     *
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
     * @param array|null                              $filter
     * @param string|\Doctrine\ORM\Query\Expr\OrderBy $order
     *
     * @return QueryBuilder
     */
    public function getPaymentStatusQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'status.id as id',
            'status.name as name',
        ]);
        $builder->from(\Shopware\Models\Order\Status::class, 'status')
            ->where('status.group = ?1')
            ->setParameter(1, 'payment');

        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        } else {
            $builder->orderBy('status.position', 'ASC');
        }

        return $builder;
    }

    /**
     * Returns a query-object for all known order statuses
     *
     * @param array|null  $filter
     * @param string|null $order
     * @param int|null    $offset
     * @param int|null    $limit
     *
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
     * @param array|null  $filter
     * @param string|null $order
     *
     * @return QueryBuilder
     */
    public function getOrderStatusQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'status.id as id',
            'status.name as name',
        ]);
        $builder->from(\Shopware\Models\Order\Status::class, 'status');
        $builder->where('status.group = ?1')
            ->setParameter(1, 'state');

        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        } else {
            $builder->orderBy('status.position', 'ASC');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param array[]|null      $filters
     * @param string|array|null $orderBy
     * @param int|null          $offset
     * @param int|null          $limit
     *
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
     *
     * @param array[]|null      $filters
     * @param string|array|null $orderBy
     *
     * @return QueryBuilder
     */
    public function getOrdersQueryBuilder($filters = null, $orderBy = null)
    {
        /** @var \Shopware\Components\Model\QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
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
            'billingState',
            'shippingState',
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
        ]);

        $builder->from(\Shopware\Models\Order\Order::class, 'orders');
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
            ->leftJoin('billing.state', 'billingState')
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
            ->leftJoin('shipping.country', 'shippingCountry')
            ->leftJoin('shipping.state', 'shippingState');

        if (!empty($filters)) {
            $builder = $this->filterListQuery($builder, $filters);
        }
        $builder->andWhere('orders.status != -1');
        $builder->andWhere('orders.number IS NOT NULL');

        if (!empty($orderBy)) {
            // Add order by path
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which can be extended for customization
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailStatusQuery()
    {
        return $this->getDetailStatusQueryBuilder()->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailStatusQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getDetailStatusQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('detailStatus')
            ->from(\Shopware\Models\Order\DetailStatus::class, 'detailStatus')
            ->orderBy('detailStatus.position', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int         $orderId
     * @param string|null $orderBy
     * @param int|null    $offset
     * @param int|null    $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getOrderStatusHistoryListQuery($orderId, $orderBy = null, $offset = null, $limit = null)
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
     *
     * @param int    $orderId
     * @param string $orderBy
     *
     * @return QueryBuilder
     */
    public function getOrderStatusHistoryListQueryBuilder($orderId, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'history.changeDate',
            'user.name as userName',
            'history.previousOrderStatusId as prevOrderStatusId',
            'history.orderStatusId as currentOrderStatusId',
            'history.previousPaymentStatusId as prevPaymentStatusId',
            'history.paymentStatusId as currentPaymentStatusId',
        ]);
        $builder->from(\Shopware\Models\Order\History::class, 'history')
            ->leftJoin('history.user', 'user')
            ->where('history.orderId = ?1')
            ->setParameter(1, $orderId);

        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which can be extended for customization
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDocumentTypesQuery()
    {
        return $this->getDocumentTypesQueryBuilder()->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDocumentTypesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getDocumentTypesQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['types'])
            ->from(Document::class, 'types');

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

        return $builder->select(['voucher.id', 'voucher.description', 'voucher.voucherCode', 'voucher.value', 'voucher.minimumCharge'])
                       ->from(\Shopware\Models\Voucher\Voucher::class, 'voucher')
                       ->join('voucher.codes', 'codes')
                       ->where(
                           '(voucher.validTo>= :today OR voucher.validTo IS NULL)')
                           ->setParameter('today', $today
                       )
                       ->andWhere('codes.customerId IS NULL')
                       ->andWhere('codes.cashed= 0')
                       ->andWhere('voucher.modus= 1')
                       ->getQuery();
    }

    /**
     * @param int[] $ids
     *
     * @return array[]
     */
    public function getList($ids)
    {
        $query = $this->getListQueryBuilder();
        $query->where('orders.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @return QueryBuilder
     */
    public function getListQueryBuilder()
    {
        $query = $this->getEntityManager()->createQueryBuilder();

        $query->select([
            'orders',
            'shipping',
            'shippingCountry',
            'shippingState',
            'subShop',
            'locale',
            'customer',
            'payment',
            'billing',
            'billingCountry',
            'billingState',
            'shop',
            'dispatch',
            'paymentStatus',
            'orderStatus',
        ]);

        $query->from(\Shopware\Models\Order\Order::class, 'orders', 'orders.id');
        $query->leftJoin('orders.customer', 'customer');
        $query->leftJoin('orders.shipping', 'shipping');
        $query->leftJoin('shipping.state', 'shippingState');
        $query->leftJoin('shipping.country', 'shippingCountry');
        $query->leftJoin('orders.languageSubShop', 'subShop');
        $query->leftJoin('subShop.locale', 'locale');
        $query->leftJoin('orders.payment', 'payment');
        $query->leftJoin('orders.paymentStatus', 'paymentStatus');
        $query->leftJoin('orders.orderStatus', 'orderStatus');
        $query->leftJoin('orders.billing', 'billing');
        $query->leftJoin('billing.country', 'billingCountry');
        $query->leftJoin('billing.state', 'billingState');
        $query->leftJoin('orders.shop', 'shop');
        $query->leftJoin('orders.dispatch', 'dispatch');

        return $query;
    }

    /**
     * @param int[] $orderIds
     *
     * @return QueryBuilder
     */
    public function getDocumentsQueryBuilder(array $orderIds)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select(['document', 'documentType']);
        $query->from(\Shopware\Models\Order\Document\Document::class, 'document');
        $query->leftJoin('document.type', 'documentType');
        $query->where('IDENTITY(document.order) IN (:ids)');
        $query->setParameter(':ids', $orderIds, Connection::PARAM_INT_ARRAY);

        return $query;
    }

    /**
     * @param int[] $orderIds
     *
     * @return array[]
     */
    public function getDocuments(array $orderIds)
    {
        return $this->getDocumentsQueryBuilder($orderIds)->getQuery()->getArrayResult();
    }

    /**
     * @param int[] $orderIds
     *
     * @return QueryBuilder
     */
    public function getDetailsQueryBuilder(array $orderIds)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select(['details', 'attribute']);
        $query->from(\Shopware\Models\Order\Detail::class, 'details');
        $query->leftJoin('details.attribute', 'attribute');
        $query->where('IDENTITY(details.order) IN (:ids)');
        $query->setParameter(':ids', $orderIds, Connection::PARAM_INT_ARRAY);

        return $query;
    }

    /**
     * @param int[] $orderIds
     *
     * @return array[]
     */
    public function getDetails(array $orderIds)
    {
        return $this->getDetailsQueryBuilder($orderIds)->getQuery()->getArrayResult();
    }

    /**
     * @param int[] $orderIds
     *
     * @return QueryBuilder
     */
    public function getPaymentsQueryBuilder(array $orderIds)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select(['payments']);
        $query->from(\Shopware\Models\Payment\PaymentInstance::class, 'payments');
        $query->where('IDENTITY(payments.order) IN (:ids)');
        $query->setParameter(':ids', $orderIds, Connection::PARAM_INT_ARRAY);

        return $query;
    }

    /**
     * @param int[] $orderIds
     *
     * @return array
     */
    public function getPayments(array $orderIds)
    {
        return $this->getPaymentsQueryBuilder($orderIds)->getQuery()->getArrayResult();
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @param array[]  $filters
     * @param array[]  $sortings
     *
     * @return array[]
     */
    public function search($offset = null, $limit = null, $filters = [], $sortings = [])
    {
        /** @var ModelManager $em */
        $em = $this->getEntityManager();
        /** @var \Shopware\Components\Model\QueryBuilder $builder */
        $builder = $em->createQueryBuilder();

        $builder->select(['orders.id']);
        $builder->from(\Shopware\Models\Order\Order::class, 'orders');
        $builder->leftJoin('orders.attribute', 'attribute');
        $builder->andWhere('orders.number IS NOT NULL');
        $builder->andWhere('orders.status != :cancelStatus');
        $builder->setParameter(':cancelStatus', -1);

        $builder = $this->filterListQuery($builder, $filters);
        $builder = $this->sortListQuery($builder, $sortings);

        if ($offset !== null) {
            $builder->setFirstResult($offset);
        }
        if ($limit !== null) {
            $builder->setMaxResults($limit);
        }
        $query = $builder->getQuery();
        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
        $paginator = $em->createPaginator($query);

        return [
            'total' => $paginator->count(),
            'orders' => $paginator->getIterator()->getArrayCopy(),
        ];
    }

    /**
     * @param \Shopware\Components\Model\QueryBuilder $builder
     * @param array[]                                 $sortings
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function sortListQuery($builder, $sortings)
    {
        if (empty($sortings)) {
            return $builder;
        }

        foreach ($sortings as $order) {
            $alias = explode('.', $order['property']);
            $this->addAliasJoin($builder, $alias[0]);
        }
        $builder->addOrderBy($sortings);

        return $builder;
    }

    /**
     * Filters the displayed fields by the passed filter value.
     *
     * @param \Shopware\Components\Model\QueryBuilder $builder
     * @param array[]|null                            $filters
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function filterListQuery(QueryBuilder $builder, $filters = null)
    {
        if (empty($filters)) {
            return $builder;
        }

        foreach ($filters as $filter) {
            if (empty($filter['property']) || $filter['value'] === null || $filter['value'] === '') {
                continue;
            }

            $alias = explode('.', $filter['property']);
            $this->addAliasJoin($builder, $alias[0]);

            switch ($filter['property']) {
                case 'free':
                    $orderIds = $this->searchOrderIds($filter['value']);
                    $builder->andWhere('orders.id IN (?4)');
                    $builder->setParameter(4, $orderIds, Connection::PARAM_INT_ARRAY);
                    break;

                case 'from':
                    $tmp = new \DateTime($filter['value']);
                    $builder->andWhere('orders.orderTime >= :orderTimeFrom');
                    $builder->setParameter('orderTimeFrom', $tmp->format('Ymd'));
                    break;

                case 'to':
                    $tmp = new \DateTime($filter['value']);
                    $tmp->add(new \DateInterval('P1D'));
                    $builder->andWhere('orders.orderTime <= :orderTimeTo');
                    $builder->setParameter('orderTimeTo', $tmp->format('Ymd'));
                    break;

                case 'details.articleNumber':
                    $builder->andWhere('details.articleNumber LIKE :articleNumber');
                    $builder->setParameter('articleNumber', $filter['value']);
                    break;

                default:
                    $builder->addFilter([$filter]);
            }
        }

        return $builder;
    }

    /**
     * @param string $alias
     */
    protected function addAliasJoin(QueryBuilder $builder, $alias)
    {
        if (in_array($alias, $builder->getAllAliases())) {
            return;
        }

        switch ($alias) {
            case 'shipping':
                $builder->leftJoin('orders.shipping', 'shipping');
                break;

            case 'billing':
                $builder->leftJoin('orders.billing', 'billing');
                break;

            case 'details':
                $builder->leftJoin('orders.details', 'details');
                break;

            case 'payment':
                $builder->leftJoin('orders.payment', 'payment');
                break;

            case 'paymentStatus':
                $builder->leftJoin('orders.paymentStatus', 'paymentStatus');
                break;

            case 'orderStatus':
                $builder->leftJoin('orders.orderStatus', 'orderStatus');
                break;

            case 'customer':
                $builder->leftJoin('orders.customer', 'customer');
                break;

            case 'billingCountry':
                $this->addAliasJoin($builder, 'billing');
                $builder->leftJoin('billing.country', 'billingCountry');
                break;

            case 'billingState':
                $this->addAliasJoin($builder, 'billing');
                $builder->leftJoin('billing.state', 'billingState');
                break;

            case 'shop':
                $builder->leftJoin('orders.shop', 'shop');
                break;

            case 'dispatch':
                $builder->leftJoin('orders.dispatch', 'dispatch');
                break;
        }
    }

    /**
     * @param string $term
     *
     * @return int[]
     */
    protected function searchInOrders($term)
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $query->select('orders.id');
        $query->from('s_order', 'orders');
        $builder = Shopware()->Container()->get('shopware.model.search_builder');
        $builder->addSearchTerm($query, $term, [
            'orders.ordernumber^3',
            'orders.transactionID^1',
            'orders.comment^0.2',
            'orders.customercomment^0.2',
            'orders.internalcomment^0.2',
        ]);

        $query->setMaxResults(self::SEARCH_TERM_LIMIT);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param string $term
     *
     * @return int[]
     */
    private function searchOrderIds($term)
    {
        $orders = $this->searchInOrders($term);

        $customers = $this->searchCustomers($term, $orders);
        $orders = array_keys(array_flip(array_merge($orders, $customers)));

        $billing = $this->searchAddressTable($term, 's_order_billingaddress', $orders);
        $orders = array_keys(array_flip(array_merge($orders, $billing)));

        $shipping = $this->searchAddressTable($term, 's_order_shippingaddress', $orders);
        $orders = array_keys(array_flip(array_merge($orders, $shipping)));

        $documents = $this->searchDocumentsTable($term, 's_order_documents', $orders);

        return array_keys(array_flip(array_merge($orders, $documents)));
    }

    /**
     * @param string $term
     * @param int[]  $excludeOrders
     *
     * @return array
     */
    private function searchCustomers($term, array $excludeOrders = [])
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $query->select(['DISTINCT customer.id']);
        $query->from('s_user', 'customer');

        $builder = Shopware()->Container()->get('shopware.model.search_builder');
        $builder->addSearchTerm($query, $term, [
            'customer.customernumber^1',
            'customer.email^2',
            'customer.firstname^3',
            'customer.lastname^3',
        ]);

        $query->setMaxResults(self::SEARCH_TERM_LIMIT);
        $ids = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return [];
        }

        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $query->select(['orders.id']);
        $query->from('s_order', 'orders');
        $query->where('orders.userID IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        if (!empty($excludeOrders)) {
            $query->andWhere('orders.id NOT IN (:exclude)');
            $query->setParameter(':exclude', $excludeOrders, Connection::PARAM_INT_ARRAY);
        }

        $query->setMaxResults(self::SEARCH_TERM_LIMIT);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param string $term
     * @param string $table
     * @param int[]  $excludedOrderIds
     *
     * @return int[]
     */
    private function searchAddressTable($term, $table, array $excludedOrderIds = [])
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $query->select('address.orderID');
        $query->from($table, 'address');
        $builder = Shopware()->Container()->get('shopware.model.search_builder');
        $builder->addSearchTerm($query, $term, [
            'address.company^1',
            'address.street^1',
            'address.zipcode^1',
            'address.city^2',
            'address.lastname^3',
            'address.firstname^3',
        ]);

        if (!empty($excludedOrderIds)) {
            $query->andWhere('address.orderID NOT IN (:ids)');
            $query->setParameter(':ids', $excludedOrderIds, Connection::PARAM_INT_ARRAY);
        }
        $query->setMaxResults(self::SEARCH_TERM_LIMIT);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param string $term
     * @param string $table
     * @param int[]  $excludedOrderIds
     *
     * @return int[]
     */
    private function searchDocumentsTable($term, $table, array $excludedOrderIds = [])
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $query->select('documents.orderID');
        $query->from($table, 'documents');
        $builder = Shopware()->Container()->get('shopware.model.search_builder');
        $builder->addSearchTerm($query, $term, [
            'documents.docID^1',
        ]);

        if (!empty($excludedOrderIds)) {
            $query->andWhere('documents.orderID NOT IN (:ids)');
            $query->setParameter(':ids', $excludedOrderIds, Connection::PARAM_INT_ARRAY);
        }
        $query->setMaxResults(self::SEARCH_TERM_LIMIT);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
