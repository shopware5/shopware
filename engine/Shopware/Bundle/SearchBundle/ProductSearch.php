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

namespace Shopware\Bundle\SearchBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

class ProductSearch implements ProductSearchInterface
{
    /**
     * @var ProductNumberSearchInterface
     */
    private $searchGateway;

    /**
     * @var ListProductServiceInterface
     */
    private $productService;

    public function __construct(
        ListProductServiceInterface $productService,
        ProductNumberSearchInterface $searchGateway
    ) {
        $this->productService = $productService;
        $this->searchGateway = $searchGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function search(
        Criteria $criteria,
        ProductContextInterface $context
    ) {
        $numberResult = $this->searchGateway->search($criteria, $context);

        $numbers = array_keys($numberResult->getProducts());
        $products = $this->productService->getList($numbers, $context);
        $products = $this->assignAttributes($products, $numberResult->getProducts());

        $result = new ProductSearchResult(
            $products,
            $numberResult->getTotalCount(),
            $numberResult->getFacets(),
            $criteria,
            $context
        );

        $result->addAttributes($numberResult->getAttributes());

        return $result;
    }

    /**
     * @param ListProduct[] $products
     * @param BaseProduct[] $searchProducts
     *
     * @return ListProduct[]
     */
    private function assignAttributes($products, $searchProducts)
    {
        foreach ($searchProducts as $searchProduct) {
            $number = $searchProduct->getNumber();

            if (!isset($products[$number])) {
                continue;
            }

            foreach ($searchProduct->getAttributes() as $key => $attribute) {
                $products[$number]->addAttribute($key, $attribute);
            }
        }

        return $products;
    }
}
