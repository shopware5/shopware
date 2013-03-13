<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Category
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

namespace Shopware\Models\Category;
use Shopware\Components\Model\TreeRepository,
    Shopware\Models\Article\Article,
    Doctrine\ORM\Query\Expr;

/**
 * This class gathers all categories with there id, description, position, parent category id and the number
 * of articles assigned to that category.
 *
 * Uses the articles association to get the numbers of articles.
 *
 * Affected Models
 *  - Category
 *  - Articles
 *
 * Affected tables
 *  - s_categories
 *  - s_articles
 *  - s_articles_categories
 */
class Repository extends TreeRepository
{
    /**
     * Returns the \Doctrine\ORM\Query to select all categories for example for the backend tree
     *
     * @param array $filterBy
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     * @param bool $selectOnlyActive
     * @internal param $categoryId
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery(array $filterBy, array $orderBy, $limit = null, $offset = null, $selectOnlyActive = true)
    {
        $builder = $this->getListQueryBuilder($filterBy, $orderBy, $limit, $offset, $selectOnlyActive);
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
     * @param bool $selectOnlyActive
     * @return  \Doctrine\ORM\Query
     */
    public function getListQueryBuilder(array $filterBy, array $orderBy, $limit = null, $offset = null, $selectOnlyActive = true)
    {
        $articleSelect = $this->getArticleQueryBuilder($selectOnlyActive)->getDQL();
        $builder = $this->createQueryBuilder('c')
            ->select(array(
                'c.id as id',
                'c.name as name',
                'c.position as position',
                'c.parentId as parentId',
                '(c.right - c.left - 1) / 2 as childrenCount',
                '(' . $articleSelect . ') as articleCount'
            ));

        $builder->addFilter($filterBy);
        $builder->addOrderBy('c.left');
        $builder->addOrderBy($orderBy);

        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select the category detail information based on the category id
     * Used for detail information in the backend module.
     *
     * @param $categoryId
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($categoryId)
    {
        $builder = $this->getDetailQueryBuilder($categoryId);
        return $builder->getQuery();
    }


    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $categoryId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilder($categoryId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
                'category',
                'PARTIAL articles.{id, name}',
                'PARTIAL mainDetail.{id,number}',
                'PARTIAL supplier.{id,name}',
                'attribute',
                'emotions', 'customerGroups', 'media'
            ))
            ->from($this->getEntityName(), 'category')
            ->leftJoin('category.articles', 'articles')
            ->leftJoin('articles.mainDetail', 'mainDetail')
            ->leftJoin('articles.supplier', 'supplier')
            ->leftJoin('category.attribute', 'attribute')
            ->leftJoin('category.emotions', 'emotions')
            ->leftJoin('category.media', 'media')
            ->leftJoin('category.customerGroups', 'customerGroups')
            ->where("category.id = ?1")
            ->setParameter(1,$categoryId);

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getListQueryBuilder" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param bool $selectOnlyActive
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getArticleQueryBuilder($selectOnlyActive = true)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->from($this->getEntityName(), 'ac')
            ->select('COUNT(a)')
            ->join('ac.articles', 'a')
            ->where('ac.right <= c.right AND ac.left >= c.left');

        if($selectOnlyActive) {
            $builder->andWhere('ac.active=1');
        }
        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getActiveQueryBuilder" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getActiveArticleQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->from($this->getEntityName(), 'ac')
            ->select('COUNT(a)')
            ->join(
                'ac.articles', 'a',
                Expr\Join::WITH,
                'a.active=1'
            )
            ->where('ac.active=1')
            ->andWhere('ac.right <= c.right AND ac.left >= c.left');
        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all active category by parent
     *
     * @param $parentId
     * @param null $customerGroupId
     * @return  \Doctrine\ORM\Query
     */
    public function getActiveByParentIdQuery($parentId, $customerGroupId = null)
    {
        $builder = $this->getActiveQueryBuilder($customerGroupId)
                ->andWhere('c.parentId = ?0')
                ->setParameter(0, $parentId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the
     * "getActiveByParentIdQuery, getActiveChildrenByIdQuery, getActiveByIdQuery" functions.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $customerGroupId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getActiveQueryBuilder($customerGroupId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $articleSelect = $this->getActiveArticleQueryBuilder()->getDQL();
        $builder->from($this->getEntityName(), 'c')
            ->select(array(
                'c as category',
                'attribute',
                'media',
                '(c.right - c.left - 1) / 2 as childrenCount',
                '(' . $articleSelect . ') as articleCount'
            ))
            ->leftJoin('c.media', 'media')
            ->leftJoin('c.attribute', 'attribute')
            ->where('c.active=1')
            ->addOrderBy('c.left')
            ->having('articleCount > 0 OR c.external IS NOT NULL OR c.blog = 1');

        if(isset($customerGroupId)) {
            $builder->leftJoin('c.customerGroups', 'cg', 'with', 'cg.id = :cgId')
                    ->setParameter('cgId', $customerGroupId)
                    ->andHaving('COUNT(cg.id) = 0')
                    ->groupBy('c.id');
        }

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all assigned categories by the articleId
     *
     * @param   int $articleId
     * @param   int|null $parentId
     * @return  \Doctrine\ORM\Query
     */
    public function getActiveByArticleIdQuery($articleId, $parentId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder = $builder
            ->from($this->getEntityName(), 'c')
            ->select(array('c'))
            ->where('c.active=1')
            ->join('c.articles', 'a', Expr\Join::WITH, 'a.id= ?0')
            ->setParameter(0, $articleId)
            ->addOrderBy('c.position');
        if ($parentId !== null) {
            $parent = $this->find($parentId);
            $builder->andWhere('c.left > ?1')
                    ->setParameter(1, $parent->getLeft())
                    ->andWhere('c.right < ?2')
                    ->setParameter(2, $parent->getRight());
        }
        return $builder->getQuery();
    }

    /**
     * Returns the \Doctrine\ORM\Query to select the category information by category id
     *
     * @param   int $id The id of the category
     * @param   int|null $customerGroupId
     * @return  \Doctrine\ORM\Query
     */
    public function getActiveByIdQuery($id, $customerGroupId = null)
    {
        $builder = $this->getActiveQueryBuilder($customerGroupId)
            ->andWhere('c.id = ?0')
            ->setParameter(0, $id);

        return $builder->getQuery();
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all active children by the category id
     *
     * @param $id | category id
     * @param   null|int $customerGroupId
     * @param   null|int $depth
     * @return  \Doctrine\ORM\Query
     */
    public function getActiveChildrenByIdQuery($id, $customerGroupId = null, $depth = null)
    {
        $node = $this->find($id);

        $builder = $this->getActiveQueryBuilder($customerGroupId)
            ->andWhere('c.left > ?0')
            ->setParameter(0, $node->getLeft())
            ->andWhere('c.right < ?1')
            ->setParameter(1, $node->getRight());

        //this subquery consider that only categories with active parents are selected
        $subQueryBuilder = $this->getEntityManager()->createQueryBuilder();
        $subQueryBuilder->from($this->getEntityName(), 'c2')
                ->select('count(c2)')
                ->where('c2.left < c.left')
                ->andWhere('c2.right > c.right')
                ->andWhere('c2.level < c.level')
                ->andWhere('c2.active != true')
                ->setFirstResult(0)
                ->setMaxResults(1);

        $subQueryDQL = $subQueryBuilder->getDQL();

        $builder->addSelect('(' . $subQueryDQL . ') as parentNotActive');
        $builder->andHaving('parentNotActive = 0');


        if($depth !== null) {
            $depth += $node->getLevel();
            $builder->andWhere('c.level <= ?2')
                    ->setParameter(2, $depth);
        }

        return $builder->getQuery();
    }

    /**
     * @param Category|int $category
     * @return int
     */
    public  function getActiveArticleIdByCategoryId($category)
    {
        if ($category !== null && !$category instanceof Category) {
            $category = $this->find($category);
        }
        if($category === null) {
            return null;
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->from($this->getEntityName(), 'c');
        $builder->select('MIN(a.id)')
            ->join('c.articles', 'a', Expr\Join::WITH, 'a.active=1');

        $builder->where('c.active=1');
        $builder->andWhere('c.right <= :right AND c.left >= :left');
        $builder->setParameters(array(
            'right' => $category->getRight(),
            'left' => $category->getLeft(),
        ));

        return $builder->getQuery()->getResult(
            \Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR
        );
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all blog categories for example for the blog backend list
     *
     * @param $parentId
     * @internal param $filterBy
     * @return \Doctrine\ORM\Query
     */
    public function getBlogCategoriesByParentQuery($parentId)
    {
        $builder = $this->getBlogCategoriesByParentBuilder($parentId);
        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getBlogCategoriesByParentQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $parentId
     * @return  \Shopware\Components\Model\QueryBuilder
     */
    public function getBlogCategoriesByParentBuilder($parentId)
    {
        $categoryNode = $this->find($parentId);
        $builder = $this->createQueryBuilder('categories')
            ->select(
                array(
                    'categories'
                )
            )
            ->andWhere('categories.left > :left AND categories.right < :right AND categories.blog = 1')
            ->setParameter("left", $categoryNode->getLeft())
            ->setParameter("right", $categoryNode->getRight());

        return $builder;
    }


    /**
     * Returns the \Doctrine\ORM\Query to select all blog categories and parent categories
     * with blog child elements for example for the blog backend tree
     *
     * @param $filterBy
     * @return \Doctrine\ORM\Query
     */
    public function getBlogCategoryTreeListQuery($filterBy)
    {
        $builder = $this->getBlogCategoryTreeListBuilder();

        $builder->addFilter($filterBy);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getBlogCategoryTreeListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return  \Doctrine\ORM\Query
     */
    public function getBlogCategoryTreeListBuilder()
    {
        $builder = $this->createQueryBuilder('c')
            ->select(array(
                'c.id as id',
                'c.name as name',
                'c.position as position',
                'c.blog as blog',
                '(
                    SELECT COUNT(c2)
                    FROM \Shopware\Models\Category\Category c2
                    WHERE c2.left > c.left AND c2.right < c.right AND c2.blog = 1
                ) as childrenCount',
            ))
            ->having('childrenCount > 0 OR blog = 1');
        return $builder;
    }

}
