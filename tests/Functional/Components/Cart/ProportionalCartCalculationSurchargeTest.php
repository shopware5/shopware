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

use Shopware\Tests\Functional\Components\CheckoutTest;

/**
 * @group Basket
 */
class ProportionalCartCalculationSurchargeTest extends CheckoutTest
{
    /**
     * A article with 7% tax
     *
     * @var string
     */
    private $tax7;

    /**
     * A article with 19% tax
     *
     * @var string
     */
    private $tax19;

    public function setUp(): void
    {
        parent::setUp();

        $this->setConfig('proportionalTaxCalculation', true);
        $this->setPaymentSurcharge(0);
        Shopware()->Container()->get('dbal_connection')->beginTransaction();

        $this->setCustomerGroupSurcharge(0, 0);

        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_premium_dispatch SET active = 0 WHERE id = 12');

        $this->tax7 = $this->createArticle(10, 7.00);
        $this->tax19 = $this->createArticle(10, 19.00);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Shopware()->Container()->get('dbal_connection')->rollBack();

        $this->setConfig('proportionalTaxCalculation', false);
        $this->setPaymentSurcharge(0);
    }

    public function testMultipleTaxesWithoutDiscounts()
    {
        Shopware()->Modules()->Basket()->sAddArticle($this->tax7, 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19, 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sShippingcostsTaxProportional']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825541, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sShippingcostsTaxProportional']);
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(2, $sBasket['content']);

        $this->reset();
    }

    public function testMultipleTaxesWithPaymentSurchargeAbsolute()
    {
        $this->setPaymentSurcharge(10);

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7, 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19, 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sShippingcostsTaxProportional']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825541, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sShippingcostsTaxProportional']);
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Zuschlag für Zahlungsart (19%)', 4.73, 3.9785825834759, 'sw-payment-absolute');
        $this->hasBasketItem($sBasket['content'], 'Zuschlag für Zahlungsart (7%)', 5.27, 4.9210156314614, 'sw-payment-absolute');

        $this->reset();
    }

    public function testMultipleTaxesWithPaymentSurchargePercent()
    {
        $this->setPaymentSurcharge(0, 10);

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7, 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19, 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sShippingcostsTaxProportional']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825541, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sShippingcostsTaxProportional']);
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Zuschlag für Zahlungsart (19%)', 1.0, 0.84033613445378, 'sw-payment');
        $this->hasBasketItem($sBasket['content'], 'Zuschlag für Zahlungsart (7%)', 1.0, 0.93457943925234, 'sw-payment');

        $this->reset();
    }

    public function testMultipleTaxesWithPaymentSurchargePercentCountry()
    {
        $this->setPaymentSurcharge(0, 0, 'DE:10');

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7, 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19, 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sShippingcostsTaxProportional']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825541, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sShippingcostsTaxProportional']);
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Zuschlag für Zahlungsart (19%)', 4.73, 3.9785825834759, 'sw-payment-absolute');
        $this->hasBasketItem($sBasket['content'], 'Zuschlag für Zahlungsart (7%)', 5.27, 4.9210156314614, 'sw-payment-absolute');

        $this->reset();
    }

    public function testMultipleTaxesWithPaymentSurchargeAbsoluteNegative()
    {
        $this->setPaymentSurcharge(-10);

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7, 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19, 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sShippingcostsTaxProportional']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825541, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sShippingcostsTaxProportional']);
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Abschlag für Zahlungsart (19%)', -4.73, -3.9785825834759, 'sw-payment-absolute');
        $this->hasBasketItem($sBasket['content'], 'Abschlag für Zahlungsart (7%)', -5.27, -4.9210156314614, 'sw-payment-absolute');

        $this->reset();
    }

    public function testMultipleTaxesWithPaymentSurchargePercentNegative()
    {
        $this->setPaymentSurcharge(0, -10);

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7, 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19, 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sShippingcostsTaxProportional']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825541, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sShippingcostsTaxProportional']);
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Abschlag für Zahlungsart (19%)', -1.0, -0.84033613445378, 'sw-payment');
        $this->hasBasketItem($sBasket['content'], 'Abschlag für Zahlungsart (7%)', -1.0, -0.93457943925234, 'sw-payment');

        $this->reset();
    }

    public function testMultipleTaxesWithPaymentSurchargePercentCountryNegative()
    {
        $this->setPaymentSurcharge(0, 0, 'DE:-10');

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7, 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19, 1);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sShippingcostsTaxProportional']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(3.9, $sBasket['sShippingcosts']);
        static::assertEquals(3.9, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(3.470843303825541, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sShippingcostsTaxProportional']);
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Abschlag für Zahlungsart (19%)', -4.73, -3.9785825834759, 'sw-payment-absolute');
        $this->hasBasketItem($sBasket['content'], 'Abschlag für Zahlungsart (7%)', -5.27, -4.9210156314614, 'sw-payment-absolute');

        $this->reset();
    }
}
