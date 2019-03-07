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
class ProportionalCartCalculationDispatchTest extends CheckoutTest
{
    public function setUp()
    {
        parent::setUp();
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        $this->setConfig('proportionalTaxCalculation', true);

        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_premium_dispatch SET active = 0 WHERE id = 12');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->setConfig('proportionalTaxCalculation', false);

        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function testSurchargeInDispatch()
    {
        $this->setPaymentSurcharge(5);
        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_premium_dispatch SET surcharge_calculation = 0');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->assertInternalType('float', $sBasket['sShippingcosts']);
        $this->assertInternalType('float', $sBasket['sShippingcostsTax']);
        $this->assertInternalType('float', $sBasket['sShippingcostsNet']);
        $this->assertInternalType('float', $sBasket['sShippingcostsWithTax']);
        $this->assertInternalType('array', $sBasket['sTaxRates']);

        $this->assertEquals(8.9000000000000004, $sBasket['sShippingcosts']);
        $this->assertEquals(8.9000000000000004, $sBasket['sShippingcostsWithTax']);
        $this->assertEquals(19.0, $sBasket['sShippingcostsTax']);
        $this->assertEquals(7.4800000000000004, $sBasket['sShippingcostsNet']);

        $this->assertFalse(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(1, $sBasket['sTaxRates']);

        $this->assertCount(1, $sBasket['content']);
    }

    public function testSurchargeInDispatchMultipleTaxes()
    {
        $this->setPaymentSurcharge(5);
        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_premium_dispatch SET surcharge_calculation = 0');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 7.00), 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->assertInternalType('float', $sBasket['sShippingcosts']);
        $this->assertInternalType('float', $sBasket['sShippingcostsTax']);
        $this->assertInternalType('float', $sBasket['sShippingcostsNet']);
        $this->assertInternalType('float', $sBasket['sShippingcostsWithTax']);
        $this->assertInternalType('array', $sBasket['sTaxRates']);

        $this->assertEquals(8.9000000000000004, $sBasket['sShippingcosts']);
        $this->assertEquals(8.9000000000000004, $sBasket['sShippingcostsWithTax']);
        $this->assertEquals(19.0, $sBasket['sShippingcostsTax']);
        $this->assertEquals(7.9206424112941871, $sBasket['sShippingcostsNet']);

        $this->assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(2, $sBasket['sTaxRates']);

        $this->assertCount(2, $sBasket['content']);
    }

    public function testBasketDiscountIntroducedByDispatch()
    {
        $this->setPaymentSurcharge(0);
        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_premium_dispatch SET active = 1 WHERE id = 12');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);

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

        $this->hasBasketItem($sBasket['content'], 'Warenkorbrabatt', -2, -1.6806722689076, 'SHIPPINGDISCOUNT');
    }

    public function testBasketDiscountIntroducedByDispatchMultipleTaxes()
    {
        $this->setPaymentSurcharge(0);
        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_premium_dispatch SET active = 1 WHERE id = 12');

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(50, 7.00), 1);

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

        $this->hasBasketItem($sBasket['content'], 'Warenkorbrabatt (19%)', -0.95, -0.79571651669517, 'SHIPPINGDISCOUNT');
        $this->hasBasketItem($sBasket['content'], 'Warenkorbrabatt (7%)', -1.05, -0.98420312629229, 'SHIPPINGDISCOUNT');
    }
}
