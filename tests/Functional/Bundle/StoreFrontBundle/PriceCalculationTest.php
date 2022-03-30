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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;

class PriceCalculationTest extends TestCase
{
    public function testCustomerGroupDiscount(): void
    {
        $number = __FUNCTION__;
        $context = $this->createContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createProduct($data);
        $listProduct = $this->helper->getListProduct($number, $context);

        static::assertInstanceOf(Price::class, $listProduct->getCheapestPrice());
        static::assertEquals(80, $listProduct->getCheapestPrice()->getCalculatedPrice());

        $graduations = $listProduct->getPrices();

        static::assertEquals(80, $graduations[0]->getCalculatedPrice());
        static::assertEquals(60, $graduations[1]->getCalculatedPrice());
        static::assertEquals(40, $graduations[2]->getCalculatedPrice());
    }

    public function testNetPrices(): void
    {
        $number = __FUNCTION__;
        $context = $this->createContext(false);

        $data = $this->getProduct($number, $context);

        $this->helper->createProduct($data);
        $listProduct = $this->helper->getListProduct($number, $context);

        /*  price / 119 * 100 (saving of data)  price - (price / 100 * 20) (customer discount)
        100 = 84.0336134454     67.2268907563   * 2
        110 = 92.4369747899     73.9495798319
        120 = 100.840336134     80.64

        75 = 63.025210084      50.4201680672   * 2
        85 = 71.4285714286     57.1428571429
        95 = 79.8319327731     63.8655462184

        50 = 42.0168067227     33.6134453782   * 2   67.2268907564
        60 = 50.4201680672     40.3361344538
        70 = 58.8235294117     47.058823529412

        */

        $cheapest = $listProduct->getCheapestPrice();
        static::assertInstanceOf(Price::class, $cheapest);
        $graduations = $listProduct->getPrices();

        static::assertEquals(67.23, $cheapest->getCalculatedPrice());
        static::assertEquals(73.950, $cheapest->getCalculatedPseudoPrice());
        static::assertEquals(134.46, $cheapest->getCalculatedReferencePrice());
        static::assertEquals(80.67, $cheapest->getCalculatedRegulationPrice());

        $graduation = $graduations[1];
        static::assertEquals(50.420, $graduation->getCalculatedPrice());
        static::assertEquals(57.14, $graduation->getCalculatedPseudoPrice());
        static::assertEquals(100.84, $graduation->getCalculatedReferencePrice());
        static::assertEquals(63.87, $graduation->getCalculatedRegulationPrice());

        $graduation = $graduations[2];
        static::assertEquals(33.61, $graduation->getCalculatedPrice());
        static::assertEquals(40.34, $graduation->getCalculatedPseudoPrice());
        static::assertEquals(67.22, $graduation->getCalculatedReferencePrice());
        static::assertEquals(47.06, $graduation->getCalculatedRegulationPrice());
    }

    public function testCurrencyFactor(): void
    {
        $number = __FUNCTION__;
        $context = $this->createContext(true, 0, 1.2);
        $data = $this->getProduct($number, $context);

        $this->helper->createProduct($data);
        $listProduct = $this->helper->getListProduct($number, $context);

        /*

        100 = 120
        110 = 132
        171 = 144

        75 = 90
        85 = 102
        95 = 114

        50 = 60
        60 = 72
        70 = 84

        */

        $cheapest = $listProduct->getCheapestPrice();
        static::assertInstanceOf(Price::class, $cheapest);
        $graduations = $listProduct->getPrices();

        static::assertEquals(120, $cheapest->getCalculatedPrice());
        static::assertEquals(132, $cheapest->getCalculatedPseudoPrice());
        static::assertEquals(240, $cheapest->getCalculatedReferencePrice());
        static::assertEquals(144, $cheapest->getCalculatedRegulationPrice());

        $graduation = $graduations[1];
        static::assertEquals(90, $graduation->getCalculatedPrice());
        static::assertEquals(102, $graduation->getCalculatedPseudoPrice());
        static::assertEquals(180, $graduation->getCalculatedReferencePrice());
        static::assertEquals(114, $graduation->getCalculatedRegulationPrice());

        $graduation = $graduations[2];
        static::assertEquals(60, $graduation->getCalculatedPrice());
        static::assertEquals(72, $graduation->getCalculatedPseudoPrice());
        static::assertEquals(120, $graduation->getCalculatedReferencePrice());
        static::assertEquals(84, $graduation->getCalculatedRegulationPrice());
    }

    public function testDiscountCurrencyNet(): void
    {
        $number = __FUNCTION__;
        $context = $this->createContext(false, 30, 1.2);
        $data = $this->getProduct($number, $context);

        $this->helper->createProduct($data);
        $listProduct = $this->helper->getListProduct($number, $context);

        /*
        INPUT   TAX         DISCOUNT(30%)  CURRENCY(1.2)    UNIT
        100     84,03361    58,82353       70,58824         141,17647
        110     92,43697    64,70588       77,64706
        120     100.8403    70.58824       84.70588

        75      63,02521    44,11765       52,94118         105,88235
        85      71,42857    50,00000       60,00000
        95      79.83193    55.88235       67.05882

        50      42,01681    29,41176       35,29412         70,58824
        60      50,42017    35,29412       42,35294
        70      58.82352    41.17647       49.41176
        */

        $cheapest = $listProduct->getCheapestPrice();
        static::assertInstanceOf(Price::class, $cheapest);
        $graduations = $listProduct->getPrices();

        static::assertEquals(70.59, $cheapest->getCalculatedPrice());
        static::assertEquals(77.65, $cheapest->getCalculatedPseudoPrice());
        static::assertEquals(141.18, $cheapest->getCalculatedReferencePrice());
        static::assertEquals(84.71, $cheapest->getCalculatedRegulationPrice());

        $graduation = $graduations[1];
        static::assertEquals(52.94, $graduation->getCalculatedPrice());
        static::assertEquals(60.00, $graduation->getCalculatedPseudoPrice());
        static::assertEquals(105.88, $graduation->getCalculatedReferencePrice());
        static::assertEquals(67.06, $graduation->getCalculatedRegulationPrice());

        $graduation = $graduations[2];
        static::assertEquals(35.29, $graduation->getCalculatedPrice());
        static::assertEquals(42.35, $graduation->getCalculatedPseudoPrice());
        static::assertEquals(70.58, $graduation->getCalculatedReferencePrice());
        static::assertEquals(49.41, $graduation->getCalculatedRegulationPrice());
    }

    public function testDiscountCurrencyGross(): void
    {
        $number = __FUNCTION__;
        $context = $this->createContext(true, 15, 1.44);
        $data = $this->getProduct($number, $context);

        $this->helper->createProduct($data);
        $listProduct = $this->helper->getListProduct($number, $context);

        /*
        INPUT   TAX         DISCOUNT(15%)    CURRENCY(1.44)    TAX(19%)    UNIT
        100     100.00000   85.00000         122.4                         244.80000
        110     110.00000   93.50000         134.64
        120     100.84033   85.71420         123.42            146.88

        75      75.00000    63.75000         91.8                          183.60000
        85      85.00000    72.25000         104.04
        95      79.83193    67.85714         97.7143           116.28

        50      50.00000    42.50000         61.2                          122.40000
        60      60.00000    51.00000         73.44
        70      58.82352    50               72                85.68
        */

        $cheapest = $listProduct->getCheapestPrice();
        static::assertInstanceOf(Price::class, $cheapest);
        $graduations = $listProduct->getPrices();

        static::assertEquals(122.4, $cheapest->getCalculatedPrice());
        static::assertEquals(134.64, $cheapest->getCalculatedPseudoPrice());
        static::assertEquals(244.80000, $cheapest->getCalculatedReferencePrice());
        static::assertEquals(146.88, $cheapest->getCalculatedRegulationPrice());

        $graduation = $graduations[1];
        static::assertEquals(91.8, $graduation->getCalculatedPrice());
        static::assertEquals(104.04, $graduation->getCalculatedPseudoPrice());
        static::assertEquals(183.60000, $graduation->getCalculatedReferencePrice());
        static::assertEquals(116.28, $graduation->getCalculatedRegulationPrice());

        $graduation = $graduations[2];
        static::assertEquals(61.2, $graduation->getCalculatedPrice());
        static::assertEquals(73.44, $graduation->getCalculatedPseudoPrice());
        static::assertEquals(122.40000, $graduation->getCalculatedReferencePrice());
        static::assertEquals(85.68, $graduation->getCalculatedRegulationPrice());
    }

    private function createContext(bool $displayGross = true, int $discount = 20, float $currencyFactor = 1): TestContext
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
}
