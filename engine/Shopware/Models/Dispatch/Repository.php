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

namespace Shopware\Models\Dispatch;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Expr\Join;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * Repository for the customer model (Shopware\Models\Dispatch\Dispatch).
 *
 * The dispatch models accumulates all data needed for a specific dispatch service
 */
class Repository extends ModelRepository
{
    /**
     * @param array|null        $filter
     * @param string|array|null $order
     * @param int|null          $offset
     * @param int|null          $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDispatchesQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getDispatchesQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDispatchesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null        $filter
     * @param string|array|null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDispatchesQueryBuilder($filter = null, $order = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('dispatches');
        $builder->setAlias('dispatches');
        $builder->from(\Shopware\Models\Dispatch\Dispatch::class, 'dispatches');
        $builder->setAlias('dispatches');

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }
        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns all info about known shipping and dispatch settings
     *
     * @param int         $dispatchId - If this parameter is given, only one data set will be returned
     * @param string|null $filter     - Used to search in the name and description of the dispatch data set
     * @param array       $order      - Name of the field which should considered as sorting field
     * @param int|null    $limit      - Reduce the number of returned data sets
     * @param int|null    $offset     - Start the output based on that offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getShippingCostsQuery($dispatchId = null, $filter = null, $order = [], $limit = null, $offset = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getShippingCostsQueryBuilder($dispatchId, $filter, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Returns basic info about known shipping and dispatch settings
     *
     * @param string|null $filter - Used to search in the name and description of the dispatch data set
     * @param array       $order  - Name of the field which should considered as sorting field
     * @param int|null    $limit  - Reduce the number of returned data sets
     * @param int|null    $offset - Start the output based on that offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($filter = null, $order = [], $limit = null, $offset = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getListQueryBuilder($filter, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getShippingCostsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int         $dispatchId - If this parameter is given, only one data set will be returned
     * @param string|null $filter     - Used to search in the name and description of the dispatch data set
     * @param array       $order      - Name of the field which should considered as sorting field
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getShippingCostsQueryBuilder($dispatchId = null, $filter = null, $order = [])
    {
        /** @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('dispatch');

        // Build the query
        $builder->select(['dispatch', 'countries', 'categories', 'holidays', 'payments', 'attribute'])
                ->leftJoin('dispatch.countries', 'countries')
                ->leftJoin('dispatch.categories', 'categories')
                ->leftJoin('dispatch.holidays', 'holidays')
                ->leftJoin('dispatch.attribute', 'attribute')
                ->leftJoin('dispatch.payments', 'payments');
        if ($dispatchId !== null) {
            $builder->where('dispatch.id = ?2')->setParameter(2, $dispatchId);
        }

        // Set the filtering logic
        if ($filter !== null) {
            $builder->andWhere('(dispatch.name LIKE ?1 OR dispatch.description LIKE ?1)');
            $builder->setParameter(1, '%' . $filter . '%');
        }

        // Set the order logic
        $this->addOrderBy($builder, $order);

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getShippingCostsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string|null $filter - Used to search in the name and description of the dispatch data set
     * @param array       $order  - Name of the field which should considered as sorting field
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $order = [])
    {
        /** @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('dispatch');

        // Build the query
        $builder->select(['dispatch']);

        // Set the filtering logic
        if ($filter !== null) {
            $builder->andWhere('(dispatch.name LIKE ?1 OR dispatch.description LIKE ?1)');
            $builder->setParameter(1, '%' . $filter . '%');
        }

        // Set the order logic
        $this->addOrderBy($builder, $order);

        return $builder;
    }

    /**
     * Get the shipping costs for a dispatch setting.
     *
     * @param int         $dispatchId Unique id
     * @param string|null $filter     string which is filtered
     * @param int|null    $limit      Count of the selected data
     * @param int|null    $offset     Start index of the selected data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getShippingCostsMatrixQuery($dispatchId = null, $filter = null, $limit = null, $offset = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getShippingCostsMatrixQueryBuilder($dispatchId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getShippingCostsMatrixQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $dispatchId - If this parameter is given, only one data set will be returned
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getShippingCostsMatrixQueryBuilder($dispatchId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->from(\Shopware\Models\Dispatch\ShippingCost::class, 'shippingcosts')->select(['shippingcosts']);

        // Assure that we will get an empty result set when no dispatch ID is provided
        if ($dispatchId === null || empty($dispatchId)) {
            $dispatchId = '-1';
        }
        $builder->where('shippingcosts.dispatchId = :dispatchId')->setParameter('dispatchId', $dispatchId);
        // We need a hard coded sorting here.
        $builder->orderBy('shippingcosts.from');

        return $builder;
    }

    /**
     * Purges all entries for a given dispatch ID.
     *
     * @param int $dispatchId
     *
     * @return \Doctrine\ORM\AbstractQuery
     */
    public function getPurgeShippingCostsMatrixQuery($dispatchId = null)
    {
        return $this->getEntityManager()
            ->createQuery('delete from Shopware\Models\Dispatch\ShippingCost cm where cm.dispatchId = ?1')
            ->setParameter(1, $dispatchId);
    }

    /**
     * Receives all known means of payment, even disabled ones
     *
     * @param array|null $filter
     * @param array|null $order
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getPaymentQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // Get the query and prepare the limit statement
        $builder = $this->getPaymentQueryBuilder($filter, $order);

        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                  ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPaymentQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null $filter
     * @param array|null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPaymentQueryBuilder($filter = null, $order = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $filters = [];
        if ($filter !== null && is_array($filter)) {
            foreach ($filter as $singleFilter) {
                $filters[$singleFilter['property']] = $singleFilter['value'];
            }
        }
        // Build the query
        $builder->from(\Shopware\Models\Payment\Payment::class, 'payment')
                ->select(['payment']);
        // Set the order logic
        $builder = $this->sortOrderQuery($builder, 'payment', $order);
        // Use the filter
        if (!empty($filters['usedIds'])) {
            $builder->andWhere('payment.id NOT IN (:usedIds)')
                ->setParameter('usedIds', $filters['usedIds'], Connection::PARAM_INT_ARRAY);
        }

        return $builder;
    }

    /**
     * Receives all known countries, even disabled ones
     *
     * @param array|null $filter
     * @param array|null $order
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCountryQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getCountryQueryBuilder($filter, $order);

        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCountryQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null $filter
     * @param array|null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountryQueryBuilder($filter = null, $order = null)
    {
        $filters = [];
        if ($filter !== null && is_array($filter)) {
            foreach ($filter as $singleFilter) {
                $filters[$singleFilter['property']] = $singleFilter['value'];
            }
        }
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        // Build the query
        $builder->from(\Shopware\Models\Country\Country::class, 'country')
                ->select(['country']);

        // Set the order logic
        $builder = $this->sortOrderQuery($builder, 'country', $order);

        // Use the filter
        if (!empty($filters['usedIds'])) {
            $builder->andWhere('country.id NOT IN (:usedIds)')
                ->setParameter('usedIds', $filters['usedIds'], Connection::PARAM_INT_ARRAY);
        }
        if (!empty($filters['onlyIds'])) {
            $builder->andWhere('country.id IN (:onlyIds)')
                ->setParameter('onlyIds', $filters['onlyIds'], Connection::PARAM_INT_ARRAY);
        }

        return $builder;
    }

    /**
     * Receives all known countries, even disabled ones
     *
     * @param array|null $filter
     * @param array|null $order
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getHolidayQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getHolidayQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                   ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getHolidayQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null $filter
     * @param array|null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getHolidayQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        // Build the query
        $builder->from(\Shopware\Models\Dispatch\Holiday::class, 'holiday')
                ->select(['holiday']);

        // Set the order logic
        $builder = $this->sortOrderQuery($builder, 'holiday', $order);
        // use the filter
        if (!empty($filter['usedIds'])) {
            $builder->andWhere('country.id NOT IN (:usedIds)')
                ->setParameter('usedIds', $filter['usedIds'], Connection::PARAM_INT_ARRAY);
        }

        return $builder;
    }

    /**
     * Selects all shipping costs with a deleted shop
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDispatchWithDeletedShopsQuery()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select('dispatch')
            ->from('Shopware\Models\Dispatch\Dispatch', 'dispatch')
            ->leftJoin('Shopware\Models\Shop\Shop', 'shop', Join::WITH, 'dispatch.multiShopId = shop.id')
            ->andWhere('dispatch.multiShopId IS NOT NULL')
            ->andWhere('shop.id IS NULL');

        return $builder->getQuery();
    }

    /**
     * Helper function which set the orderBy path for the order list query.
     *
     * @param string     $modelPrefix
     * @param array|null $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function sortOrderQuery(\Doctrine\ORM\QueryBuilder $builder, $modelPrefix, $orderBy)
    {
        //order the query with the passed orderBy parameter
        if (!empty($orderBy)) {
            foreach ($orderBy as $order) {
                if (!isset($order['direction'])) {
                    $order['direction'] = 'ASC';
                }
                if (isset($order['property'])) {
                    $builder->addOrderBy($modelPrefix . '.' . $order['property'], $order['direction']);
                }
            }
        }

        return $builder;
    }
}
