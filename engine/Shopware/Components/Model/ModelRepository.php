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
use Enlight_Hook;

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
 *
 * @template TEntityClass of object
 * @extends EntityRepository<TEntityClass>
 */
class ModelRepository extends EntityRepository implements Enlight_Hook
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
