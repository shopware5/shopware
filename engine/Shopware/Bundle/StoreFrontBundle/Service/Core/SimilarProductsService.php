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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\SearchBundle\Condition\SimilarProductCondition;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SimilarProductsService implements Service\SimilarProductsServiceInterface
{
    /**
     * @var Gateway\SimilarProductsGateway
     */
    private $gateway;

    /**
     * @var Service\ListProductServiceInterface
     */
    private $listProductService;
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ProductSearchInterface
     */
    private $search;

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $factory;

    /**
     * @param Gateway\SimilarProductsGateway      $gateway
     * @param Service\ListProductServiceInterface $listProductService
     * @param \Shopware_Components_Config         $config
     * @param ProductSearchInterface              $search
     * @param StoreFrontCriteriaFactoryInterface  $factory
     */
    public function __construct(
        Gateway\SimilarProductsGateway $gateway,
        Service\ListProductServiceInterface $listProductService,
        ProductSearchInterface $search,
        StoreFrontCriteriaFactoryInterface $factory,
        \Shopware_Components_Config $config
    ) {
        $this->gateway = $gateway;
        $this->listProductService = $listProductService;
        $this->config = $config;
        $this->search = $search;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, Struct\ProductContextInterface $context)
    {
        /**
         * returns an array which is associated with the different product numbers.
         * Each array contains a list of product numbers which are related to the reference product.
         */
        $numbers = $this->gateway->getList($products, $context);

        //loads the list product data for the selected numbers.
        //all numbers are joined in the extractNumbers function to prevent that a product will be
        //loaded multiple times
        $listProducts = $this->listProductService->getList(
            $this->extractNumbers($numbers),
            $context
        );

        $result = [];
        $fallback = [];

        foreach ($products as $product) {
            if (!isset($numbers[$product->getId()])) {
                $fallback[$product->getNumber()] = $product;
                continue;
            }

            $result[$product->getNumber()] = $this->getProductsByNumbers(
                $listProducts,
                $numbers[$product->getId()]
            );
        }

        if (empty($fallback)) {
            return $result;
        }

        $limit = $this->config->get('similarLimit');
        if ($limit <= 0) {
            return $result;
        }

        $fallbackResult = [];
        /** @var \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct $product */
        foreach ($fallback as $product) {
            $criteria = $this->factory->createBaseCriteria([$context->getShop()->getCategory()->getId()], $context);
            $criteria->limit($limit);

            $condition = new SimilarProductCondition($product->getId(), $product->getName());

            $criteria->addBaseCondition($condition);
            $criteria->addSorting(new PopularitySorting());
            $criteria->setFetchCount(false);

            /** @var \Shopware\Bundle\SearchBundle\ProductSearchResult $searchResult */
            $searchResult = $this->search->search($criteria, $context);

            $fallbackResult[$product->getNumber()] = $searchResult->getProducts();
        }

        return $result + $fallbackResult;
    }

    /**
     * @param Struct\BaseProduct[] $products
     * @param array                $numbers
     *
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
     * @param $numbers
     *
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
}
