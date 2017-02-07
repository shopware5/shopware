<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Price;

use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceRounding;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTax;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxCalculator;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCalculator;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\Generator;

/**
 * Class PriceCalculatorTest
 * @package Shopware\Tests\Unit\Bundle\CartBundle\Calculation
 */
class PriceCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider priceCalculationWithGrossPricesProvider
     * @param PriceRounding $priceRounding
     * @param Price $expected
     * @param PriceDefinition $priceDefinition
     */
    public function testPriceCalculationWithGrossPrices(
        PriceRounding $priceRounding,
        Price $expected,
        PriceDefinition $priceDefinition
    ) {
        $calculator = new PriceCalculator(
            new TaxCalculator(
                $priceRounding,
                [new TaxRuleCalculator($priceRounding)]
            ),
            $priceRounding,
            Generator::createGrossPriceDetector()
        );

        $lineItemPrice = $calculator->calculate(
            $priceDefinition,
            Generator::createContext()
        );

        static::assertEquals($expected, $lineItemPrice);
    }

    /**
     * @dataProvider unitPriceCalculationWithGrossPricesProvider
     * @param PriceRounding $priceRounding
     * @param Price $expected
     * @param PriceDefinition $priceDefinition
     */
    public function testUnitPriceCalculationWithGrossPrices(
        PriceRounding $priceRounding,
        Price $expected,
        PriceDefinition $priceDefinition
    ) {
        $calculator = new PriceCalculator(
            new TaxCalculator(
                $priceRounding,
                [new TaxRuleCalculator($priceRounding)]
            ),
            $priceRounding,
            Generator::createGrossPriceDetector()
        );

        $lineItemPrice = $calculator->calculate(
            $priceDefinition,
            Generator::createContext()
        );

        static::assertEquals($expected, $lineItemPrice);
    }


    public function unitPriceCalculationWithGrossPricesProvider()
    {
        $highTaxRules = new TaxRuleCollection([new TaxRule(19)]);

        return [
            [
                new PriceRounding(2),
                new Price(0.01, 1.00, new CalculatedTaxCollection([new CalculatedTax(0.16, 19, 1.00)]), $highTaxRules, 99.99),
                new PriceDefinition(0.00840336134453782, $highTaxRules, 99.99),
            ],
            [
                new PriceRounding(2),
                new Price(3.10, 0.03, new CalculatedTaxCollection([new CalculatedTax(0.00, 19, 0.03)]), $highTaxRules, 0.01),
                new PriceDefinition(2.60504201680672, $highTaxRules, 0.01),
            ], [
                new PriceRounding(2),
                new Price(0.40, 0.1, new CalculatedTaxCollection([new CalculatedTax(0.02, 19, 0.1)]), $highTaxRules, 0.25),
                new PriceDefinition(0.336134453781513, $highTaxRules, 0.25),
            ], [
                new PriceRounding(2),
                new Price(99.99, 9998, new CalculatedTaxCollection([new CalculatedTax(1596.32, 19, 9998)]), $highTaxRules, 99.99),
                new PriceDefinition(84.0252100840336, $highTaxRules, 99.99),
            ], [
                new PriceRounding(2),
                new Price(99.99, 1.0, new CalculatedTaxCollection([new CalculatedTax(0.16, 19, 1.0)]), $highTaxRules, 0.01),
                new PriceDefinition(84.0252100840336, $highTaxRules, 0.01),
            ], [
                new PriceRounding(2),
                new Price(99.99, 25.00, new CalculatedTaxCollection([new CalculatedTax(3.99, 19, 25.00)]), $highTaxRules, 0.25),
                new PriceDefinition(84.0252100840336, $highTaxRules, 0.25)
            ], [
                new PriceRounding(3),
                new Price(0.001, 0.100, new CalculatedTaxCollection([new CalculatedTax(0.0160, 19, 0.100)]), $highTaxRules, 99.99),
                new PriceDefinition(0.000840336134453782, $highTaxRules, 99.99),
            ], [
                new PriceRounding(3),
                new Price(0.004, 0.001, new CalculatedTaxCollection([new CalculatedTax(0.000, 19, 0.001)]), $highTaxRules, 0.25),
                new PriceDefinition(0.00336134453781513, $highTaxRules, 0.25)
            ], [
                new PriceRounding(3),
                new Price(99.999, 9998.9, new CalculatedTaxCollection([new CalculatedTax(1596.463, 19, 9998.9)]), $highTaxRules, 99.99),
                new PriceDefinition(84.0327731092437, $highTaxRules, 99.99)
            ], [
                new PriceRounding(3),
                new Price(99.999, 1.000, new CalculatedTaxCollection([new CalculatedTax(0.160, 19, 1.000)]), $highTaxRules, 0.01),
                new PriceDefinition(84.0327731092437, $highTaxRules, 0.01)
            ], [
                new PriceRounding(3),
                new Price(99.999, 25.000, new CalculatedTaxCollection([new CalculatedTax(3.992, 19, 25.000)]), $highTaxRules, 0.25),
                new PriceDefinition(84.0327731092437, $highTaxRules, 0.25)
            ], [
                new PriceRounding(3),
                new Price(13.474, 17.92, new CalculatedTaxCollection([new CalculatedTax(2.861, 19, 17.92)]), $highTaxRules, 1.33),
                new PriceDefinition(11.3226890756303, $highTaxRules, 1.33)
            ], [
                new PriceRounding(3),
                new Price(44.444, 197.331, new CalculatedTaxCollection([new CalculatedTax(31.507, 19, 197.331)]), $highTaxRules, 4.44),
                new PriceDefinition(37.3478991596639, $highTaxRules, 4.44)
            ], [
                new PriceRounding(3),
                new Price(777.777, 6035.550, new CalculatedTaxCollection([new CalculatedTax(963.659, 19, 6035.550)]), $highTaxRules, 7.76),
                new PriceDefinition(653.594117647059, $highTaxRules, 7.76)
            ]
        ];
    }

    public function priceCalculationWithGrossPricesProvider()
    {
        $highTaxRules = new TaxRuleCollection([new TaxRule(19)]);
        $lowTaxRuleCollection = new TaxRuleCollection([new TaxRule(7)]);

        return [
            [
                new PriceRounding(2),
                new Price(15.99, 15.99, new CalculatedTaxCollection([new CalculatedTax(2.55, 19, 15.99)]), $highTaxRules),
                new PriceDefinition(13.436974789916, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(21.32, 21.32, new CalculatedTaxCollection([new CalculatedTax(3.40, 19, 21.32)]), $highTaxRules),
                new PriceDefinition(17.9159663865546, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(50, 50, new CalculatedTaxCollection([new CalculatedTax(7.98, 19, 50)]), $highTaxRules),
                new PriceDefinition(42.0168067226891, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(-5.88, -5.88, new CalculatedTaxCollection([new CalculatedTax(-0.94, 19, -5.88)]), $highTaxRules),
                new PriceDefinition(-4.94117647058824, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(95799.97, 95799.97, new CalculatedTaxCollection([new CalculatedTax(15295.79, 19, 95799.97)]), $highTaxRules),
                new PriceDefinition(80504.1764705882, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(0.05, 0.05, new CalculatedTaxCollection([new CalculatedTax(0.01, 19, 0.05)]), $highTaxRules),
                new PriceDefinition(0.0420168067226891, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(0.01, 0.01, new CalculatedTaxCollection([new CalculatedTax(0.00, 19, 0.01)]), $highTaxRules),
                new PriceDefinition(0.00840336134453782, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(0.08, 0.08, new CalculatedTaxCollection([new CalculatedTax(0.01, 19, 0.08)]), $highTaxRules),
                new PriceDefinition(0.0672268907563025, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(0.11, 0.11, new CalculatedTaxCollection([new CalculatedTax(0.02, 19, 0.11)]), $highTaxRules),
                new PriceDefinition(0.092436974789916, $highTaxRules),
            ], [
                new PriceRounding(2),
                new Price(0.11, 0.11, new CalculatedTaxCollection([new CalculatedTax(0.01, 7, 0.11)]), $lowTaxRuleCollection),
                new PriceDefinition(0.102803738317757, $lowTaxRuleCollection),
            ], [
                new PriceRounding(2),
                new Price(15.99, 15.99, new CalculatedTaxCollection([new CalculatedTax(1.05, 7, 15.99)]), $lowTaxRuleCollection),
                new PriceDefinition(14.9439252336449, $lowTaxRuleCollection)
            ], [
                new PriceRounding(2),
                new Price(21.32, 21.32, new CalculatedTaxCollection([new CalculatedTax(1.39, 7, 21.32)]), $lowTaxRuleCollection),
                new PriceDefinition(19.9252336448598, $lowTaxRuleCollection)
            ], [
                new PriceRounding(2),
                new Price(50.00, 50.00, new CalculatedTaxCollection([new CalculatedTax(3.27, 7, 50.00)]), $lowTaxRuleCollection),
                new PriceDefinition(46.7289719626168, $lowTaxRuleCollection)
            ], [
                new PriceRounding(2),
                new Price(95799.97, 95799.97, new CalculatedTaxCollection([new CalculatedTax(6267.29, 7, 95799.97)]), $lowTaxRuleCollection),
                new PriceDefinition(89532.6822429906, $lowTaxRuleCollection)
            ], [
                new PriceRounding(2),
                new Price(0.05, 0.05, new CalculatedTaxCollection([new CalculatedTax(0.00, 7, 0.05)]), $lowTaxRuleCollection),
                new PriceDefinition(0.0467289719626168, $lowTaxRuleCollection)
            ], [
                new PriceRounding(2),
                new Price(0.01, 0.01, new CalculatedTaxCollection([new CalculatedTax(0.00, 7, 0.01)]), $lowTaxRuleCollection),
                new PriceDefinition(0.00934579439252336, $lowTaxRuleCollection)
            ], [
                new PriceRounding(2),
                new Price(0.08, 0.08, new CalculatedTaxCollection([new CalculatedTax(0.01, 7, 0.08)]), $lowTaxRuleCollection),
                new PriceDefinition(0.0747663551401869, $lowTaxRuleCollection),
            ], [
                new PriceRounding(2),
                new Price(-5.88, -5.88, new CalculatedTaxCollection([new CalculatedTax(-0.38, 7, -5.88)]), $lowTaxRuleCollection),
                new PriceDefinition(-5.49532710280374, $lowTaxRuleCollection),
            ], [
                new PriceRounding(3),
                new Price(15.999, 15.999, new CalculatedTaxCollection([new CalculatedTax(2.554, 19, 15.999)]), $highTaxRules),
                new PriceDefinition(13.4445378151261, $highTaxRules),
            ], [
                new PriceRounding(3),
                new Price(21.322, 21.322, new CalculatedTaxCollection([new CalculatedTax(3.404, 19, 21.322)]), $highTaxRules),
                new PriceDefinition(17.9176470588235, $highTaxRules),
            ], [
                new PriceRounding(3),
                new Price(50.00, 50.00, new CalculatedTaxCollection([new CalculatedTax(7.983, 19, 50.00)]), $highTaxRules),
                new PriceDefinition(42.01680672268908, $highTaxRules),
            ], [
                new PriceRounding(3),
                new Price(95799.974, 95799.974, new CalculatedTaxCollection([new CalculatedTax(15295.794, 19, 95799.974)]), $highTaxRules),
                new PriceDefinition(80504.1798319328, $highTaxRules),
            ], [
                new PriceRounding(3),
                new Price(0.005, 0.005, new CalculatedTaxCollection([new CalculatedTax(0.001, 19, 0.005)]), $highTaxRules),
                new PriceDefinition(0.00420168067226891, $highTaxRules),
            ], [
                new PriceRounding(3),
                new Price(0.001, 0.001, new CalculatedTaxCollection([new CalculatedTax(0.000, 19, 0.001)]), $highTaxRules),
                new PriceDefinition(0.000840336134453782, $highTaxRules),
            ], [
                new PriceRounding(3),
                new Price(0.008, 0.008, new CalculatedTaxCollection([new CalculatedTax(0.001, 19, 0.008)]), $highTaxRules),
                new PriceDefinition(0.00672268907563025, $highTaxRules),
            ], [
                new PriceRounding(3),
                new Price(-5.988, -5.988, new CalculatedTaxCollection([new CalculatedTax(-0.956, 19, -5.988)]), $highTaxRules),
                new PriceDefinition(-5.03193277310924, $highTaxRules),
            ]
        ];
    }
}
