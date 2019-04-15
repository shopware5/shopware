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

namespace Shopware\Models\Customer;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Order\Order;

/**
 * Repository for the customer model (Shopware\Models\Customer\Customer).
 *
 * The customer model repository is responsible to load all customer data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all data about a single customer.
     *
     * @param int $customerId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCustomerDetailQuery($customerId)
    {
        $builder = $this->getCustomerDetailQueryBuilder($customerId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCustomerDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $customerId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerDetailQueryBuilder($customerId)
    {
        // Sub query to select the canceledOrderAmount. This can't be done with another join condition
        $subQueryBuilder = $this->getEntityManager()->createQueryBuilder();
        $subQueryBuilder->select('SUM(canceledOrders.invoiceAmount)')
            ->from(Customer::class, 'customer2')
            ->leftJoin('customer2.orders', 'canceledOrders', \Doctrine\ORM\Query\Expr\Join::WITH, 'canceledOrders.cleared = 16')
            ->where('customer2.id = :customerId');

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'customer',
            'IDENTITY(customer.defaultBillingAddress) as default_billing_address_id',
            'IDENTITY(customer.defaultShippingAddress) as default_shipping_address_id',
            'billing',
            'shipping',
            'paymentData',
            'locale.language',
            'shop.name as shopName',
            $builder->expr()->count('doneOrders.id') . ' as orderCount',
            'SUM(doneOrders.invoiceAmount) as amount',
            '(' . $subQueryBuilder->getDQL() . ') as canceledOrderAmount',
        ]);
        // Join s_orders second time to display the count of canceled orders and the count and total amount of done orders
        $builder->from($this->getEntityName(), 'customer')
                ->leftJoin('customer.defaultBillingAddress', 'billing')
                ->leftJoin('customer.defaultShippingAddress', 'shipping')
                ->leftJoin('customer.shop', 'shop')
                ->leftJoin('customer.languageSubShop', 'subShop')
                ->leftJoin('subShop.locale', 'locale')
                ->leftJoin('customer.paymentData', 'paymentData', \Doctrine\ORM\Query\Expr\Join::WITH, 'paymentData.paymentMean = customer.paymentId')
                ->leftJoin('customer.orders', 'doneOrders', \Doctrine\ORM\Query\Expr\Join::WITH, 'doneOrders.status <> -1 AND doneOrders.status <> 4')
                ->where('customer.id = :customerId')
                ->setParameter('customerId', $customerId);

        $builder->groupBy('customer.id');

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all defined customer groups. Used to create the customer group price tabs on the article detail page in the article backend module.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCustomerGroupsQuery()
    {
        $builder = $this->getCustomerGroupsQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCustomerGroupsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerGroupsQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['groups'])
                       ->from(Group::class, 'groups')
                       ->orderBy('groups.id');
    }

    /**
     * Returns a list of orders for the passed customer id and filtered by the filter parameter.
     *
     * @param int      $customerId
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getOrdersQuery($customerId, $filter = null, $orderBy = null, $limit = null, $offset = null)
    {
        $builder = $this->getOrdersQueryBuilder($customerId, $filter, $orderBy);
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
     * @param int         $customerId
     * @param string|null $filter
     * @param array|null  $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrdersQueryBuilder($customerId, $filter = null, $orderBy = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        // Select the different entities
        $builder->select([
            'orders.id as id',
            'orders.number as orderNumber',
            'orders.invoiceAmount as invoiceAmount',
            'orders.orderTime as orderTime',
            'dispatch.id as dispatchId',
            'orders.paymentId as paymentId',
            'orders.status as orderStatusId',
            'orders.cleared as paymentStatusId',
        ]);

        // Join the required tables for the order list
        $builder->from(Order::class, 'orders')
                ->leftJoin('orders.payment', 'payment')
                ->leftJoin('orders.dispatch', 'dispatch')
                ->leftJoin('orders.orderStatus', 'orderStatus')
                ->leftJoin('orders.paymentStatus', 'paymentStatus');

        // Filter the displayed columns with the passed filter string
        if (!empty($filter)) {
            $builder->where('orders.customerId = :customerId');
            $builder->andWhere(
                '(
                    orders.number LIKE ?1 
                    OR orders.invoiceAmount LIKE ?3 
                    OR orders.orderTime LIKE ?2 
                    OR payment.description LIKE ?1 
                    OR dispatch.name LIKE ?1 
                    OR orderStatus.description LIKE ?1
                    OR paymentStatus.description LIKE ?1
                )'
            )
            ->setParameter(1, $filter . '%')
            ->setParameter(2, '%' . $filter)
            ->setParameter(3, str_replace('.', '_', str_replace(',', '_', $filter)) . '%')
            ->setParameter('customerId', $customerId);
        } else {
            $builder->where('orders.customerId = :customerId')->setParameter('customerId', $customerId);
        }
        $builder->andWhere('orders.status NOT IN (:stati)');
        $builder->setParameter(':stati', [-1, 4], Connection::PARAM_INT_ARRAY);

        $this->addOrderBy($builder, $orderBy);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search for customers
     * with the passed email address. The passed customer id is excluded.
     *
     * @param string|null $email
     * @param int|null    $customerId
     * @param int|null    $shopId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getValidateEmailQuery($email = null, $customerId = null, $shopId = null)
    {
        $builder = $this->getValidateEmailQueryBuilder($email, $customerId, $shopId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateEmailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string|null $email
     * @param int|null    $customerId
     * @param int|null    $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getValidateEmailQueryBuilder($email = null, $customerId = null, $shopId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['customer'])
                ->from($this->getEntityName(), 'customer')
                ->where('customer.email = ?1')
                ->setParameter(1, $email);

        if (!empty($customerId)) {
            $builder->andWhere('customer.id != ?2')
                    ->setParameter(2, $customerId);
            $builder->andWhere('customer.accountMode = 0');
        }

        if (!empty($shopId)) {
            $builder->andWhere('customer.shopId  = ?3')
               ->setParameter(3, $shopId);
        }

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all defined customer groups. Used to show all unselected customer groups to restrict the category
     *
     * @param int[] $usedIds
     * @param int   $offset
     * @param int   $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCustomerGroupsWithoutIdsQuery($usedIds, $offset, $limit)
    {
        $builder = $this->getCustomerGroupsWithoutIdsQueryBuilder($usedIds, $offset, $limit);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCustomerGroupsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[] $usedIds
     * @param int   $offset
     * @param int   $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerGroupsWithoutIdsQueryBuilder($usedIds, $offset, $limit)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['groups'])->from(Group::class, 'groups');
        if (!empty($usedIds)) {
            $builder->where('groups.id NOT IN (:usedIds)')
                ->setParameter('usedIds', $usedIds, Connection::PARAM_INT_ARRAY);
        }
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder;
    }
}
