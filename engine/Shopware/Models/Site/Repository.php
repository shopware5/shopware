<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace   Shopware\Models\Site;
use         Shopware\Components\Model\ModelRepository,
            Doctrine\ORM\Query\Expr;
/**
 * Repository for the site model (Shopware\Models\Site\Site).
 * <br>
 * The premium model repository is responsible for loading site data.
 */
class Repository extends ModelRepository
{
    /**
     * Returns the \Doctrine\ORM\Query to select all categories for example for the backend tree
     *
     * @param   array $filterBy
     * @param   array $orderBy
     * @param   null $limit
     * @param   null $offset
     * @return  \Doctrine\ORM\Query
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
     * @param   array $filterBy
     * @param   array $orderBy
     * @param   null $limit
     * @param   null $offset
     * @return  \Doctrine\ORM\Query
     */
    public function getGroupListQueryBuilder(array $filterBy = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->from('Shopware\Models\Site\Group', 'g');
        $builder->leftJoin('g.mapping', 'm');

        $builder->select(array(
            'g.id as id',
            'g.key as key',
            'g.name as name',
            'g.active as active',
            'm.id as mappingId'
        ));
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
     * for the passed node name.
     * @param $nodeName
     * @return \Doctrine\ORM\Query
     */
    public function getSitesByNodeNameQuery($nodeName)
    {
        $builder = $this->getSitesByNodeNameQueryBuilder($nodeName);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSitesByNodeNameQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $nodeName
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSitesByNodeNameQueryBuilder($nodeName)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('sites', 'children', 'attribute', 'childrenAttribute'))
                ->from('Shopware\Models\Site\Site', 'sites')
                ->leftJoin('sites.attribute', 'attribute')
                ->leftJoin('sites.children', 'children')
                ->leftJoin('children.attribute', 'childrenAttribute')
                ->where($builder->expr()->eq('sites.parentId',0))
                ->andWhere(
                    $builder->expr()->orX(
                        $builder->expr()->eq('sites.grouping', '?1'),        // = gBottom
                        $builder->expr()->like('sites.grouping', '?2'),      //like 'gBottom|%
                        $builder->expr()->like('sites.grouping', '?3'),      //like '|gBottom
                        $builder->expr()->like('sites.grouping', '?4')      //like '|gBottom|
                    )
                )
                ->setParameter(1, $nodeName)
                ->setParameter(2, $nodeName . '|%')
                ->setParameter(3, '%|' . $nodeName)
                ->setParameter(4, '%|' . $nodeName . '|%');
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $siteId
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
     * @param $siteId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($siteId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                      ->from('Shopware\Models\Attribute\Site', 'attribute')
                      ->where('attribute.siteId = ?1')
                      ->setParameter(1, $siteId);
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $siteId
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
     * @param $siteId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSiteQueryBuilder($siteId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('site'))
                ->from('Shopware\Models\Site\Site', 'site')
                ->leftJoin('site.attribute', 'attribute')
                ->where('site.id = ?1')
                ->setParameter(1, $siteId);
        return $builder;
    }

    /**
     * Returns an array of all custom sites of a shop
     * @param $shopId
     * @return array
     */
    public function getSitesByShopId($shopId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('site', 'attribute'))
            ->from('Shopware\Models\Site\Site', 'site')
            ->from('Shopware\Models\Shop\Shop', 'shop')
            ->leftJoin('site.attribute', 'attribute')
            ->leftJoin('shop.pages', 'siteGroup')
            ->where(
                $builder->expr()->orX(
                    $builder->expr()->eq('site.grouping', 'siteGroup.key'),
                    $builder->expr()->like('site.grouping', 'CONCAT(\'%|\', siteGroup.key)'),
                    $builder->expr()->like('site.grouping', 'CONCAT(siteGroup.key, \'|%\')'),
                    $builder->expr()->like('site.grouping', 'CONCAT(\'%|\', siteGroup.key, \'|%\')')
                )
            )
            ->andWhere('shop.id = :shopId')
            ->setParameter('shopId', $shopId)
            ->groupBy('site.id')
            ->OrderBy('site.parentId')
            ->AddOrderBy('site.id');

        $result = $builder->getQuery()->getArrayResult();

        $sites = array();

        foreach ($result as $site) {
            $id = $site['id'];
            $parentId = $site['parentId'];

            if ($parentId) {
                if (empty($sites[$parentId])) {
                    continue;
                }
                $sites[$parentId]['children'][$id] = $site;
            } else {
                $sites[$id] = $site;
            }
        }

        return $sites;
    }
}
