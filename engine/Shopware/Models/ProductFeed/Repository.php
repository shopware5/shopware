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

namespace Shopware\Models\ProductFeed;

use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * Repository for the ProductFeed model (Shopware\Models\ProductFeed\ProductFeed).
 * <br>
 * The ProductFeed model repository is responsible to load all feed data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of defined
     * product feeds.
     *
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($orderBy = null, $offset = null, $limit = null)
    {
        $builder = $this->getListQueryBuilder($orderBy);
        if ($limit !== null) {
            $builder->setFirstResult($offset)->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder(array $orderBy = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'productFeed.id as id',
                'productFeed.name as name',
                'productFeed.active as active',
                'productFeed.fileName as fileName',
                'productFeed.countArticles as countArticles',
                'productFeed.hash as hash',
                'productFeed.lastExport as lastExport',
            ]
        );
        $builder->from(\Shopware\Models\ProductFeed\ProductFeed::class, 'productFeed');
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of active
     * product feeds.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getActiveListQuery()
    {
        $builder = $this->getActiveListQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getActiveListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActiveListQueryBuilder()
    {
        $builder = $this->createQueryBuilder('feeds');
        $builder->select(
            [
                'feeds',
            ]
        );
        $builder->where('feeds.active = 1');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which
     * holds the detail information of the product feed
     *
     * @param int $feedId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($feedId)
    {
        $builder = $this->getDetailQueryBuilder($feedId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $feedId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilder($feedId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['feeds', 'suppliers', 'categories', 'articles'])
                ->from(\Shopware\Models\ProductFeed\ProductFeed::class, 'feeds')
                ->leftJoin('feeds.categories', 'categories')
                ->leftJoin('feeds.suppliers', 'suppliers')
                ->leftJoin('feeds.articles', 'articles')
                ->where('feeds.id = ?1')
                ->setParameter(1, $feedId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the product feed attributes
     * for the passed id.
     *
     * @param int $productFeedId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAttributesQuery($productFeedId)
    {
        $builder = $this->getAttributesQueryBuilder($productFeedId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $productFeedId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($productFeedId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
                ->from(\Shopware\Models\Attribute\ProductFeed::class, 'attribute')
                ->where('attribute.productFeedId = ?1')
                ->setParameter(1, $productFeedId);

        return $builder;
    }
}
