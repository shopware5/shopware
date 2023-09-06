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

namespace Shopware\Bundle\SitemapBundle\Provider;

use DateTime;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use PDO;
use Shopware\Bundle\SitemapBundle\Service\LinkFilter;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Site\Site;

class StaticUrlProvider implements UrlProviderInterface
{
    private RouterInterface $router;

    private ConnectionInterface $connection;

    private bool $allExported = false;

    public function __construct(RouterInterface $router, ConnectionInterface $connection)
    {
        $this->router = $router;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return [];
        }

        $shopId = $shopContext->getShop()->getId();

        $sites = $this->getSitesByShopId($shopId);
        foreach ($sites as $key => &$site) {
            $site['urlParams'] = [
                'sViewport' => 'custom',
                'sCustom' => $site['id'],
            ];

            if (!LinkFilter::filterLink($site['link'], $site['urlParams'])) {
                unset($sites[$key]);
                continue;
            }

            $site['changed'] = new DateTime($site['changed']);
        }
        unset($site);

        $sites = array_values($sites);

        $this->allExported = true;

        if (\count($sites) === 0) {
            return [];
        }

        $routes = $this->router->generateList(array_column($sites, 'urlParams'), $routingContext);

        $urls = [];

        for ($i = 0, $routeCount = \count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $sites[$i]['changed'], 'weekly', Site::class, $sites[$i]['id']);
        }

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->allExported = false;
    }

    /**
     * Helper function to read all static pages of a shop from the database
     */
    private function getSitesByShopId(int $shopId): array
    {
        $keys = $this->connection->createQueryBuilder()
            ->select('shopGroups.key')
            ->from('s_core_shop_pages', 'shopPages')
            ->innerJoin('shopPages', 's_cms_static_groups', 'shopGroups', 'shopGroups.id = shopPages.group_id')
            ->where('shopPages.shop_id = :shopId')
            ->setParameter('shopId', $shopId)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        $sites = [];
        foreach ($keys as $key) {
            $builder = $this->connection->createQueryBuilder();
            $current = $builder->from('s_cms_static', 'sites')
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
                ->setParameter('shopId', '%|' . $shopId . '|%')
                ->execute()
                ->fetchAll(PDO::FETCH_ASSOC);

            foreach ($current as $item) {
                $sites[$item['id']] = $item;
            }
        }

        return array_values($sites);
    }
}
