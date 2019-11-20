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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class VariantConditionTest extends TestCase
{
    private $groups = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setConfig('hideNoInStock', false);
    }

    public function testSingleNotExpandOption()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $condition = $this->createCondition(['xl', 'l'], 'size');

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']])],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']])],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green']])],
            ],
            ['A1', 'B1'],
            null,
            [$condition]
        );
    }

    public function testSingleNotExpandOptionSortByPrice()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $condition = $this->createCondition(['xl', 'l'], 'size');
        $sorting = new PriceSorting();
        $sorting->setDirection(PriceSorting::SORT_ASC);

        $result = $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]), 'priceOffset' => [50, 40, 30, 20]],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']]), 'priceOffset' => [10]],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green']])],
            ],
            ['B1', 'A1'],
            null,
            [$condition],
            [],
            [$sorting]
        );

        $this->assertSearchResultSorting($result, ['B1', 'A1']);
    }

    public function testMultiNotExpandOption()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green', 'blue'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'green'], 'color');
        $conditionSize = $this->createCondition(['l', 'xl'], 'size');

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']])],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']])],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']])],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']])],
            ],
            ['A1', 'B2', 'D2'],
            null,
            [$conditionColor, $conditionSize]
        );
    }

    public function testMultiNotExpandOptionSortByPrice()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green', 'blue'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'green'], 'color');
        $conditionSize = $this->createCondition(['l', 'xl'], 'size');
        $sorting = new PriceSorting();
        $sorting->setDirection(PriceSorting::SORT_ASC);

        $result = $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'priceOffset' => [40, 50, 30, 80]],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]), 'priceOffset' => [100, 80]],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]), 'priceOffset' => [33, 22]],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]), 'priceOffset' => [10, 20, 30, 40, 50, 60, 70, 80]],
            ],
            ['A1', 'B2', 'D2'],
            null,
            [$conditionColor, $conditionSize],
            [],
            [$sorting]
        );

        $this->assertSearchResultSorting($result, ['D2', 'A1', 'B2']);
    }

    public function testSingleExpandOption()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $condition = $this->createCondition(['xl', 'l'], 'size', true);

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']])],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']])],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green']])],
            ],
            ['A1', 'A2', 'B1'],
            null,
            [$condition]
        );
    }

    public function testSingleExpandOptionSortByPrice()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $condition = $this->createCondition(['xl', 'l'], 'size', true);
        $sorting = new PriceSorting();
        $sorting->setDirection(PriceSorting::SORT_ASC);

        $result = $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]), 'priceOffset' => [60, 50, 70, 80]],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']]), 'priceOffset' => [10]],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green']]), 'priceOffset' => [20, 30]],
            ],
            ['A1', 'A2', 'B1'],
            null,
            [$condition],
            [],
            [$sorting]
        );

        $this->assertSearchResultSorting($result, ['B1', 'A2', 'A1']);
    }

    public function testMultiExpandOption()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'blue', 'green'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'green'], 'color', true);
        $conditionSize = $this->createCondition(['l', 'xl'], 'size', true);

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']])],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']])],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']])],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']])],
            ],
            [
                'A1', 'A2', 'A3', 'A4',
                'B2',
                'D5', 'D6',
            ],
            null,
            [$conditionColor, $conditionSize]
        );
    }

    public function testMultiExpandOptionSortByPrice()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'blue', 'green'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'green'], 'color', true);
        $conditionSize = $this->createCondition(['l', 'xl'], 'size', true);
        $sorting = new PriceSorting();
        $sorting->setDirection(PriceSorting::SORT_ASC);

        $result = $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'priceOffset' => [55, 40, 45, 65]],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]), 'priceOffset' => [33, 44]],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]), 'priceOffset' => [20, 30]],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]), 'priceOffset' => [20, 30, 40, 50, 60, 70, 80]],
            ],
            [
                'A1', 'A2', 'A3', 'A4',
                'B2',
                'D5', 'D6',
            ],
            null,
            [$conditionColor, $conditionSize],
            [],
            [$sorting]
        );

        $this->assertSearchResultSorting($result, ['A2', 'B2', 'A3', 'A1', 'D5', 'A4', 'D6']);
    }

    public function testMultiCrossExpandOption()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'blue', 'green'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'blue'], 'color', true);
        $conditionSize = $this->createCondition(['l', 'xl'], 'size');

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']])],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']])],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']])],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']])],
            ],
            ['A1', 'D2'],
            null,
            [$conditionColor, $conditionSize]
        );
    }

    public function testMultiCrossExpandOptionSortByPrice()
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'blue', 'green'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'blue'], 'color', true);
        $conditionSize = $this->createCondition(['l', 'xl'], 'size');
        $sorting = new PriceSorting();
        $sorting->setDirection(PriceSorting::SORT_ASC);

        $result = $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'priceOffset' => [90, 85, 75, 65]],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]), 'priceOffset' => [60, 50]],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]), 'priceOffset' => [35, 45]],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]), 'priceOffset' => [11, 21, 31, 41, 51, 61, 71, 81]],
            ],
            ['A1', 'D2'],
            null,
            [$conditionColor, $conditionSize],
            [],
            [$sorting]
        );

        $this->assertSearchResultSorting($result, ['D2', 'A1']);
    }

    /**
     * Creates and return the VariantCondition of the given options of the given group.
     *
     * @param array  $options
     * @param string $groupName
     * @param bool   $expand
     *
     * @return VariantCondition
     */
    public function createCondition($options, $groupName, $expand = false)
    {
        $mapping = $this->mapOptions();

        $ids = array_intersect_key(
            $mapping['options'],
            array_flip($options)
        );

        return new VariantCondition(array_values($ids), $expand, $mapping['groups'][$groupName]);
    }

    /**
     * Get products and set the graduated prices of the variants.
     *
     * @param string   $number
     * @param Category $category
     * @param array    $data
     *
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $data = []
    ) {
        $product = parent::getProduct($number, $context, $category);

        $configurator = $this->helper->createConfiguratorSet($data['groups']);

        $variants = array_merge([
            'prices' => $this->helper->getGraduatedPrices($context->getCurrentCustomerGroup()->getKey()),
        ], $this->helper->getUnitData());

        $variants = $this->helper->generateVariants(
            $configurator['groups'],
            $number,
            $variants
        );

        $i = 0;
        foreach ($variants as &$variant) {
            if (!isset($data['priceOffset'][$i])) {
                continue;
            }

            $priceOffset = $data['priceOffset'][$i];
            $variant['prices'] = $this->helper->getGraduatedPrices($context->getCurrentCustomerGroup()->getKey(), $priceOffset);
            ++$i;
        }

        if (isset($variants[0]) && isset($variants[0]['prices'])) {
            $product['mainDetail']['prices'] = $variants[0]['prices'];
        }

        $product['configuratorSet'] = $configurator;
        $product['variants'] = $variants;

        return $product;
    }

    /**
     * Returns the mapping of group and option names to ids.
     *
     * @return array
     */
    private function mapOptions()
    {
        $mapping = [];
        foreach ($this->groups as $group) {
            $mapping['groups'][$group->getName()] = $group->getId();
            foreach ($group->getOptions() as $option) {
                $mapping['options'][$option->getName()] = $option->getId();
            }
        }

        return $mapping;
    }

    /**
     * Creates the structure of the configurator.
     *
     * @param array $expected
     *
     * @return Group[]
     */
    private function buildConfigurator($expected)
    {
        $groups = [];
        foreach ($expected as $group => $optionNames) {
            /* @var $allGroups Group[] */
            foreach ($this->groups as $globalGroup) {
                if ($globalGroup->getName() !== $group) {
                    continue;
                }

                $options = [];
                foreach ($globalGroup->getOptions() as $option) {
                    if (in_array($option->getName(), $optionNames, true)) {
                        $options[] = $option;
                    }
                }

                $clone = clone $globalGroup;
                $clone->setOptions($options);

                $groups[] = $clone;
            }
        }

        return $groups;
    }
}
