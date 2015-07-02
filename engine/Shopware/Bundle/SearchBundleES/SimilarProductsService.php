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

namespace Shopware\Bundle\SearchBundleES;

use Doctrine\DBAL\Connection;
use Elasticsearch\Client;
use ONGR\ElasticsearchDSL\Filter\IdsFilter;
use ONGR\ElasticsearchDSL\Filter\NotFilter;
use ONGR\ElasticsearchDSL\Filter\TermFilter;
use ONGR\ElasticsearchDSL\Filter\TermsFilter;
use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FuzzyLikeThisFieldQuery;
use ONGR\ElasticsearchDSL\Query\TermsQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\ESIndexingBundle\IndexFactoryInterface;
use Shopware\Bundle\ESIndexingBundle\Product\ProductMapping;
use Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductsGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;

class SimilarProductsService implements SimilarProductsServiceInterface
{
    /**
     * @var SimilarProductsGatewayInterface
     */
    private $gateway;

    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var SimilarProductsServiceInterface
     */
    private $similarProductsService;

    /**
     * @var IndexFactoryInterface
     */
    private $indexFactory;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Client $client
     * @param ListProductServiceInterface $listProductService
     * @param SimilarProductsGatewayInterface $gateway
     * @param SimilarProductsServiceInterface $similarProductsService
     * @param \Shopware_Components_Config $config
     * @param IndexFactoryInterface $indexFactory
     * @param Connection $connection
     */
    public function __construct(
        Client $client,
        ListProductServiceInterface $listProductService,
        SimilarProductsGatewayInterface $gateway,
        SimilarProductsServiceInterface $similarProductsService,
        \Shopware_Components_Config $config,
        IndexFactoryInterface $indexFactory,
        Connection $connection
    ) {
        $this->gateway = $gateway;
        $this->client = $client;
        $this->listProductService = $listProductService;
        $this->config = $config;
        $this->similarProductsService = $similarProductsService;
        $this->indexFactory = $indexFactory;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, Struct\ProductContextInterface $context)
    {
        $result = $this->getDefined($products, $context);
        $fallback = $this->findFallbackProducts($products, $result);
        if (empty($fallback)) {
            return $result;
        }

        $limit = (int) $this->config->get('similarLimit');
        if ($limit <= 0) {
            return $result;
        }

        foreach ($fallback as $product) {
            $search = $this->getSearch($product, $context, $limit);
            $body   = $search->toArray();

            $index = $this->indexFactory->createShopIndex($context->getShop());
            $similar = $this->client->search([
                'index' => $index->getName(),
                'type'  => ProductMapping::TYPE,
                'body'  => $body
            ]);

            if (empty($similar['hits']['hits'])) {
                continue;
            }

            $numbers = array_column($similar['hits']['hits'], '_id');
            $result[$product->getNumber()] = $this->listProductService->getList($numbers, $context);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Struct\ListProduct $product, Struct\ProductContextInterface $context)
    {
        $similar = $this->getList([$product], $context);
        return array_shift($similar);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param array $result
     * @return Struct\ListProduct[]
     */
    private function findFallbackProducts($products, $result)
    {
        return array_filter($products, function (Struct\ListProduct $listProduct) use ($result) {
            return !isset($result[$listProduct->getNumber()]);
        });
    }

    /**
     * @param Struct\BaseProduct[] $products
     * @param Struct\ProductContextInterface $context
     * @return array
     */
    private function getDefined($products, Struct\ProductContextInterface $context)
    {
        /**
         * returns an array which is associated with the different product numbers.
         * Each array contains a list of product numbers which are related to the reference product.
         */
        $numbers = $this->gateway->getList($products, $context);

        if (empty($numbers)) {
            return [];
        }

        //loads the list product data for the selected numbers.
        //all numbers are joined in the extractNumbers function to prevent that a product will be
        //loaded multiple times
        $listProducts = $this->listProductService->getList(
            $this->extractNumbers($numbers),
            $context
        );

        $result = [];

        foreach ($products as $product) {
            if (!isset($numbers[$product->getId()])) {
                continue;
            }

            $result[$product->getNumber()] = $this->getProductsByNumbers(
                $listProducts,
                $numbers[$product->getId()]
            );
        }

        return $result;
    }


    /**
     * @param $numbers
     * @return array
     */
    private function extractNumbers($numbers)
    {
        //collect all numbers to send a single list product request.
        $related = [];
        foreach ($numbers as $value) {
            $related = array_merge($related, $value);
        }

        //filter duplicate numbers to prevent duplicate data requests and iterations.
        $unique = array_unique($related);

        return array_values($unique);
    }

    /**
     * @param Struct\BaseProduct[] $products
     * @param array $numbers
     * @return Struct\BaseProduct[]
     */
    private function getProductsByNumbers($products, array $numbers)
    {
        $result = [];

        foreach ($products as $product) {
            if (in_array($product->getNumber(), $numbers)) {
                $result[$product->getNumber()] = $product;
            }
        }
        return $result;
    }

    /**
     * @param Struct\ListProduct $product
     * @param Struct\ShopContextInterface $context
     * @param $limit
     * @return Search
     */
    protected function getSearch($product, Struct\ShopContextInterface $context, $limit)
    {
        $search = new Search();
        $search->setSize($limit);
        $search->addQuery($this->getSimilarQuery($product));
        $search->addFilter($this->getProductNumberFilter($product));
        $search->addFilter($this->getCategoryFilter($context->getShop()->getCategory()));
        $search->addFilter($this->getCustomerGroupFilter($context->getCurrentCustomerGroup()));
        return $search;
    }

    /**
     * @param Struct\Customer\Group $customerGroup
     * @return NotFilter
     */
    protected function getCustomerGroupFilter(Struct\Customer\Group $customerGroup)
    {
        return new NotFilter(
            new TermsFilter(
                'blockedCustomerGroupIds',
                [$customerGroup->getId()]
            )
        );
    }

    /**
     * @param Struct\BaseProduct $product
     * @return NotFilter
     */
    protected function getProductNumberFilter(Struct\BaseProduct $product)
    {
        return new NotFilter(new IdsFilter([$product->getNumber()]));
    }

    /**
     * @param Struct\Category $category
     * @return TermFilter
     */
    private function getCategoryFilter(Struct\Category $category)
    {
        return new TermFilter('categoryIds', $category->getId());
    }

    /**
     * @param Struct\ListProduct $product
     * @return BoolQuery
     */
    protected function getSimilarQuery(Struct\ListProduct $product)
    {
        $categories = $this->getProductCategories($product);

        $queries = [
            new FuzzyLikeThisFieldQuery('name', $product->getName(), ['boost' => 5]),
            new TermsQuery('categoryIds', $categories)
        ];

        $query = new BoolQuery();
        $query->addParameter('minimum_should_match', 1);
        foreach ($queries as $bool) {
            $query->add($bool, BoolQuery::SHOULD);
        }

        return $query;
    }

    /**
     * @param Struct\BaseProduct $product
     * @return int[]
     */
    private function getProductCategories(Struct\BaseProduct $product)
    {
        $query = $this->connection->createQueryBuilder();

        return $query->select('categoryID')
            ->from('s_articles_categories', 'category')
            ->where('articleID = :productId')
            ->setParameter(':productId', $product->getId())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
