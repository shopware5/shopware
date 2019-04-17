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

use Shopware\Bundle\StoreFrontBundle\Gateway\RelatedProductsGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

class RelatedProductsService implements Service\RelatedProductsServiceInterface
{
    /**
     * @var RelatedProductsGatewayInterface
     */
    private $gateway;

    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    public function __construct(
        RelatedProductsGatewayInterface $gateway,
        ListProductServiceInterface $listProductService
    ) {
        $this->gateway = $gateway;
        $this->listProductService = $listProductService;
    }

    /**
     * {@inheritdoc}
     */
    public function get(BaseProduct $product, ProductContextInterface $context)
    {
        $related = $this->getList([$product], $context);

        return array_shift($related);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ProductContextInterface $context)
    {
        /*
         * Returns an array which is associated with the different product numbers.
         * Each array contains a list of product numbers which are related to the reference product.
         */
        $numbers = $this->gateway->getList($products);

        /*
         * Loads the list product data for the selected numbers.
         * All numbers are joined in the `extractNumbers` function to prevent that a product will be loaded multiple times
         */
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
     * @param BaseProduct[] $products
     * @param string[]      $numbers
     *
     * @return BaseProduct[]
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
     * @param array<string, string[]> $numbers
     *
     * @return array
     */
    private function extractNumbers($numbers)
    {
        // Collect all numbers to send a single list product request.
        $related = [];
        foreach ($numbers as $value) {
            $related = array_merge($related, $value);
        }

        // Filter duplicate numbers to prevent duplicate data requests and iterations.
        $unique = array_unique($related);

        return array_values($unique);
    }
}
