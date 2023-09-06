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

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Bundle\SearchBundle\FacetResult\TreeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\TreeItem;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class CategoryFacetTest extends TestCase
{
    public function testSingleProductInFacet(): void
    {
        $baseCategory = $this->helper->createCategory([
            'name' => 'firstLevel',
        ]);

        $subCategory = $this->helper->createCategory([
            'name' => 'secondLevel',
            'parent' => $baseCategory->getId(),
        ]);

        $result = $this->search(
            [
                'first' => $baseCategory,
                'second' => $subCategory,
                'third' => $subCategory,
                'fourth' => null,
            ],
            ['first', 'second', 'third'],
            $baseCategory,
            [],
            [new CategoryFacet()]
        );

        static::assertCount(2, $result->getFacets());

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(TreeFacetResult::class, $facet);

        static::assertCount(1, $facet->getValues());

        $value = $facet->getValues()[0];
        static::assertInstanceOf(TreeItem::class, $value);
        static::assertEquals('firstLevel', $value->getLabel());
    }

    public function testMultipleCategories(): void
    {
        $baseCategory = $this->helper->createCategory([
            'name' => 'firstLevel',
        ]);

        $subCategory1 = $this->helper->createCategory([
            'name' => 'secondLevel-1',
            'parent' => $baseCategory->getId(),
        ]);
        $subCategory2 = $this->helper->createCategory([
            'name' => 'secondLevel-2',
            'parent' => $baseCategory->getId(),
        ]);

        $result = $this->search(
            [
                'first' => $subCategory1,
                'second' => $subCategory1,
                'third' => $subCategory2,
                'fourth' => $subCategory2,
                'fifth' => $subCategory2,
            ],
            ['first', 'second', 'third', 'fourth', 'fifth'],
            $baseCategory,
            [],
            [new CategoryFacet()]
        );

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(TreeFacetResult::class, $facet);

        static::assertCount(1, $facet->getValues());

        $value = $facet->getValues()[0];
        static::assertEquals('firstLevel', $value->getLabel());
        static::assertTrue($value->isActive());

        static::assertEquals('secondLevel-1', $value->getValues()[0]->getLabel());
        static::assertEquals('secondLevel-2', $value->getValues()[1]->getLabel());
    }

    public function testNestedCategories(): void
    {
        $baseCategory = $this->helper->createCategory([
            'name' => 'firstLevel',
        ]);

        $subCategory1 = $this->helper->createCategory([
            'name' => 'secondLevel-1',
            'parent' => $baseCategory->getId(),
        ]);

        $subCategory2 = $this->helper->createCategory([
            'name' => 'thirdLevel-2',
            'parent' => $subCategory1->getId(),
        ]);

        $subCategory3 = $this->helper->createCategory([
            'name' => 'secondLevel-2',
            'parent' => $baseCategory->getId(),
        ]);

        $result = $this->search(
            [
                'first' => $subCategory1,
                'second' => $subCategory1,
                'third' => $subCategory2,
                'fourth' => $subCategory3,
                'fifth' => $subCategory3,
            ],
            ['first', 'second', 'third'],
            $subCategory1,
            [],
            [new CategoryFacet(null, 4)]
        );

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(TreeFacetResult::class, $facet);

        static::assertCount(1, $facet->getValues());

        $value = $facet->getValues()[0];
        static::assertInstanceOf(TreeItem::class, $value);
        static::assertEquals('firstLevel', $value->getLabel());

        $value = $value->getValues()[0];
        static::assertEquals('secondLevel-1', $value->getLabel());
        static::assertTrue($value->isActive());

        $value = $value->getValues()[0];
        static::assertEquals('thirdLevel-2', $value->getLabel());
    }

    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $additionally = null
    ): array {
        if ($additionally !== null) {
            static::assertInstanceOf(Category::class, $additionally);
        }

        return parent::getProduct($number, $context, $additionally);
    }

    protected function addCategoryBaseCondition(Criteria $criteria, Category $category): void
    {
        parent::addCategoryBaseCondition($criteria, $category);
        $criteria->addCondition(
            new CategoryCondition([$category->getId()])
        );
    }
}
