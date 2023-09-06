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
class ProportionalCartCalculationCustomerGroupTest extends CheckoutTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Shopware()->Container()->get(Connection::class)->beginTransaction();
        $this->setConfig('proportionalTaxCalculation', true);

        $this->setPaymentSurcharge(0);
        $this->setCustomerGroupSurcharge(20, 5);

        Shopware()->Container()->get(Connection::class)->executeQuery('UPDATE s_premium_dispatch SET active = 0 WHERE id = 12');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearCustomerGroupDiscount('EK');
        $this->setConfig('proportionalTaxCalculation', false);

        Shopware()->Container()->get(Connection::class)->rollBack();
    }

    public function testCustomerGroupWithMinimumOrderNormal()
    {
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(5, 19.00), 1);

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

        $this->hasBasketItem($sBasket['content'], 'Mindermengenzuschlag', 5, 4.2016806722689, 'sw-surcharge');
    }

    public function testCustomerGroupWithMinimumOrderTaxes()
    {
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(5, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(3, 7.00), 1);

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
        static::assertEquals(3.424412889920227, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], 'Mindermengenzuschlag (7%)', 2, 1.870206613302, 'sw-surcharge');
        $this->hasBasketItem($sBasket['content'], 'Mindermengenzuschlag (19%)', 3, 2.5200663224931, 'sw-surcharge');
    }

    public function testCustomerGroupDiscountNormal()
    {
        $this->addCustomerGroupDiscount('EK', 20, 10);

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(500, 19.00), 1);

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

        $this->hasBasketItem($sBasket['content'], '-10 % Warenkorbrabatt', -50, -42.016806722689, 'sw-discount');
    }

    public function testCustomerGroupDiscountNormalMultipleTaxes()
    {
        $this->addCustomerGroupDiscount('EK', 20, 10);

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(500, 19.00), 1);
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(500, 7.00), 1);

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
        static::assertEquals(3.4708433038255415, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(4, $sBasket['content']);

        $this->hasBasketItem($sBasket['content'], '-10 % Warenkorbrabatt (19%)', -50, -42.016806722689, 'sw-discount');
        $this->hasBasketItem($sBasket['content'], '-10 % Warenkorbrabatt (7%)', -50, -46.728971962617, 'sw-discount');
    }
}
