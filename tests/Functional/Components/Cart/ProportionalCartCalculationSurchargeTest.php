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
class ProportionalCartCalculationSurchargeTest extends CheckoutTestCase
{
    /**
     * A product with 7% tax
     */
    private string $tax7;

    /**
     * A product with 19% tax
     */
    private string $tax19;

    public function setUp(): void
    {
        parent::setUp();

        $this->setConfig('proportionalTaxCalculation', true);
        $this->setPaymentSurcharge(0);
        Shopware()->Container()->get(Connection::class)->beginTransaction();

        $this->setCustomerGroupSurcharge(0, 0);

        Shopware()->Container()->get(Connection::class)->executeQuery('UPDATE s_premium_dispatch SET active = 0 WHERE id = 12');

        $this->tax7 = $this->createProduct(10, 7.00);
        $this->tax19 = $this->createProduct(10, 19.00);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Shopware()->Container()->get(Connection::class)->rollBack();

        $this->setConfig('proportionalTaxCalculation', false);
        $this->setPaymentSurcharge(0);
    }

    public function testMultipleTaxesWithoutDiscounts(): void
    {
        Shopware()->Modules()->Basket()->sAddArticle($this->tax7);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19);

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

    public function testMultipleTaxesWithPaymentSurchargeAbsolute(): void
    {
        $this->setPaymentSurcharge(10);

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19);

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

    public function testMultipleTaxesWithPaymentSurchargePercent(): void
    {
        $this->setPaymentSurcharge(0, 10);

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19);

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

    public function testMultipleTaxesWithPaymentSurchargePercentCountry(): void
    {
        $this->setPaymentSurcharge(0, 0, 'DE:10');

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19);

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

    public function testMultipleTaxesWithPaymentSurchargeAbsoluteNegative(): void
    {
        $this->setPaymentSurcharge(-10);

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19);

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

    public function testMultipleTaxesWithPaymentSurchargePercentNegative(): void
    {
        $this->setPaymentSurcharge(0, -10);

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19);

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

    public function testMultipleTaxesWithPaymentSurchargePercentCountryNegative(): void
    {
        $this->setPaymentSurcharge(0, 0, 'DE:-10');

        Shopware()->Modules()->Basket()->sAddArticle($this->tax7);
        Shopware()->Modules()->Basket()->sAddArticle($this->tax19);

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
