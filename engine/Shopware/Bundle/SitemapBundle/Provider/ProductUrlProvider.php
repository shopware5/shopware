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
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Shopware\Bundle\SearchBundle\Condition\LastProductIdCondition;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing;
use Shopware\Models\Article\Article as Product;

class ProductUrlProvider implements UrlProviderInterface
{
    /**
     * @var Routing\RouterInterface
     */
    private $router;

    /**
     * @var int|null|null
     */
    private $lastId;

    /**
     * @var ProductNumberSearchInterface
     */
    private $productNumberSearch;

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $storeFrontCriteriaFactory;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param int $batchSize
     */
    public function __construct(
        Routing\RouterInterface $router,
        ProductNumberSearchInterface $productNumberSearch,
        StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory,
        ConnectionInterface $connection,
        $batchSize
    ) {
        $this->router = $router;
        $this->productNumberSearch = $productNumberSearch;
        $this->storeFrontCriteriaFactory = $storeFrontCriteriaFactory;
        $this->connection = $connection;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Routing\Context $routingContext, ShopContextInterface $shopContext)
    {
        $criteria = $this->storeFrontCriteriaFactory
            ->createBaseCriteria([$shopContext->getShop()->getCategory()->getId()], $shopContext);
        $criteria->setFetchCount(false);
        $criteria->limit($this->batchSize);

        if ($this->lastId) {
            $criteria->addBaseCondition(new LastProductIdCondition($this->lastId));
        }

        $productNumberSearchResult = $this->productNumberSearch->search($criteria, $shopContext);

        if (count($productNumberSearchResult->getProducts()) === 0) {
            return [];
        }

        // Load all available product ids
        $productIds = array_map(function (BaseProduct $baseProduct) {
            return $baseProduct->getId();
        }, array_values($productNumberSearchResult->getProducts()));
        unset($productNumberSearchResult);

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
        $routes = $this->router->generateList(array_column($products, 'urlParams'), $routingContext);

        $urls = [];
        for ($i = 0, $productCount = count($products); $i < $productCount; ++$i) {
            $urls[] = new Url($routes[$i], new \DateTime($products[$i]['changetime']), 'weekly', Product::class, $products[$i]['id']);
        }

        reset($products);
        $this->lastId = array_pop($productIds);

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->lastId = null;
    }
}
