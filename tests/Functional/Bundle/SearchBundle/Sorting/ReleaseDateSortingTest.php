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
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class ReleaseDateSortingTest extends TestCase
{
    public function testReleaseDateSorting(): void
    {
        $sorting = new ReleaseDateSorting();

        $this->search(
            [
                'first' => '2014-01-01',
                'second' => '2013-04-03',
                'third' => '2014-12-12',
                'fourth' => '2012-01-03',
            ],
            ['fourth', 'second', 'first', 'third'],
            null,
            [],
            [],
            [$sorting]
        );
    }

    /**
     * @param string      $number
     * @param string|null $releaseDate
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $releaseDate = null
    ): array {
        $product = parent::getProduct($number, $context, $category);

        $product['added'] = $releaseDate;

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
