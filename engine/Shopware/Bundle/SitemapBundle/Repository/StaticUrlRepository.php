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

namespace Shopware\Bundle\SitemapBundle\Repository;

use Doctrine\DBAL\Connection;

class StaticUrlRepository implements StaticUrlRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSitesByShopId($shopId)
    {
        $keys = $this->getQueryBuilder($shopId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $sites = [];
        foreach ($keys as $key) {
            $current = $this->getDetailQueryBuilder($shopId, $key)
                ->execute()
                ->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($current as $item) {
                $sites[$item['id']] = $item;
            }
        }

        return array_values($sites);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder($shopId)
    {
        return $this->connection->createQueryBuilder()
            ->select('shopGroups.key')
            ->from('s_core_shop_pages', 'shopPages')
            ->innerJoin('shopPages', 's_cms_static_groups', 'shopGroups', 'shopGroups.id = shopPages.group_id')
            ->where('shopPages.shop_id = :shopId')
            ->setParameter('shopId', $shopId);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailQueryBuilder($shopId, $key)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->from('s_cms_static', 'sites')
            ->select('*')
            ->where('sites.active = 1')
            ->andWhere(
                $builder->expr()->orX(
                    $builder->expr()->eq('sites.grouping', ':g1'),   //  = bottom
                    $builder->expr()->like('sites.grouping', ':g2'), // like 'bottom|%
                    $builder->expr()->like('sites.grouping', ':g3'), // like '|bottom
                    $builder->expr()->like('sites.grouping', ':g4')  // like '|bottom|
                )
            )
            ->andWhere(
                $builder->expr()->orX(
                    $builder->expr()->like('sites.shop_ids', ':shopId'),
                    $builder->expr()->isNull('sites.shop_ids')
                )
            )
            ->setParameter('g1', $key)
            ->setParameter('g2', $key . '|%')
            ->setParameter('g3', '%|' . $key)
            ->setParameter('g4', '%|' . $key . '|%')
            ->setParameter('shopId', '%|' . $shopId . '|%');

        return $builder;
    }
}
