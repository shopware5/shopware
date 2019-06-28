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

use Enlight_Controller_Request_RequestHttp;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware\Tests\Functional\Traits\FixtureBehaviour;

class CartMigrationTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use FixtureBehaviour;

    public function testMigrateOnLoginWithEmptyCart(): void
    {
        self::executeFixture(__DIR__ . '/fixture/cart_migration_1.sql');

        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestTestCase());
        Shopware()->Modules()->Basket()->sRefreshBasket();
        static::assertEquals(0, Shopware()->Session()->get('sBasketAmount'));

        $this->loginFrontendUser();

        static::assertGreaterThan(0, Shopware()->Session()->get('sBasketAmount'));
    }

    public function testMigrateOnLoginWithFilledCart(): void
    {
        self::executeFixture(__DIR__ . '/fixture/cart_migration_1.sql');

        Shopware()->Modules()->Basket()->sAddArticle('SW10001');
        Shopware()->Modules()->Basket()->sRefreshBasket();

        $currentBasketAmount = Shopware()->Session()->get('sBasketAmount');

        $this->loginFrontendUser();

        static::assertEquals($currentBasketAmount, Shopware()->Session()->get('sBasketAmount'));
    }

    protected function loginFrontendUser(): void
    {
        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());
        $user = Shopware()->Db()->fetchRow(
            'SELECT `id`, `email`, `password`, `subshopID`, `language` FROM s_user WHERE `id` = 1'
        );

        $shop = Shopware()->Models()->getRepository(Shop::class)->getActiveById($user['language']);
        $shop->registerResources();

        Shopware()->Session()->Admin = true;
        Shopware()->System()->_POST = [
            'email' => $user['email'],
            'passwordMD5' => $user['password'],
        ];
        Shopware()->Modules()->Admin()->sLogin(true);
    }
}
