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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestContext;

class VariantConditionWithCurrencyFactor extends TestCase
{
    /**
     * @var Group[]
     */
    private array $groups = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setConfig('hideNoInStock', false);
    }

    public function testCustomerGroupDiscount(): void
    {
        $context = $this->getContext();
        $result = $this->getSearchResult($context);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 24,
                    'B1' => 40,
                    'C1' => 16,
                ],
                'pseudoPrice' => [
                    'A1' => 104,
                    'B1' => 120,
                    'C1' => 96,
                ],
            ]
        );
    }

    public function testExpandedCustomerGroupDiscount(): void
    {
        $context = $this->getContext();
        $result = $this->getSearchResult($context, true);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 24,
                    'A2' => 32,
                    'B1' => 40,
                    'C1' => 16,
                ],
                'pseudoPrice' => [
                    'A1' => 104,
                    'A2' => 112,
                    'B1' => 120,
                    'C1' => 96,
                ],
            ]
        );
    }

    public function testNetPrices(): void
    {
        $context = $this->getContext(false);
        $result = $this->getSearchResult($context);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 20.17,
                    'B1' => 33.61,
                    'C1' => 13.45,
                ],
                'pseudoPrice' => [
                    'A1' => 87.39,
                    'B1' => 100.84,
                    'C1' => 80.67,
                ],
            ]
        );
    }

    public function testExpandedNetPrices(): void
    {
        $context = $this->getContext(false);
        $result = $this->getSearchResult($context, true);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 20.17,
                    'A2' => 26.89,
                    'B1' => 33.61,
                    'C1' => 13.45,
                ],
                'pseudoPrice' => [
                    'A1' => 87.39,
                    'A2' => 94.12,
                    'B1' => 100.84,
                    'C1' => 80.67,
                ],
            ]
        );
    }

    public function testCurrencyFactor(): void
    {
        $context = $this->getContext(true, 0, 1.2);
        $result = $this->getSearchResult($context);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 36,
                    'B1' => 60,
                    'C1' => 24,
                ],
                'pseudoPrice' => [
                    'A1' => 156,
                    'B1' => 180,
                    'C1' => 144,
                ],
            ]
        );
    }

    public function testExpandedCurrencyFactor(): void
    {
        $context = $this->getContext(true, 0, 1.2);
        $result = $this->getSearchResult($context, true);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 36,
                    'A2' => 48,
                    'B1' => 60,
                    'C1' => 24,
                ],
                'pseudoPrice' => [
                    'A1' => 156,
                    'A2' => 168,
                    'B1' => 180,
                    'C1' => 144,
                ],
            ]
        );
    }

    public function testDiscountCurrencyNet(): void
    {
        $context = $this->getContext(false, 30, 1.2);
        $result = $this->getSearchResult($context);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 21.18,
                    'B1' => 35.29,
                    'C1' => 14.12,
                ],
                'pseudoPrice' => [
                    'A1' => 91.76,
                    'B1' => 105.88,
                    'C1' => 84.71,
                ],
            ]
        );
    }

    public function testExpandedDiscountCurrencyNet(): void
    {
        $context = $this->getContext(false, 30, 1.2);
        $result = $this->getSearchResult($context, true);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 21.18,
                    'A2' => 28.24,
                    'B1' => 35.29,
                    'C1' => 14.12,
                ],
                'pseudoPrice' => [
                    'A1' => 91.76,
                    'A2' => 98.82,
                    'B1' => 105.88,
                    'C1' => 84.71,
                ],
            ]
        );
    }

    public function testDiscountCurrencyGross(): void
    {
        $context = $this->getContext(true, 15, 1.44);
        $result = $this->getSearchResult($context);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 36.72,
                    'B1' => 61.20,
                    'C1' => 24.48,
                ],
                'pseudoPrice' => [
                    'A1' => 159.12,
                    'B1' => 183.6,
                    'C1' => 146.88,
                ],
            ]
        );
    }

    public function testExpandedDiscountCurrencyGross(): void
    {
        $context = $this->getContext(true, 15, 1.44);
        $result = $this->getSearchResult($context, true);

        $this->assertPrices(
            $result->getProducts(),
            [
                'cheapestPrice' => [
                    'A1' => 36.72,
                    'A2' => 48.96,
                    'B1' => 61.20,
                    'C1' => 24.48,
                ],
                'pseudoPrice' => [
                    'A1' => 159.12,
                    'A2' => 171.36,
                    'B1' => 183.6,
                    'C1' => 146.88,
                ],
            ]
        );
    }

    /**
     * Creates and return the VariantCondition of the given options of the given group.
     */
    public function createCondition(array $options, string $groupName, bool $expand = false): VariantCondition
    {
        $mapping = $this->mapOptions();

        $ids = array_intersect_key(
            $mapping['options'],
            array_flip($options)
        );

        return new VariantCondition(array_values($ids), $expand, $mapping['groups'][$groupName]);
    }

    /**
     * Creates the TestContext with the given configurations.
     *
     * @param bool $displayGross
     */
    protected function getContext($displayGross = true, int $discount = 20, int $currencyFactor = 1): TestContext
    {
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup(
            [
                'key' => 'DISC',
                'tax' => $displayGross,
                'mode' => true,
                'discount' => $discount,
            ]
        );

        $currency = $this->helper->createCurrency(
            [
                'factor' => $currencyFactor,
            ]
        );

        $shop = $this->helper->getShop();

        return $this->helper->createContext(
            $customerGroup,
            $shop,
            [$tax],
            null,
            $currency
        );
    }

    /**
     * Get products and set the graduated prices and inStock of the variants.
     *
     * @param string $number
     * @param array  $data
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $data = []
    ): array {
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
                            'pseudoPrice' => $graduationPrice + 100,
                        ];
                        $priceCount += 9;
                    }
                    $variant['prices'][\count($variant['prices']) - 1]['to'] = 'beliebig';
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

    private function getSearchResult(TestContext $context, $expanded = false): ProductNumberSearchResult
    {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $condition = $this->createCondition(['xl', 'l'], 'size', $expanded);

        $expected = ['B1', 'A1', 'C1'];
        if ($expanded) {
            $expected = ['A1', 'A2', 'B1', 'C1'];
        }

        return $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]),
                    'graduationPrices' => [
                        [60, 50],
                        [60, 40],
                        [70, 30],
                        [80, 40],
                    ],
                ],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']]),
                    'graduationPrices' => [
                        [60, 50],
                    ],
                ],
                'C' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl']]),
                    'graduationPrices' => [
                        [100, 50],
                        [110, 20],
                    ],
                ],
            ],
            $expected,
            null,
            [$condition],
            [],
            [],
            $context,
            ['useLastGraduationForCheapestPrice' => true],
            true
        );
    }

    /**
     * Returns the mapping of group and option names to ids.
     */
    private function mapOptions(): array
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
     * @return Group[]
     */
    private function buildConfigurator(array $expected): array
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
                    if (\in_array($option->getName(), $optionNames, true)) {
                        $options[] = $option;
                    }
                }

                $clone = clone $globalGroup;
                $clone->setOptions(new ArrayCollection($options));

                $groups[] = $clone;
            }
        }

        return $groups;
    }

    /**
     * Assert the cheapest and pseudo prices of the products / variants.
     *
     * @param ListProduct[]|BaseProduct[] $products
     */
    private function assertPrices(array $products, array $prices): void
    {
        $this->assertPriceCount($products, $prices);

        foreach ($products as $product) {
            static::assertInstanceOf(ListProduct::class, $product);
            $number = $product->getNumber();
            $cheapestPrice = $product->getCheapestPrice();
            static::assertNotNull($cheapestPrice);
            if (isset($prices['cheapestPrice'][$number])) {
                static::assertEquals($prices['cheapestPrice'][$number], $cheapestPrice->getCalculatedPrice());
            }

            if (isset($prices['pseudoPrice'][$number])) {
                static::assertEquals($prices['pseudoPrice'][$number], $cheapestPrice->getCalculatedPseudoPrice());
            }
        }
    }

    /**
     * Assert the counting of the cheapest and pseudo prices.
     *
     * @param BaseProduct[]        $products
     * @param array<string, mixed> $prices
     */
    private function assertPriceCount(
        array $products,
        array $prices
    ): void {
        $numbers = array_map(function (ListProduct $product) {
            return $product->getNumber();
        }, $products);

        $expectedCheapestPriceNumbers = array_keys($prices['cheapestPrice']);

        foreach ($numbers as $number) {
            static::assertContains($number, $expectedCheapestPriceNumbers, sprintf('Cheapest price of product with number: `%s` found but not expected', $number));
        }
        foreach ($expectedCheapestPriceNumbers as $number) {
            static::assertContains($number, $numbers, sprintf('Expected cheapest price of product with number: `%s` not found', $number));
        }

        static::assertCount(\count($expectedCheapestPriceNumbers), $products);

        // Pseudo prices
        $expectedPseudoPriceNumbers = array_keys($prices['pseudoPrice']);

        foreach ($numbers as $number) {
            static::assertContains($number, $expectedPseudoPriceNumbers, sprintf('Pseudo price of product with number: `%s` found but not expected', $number));
        }
        foreach ($expectedPseudoPriceNumbers as $number) {
            static::assertContains($number, $numbers, sprintf('Expected pseudo price of product with number: `%s` not found', $number));
        }

        static::assertCount(\count($expectedPseudoPriceNumbers), $products);
    }
}
