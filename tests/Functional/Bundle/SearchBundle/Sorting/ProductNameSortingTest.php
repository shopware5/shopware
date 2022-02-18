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
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class ProductNameSortingTest extends TestCase
{
    public function testNameSorting(): void
    {
        $sorting = new ProductNameSorting();

        $this->search(
            [
                'first' => 'Charlie',
                'second' => 'Alpha',
                'third' => 'Bravo',
            ],
            ['second', 'third', 'first'],
            null,
            [],
            [],
            [$sorting]
        );
    }

    /**
     * @param string      $number
     * @param string|null $name
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $name = null
    ): array {
        $product = parent::getProduct($number, $context, $category);
        $product['name'] = $name;

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
