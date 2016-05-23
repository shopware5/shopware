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

use Shopware\Components\Model\ModelRepository;
use Doctrine\ORM\Query;

/**
 * Repository class for Supplier entity
 */
class SupplierRepository extends ModelRepository
{
    /**
     * Query to fetch all suppliers that can be used
     * to generate friendly routes
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFriendlyUrlSuppliersQuery($offset = null, $limit = null)
    {
        return $this->getFriendlyUrlSuppliersBuilder($offset, $limit)->getQuery();
    }

    /**
     * Query builder to fetch all suppliers that can be used
     * to generate friendly routes
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFriendlyUrlSuppliersBuilder($offset = null, $limit = null)
    {
        $builder = $this->createQueryBuilder('supplier')
            ->select(array('supplier.id'));

        if ($limit != null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Query to fetch the number of suppliers that can be used
     * to generate friendly routes
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFriendlyUrlSuppliersCountQueryBuilder()
    {
        return $this->createQueryBuilder('supplier')
                ->select('COUNT(DISTINCT supplier.id)');
    }

    /**
     * Returns the \Doctrine\ORM\Query to select the manufacturer detail information based on the manufacturer id
     * Used for detail information in the backend module.
     *
     * @param int $manufacturerId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($manufacturerId)
    {
        $builder = $this->getDetailQueryBuilder($manufacturerId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $manufacturerId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilder($manufacturerId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'supplier',
            'attribute',
        ))
            ->from('Shopware\Models\Article\Supplier', 'supplier')
            ->leftJoin('supplier.attribute', 'attribute')
            ->where('supplier.id = ?1')
            ->setParameter(1, $manufacturerId);

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all manufacturers for example for the backend tree
     *
     * @param array $filterBy
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery(array $filterBy, array $orderBy, $limit = null, $offset = null)
    {
        $builder = $this->getListQueryBuilder($filterBy, $orderBy, $limit, $offset);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param   array $filterBy
     * @param   array $orderBy
     * @param   null  $limit
     * @param   null  $offset
     *
     * @return  \Doctrine\ORM\Query
     */
    public function getListQueryBuilder(array $filterBy, array $orderBy, $limit = null, $offset = null)
    {
        /**@var $builder \Shopware\Components\Model\QueryBuilder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['supplier'])
            ->from('Shopware\Models\Article\Supplier', 'supplier');

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
