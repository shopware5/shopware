<?php

declare(strict_types=1);
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

class CartRaceConditionTest extends CheckoutTest
{
    public bool $clearBasketOnReset = false;

    public function testPriceChangesInConfirmWithoutPriceModifications(): void
    {
        $productNumber = $this->createProduct(5, 19.00);

        $this->loginFrontendCustomer();

        $this->addProduct($productNumber);
        $this->visitConfirm();

        $orderAmount = $this->View()->getAssign('sBasket')['AmountNumeric'];

        $this->visitFinish();

        static::assertSame($orderAmount, $this->View()->getAssign('sBasket')['AmountNumeric']);
    }

    public function testPriceChangesInConfirmWithPriceModifications(): void
    {
        $productNumber1 = $this->createProduct(5, 19.00);

        $this->loginFrontendCustomer();

        $this->addProduct($productNumber1);
        $this->visitConfirm();

        $orderAmount = (float) $this->View()->getAssign('sBasket')['AmountNumeric'];
        static::assertGreaterThan(0.0, $orderAmount);

        $this->updateProductPrice($productNumber1, 20, 19.00);

        $this->visitFinish();

        $orderNumber = $this->View()->getAssign('sOrderNumber');

        $savedOrderAmount = (float) $this->getContainer()->get(Connection::class)->executeQuery(
            'SELECT invoice_amount FROM s_order WHERE ordernumber = :orderNumber', ['orderNumber' => $orderNumber]
        )->fetchOne();

        static::assertSame($orderAmount, $savedOrderAmount);
    }
}
