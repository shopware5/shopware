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
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\SitemapBundle\Condition\LastIdCondition;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing;
use Shopware\Components\Routing\Router;

class ProductUrlProvider implements UrlProviderInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var int
     */
    private $lastId = -1;

    /**
     * @var ProductNumberSearchInterface
     */
    private $productNumberSearch;

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $storeFrontCriteriaFactory;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Router                             $router
     * @param ProductNumberSearchInterface       $productNumberSearch
     * @param StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory
     * @param Connection                         $connection
     */
    public function __construct(
        Router $router,
        ProductNumberSearchInterface $productNumberSearch,
        StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory,
        Connection $connection)
    {
        $this->router = $router;
        $this->productNumberSearch = $productNumberSearch;
        $this->storeFrontCriteriaFactory = $storeFrontCriteriaFactory;
        $this->connection = $connection;
    }

    /**
     * @param Routing\Context      $routingContext
     * @param ShopContextInterface $shopContext
     *
     * @return null|Url[]
     */
    public function getUrls(Routing\Context $routingContext, ShopContextInterface $shopContext)
    {
        $criteria = $this->storeFrontCriteriaFactory
            ->createBaseCriteria([$shopContext->getShop()->getCategory()->getId()], $shopContext);
        $criteria->setFetchCount(false);
        $criteria->limit(50000);

        if ($this->lastId > -1) {
            $criteria->addBaseCondition(new LastIdCondition($this->lastId));
        }

        $productNumberSearchResult = $this->productNumberSearch->search($criteria, $shopContext);

        if (empty($productNumberSearchResult->getProducts())) {
            return null;
        }

        // Load all available product ids
        $productIds = array_map(function (BaseProduct $baseProduct) {
            return $baseProduct->getId();
        }, array_values($productNumberSearchResult->getProducts()));
        unset($productNumberSearchResult);

        $statement = $this->connection->executeQuery(
            'SELECT id, changetime AS changed FROM s_articles WHERE id IN (:productIds)',
            [':productIds' => $productIds],
            [':productIds' => Connection::PARAM_INT_ARRAY]
        );

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
        $routes = $this->router->generateList(array_map(function (array $product) {
            return $product['urlParams'];
        }, $products), $routingContext);

        $urls = [];
        for ($i = 0, $productCount = count($products); $i < $productCount; ++$i) {
            $urls[] = new Url($routes[$i], new \DateTime($product[$i]['changed']), 'weekly');
        }

        reset($products);
        $this->lastId = array_pop($products)['id'];

        return $urls;
    }
}
