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

namespace Shopware\Tests\Functional\Components\Cart;

use Shopware\Components\Test\CheckoutTest;

/**
 * @group Basket
 */
class ProportionalCartCalculationVoucherTest extends CheckoutTest
{
    public function setUp()
    {
        parent::setUp();
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        $this->setConfig('proportionalTaxCalculation', true);

        $this->setPaymentSurcharge(0);
        $this->setCustomerGroupSurcharge(0, 0);

        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_premium_dispatch SET active = 0 WHERE id = 12');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->clearCustomerGroupDiscount('EK');
        $this->setConfig('proportionalTaxCalculation', false);

        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function testAbsoluteVoucher()
    {
        $this->setVoucherTax('absolut', 'default');
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->assertInternalType('float', $sBasket['sShippingcosts']);
        $this->assertInternalType('float', $sBasket['sShippingcostsTax']);
        $this->assertInternalType('float', $sBasket['sShippingcostsNet']);
        $this->assertInternalType('float', $sBasket['sShippingcostsWithTax']);
        $this->assertInternalType('array', $sBasket['sTaxRates']);

        $this->assertEquals(3.9, $sBasket['sShippingcosts']);
        $this->assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        $this->assertEquals(19.0, $sBasket['sShippingcostsTax']);
        $this->assertEquals(3.2799999999999998, $sBasket['sShippingcostsNet']);

        $this->assertFalse(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(1, $sBasket['sTaxRates']);

        $this->assertCount(2, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein', -5, -4.2016806722689, 'GUTABS');
    }

    public function testAbsoluteVoucherMultipleTaxesWithMaxTax()
    {
        $this->setVoucherTax('GUTABS', 'default');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 7.00), 1);
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->assertInternalType('float', $sBasket['sShippingcosts']);
        $this->assertInternalType('float', $sBasket['sShippingcostsTax']);
        $this->assertInternalType('float', $sBasket['sShippingcostsNet']);
        $this->assertInternalType('float', $sBasket['sShippingcostsWithTax']);
        $this->assertInternalType('array', $sBasket['sTaxRates']);

        $this->assertEquals(3.9, $sBasket['sShippingcosts']);
        $this->assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        $this->assertEquals(19.0, $sBasket['sShippingcostsTax']);
        $this->assertEquals(3.4708433038255415, $sBasket['sShippingcostsNet']);

        $this->assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(2, $sBasket['sTaxRates']);

        $this->assertCount(3, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein', -5, -4.202, 'GUTABS');
    }

    public function testAbsoluteVoucherMultipleTaxes()
    {
        $this->setVoucherTax('GUTABS', 'auto');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 7.00), 1);
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->assertInternalType('float', $sBasket['sShippingcosts']);
        $this->assertInternalType('float', $sBasket['sShippingcostsTax']);
        $this->assertInternalType('float', $sBasket['sShippingcostsNet']);
        $this->assertInternalType('float', $sBasket['sShippingcostsWithTax']);
        $this->assertInternalType('array', $sBasket['sTaxRates']);

        $this->assertEquals(3.9, $sBasket['sShippingcosts']);
        $this->assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        $this->assertEquals(19.0, $sBasket['sShippingcostsTax']);
        $this->assertEquals(3.4708433038255415, $sBasket['sShippingcostsNet']);

        $this->assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(2, $sBasket['sTaxRates']);

        $this->assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein (7%)', -2.63, -2.4605078157307, 'GUTABS');
        $this->hasBasketItem($sBasket['content'], 'Gutschein (19%)', -2.37, -1.9892912917379, 'GUTABS');
    }

    public function testPercentVoucher()
    {
        $this->setVoucherTax('GUTPROZ', 'default');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(100, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddVoucher('prozentual');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->assertInternalType('float', $sBasket['sShippingcosts']);
        $this->assertInternalType('float', $sBasket['sShippingcostsTax']);
        $this->assertInternalType('float', $sBasket['sShippingcostsNet']);
        $this->assertInternalType('float', $sBasket['sShippingcostsWithTax']);
        $this->assertInternalType('array', $sBasket['sTaxRates']);

        $this->assertEquals(3.9, $sBasket['sShippingcosts']);
        $this->assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        $this->assertEquals(19.0, $sBasket['sShippingcostsTax']);
        $this->assertEquals(3.2799999999999998, $sBasket['sShippingcostsNet']);

        $this->assertFalse(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(1, $sBasket['sTaxRates']);

        $this->assertCount(2, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 %', -10, -8.403, 'GUTPROZ');
    }

    public function testPercentVoucherProportionalWithOneTax()
    {
        $this->setVoucherTax('GUTPROZ', 'auto');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(100, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddVoucher('prozentual');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->assertInternalType('float', $sBasket['sShippingcosts']);
        $this->assertInternalType('float', $sBasket['sShippingcostsTax']);
        $this->assertInternalType('float', $sBasket['sShippingcostsNet']);
        $this->assertInternalType('float', $sBasket['sShippingcostsWithTax']);
        $this->assertInternalType('array', $sBasket['sTaxRates']);

        $this->assertEquals(3.9, $sBasket['sShippingcosts']);
        $this->assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        $this->assertEquals(19.0, $sBasket['sShippingcostsTax']);
        $this->assertEquals(3.2799999999999998, $sBasket['sShippingcostsNet']);

        $this->assertFalse(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(1, $sBasket['sTaxRates']);

        $this->assertCount(2, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 %', -10, -8.4033613445378, 'GUTPROZ');
    }

    public function testPercentVoucherProportionalWithMultipleTaxes()
    {
        $this->setVoucherTax('GUTPROZ', 'auto');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 7.00), 1);
        Shopware()->Modules()->Basket()->sAddVoucher('prozentual');

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->assertInternalType('float', $sBasket['sShippingcosts']);
        $this->assertInternalType('float', $sBasket['sShippingcostsTax']);
        $this->assertInternalType('float', $sBasket['sShippingcostsNet']);
        $this->assertInternalType('float', $sBasket['sShippingcostsWithTax']);
        $this->assertInternalType('array', $sBasket['sTaxRates']);

        $this->assertEquals(3.9, $sBasket['sShippingcosts']);
        $this->assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        $this->assertEquals(19.0, $sBasket['sShippingcostsTax']);
        $this->assertEquals(3.4708433038255415, $sBasket['sShippingcostsNet']);

        $this->assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(2, $sBasket['sTaxRates']);

        $this->assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 % (7%)', -5, -4.6728971962617, 'GUTPROZ');
        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 % (19%)', -5, -4.2016806722689, 'GUTPROZ');
    }

    public function testProportionalBasketView()
    {
        $this->setVoucherTax('GUTABS', 'auto');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 7.00), 1);
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');

        $this->dispatch('/checkout/cart');

        $sBasket1 = $this->View()->getAssign('sBasketProportional');
        $sBasket2 = $this->View()->getAssign('sBasket');

        foreach ($sBasket2 as $key => $item) {
            if ($key !== 'content') {
                $this->assertEquals($sBasket2[$key], $sBasket1[$key]);
            }
        }
    }
}
