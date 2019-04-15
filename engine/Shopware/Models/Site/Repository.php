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

namespace Shopware\Models\Site;

use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * Repository for the site model (Shopware\Models\Site\Site).
 *
 * The premium model repository is responsible for loading site data.
 */
class Repository extends ModelRepository
{
    /**
     * Returns the \Doctrine\ORM\Query to select all categories for example for the backend tree
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getGroupListQuery(array $filterBy = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $builder = $this->getGroupListQueryBuilder($filterBy, $orderBy, $limit, $offset);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGroupListQueryBuilder(array $filterBy = null, array $orderBy = null, $limit = null, $offset = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->from(\Shopware\Models\Site\Group::class, 'g');
        $builder->leftJoin('g.mapping', 'm');

        $builder->select([
            'g.id as id',
            'g.key as key',
            'g.name as name',
            'g.active as active',
            'm.id as mappingId',
        ]);
        if ($filterBy !== null) {
            $builder->addFilter($filterBy);
        }
        if ($orderBy !== null) {
            $builder->addOrderBy($orderBy);
        }

        $builder->addOrderBy('mappingId');
        $builder->addOrderBy('name');

        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select all sites
     * for the passed node name and shop
     *
     * @param string $nodeName
     * @param int    $shopId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getSitesByNodeNameQuery($nodeName, $shopId = null)
    {
        $builder = $this->getSitesByNodeNameQueryBuilder($nodeName, $shopId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSitesByNodeNameQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $nodeName
     * @param int    $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSitesByNodeNameQueryBuilder($nodeName, $shopId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['sites', 'children', 'attribute', 'childrenAttribute'])
                ->from(\Shopware\Models\Site\Site::class, 'sites')
                ->leftJoin('sites.attribute', 'attribute')
                ->leftJoin('sites.children', 'children')
                ->leftJoin('children.attribute', 'childrenAttribute')
                ->where('sites.parentId = 0')
                ->andWhere(
                    // = gBottom, like 'gBottom|%', like '|gBottom', like '|gBottom|'
                    '(sites.grouping = ?1 OR sites.grouping LIKE ?2 OR sites.grouping LIKE ?3 OR sites.grouping LIKE ?4)'
                )
                ->setParameter(1, $nodeName)
                ->setParameter(2, $nodeName . '|%')
                ->setParameter(3, '%|' . $nodeName)
                ->setParameter(4, '%|' . $nodeName . '|%');

        if ($shopId) {
            $builder
                ->andWhere('(sites.shopIds LIKE :shopId OR sites.shopIds IS NULL)')
                ->setParameter('shopId', '%|' . $shopId . '|%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $siteId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAttributesQuery($siteId)
    {
        $builder = $this->getAttributesQueryBuilder($siteId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $siteId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($siteId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
                      ->from(\Shopware\Models\Attribute\Site::class, 'attribute')
                      ->where('attribute.siteId = ?1')
                      ->setParameter(1, $siteId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $siteId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getSiteQuery($siteId)
    {
        $builder = $this->getSiteQueryBuilder($siteId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSiteQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $siteId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSiteQueryBuilder($siteId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['site'])
                ->from(\Shopware\Models\Site\Site::class, 'site')
                ->leftJoin('site.attribute', 'attribute')
                ->where('site.id = ?1')
                ->setParameter(1, $siteId);

        return $builder;
    }

    /**
     * Returns a query with all site objects with an empty link
     *
     * @param int $shopId
     * @param int $offset
     * @param int $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getSitesWithoutLinkQuery($shopId, $offset, $limit)
    {
        $builder = $this->getSitesWithoutLinkQueryBuilder($shopId);
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder->getQuery();
    }

    /**
     * Returns the QueryBuilder object for getSitesWithoutLinkQuery
     *
     * @param int $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSitesWithoutLinkQueryBuilder($shopId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['site'])
            ->from(\Shopware\Models\Site\Site::class, 'site')
            ->where('site.link = \'\'')
            ->andWhere('(site.shopIds LIKE :shopId OR site.shopIds IS NULL)')
            ->setParameter('shopId', '%|' . $shopId . '|%');

        return $builder;
    }
}
