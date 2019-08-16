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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle\Product;

use Shopware\Bundle\ESIndexingBundle\Product\ProductListingVariationLoader;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Article\Detail;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class ProductListingVariationLoaderTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->helper->cleanUp();
    }

    public function testAvailabilityWithOne(): void
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $productData = $this->getProduct($number, $context);

        $productData = $this->helper->createArticle($productData);

        $numbers = array_map(static function (Detail $detail) {
            return $detail->getNumber();
        }, $productData->getDetails()->getValues());
        $numbers[] = $productData->getMainDetail()->getNumber();

        $products = Shopware()->Container()->get('shopware_storefront.list_product_service')->getList($numbers, $context);
        $variantConfiguration = Shopware()->Container()->get('shopware_storefront.configurator_service')->getProductsConfigurations($products, $context);

        $groups = $this->getProductGroups($productData);
        $groupIds = array_keys($groups);
        $firstId = $groupIds[0];

        $variantFacet = new VariantFacet([$firstId]);

        $available = $this->getService()->getAvailability($products, $variantConfiguration, $variantFacet);
        $number .= '1';
        static::assertArrayHasKey('g' . $firstId, $available[$number]);
        static::assertArrayNotHasKey('g' . $groupIds[1], $available[$number]);
    }

    public function testAvailabilityWithTwo(): void
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $productData = $this->getProduct($number, $context);

        $productData = $this->helper->createArticle($productData);

        $numbers = array_map(static function (Detail $detail) {
            return $detail->getNumber();
        }, $productData->getDetails()->getValues());
        $numbers[] = $productData->getMainDetail()->getNumber();

        $products = Shopware()->Container()->get('shopware_storefront.list_product_service')->getList($numbers, $context);
        $variantConfiguration = Shopware()->Container()->get('shopware_storefront.configurator_service')->getProductsConfigurations($products, $context);

        $groups = $this->getProductGroups($productData);
        $groupIds = array_keys($groups);
        list($firstId, $secondId) = $groupIds;

        $variantFacet = new VariantFacet([$firstId, $secondId]);

        $available = $this->getService()->getAvailability($products, $variantConfiguration, $variantFacet);
        $number .= '1';
        static::assertArrayHasKey('g' . $firstId, $available[$number]);
        static::assertArrayHasKey('g' . $firstId . '-' . $secondId, $available[$number]);
        static::assertArrayHasKey('g' . $secondId, $available[$number]);
        static::assertArrayNotHasKey('g' . $groupIds[2], $available[$number]);
        static::assertArrayNotHasKey('g' . $secondId . '-' . $firstId, $available[$number]);
    }

    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additionally = null
    ): array {
        $product = parent::getProduct($number, $context, $category);

        $configurator = $this->helper->getConfigurator(
            $context->getCurrentCustomerGroup(),
            $number,
            [
                'Farbe' => ['rot', 'blau', 'grün'],
                'Größe' => ['L', 'M', 'S'],
                'Form' => ['rund', 'eckig', 'oval'],
            ]
        );

        $product = array_merge($product, $configurator);

        return $product;
    }

    private function getProductGroups(Product $product): array
    {
        $groups = [];

        /** @var Group $item */
        foreach ($product->getConfiguratorSet()->getGroups() as $item) {
            $groups[$item->getId()] = [
                'name' => $item->getName(),
            ];
        }

        return $groups;
    }

    private function getService(): ProductListingVariationLoader
    {
        return Shopware()->Container()->get('shopware_elastic_search.product_listing_variation_loader');
    }
}
