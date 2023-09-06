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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class PropertyConditionTest extends TestCase
{
    public function testSinglePropertyConditionWithOneValue(): void
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first = $this->createPropertyCombination($properties, [0, 4]);
        $second = $this->createPropertyCombination($properties, [1, 5]);
        $third = $this->createPropertyCombination($properties, [2, 6]);
        $fourth = $this->createPropertyCombination($properties, [3, 7]);

        $conditions = [];

        $conditions[] = new PropertyCondition([
            $values[0]['id'],
        ]);

        $this->search(
            [
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth,
            ],
            ['first'],
            null,
            $conditions
        );
    }

    public function testSinglePropertyConditionWithTwoValues(): void
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first = $this->createPropertyCombination($properties, [0, 4]);
        $second = $this->createPropertyCombination($properties, [1, 5]);
        $third = $this->createPropertyCombination($properties, [2, 6]);
        $fourth = $this->createPropertyCombination($properties, [3, 7]);

        $conditions = [];

        $conditions[] = new PropertyCondition([
            $values[0]['id'],
            $values[1]['id'],
        ]);

        $this->search(
            [
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth,
            ],
            ['first', 'second'],
            null,
            $conditions
        );
    }

    public function testSinglePropertyConditionWithThreeValues(): void
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first = $this->createPropertyCombination($properties, [0, 4]);
        $second = $this->createPropertyCombination($properties, [1, 5]);
        $third = $this->createPropertyCombination($properties, [2, 6]);
        $fourth = $this->createPropertyCombination($properties, [3, 7]);

        $conditions = [];

        $conditions[] = new PropertyCondition([
            $values[0]['id'],
            $values[1]['id'],
            $values[3]['id'],
        ]);

        $this->search(
            [
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth,
            ],
            ['first', 'second', 'fourth'],
            null,
            $conditions
        );
    }

    public function testTwoPropertyConditionsWithOneValue(): void
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first = $this->createPropertyCombination($properties, [0, 4]);
        $second = $this->createPropertyCombination($properties, [0, 4]);
        $third = $this->createPropertyCombination($properties, [2, 6]);
        $fourth = $this->createPropertyCombination($properties, [3, 7]);

        $conditions = [];

        $conditions[] = new PropertyCondition([
            $values[0]['id'],
        ]);

        $conditions[] = new PropertyCondition([
            $values[4]['id'],
        ]);

        $this->search(
            [
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth,
            ],
            ['first', 'second'],
            null,
            $conditions
        );
    }

    public function testTwoPropertyConditionsWithTwoValues(): void
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first = $this->createPropertyCombination($properties, [0, 4]);
        $second = $this->createPropertyCombination($properties, [1, 5]);
        $third = $this->createPropertyCombination($properties, [1, 6]);
        $fourth = $this->createPropertyCombination($properties, [3, 5]);

        $conditions = [];

        $conditions[] = new PropertyCondition([
            $values[0]['id'],
            $values[1]['id'],
        ]);

        $conditions[] = new PropertyCondition([
            $values[4]['id'],
            $values[5]['id'],
        ]);

        $this->search(
            [
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth,
            ],
            ['first', 'second'],
            null,
            $conditions
        );
    }

    public function testTwoPropertyConditionsWithThreeValues(): void
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first = $this->createPropertyCombination($properties, [0, 4]);
        $second = $this->createPropertyCombination($properties, [1, 5]);
        $third = $this->createPropertyCombination($properties, [2, 6]);
        $fourth = $this->createPropertyCombination($properties, [3, 5]);

        $conditions = [];

        $conditions[] = new PropertyCondition([
            $values[0]['id'],
            $values[1]['id'],
            $values[2]['id'],
        ]);

        $conditions[] = new PropertyCondition([
            $values[4]['id'],
            $values[5]['id'],
            $values[6]['id'],
        ]);

        $this->search(
            [
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth,
            ],
            ['first', 'second', 'third'],
            null,
            $conditions
        );
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        string $number,
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
