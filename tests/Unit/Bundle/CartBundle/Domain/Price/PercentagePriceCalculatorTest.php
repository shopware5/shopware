<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Price;

use Shopware\Bundle\CartBundle\Domain\Price\PercentagePriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCollection;
use Shopware\Bundle\CartBundle\Domain\Price\PriceRounding;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTax;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\PercentageTaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\PercentageTaxRuleCalculator;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxCalculator;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCalculator;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\Generator;

class PercentagePriceCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider calculatePercentagePriceOfGrossPricesProvider
     * @param float $percentage
     * @param Price $expected
     * @param PriceCollection $prices
     */
    public function testCalculatePercentagePriceOfGrossPrices(
        $percentage,
        Price $expected,
        PriceCollection $prices
    ) {
        $rounding = new PriceRounding(2);

        $calculator = new PercentagePriceCalculator(
            new PriceRounding(2),
            new PriceCalculator(
                new TaxCalculator(
                    new PriceRounding(2),
                    [
                        new TaxRuleCalculator($rounding),
                        new PercentageTaxRuleCalculator(new TaxRuleCalculator($rounding))
                    ]
                ),
                $rounding,
                Generator::createGrossPriceDetector()
            )
        );

        $price = $calculator->calculatePrice(
            $percentage,
            $prices,
            Generator::createContext()
        );
        static::assertEquals($expected, $price);
    }

    public function calculatePercentagePriceOfGrossPricesProvider()
    {
        $highTax = new TaxRuleCollection([new TaxRule(19)]);

        return [
//            [
//                -10,
//                new Price(-6.0, new CalculatedTaxCollection([new CalculatedTax(-0.96, 19, -6.0)]), $highTax),
//                new PriceCollection([
//                    new Price(30.00, new CalculatedTaxCollection([new CalculatedTax(4.79, 19, 30.00)]), $highTax),
//                    new Price(30.00, new CalculatedTaxCollection([new CalculatedTax(4.79, 19, 30.00)]), $highTax),
//                ])
//            ],
            [
                //10% discount
                -10,
                //expected calculated "discount" price
                new Price(
                    -6.0,
                    -6.0,
                    new CalculatedTaxCollection([
                        new CalculatedTax(-0.48, 19, -3.0),
                        new CalculatedTax(-0.20, 7, -3.0),
                    ]),
                    new TaxRuleCollection([
                        new PercentageTaxRule(19, 50),
                        new PercentageTaxRule(7, 50)
                    ])
                ),
                //prices of cart line items
                new PriceCollection([
                    new Price(30.00, 30.00, new CalculatedTaxCollection([new CalculatedTax(4.79, 19, 30.00)]), $highTax),
                    new Price(30.00, 30.00, new CalculatedTaxCollection([new CalculatedTax(1.96, 7, 30.00)]), $highTax),
                ])
            ],
//[
//                -99,
//                new Price(-36.61, new CalculatedTaxCollection([new CalculatedTax(-5.85, 19, -36.61)]), $highTax),
//                new PriceCollection([
//                    new Price(20.99, new CalculatedTaxCollection([new CalculatedTax(3.35, 19, 20.99)]), $highTax),
//                    new Price(15.99, new CalculatedTaxCollection([new CalculatedTax(2.55, 19, 15.99)]), $highTax)
//                ])
//            ], [
//                -12.12,
//                new Price(-4.48, new CalculatedTaxCollection([new CalculatedTax(-0.72, 19, -4.48)]), $highTax),
//                new PriceCollection([
//                    new Price(20.99, new CalculatedTaxCollection([new CalculatedTax(3.35, 19, 20.99)]), $highTax),
//                    new Price(15.99, new CalculatedTaxCollection([new CalculatedTax(2.55, 19, 15.99)]), $highTax)
//                ])
//            ], [
//                0.5,
//                new Price(0.18, new CalculatedTaxCollection([new CalculatedTax(0.03, 19, 0.18)]), $highTax),
//                new PriceCollection([
//                    new Price(20.99, new CalculatedTaxCollection([new CalculatedTax(3.35, 19, 20.99)]), $highTax),
//                    new Price(15.99, new CalculatedTaxCollection([new CalculatedTax(2.55, 19, 15.99)]), $highTax)
//                ])
//            ], [
//                99,
//                new Price(36.61, new CalculatedTaxCollection([new CalculatedTax(5.85, 19, 36.61)]), $highTax),
//                new PriceCollection([
//                    new Price(20.99, new CalculatedTaxCollection([new CalculatedTax(3.35, 19, 20.99)]), $highTax),
//                    new Price(15.99, new CalculatedTaxCollection([new CalculatedTax(2.55, 19, 15.99)]), $highTax)
//                ])
//            ], [
//                -100,
//                new Price(
//                    -96,
//                    new CalculatedTaxCollection([new CalculatedTax(-15.33, 19, -96)]),
//                    new TaxRuleCollection([new TaxRule(19)])
//                ),
//                new PriceCollection([
//                    new Price(55, new CalculatedTaxCollection([new CalculatedTax(8.78, 19, 55)]), new TaxRuleCollection([new TaxRule(19)])),
//                    new Price(41, new CalculatedTaxCollection([new CalculatedTax(6.55, 19, 41)]), new TaxRuleCollection([new TaxRule(19)]))
//                ])
//            ]
        ];
    }
}
