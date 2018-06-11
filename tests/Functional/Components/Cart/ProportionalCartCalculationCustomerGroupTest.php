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
 * Class ProportionalCartCalculationCustomerGroupTest
 *
 * @group Basket
 */
class ProportionalCartCalculationCustomerGroupTest extends CheckoutTest
{
    public function setUp()
    {
        parent::setUp();
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        $this->setConfig('proportionalTaxCalculation', true);

        $this->setPaymentSurcharge(0);
        $this->setCustomerGroupSurcharge(20, 5);

        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_premium_dispatch SET active = 0 WHERE id = 12');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->clearCustomerGroupDiscount('EK');
        $this->setConfig('proportionalTaxCalculation', false);

        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function testCustomerGroupWithMinimumOrderNormal()
    {
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(5, 19.00), 1);

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

        $this->hasBasketItem($sBasket['content'], 'Mindermengenzuschlag', 5, 4.2016806722689, 'sw-surcharge');
    }

    public function testCustomerGroupWithMinimumOrderTaxes()
    {
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(5, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(3, 7.00), 1);

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
        $this->assertEquals(3.4151417576376346, $sBasket['sShippingcostsNet']);

        $this->assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(2, $sBasket['sTaxRates']);

        $this->assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Mindermengenzuschlag (7%)', 1.88, 1.7523364485981, 'sw-surcharge');
        $this->hasBasketItem($sBasket['content'], 'Mindermengenzuschlag (19%)', 3.13, 2.6260504201681, 'sw-surcharge');
    }

    public function testCustomerGroupDiscountNormal()
    {
        $this->addCustomerGroupDiscount('EK', 20, 10);

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(500, 19.00), 1);

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

        $this->hasBasketItem($sBasket['content'], '-10 % Warenkorbrabatt', -50, -42.016806722689, 'sw-discount');
    }

    public function testCustomerGroupDiscountNormalMultipleTaxes()
    {
        $this->addCustomerGroupDiscount('EK', 20, 10);

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(500, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(500, 7.00), 1);

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
        $this->assertEquals(3.4610853687269296, $sBasket['sShippingcostsNet']);

        $this->assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        $this->assertCount(2, $sBasket['sTaxRates']);

        $this->assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], '-10 % Warenkorbrabatt (19%)', -50, -42.016806722689, 'sw-discount');
        $this->hasBasketItem($sBasket['content'], '-10 % Warenkorbrabatt (7%)', -50, -46.728971962617, 'sw-discount');
    }
}
