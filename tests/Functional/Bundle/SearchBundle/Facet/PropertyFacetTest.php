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

use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class PropertyFacetTest extends TestCase
{
    public function testPropertyFacet(): void
    {
        $properties = $this->helper->getProperties(2, 3);

        $firstCombination = $this->createPropertyCombination(
            $properties,
            [0, 1, 2]
        );

        $secondCombination = $this->createPropertyCombination(
            $properties,
            [1, 2, 3]
        );

        $thirdCombination = $this->createPropertyCombination(
            $properties,
            [2, 3, 4, 5]
        );

        $result = $this->search(
            [
                'first' => $firstCombination,
                'second' => $secondCombination,
                'third' => $thirdCombination,
                'fourth' => [],
            ],
            ['first', 'second', 'third', 'fourth'],
            null,
            [],
            [new PropertyFacet()]
        );

        static::assertCount(2, $result->getFacets());

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(FacetResultGroup::class, $facet);

        static::assertCount(2, $facet->getFacetResults());
        foreach ($facet->getFacetResults() as $result) {
            static::assertInstanceOf(ValueListFacetResult::class, $result);
            static::assertCount(3, $result->getValues());
        }
    }

    public function testMultiplePropertySets(): void
    {
        $properties = $this->helper->getProperties(2, 3);
        $first = $this->createPropertyCombination($properties, [0, 1, 2]);
        $second = $this->createPropertyCombination($properties, [3, 4, 5]);

        $properties = $this->helper->getProperties(2, 3, 'PHP');
        $third = $this->createPropertyCombination($properties, [0, 1, 2]);
        $fourth = $this->createPropertyCombination($properties, [3, 4, 5]);

        $result = $this->search(
            [
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth,
            ],
            ['first', 'second', 'third', 'fourth'],
            null,
            [],
            [new PropertyFacet()]
        );

        static::assertCount(2, $result->getFacets());

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(FacetResultGroup::class, $facet);

        static::assertCount(4, $facet->getFacetResults());
        foreach ($facet->getFacetResults() as $result) {
            static::assertInstanceOf(ValueListFacetResult::class, $result);
            static::assertCount(3, $result->getValues());
        }
    }

    /**
     * @param string               $number
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        ?Category $category = null,
        $properties = []
    ): array {
        $product = parent::getProduct($number, $context, $category);

        return array_merge($product, $properties);
    }

    /**
     * @param array<string, array> $properties
     * @param int[]                $indexes
     *
     * @return array<string, array>
     */
    private function createPropertyCombination(array $properties, array $indexes): array
    {
        $combination = $properties;
        unset($combination['all']);

        $values = [];
        foreach ($properties['propertyValues'] as $index => $value) {
            if (\in_array($index, $indexes)) {
                $values[] = $value;
            }
        }
        $combination['propertyValues'] = $values;

        return $combination;
    }
}
