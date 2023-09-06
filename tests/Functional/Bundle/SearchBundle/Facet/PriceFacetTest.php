<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestContext;

/**
 * @group elasticSearch
 */
class PriceFacetTest extends TestCase
{
    public function testFacetWithCurrentCustomerGroupPrices(): void
    {
        $context = $this->getTestContext();
        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            [
                'first' => [$customerGroup->getKey() => 20, $fallback->getKey() => 1],
                'second' => [$customerGroup->getKey() => 10, $fallback->getKey() => 1],
                'third' => [$customerGroup->getKey() => 12, $fallback->getKey() => 1],
                'fourth' => [$customerGroup->getKey() => 14, $fallback->getKey() => 1],
            ],
            ['second', 'third', 'fourth', 'first'],
            null,
            [],
            [new PriceFacet()],
            [],
            $context
        );

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(RangeFacetResult::class, $facet);

        static::assertEquals(110.00, $facet->getMin());
        static::assertEquals(120.00, $facet->getMax());
    }

    public function testFacetWithFallbackCustomerGroupPrices(): void
    {
        $context = $this->getTestContext();
        $context->setFallbackCustomerGroup($this->getEkCustomerGroup());
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            [
                'first' => [$fallback->getKey() => 30],
                'second' => [$fallback->getKey() => 5],
                'third' => [$fallback->getKey() => 12],
                'fourth' => [$fallback->getKey() => 14],
            ],
            ['second', 'third', 'fourth', 'first'],
            null,
            [],
            [new PriceFacet()],
            [],
            $context
        );

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(RangeFacetResult::class, $facet);

        static::assertEquals(105.00, $facet->getMin());
        static::assertEquals(130.00, $facet->getMax());
    }

    /**
     * @group skipElasticSearch
     */
    public function testFacetWithMixedCustomerGroupPrices(): void
    {
        $context = $this->getTestContext();
        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            [
                'first' => [$customerGroup->getKey() => 0, $fallback->getKey() => 5],
                'second' => [$fallback->getKey() => 50],
                'third' => [$customerGroup->getKey() => 12, $fallback->getKey() => 14],
                'fourth' => [$fallback->getKey() => 12],
            ],
            ['second', 'third', 'fourth', 'first'],
            null,
            [],
            [new PriceFacet()],
            [],
            $context
        );

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(RangeFacetResult::class, $facet);

        static::assertEquals(100.00, $facet->getMin());
        static::assertEquals(150.00, $facet->getMax());
    }

    /**
     * @group skipElasticSearch
     */
    public function testFacetWithCurrencyFactor(): void
    {
        $context = $this->getTestContext();
        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $context->getCurrency()->setFactor(2.5);

        $result = $this->search(
            [
                'first' => [$customerGroup->getKey() => 0, $fallback->getKey() => 5],
                'second' => [$fallback->getKey() => 50],
                'third' => [$customerGroup->getKey() => 12, $fallback->getKey() => 14],
                'fourth' => [$fallback->getKey() => 12],
            ],
            ['second', 'third', 'fourth', 'first'],
            null,
            [],
            [new PriceFacet()],
            [],
            $context
        );

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(RangeFacetResult::class, $facet);

        static::assertEquals(250.00, $facet->getMin());
        static::assertEquals(375.00, $facet->getMax());
    }

    /**
     * @param string             $number
     * @param array<string, int> $prices
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        ?Category $category = null,
        $prices = []
    ): array {
        $product = parent::getProduct($number, $context, $category);

        if (!empty($prices)) {
            $product['mainDetail']['prices'] = [];

            foreach ($prices as $key => $price) {
                $product['mainDetail']['prices'] = array_merge(
                    $product['mainDetail']['prices'],
                    $this->helper->getGraduatedPrices($key, $price)
                );
            }
        }

        return $product;
    }

    private function getTestContext(): TestContext
    {
        $context = $this->getContext();

        $data = ['key' => 'BAK', 'tax' => true];

        $context->setFallbackCustomerGroup(
            $this->converter->convertCustomerGroup($this->helper->createCustomerGroup($data))
        );

        $context->getCurrentCustomerGroup()->setDisplayGrossPrices(true);
        $context->getCurrentCustomerGroup()->setUseDiscount(false);

        return $context;
    }
}
