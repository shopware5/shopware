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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Struct\Blog\Blog;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class BlogGateway
{
    /**
     * @var Hydrator\BlogHydrator
     */
    private $blogHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection            $connection
     * @param FieldHelper           $fieldHelper
     * @param Hydrator\BlogHydrator $blogHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\BlogHydrator $blogHydrator
    ) {
        $this->connection = $connection;
        $this->blogHydrator = $blogHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Blog\Blog requires the following data:
     * - Blog base data
     * - Core attribute
     * - Assigned tags
     * - Assigned products
     * - Assigned comments
     * - Assigned media objects
     * - Core attribute of the media object
     *
     * @param array $blogIds
     * @return Blog[]
     */
    public function getList(array $blogIds)
    {
        $data = $this->getQuery($blogIds)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $articles = $this->getArticlesQuery(array_column($data, '__blog_id'))
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);

        $medias = $this->getMediaQuery(array_column($data, '__blog_id'))
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);

        $blogs = [];
        foreach ($data as $row) {
            $id = $row['__blog_id'];

            $blog = $this->blogHydrator->hydrate($row);

            if (array_key_exists($id, $articles)) {
                $blog->setProductNumbers($articles[$id]);
            }

            if (array_key_exists($id, $medias)) {
                $blog->setMediaIds($medias[$id]);
            }

            $blogs[$id] = $blog;
        }

        return $blogs;
    }

    /**
     * @param int[] $ids
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery(array $ids)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getBlogFields())
            ->addSelect('GROUP_CONCAT(blogTags.name) as __blog_tags');

        $query->from('s_blog', 'blog')
            ->leftJoin('blog', 's_blog_attributes', 'blogAttribute', 'blogAttribute.blog_id = blog.id')
            ->leftJoin('blog', 's_blog_tags', 'blogTags', 'blogTags.blog_id = blog.id');

        $query->where('blog.id IN (:blogIds)')
            ->andWhere('blog.active = 1');

        $query->groupBy('blog.id');

        $query->setParameter(':blogIds', $ids, Connection::PARAM_INT_ARRAY);

        return $query;
    }

    /**
     * @param int[] $blogIds
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getArticlesQuery(array $blogIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['blog_id', 'ordernumber'])
            ->from('s_blog_assigned_articles', 'blogArticles')
            ->innerJoin('blogArticles', 's_articles_details', 'details', 'details.id = blogArticles.article_id')
            ->where('blogArticles.blog_id IN (:blogIds)')
            ->setParameter(':blogIds', $blogIds, Connection::PARAM_INT_ARRAY);

        return $query;
    }

    /**
     * @param int[] $blogIds
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getMediaQuery(array $blogIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['blog_id', 'media_id'])
            ->from('s_blog_media', 'blogMedia')
            ->where('blogMedia.blog_id IN (:blogIds)')
            ->orderBy('preview', 'desc')
            ->setParameter(':blogIds', $blogIds, Connection::PARAM_INT_ARRAY);

        return $query;
    }
}
