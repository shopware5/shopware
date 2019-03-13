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

class CategoryProvider implements UrlProviderInterface
{
    const NAME = 'category';

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
            ->addSelect(['cat.id', 'cat.blog'])
            ->orderBy('ISNULL(cat.path)', 'DESC')
            ->addOrderBy('id', 'ASC')
            ->setParameter(':shop', $context->getShopId())
            ->andWhere('cat.shops IS NULL OR cat.shops LIKE :shopLike')
            ->setParameter(':shopLike', '%|' . $context->getShopId() . '|%');

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
                function ($category) use ($context) {
                    if (((int) $category['id']) === $context->getShopId()) {
                        return ['sViewport' => 'index'];
                    }
                    $viewport = (((int) $category['blog']) === 1) ? 'blog' : 'cat';

                    return ['sViewport' => $viewport, 'sCategory' => $category['id']];
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
            ->addSelect(['COUNT(cat.id)'])
            ->setParameter(':shop', $context->getShopId())
            ->andWhere('cat.shops IS NULL OR cat.shops LIKE :shopLike')
            ->setParameter(':shopLike', '%|' . $context->getShopId() . '|%')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        return $this->connection->createQueryBuilder()
            ->from('s_categories', 'cat')
            ->join(
                'cat',
                's_core_shops',
                'shop',
                'cat.path LIKE CONCAT("%|",shop.category_id,"|%")
                OR cat.id = shop.category_id'
            )
            ->leftJoin(
                'cat',
                's_categories_avoid_customergroups',
                'avoid',
                'avoid.categoryID = cat.id AND avoid.customergroupID = shop.customer_group_id'
            )
            ->where('shop.id = :shop')
            ->andWhere('cat.active = 1')
            ->andWhere('avoid.customergroupID IS NULL');
    }
}
