<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Sorting;

use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestContext;

/**
 * @group elasticSearch
 */
class PriceSortingTest extends TestCase
{
    public function testCurrentCustomerGroupPriceSorting(): void
    {
        $sorting = new PriceSorting();
        $context = $this->getPriceContext(true, 0);

        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $this->search(
            [
                'first' => [$customerGroup->getKey() => 20, $fallback->getKey() => 1],
                'second' => [$customerGroup->getKey() => 10, $fallback->getKey() => 1],
                'third' => [$customerGroup->getKey() => 12, $fallback->getKey() => 1],
                'fourth' => [$customerGroup->getKey() => 14, $fallback->getKey() => 1],
            ],
            ['second', 'third', 'fourth', 'first'],
            null,
            [],
            [],
            [$sorting],
            $context
        );
    }

    public function testFallbackCustomerGroupPriceSorting(): void
    {
        $sorting = new PriceSorting();
        $context = $this->getPriceContext(true, 0);

        $fallback = $context->getFallbackCustomerGroup();

        $this->search(
            [
                'first' => [$fallback->getKey() => 20],
                'second' => [$fallback->getKey() => 10],
                'third' => [$fallback->getKey() => 12],
                'fourth' => [$fallback->getKey() => 14],
            ],
            ['second', 'third', 'fourth', 'first'],
            null,
            [],
            [],
            [$sorting],
            $context
        );
    }

    /**
     * @group skipElasticSearch
     */
    public function testFallbackAndCurrentCustomerGroupPriceSorting(): void
    {
        $sorting = new PriceSorting();
        $context = $this->getPriceContext(true, 0);

        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $this->search(
            [
                'first' => [$customerGroup->getKey() => 20, $fallback->getKey() => 1],
                'second' => [$fallback->getKey() => 10],
                'third' => [$fallback->getKey() => 12],
                'fourth' => [$customerGroup->getKey() => 14, $fallback->getKey() => 1],
            ],
            ['second', 'third', 'fourth', 'first'],
            null,
            [],
            [],
            [$sorting],
            $context
        );
    }

    /**
     * @group skipElasticSearch
     */
    public function testCustomerGroupDiscount(): void
    {
        $sorting = new PriceSorting();
        $context = $this->getPriceContext(true, 10);

        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $this->search(
            [
                'first' => [$customerGroup->getKey() => 40, $fallback->getKey() => 1],
                'second' => [$fallback->getKey() => 10],
                'third' => [$fallback->getKey() => 20],
                'fourth' => [$customerGroup->getKey() => 30, $fallback->getKey() => 1],
            ],
            ['second', 'third', 'fourth', 'first'],
            null,
            [],
            [],
            [$sorting],
            $context
        );
    }

    protected function getPriceContext($displayGross, $discount = null): TestContext
    {
        $context = $this->getContext();

        $data = ['key' => 'BAK', 'tax' => $displayGross];

        $context->setFallbackCustomerGroup(
            $this->converter->convertCustomerGroup($this->helper->createCustomerGroup($data))
        );

        $context->getCurrentCustomerGroup()->setDisplayGrossPrices($displayGross);
        $context->getCurrentCustomerGroup()->setUseDiscount($discount !== null);
        $context->getCurrentCustomerGroup()->setPercentageDiscount($discount);

        return $context;
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
        Category $category = null,
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

    protected function search(
        array $products,
        array $expectedNumbers,
        Category $category = null,
        array $conditions = [],
        array $facets = [],
        array $sortings = [],
        $context = null,
        array $configs = [],
        bool $variantSearch = false
    ): ProductNumberSearchResult {
        $result = parent::search(
            $products,
            $expectedNumbers,
            $category,
            $conditions,
            $facets,
            $sortings,
            $context
        );

        $this->assertSearchResultSorting($result, $expectedNumbers);

        return $result;
    }
}
