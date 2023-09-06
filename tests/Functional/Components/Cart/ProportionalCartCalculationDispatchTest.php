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
class ProportionalCartCalculationDispatchTest extends CheckoutTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Shopware()->Container()->get(Connection::class)->beginTransaction();
        $this->setConfig('proportionalTaxCalculation', true);

        Shopware()->Container()->get(Connection::class)->executeQuery('UPDATE s_premium_dispatch SET active = 0 WHERE id = 12');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->setConfig('proportionalTaxCalculation', false);

        Shopware()->Container()->get(Connection::class)->rollBack();
    }

    public function testSurchargeInDispatch(): void
    {
        $this->setPaymentSurcharge(5);
        Shopware()->Container()->get(Connection::class)->executeQuery('UPDATE s_premium_dispatch SET surcharge_calculation = 0');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(8.9000000000000004, $sBasket['sShippingcosts']);
        static::assertEquals(8.9000000000000004, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(7.4800000000000004, $sBasket['sShippingcostsNet']);

        static::assertFalse(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(1, $sBasket['sTaxRates']);

        static::assertCount(1, $sBasket['content']);
    }

    public function testSurchargeInDispatchMultipleTaxes(): void
    {
        $this->setPaymentSurcharge(5);
        Shopware()->Container()->get(Connection::class)->executeQuery('UPDATE s_premium_dispatch SET surcharge_calculation = 0');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 7.00));

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        static::assertIsFloat($sBasket['sShippingcosts']);
        static::assertIsFloat($sBasket['sShippingcostsTax']);
        static::assertIsFloat($sBasket['sShippingcostsNet']);
        static::assertIsFloat($sBasket['sShippingcostsWithTax']);
        static::assertIsArray($sBasket['sTaxRates']);

        static::assertEquals(8.9000000000000004, $sBasket['sShippingcosts']);
        static::assertEquals(8.9000000000000004, $sBasket['sShippingcostsWithTax']);
        static::assertEquals(19.0, $sBasket['sShippingcostsTax']);
        static::assertEquals(7.9206424112941871, $sBasket['sShippingcostsNet']);

        static::assertTrue(isset($sBasket['sShippingcostsTaxProportional']));
        static::assertCount(2, $sBasket['sTaxRates']);

        static::assertCount(2, $sBasket['content']);
    }

    public function testBasketDiscountIntroducedByDispatch(): void
    {
        $this->setPaymentSurcharge(0);
        Shopware()->Container()->get(Connection::class)->executeQuery('UPDATE s_premium_dispatch SET active = 1 WHERE id = 12');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));

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

        $this->hasBasketItem($sBasket['content'], 'Warenkorbrabatt', -2, -1.6806722689076, 'SHIPPINGDISCOUNT');
    }

    public function testBasketDiscountIntroducedByDispatchMultipleTaxes(): void
    {
        $this->setPaymentSurcharge(0);
        Shopware()->Container()->get(Connection::class)->executeQuery('UPDATE s_premium_dispatch SET active = 1 WHERE id = 12');

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 19.00));
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(50, 7.00));

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

        $this->hasBasketItem($sBasket['content'], 'Warenkorbrabatt (19%)', -0.95, -0.79571651669517, 'SHIPPINGDISCOUNT');
        $this->hasBasketItem($sBasket['content'], 'Warenkorbrabatt (7%)', -1.05, -0.98420312629229, 'SHIPPINGDISCOUNT');
    }
}
