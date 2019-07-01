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

namespace Shopware\Models\Blog;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * Repository for the Blog model (Shopware\Models\Blog\Blog).
 * <br>
 * The Blog model repository is responsible to load all Blog data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog articles for the frontend list
     *
     * @param int[]    $blogCategoryIds
     * @param int|null $offset
     * @param int|null $limit
     * @param int|null $shopId
     *
     * @internal param $blogCategory
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($blogCategoryIds, $offset = null, $limit = null, array $filter = null, $shopId = null)
    {
        $builder = $this->getListQueryBuilder($blogCategoryIds, $filter, $shopId);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[]    $blogCategoryIds
     * @param array    $filter
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($blogCategoryIds, $filter, $shopId = null)
    {
        $builder = $this->createQueryBuilder('blog');
        $builder->select([
            'blog',
            'author',
            'media',
            'mappingMedia',
            'tags',
            'attribute',
            'comments',
        ])
        ->leftJoin('blog.tags', 'tags')
        ->leftJoin('blog.author', 'author')
        ->leftJoin('blog.media', 'mappingMedia', Join::WITH, 'mappingMedia.preview = 1')
        ->leftJoin('mappingMedia.media', 'media')
        ->leftJoin('blog.attribute', 'attribute')
        ->where('blog.active = 1')
        ->andWhere('blog.displayDate < :now')
        ->setParameter('now', new \DateTime())
        ->orderBy('blog.displayDate', 'DESC');

        if ($shopId !== null) {
            $builder->andWhere('(blog.shopIds LIKE :shopId OR blog.shopIds IS NULL)')
                ->setParameter('shopId', '%|' . $shopId . '|%');
        }

        if (!empty($blogCategoryIds)) {
            $builder->andWhere('blog.categoryId IN (:categoryIds)')
                ->setParameter('categoryIds', $blogCategoryIds, Connection::PARAM_INT_ARRAY);
        }

        if ($shopId && Shopware()->Config()->get('displayOnlySubShopBlogComments')) {
            $builder
                ->leftJoin('blog.comments', 'comments', Join::WITH, 'comments.active = 1 AND (comments.shopId IS NULL OR comments.shopId = :shopId)')
                ->setParameter('shopId', $shopId);
        } else {
            $builder->leftJoin('blog.comments', 'comments', Join::WITH, 'comments.active = 1');
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog articles for the backend list
     *
     * @param int      $blogId
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAverageVoteQuery($blogId, $shopId = null)
    {
        return $this->getAverageVoteQueryBuilder($blogId, $shopId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAverageVoteQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int      $blogId
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAverageVoteQueryBuilder($blogId, $shopId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'AVG(comment.points) as avgVote',
        ])
        ->from(Comment::class, 'comment')
        ->where('comment.active = 1')
        ->andWhere('comment.blogId = :blogId')
        ->setParameter('blogId', $blogId);

        if ($shopId && Shopware()->Config()->get('displayOnlySubShopBlogComments')) {
            $builder
                ->andWhere('comment.shopId IS NULL OR comment.shopId = :shopId')
                ->setParameter('shopId', $shopId);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog articles for the backend list
     *
     * @param int $blogId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getTagsByBlogId($blogId)
    {
        $builder = $this->getTagsByBlogIdBuilder($blogId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTagsByBlogId" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $blogId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTagsByBlogIdBuilder($blogId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'tags',
        ])
        ->from(Tag::class, 'tags')
        ->andWhere('tags.blogId = :blogId')
        ->setParameter('blogId', $blogId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog date filter
     *
     * @param int[]    $categoryIds
     * @param array    $filter
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDisplayDateFilterQuery($categoryIds, $filter, $shopId = null)
    {
        $builder = $this->getDisplayDateFilterQueryBuilder($categoryIds, $filter, $shopId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDisplayDateFilterQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[]    $categoryIds
     * @param array    $filter
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDisplayDateFilterQueryBuilder($categoryIds, $filter, $shopId = null)
    {
        $builder = $this->getFilterQueryBuilder($categoryIds, $filter, $shopId);
        $builder->select([
            'DATE_FORMAT(blog.displayDate,\'%Y-%m\') as dateFormatDate',
            'COUNT(DISTINCT blog.id) as dateCount',
        ]);
        $builder->groupBy('dateFormatDate');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog author filter
     *
     * @param int[]    $categoryIds
     * @param array    $filter
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAuthorFilterQuery($categoryIds, $filter, $shopId = null)
    {
        $builder = $this->getAuthorFilterQueryBuilder($categoryIds, $filter, $shopId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAuthorFilterQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[]    $categoryIds
     * @param array    $filter
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAuthorFilterQueryBuilder($categoryIds, $filter, $shopId = null)
    {
        $builder = $this->getFilterQueryBuilder($categoryIds, $filter, $shopId);
        $builder->select([
            'author.name',
            'Count(DISTINCT blog.id) as authorCount',
        ])
        ->andWhere('author.name != \'\'')
        ->groupBy('author.name');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog tags filter
     *
     * @param int[]    $categoryIds
     * @param array    $filter
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getTagsFilterQuery($categoryIds, $filter, $shopId = null)
    {
        $builder = $this->getTagsFilterQueryBuilder($categoryIds, $filter, $shopId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTagsFilterQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[]    $categoryIds
     * @param array    $filter
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTagsFilterQueryBuilder($categoryIds, $filter, $shopId = null)
    {
        $builder = $this->getFilterQueryBuilder($categoryIds, $filter, $shopId);
        $builder->select([
            'tags.name',
            'Count(DISTINCT blog.id) as tagsCount',
        ])
        ->andWhere('tags.name != \'\'')
        ->groupBy('tags.name');

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getDisplayDateFilterQueryBuilder, getAuthorFilterQueryBuilder, getTagsFilterQueryBuilder" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[]    $categoryIds
     * @param array    $filter
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilterQueryBuilder($categoryIds, $filter, $shopId = null)
    {
        $builder = $this->createQueryBuilder('blog');
        $builder->leftJoin('blog.tags', 'tags')
            ->leftJoin('blog.author', 'author')
            ->where('blog.active = 1')
            ->andWhere('blog.displayDate < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('blog.displayDate', 'DESC');

        if ($shopId !== null) {
            $builder->andWhere('(blog.shopIds LIKE :shopId OR blog.shopIds IS NULL)')
                ->setParameter('shopId', '%|' . $shopId . '|%');
        }

        if (!empty($categoryIds)) {
            $builder->andWhere('blog.categoryId IN (:categoryIds)')
                ->setParameter('categoryIds', $categoryIds, Connection::PARAM_INT_ARRAY);
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog author filter
     *
     * @param int[]      $blogCategoryIds
     * @param array|null $order
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBackendListQuery($blogCategoryIds, array $filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getBackendListQueryBuilder($blogCategoryIds, $filter, $order);
        if ($offset !== null) {
            $builder->setFirstResult($offset);
        }
        if ($limit !== null) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getBackendListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[] $blogCategoryIds
     * @param array $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBackendListQueryBuilder($blogCategoryIds, array $filter, $order)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'blog.id',
                'blog.title as title',
                'blog.views as views',
                'blog.displayDate as displayDate',
                'blog.active as active',
                'COUNT(comments) as numberOfComments',
            ])
            ->from($this->getEntityName(), 'blog')
            ->leftJoin('blog.comments', 'comments', Join::WITH, 'comments.active != 1')
            ->groupBy('blog.id');

        if (!empty($blogCategoryIds)) {
            $builder->where('blog.categoryId IN (:blogCategoryIds)')
                ->setParameter('blogCategoryIds', $blogCategoryIds, Connection::PARAM_INT_ARRAY);
        }

        if (!empty($filter) && $filter[0]['property'] === 'filter' && !empty($filter[0]['value'])) {
            $builder->andWhere('blog.title LIKE ?1')
                    ->orWhere('blog.views LIKE ?1')
                    ->setParameter(1, '%' . $filter[0]['value'] . '%');
        }

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog article for the detail page
     *
     * @param int      $blogArticleId
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($blogArticleId, $shopId = null)
    {
        $builder = $this->getDetailQueryBuilder($blogArticleId, $shopId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int      $blogArticleId
     * @param int|null $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilder($blogArticleId, $shopId = null)
    {
        $builder = $this->createQueryBuilder('blog');
        $builder->select(['blog', 'tags', 'author', 'media', 'mappingMedia', 'assignedArticles', 'assignedArticlesDetail', 'attribute', 'comments'])
                ->leftJoin('blog.tags', 'tags')
                ->leftJoin('blog.author', 'author')
                ->leftJoin('blog.assignedArticles', 'assignedArticles')
                ->leftJoin('assignedArticles.mainDetail', 'assignedArticlesDetail')
                ->leftJoin('blog.media', 'mappingMedia')
                ->leftJoin('blog.attribute', 'attribute')
                ->leftJoin('mappingMedia.media', 'media')
                ->where('blog.id = :blogArticleId')
                ->addOrderBy('comments.creationDate', 'ASC')
                ->setParameter('blogArticleId', $blogArticleId);

        if ($shopId !== null) {
            $builder->andWhere('(blog.shopIds LIKE :shopId OR blog.shopIds IS NULL)')
                ->setParameter('shopId', '%|' . $shopId . '|%');
        }

        if ($shopId && Shopware()->Config()->get('displayOnlySubShopBlogComments')) {
            $builder
                ->leftJoin('blog.comments', 'comments', Join::WITH, 'comments.active = 1 AND (comments.shopId IS NULL OR comments.shopId = :shopId)')
                ->setParameter('shopId', $shopId);
        } else {
            $builder->leftJoin('blog.comments', 'comments', Join::WITH, 'comments.active = 1');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog article for the detail page
     *
     * @param array $filter
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBackendDetailQuery($filter)
    {
        $builder = $this->getBackedDetailQueryBuilder($filter);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getBackendDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBackedDetailQueryBuilder($filter)
    {
        $builder = $this->createQueryBuilder('blog');
        $builder->select(['blog', 'tags', 'media', 'mappingMedia', 'assignedArticles', 'assignedArticlesDetail'])
                ->leftJoin('blog.tags', 'tags')
                ->leftJoin('blog.assignedArticles', 'assignedArticles')
                ->leftJoin('assignedArticles.mainDetail', 'assignedArticlesDetail')
                ->leftJoin('blog.media', 'mappingMedia')
                ->leftJoin('mappingMedia.media', 'media')
                ->addFilter($filter);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog article comments
     *
     * @param int   $blogId
     * @param array $filter
     * @param array $order
     * @param int   $offset
     * @param int   $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBlogCommentsById($blogId, $filter, $order, $offset, $limit)
    {
        $builder = $this->getBlogCommentsByIdBuilder($blogId, $filter, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getBlogCommentsById" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int   $blogId
     * @param array $filter
     * @param array $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBlogCommentsByIdBuilder($blogId, $filter, $order)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'comment.id',
                'comment.active as active',
                'comment.creationDate as creationDate',
                'comment.email as eMail',
                'comment.name as name',
                'comment.points as points',
                'comment.headline as headline',
                'comment.comment as content',
                'comment.shopId',
            ])
                ->from(Comment::class, 'comment')
                ->where('comment.blogId = ?1')
                ->setParameter(1, $blogId);

        if (!empty($filter) && $filter[0]['property'] == 'filter' && !empty($filter[0]['value'])) {
            $builder->andWhere('comment.headline LIKE ?2')
                    ->orWhere('comment.name LIKE ?2')
                    ->setParameter(2, '%' . $filter[0]['value'] . '%');
        }

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the all blog tags
     *
     * @param int $blogId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBlogTagsById($blogId)
    {
        $builder = $this->getBlogTagsByIdBuilder($blogId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getBlogTagsById" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $blogId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBlogTagsByIdBuilder($blogId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['tags'])
                ->from(Tag::class, 'tags')
                ->where('tags.blogId = ?1')
            ->setParameter(1, $blogId);

        return $builder;
    }
}
