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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SitemapBundle\ProductRepositoryInterface;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\Context as RoutingContext;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Article\Article as Product;

class ProductUrlProvider implements UrlProviderInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var int|null|null
     */
    private $lastId;

    /**
     * @var ProductRepositoryInterface
     */
    private $repository;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        RouterInterface $router,
        ProductRepositoryInterface $repository,
        Connection $connection
    ) {
        $this->router = $router;
        $this->repository = $repository;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(RoutingContext $routingContext, ShopContextInterface $shopContext)
    {
        // Load all available product ids
        $productIds = $this->repository->getProductIds($shopContext, $this->lastId);

        $products = $this->getProducts($productIds);

        $this->lastId = array_pop($productIds);

        return $this->generateUrls($products, $routingContext);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->lastId = null;
    }

    /**
     * @return array
     */
    protected function generateUrls(array $products, RoutingContext $routingContext)
    {
        // Batch generate routes
        $routes = $this->router->generateList(array_column($products, 'urlParams'), $routingContext);
        $urls = [];
        for ($i = 0, $productCount = count($products); $i < $productCount; ++$i) {
            $urls[] = new Url($routes[$i], new \DateTime($products[$i]['changetime']), 'weekly', Product::class, $products[$i]['id']);
        }

        return $urls;
    }

    /**
     * @param int[] $productIds
     *
     * @return array
     */
    protected function getProducts(array $productIds)
    {
        $qb = $this->connection->createQueryBuilder();
        $statement = $qb->from('s_articles', 'product')
            ->select('id, changetime')
            ->where('id IN(:productIds)')
            ->setParameter('productIds', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute();

        $products = [];
        // Enrich product ids with date of last modification
        while ($product = $statement->fetch()) {
            $product['urlParams'] = [
                'sViewport' => 'detail',
                'sArticle' => $product['id'],
            ];

            $products[] = $product;
        }

        // Batch generate routes
        return $products;
    }
}
