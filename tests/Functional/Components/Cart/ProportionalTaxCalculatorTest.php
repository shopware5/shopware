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

namespace Shopware\Tests\Functional\Components\Cart;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Cart\ProportionalTaxCalculatorInterface;
use Shopware\Components\Cart\Struct\Price;

/**
 * @group Basket
 */
class ProportionalTaxCalculatorTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var ProportionalTaxCalculatorInterface
     */
    private $taxCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taxCalculator = Shopware()->Container()->get('shopware.cart.proportional_tax_calculator');
    }

    public function testDifferentTaxes(): void
    {
        $basket = [
            new Price(30, 25.21, 19, 4.79),
            new Price(30, 28.04, 7, 1.96),
        ];

        static::assertTrue($this->taxCalculator->hasDifferentTaxes($basket));
    }

    public function testDifferentTaxesWithOneTax(): void
    {
        $basket = [
            new Price(30, 25.21, 19, 4.79),
        ];

        static::assertFalse($this->taxCalculator->hasDifferentTaxes($basket));
    }

    public function testAbsoluteCalculationWithOneTax(): void
    {
        $basket = [
            new Price(30, 25.21, 19, 4.79),
        ];

        $calculatedProportional = $this->taxCalculator->calculate(10, $basket, false);

        static::assertCount(1, $calculatedProportional);

        static::assertEquals(10, $calculatedProportional[0]->getPrice());
        static::assertEquals(8.403361344537815, $calculatedProportional[0]->getNetPrice());
        static::assertEquals(19, $calculatedProportional[0]->getTaxRate());
        static::assertEquals(1.596638655462185, $calculatedProportional[0]->getTax());
    }

    public function testAbsoluteCalculation(): void
    {
        $basket = [
            new Price(30, 25.21, 19, 4.79),
            new Price(30, 28.04, 7, 1.96),
        ];

        $calculatedProportional = $this->taxCalculator->calculate(10, $basket, false);

        static::assertCount(2, $calculatedProportional);

        // 19%
        static::assertEquals(4.734272300469484, $calculatedProportional[0]->getPrice());
        static::assertEquals(3.978380084428138, $calculatedProportional[0]->getNetPrice());
        static::assertEquals(19, $calculatedProportional[0]->getTaxRate());
        static::assertEquals(0.7558922160413462, $calculatedProportional[0]->getTax());

        // 7%
        static::assertEquals(5.265727699530516, $calculatedProportional[1]->getPrice());
        static::assertEquals(4.921240840682725, $calculatedProportional[1]->getNetPrice());
        static::assertEquals(7, $calculatedProportional[1]->getTaxRate());
        static::assertEquals(0.3444868588477908, $calculatedProportional[1]->getTax());
    }

    public function testPercentCalculationWithOneTax(): void
    {
        $basket = [
            new Price(30, 25.21, 19, 4.79),
        ];

        $calculatedProportional = $this->taxCalculator->recalculatePercentageDiscount(10, $basket, false);

        static::assertCount(1, $calculatedProportional);

        static::assertEquals(3, $calculatedProportional['1f0e3dad99908345f7439f8ffabdffc4']->getPrice());
        static::assertEquals(2.5210084033613445, $calculatedProportional['1f0e3dad99908345f7439f8ffabdffc4']->getNetPrice());
        static::assertEquals(19, $calculatedProportional['1f0e3dad99908345f7439f8ffabdffc4']->getTaxRate());
    }

    public function testPercentCalculation(): void
    {
        $basket = [
            new Price(30, 25.21, 19, 4.79),
            new Price(30, 28.04, 7, 1.96),
        ];

        $calculatedProportional = $this->taxCalculator->recalculatePercentageDiscount(10, $basket, false);

        static::assertCount(2, $calculatedProportional);

        static::assertEquals(3, $calculatedProportional['1f0e3dad99908345f7439f8ffabdffc4']->getPrice());
        static::assertEquals(2.5210084033613445, $calculatedProportional['1f0e3dad99908345f7439f8ffabdffc4']->getNetPrice());
        static::assertEquals(19, $calculatedProportional['1f0e3dad99908345f7439f8ffabdffc4']->getTaxRate());

        static::assertEquals(3, $calculatedProportional['8f14e45fceea167a5a36dedd4bea2543']->getPrice());
        static::assertEquals(2.803738317757009, $calculatedProportional['8f14e45fceea167a5a36dedd4bea2543']->getNetPrice());
        static::assertEquals(7, $calculatedProportional['8f14e45fceea167a5a36dedd4bea2543']->getTaxRate());
    }

    public function testNetAbsoluteCalculation(): void
    {
        $basket = [
            new Price(30, 25.21, 19, 4.79),
            new Price(30, 28.04, 7, 1.96),
        ];

        $calculatedProportional = $this->taxCalculator->calculate(10, $basket, true);

        static::assertCount(2, $calculatedProportional);

        foreach ($calculatedProportional as $item) {
            static::assertEquals($item->getPrice(), $item->getNetPrice());
        }
    }

    public function testNetPercentCalculation(): void
    {
        $basket = [
            new Price(30, 25.21, 19, 4.79),
            new Price(30, 28.04, 7, 1.96),
        ];

        $calculatedProportional = $this->taxCalculator->recalculatePercentageDiscount(10, $basket, true);

        static::assertCount(2, $calculatedProportional);

        foreach ($calculatedProportional as $item) {
            static::assertEquals($item->getPrice(), $item->getNetPrice());
        }
    }

    /**
     * @dataProvider calculate_thereIsNoPriceWith_NAN_test_dataProvider
     */
    public function testCalculateThereIsNoPriceWithNAN(Price $price): void
    {
        $messageTemplate = 'The %s with value: %s is NAN';

        $prices = $this->taxCalculator->calculate(0, [$price], false);
        $result = array_shift($prices);
        static::assertInstanceOf(Price::class, $result);

        static::assertFalse(
            is_nan($result->getPrice()),
            sprintf($messageTemplate, 'Price', $result->getPrice())
        );

        static::assertFalse(
            is_nan($result->getNetPrice()),
            sprintf($messageTemplate, 'NetPrice', $result->getNetPrice())
        );

        static::assertFalse(
            is_nan($result->getTax()),
            sprintf($messageTemplate, 'Tax', $result->getTax())
        );
    }

    /**
     * @return array<array<Price>>
     */
    public function calculate_thereIsNoPriceWith_NAN_test_dataProvider(): array
    {
        return [
            [new Price(0, 0, 6, 0)],
            [new Price(0.0, 0.0, 7, 0.0)],
            [new Price('0', '0', 9, '0')],
            [new Price('0.0', '0.0', '10', '0.0')],
            [new Price('5,5', '4,0', 11, '1,1')],
            [new Price('0.51954864', '0.41954864', 13, '0.1')],
        ];
    }
}
