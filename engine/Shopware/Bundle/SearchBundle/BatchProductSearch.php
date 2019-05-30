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
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

class BatchProductSearch
{
    /**
     * @var BatchProductNumberSearch
     */
    private $productNumberSearch;

    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    public function __construct(
        BatchProductNumberSearch $productNumberSearch,
        ListProductServiceInterface $listProductService
    ) {
        $this->productNumberSearch = $productNumberSearch;
        $this->listProductService = $listProductService;
    }

    /**
     * Creates a search request on the internal search gateway to
     * get the product result for the passed criteria object.
     *
     * @return BatchProductSearchResult
     */
    public function search(BatchProductNumberSearchRequest $request, Struct\ShopContextInterface $context)
    {
        $searchResult = $this->productNumberSearch->search($request, $context);
        $listProducts = $this->listProductService->getList($searchResult->getProductNumbers(), $context);

        return $this->mapListProducts($searchResult, $listProducts);
    }

    /**
     * @param ListProduct[] $listProducts
     *
     * @return BatchProductSearchResult
     */
    private function mapListProducts(BatchProductNumberSearchResult $searchResult, array $listProducts)
    {
        $result = [];

        foreach ($searchResult->getAll() as $key => $baseProducts) {
            $products = array_intersect_key($listProducts, $baseProducts);
            $products = $this->assignAttributes($products, $baseProducts);

            $result[$key] = $products;
        }

        return new BatchProductSearchResult($result);
    }

    /**
     * @param array<string, Struct\ListProduct|null> $products
     * @param Struct\BaseProduct[]                   $searchProducts
     *
     * @return Struct\ListProduct[]
     */
    private function assignAttributes($products, $searchProducts)
    {
        foreach ($searchProducts as $searchProduct) {
            $number = $searchProduct->getNumber();

            $product = $products[$number];

            if (!$product) {
                continue;
            }

            $product->addAttributes($searchProduct->getAttributes());
        }

        return $products;
    }
}
