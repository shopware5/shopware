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
use Shopware\Bundle\SearchBundle\Sorting\ProductAttributeSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class ProductAttributeSortingTest extends TestCase
{
    public function testSingleFieldSorting(): void
    {
        $sorting = new ProductAttributeSorting('attr1');

        $this->search(
            [
                'first' => ['attr1' => 'Charlie'],
                'second' => ['attr1' => 'Alpha'],
                'third' => ['attr1' => 'Bravo'],
            ],
            ['second', 'third', 'first'],
            null,
            [],
            [],
            [$sorting]
        );
    }

    public function testMultipleFieldSorting(): void
    {
        $this->search(
            [
                'first' => ['attr1' => 'Charlie'],
                'second' => ['attr1' => 'Alpha'],
                'third' => ['attr1' => 'Bravo', 'attr2' => 'Bravo'],
                'fourth' => ['attr1' => 'Bravo', 'attr2' => 'Alpha'],
            ],
            ['second', 'fourth', 'third', 'first'],
            null,
            [],
            [],
            [
                new ProductAttributeSorting('attr1'),
                new ProductAttributeSorting('attr2'),
            ]
        );
    }

    /**
     * @param string                     $number
     * @param array<string, string|null> $attribute
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $attribute = ['attr1' => null]
    ): array {
        $product = parent::getProduct($number, $context, $category);
        $product['mainDetail']['attribute'] = $attribute;

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
