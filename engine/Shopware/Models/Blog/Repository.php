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

use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelRepository;

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
     * @param $blogCategoryIds
     * @param null  $offset
     * @param null  $limit
     * @param array $filter
     *
     * @internal param $blogCategory
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($blogCategoryIds, $offset = null, $limit = null, array $filter = null)
    {
        $builder = $this->getListQueryBuilder($blogCategoryIds, $filter);
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
     * @param $blogCategoryIds
     * @param array $filter
     *
     * @internal param $blogCategoryIds
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($blogCategoryIds, $filter)
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
        ->leftJoin('blog.media', 'mappingMedia', \Doctrine\ORM\Query\Expr\Join::WITH, 'mappingMedia.preview = 1')
        ->leftJoin('mappingMedia.media', 'media')
        ->leftJoin('blog.attribute', 'attribute')
        ->leftJoin('blog.comments', 'comments', \Doctrine\ORM\Query\Expr\Join::WITH, 'comments.active = 1')
        ->where('blog.active = 1')
        ->andWhere('blog.displayDate < :now')
        ->setParameter('now', new \DateTime())
        ->orderBy('blog.displayDate', 'DESC');

        if (!empty($blogCategoryIds)) {
            $builder->andWhere($builder->expr()->in('blog.categoryId', $blogCategoryIds));
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog articles for the backend list
     *
     * @param $blogId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAverageVoteQuery($blogId)
    {
        $builder = $this->getAverageVoteQueryBuilder($blogId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAverageVoteQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $blogId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAverageVoteQueryBuilder($blogId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'AVG(comment.points) as avgVote',
        ])
        ->from('Shopware\Models\Blog\Comment', 'comment')
        ->where('comment.active = 1')
        ->andWhere('comment.blogId = :blogId')
        ->setParameter('blogId', $blogId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog articles for the backend list
     *
     * @param $blogId
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
     * @param $blogId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTagsByBlogIdBuilder($blogId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'tags',
        ])
        ->from('Shopware\Models\Blog\Tag', 'tags')
        ->andWhere('tags.blogId = :blogId')
        ->setParameter('blogId', $blogId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog date filter
     *
     * @param $categoryIds
     * @param $filter
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDisplayDateFilterQuery($categoryIds, $filter)
    {
        $builder = $this->getDisplayDateFilterQueryBuilder($categoryIds, $filter);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDisplayDateFilterQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $categoryIds
     * @param $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDisplayDateFilterQueryBuilder($categoryIds, $filter)
    {
        $builder = $this->getFilterQueryBuilder($categoryIds, $filter);
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
     * @param $categoryIds
     * @param $filter
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAuthorFilterQuery($categoryIds, $filter)
    {
        $builder = $this->getAuthorFilterQueryBuilder($categoryIds, $filter);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAuthorFilterQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $categoryIds
     * @param $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAuthorFilterQueryBuilder($categoryIds, $filter)
    {
        $builder = $this->getFilterQueryBuilder($categoryIds, $filter);
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
     * @param $categoryIds
     * @param $filter
     *
     * @return \Doctrine\ORM\Query
     */
    public function getTagsFilterQuery($categoryIds, $filter)
    {
        $builder = $this->getTagsFilterQueryBuilder($categoryIds, $filter);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTagsFilterQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $categoryIds
     * @param $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTagsFilterQueryBuilder($categoryIds, $filter)
    {
        $builder = $this->getFilterQueryBuilder($categoryIds, $filter);
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
     * @param $categoryIds
     * @param $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilterQueryBuilder($categoryIds, $filter)
    {
        $builder = $this->createQueryBuilder('blog');
        $builder->leftJoin('blog.tags', 'tags')
                ->leftJoin('blog.author', 'author')
                ->where('blog.active = 1')
                ->andWhere('blog.displayDate < :now')
                ->setParameter('now', new \DateTime())
                ->orderBy('blog.displayDate', 'DESC');

        if (!empty($categoryIds)) {
            $builder->andWhere($builder->expr()->in('blog.categoryId', $categoryIds));
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog author filter
     *
     * @param $blogCategoryIds
     * @param array|null $filter
     * @param null       $order
     * @param null       $offset
     * @param null       $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBackendListQuery($blogCategoryIds, array $filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getBackendListQueryBuilder($blogCategoryIds, $filter, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getBackendListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $blogCategoryIds
     * @param $filter
     * @param $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBackendListQueryBuilder($blogCategoryIds, array $filter, $order)
    {
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
            ->leftJoin('blog.comments', 'comments', \Doctrine\ORM\Query\Expr\Join::WITH, 'comments.active != 1')
            ->groupBy('blog.id');

        if (!empty($blogCategoryIds)) {
            $builder->where($builder->expr()->in('blog.categoryId', $blogCategoryIds));
        }

        if (!empty($filter) && $filter[0]['property'] == 'filter' && !empty($filter[0]['value'])) {
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
     * @param $blogArticleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($blogArticleId)
    {
        $builder = $this->getDetailQueryBuilder($blogArticleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $blogArticleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilder($blogArticleId)
    {
        $builder = $this->createQueryBuilder('blog');
        $builder->select(['blog', 'tags', 'author', 'media', 'mappingMedia', 'assignedArticles', 'assignedArticlesDetail', 'attribute', 'comments'])
                ->leftJoin('blog.tags', 'tags')
                ->leftJoin('blog.author', 'author')
                ->leftJoin('blog.assignedArticles', 'assignedArticles')
                ->leftJoin('assignedArticles.mainDetail', 'assignedArticlesDetail')
                ->leftJoin('blog.media', 'mappingMedia')
                ->leftJoin('blog.attribute', 'attribute')
                ->leftJoin('blog.comments', 'comments', \Doctrine\ORM\Query\Expr\Join::WITH, 'comments.active = 1')
                ->leftJoin('mappingMedia.media', 'media')
                ->where('blog.id = :blogArticleId')
                ->addOrderBy('comments.creationDate', 'ASC')
                ->setParameter('blogArticleId', $blogArticleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog article for the detail page
     *
     * @param $filter
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
     * @param $filter
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
     * @param $blogId
     * @param $filter
     * @param $order
     * @param $offset
     * @param $limit
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
     * @param $blogId
     * @param $filter
     * @param $order
     *
     * @internal param $offset
     * @internal param $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBlogCommentsByIdBuilder($blogId, $filter, $order)
    {
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
            ])
                ->from('Shopware\Models\Blog\Comment', 'comment')
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
     * @param $blogId
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
     * @param $blogId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBlogTagsByIdBuilder($blogId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['tags'])
                ->from('Shopware\Models\Blog\Tag', 'tags')
                ->where('tags.blogId = ?1')
            ->setParameter(1, $blogId);

        return $builder;
    }
}
