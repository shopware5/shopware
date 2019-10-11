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
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class VariantConditionOnSaleTest extends TestCase
{
    private $groups = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setConfig('hideNoInStock', true);
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
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 40],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [0, 10, 10, 10],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']]),
                    'graduationPrices' => [
                        [60, 40],
                    ],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl']]),
                    'graduationPrices' => [
                        [100, 50],
                        [110, 20],
                    ],
                    'inStock' => [10, 0],
                ],
            ],
            ['B1', 'A2', 'C1'],
            null,
            [$condition],
            [],
            [$sorting],
            null,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );

        $this->assertPrices(
            $result->getProducts(),
            [
                'A2' => 30,
                'B1' => 40,
                'C1' => 50,
            ]
        );

        $this->assertSearchResultSorting($result, ['A2', 'B1', 'C1']);
    }

    public function testSingleNotExpandOptionWithLastStockSortByPrice()
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
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]),
                    'graduationPrices' => [
                        [80, 60],
                        [60, 40],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [
                        0,
                        10,
                        0,
                        0,
                    ],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']]),
                    'graduationPrices' => [
                        [60, 50],
                    ],
                    'inStock' => [
                        0,
                        0,
                    ],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl']]),
                    'graduationPrices' => [
                        [100, 50],
                        [110, 20],
                    ],
                ],
            ],
            ['A2', 'C1'],
            null,
            [$condition],
            [],
            [$sorting],
            null,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );

        $this->assertPrices(
            $result->getProducts(),
            [
                'A2' => 40,
                'C1' => 20,
            ]
        );

        $this->assertSearchResultSorting($result, ['C1', 'A2']);
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
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 40],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [0, 0, 0],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 40],
                    ],
                    'inStock' => [10, 0],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 10],
                    ],
                ],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 20],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [0, 10, 0, 0],
                ],
            ],
            ['A4', 'D2'],
            null,
            [$conditionColor, $conditionSize],
            [],
            [$sorting],
            null,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );

        $this->assertPrices(
            $result->getProducts(),
            [
                'A4' => 40,
                'D2' => 20,
            ]
        );

        $this->assertSearchResultSorting($result, ['D2', 'A4']);
    }

    public function testMultiNotExpandOptionSortByPriceWithMultipleCustomerGroups()
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

        $context = $this->getContext();
        $context->getCurrentCustomerGroup()->setKey('EK');
        $context->getCurrentCustomerGroup()->setId(1);

        $result = $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 40],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [0, 0, 0],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 40],
                    ],
                    'inStock' => [10, 0],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 10],
                    ],
                ],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 20],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [0, 10, 0, 0],
                ],
            ],
            ['A4', 'D2'],
            null,
            [$conditionColor, $conditionSize],
            [],
            [$sorting],
            $context,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );

        static::assertNotEmpty($result);
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
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 20],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [10, 0, 0, 10],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']]),
                    'graduationPrices' => [
                        [60, 55],
                        [60, 20],
                    ],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 20],
                    ],
                ],
            ],
            ['A1', 'A4', 'B1'],
            null,
            [$condition],
            [],
            [$sorting],
            null,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );

        $this->assertPrices(
            $result->getProducts(),
            [
                'A1' => 50,
                'A4' => 40,
                'B1' => 55,
            ]
        );

        $this->assertSearchResultSorting($result, ['A4', 'A1', 'B1']);
    }

    public function testSingleExpandOptionWithLastStockSortByPrice()
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
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 20],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [10, 0, 0, 0],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']]),
                    'graduationPrices' => [
                        [60, 55],
                    ],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 20],
                    ],
                ],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]),
                    'graduationPrices' => [
                        [60, 51],
                        [60, 20],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [20, 0, 0, 20],
                ],
                'E' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]),
                    'graduationPrices' => [
                        [90, 50],
                        [90, 20],
                        [70, 30],
                        [80, 40],
                    ],
                    'inStock' => [0, 20, 0, 20],
                ],
            ],
            ['A1', 'B1', 'D1', 'D4', 'E2'],
            null,
            [$condition],
            [],
            [$sorting],
            null,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );

        $this->assertPrices(
            $result->getProducts(),
            [
                'A1' => 50,
                'B1' => 55,
                'D1' => 51,
                'D4' => 40,
                'E2' => 20,
            ]
        );

        $this->assertSearchResultSorting($result, ['E2', 'D4', 'A1', 'D1', 'B1']);
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
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 20],
                        [100, 30],
                        [80, 40],
                    ],
                    'inStock' => [0, 10, 0, 10],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]),
                    'graduationPrices' => [
                        [120, 40],
                        [88, 23],
                    ],
                    'inStock' => [10, 0],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]),
                    'graduationPrices' => [
                        [55, 45],
                        [55, 15],
                    ],
                ],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]),
                    'graduationPrices' => [
                        [88, 22],
                        [66, 11],
                        [25, 19],
                        [99, 66],
                        [69, 18],
                        [77, 66],
                        [55, 44],
                        [100, 50],
                    ],
                    'inStock' => [10, 10, 10, 10, 10, 0],
                ],
            ],
            [
                'A2', 'A4',
                'D5',
            ],
            null,
            [$conditionColor, $conditionSize],
            [],
            [$sorting],
            null,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );

        $this->assertPrices(
            $result->getProducts(),
            [
                'A2' => 20,
                'A4' => 40,
                'D5' => 18,
            ]
        );

        $this->assertSearchResultSorting($result, ['D5', 'A2', 'A4']);
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
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['l', 'xl']]),
                    'graduationPrices' => [
                        [88, 22],
                        [66, 20],
                        [25, 11],
                        [99, 10],
                    ],
                    'inStock' => [10, 0, 0, 0],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['s', 'xl']]),
                    'graduationPrices' => [
                        [88, 22],
                        [66, 11],
                    ],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green']]),
                    'graduationPrices' => [
                        [88, 22],
                        [66, 11],
                    ],
                ],
                'D' => ['groups' => $this->buildConfigurator(['color' => ['blue', 'green'], 'size' => ['s', 'l', 'xl']]),
                    'graduationPrices' => [
                        [88, 22],
                        [66, 11],
                        [25, 19],
                        [99, 66],
                        [69, 18],
                        [77, 5],
                    ],
                    'inStock' => [0, 0, 10, 0, 0, 0],
                ],
            ],
            ['A1', 'D3'],
            null,
            [$conditionColor, $conditionSize],
            [],
            [$sorting],
            null,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );

        $this->assertPrices(
            $result->getProducts(),
            [
                'A1' => 22,
                'D3' => 19,
            ]
        );

        $this->assertSearchResultSorting($result, ['D3', 'A1']);
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

        $variants = array_merge([
            'prices' => $this->helper->getGraduatedPrices($context->getCurrentCustomerGroup()->getKey()),
        ], $this->helper->getUnitData());

        $variants = $this->helper->generateVariants(
            $configurator['groups'],
            $number,
            $variants
        );

        if (isset($data['graduationPrices'])) {
            $variantCount = 0;
            foreach ($variants as &$variant) {
                if (isset($data['inStock'][$variantCount])) {
                    $variant['inStock'] = $data['inStock'][$variantCount];
                }

                if (isset($data['graduationPrices'][$variantCount])) {
                    $variant['prices'] = [];
                    $priceCount = 0;
                    foreach ($data['graduationPrices'][$variantCount] as $graduationPrice) {
                        ++$priceCount;
                        $variant['prices'][] = [
                            'from' => $priceCount,
                            'to' => $priceCount + 9,
                            'price' => $graduationPrice,
                            'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey(),
                            'pseudoPrice' => $graduationPrice + 110,
                        ];
                        $priceCount += 9;
                    }
                    $variant['prices'][count($variant['prices']) - 1]['to'] = 'beliebig';
                }

                ++$variantCount;
            }
        }

        if (isset($variants[0]) && isset($variants[0]['prices'])) {
            $product['mainDetail']['prices'] = $variants[0]['prices'];
        }

        $product['configuratorSet'] = $configurator;
        $product['variants'] = $variants;

        return $product;
    }

    /**
     * Assert the cheapest and pseudo prices of the products / variants.
     *
     * @param ListProduct[] $products
     */
    private function assertPrices(array $products, array $prices)
    {
        foreach ($products as $product) {
            $number = $product->getNumber();
            if (!isset($prices[$number])) {
                continue;
            }

            static::assertEquals($prices[$number], $product->getCheapestPrice()->getCalculatedPrice());
        }
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
            /* @var Group[] $allGroups */
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
