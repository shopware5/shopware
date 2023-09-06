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

use Doctrine\DBAL\Connection;
use Shopware\Tests\Functional\Components\CheckoutTestCase;

/**
 * @group Basket
 */
class ProportionalCartCalculationVoucherTest extends CheckoutTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Shopware()->Container()->get(Connection::class)->beginTransaction();
        $this->setConfig('proportionalTaxCalculation', true);

        $this->setPaymentSurcharge(0);
        $this->setCustomerGroupSurcharge(0, 0);

        Shopware()->Container()->get(Connection::class)->executeQuery('UPDATE s_premium_dispatch SET active = 0 WHERE id = 12');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearCustomerGroupDiscount('EK');
        $this->setConfig('proportionalTaxCalculation', false);

        Shopware()->Container()->get(Connection::class)->rollBack();
    }

    public function testAbsoluteVoucher(): void
    {
        $this->setVoucherTax('absolut', 'default');
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.2799999999999998, $sBasket['sShippingcostsNet']);

        static::assertFalse(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(1, $sBasket['sTaxRates']);

        static::assertCount(2, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein', -5, -4.2016806722689, 'GUTABS');
    }

    public function testAbsoluteVoucherMultipleTaxesWithMaxTax(): void
    {
        $this->setVoucherTax('GUTABS', 'default');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 7.00));
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825543, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(3, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein', -5, -4.202, 'GUTABS');
    }

    public function testAbsoluteVoucherMultipleTaxes(): void
    {
        $this->setVoucherTax('GUTABS', 'auto');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 7.00));
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825543, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein (7%)', -2.63, -2.4605078157307, 'GUTABS');
        $this->hasBasketItem($sBasket['content'], 'Gutschein (19%)', -2.37, -1.9892912917379, 'GUTABS');
    }

    public function testPercentVoucher(): void
    {
        $this->setVoucherTax('GUTPROZ', 'default');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(100, 19.00));
        Shopware()->Modules()->Basket()->sAddVoucher('prozentual');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.2799999999999998, $sBasket['sShippingcostsNet']);

        static::assertFalse(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(1, $sBasket['sTaxRates']);

        static::assertCount(2, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 %', -10, -8.403, 'GUTPROZ');
    }

    public function testPercentVoucherProportionalWithOneTax(): void
    {
        $this->setVoucherTax('GUTPROZ', 'auto');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(100, 19.00));
        Shopware()->Modules()->Basket()->sAddVoucher('prozentual');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.2799999999999998, $sBasket['sShippingcostsNet']);

        static::assertFalse(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(1, $sBasket['sTaxRates']);

        static::assertCount(2, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 %', -10, -8.4033613445378, 'GUTPROZ');
    }

    public function testPercentVoucherProportionalWithMultipleTaxes(): void
    {
        $this->setVoucherTax('GUTPROZ', 'auto');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 7.00));
        Shopware()->Modules()->Basket()->sAddVoucher('prozentual');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825543, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 % (7%)', -5, -4.6728971962617, 'GUTPROZ');
        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 % (19%)', -5, -4.2016806722689, 'GUTPROZ');
    }

    public function testProportionalBasketView(): void
    {
        $this->setVoucherTax('GUTABS', 'auto');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 7.00));
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');

        $this->dispatch('/checkout/cart');

        $sBasket1 = $this->View()->getAssign('sBasketProportional');
        $sBasket2 = $this->View()->getAssign('sBasket');

        foreach ($sBasket2 as $key => $item) {
            if ($key !== 'content') {
                static::assertEquals($sBasket2[$key], $sBasket1[$key]);
            }
        }
    }
}
