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

use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class VariantConditionWithPriceGroupTest extends TestCase
{
    private $groups = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setConfig('hideNoInStock', false);
    }

    public function testPriceGroupWithSingleNotExpandOption()
    {
        $priceCondition = new PriceCondition(18, 18);
        $context = $this->getContext();

        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $variantCondition = $this->createCondition(['xl', 'l'], 'size');

        $priceGroup = $this->helper->createPriceGroup([
            ['key' => $context->getCurrentCustomerGroup()->getKey(), 'quantity' => 1,  'discount' => 10],
        ]);

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [10, 20, 30, 50], 'priceGroup' => $priceGroup],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [20, 40, 60, 90], 'priceGroup' => $priceGroup],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [10, 100, 50, 110], 'priceGroup' => $priceGroup],
            ],
            ['B1'],
            null,
            [$variantCondition, $priceCondition],
            [],
            [],
            $context,
            ['useLastGraduationForCheapestPrice' => false]
        );
    }

    public function testPriceGroupWithMultiNotExpandOption()
    {
        $priceCondition = new PriceCondition(18, 18);
        $context = $this->getContext();

        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'blue', 'green'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'green'], 'color');
        $conditionSize = $this->createCondition(['l', 'xl'], 'size');

        $priceGroup = $this->helper->createPriceGroup([
            ['key' => $context->getCurrentCustomerGroup()->getKey(), 'quantity' => 1,  'discount' => 10],
        ]);

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [10, 20, 30, 50], 'priceGroup' => $priceGroup],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]), 'variantPrices' => [20, 40, 60], 'priceGroup' => $priceGroup],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]), 'variantPrices' => [10, 100], 'priceGroup' => $priceGroup],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]), 'variantPrices' => [50, 30, 20], 'priceGroup' => $priceGroup],
            ],
            ['B2', 'D5'],
            null,
            [$conditionColor, $conditionSize, $priceCondition],
            [],
            [],
            $context,
            ['useLastGraduationForCheapestPrice' => false]
        );
    }

    public function testPriceGroupWithSingleExpandOption()
    {
        $priceCondition = new PriceCondition(18, 18);
        $context = $this->getContext();

        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $variantCondition = $this->createCondition(['xl', 'l'], 'size', true);

        $priceGroup = $this->helper->createPriceGroup([
            ['key' => $context->getCurrentCustomerGroup()->getKey(), 'quantity' => 1,  'discount' => 10],
        ]);

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [10, 20, 30, 50], 'priceGroup' => $priceGroup],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [20, 40, 60, 90], 'priceGroup' => $priceGroup],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [10, 100, 50, 110], 'priceGroup' => $priceGroup],
            ],
            ['A2', 'B1'],
            null,
            [$variantCondition, $priceCondition],
            [],
            [],
            $context,
            ['useLastGraduationForCheapestPrice' => false]
        );
    }

    public function testPriceGroupWithMultiExpandOption()
    {
        $priceCondition = new PriceCondition(18, 18);
        $context = $this->getContext();

        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'blue', 'green'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'green'], 'color', true);
        $conditionSize = $this->createCondition(['l', 'xl'], 'size', true);

        $priceGroup = $this->helper->createPriceGroup([
            ['key' => $context->getCurrentCustomerGroup()->getKey(), 'quantity' => 1,  'discount' => 10],
        ]);

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [10, 20, 30, 50], 'priceGroup' => $priceGroup],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]), 'variantPrices' => [20, 40, 60], 'priceGroup' => $priceGroup],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]), 'variantPrices' => [20, 100], 'priceGroup' => $priceGroup],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]), 'variantPrices' => [50, 30, 20, 100, 200, 20, 150, 125], 'priceGroup' => $priceGroup],
            ],
            ['A2', 'D6'],
            null,
            [$conditionColor, $conditionSize, $priceCondition],
            [],
            [],
            $context,
            ['useLastGraduationForCheapestPrice' => false]
        );
    }

    public function testPriceGroupWithMultiCrossExpandOption()
    {
        $priceCondition = new PriceCondition(18, 18);
        $context = $this->getContext();
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'blue', 'green'],
                'size' => ['s', 'm', 'l', 'xl'],
            ]
        );

        $conditionColor = $this->createCondition(['red', 'blue'], 'color', true);
        $conditionSize = $this->createCondition(['l', 'xl'], 'size');

        $priceGroup = $this->helper->createPriceGroup([
            ['key' => $context->getCurrentCustomerGroup()->getKey(), 'quantity' => 1,  'discount' => 10],
        ]);

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]), 'variantPrices' => [100, 20, 150, 30], 'priceGroup' => $priceGroup],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]), 'variantPrices' => [88, 20, 30, 50], 'priceGroup' => $priceGroup],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['xl']]), 'variantPrices' => [18, 80, 70, 80]],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]), 'variantPrices' => [40, 30, 20, 50], 'priceGroup' => $priceGroup],
            ],
            ['A1', 'D2', 'C1'],
            null,
            [$conditionColor, $conditionSize, $priceCondition],
            [],
            [],
            $context,
            ['useLastGraduationForCheapestPrice' => false]
        );
    }

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
     * Get products and set the graduated prices and inStock of the variants.
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

        $variants = $this->helper->generateVariants(
            $configurator['groups'],
            $number
        );

        $i = 0;
        foreach ($variants as &$variant) {
            if (!isset($data['variantPrices'][$i])) {
                continue;
            }

            $variant['prices'][] = $this->getPriceData($data['variantPrices'][$i], $context->getCurrentCustomerGroup()->getKey());
            ++$i;
        }

        if ($data['priceGroup']) {
            $product['priceGroupActive'] = true;
            $product['priceGroupId'] = $data['priceGroup']->getId();
        }

        $product['configuratorSet'] = $configurator;
        $product['variants'] = $variants;

        return $product;
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
            /*
             * @var $allGroups Group[]
             */
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
     * Returns the price data for a variant.
     *
     * @param int    $price
     * @param string $group
     *
     * @return array
     */
    private function getPriceData($price, $group)
    {
        return [
            'from' => 1,
            'to' => 'beliebig',
            'price' => $price,
            'customerGroupKey' => $group,
            'pseudoPrice' => $price + 10,
        ];
    }
}
