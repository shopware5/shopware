<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\BlogGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\BlogHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class BlogGateway implements BlogGatewayInterface
{
    private BlogHydrator $blogHydrator;

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
     */
    private FieldHelper $fieldHelper;

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        BlogHydrator $blogHydrator
    ) {
        $this->connection = $connection;
        $this->blogHydrator = $blogHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $blogIds, ShopContextInterface $context)
    {
        $data = $this->getQuery($blogIds, $context)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);

        $articles = $this->getProductsQuery(array_column($data, '__blog_id'))
            ->execute()
            ->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $medias = $this->getMediaQuery(array_column($data, '__blog_id'))
            ->execute()
            ->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $blogs = [];
        foreach ($data as $row) {
            $id = (int) $row['__blog_id'];

            $blog = $this->blogHydrator->hydrate($row);

            if (\array_key_exists($id, $articles)) {
                $blog->setProductNumbers($articles[$id]);
            }

            if (\array_key_exists($id, $medias)) {
                $blog->setMediaIds($medias[$id]);
            }

            $blogs[$id] = $blog;
        }

        return $blogs;
    }

    /**
     * @param int[] $ids
     */
    private function getQuery(array $ids, ShopContextInterface $context): QueryBuilder
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

        $this->fieldHelper->addBlogTranslation($query, $context);

        $query->setParameter(':blogIds', $ids, Connection::PARAM_INT_ARRAY);

        return $query;
    }

    /**
     * @param int[] $blogIds
     */
    private function getProductsQuery(array $blogIds): QueryBuilder
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
     */
    private function getMediaQuery(array $blogIds): QueryBuilder
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
