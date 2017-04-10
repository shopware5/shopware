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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Price;

use Shopware\Bundle\CartBundle\Domain\Price\AmountCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCollection;
use Shopware\Bundle\CartBundle\Domain\Price\PriceRounding;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTax;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\VerticalTaxAmountCalculator;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\Generator;

/**
 * Class PriceCalculatorTest
 */
class AmountCalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider calculateAmountWithGrossPricesProvider
     *
     * @param CartPrice       $expected
     * @param PriceCollection $prices
     */
    public function testCalculateAmountWithGrossPrices(CartPrice $expected, PriceCollection $prices)
    {
        $calculator = new AmountCalculator(
            Generator::createGrossPriceDetector(),
            new PriceRounding(2),
            new VerticalTaxAmountCalculator()
        );
        $cartPrice = $calculator->calculateAmount($prices, Generator::createContext());
        static::assertEquals($expected, $cartPrice);
    }

    /**
     * @dataProvider calculateAmountWithNetPricesProvider
     *
     * @param CartPrice       $expected
     * @param PriceCollection $prices
     */
    public function testCalculateAmountWithNetPrices(CartPrice $expected, PriceCollection $prices)
    {
        $calculator = new AmountCalculator(Generator::createNetPriceDetector(), new PriceRounding(2),
            new VerticalTaxAmountCalculator());
        $cartPrice = $calculator->calculateAmount($prices, Generator::createContext());
        static::assertEquals($expected, $cartPrice);
    }

    /**
     * @dataProvider calculateAmountForNetDeliveriesProvider
     *
     * @param CartPrice       $expected
     * @param PriceCollection $prices
     */
    public function testCalculateAmountForNetDeliveries(CartPrice $expected, PriceCollection $prices)
    {
        $calculator = new AmountCalculator(Generator::createNetDeliveryDetector(), new PriceRounding(2),
            new VerticalTaxAmountCalculator());
        $cartPrice = $calculator->calculateAmount($prices, Generator::createContext());
        static::assertEquals($expected, $cartPrice);
    }

    public function calculateAmountForNetDeliveriesProvider()
    {
        $highTax = new TaxRuleCollection([new TaxRule(19)]);
        $lowTax = new TaxRuleCollection([new TaxRule(7)]);

        return [
            [
                new CartPrice(19.5, 19.5, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.5)]), $highTax),
                ]),
            ], [
                new CartPrice(33.7, 33.7, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(14.20, 14.20, new CalculatedTaxCollection([new CalculatedTax(2.27, 19, 14.20)]), $highTax),
                ]),
            ], [
                new CartPrice(33.70, 33.70, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(14.20, 14.20, new CalculatedTaxCollection([new CalculatedTax(0.93, 7, 14.20)]), $lowTax),
                ]),
            ], [
                new CartPrice(105.6, 105.6, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(5.32, 19, 33.30)]), $highTax),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(1.28, 7, 19.50)]), $lowTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(2.18, 7, 33.30)]), $lowTax),
                ]),
            ], [
                new CartPrice(105.60, 105.60, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(5.32, 19, 33.30)]), $highTax),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(1.28, 7, 19.50)]), $lowTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(2.18, 7, 33.30)]), $lowTax),
                ]),
            ], [
                new CartPrice(20, 20, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new PriceCollection([
                    new Price(10.00, 10.00, new CalculatedTaxCollection([]), new TaxRuleCollection([])),
                    new Price(10.00, 10.00, new CalculatedTaxCollection([]), new TaxRuleCollection([])),
                ]),
            ],
        ];
    }

    /**
     * @return array
     */
    public function calculateAmountWithNetPricesProvider()
    {
        $highTax = new TaxRuleCollection([new TaxRule(19)]);
        $lowTax = new TaxRuleCollection([new TaxRule(7)]);
        $mixedTaxes = new TaxRuleCollection([new TaxRule(19), new TaxRule(7)]);

        return [
            [
                new CartPrice(19.5, 22.61, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.5)]), $highTax),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                ]),
            ], [
                new CartPrice(33.7, 39.08, new CalculatedTaxCollection([new CalculatedTax(5.38, 19, 33.7)]), $highTax),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(14.20, 14.20, new CalculatedTaxCollection([new CalculatedTax(2.27, 19, 14.20)]), $highTax),
                ]),
            ], [
                new CartPrice(
                    33.70,
                    37.74,
                    new CalculatedTaxCollection([
                        new CalculatedTax(3.11, 19, 19.50),
                        new CalculatedTax(0.93, 7, 14.20),
                    ]),
                    $mixedTaxes
                ),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(14.20, 14.20, new CalculatedTaxCollection([new CalculatedTax(0.93, 7, 14.20)]), $lowTax),
                ]),
            ], [
                new CartPrice(
                    105.6,
                    117.49,
                    new CalculatedTaxCollection([
                        new CalculatedTax(8.43, 19, 52.8),
                        new CalculatedTax(3.46, 7, 52.8),
                    ]),
                    $mixedTaxes
                ),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(5.32, 19, 33.30)]), $highTax),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(1.28, 7, 19.50)]), $lowTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(2.18, 7, 33.30)]), $lowTax),
                ]),
            ], [
                new CartPrice(
                    244.5,
                    272.44,
                    new CalculatedTaxCollection([
                        new CalculatedTax(8.43, 19, 52.8),
                        new CalculatedTax(8.05, 18, 52.8),
                        new CalculatedTax(7.67, 17, 52.8),
                        new CalculatedTax(3.46, 7, 52.8),
                        new CalculatedTax(0.33, 1, 33.3),
                    ]),
                    new TaxRuleCollection([
                        new TaxRule(19),
                        new TaxRule(18),
                        new TaxRule(17),
                        new TaxRule(7),
                        new TaxRule(1),
                    ])
                ),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(5.32, 19, 33.30)]), $highTax),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(2.97, 18, 19.50)]), new TaxRuleCollection([new TaxRule(18)])),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(5.08, 18, 33.30)]), new TaxRuleCollection([new TaxRule(18)])),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(2.83, 17, 19.50)]), new TaxRuleCollection([new TaxRule(17)])),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(4.84, 17, 33.30)]), new TaxRuleCollection([new TaxRule(17)])),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(1.28, 7, 19.50)]), $lowTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(2.18, 7, 33.30)]), $lowTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(0.33, 1, 33.30)]), new TaxRuleCollection([new TaxRule(1)])),
                ]),
            ], [
                new CartPrice(20, 20, new CalculatedTaxCollection([]), new TaxRuleCollection()),
                new PriceCollection([
                    new Price(10.00, 10.00, new CalculatedTaxCollection([]), new TaxRuleCollection([])),
                    new Price(10.00, 10.00, new CalculatedTaxCollection([]), new TaxRuleCollection([])),
                ]),
            ],
            [
                new CartPrice(
                    34.97,
                    41.67,
                    new CalculatedTaxCollection([
                        new CalculatedTax(6.7, 19, 34.97),
                    ]),
                    new TaxRuleCollection([
                        new TaxRule(19),
                    ])
                ),
                new PriceCollection([
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(2.00, 2.00, new CalculatedTaxCollection([new CalculatedTax(0.38, 19, 2.00)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(2.45, 12.25, new CalculatedTaxCollection([new CalculatedTax(2.33, 19, 12.25)]), new TaxRuleCollection([new TaxRule(19)]), 5),
                    new Price(0.50, 2.5, new CalculatedTaxCollection([new CalculatedTax(0.48, 19, 2.5)]), new TaxRuleCollection([new TaxRule(19)]), 5),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.40, 1.40, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.40)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(3.78, 3.78, new CalculatedTaxCollection([new CalculatedTax(0.72, 19, 3.78)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(-0.96, -0.96, new CalculatedTaxCollection([new CalculatedTax(-0.18, 19, -0.96)]), new TaxRuleCollection([new TaxRule(19)])),
                ]),
            ],
        ];
    }

    /**
     * @return array
     */
    public function calculateAmountWithGrossPricesProvider()
    {
        $highTax = new TaxRuleCollection([new TaxRule(19)]);
        $lowTax = new TaxRuleCollection([new TaxRule(7)]);
        $mixedTaxes = new TaxRuleCollection([new TaxRule(19), new TaxRule(7)]);

        return [
            [
                new CartPrice(16.39, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                ]),
            ], [
                new CartPrice(28.32, 33.7, new CalculatedTaxCollection([new CalculatedTax(5.38, 19, 33.7)]), $highTax),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(14.20, 14.20, new CalculatedTaxCollection([new CalculatedTax(2.27, 19, 14.20)]), $highTax),
                ]),
            ], [
                new CartPrice(
                    29.66,
                    33.70,
                    new CalculatedTaxCollection([
                        new CalculatedTax(3.11, 19, 19.50),
                        new CalculatedTax(0.93, 7, 14.20),
                    ]),
                    $mixedTaxes
                ),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(14.20, 14.20, new CalculatedTaxCollection([new CalculatedTax(0.93, 7, 14.20)]), $lowTax),
                ]),
            ], [
                new CartPrice(
                    93.71,
                    105.6,
                    new CalculatedTaxCollection([
                        new CalculatedTax(8.43, 19, 52.8),
                        new CalculatedTax(3.46, 7, 52.8),
                    ]),
                    $mixedTaxes
                ),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(5.32, 19, 33.30)]), $highTax),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(1.28, 7, 19.50)]), $lowTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(2.18, 7, 33.30)]), $lowTax),
                ]),
            ], [
                new CartPrice(
                    216.56,
                    244.5,
                    new CalculatedTaxCollection([
                        new CalculatedTax(8.43, 19, 52.8),
                        new CalculatedTax(8.05, 18, 52.8),
                        new CalculatedTax(7.67, 17, 52.8),
                        new CalculatedTax(3.46, 7, 52.8),
                        new CalculatedTax(0.33, 1, 33.30),
                    ]),
                    new TaxRuleCollection([
                        new TaxRule(19),
                        new TaxRule(18),
                        new TaxRule(17),
                        new TaxRule(7),
                        new TaxRule(1),
                    ])
                ),
                new PriceCollection([
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(3.11, 19, 19.50)]), $highTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(5.32, 19, 33.30)]), $highTax),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(2.97, 18, 19.50)]), new TaxRuleCollection([new TaxRule(18)])),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(5.08, 18, 33.30)]), new TaxRuleCollection([new TaxRule(18)])),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(2.83, 17, 19.50)]), new TaxRuleCollection([new TaxRule(17)])),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(4.84, 17, 33.30)]), new TaxRuleCollection([new TaxRule(17)])),
                    new Price(19.50, 19.50, new CalculatedTaxCollection([new CalculatedTax(1.28, 7, 19.50)]), $lowTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(2.18, 7, 33.30)]), $lowTax),
                    new Price(33.30, 33.30, new CalculatedTaxCollection([new CalculatedTax(0.33, 1, 33.30)]), new TaxRuleCollection([new TaxRule(1)])),
                ]),
            ], [
                new CartPrice(20, 20, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new PriceCollection([
                    new Price(10.00, 10.00, new CalculatedTaxCollection([]), new TaxRuleCollection([])),
                    new Price(10.00, 10.00, new CalculatedTaxCollection([]), new TaxRuleCollection([])),
                ]),
            ], [
                new CartPrice(
                    35.00,
                    41.70,
                    new CalculatedTaxCollection([
                        new CalculatedTax(6.7, 19, 41.70),
                    ]),
                    new TaxRuleCollection([
                        new TaxRule(19),
                    ])
                ),
                new PriceCollection([
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(2.38, 2.38, new CalculatedTaxCollection([new CalculatedTax(0.38, 19, 2.38)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(14.6, 14.6, new CalculatedTaxCollection([new CalculatedTax(2.33, 19, 14.6)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(3.0, 3.0, new CalculatedTaxCollection([new CalculatedTax(0.48, 19, 3.0)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(1.67, 1.67, new CalculatedTaxCollection([new CalculatedTax(0.27, 19, 1.67)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(4.50, 4.50, new CalculatedTaxCollection([new CalculatedTax(0.72, 19, 4.50)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(-1.15, -1.15, new CalculatedTaxCollection([new CalculatedTax(-0.18, 19, -1.15)]), new TaxRuleCollection([new TaxRule(19)])),
                ]),
            ], [
                new CartPrice(
                    0,
                    0,
                    new CalculatedTaxCollection([new CalculatedTax(0, 19, 0)]),
                    new TaxRuleCollection([new TaxRule(19)])
                ),
                new PriceCollection([
                    new Price(55, 55, new CalculatedTaxCollection([new CalculatedTax(8.78, 19, 55)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(41, 41, new CalculatedTaxCollection([new CalculatedTax(6.55, 19, 41)]), new TaxRuleCollection([new TaxRule(19)])),
                    new Price(-96, -96, new CalculatedTaxCollection([new CalculatedTax(-15.33, 19, -96)]), new TaxRuleCollection([new TaxRule(19)])),
                ]),
            ],
        ];
    }
}
