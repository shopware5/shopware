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

namespace Shopware\Models\Category;

use Doctrine\ORM\Query\Expr;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

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
class Repository extends ModelRepository
{
    /**
     * @param int          $id        identifier of category
     * @param string|array $field     string or array of selectable fields
     * @param string|null  $separator if separator is given string will be returned
     *
     * @return array|string
     */
    public function getPathById($id, $field = 'name', $separator = null)
    {
        if (empty($id)) {
            return '';
        }

        /** @var Category $category */
        $category = $this->find($id);

        $before = $this->getCategoryPathBefore($category, $field, $separator);

        $self = $this->getCategoryPathQuery($id, $field);

        if (!$before) {
            if ($separator) {
                return $self;
            }

            return [$id => $self];
        }

        $before[$category->getId()] = $self;
        if ($separator !== null) {
            return implode($separator, $before);
        }

        return $before;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all categories for example for the backend tree
     *
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $selectOnlyActive
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery(array $filterBy, array $orderBy = [], $limit = null, $offset = null, $selectOnlyActive = true)
    {
        $builder = $this->getListQueryBuilder($filterBy, $orderBy, $limit, $offset, $selectOnlyActive);

        return $builder->getQuery();
    }

    /**
     * Returns a query builder object to get all defined categories with an count of sub categories.
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return QueryBuilder
     */
    public function getBackendListQuery(array $filterBy = [], array $orderBy = [], $limit = null, $offset = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('c');
        $builder->select([
            'c.id as id',
            'c.active as active',
            'c.name as name',
            'c.position as position',
            'c.parentId as parentId',
        ]);

        $builder = $this->addChildrenCountSelect($builder);
        if (!empty($filterBy)) {
            $builder->addFilter($filterBy);
        }

        $builder->addOrderBy('c.parentId');
        $builder->addOrderBy('c.position');
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $categoryId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBackendDetailQuery($categoryId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
                'category',
                'attribute',
                'emotions',
                'customerGroups',
                'media',
            ])
            ->from($this->getEntityName(), 'category')
            ->leftJoin('category.attribute', 'attribute')
            ->leftJoin('category.emotions', 'emotions')
            ->leftJoin('category.media', 'media')
            ->leftJoin('category.customerGroups', 'customerGroups')
            ->where('category.id = ?1')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter(1, $categoryId);

        return $builder;
    }

    /**
     * Helper method to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $selectOnlyActive
     *
     * @return QueryBuilder
     */
    public function getListQueryBuilder(array $filterBy, array $orderBy = [], $limit = null, $offset = null, $selectOnlyActive = true)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('c');
        $builder->select([
            'c.id as id',
            'c.active as active',
            'c.name as name',
            'c.position as position',
            'c.parentId as parentId',
            'c.mediaId as mediaId',
        ]);
        $builder = $this->addChildrenCountSelect($builder);
        $builder = $this->addArticleCountSelect($builder);

        if (!empty($filterBy)) {
            $builder->addFilter($filterBy);
        }

        $builder->addOrderBy('c.parentId');
        $builder->addOrderBy('c.position');
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select the category detail information based on the category id
     * Used for detail information in the backend module.
     *
     * @param int $categoryId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($categoryId)
    {
        $builder = $this->getDetailQueryBuilder($categoryId);

        return $builder->getQuery();
    }

    /**
     * Returns the \Doctrine\ORM\Query to select the category detail information based on the category id
     * Used for detail information in the api resource.
     *
     * @param int $categoryId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQueryWithoutArticles($categoryId)
    {
        $builder = $this->getDetailQueryBuilderWithoutArticles($categoryId);
        $builder = $this->addChildrenCountSelect($builder);
        $builder = $this->addArticleCountSelect($builder);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $categoryId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilder($categoryId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'category',
            'PARTIAL articles.{id, name}',
            'PARTIAL mainDetail.{id,number}',
            'PARTIAL supplier.{id,name}',
            'attribute',
            'emotions', 'customerGroups', 'media',
        ])
            ->from($this->getEntityName(), 'category')
            ->leftJoin('category.articles', 'articles')
            ->leftJoin('articles.mainDetail', 'mainDetail')
            ->leftJoin('articles.supplier', 'supplier')
            ->leftJoin('category.attribute', 'attribute')
            ->leftJoin('category.emotions', 'emotions')
            ->leftJoin('category.media', 'media')
            ->leftJoin('category.customerGroups', 'customerGroups')
            ->where('category.id = ?1')
            ->setParameter(1, $categoryId);

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getDetailWithoutArticlesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $categoryId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilderWithoutArticles($categoryId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'c',
            'attribute',
            'emotions',
            'customerGroups',
            'media',
            'manualSorting',
        ])
            ->from($this->getEntityName(), 'c')
            ->leftJoin('c.attribute', 'attribute')
            ->leftJoin('c.emotions', 'emotions')
            ->leftJoin('c.media', 'media')
            ->leftJoin('c.customerGroups', 'customerGroups')
            ->leftJoin('c.manualSorting', 'manualSorting')
            ->where('c.id = ?1')
            ->setParameter(1, $categoryId);

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all active category by parent
     *
     * @param int      $parentId
     * @param int|null $customerGroupId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getActiveByParentIdQuery($parentId, $customerGroupId = null)
    {
        $builder = $this->getActiveQueryBuilder($customerGroupId)
            ->andWhere('c.parentId = :parentId')
            ->setParameter('parentId', $parentId);

        return $builder->getQuery();
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all assigned categories by the articleId
     *
     * @param int      $articleId
     * @param int|null $parentId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getActiveByArticleIdQuery($articleId, $parentId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder = $builder->from($this->getEntityName(), 'c')
            ->select(['c'])
            ->where('c.active=1')
            ->join('c.articles', 'a', Expr\Join::WITH, 'a.id= ?0')
            ->setParameter(0, $articleId)
            ->addOrderBy('c.position');

        if ($parentId !== null) {
            $builder->andWhere('c.parentId = :parentId')
                    ->setParameter('parentId', $parentId);
        }

        return $builder->getQuery();
    }

    /**
     * Returns the \Doctrine\ORM\Query to select the category information by category id
     *
     * @param int      $id              The id of the category
     * @param int|null $customerGroupId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getActiveByIdQuery($id, $customerGroupId = null)
    {
        $builder = $this->getActiveQueryBuilder($customerGroupId)
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', $id);

        return $builder->getQuery();
    }

    /**
     * Returns a tree structure result of all active category children of the passed category id.
     *
     * If the customer group id parameter is passed the function returns only this child categories
     * which are allowed to displayed for the passed customer group id.
     *
     * The depth parameter can be used to shrink the sql result. If the parameters is set to false,
     * all sub categories returned.
     *
     * @param int      $id
     * @param int|null $customerGroupId
     * @param int|null $depth
     * @param int|null $shopId
     *
     * @return array
     */
    public function getActiveChildrenTree($id, $customerGroupId = null, $depth = null, $shopId = null)
    {
        $builder = $this->getActiveQueryBuilder($customerGroupId, $shopId);
        $builder->andWhere('c.parentId = :parent')
            ->setParameter('parent', $id);

        $query = $builder->getQuery();
        $children = $query->getArrayResult();
        $categories = [];
        --$depth;

        foreach ($children as &$child) {
            $category = $child['category'];
            $category['childrenCount'] = $child['childrenCount'];
            $category['articleCount'] = $child['articleCount'];

            // Check if no depth passed or the current depth is lower than the passed depth
            if ($depth === null || $depth > 0) {
                $category['sub'] = $this->getActiveChildrenTree($child['category']['id'], $customerGroupId, $depth, $shopId);
            }
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Returns a flat list of all active category children of the passed category id.
     * If the customer group id parameter is passed the function returns only this child categories
     * which are allowed to displayed for the passed customer group id.
     * The depth parameter can be used to shrink the sql result. If the parameters is set to false,
     * all sub categories returned.
     *
     * @param int      $id
     * @param int|null $customerGroupId
     * @param int|null $depth
     * @param int|null $shopId
     *
     * @return array
     */
    public function getActiveChildrenList($id, $customerGroupId = null, $depth = null, $shopId = null)
    {
        $builder = $this->getActiveQueryBuilder($customerGroupId, $shopId);
        $builder->andWhere('c.parentId = :parent')
            ->setParameter('parent', $id);

        $children = $builder->getQuery()->getArrayResult();
        $categories = [];
        --$depth;

        foreach ($children as &$child) {
            $category = $child['category'];
            $category['childrenCount'] = $child['childrenCount'];
            $category['articleCount'] = $child['articleCount'];

            $categories[] = $category;
            // Check if no depth passed or the current depth is lower than the passed depth
            if ($depth === null || $depth > 0) {
                $subCategories = $this->getActiveChildrenList($child['category']['id'], $customerGroupId, $depth);
                $categories = array_merge($categories, $subCategories);
            }
        }

        return $categories;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getChildrenCountList($id)
    {
        $builder = $this->getCategoriesByParentBuilder($id);
        $builder->select('COUNT(categories) as categoriesCount');

        return $builder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getFullChildrenList($id)
    {
        return $this->getCategoriesByParentBuilder($id)->getQuery()->getArrayResult();
    }

    /**
     * Returns first active articleId for given category
     *
     * @param Category|int $category
     *
     * @return array|null
     */
    public function getActiveArticleIdByCategoryId($category)
    {
        if ($category !== null && !$category instanceof Category) {
            $category = $this->find($category);
        }
        if ($category === null) {
            return null;
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->from($this->getEntityName(), 'c');
        $builder->select('MIN(a.id)')
            ->innerJoin('c.allArticles', 'a', Expr\Join::WITH, 'a.active=1')
            ->where('c.active=1')
            ->andWhere('c.id = :id');

        $builder->setParameter('id', $category->getId());

        return $builder->getQuery()->getResult(
            \Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR
        );
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all blog categories for example for the blog backend list
     *
     * @param int      $parentId
     * @param int|null $offset
     * @param int|null $limit
     *
     * @internal param $filterBy
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBlogCategoriesByParentQuery($parentId, $offset = null, $limit = null)
    {
        $builder = $this->getBlogCategoriesByParentBuilder($parentId, $offset, $limit);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getBlogCategoriesByParentQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int      $parentId
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return QueryBuilder
     */
    public function getBlogCategoriesByParentBuilder($parentId, $offset = null, $limit = null)
    {
        return $this->getCategoriesByParentBuilder($parentId, $offset, $limit)
            ->andWhere('categories.blog = 1');
    }

    /**
     * Helper method to create the query builder for the "getBlogCategoriesByParentQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int      $parentId
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCategoriesByParentBuilder($parentId, $offset = null, $limit = null)
    {
        $builder = $this->createQueryBuilder('categories')
            ->select(['categories']);

        if ($parentId > 1) {
            $builder->andWhere('categories.path LIKE :path')
                ->setParameter('path', '%|' . $parentId . '|%');
        }

        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all blog categories and parent categories
     * with blog child elements for example for the blog backend tree
     *
     * @param array $filterBy
     *
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
     * @return QueryBuilder
     */
    public function getBlogCategoryTreeListBuilder()
    {
        $subQuery = $this->getEntityManager()->createQueryBuilder();
        $subQuery->from(\Shopware\Models\Category\Category::class, 'c2')
            ->select('COUNT(c2.id)')
            ->where('c2.parentId = c.id')
            ->andWhere('c2.blog = 1');

        $builder = $this->createQueryBuilder('c');
        $builder->select([
            'c.id as id',
            'c.name as name',
            'c.position as position',
            'c.blog as blog',
        ]);
        $builder->having('childrenCount > 0 OR blog = 1');
        $builder->addSelect('(' . $subQuery->getDQL() . ') as childrenCount');

        return $builder;
    }

    /**
     * Helper function to select all path elements for the passed category.
     *
     * @param Category $category
     * @param string   $field
     * @param string   $separator
     *
     * @return array|string|null
     */
    protected function getCategoryPathBefore($category, $field, $separator)
    {
        if (!$category instanceof Category) {
            return '';
        }
        $parent = $category->getParent();

        if (!$parent instanceof Category || $parent->getId() === 1) {
            return null;
        }
        $parentValue = $this->getCategoryPathBefore($parent, $field, $separator);
        $selfValue = $this->getCategoryPathQuery($parent->getId(), $field);

        if ($parentValue) {
            $parentValue[$parent->getId()] = $selfValue;

            return $parentValue;
        }

        return [$parent->getId() => $selfValue];
    }

    /**
     * @param int          $id
     * @param string|array $fields
     */
    protected function getCategoryPathQuery($id, $fields)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $selection = [];
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $selection[] = 'category.' . $field;
            }
        } else {
            $selection[] = 'category.' . $fields;
        }

        $builder->select($selection)
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter('id', (int) $id);

        $result = $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY
        );

        if (Shopware()->Container()->initialized('shop')) {
            $translationComponent = Shopware()->Container()->get('translation');
            $shopId = Shopware()->Shop()->getId();
            $fallbackId = null;
            if (Shopware()->Shop()->getFallback()) {
                $fallbackId = Shopware()->Shop()->getFallback()->getId();
            }

            $translations = $translationComponent->readWithFallback($shopId, $fallbackId, 'category', $id);
            if (isset($translations) && isset($translations['description']) && (isset($result['name']) || $fields === 'name')) {
                $result['name'] = $translations['description'];
            }
        }

        if (is_array($fields)) {
            return $result;
        }

        return $result[$fields];
    }

    /**
     * Helper function to create the query builder for the
     * "getActiveByParentIdQuery, getActiveChildrenByIdQuery, getActiveByIdQuery" functions.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int|null $customerGroupId
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getActiveQueryBuilder($customerGroupId = null, $shopId = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->from($this->getEntityName(), 'c')
                ->select([
                    'c as category',
                    'attribute',
                    'media',
                ])
                ->leftJoin('c.media', 'media')
                ->leftJoin('c.attribute', 'attribute')
                ->andWhere('c.active = 1');

        if ($shopId) {
            $builder
                ->andWhere('c.shops IS NULL OR c.shops LIKE :shopLike')
                ->setParameter(':shopLike', '%|' . $shopId . '|%');
        }

        $builder = $this->addArticleCountSelect($builder, true);
        $builder = $this->addChildrenCountSelect($builder);

        if (isset($customerGroupId)) {
            $builder->leftJoin('c.customerGroups', 'cg', 'with', 'cg.id = :cgId')
                    ->setParameter('cgId', $customerGroupId)
                    ->andHaving('COUNT(cg.id) = 0');
        }

        //to prevent a temporary table and file sort we have to set the same sort and group by condition
        $builder->groupBy('c.parentId')
            ->addGroupBy('c.position')
            ->addGroupBy('c.id')
            ->orderBy('c.parentId', 'ASC')
            ->addOrderBy('c.position', 'ASC')
            ->addOrderBy('c.id', 'ASC');

        return $builder;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $builder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function addChildrenCountSelect($builder)
    {
        $subQuery = $this->getEntityManager()->createQueryBuilder();
        $subQuery->from(\Shopware\Models\Category\Category::class, 'c2')
                ->select('COUNT(c2.id)')
                ->where('c2.parentId = c.id');

        $dql = $subQuery->getDQL();
        $builder->addSelect('(' . $dql . ') as childrenCount');

        return $builder;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $builder
     * @param bool                       $onlyActive
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function addArticleCountSelect($builder, $onlyActive = false)
    {
        $subQuery = $this->getEntityManager()->createQueryBuilder();
        $subQuery->from(\Shopware\Models\Category\Category::class, 'c3')
            ->select('COUNT(articles)')
            ->where('c3.id = c.id');

        if ($onlyActive) {
            $subQuery->leftJoin('c3.allArticles', 'articles', 'WITH', 'articles.active = true');
        } else {
            $subQuery->leftJoin('c3.allArticles', 'articles');
        }
        $subQuery->addGroupBy('c3.id');

        $dql = $subQuery->getDQL();
        $builder->addSelect('(' . $dql . ') as articleCount');

        return $builder;
    }
}
