<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Service\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SimilarProductsService implements Service\SimilarProductsServiceInterface
{
    /**
     * @var Gateway\SimilarProductsGatewayInterface
     */
    private $gateway;

    /**
     * @var Service\ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @param Gateway\SimilarProductsGatewayInterface $gateway
     * @param Service\ListProductServiceInterface $listProductService
     */
    public function __construct(
        Gateway\SimilarProductsGatewayInterface $gateway,
        Service\ListProductServiceInterface $listProductService
    ) {
        $this->gateway = $gateway;
        $this->listProductService = $listProductService;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $similar = $this->getList(array($product), $context);

        return array_shift($similar);
    }

    /**
     * @inheritdoc
     */
    public function getList($products, Struct\Context $context)
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

        $result = array();
        $fallback = array();

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

        $fallback = $this->gateway->getByListCategory($fallback, $context);

        //loads the list product data for the selected numbers.
        //all numbers are joined in the extractNumbers function to prevent that a product will be
        //loaded multiple times
        $listProducts = $this->listProductService->getList(
            $this->extractNumbers($fallback),
            $context
        );

        $fallbackResult = array();
        foreach ($products as $product) {
            $fallbackResult[$product->getNumber()] = $this->getProductsByNumbers(
                $listProducts,
                $fallback[$product->getId()]
            );
        }

        return array_merge($result, $fallbackResult);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param array $numbers
     * @return Struct\ListProduct[]
     */
    private function getProductsByNumbers($products, array $numbers)
    {
        $result = array();

        foreach ($products as $product) {
            if (in_array($product->getNumber(), $numbers)) {
                $result[$product->getNumber()] = $product;
            }
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
        $related = array();
        foreach ($numbers as $value) {
            $related = array_merge($related, $value);
        }

        //filter duplicate numbers to prevent duplicate data requests and iterations.
        $unique = array_unique($related);

        return array_values($unique);
    }
}
