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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Sorting;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Sorting\ManualSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group skipElasticsearch
 */
class ManualSortingTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var CategoryCondition
     */
    private $condition;

    public function setUp(): void
    {
        parent::setUp();
        $this->category = $this->helper->createCategory(['name' => 'My fancy Products']);
        $this->condition = new CategoryCondition([$this->category->getId()]);
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

    protected function createProduct(
        $number,
        ShopContext $context,
        Category $category,
        $position
    ) {
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
        $products,
        $expectedNumbers,
        $category = null,
        $conditions = [],
        $facets = [],
        $sortings = [],
        $context = null,
        array $configs = [],
        $variantSearch = false
    ) {
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
