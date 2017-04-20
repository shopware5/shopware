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

namespace   Shopware\Models\Customer;

use Shopware\Components\Model\ModelRepository;

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
     * @param $customerId
     *
     * @internal param $id
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
     * @param $customerId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerDetailQueryBuilder($customerId)
    {
        // sub query to select the canceledOrderAmount. This can't be done with another join condition
        $subQueryBuilder = $this->getEntityManager()->createQueryBuilder();
        $subQueryBuilder->select('SUM(canceledOrders.invoiceAmount)')
            ->from('Shopware\Models\Customer\Customer', 'customer2')
            ->leftJoin('customer2.orders', 'canceledOrders', \Doctrine\ORM\Query\Expr\Join::WITH, 'canceledOrders.cleared = 16')
            ->where($subQueryBuilder->expr()->eq('customer2', $customerId));

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
        //join s_orders second time to display the count of canceled orders and the count and total amount of done orders
        $builder->from($this->getEntityName(), 'customer')
                ->leftJoin('customer.billing', 'billing')
                ->leftJoin('customer.shipping', 'shipping')
                ->leftJoin('customer.shop', 'shop')
                ->leftJoin('customer.languageSubShop', 'subShop')
                ->leftJoin('subShop.locale', 'locale')
                ->leftJoin('customer.paymentData', 'paymentData', \Doctrine\ORM\Query\Expr\Join::WITH, 'paymentData.paymentMean = customer.paymentId')
                ->leftJoin('customer.orders', 'doneOrders', \Doctrine\ORM\Query\Expr\Join::WITH, 'doneOrders.status <> -1 AND doneOrders.status <> 4')
                ->where($builder->expr()->eq('customer.id', $customerId));

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
                       ->from('Shopware\Models\Customer\Group', 'groups')
                       ->orderBy('groups.id');
    }

    /**
     * Returns a list of orders for the passed customer id and filtered by the filter parameter.
     *
     * @param      $customerId
     * @param null $filter
     * @param null $orderBy
     * @param null $limit
     * @param null $offset
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
     * @param      $customerId
     * @param      $filter
     * @param null $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrdersQueryBuilder($customerId, $filter = null, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        //select the different entities
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

        //join the required tables for the order list
        $builder->from('Shopware\Models\Order\Order', 'orders')
                ->leftJoin('orders.payment', 'payment')
                ->leftJoin('orders.dispatch', 'dispatch')
                ->leftJoin('orders.orderStatus', 'orderStatus')
                ->leftJoin('orders.paymentStatus', 'paymentStatus');

        $expr = Shopware()->Models()->getExpressionBuilder();
        //filter the displayed columns with the passed filter string
        if (!empty($filter)) {
            $builder->where(
                $expr->andX(
                    $expr->eq('orders.customerId', $customerId),
                    $expr->orX(
                        $expr->like('orders.number', '?1'),        //Search only the beginning of the order number.
                        $expr->like('orders.invoiceAmount', '?3'),      //Search only the beginning of the order amount, replace , and . with _ wildcard
                        $expr->like('orders.orderTime', '?2'),          //Search only for the end of the order date.
                        $expr->like('payment.description', '?1'),       //Search only the beginning of the payment description.
                        $expr->like('dispatch.name', '?1'),             //Search only the beginning of the dispatch name.
                        $expr->like('orderStatus.description', '?1'),    //Search only the beginning of the order state.
                        $expr->like('paymentStatus.description', '?1')
                    )
                )
            )
            ->setParameter(1, $filter . '%')
            ->setParameter(2, '%' . $filter)
            ->setParameter(3, str_replace('.', '_', str_replace(',', '_', $filter)) . '%');
        } else {
            $builder->where($expr->eq('orders.customerId', $customerId));
        }
        $builder->andWhere($builder->expr()->notIn('orders.status', ['-1', '4']));

        $this->addOrderBy($builder, $orderBy);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search for customers
     * with the passed email address. The passed customer id is excluded.
     *
     * @param null $email
     * @param null $customerId
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
     * @param null $email
     * @param null $customerId
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
     * @param $usedIds
     * @param $offset
     * @param $limit
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
     * @param $usedIds
     * @param $offset
     * @param $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerGroupsWithoutIdsQueryBuilder($usedIds, $offset, $limit)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['groups'])->from('Shopware\Models\Customer\Group', 'groups');
        if (!empty($usedIds)) {
            $builder->where($builder->expr()->notIn('groups.id', $usedIds));
        }
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder;
    }
}
