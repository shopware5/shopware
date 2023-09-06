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

class FloatTaxCalculationTest extends CheckoutTestCase
{
    private Connection $connection;

    private int $taxId;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = Shopware()->Container()->get(Connection::class);
        $this->connection->beginTransaction();
        $this->connection->insert('s_core_tax', ['tax' => 7.75, 'description' => '7.75 %']);
        $this->taxId = (int) $this->connection->lastInsertId();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->connection->rollBack();
    }

    public function testCartWithVoucher(): void
    {
        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(100, 7.75), 1);
        $voucherCode = $this->createVoucher(10, $this->taxId);
        Shopware()->Modules()->Basket()->sAddVoucher($voucherCode);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasket');

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 %', -10.00, -9.281, $voucherCode);
    }

    public function testCartWithVoucherProportional(): void
    {
        $this->setConfig('proportionalTaxCalculation', true);

        Shopware()->Modules()->Basket()->sAddArticle($this->createProduct(100, 7.75), 1);
        $voucherCode = $this->createVoucher(10, $this->taxId);
        Shopware()->Modules()->Basket()->sAddVoucher($voucherCode);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 %', -10.00, -9.281, $voucherCode);

        $this->setConfig('proportionalTaxCalculation', false);
    }
}
