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

use Shopware\Bundle\StoreFrontBundle;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductSearch implements ProductSearchInterface
{
    /**
     * @var ProductNumberSearchInterface
     */
    private $searchGateway;

    /**
     * @var StoreFrontBundle\Service\ListProductServiceInterface
     */
    private $productService;

    /**
     * @param StoreFrontBundle\Service\ListProductServiceInterface $productService
     * @param ProductNumberSearchInterface                         $searchGateway
     */
    public function __construct(
        StoreFrontBundle\Service\ListProductServiceInterface $productService,
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
        StoreFrontBundle\Struct\ProductContextInterface $context
    ) {
        $numberResult = $this->searchGateway->search($criteria, $context);

        $numbers = array_keys($numberResult->getProducts());
        $products = $this->productService->getList($numbers, $context);
        $products = $this->assignAttributes($products, $numberResult->getProducts());

        $result = new ProductSearchResult(
            $products,
            $numberResult->getTotalCount(),
            $numberResult->getFacets()
        );

        $result->addAttributes($numberResult->getAttributes());

        return $result;
    }

    /**
     * @param StoreFrontBundle\Struct\ListProduct[] $products
     * @param StoreFrontBundle\Struct\BaseProduct[] $searchProducts
     *
     * @return StoreFrontBundle\Struct\ListProduct[]
     */
    private function assignAttributes($products, $searchProducts)
    {
        foreach ($searchProducts as $searchProduct) {
            $number = $searchProduct->getNumber();

            $product = $products[$number];

            if (!$product) {
                continue;
            }

            foreach ($searchProduct->getAttributes() as $key => $attribute) {
                $product->addAttribute($key, $attribute);
            }
        }

        return $products;
    }
}
