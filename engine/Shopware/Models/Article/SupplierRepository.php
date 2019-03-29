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

namespace Shopware\Models\Article;

use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * Repository class for Supplier entity
 */
class SupplierRepository extends ModelRepository
{
    /**
     * Query to fetch all suppliers that can be used
     * to generate friendly routes
     *
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return Query
     */
    public function getFriendlyUrlSuppliersQuery($offset = null, $limit = null)
    {
        return $this->getFriendlyUrlSuppliersBuilder($offset, $limit)->getQuery();
    }

    /**
     * Query builder to fetch all suppliers that can be used
     * to generate friendly routes
     *
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return QueryBuilder
     */
    public function getFriendlyUrlSuppliersBuilder($offset = null, $limit = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('supplier')
            ->select(['supplier.id']);

        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Query to fetch the number of suppliers that can be used
     * to generate friendly routes
     *
     * @return QueryBuilder
     */
    public function getFriendlyUrlSuppliersCountQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('supplier')
            ->select('COUNT(DISTINCT supplier.id)');

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select the manufacturer detail information based on the manufacturer id
     * Used for detail information in the backend module.
     *
     * @param int $manufacturerId
     *
     * @return Query
     */
    public function getDetailQuery($manufacturerId)
    {
        return $this->getDetailQueryBuilder($manufacturerId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $manufacturerId
     *
     * @return QueryBuilder
     */
    public function getDetailQueryBuilder($manufacturerId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'supplier',
            'attribute',
        ])
            ->from(Supplier::class, 'supplier')
            ->leftJoin('supplier.attribute', 'attribute')
            ->where('supplier.id = ?1')
            ->setParameter(1, $manufacturerId);

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all manufacturers for example for the backend tree
     *
     * @return Query
     */
    public function getListQuery(array $filterBy, array $orderBy, $limit = null, $offset = null)
    {
        return $this->getListQueryBuilder($filterBy, $orderBy, $limit, $offset)->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getListQueryBuilder(array $filterBy, array $orderBy, $limit = null, $offset = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['supplier'])
            ->from(Supplier::class, 'supplier');

        if (!empty($filterBy)) {
            $builder->addFilter($filterBy);
        }

        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }
}
