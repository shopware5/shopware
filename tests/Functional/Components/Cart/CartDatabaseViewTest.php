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
use Shopware\Tests\Functional\Components\CheckoutTestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CartDatabaseViewTest extends CheckoutTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public bool $clearBasketOnReset = false;

    public function testEnsureCartInViewHasSameIDsAsTheDatabase(): void
    {
        $productNumber = $this->createProduct(5, 19.00);

        $this->loginCustomerOfGroup();

        $this->addProduct($productNumber);
        $this->visitConfirm();

        $cartItems = $this->View()->getAssign('sBasket')['content'];
        $cartPositionIds = array_map('\intval', array_column($cartItems, 'id'));

        $connection = $this->getContainer()->get(Connection::class);
        $preparedStatement = $connection->prepare('SELECT id FROM s_order_basket WHERE id = :id');
        foreach ($cartPositionIds as $cartPositionId) {
            $preparedStatement->bindValue('id', $cartPositionId);
            $cartPositionDatabaseId = (int) $preparedStatement->executeQuery()->fetchOne();
            static::assertSame($cartPositionId, $cartPositionDatabaseId, 'Cart position in view is not the same as in the database.');
        }
    }
}
