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

namespace Shopware\Tests\Unit\Components\Cart;

use Doctrine\DBAL\Connection;
use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\CartPositionsMode;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Cart\Struct\Price;
use Shopware\Components\Cart\TaxAggregator;
use Shopware\Models\Tax\Tax;

class TaxAggregatorTest extends TestCase
{
    private const REGULAR_TAX_RATE = 19.0;
    private const MAXIMUM_TAX_RATE = 21.0;
    private const DISCOUNT_TAX_RATE = 17.0;

    private const SHIPPING_COSTS_TAX_RATE = self::REGULAR_TAX_RATE;
    private const SHIPPING_COSTS_NET = 4.59;
    private const SHIPPING_COSTS_WITH_TAX = self::SHIPPING_COSTS_NET * (self::SHIPPING_COSTS_TAX_RATE / 100);

    /**
     * @dataProvider positionsTaxSumDataProvider
     *
     * @param array<float>|null         $expected
     * @param array<string, mixed>|null $positions
     */
    public function testPositionsTaxSum(
        ?array $expected,
        float $maximumTaxRate,
        ?array $positions = null,
        ?string $taxAutoModeConfig = null,
        ?string $discountTaxConfig = null
    ): void {
        $subject = new TaxAggregator(
            static::createConfiguredMock(
                Connection::class,
                [
                    'fetchOne' => 'none',
                ]
            ),
            static::createStub(ContextServiceInterface::class),
            null,
            $discountTaxConfig,
            $taxAutoModeConfig
        );
        $basket = [];

        if ($positions) {
            $basket['content'] = $positions;
        }

        static::assertSame(
            $expected,
            $subject->positionsTaxSum($basket, $maximumTaxRate)
        );
    }

    /**
     * @return Generator<array>
     */
    public function positionsTaxSumDataProvider(): Generator
    {
        yield 'No positions' => [
            null,
            self::MAXIMUM_TAX_RATE,
        ];

        yield 'Item with "tax_rate" set' => [
            [
                sprintf('%.2f', self::REGULAR_TAX_RATE) => 1.1,
            ],
            self::MAXIMUM_TAX_RATE,
            [
                [
                    'tax_rate' => self::REGULAR_TAX_RATE,
                    'tax' => 1.1,
                ],
            ],
        ];

        yield 'Item with "taxPercent" set' => [
            [
                sprintf('%.2f', self::REGULAR_TAX_RATE) => 1.1,
            ],
            self::MAXIMUM_TAX_RATE,
            [
                [
                    'taxPercent' => self::REGULAR_TAX_RATE,
                    'tax' => 1.1,
                ],
            ],
        ];

        yield 'Voucher item' => [
            null,
            self::MAXIMUM_TAX_RATE,
            [
                [
                    'modus' => CartPositionsMode::VOUCHER,
                    'ordernumber' => '892cc16b-0394-4896-8319-9d5803515cef',
                ],
            ],
        ];

        yield 'Regular item without a tax rate' => [
            null,
            self::MAXIMUM_TAX_RATE,
            [
                [
                    'modus' => CartPositionsMode::PRODUCT,
                ],
            ],
        ];

        foreach ([true, false] as $taxAutoModeConfig) {
            yield sprintf('Regular item without "tax_rate", taxAutoMode: %d', $taxAutoModeConfig) => [
                [
                    sprintf('%.2f', $taxAutoModeConfig ? self::MAXIMUM_TAX_RATE : self::DISCOUNT_TAX_RATE) => 1.1,
                ],
                self::MAXIMUM_TAX_RATE,
                [
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax' => 1.1,
                    ],
                ],
                $taxAutoModeConfig ? '1' : null,
                sprintf('%.2f', self::DISCOUNT_TAX_RATE),
            ];
        }

        yield 'Multiple items with "tax_rate" set' => [
            [
                sprintf('%.2f', self::REGULAR_TAX_RATE) => self::DISCOUNT_TAX_RATE * 4,
            ],
            self::MAXIMUM_TAX_RATE,
            [
                [
                    'tax_rate' => self::REGULAR_TAX_RATE,
                    'tax' => self::DISCOUNT_TAX_RATE,
                ],
                [
                    'tax_rate' => self::REGULAR_TAX_RATE,
                    'tax' => self::DISCOUNT_TAX_RATE,
                ],
                [
                    'tax_rate' => self::REGULAR_TAX_RATE,
                    'tax' => self::DISCOUNT_TAX_RATE,
                ],
                [
                    'tax_rate' => self::REGULAR_TAX_RATE,
                    'tax' => self::DISCOUNT_TAX_RATE,
                ],
            ],
        ];
    }

    /**
     * @dataProvider shippingCostsTaxSumDataProvider
     *
     * @param array<float>|null $expected
     * @param array<Price>|null $shippingCostsTaxProportional
     */
    public function testShippingCostsTaxSum(
        ?array $expected = null,
        ?float $shippingCostsTax = null,
        ?array $shippingCostsTaxProportional = null
    ): void {
        $subject = new TaxAggregator(
            static::createStub(Connection::class),
            static::createStub(ContextServiceInterface::class)
        );

        $basket = [
            'sShippingcostsNet' => self::SHIPPING_COSTS_NET,
            'sShippingcostsWithTax' => self::SHIPPING_COSTS_WITH_TAX,
        ];

        if ($shippingCostsTax !== null) {
            $basket['sShippingcostsTax'] = $shippingCostsTax;
        }

        if ($shippingCostsTaxProportional !== null) {
            $basket['sShippingcostsTaxProportional'] = $shippingCostsTaxProportional;
        }

        static::assertSame(
            $expected,
            $subject->shippingCostsTaxSum($basket)
        );
    }

    /**
     * @return Generator<array>
     */
    public function shippingCostsTaxSumDataProvider(): Generator
    {
        yield 'No shipping costs tax rate' => [];

        yield 'Proportional tax calculation' => [
            [
                sprintf('%.2f', self::SHIPPING_COSTS_TAX_RATE) => 1.0,
            ],
            self::SHIPPING_COSTS_TAX_RATE,
            [
                static::createConfiguredMock(
                    Price::class,
                    [
                        'getTaxRate' => self::SHIPPING_COSTS_TAX_RATE,
                        'getTax' => 1.0,
                    ]
                ),
            ],
        ];

        yield 'Regular tax calculation' => [
            [
                sprintf('%.2f', self::SHIPPING_COSTS_TAX_RATE) => self::SHIPPING_COSTS_WITH_TAX - self::SHIPPING_COSTS_NET,
            ],
            self::SHIPPING_COSTS_TAX_RATE,
        ];

        yield 'Proportional tax calculation with multiple positions and tax rates' => [
            [
                sprintf('%.2f', self::SHIPPING_COSTS_TAX_RATE - 1) => 1.1,
                sprintf('%.2f', self::SHIPPING_COSTS_TAX_RATE) => 2.2,
                sprintf('%.2f', self::SHIPPING_COSTS_TAX_RATE + 1) => 2.2,
            ],
            self::SHIPPING_COSTS_TAX_RATE,
            [
                static::createConfiguredMock(
                    Price::class,
                    [
                        'getTaxRate' => self::SHIPPING_COSTS_TAX_RATE,
                        'getTax' => 1.1,
                    ]
                ),
                static::createConfiguredMock(
                    Price::class,
                    [
                        'getTaxRate' => self::SHIPPING_COSTS_TAX_RATE,
                        'getTax' => 1.1,
                    ]
                ),
                static::createConfiguredMock(
                    Price::class,
                    [
                        'getTaxRate' => self::SHIPPING_COSTS_TAX_RATE + 1,
                        'getTax' => 1.1,
                    ]
                ),
                static::createConfiguredMock(
                    Price::class,
                    [
                        'getTaxRate' => self::SHIPPING_COSTS_TAX_RATE + 1,
                        'getTax' => 1.1,
                    ]
                ),
                static::createConfiguredMock(
                    Price::class,
                    [
                        'getTaxRate' => self::SHIPPING_COSTS_TAX_RATE - 1,
                        'getTax' => 1.1,
                    ]
                ),
            ],
        ];
    }

    /**
     * @dataProvider getVoucherTaxRateDataProvider
     *
     * @param array<float>|null $expected
     * @param array<array>|null $positions
     */
    public function testGetVoucherTaxRate(
        ?array $expected = null,
        ?array $positions = null,
        ?string $voucherTaxMode = null,
        ?string $voucherTaxConfig = null
    ): void {
        $connection = static::createConfiguredMock(
            Connection::class,
            [
                'fetchOne' => $voucherTaxMode,
            ]
        );

        $contextService = static::createConfiguredMock(
            ContextServiceInterface::class,
            [
                'getContext' => static::createConfiguredMock(
                    ShopContextInterface::class,
                    [
                        'getTaxRule' => static::createConfiguredMock(
                            \Shopware\Bundle\StoreFrontBundle\Struct\Tax::class,
                            [
                                'getTax' => (float) $voucherTaxMode,
                            ]
                        ),
                    ]
                ),
            ]
        );

        $subject = new TaxAggregator(
            $connection,
            $contextService,
            $voucherTaxConfig
        );

        $basket = [];

        if ($positions !== null) {
            $basket['content'] = $positions;
        }

        static::assertSame(
            $expected,
            $subject->positionsTaxSum($basket, self::MAXIMUM_TAX_RATE)
        );
    }

    /**
     * @return Generator<array>
     */
    public function getVoucherTaxRateDataProvider(): Generator
    {
        // These datasets test, whether the config/fallback value is returned in case we provide the default or no tax mode
        foreach (['default', null, ''] as $voucherTaxMode) {
            yield sprintf('Default voucher tax mode: "%s"', $voucherTaxMode ?? 'null') => [
                [
                    sprintf('%.2f', self::REGULAR_TAX_RATE) => 1.1,
                ],
                [
                    [
                        'modus' => CartPositionsMode::VOUCHER,
                        'ordernumber' => '58290449-7d4d-4d7a-ac9e-e2da9cc26e77',
                        'tax' => 1.1,
                    ],
                ],
                $voucherTaxMode,
                sprintf('%.2f', self::REGULAR_TAX_RATE),
            ];
        }

        // This dataset tests, whether the maximum tax rate is returned, when using tax mode "auto"
        yield 'Voucher tax mode: auto' => [
            [
                sprintf('%.2f', self::MAXIMUM_TAX_RATE) => 1.1,
            ],
            [
                [
                    'modus' => CartPositionsMode::VOUCHER,
                    'ordernumber' => 'e8cc21ae-0563-4db5-a3b5-17b5bc79cbfe',
                    'tax' => 1.1,
                ],
            ],
            TaxAggregator::VOUCHER_TAX_MODE_AUTO,
        ];

        // This dataset tests, whether 0.0 is returned, when using tax mode "none"
        yield 'Voucher tax mode: none' => [
            null,
            [
                [
                    'modus' => CartPositionsMode::VOUCHER,
                    'ordernumber' => '0bd3d14e-579e-4c75-8044-4d0c95997905',
                    'tax' => 1.1,
                ],
            ],
            TaxAggregator::VOUCHER_TAX_MODE_NONE,
        ];

        // This dataset tests, whether the method attempts to read the tax rate from context, when using any other tax mode
        yield 'Voucher tax mode: 19' => [
            [
                sprintf('%.2f', self::REGULAR_TAX_RATE) => 1.1,
            ],
            [
                [
                    'modus' => CartPositionsMode::VOUCHER,
                    'ordernumber' => 'e0444cee-31e1-4107-b875-80de0c071b01',
                    'tax' => 1.1,
                ],
            ],
            sprintf('%d', self::REGULAR_TAX_RATE),
        ];
    }

    /**
     * @dataProvider taxSumDataProvider
     *
     * @param array<string, float>                                                                                                                                              $expected
     * @param array{content?: non-empty-array, sShippingcostsTax?: float, sShippingcostsTaxProportional?: array<Price>, sShippingcostsNet: float, sShippingcostsWithTax: float} $basket
     */
    public function testTaxSum(array $expected, array $basket): void
    {
        $subject = new TaxAggregator(
            static::createStub(Connection::class),
            static::createStub(ContextServiceInterface::class)
        );

        static::assertSame(
            $expected,
            $subject->taxSum($basket, self::MAXIMUM_TAX_RATE)
        );
    }

    /**
     * @return Generator<array>
     */
    public function taxSumDataProvider(): Generator
    {
        yield 'Empty basket' => [
            [],
            [],
        ];

        yield 'Basket with a few positions' => [
            [
                sprintf('%.2f', self::REGULAR_TAX_RATE) => 4.4,
            ],
            [
                'content' => [
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax_rate' => self::REGULAR_TAX_RATE,
                        'tax' => 1.1,
                    ],
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax_rate' => self::REGULAR_TAX_RATE,
                        'tax' => 1.1,
                    ],
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax_rate' => self::REGULAR_TAX_RATE,
                        'tax' => 1.1,
                    ],
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax_rate' => self::REGULAR_TAX_RATE,
                        'tax' => 1.1,
                    ],
                ],
            ],
        ];

        yield 'Basket with a few positions and shipping costs' => [
            [
                sprintf('%.2f', self::REGULAR_TAX_RATE) => 10.4 + (self::SHIPPING_COSTS_WITH_TAX - self::SHIPPING_COSTS_NET),
            ],
            [
                'content' => [
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax_rate' => self::REGULAR_TAX_RATE,
                        'tax' => 1.1,
                    ],
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax_rate' => self::REGULAR_TAX_RATE,
                        'tax' => 2.1,
                    ],
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax_rate' => self::REGULAR_TAX_RATE,
                        'tax' => 3.1,
                    ],
                    [
                        'modus' => CartPositionsMode::PRODUCT,
                        'tax_rate' => self::REGULAR_TAX_RATE,
                        'tax' => 4.1,
                    ],
                ],
                'sShippingcostsTax' => self::REGULAR_TAX_RATE,
                'sShippingcostsWithTax' => self::SHIPPING_COSTS_WITH_TAX,
                'sShippingcostsNet' => self::SHIPPING_COSTS_NET,
            ],
        ];
    }
}
