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

namespace Shopware\Bundle\SitemapBundle\Provider;

use DateTime;
use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Article\Supplier;

class ManufacturerUrlProvider implements UrlProviderInterface
{
    private RouterInterface $router;

    private Connection $connection;

    private bool $allExported = false;

    public function __construct(Connection $connection, RouterInterface $router)
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

        $manufacturers = $this->getManufacturersForSitemap($shopContext);

        if (!$manufacturers) {
            return [];
        }

        foreach ($manufacturers as &$manufacturer) {
            $manufacturer['changed'] = new DateTime($manufacturer['changed']);
            $manufacturer['urlParams'] = [
                'sViewport' => 'listing',
                'sAction' => 'manufacturer',
                'sSupplier' => $manufacturer['id'],
            ];
        }

        unset($manufacturer);

        $routes = $this->router->generateList(array_column($manufacturers, 'urlParams'), $routingContext);
        $urls = [];

        for ($i = 0, $routeCount = \count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $manufacturers[$i]['changed'], 'weekly', Supplier::class, $manufacturers[$i]['id']);
        }

        $this->allExported = true;

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
     * Gets all suppliers that have products for the current shop
     *
     * @return array<array<string, mixed>>
     */
    private function getManufacturersForSitemap(ShopContextInterface $shopContext): array
    {
        $categoryId = $shopContext->getShop()->getCategory()->getId();

        return $this->connection->createQueryBuilder()
            ->select(['manufacturer.id', 'manufacturer.name', 'manufacturer.changed'])
            ->from('s_articles_supplier', 'manufacturer')
            ->innerJoin('manufacturer', 's_articles', 'product', 'product.supplierID = manufacturer.id')
            ->innerJoin('product', 's_articles_categories_ro', 'categories', 'categories.articleID = product.id AND categories.categoryID = :categoryId')
            ->setParameter(':categoryId', $categoryId)
            ->groupBy('manufacturer.id')
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}
