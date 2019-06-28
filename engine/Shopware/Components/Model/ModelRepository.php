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

namespace Shopware\Components\Model;

use Doctrine\ORM\EntityRepository;

/**
 * Interface for the various standard models.
 *
 * This interface defines all standard functions for the various models
 * These standard function must later be implemented in the various models.
 *
 * <code>
 * $modelRepository = new Shopware\Components\Models\ModelRepository;
 * $modelRepository->createQueryBuilder();
 * </code>
 */
class ModelRepository extends EntityRepository implements \Enlight_Hook
{
    /**
     * Creates a new QueryBuilder instance that is pre-populated for this entity name.
     *
     * @param string $alias
     * @param string $indexBy the index for the from
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder($alias, $indexBy = null)
    {
        /** @var QueryBuilder $builder */
        $builder = parent::createQueryBuilder($alias, $indexBy);
        $builder->setAlias($alias);

        return $builder;
    }

    /**
     * Returns an instance of a \Doctrine\ORM\Query object which uses the createQueryBuilder function to generate
     * the sql statements.
     *
     * @param int $limit  max count of returned rows
     * @param int $offset start value for the limitation
     *
     * @return \Doctrine\ORM\Query
     *
     * @deprecated since 5.6, to be removed in 5.7. Use findBy([], null, $limit, $offset) instead
     */
    public function queryAll($limit = null, $offset = null)
    {
        return $this->queryBy([], [], $limit, $offset);
    }

    /**
     * Returns an instance of a \Doctrine\ORM\Query object which uses the createQueryBuilder function to generate
     * the sql statements. The query object is limited with the given extensions (where, order, limit).
     *
     * @param array $criteria Expects an array of Doctrine\ORM\Query\Expr to limit the result
     * @param array $orderBy  Expects an array of order conditions (example: array('expression' => 'name', [OPTIONAL] 'direction' => 'ASC'))
     * @param int   $limit    max count of returned rows
     * @param int   $offset   start value for the limitation
     *
     * @return \Doctrine\ORM\Query
     *
     * @deprecated since 5.6, to be removed in 5.7. Use findBy instead
     */
    public function queryBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
    {
        $builder = $this->createQueryBuilder('entity');

        $builder->addFilter($criteria);
        $builder->addOrderBy($orderBy);

        $query = $builder->getQuery();

        if (isset($limit)) {
            $query->setFirstResult($offset);
            $query->setMaxResults($limit);
        }

        return $query;
    }

    /**
     * Adds filters to the query results.
     *
     * <code>
     *      $this->addFilter($builder, array(array(
     *          'property' => 'name'
     *          'value' => 'A%'
     *      )));
     * </code>
     *
     * @return QueryBuilder
     */
    public function addFilter(QueryBuilder $builder, array $filter)
    {
        return $builder->addFilter($filter);
    }

    /**
     * Adds an ordering to the query results.
     *
     * <code>
     *      $this->addFilter($builder, array(array(
     *          'property' => 'name'
     *          'direction' => 'DESC'
     *      )));
     * </code>
     *
     * @return QueryBuilder
     */
    public function addOrderBy(QueryBuilder $builder, array $orderBy)
    {
        return $builder->addOrderBy($orderBy);
    }
}
