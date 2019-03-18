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

namespace Shopware\Components\HttpCache\UrlProvider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;

class ProductProvider implements UrlProviderInterface
{
    const NAME = 'product';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(Connection $connection, RouterInterface $router)
    {
        $this->connection = $connection;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $context, $limit = null, $offset = null)
    {
        $qb = $this->getBaseQuery()
            ->addSelect(['DISTINCT details.articleID'])
            ->andWhere('details.kind = 1')
            ->orderBy('details.articleID', 'ASC')
            ->setParameter(':shop', $context->getShopId());

        if ($limit !== null && $offset !== null) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $result = $qb->execute()->fetchAll();

        if (!count($result)) {
            return [];
        }

        return $this->router->generateList(
            array_map(
                function ($product) {
                    return ['controller' => 'detail', 'action' => 'index', 'sArticle' => $product['articleID']];
                },
                $result
            ),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(Context $context)
    {
        return (int) $this->getBaseQuery()
            ->addSelect(['COUNT(DISTINCT details.articleID)'])
            ->setParameter(':shop', $context->getShopId())
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        return $this->connection->createQueryBuilder()
            ->from('s_articles_categories_ro', 'art_cat')
            ->join('art_cat', 's_categories', 'cat', 'art_cat.categoryID = cat.id')
            ->leftJoin('art_cat', 's_categories', 'parent_cat', 'art_cat.categoryID = parent_cat.id')
            ->join('art_cat', 's_articles_details', 'details', 'art_cat.articleID = details.articleID')
            ->join('parent_cat', 's_core_shops', 'shop', 'cat.path LIKE CONCAT("%|", shop.category_id, "|%")')
            ->where('shop.id = :shop')
            ->andWhere('cat.active = 1')
            ->andWhere('parent_cat.active = 1')
            ->andWhere('details.active = 1');
    }
}
