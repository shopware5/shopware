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

use Doctrine\DBAL\Connection;
use Shopware\Tests\Functional\Components\CheckoutTest;

class FloatTaxCalculationTest extends CheckoutTest
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $taxId;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();
        $this->connection->insert('s_core_tax', ['tax' => 7.75, 'description' => '7.75 %']);
        $this->taxId = $this->connection->lastInsertId();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->connection->rollBack();
    }

    public function testCartWithVoucher()
    {
        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(100, 7.75), 1);
        $voucherCode = $this->createVoucher(10, $this->taxId);
        Shopware()->Modules()->Basket()->sAddVoucher($voucherCode);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasket');

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 %', -10.00, -9.281, $voucherCode);
    }

    public function testCartWithVoucherProportional()
    {
        $this->setConfig('proportionalTaxCalculation', true);

        Shopware()->Modules()->Basket()->sAddArticle($this->createArticle(100, 7.75), 1);
        $voucherCode = $this->createVoucher(10, $this->taxId);
        Shopware()->Modules()->Basket()->sAddVoucher($voucherCode);

        $this->dispatch('/checkout/cart');

        $sBasket = $this->View()->getAssign('sBasketProportional');

        $this->hasBasketItem($sBasket['content'], 'Gutschein 10 %', -10.00, -9.281, $voucherCode);

        $this->setConfig('proportionalTaxCalculation', false);
    }
}
