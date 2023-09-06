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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Sorting;

use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\Sorting\ManualSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group skipElasticsearch
 */
class ManualSortingTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private Category $category;

    public function setUp(): void
    {
        parent::setUp();
        $this->category = $this->helper->createCategory(['name' => 'My fancy Products']);
    }

    public function testAscendingSorting(): void
    {
        $sorting = new ManualSorting();

        $this->search(
            [
                'first' => 3,
                'second' => 20,
                'third' => 1,
            ],
            ['third', 'first', 'second'],
            $this->category,
            [],
            [],
            [$sorting]
        );
    }

    public function testDescendingSorting(): void
    {
        $sorting = new ManualSorting(SortingInterface::SORT_DESC);

        $this->search(
            [
                'first' => 3,
                'second' => 20,
                'third' => 1,
            ],
            ['second', 'first', 'third'],
            $this->category,
            [],
            [],
            [$sorting]
        );
    }

    /**
     * @param int $position
     */
    protected function createProduct(
        string $number,
        ShopContext $context,
        Category $category,
        $position
    ): Article {
        $product = parent::createProduct(
            $number,
            $context,
            $category,
            $position
        );

        Shopware()->Db()->insert('s_categories_manual_sorting', [
            'product_id' => $product->getId(),
            'category_id' => $this->category->getId(),
            'position' => $position,
        ]);

        return $product;
    }

    protected function search(
        array $products,
        array $expectedNumbers,
        ?Category $category = null,
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
