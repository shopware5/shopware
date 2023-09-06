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

namespace Shopware\Models\Order;

use DateInterval;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\OrderBy;
use PDO;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Article;
use Shopware\Models\Document\Document;
use Shopware\Models\Order\Document\Document as OrderDocument;
use Shopware\Models\Payment\PaymentInstance;
use Shopware\Models\Voucher\Voucher;

/**
 * Repository for the order model (Shopware\Models\Order\Order).
 *
 * The order model repository is responsible to load all order data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 *
 * @extends ModelRepository<Order>
 */
class Repository extends ModelRepository
{
    /**
     * Limits the result of the search term queries
     */
    public const SEARCH_TERM_LIMIT = 400;

    /**
     * Returns a query-object for all known payment status
     *
     * @param array|null     $filter
     * @param string|OrderBy $order
     * @param int|null       $offset
     * @param int|null       $limit
     *
     * @return Query<Status>
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
     * @param array|null     $filter
     * @param string|OrderBy $order
     *
     * @return QueryBuilder
     */
    public function getPaymentStatusQueryBuilder($filter = null, $order = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'status.id as id',
            'status.name as name',
        ]);
        $builder->from(Status::class, 'status')
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
     * @return Query<Status>
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
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'status.id as id',
            'status.name as name',
        ]);
        $builder->from(Status::class, 'status');
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
     * @param array<array{property: string, value: mixed, expression?: string}>|null $filters
     * @param string|array<array{property: string, direction: string}>|null          $orderBy
     * @param int|null                                                               $offset
     * @param int|null                                                               $limit
     *
     * @return Query<Order>
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
     * @param array<array{property: string, value: mixed, expression?: string}>|null $filters
     * @param string|array<array{property: string, direction: string}>|null          $orderBy
     *
     * @return QueryBuilder
     */
    public function getOrdersQueryBuilder($filters = null, $orderBy = null)
    {
        /** @var QueryBuilder $builder */
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

        $builder->from(Order::class, 'orders');
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
     * @return Query<DetailStatus>
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
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('detailStatus')
            ->from(DetailStatus::class, 'detailStatus')
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
     * @return Query<History>
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
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'history.changeDate',
            'user.name as userName',
            'history.previousOrderStatusId as prevOrderStatusId',
            'history.orderStatusId as currentOrderStatusId',
            'history.previousPaymentStatusId as prevPaymentStatusId',
            'history.paymentStatusId as currentPaymentStatusId',
        ]);
        $builder->from(History::class, 'history')
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
     * @return Query<Document>
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
        /** @var QueryBuilder $builder */
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
     * @return Query<array<string, mixed>>
     */
    public function getVoucherQuery()
    {
        $today = new DateTime();
        $today = "'" . $today->format('Y-m-d') . "'";

        $builder = Shopware()->Models()->createQueryBuilder();

        return $builder->select([
                'voucher.id',
                'voucher.description',
                'voucher.voucherCode',
                'voucher.value',
                'voucher.minimumCharge',
            ])
           ->from(Voucher::class, 'voucher')
           ->join('voucher.codes', 'codes')
           ->where('(voucher.validTo>= :today OR voucher.validTo IS NULL)')
           ->setParameter('today', $today)
           ->andWhere('codes.customerId IS NULL')
           ->andWhere('codes.cashed= 0')
           ->andWhere('voucher.modus= 1')
           ->getQuery();
    }

    /**
     * @param int[] $ids
     *
     * @return array<array<string, mixed>>
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
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
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

        $builder->from(Order::class, 'orders', 'orders.id');
        $builder->leftJoin('orders.customer', 'customer');
        $builder->leftJoin('orders.shipping', 'shipping');
        $builder->leftJoin('shipping.state', 'shippingState');
        $builder->leftJoin('shipping.country', 'shippingCountry');
        $builder->leftJoin('orders.languageSubShop', 'subShop');
        $builder->leftJoin('subShop.locale', 'locale');
        $builder->leftJoin('orders.payment', 'payment');
        $builder->leftJoin('orders.paymentStatus', 'paymentStatus');
        $builder->leftJoin('orders.orderStatus', 'orderStatus');
        $builder->leftJoin('orders.billing', 'billing');
        $builder->leftJoin('billing.country', 'billingCountry');
        $builder->leftJoin('billing.state', 'billingState');
        $builder->leftJoin('orders.shop', 'shop');
        $builder->leftJoin('orders.dispatch', 'dispatch');

        return $builder;
    }

    /**
     * @param int[] $orderIds
     *
     * @return QueryBuilder
     */
    public function getDocumentsQueryBuilder(array $orderIds)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['document', 'documentType']);
        $builder->from(OrderDocument::class, 'document');
        $builder->leftJoin('document.type', 'documentType');
        $builder->where('IDENTITY(document.order) IN (:ids)');
        $builder->setParameter(':ids', $orderIds, Connection::PARAM_INT_ARRAY);

        return $builder;
    }

    /**
     * @param int[] $orderIds
     *
     * @return array<array<string, mixed>>
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
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'attribute']);
        $builder->from(Detail::class, 'details');
        $builder->leftJoin('details.attribute', 'attribute');
        $builder->where('IDENTITY(details.order) IN (:ids)');
        $builder->setParameter(':ids', $orderIds, Connection::PARAM_INT_ARRAY);

        return $builder;
    }

    /**
     * @param int[] $orderIds
     *
     * @return array<array<string, mixed>>
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
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['payments']);
        $builder->from(PaymentInstance::class, 'payments');
        $builder->where('IDENTITY(payments.order) IN (:ids)');
        $builder->setParameter(':ids', $orderIds, Connection::PARAM_INT_ARRAY);

        return $builder;
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
     * @param int|null                                                          $offset
     * @param int|null                                                          $limit
     * @param array<array{property: string, value: mixed, expression?: string}> $filters
     * @param array<array{property: string, direction: string}>                 $sortings
     *
     * @return array{total: int, orders: array<array{id: int}>}
     */
    public function search($offset = null, $limit = null, $filters = [], $sortings = [])
    {
        $em = $this->getEntityManager();
        $builder = $em->createQueryBuilder()
            ->select(['orders.id'])
            ->from(Order::class, 'orders')
            ->leftJoin('orders.attribute', 'attribute')
            ->andWhere('orders.number IS NOT NULL')
            ->andWhere('orders.status != :cancelStatus')
            ->setParameter(':cancelStatus', -1);

        $builder = $this->filterListQuery($builder, $filters);
        $builder = $this->sortListQuery($builder, $sortings);

        if ($offset !== null) {
            $builder->setFirstResult($offset);
        }
        if ($limit !== null) {
            $builder->setMaxResults($limit);
        }
        /** @var Query<array{id: int}> $query */
        $query = $builder->getQuery();
        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
        $paginator = $em->createPaginator($query);

        return [
            'total' => $paginator->count(),
            'orders' => iterator_to_array($paginator),
        ];
    }

    /**
     * @param QueryBuilder                                      $builder
     * @param array<array{property: string, direction: string}> $sortings
     *
     * @return QueryBuilder
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
     * @param array<array{property: string, value: mixed, expression?: string}>|null $filters
     *
     * @return QueryBuilder
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
                    $tmp = new DateTime($filter['value']);
                    $builder->andWhere('orders.orderTime >= :orderTimeFrom');
                    $builder->setParameter('orderTimeFrom', $tmp->format('Ymd'));
                    break;

                case 'to':
                    $tmp = new DateTime($filter['value']);
                    $tmp->add(new DateInterval('P1D'));
                    $builder->andWhere('orders.orderTime <= :orderTimeTo');
                    $builder->setParameter('orderTimeTo', $tmp->format('Ymd'));
                    break;

                case 'details.articleNumber':
                    $builder->andWhere('details.articleNumber LIKE :articleNumber');
                    $builder->setParameter('articleNumber', $filter['value']);
                    break;

                case 'article.supplierId':
                    $builder->andWhere('article.supplierId = :supplierId');
                    $builder->setParameter('supplierId', $filter['value']);
                    break;

                default:
                    $builder->addFilter([$filter]);
            }
        }

        return $builder;
    }

    /**
     * @param string $alias
     *
     * @return void
     */
    protected function addAliasJoin(QueryBuilder $builder, $alias)
    {
        if (\in_array($alias, $builder->getAllAliases(), true)) {
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

            case 'article':
                $this->addAliasJoin($builder, 'details');
                $builder->leftJoin(Article::class, 'article', 'WITH', 'article.id = details.articleId');
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

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<int, int|string>
     */
    private function searchOrderIds(string $term): array
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
     * @param int[] $excludeOrders
     */
    private function searchCustomers(string $term, array $excludeOrders = []): array
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
        $ids = $query->execute()->fetchAll(PDO::FETCH_COLUMN);

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

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param array<int, int|string> $excludedOrderIds
     *
     * @return int[]
     */
    private function searchAddressTable(string $term, string $table, array $excludedOrderIds = []): array
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

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param array<int, int|string> $excludedOrderIds
     *
     * @return int[]
     */
    private function searchDocumentsTable(string $term, string $table, array $excludedOrderIds = []): array
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

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }
}
